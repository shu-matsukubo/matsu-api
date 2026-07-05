<?php

namespace App\Http\Resources\Expenses;

use App\Http\Resources\BaseResource;
use App\Models\Expenses\Expense;
use App\Support\DateUtil;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * @mixin Expense
 */
class ExpenseHistoryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CarbonImmutable $date */
        $date = $this->date;

        return [
            'net_amount' => (int) ($this->amount - $this->point_amount),
            'memo' => $this->memo,
            'date' => DateUtil::toDateString($date),
        ];
    }
}
