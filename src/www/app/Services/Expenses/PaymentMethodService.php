<?php

namespace App\Services\Expenses;

use App\Models\Expenses\ExpensePaymentMethod;

class PaymentMethodService
{
    /**
     * 支払方法一覧を取得
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ExpensePaymentMethod>
     */
    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return ExpensePaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 支払方法を作成
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ExpensePaymentMethod
    {
        return ExpensePaymentMethod::create($data);
    }

    /**
     * 支払方法を削除
     */
    public function delete(int $id): bool
    {
        return (bool) ExpensePaymentMethod::findOrFail($id)->delete();
    }
}
