<?php

namespace App\Services\Expenses;

use App\Http\Resources\Expenses\ExpenseResource;
use App\Http\Resources\Expenses\SummaryResource;
use App\Support\DateUtil;
use App\Enums\Expenses\ExpenseGroupBy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Queries\Expenses\ExpenseQuery;
use App\Models\Expenses\Expense;

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
        $month = $params['month'] ?? null;
        $range = DateUtil::monthRange(DateUtil::resolveMonth($month));

        $groupBy = ExpenseGroupBy::from($params['group_by'] ?? null);

        // 履歴情報本体を取得
        $result = $this->query->aggregate($range, $groupBy);

        // 一定周期ごとの変動費の計算
        if ($groupBy->supportsRecurring()) {
            $result = $this->query->recurring($result, $groupBy, $month);
        }

        // 合計金額を計算
        $total = $this->query->totalNetAmount($range);

        // 固定費を計算
        $fixedCostTotal = $this->query->totalFixedCost($month);

        return SummaryResource::collection($result)->additional([
            'meta' => [
                'total_net_amount' => (int) ($total),
                'fixed_cost_net_amount' => (int) ($fixedCostTotal),
            ],
        ]);
    }

    /*
    * 支出履歴を取得
    */
    private function getHistory(array $params): AnonymousResourceCollection
    {
        // 範囲を指定
        $range = DateUtil::monthRange(DateUtil::resolveMonth($params['month']));

        // 取得
        $result = Expense::whereBetween('date', [
            $range['start'],
            $range['end']
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return ExpenseResource::collection($result);
    }

    /*
    * 支出を作成
    */
    public function create(array $data)
    {
        return Expense::create($data);
    }

    /*
    * 支出を削除
    */
    public function delete(Expense $expense)
    {
        return $expense->delete();
    }
}
