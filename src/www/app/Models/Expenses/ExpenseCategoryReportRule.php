<?php

namespace App\Models\Expenses;

use App\Enums\Expenses\ReportType;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * カテゴリテーブルのリレーション
     *
     * @return BelongsTo<ExpenseCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
