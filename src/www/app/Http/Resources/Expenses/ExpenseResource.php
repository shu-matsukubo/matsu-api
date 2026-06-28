<?php

namespace App\Http\Resources\Expenses;

use App\Http\Resources\BaseResource;

class ExpenseResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'point_amount' => $this->point_amount,
            'category_name' => $this->category?->name,
            'payment_method_name' => $this->paymentMethod?->name,
            'date' => $this->date,
        ];
    }
}
