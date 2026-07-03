<?php

namespace App\Queries\Expenses;

use App\Enums\ActiveStatus;
use App\Enums\Expenses\ExpenseGroupBy;
use App\Enums\Expenses\ReportType;
use App\Models\Expenses\Expense;
use App\Models\Expenses\ExpenseRecurringAdjustment;
use App\Support\DateUtil;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseQuery
{
    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return Collection<int, \stdClass>
     */
    public function aggregate(array $range, ExpenseGroupBy $groupBy): Collection
    {
        if ($groupBy === ExpenseGroupBy::DATE) {
            return $this->aggregateByDate($range);
        }

        $model = $groupBy->model();
        $table = $groupBy->table();
        $key = (string) $groupBy->recurringKey();

        /** @var Builder<Model> $query */
        $query = $model::query();

        /** @var Collection<int, \stdClass> $result */
        $result = $query
            ->leftJoin('expenses', function ($join) use ($range, $table, $key) {
                /** @var JoinClause $join */
                $join->on("expenses.$key", '=', "$table.id")
                    ->whereBetween('expenses.date', [$range['start'], $range['end']])
                    ->whereNull('expenses.deleted_at');
            })
            ->select([
                "$table.id as $key",
                "$table.name",
                "$table.initial_balance",

                DB::raw('COALESCE(SUM(expenses.amount), 0) as total_amount'),
                DB::raw('COALESCE(SUM(expenses.point_amount), 0) as total_point'),
                DB::raw('COALESCE(SUM(expenses.amount) - SUM(expenses.point_amount), 0) as net_amount'),
                DB::raw('COUNT(expenses.id) as transaction_count'),
            ])
            ->where(function ($q) use ($table) {
                /** @var \Illuminate\Database\Query\Builder $q */
                $q->where($table.'.is_active', ActiveStatus::ACTIVE->value);
            })
            ->groupBy([
                "$table.id",
                "$table.name",
                "$table.initial_balance",
            ])
            ->orderBy("$table.sort_order")
            ->toBase()
            ->get();

        return $result;
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return Collection<int, \stdClass>
     */
    public function aggregateByDate(array $range): Collection
    {
        /** @var Collection<int, \stdClass> $result */
        $result = Expense::query()
            ->includedInReport(ReportType::DAILY)
            ->select([
                'expenses.date',
                DB::raw('SUM(expenses.amount) as total_amount'),
                DB::raw('SUM(expenses.point_amount) as total_point'),
                DB::raw('(SUM(expenses.amount) - SUM(expenses.point_amount)) as net_amount'),
                DB::raw('COUNT(*) as transaction_count'),
            ])
            ->whereBetween('expenses.date', [$range['start'], $range['end']])
            ->groupBy('expenses.date')
            ->orderBy('expenses.date')
            ->toBase()
            ->get();

        return $result;
    }

    /**
     * @param  Collection<int, \stdClass>  $result
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return Collection<int, \stdClass>
     */
    public function recurring(Collection $result, ExpenseGroupBy $groupBy, array $range): Collection
    {
        $key = (string) $groupBy->recurringKey();
        if ($key === '') {
            return $result;
        }

        $list = ExpenseRecurringAdjustment::query()
            ->whereIn($key, $result->pluck($key)->filter())
            ->where('is_fixed_cost', 0)
            ->where('start_date', '<=', $range['end'])
            ->where(function ($q) use ($range) {
                /** @var Builder<ExpenseRecurringAdjustment> $q */
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $range['start']);
            })
            ->get()
            ->groupBy($key);

        return $result->transform(function ($item) use ($list, $key, $range) {
            /** @var \stdClass $item */
            $adjustment_list = $list[$item->$key] ?? [];
            /** @var float|int|string $extra */
            $extra = collect($adjustment_list)->sum(function ($adjustment) use ($range) {
                /** @var ExpenseRecurringAdjustment $adjustment */
                return (int) $adjustment->amount * $this->countRecurringOccurrences($adjustment, $range);
            });

            /** @var float|int|string $initialBalance */
            $initialBalance = $item->initial_balance ?? 0;
            $item->initial_balance = (int) $initialBalance + (int) $extra;

            return $item;
        });
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     */
    public function totalNetAmount(array $range): int
    {
        /** @var \stdClass|null $result */
        $result = Expense::query()
            ->whereBetween('date', [$range['start'], $range['end']])
            ->selectRaw('
            SUM(amount - point_amount) as net_amount
        ')
            ->toBase()
            ->first();

        /** @var float|int|string|null $netAmount */
        $netAmount = $result->net_amount ?? 0;

        return (int) $netAmount;
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return Collection<int, ExpenseRecurringAdjustment>
     */
    public function fixedCostAdjustments(array $range): Collection
    {
        return ExpenseRecurringAdjustment::query()
            ->where('is_fixed_cost', 1)
            ->where('start_date', '<=', $range['end'])
            ->where(function ($q) use ($range) {
                /** @var Builder<ExpenseRecurringAdjustment> $q */
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $range['start']);
            })
            ->orderBy('payment_day')
            ->orderBy('memo')
            ->get();
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     * @return Collection<int, Expense>
     */
    public function history(array $range, ?string $categoryId = null): Collection
    {
        return Expense::query()
            ->whereBetween('date', [$range['start'], $range['end']])
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param  array{start: CarbonImmutable, end: CarbonImmutable}  $range
     */
    private function countRecurringOccurrences(ExpenseRecurringAdjustment $adjustment, array $range): int
    {
        $activeStart = CarbonImmutable::parse($adjustment->start_date);
        $activeEnd = $adjustment->end_date
            ? CarbonImmutable::parse($adjustment->end_date)
            : null;
        $intervalMonths = max(1, (int) $adjustment->interval_months);

        $count = 0;
        $cursor = $range['start']->startOfMonth();
        $lastMonth = $range['end']->startOfMonth();
        $startMonth = $activeStart->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $windowStart = DateUtil::max($cursor, $range['start']);
            $windowEnd = DateUtil::min($cursor->endOfMonth(), $range['end']);
            $monthDiff = DateUtil::monthDiff($startMonth, $cursor);

            $isActiveInWindow = $windowEnd->gte($activeStart)
                && (! $activeEnd || $windowStart->lte($activeEnd));
            $isRecurringMonth = $monthDiff >= 0 && $monthDiff % $intervalMonths === 0;

            if ($isActiveInWindow && $isRecurringMonth) {
                $count++;
            }

            $cursor = $cursor->addMonth();
        }

        return $count;
    }
}
