<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds voucher number field as per PDF documentation
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Add voucher number field after id (as per PDF: Mandatory field)
            $table->string('voucher_number', 50)->nullable()->unique()->after('id');
        });

        // Generate voucher numbers for existing records
        $expenses = DB::table('expenses')->orderBy('id')->get();
        foreach ($expenses as $expense) {
            $yearMonth = date('Ym', strtotime($expense->expense_date ?? $expense->created_at));
            $voucherNumber = sprintf('EXP-%s-%04d', $yearMonth, $expense->id);
            
            DB::table('expenses')
                ->where('id', $expense->id)
                ->update(['voucher_number' => $voucherNumber]);
        }

        // Make voucher_number not nullable after populating existing records
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('voucher_number', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('voucher_number');
        });
    }
};
