<?php

namespace App\Services\Expenses;

use App\Models\Expenses\ExpensePaymentMethod;

class PaymentMethodService
{
    /*
    * 支払方法一覧を取得
    */
    public function list()
    {
        return ExpensePaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /*
    * 支払方法を作成
    */
    public function create(array $data)
    {
        return ExpensePaymentMethod::create($data);
    }

    /*
    * 支払方法を削除
    */
    public function delete(int $id)
    {
        return ExpensePaymentMethod::findOrFail($id)->delete();
    }
}
