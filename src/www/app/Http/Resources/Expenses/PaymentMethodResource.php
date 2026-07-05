<?php

namespace App\Http\Resources\Expenses;

use App\Http\Resources\BaseResource;
use App\Models\Expenses\ExpensePaymentMethod;
use Illuminate\Http\Request;

/**
 * @mixin ExpensePaymentMethod
 */
class PaymentMethodResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
