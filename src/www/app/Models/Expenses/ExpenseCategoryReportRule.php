<?php

namespace App\Models\Expenses;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Enums\Expenses\ReportType;

#[Fillable([
    'category_id',
    'report_type',
    'is_included',
])]
class ExpenseCategoryReportRule extends BaseModel
{
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'report_type' => ReportType::class,
        ]);
    }

    /*
    * カテゴリテーブルのリレーション
    */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
