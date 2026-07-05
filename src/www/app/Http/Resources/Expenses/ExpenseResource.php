<?php

namespace App\Http\Resources\Expenses;

use App\Http\Resources\BaseResource;
use App\Models\Expenses\Expense;
use Illuminate\Http\Request;

/**
 * @mixin Expense
 */
class ExpenseResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
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
