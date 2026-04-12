<?php

namespace App\Http\Resources\Expenses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\DateUtil;

class SummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $netAmount = (int) ($this->net_amount ?? 0);

        $res = [
            'total_amount'      => (int) ($this->total_amount ?? 0),
            'total_point'       => (int) ($this->total_point ?? 0),
            'net_amount'        => $netAmount,
            'transaction_count' => (int) ($this->transaction_count ?? 0),
        ];

        if (isset($this->category_id)) {
            $res['category_id'] = $this->category_id;
            $res['category_name'] = $this->name ?? '未分類';
            $res['initial_balance'] = $this->initial_balance ?? 0;
            $res['remaining_balance'] = ($res['initial_balance'] - $netAmount);
        } elseif (isset($this->payment_method_id)) {
            $res['payment_method_id'] = $this->payment_method_id;
            $res['payment_method_name'] = $this->name ?? '不明';
            $res['initial_balance'] = $this->initial_balance ?? 0;
            $res['remaining_balance'] = ($res['initial_balance'] - $netAmount);
        } elseif (isset($this->date)) {
            $res['date'] = DateUtil::toDateString($this->date);
        }

        return $res;
    }
}
