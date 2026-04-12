<?php

namespace App\Queries\Expenses;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Enums\Expenses\ExpenseGroupBy;
use App\Models\Expenses\Expense;
use App\Models\Expenses\ExpenseRecurringAdjustment;
use App\Support\DateUtil;
use App\Enums\Expenses\ReportType;

class ExpenseQuery
{
    public function aggregate(array $range, ExpenseGroupBy $groupBy): Collection
    {
        if ($groupBy === ExpenseGroupBy::DATE) {
            return $this->aggregateByDate($range);
        }

        $model = $groupBy->model();
        $table = $groupBy->table();
        $key = $groupBy->recurringKey();

        return $model::query()
            ->leftJoin('expenses', function ($join) use ($range, $table, $key) {
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
            ->active()
            ->groupBy([
                "$table.id",
                "$table.name",
                "$table.initial_balance",
            ])
            ->orderBy("$table.sort_order")
            ->get();
    }

    public function aggregateByDate(array $range): Collection
    {
        return Expense::query()
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
            ->get();
    }

    public function recurring(Collection $result, ExpenseGroupBy $groupBy, ?string $month): Collection
    {
        $key = $groupBy->recurringKey();
        if (!$key) return $result;

        $target = DateUtil::startOfMonth(DateUtil::resolveMonth($month));

        $list = ExpenseRecurringAdjustment::query()
            ->whereIn($key, $result->pluck($key)->filter())
            ->whereDate('start_month', '<=', $target)
            ->where(function ($q) use ($target) {
                $q->whereNull('end_month')
                    ->orWhereDate('end_month', '>=', $target);
            })
            ->whereRaw(
                'MOD(TIMESTAMPDIFF(MONTH, start_month, ?), interval_months) = 0',
                [$target->format('Y-m-01')]
            )
            ->get()
            ->groupBy($key);

        return $result->transform(function ($item) use ($list, $key) {
            $extra = collect($list[$item->$key] ?? [])->sum('amount');
            $item->initial_balance += $extra;
            return $item;
        });
    }
}
