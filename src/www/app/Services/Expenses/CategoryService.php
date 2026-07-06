<?php

namespace App\Services\Expenses;

use App\Enums\ActiveStatus;
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
        return ExpenseCategory::where('is_active', ActiveStatus::ACTIVE)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * カテゴリを作成
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ExpenseCategory
    {
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ])->validate();

        /** @var array<string, mixed> $validated */
        return ExpenseCategory::create($validated);
    }

    /**
     * カテゴリを削除
     */
    public function delete(string $id): bool
    {
        return (bool) ExpenseCategory::findOrFail($id)->delete();
    }
}
