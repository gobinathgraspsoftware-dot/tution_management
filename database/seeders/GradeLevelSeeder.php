<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeLevelSeeder extends Seeder
{
    /**
     * Predefined grade levels for Arena Matriks Edu Group.
     * Uses updateOrInsert to be idempotent (safe to re-run).
     */
    public function run(): void
    {
        $gradeLevels = [
            'Standard 1',
            'Standard 2',
            'Standard 3',
            'Standard 4',
            'Standard 5',
            'Standard 6',
            'Form 1',
            'Form 2',
            'Form 3',
            'Form 4',
            'Form 5',
            'Form 6',
            'Pre-University',
        ];

        $now = Carbon::now();

        foreach ($gradeLevels as $name) {
            DB::table('grade_levels')->updateOrInsert(
                ['name' => $name],
                ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $this->command->info('GradeLevelSeeder: ' . count($gradeLevels) . ' grade levels seeded.');
    }
}
