<?php

namespace App\Models\Expenses;

use App\Enums\ActiveStatus;
use App\Models\BaseModel;
use App\Models\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'name',
    'sort_order',
    'is_active',
])]
class ExpensePaymentMethod extends BaseModel
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
}
