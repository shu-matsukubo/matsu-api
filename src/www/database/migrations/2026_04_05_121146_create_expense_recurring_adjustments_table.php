<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_recurring_adjustments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('payment_method_id')->nullable();
            $table->ulid('category_id')->nullable();

            $table->integer('amount');
            $table->boolean('is_fixed_cost')->default(false);

            $table->unsignedInteger('interval_months');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('memo')->nullable();

            $table->timestamps();

            $table->foreign('payment_method_id')->references('id')->on('expense_payment_methods')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('expense_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_recurring_adjustments');
    }
};
