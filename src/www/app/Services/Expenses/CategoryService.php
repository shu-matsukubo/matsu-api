<?php

namespace App\Services\Expenses;

use App\Models\Expenses\ExpenseCategory;

class CategoryService
{
    /**
     * カテゴリ一覧を取得
     *
     * @return \Illuminate\Support\Collection<int, ExpenseCategory>
     */
    public function list()
    {
        return ExpenseCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * カテゴリを作成
     *
     * @param array<string, mixed> $data
     * @return ExpenseCategory
     */
    public function create(array $data)
    {
        /** @var ExpenseCategory $category */
        $category = ExpenseCategory::create($data);

        return $category;
    }

    /*
    * カテゴリを削除
    */
    public function delete(int $id)
    {
        return ExpenseCategory::findOrFail($id)->delete();
    }
}
