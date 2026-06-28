<?php

namespace App\Services\Expenses;

use App\Models\Expenses\ExpensePaymentMethod;

class PaymentMethodService
{
    /**
     * 支払方法一覧を取得
     *
     * @return \Illuminate\Support\Collection<int, ExpensePaymentMethod>
     */
    public function list()
    {
        return ExpensePaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * 支払方法を作成
     *
     * @param array<string, mixed> $data
     * @return ExpensePaymentMethod
     */
    public function create(array $data)
    {
        /** @var ExpensePaymentMethod $paymentMethod */
        $paymentMethod = ExpensePaymentMethod::create($data);

        return $paymentMethod;
    }

    /*
    * 支払方法を削除
    */
    public function delete(int $id)
    {
        return ExpensePaymentMethod::findOrFail($id)->delete();
    }
}
