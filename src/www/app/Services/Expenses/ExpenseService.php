<?php

namespace App\Services\Expenses;

use App\Enums\Expenses\ExpenseGroupBy;
use App\Http\Resources\Expenses\ExpenseResource;
use App\Http\Resources\Expenses\SummaryResource;
use App\Models\Expenses\Expense;
use App\Queries\Expenses\ExpenseQuery;
use App\Support\DateUtil;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
            default   => $this->getHistory($params),
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

        $total = $this->query->totalNetAmount($range);
        $fixedCostTotal = $this->query->totalFixedCost($range);

        return SummaryResource::collection($result)->additional([
            'meta' => [
                'total_net_amount' => (int) ($total),
                'fixed_cost_net_amount' => (int) ($fixedCostTotal),
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

        $result = Expense::whereBetween('date', [
            $range['start'],
            $range['end'],
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return ExpenseResource::collection($result);
    }

    /**
     * 支出を作成
     */
    public function create(array $data)
    {
        return Expense::create($data);
    }

    /**
     * 支出を削除
     */
    public function delete(Expense $expense)
    {
        return $expense->delete();
    }
}
