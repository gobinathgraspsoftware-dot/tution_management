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
        Schema::create('socso', function (Blueprint $table) {
            $table->id();
            $table->string('wage_limit')->nullable();
            $table->decimal('from_amount', 10, 2)->default(0.00);
            $table->decimal('to_amount', 10, 2)->default(0.00);
            $table->decimal('employee_contribution', 10, 2)->default(0.00);
            $table->decimal('employer_contribution', 10, 2)->default(0.00);
            $table->decimal('total_contribution', 10, 2)->default(0.00);
            $table->tinyInteger('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->timestamps();

            // Indexes for faster lookups
            $table->index(['from_amount', 'to_amount']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socso');
    }
};
