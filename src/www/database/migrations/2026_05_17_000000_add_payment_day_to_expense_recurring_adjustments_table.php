<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_recurring_adjustments', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_day')->nullable()->after('amount');
        });

        DB::table('expense_recurring_adjustments')
            ->whereNull('payment_day')
            ->orderBy('id')
            ->each(function ($adjustment) {
                DB::table('expense_recurring_adjustments')
                    ->where('id', $adjustment->id)
                    ->update([
                        'payment_day' => (int) date('j', strtotime($adjustment->start_date)),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_recurring_adjustments', function (Blueprint $table) {
            $table->dropColumn('payment_day');
        });
    }
};
