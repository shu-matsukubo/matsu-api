<?php

namespace App\Models\Expenses;

use App\Enums\ActiveStatus;
use App\Models\BaseModel;
use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'sort_order',
    'is_active',
])]
class ExpenseCategory extends BaseModel
{
    use HasActiveScope;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'is_active' => ActiveStatus::class,
        ]);
    }

    /**
     * 支払い履歴とのリレーション
     *
     * @return HasMany<Expense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * レポートルールテーブルとのリレーション
     *
     * @return HasMany<ExpenseCategoryReportRule, $this>
     */
    public function reportRules(): HasMany
    {
        return $this->hasMany(ExpenseCategoryReportRule::class, 'category_id');
    }
}
