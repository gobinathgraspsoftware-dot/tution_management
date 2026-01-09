<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Seeds default expense categories as per PDF documentation
     */
    public function up(): void
    {
        // Default categories from PDF documentation
        $categories = [
            ['name' => 'Staff Salary', 'description' => 'Monthly staff salary payments', 'status' => 'active'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, internet bills', 'status' => 'active'],
            ['name' => 'Rental', 'description' => 'Office/premises rental payments', 'status' => 'active'],
            ['name' => 'Teaching Materials', 'description' => 'Books, worksheets, educational supplies', 'status' => 'active'],
            ['name' => 'Marketing', 'description' => 'Advertising, promotions, branding', 'status' => 'active'],
            ['name' => 'Stationery', 'description' => 'Office supplies, papers, pens', 'status' => 'active'],
            ['name' => 'Transport', 'description' => 'Travel, fuel, vehicle maintenance', 'status' => 'active'],
            ['name' => 'Miscellaneous', 'description' => 'Other uncategorized expenses', 'status' => 'active'],
        ];

        foreach ($categories as $category) {
            // Only insert if category doesn't exist
            $exists = DB::table('expense_categories')
                ->where('name', $category['name'])
                ->exists();

            if (!$exists) {
                DB::table('expense_categories')->insert(array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove seeded categories
        DB::table('expense_categories')
            ->whereIn('name', [
                'Staff Salary',
                'Utilities', 
                'Rental',
                'Teaching Materials',
                'Marketing',
                'Stationery',
                'Transport',
                'Miscellaneous',
            ])
            ->delete();
    }
};
