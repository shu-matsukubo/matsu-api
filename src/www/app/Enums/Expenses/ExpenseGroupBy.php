<?php

namespace App\Enums\Expenses;

use App\Models\Expenses\Expense;
use App\Models\Expenses\ExpenseCategory;
use App\Models\Expenses\ExpensePaymentMethod;
use Illuminate\Database\Eloquent\Model;

enum ExpenseGroupBy: string
{
    case CATEGORY = 'category';
    case PAYMENT_METHOD = 'payment_method';
    case DATE = 'date';

    public static function fromRequest(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::CATEGORY;
    }

    /**
     * @return class-string<Model>
     */
    public function model(): string
    {
        return match ($this) {
            self::CATEGORY => ExpenseCategory::class,
            self::PAYMENT_METHOD => ExpensePaymentMethod::class,
            self::DATE => Expense::class,
        };
    }

    public function table(): string
    {
        return match ($this) {
            self::CATEGORY => 'expense_categories',
            self::PAYMENT_METHOD => 'expense_payment_methods',
            self::DATE => 'expenses',
        };
    }

    public function foreignKey(): ?string
    {
        return match ($this) {
            self::CATEGORY => 'category_id',
            self::PAYMENT_METHOD => 'payment_method_id',
            self::DATE => null,
        };
    }

    public function idAlias(): ?string
    {
        return match ($this) {
            self::CATEGORY => 'category_id',
            self::PAYMENT_METHOD => 'payment_method_id',
            self::DATE => null,
        };
    }

    public function isMaster(): bool
    {
        return $this !== self::DATE;
    }

    public function recurringKey(): ?string
    {
        return match ($this) {
            self::CATEGORY => 'category_id',
            self::PAYMENT_METHOD => 'payment_method_id',
            default => null,
        };
    }

    public function resultKey(): ?string
    {
        return $this->recurringKey();
    }

    public function supportsRecurring(): bool
    {
        return $this !== self::DATE;
    }
}
