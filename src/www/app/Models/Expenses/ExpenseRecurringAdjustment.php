<?php

namespace App\Models\Expenses;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'payment_method_id',
    'category_id',
    'amount',
    'is_fixed_cost',
    'interval_months',
    'start_date',
    'end_date',
    'memo',
])]
class ExpenseRecurringAdjustment extends BaseModel
{
    //
}
