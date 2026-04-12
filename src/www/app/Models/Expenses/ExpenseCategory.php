<?php

namespace App\Models\Expenses;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\ActiveStatus;
use App\Models\Traits\HasActiveScope;

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

    /*
    * 支払い履歴とのリレーション
    */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /*
    * レポートルールテーブルとのリレーション
    */
    public function reportRules()
    {
        return $this->hasMany(ExpenseCategoryReportRule::class, 'category_id');
    }
}
