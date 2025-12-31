<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SocsoInsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * SOCSO Insurance Only Contribution Rates (Employment Injury Scheme only)
     * This is for employees above 60 years old or foreign workers
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['wage_limit' => '0.00-30.00', 'from_amount' => 0.00, 'to_amount' => 30.00, 'employee_contribution' => 0.05, 'employer_contribution' => 0.05, 'total_contribution' => 0.10],
            ['wage_limit' => '30.01-50.00', 'from_amount' => 30.01, 'to_amount' => 50.00, 'employee_contribution' => 0.10, 'employer_contribution' => 0.10, 'total_contribution' => 0.20],
            ['wage_limit' => '50.01-70.00', 'from_amount' => 50.01, 'to_amount' => 70.00, 'employee_contribution' => 0.15, 'employer_contribution' => 0.15, 'total_contribution' => 0.30],
            ['wage_limit' => '70.01-100.00', 'from_amount' => 70.01, 'to_amount' => 100.00, 'employee_contribution' => 0.20, 'employer_contribution' => 0.20, 'total_contribution' => 0.40],
            ['wage_limit' => '100.01-140.00', 'from_amount' => 100.01, 'to_amount' => 140.00, 'employee_contribution' => 0.25, 'employer_contribution' => 0.25, 'total_contribution' => 0.50],
            ['wage_limit' => '140.01-200.00', 'from_amount' => 140.01, 'to_amount' => 200.00, 'employee_contribution' => 0.35, 'employer_contribution' => 0.35, 'total_contribution' => 0.70],
            ['wage_limit' => '200.01-300.00', 'from_amount' => 200.01, 'to_amount' => 300.00, 'employee_contribution' => 0.50, 'employer_contribution' => 0.50, 'total_contribution' => 1.00],
            ['wage_limit' => '300.01-400.00', 'from_amount' => 300.01, 'to_amount' => 400.00, 'employee_contribution' => 0.70, 'employer_contribution' => 0.70, 'total_contribution' => 1.40],
            ['wage_limit' => '400.01-500.00', 'from_amount' => 400.01, 'to_amount' => 500.00, 'employee_contribution' => 0.90, 'employer_contribution' => 0.90, 'total_contribution' => 1.80],
            ['wage_limit' => '500.01-600.00', 'from_amount' => 500.01, 'to_amount' => 600.00, 'employee_contribution' => 1.10, 'employer_contribution' => 1.10, 'total_contribution' => 2.20],
            ['wage_limit' => '600.01-700.00', 'from_amount' => 600.01, 'to_amount' => 700.00, 'employee_contribution' => 1.30, 'employer_contribution' => 1.30, 'total_contribution' => 2.60],
            ['wage_limit' => '700.01-800.00', 'from_amount' => 700.01, 'to_amount' => 800.00, 'employee_contribution' => 1.50, 'employer_contribution' => 1.50, 'total_contribution' => 3.00],
            ['wage_limit' => '800.01-900.00', 'from_amount' => 800.01, 'to_amount' => 900.00, 'employee_contribution' => 1.70, 'employer_contribution' => 1.70, 'total_contribution' => 3.40],
            ['wage_limit' => '900.01-1000.00', 'from_amount' => 900.01, 'to_amount' => 1000.00, 'employee_contribution' => 1.90, 'employer_contribution' => 1.90, 'total_contribution' => 3.80],
            ['wage_limit' => '1000.01-1100.00', 'from_amount' => 1000.01, 'to_amount' => 1100.00, 'employee_contribution' => 2.10, 'employer_contribution' => 2.10, 'total_contribution' => 4.20],
            ['wage_limit' => '1100.01-1200.00', 'from_amount' => 1100.01, 'to_amount' => 1200.00, 'employee_contribution' => 2.30, 'employer_contribution' => 2.30, 'total_contribution' => 4.60],
            ['wage_limit' => '1200.01-1300.00', 'from_amount' => 1200.01, 'to_amount' => 1300.00, 'employee_contribution' => 2.50, 'employer_contribution' => 2.50, 'total_contribution' => 5.00],
            ['wage_limit' => '1300.01-1400.00', 'from_amount' => 1300.01, 'to_amount' => 1400.00, 'employee_contribution' => 2.70, 'employer_contribution' => 2.70, 'total_contribution' => 5.40],
            ['wage_limit' => '1400.01-1500.00', 'from_amount' => 1400.01, 'to_amount' => 1500.00, 'employee_contribution' => 2.90, 'employer_contribution' => 2.90, 'total_contribution' => 5.80],
            ['wage_limit' => '1500.01-1600.00', 'from_amount' => 1500.01, 'to_amount' => 1600.00, 'employee_contribution' => 3.10, 'employer_contribution' => 3.10, 'total_contribution' => 6.20],
            ['wage_limit' => '1600.01-1700.00', 'from_amount' => 1600.01, 'to_amount' => 1700.00, 'employee_contribution' => 3.30, 'employer_contribution' => 3.30, 'total_contribution' => 6.60],
            ['wage_limit' => '1700.01-1800.00', 'from_amount' => 1700.01, 'to_amount' => 1800.00, 'employee_contribution' => 3.50, 'employer_contribution' => 3.50, 'total_contribution' => 7.00],
            ['wage_limit' => '1800.01-1900.00', 'from_amount' => 1800.01, 'to_amount' => 1900.00, 'employee_contribution' => 3.70, 'employer_contribution' => 3.70, 'total_contribution' => 7.40],
            ['wage_limit' => '1900.01-2000.00', 'from_amount' => 1900.01, 'to_amount' => 2000.00, 'employee_contribution' => 3.90, 'employer_contribution' => 3.90, 'total_contribution' => 7.80],
            ['wage_limit' => '2000.01-2100.00', 'from_amount' => 2000.01, 'to_amount' => 2100.00, 'employee_contribution' => 4.10, 'employer_contribution' => 4.10, 'total_contribution' => 8.20],
            ['wage_limit' => '2100.01-2200.00', 'from_amount' => 2100.01, 'to_amount' => 2200.00, 'employee_contribution' => 4.30, 'employer_contribution' => 4.30, 'total_contribution' => 8.60],
            ['wage_limit' => '2200.01-2300.00', 'from_amount' => 2200.01, 'to_amount' => 2300.00, 'employee_contribution' => 4.50, 'employer_contribution' => 4.50, 'total_contribution' => 9.00],
            ['wage_limit' => '2300.01-2400.00', 'from_amount' => 2300.01, 'to_amount' => 2400.00, 'employee_contribution' => 4.70, 'employer_contribution' => 4.70, 'total_contribution' => 9.40],
            ['wage_limit' => '2400.01-2500.00', 'from_amount' => 2400.01, 'to_amount' => 2500.00, 'employee_contribution' => 4.90, 'employer_contribution' => 4.90, 'total_contribution' => 9.80],
            ['wage_limit' => '2500.01-2600.00', 'from_amount' => 2500.01, 'to_amount' => 2600.00, 'employee_contribution' => 5.10, 'employer_contribution' => 5.10, 'total_contribution' => 10.20],
            ['wage_limit' => '2600.01-2700.00', 'from_amount' => 2600.01, 'to_amount' => 2700.00, 'employee_contribution' => 5.30, 'employer_contribution' => 5.30, 'total_contribution' => 10.60],
            ['wage_limit' => '2700.01-2800.00', 'from_amount' => 2700.01, 'to_amount' => 2800.00, 'employee_contribution' => 5.50, 'employer_contribution' => 5.50, 'total_contribution' => 11.00],
            ['wage_limit' => '2800.01-2900.00', 'from_amount' => 2800.01, 'to_amount' => 2900.00, 'employee_contribution' => 5.70, 'employer_contribution' => 5.70, 'total_contribution' => 11.40],
            ['wage_limit' => '2900.01-3000.00', 'from_amount' => 2900.01, 'to_amount' => 3000.00, 'employee_contribution' => 5.90, 'employer_contribution' => 5.90, 'total_contribution' => 11.80],
            ['wage_limit' => '3000.01-3100.00', 'from_amount' => 3000.01, 'to_amount' => 3100.00, 'employee_contribution' => 6.10, 'employer_contribution' => 6.10, 'total_contribution' => 12.20],
            ['wage_limit' => '3100.01-3200.00', 'from_amount' => 3100.01, 'to_amount' => 3200.00, 'employee_contribution' => 6.30, 'employer_contribution' => 6.30, 'total_contribution' => 12.60],
            ['wage_limit' => '3200.01-3300.00', 'from_amount' => 3200.01, 'to_amount' => 3300.00, 'employee_contribution' => 6.50, 'employer_contribution' => 6.50, 'total_contribution' => 13.00],
            ['wage_limit' => '3300.01-3400.00', 'from_amount' => 3300.01, 'to_amount' => 3400.00, 'employee_contribution' => 6.70, 'employer_contribution' => 6.70, 'total_contribution' => 13.40],
            ['wage_limit' => '3400.01-3500.00', 'from_amount' => 3400.01, 'to_amount' => 3500.00, 'employee_contribution' => 6.90, 'employer_contribution' => 6.90, 'total_contribution' => 13.80],
            ['wage_limit' => '3500.01-3600.00', 'from_amount' => 3500.01, 'to_amount' => 3600.00, 'employee_contribution' => 7.10, 'employer_contribution' => 7.10, 'total_contribution' => 14.20],
            ['wage_limit' => '3600.01-3700.00', 'from_amount' => 3600.01, 'to_amount' => 3700.00, 'employee_contribution' => 7.30, 'employer_contribution' => 7.30, 'total_contribution' => 14.60],
            ['wage_limit' => '3700.01-3800.00', 'from_amount' => 3700.01, 'to_amount' => 3800.00, 'employee_contribution' => 7.50, 'employer_contribution' => 7.50, 'total_contribution' => 15.00],
            ['wage_limit' => '3800.01-3900.00', 'from_amount' => 3800.01, 'to_amount' => 3900.00, 'employee_contribution' => 7.70, 'employer_contribution' => 7.70, 'total_contribution' => 15.40],
            ['wage_limit' => '3900.01-4000.00', 'from_amount' => 3900.01, 'to_amount' => 4000.00, 'employee_contribution' => 7.90, 'employer_contribution' => 7.90, 'total_contribution' => 15.80],
            ['wage_limit' => '4000.01-4100.00', 'from_amount' => 4000.01, 'to_amount' => 4100.00, 'employee_contribution' => 8.10, 'employer_contribution' => 8.10, 'total_contribution' => 16.20],
            ['wage_limit' => '4100.01-4200.00', 'from_amount' => 4100.01, 'to_amount' => 4200.00, 'employee_contribution' => 8.30, 'employer_contribution' => 8.30, 'total_contribution' => 16.60],
            ['wage_limit' => '4200.01-4300.00', 'from_amount' => 4200.01, 'to_amount' => 4300.00, 'employee_contribution' => 8.50, 'employer_contribution' => 8.50, 'total_contribution' => 17.00],
            ['wage_limit' => '4300.01-4400.00', 'from_amount' => 4300.01, 'to_amount' => 4400.00, 'employee_contribution' => 8.70, 'employer_contribution' => 8.70, 'total_contribution' => 17.40],
            ['wage_limit' => '4400.01-4500.00', 'from_amount' => 4400.01, 'to_amount' => 4500.00, 'employee_contribution' => 8.90, 'employer_contribution' => 8.90, 'total_contribution' => 17.80],
            ['wage_limit' => '4500.01-4600.00', 'from_amount' => 4500.01, 'to_amount' => 4600.00, 'employee_contribution' => 9.10, 'employer_contribution' => 9.10, 'total_contribution' => 18.20],
            ['wage_limit' => '4600.01-4700.00', 'from_amount' => 4600.01, 'to_amount' => 4700.00, 'employee_contribution' => 9.30, 'employer_contribution' => 9.30, 'total_contribution' => 18.60],
            ['wage_limit' => '4700.01-4800.00', 'from_amount' => 4700.01, 'to_amount' => 4800.00, 'employee_contribution' => 9.50, 'employer_contribution' => 9.50, 'total_contribution' => 19.00],
            ['wage_limit' => '4800.01-4900.00', 'from_amount' => 4800.01, 'to_amount' => 4900.00, 'employee_contribution' => 9.70, 'employer_contribution' => 9.70, 'total_contribution' => 19.40],
            ['wage_limit' => '4900.01-5000.00', 'from_amount' => 4900.01, 'to_amount' => 5000.00, 'employee_contribution' => 9.90, 'employer_contribution' => 9.90, 'total_contribution' => 19.80],
            ['wage_limit' => '5000.01-90000.00', 'from_amount' => 5000.01, 'to_amount' => 90000.00, 'employee_contribution' => 9.90, 'employer_contribution' => 9.90, 'total_contribution' => 19.80],
        ];

        foreach ($data as $item) {
            DB::table('socso_insurance')->insert([
                'wage_limit' => $item['wage_limit'],
                'from_amount' => $item['from_amount'],
                'to_amount' => $item['to_amount'],
                'employee_contribution' => $item['employee_contribution'],
                'employer_contribution' => $item['employer_contribution'],
                'total_contribution' => $item['total_contribution'],
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
