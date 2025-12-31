<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SocsoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * SOCSO (PERKESO) Contribution Rates based on Malaysia statutory requirements
     * This is the full rate for employees below 60 years old
     */
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            ['wage_limit' => '0.00-30.00', 'from_amount' => 0.00, 'to_amount' => 30.00, 'employee_contribution' => 0.10, 'employer_contribution' => 0.40, 'total_contribution' => 0.50],
            ['wage_limit' => '30.01-50.00', 'from_amount' => 30.01, 'to_amount' => 50.00, 'employee_contribution' => 0.20, 'employer_contribution' => 0.70, 'total_contribution' => 0.90],
            ['wage_limit' => '50.01-70.00', 'from_amount' => 50.01, 'to_amount' => 70.00, 'employee_contribution' => 0.30, 'employer_contribution' => 1.10, 'total_contribution' => 1.40],
            ['wage_limit' => '70.01-100.00', 'from_amount' => 70.01, 'to_amount' => 100.00, 'employee_contribution' => 0.40, 'employer_contribution' => 1.50, 'total_contribution' => 1.90],
            ['wage_limit' => '100.01-140.00', 'from_amount' => 100.01, 'to_amount' => 140.00, 'employee_contribution' => 0.60, 'employer_contribution' => 2.10, 'total_contribution' => 2.70],
            ['wage_limit' => '140.01-200.00', 'from_amount' => 140.01, 'to_amount' => 200.00, 'employee_contribution' => 0.85, 'employer_contribution' => 2.95, 'total_contribution' => 3.80],
            ['wage_limit' => '200.01-300.00', 'from_amount' => 200.01, 'to_amount' => 300.00, 'employee_contribution' => 1.25, 'employer_contribution' => 4.35, 'total_contribution' => 5.60],
            ['wage_limit' => '300.01-400.00', 'from_amount' => 300.01, 'to_amount' => 400.00, 'employee_contribution' => 1.75, 'employer_contribution' => 6.15, 'total_contribution' => 7.90],
            ['wage_limit' => '400.01-500.00', 'from_amount' => 400.01, 'to_amount' => 500.00, 'employee_contribution' => 2.25, 'employer_contribution' => 7.85, 'total_contribution' => 10.10],
            ['wage_limit' => '500.01-600.00', 'from_amount' => 500.01, 'to_amount' => 600.00, 'employee_contribution' => 2.75, 'employer_contribution' => 9.65, 'total_contribution' => 12.40],
            ['wage_limit' => '600.01-700.00', 'from_amount' => 600.01, 'to_amount' => 700.00, 'employee_contribution' => 3.25, 'employer_contribution' => 11.35, 'total_contribution' => 14.60],
            ['wage_limit' => '700.01-800.00', 'from_amount' => 700.01, 'to_amount' => 800.00, 'employee_contribution' => 3.75, 'employer_contribution' => 13.15, 'total_contribution' => 16.90],
            ['wage_limit' => '800.01-900.00', 'from_amount' => 800.01, 'to_amount' => 900.00, 'employee_contribution' => 4.25, 'employer_contribution' => 14.85, 'total_contribution' => 19.10],
            ['wage_limit' => '900.01-1000.00', 'from_amount' => 900.01, 'to_amount' => 1000.00, 'employee_contribution' => 4.75, 'employer_contribution' => 16.65, 'total_contribution' => 21.40],
            ['wage_limit' => '1000.01-1100.00', 'from_amount' => 1000.01, 'to_amount' => 1100.00, 'employee_contribution' => 5.25, 'employer_contribution' => 18.35, 'total_contribution' => 23.60],
            ['wage_limit' => '1100.01-1200.00', 'from_amount' => 1100.01, 'to_amount' => 1200.00, 'employee_contribution' => 5.75, 'employer_contribution' => 20.15, 'total_contribution' => 25.90],
            ['wage_limit' => '1200.01-1300.00', 'from_amount' => 1200.01, 'to_amount' => 1300.00, 'employee_contribution' => 6.25, 'employer_contribution' => 21.85, 'total_contribution' => 28.10],
            ['wage_limit' => '1300.01-1400.00', 'from_amount' => 1300.01, 'to_amount' => 1400.00, 'employee_contribution' => 6.75, 'employer_contribution' => 23.65, 'total_contribution' => 30.40],
            ['wage_limit' => '1400.01-1500.00', 'from_amount' => 1400.01, 'to_amount' => 1500.00, 'employee_contribution' => 7.25, 'employer_contribution' => 25.35, 'total_contribution' => 32.60],
            ['wage_limit' => '1500.01-1600.00', 'from_amount' => 1500.01, 'to_amount' => 1600.00, 'employee_contribution' => 7.75, 'employer_contribution' => 27.15, 'total_contribution' => 34.90],
            ['wage_limit' => '1600.01-1700.00', 'from_amount' => 1600.01, 'to_amount' => 1700.00, 'employee_contribution' => 8.25, 'employer_contribution' => 28.85, 'total_contribution' => 37.10],
            ['wage_limit' => '1700.01-1800.00', 'from_amount' => 1700.01, 'to_amount' => 1800.00, 'employee_contribution' => 8.75, 'employer_contribution' => 30.65, 'total_contribution' => 39.40],
            ['wage_limit' => '1800.01-1900.00', 'from_amount' => 1800.01, 'to_amount' => 1900.00, 'employee_contribution' => 9.25, 'employer_contribution' => 32.35, 'total_contribution' => 41.60],
            ['wage_limit' => '1900.01-2000.00', 'from_amount' => 1900.01, 'to_amount' => 2000.00, 'employee_contribution' => 9.75, 'employer_contribution' => 34.15, 'total_contribution' => 43.90],
            ['wage_limit' => '2000.01-2100.00', 'from_amount' => 2000.01, 'to_amount' => 2100.00, 'employee_contribution' => 10.25, 'employer_contribution' => 35.85, 'total_contribution' => 46.10],
            ['wage_limit' => '2100.01-2200.00', 'from_amount' => 2100.01, 'to_amount' => 2200.00, 'employee_contribution' => 10.75, 'employer_contribution' => 37.65, 'total_contribution' => 48.40],
            ['wage_limit' => '2200.01-2300.00', 'from_amount' => 2200.01, 'to_amount' => 2300.00, 'employee_contribution' => 11.25, 'employer_contribution' => 39.35, 'total_contribution' => 50.60],
            ['wage_limit' => '2300.01-2400.00', 'from_amount' => 2300.01, 'to_amount' => 2400.00, 'employee_contribution' => 11.75, 'employer_contribution' => 41.15, 'total_contribution' => 52.90],
            ['wage_limit' => '2400.01-2500.00', 'from_amount' => 2400.01, 'to_amount' => 2500.00, 'employee_contribution' => 12.25, 'employer_contribution' => 42.85, 'total_contribution' => 55.10],
            ['wage_limit' => '2500.01-2600.00', 'from_amount' => 2500.01, 'to_amount' => 2600.00, 'employee_contribution' => 12.75, 'employer_contribution' => 44.65, 'total_contribution' => 57.40],
            ['wage_limit' => '2600.01-2700.00', 'from_amount' => 2600.01, 'to_amount' => 2700.00, 'employee_contribution' => 13.25, 'employer_contribution' => 46.35, 'total_contribution' => 59.60],
            ['wage_limit' => '2700.01-2800.00', 'from_amount' => 2700.01, 'to_amount' => 2800.00, 'employee_contribution' => 13.75, 'employer_contribution' => 48.15, 'total_contribution' => 61.90],
            ['wage_limit' => '2800.01-2900.00', 'from_amount' => 2800.01, 'to_amount' => 2900.00, 'employee_contribution' => 14.25, 'employer_contribution' => 49.85, 'total_contribution' => 64.10],
            ['wage_limit' => '2900.01-3000.00', 'from_amount' => 2900.01, 'to_amount' => 3000.00, 'employee_contribution' => 14.75, 'employer_contribution' => 51.65, 'total_contribution' => 66.40],
            ['wage_limit' => '3000.01-3100.00', 'from_amount' => 3000.01, 'to_amount' => 3100.00, 'employee_contribution' => 15.25, 'employer_contribution' => 53.35, 'total_contribution' => 68.60],
            ['wage_limit' => '3100.01-3200.00', 'from_amount' => 3100.01, 'to_amount' => 3200.00, 'employee_contribution' => 15.75, 'employer_contribution' => 55.15, 'total_contribution' => 70.90],
            ['wage_limit' => '3200.01-3300.00', 'from_amount' => 3200.01, 'to_amount' => 3300.00, 'employee_contribution' => 16.25, 'employer_contribution' => 56.85, 'total_contribution' => 73.10],
            ['wage_limit' => '3300.01-3400.00', 'from_amount' => 3300.01, 'to_amount' => 3400.00, 'employee_contribution' => 16.75, 'employer_contribution' => 58.65, 'total_contribution' => 75.40],
            ['wage_limit' => '3400.01-3500.00', 'from_amount' => 3400.01, 'to_amount' => 3500.00, 'employee_contribution' => 17.25, 'employer_contribution' => 60.35, 'total_contribution' => 77.60],
            ['wage_limit' => '3500.01-3600.00', 'from_amount' => 3500.01, 'to_amount' => 3600.00, 'employee_contribution' => 17.75, 'employer_contribution' => 62.15, 'total_contribution' => 79.90],
            ['wage_limit' => '3600.01-3700.00', 'from_amount' => 3600.01, 'to_amount' => 3700.00, 'employee_contribution' => 18.25, 'employer_contribution' => 63.85, 'total_contribution' => 82.10],
            ['wage_limit' => '3700.01-3800.00', 'from_amount' => 3700.01, 'to_amount' => 3800.00, 'employee_contribution' => 18.75, 'employer_contribution' => 65.65, 'total_contribution' => 84.40],
            ['wage_limit' => '3800.01-3900.00', 'from_amount' => 3800.01, 'to_amount' => 3900.00, 'employee_contribution' => 19.25, 'employer_contribution' => 67.35, 'total_contribution' => 86.60],
            ['wage_limit' => '3900.01-4000.00', 'from_amount' => 3900.01, 'to_amount' => 4000.00, 'employee_contribution' => 19.75, 'employer_contribution' => 69.15, 'total_contribution' => 88.90],
            ['wage_limit' => '4000.01-4100.00', 'from_amount' => 4000.01, 'to_amount' => 4100.00, 'employee_contribution' => 20.25, 'employer_contribution' => 70.85, 'total_contribution' => 91.10],
            ['wage_limit' => '4100.01-4200.00', 'from_amount' => 4100.01, 'to_amount' => 4200.00, 'employee_contribution' => 20.75, 'employer_contribution' => 72.65, 'total_contribution' => 93.40],
            ['wage_limit' => '4200.01-4300.00', 'from_amount' => 4200.01, 'to_amount' => 4300.00, 'employee_contribution' => 21.25, 'employer_contribution' => 74.35, 'total_contribution' => 95.60],
            ['wage_limit' => '4300.01-4400.00', 'from_amount' => 4300.01, 'to_amount' => 4400.00, 'employee_contribution' => 21.75, 'employer_contribution' => 76.15, 'total_contribution' => 97.90],
            ['wage_limit' => '4400.01-4500.00', 'from_amount' => 4400.01, 'to_amount' => 4500.00, 'employee_contribution' => 22.25, 'employer_contribution' => 77.85, 'total_contribution' => 100.10],
            ['wage_limit' => '4500.01-4600.00', 'from_amount' => 4500.01, 'to_amount' => 4600.00, 'employee_contribution' => 22.75, 'employer_contribution' => 79.65, 'total_contribution' => 102.40],
            ['wage_limit' => '4600.01-4700.00', 'from_amount' => 4600.01, 'to_amount' => 4700.00, 'employee_contribution' => 23.25, 'employer_contribution' => 81.35, 'total_contribution' => 104.60],
            ['wage_limit' => '4700.01-4800.00', 'from_amount' => 4700.01, 'to_amount' => 4800.00, 'employee_contribution' => 23.75, 'employer_contribution' => 83.15, 'total_contribution' => 106.90],
            ['wage_limit' => '4800.01-4900.00', 'from_amount' => 4800.01, 'to_amount' => 4900.00, 'employee_contribution' => 24.25, 'employer_contribution' => 84.85, 'total_contribution' => 109.10],
            ['wage_limit' => '4900.01-5000.00', 'from_amount' => 4900.01, 'to_amount' => 5000.00, 'employee_contribution' => 24.75, 'employer_contribution' => 86.65, 'total_contribution' => 111.40],
            ['wage_limit' => '5000.01-90000.00', 'from_amount' => 5000.01, 'to_amount' => 90000.00, 'employee_contribution' => 24.75, 'employer_contribution' => 86.65, 'total_contribution' => 111.40],
        ];

        foreach ($data as $item) {
            DB::table('socso')->insert([
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
