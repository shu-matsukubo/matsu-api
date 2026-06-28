<?php

namespace App\Http\Resources\Expenses;

use App\Http\Resources\BaseResource;
use App\Support\DateUtil;

class ExpenseHistoryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'net_amount' => (int) ($this->amount - $this->point_amount),
            'memo' => $this->memo,
            'date' => DateUtil::toDateString($this->date),
        ];
    }
}
