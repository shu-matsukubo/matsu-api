<?php

namespace App\Services\Expenses;

use App\Models\Expenses\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * カテゴリ一覧を取得
     *
     * @return Collection<int, ExpenseCategory>
     */
    public function list(): Collection
    {
        return ExpenseCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * カテゴリを作成
     *
     * @param  array<string, mixed>  $data
     * @return ExpenseCategory
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(array $data): ExpenseCategory
    {
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ])->validate();

        return ExpenseCategory::create($validated);
    }

    /**
     * カテゴリを削除
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $deleted = ExpenseCategory::findOrFail($id)->delete();
        if ($deleted === false) {
            throw new \Exception("Failed to delete category with ID: $id");
        }
        return true;
    }
}
