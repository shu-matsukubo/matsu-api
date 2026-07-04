<?php

namespace App\Models\Expenses;

use App\Enums\Expenses\ReportType;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;

#[Fillable([
    'amount',
    'point_amount',
    'payment_method_id',
    'category_id',
    'memo',
    'date',
])]
class Expense extends BaseModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'date' => 'immutable_date',
            'deleted_at' => 'immutable_date',
        ]);
    }

    /**
     * @return BelongsTo<ExpensePaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(ExpensePaymentMethod::class);
    }

    /**
     * @return BelongsTo<ExpenseCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * レポート対象に含めるもののみ取得するスコープ
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeIncludedInReport(Builder $query, ReportType $type): Builder
    {
        return $query
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->leftJoin('expense_category_report_rules as rules', function ($join) use ($type) {
                /** @var JoinClause $join */
                $join->on('rules.category_id', '=', 'expense_categories.id')
                    ->where('rules.report_type', $type->value);
            })
            ->where(function ($q) {
                /** @phpstan-ignore argument.type */
                $q->whereNull('rules.is_included')
                    ->orWhere('rules.is_included', true);
            });
    }
}
