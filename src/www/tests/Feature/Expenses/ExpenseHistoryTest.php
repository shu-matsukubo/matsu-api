<?php

namespace Tests\Feature\Expenses;

use App\Models\Expenses\Expense;
use App\Models\Expenses\ExpenseCategory;
use App\Models\Expenses\ExpensePaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_returns_expenses_for_category_in_date_range(): void
    {
        $category = ExpenseCategory::create([
            'name' => 'Food',
            'sort_order' => 1,
        ]);
        $otherCategory = ExpenseCategory::create([
            'name' => 'Transport',
            'sort_order' => 2,
        ]);
        $paymentMethod = ExpensePaymentMethod::create([
            'name' => 'Cash',
            'sort_order' => 1,
        ]);

        Expense::create([
            'amount' => 1200,
            'point_amount' => 200,
            'payment_method_id' => $paymentMethod->id,
            'category_id' => $category->id,
            'memo' => 'lunch',
            'date' => '2026-05-10',
        ]);
        Expense::create([
            'amount' => 800,
            'point_amount' => 0,
            'payment_method_id' => $paymentMethod->id,
            'category_id' => $category->id,
            'memo' => 'outside range',
            'date' => '2026-04-30',
        ]);
        Expense::create([
            'amount' => 500,
            'point_amount' => 0,
            'payment_method_id' => $paymentMethod->id,
            'category_id' => $otherCategory->id,
            'memo' => 'train',
            'date' => '2026-05-12',
        ]);

        $response = $this->getJson(
            "/api/expenses?mode=history&start_date=2026-05-01&end_date=2026-05-31&category_id={$category->id}"
        );

        $response
            ->assertOk()
            ->assertExactJson([
                [
                    'net_amount' => 1000,
                    'memo' => 'lunch',
                    'date' => '2026-05-10',
                ],
            ]);
    }
}
