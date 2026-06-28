<?php

namespace App\Services\Expenses;

use App\Enums\Expenses\ExpenseGroupBy;
use App\Http\Resources\Expenses\ExpenseHistoryResource;
use App\Http\Resources\Expenses\SummaryResource;
use App\Models\Expenses\Expense;
use App\Models\Expenses\ExpenseRecurringAdjustment;
use App\Queries\Expenses\ExpenseQuery;
use App\Support\DateUtil;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function __construct(private ExpenseQuery $query)
    {
        //
    }

    /**
     * モードに応じて支出データを取得
     */
    public function getExpensesByMode(string $mode, array $params): AnonymousResourceCollection
    {
        return match ($mode) {
            'summary' => $this->getSummary($params),
            'history' => $this->getHistory($params),
            default => $this->getHistory($params),
        };
    }

    /**
     * カテゴリごとの集計データを取得
     */
    private function getSummary(array $params): AnonymousResourceCollection
    {
        $range = DateUtil::resolveDateRange(
            $params['start_date'] ?? null,
            $params['end_date'] ?? null
        );

        $groupBy = ExpenseGroupBy::from($params['group_by'] ?? null);

        $result = $this->query->aggregate($range, $groupBy);

        if ($groupBy->supportsRecurring()) {
            $result = $this->query->recurring($result, $groupBy, $range);
        }

        $expenseTotal = $this->query->totalNetAmount($range);
        $fixedCosts = $this->buildFixedCosts($this->query->fixedCostAdjustments($range), $range);
        $fixedCostTotal = $fixedCosts->sum('amount');

        return SummaryResource::collection($result)->additional([
            'meta' => [
                'total_net_amount' => (int) ($expenseTotal + $fixedCostTotal),
                'fixed_cost_net_amount' => (int) ($fixedCostTotal),
                'fixed_costs' => $fixedCosts,
            ],
        ]);
    }

    /**
     * 支出履歴を取得
     */
    private function getHistory(array $params): AnonymousResourceCollection
    {
        $range = DateUtil::resolveDateRange(
            $params['start_date'] ?? null,
            $params['end_date'] ?? null
        );

        $result = $this->query->history(
            $range,
            $params['category_id'] ?? null
        );

        return ExpenseHistoryResource::collection($result);
    }

    /**
     * 支出を作成
     */
    public function create(array $data): Expense
    {
        /** @var Expense $expense */
        $expense = Expense::create($data);

        return $expense;
    }

    /**
     * 支出を削除
     */
    public function delete(Expense $expense)
    {
        return $expense->delete();
    }

    private function buildFixedCosts(Collection $adjustments, array $range): Collection
    {
        return $adjustments
            ->flatMap(fn (ExpenseRecurringAdjustment $adjustment) => $this->fixedCostOccurrences($adjustment, $range))
            ->sortBy([
                ['payment_date', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function fixedCostOccurrences(ExpenseRecurringAdjustment $adjustment, array $range): Collection
    {
        $activeStart = DateUtil::parseDateValue($adjustment->start_date, 'start_date');
        $activeEnd = $adjustment->end_date
            ? DateUtil::parseDateValue($adjustment->end_date, 'end_date')
            : null;
        $intervalMonths = max(1, (int) $adjustment->interval_months);
        $paymentDay = $this->resolvePaymentDay($adjustment, $activeStart);

        $items = collect();
        $cursor = $range['start']->startOfMonth();
        $lastMonth = $range['end']->startOfMonth();
        $startMonth = $activeStart->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $monthDiff = DateUtil::monthDiff($startMonth, $cursor);
            $isRecurringMonth = $monthDiff >= 0 && $monthDiff % $intervalMonths === 0;

            if ($isRecurringMonth) {
                $paymentDate = DateUtil::dateInMonth($cursor, $paymentDay);
                $isActive = $paymentDate->gte($activeStart)
                    && (! $activeEnd || $paymentDate->lte($activeEnd));
                $isInRange = $paymentDate->betweenIncluded($range['start'], $range['end']);

                if ($isActive && $isInRange) {
                    $items->push([
                        'name' => $adjustment->memo ?? '',
                        'payment_date' => DateUtil::toDateString($paymentDate),
                        'amount' => (int) $adjustment->amount,
                    ]);
                }
            }

            $cursor = $cursor->addMonth();
        }

        return $items;
    }

    private function resolvePaymentDay(
        ExpenseRecurringAdjustment $adjustment,
        CarbonImmutable $activeStart
    ): int {
        $paymentDay = (int) ($adjustment->payment_day ?? 0);

        if ($paymentDay < 1) {
            return DateUtil::dayOfMonth($activeStart);
        }

        return $paymentDay;
    }
}
