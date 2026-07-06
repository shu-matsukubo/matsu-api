<?php

namespace App\Services\Expenses;

use App\Enums\ActiveStatus;
use App\Models\Expenses\ExpensePaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class PaymentMethodService
{
    /**
     * 支払方法一覧を取得
     *
     * @return Collection<int, ExpensePaymentMethod>
     */
    public function list(): Collection
    {
        return ExpensePaymentMethod::where('is_active', ActiveStatus::ACTIVE)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 支払方法を作成
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function create(array $data): ExpensePaymentMethod
    {
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ])->validate();

        /** @var array<string, mixed> $validated */
        return ExpensePaymentMethod::create($validated);
    }

    /**
     * 支払方法を削除
     */
    public function delete(string $id): bool
    {
        $deleted = ExpensePaymentMethod::findOrFail($id)->delete();
        if ($deleted === false) {
            throw new \Exception("Failed to delete payment method with ID: $id");
        }

        return true;
    }
}
