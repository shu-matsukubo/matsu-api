<?php

namespace Tests\Unit\Expenses;

use App\Models\Expenses\ExpenseRecurringAdjustment;
use App\Queries\Expenses\ExpenseQuery;
use App\Services\Expenses\ExpenseService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class FixedCostOccurrenceTest extends TestCase
{
    public function test_fixed_cost_without_payment_day_uses_start_date_day(): void
    {
        $occurrences = $this->fixedCostOccurrences(new ExpenseRecurringAdjustment([
            'amount' => 1200,
            'payment_day' => null,
            'is_fixed_cost' => true,
            'interval_months' => 1,
            'start_date' => '2026-05-06',
            'end_date' => null,
            'memo' => 'subscription',
        ]));

        $this->assertSame([
            [
                'name' => 'subscription',
                'payment_date' => '2026-05-06',
                'amount' => 1200,
            ],
        ], $occurrences->all());
    }

    public function test_fixed_cost_with_zero_payment_day_falls_back_to_start_date_day(): void
    {
        $occurrences = $this->fixedCostOccurrences(new ExpenseRecurringAdjustment([
            'amount' => 800,
            'payment_day' => 0,
            'is_fixed_cost' => true,
            'interval_months' => 1,
            'start_date' => '2026-05-06',
            'end_date' => null,
            'memo' => 'utility',
        ]));

        $this->assertSame([
            [
                'name' => 'utility',
                'payment_date' => '2026-05-06',
                'amount' => 800,
            ],
        ], $occurrences->all());
    }

    public function test_fixed_cost_with_payment_day_on_start_date_is_included(): void
    {
        $occurrences = $this->fixedCostOccurrences(new ExpenseRecurringAdjustment([
            'amount' => 1200,
            'payment_day' => 6,
            'is_fixed_cost' => true,
            'interval_months' => 1,
            'start_date' => '2026-05-06',
            'end_date' => null,
            'memo' => 'Apple Oneサブスク',
        ]));

        $this->assertSame([
            [
                'name' => 'Apple Oneサブスク',
                'payment_date' => '2026-05-06',
                'amount' => 1200,
            ],
        ], $occurrences->all());
    }

    private function fixedCostOccurrences(ExpenseRecurringAdjustment $adjustment)
    {
        $method = new ReflectionMethod(ExpenseService::class, 'fixedCostOccurrences');
        $method->setAccessible(true);

        return $method->invoke(new ExpenseService(new ExpenseQuery()), $adjustment, [
            'start' => CarbonImmutable::parse('2026-05-01'),
            'end' => CarbonImmutable::parse('2026-05-31'),
        ]);
    }
}
