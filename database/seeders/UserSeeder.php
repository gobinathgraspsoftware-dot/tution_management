<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Parents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating users...');
        $this->command->info('');

        // ===================================================================
        // SUPER ADMIN USER
        // ===================================================================
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@arenamatriks.com'],
            [
                'name' => 'Super Administrator',
                'phone' => '0123456789',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'last_login_at' => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');
        $this->command->info('✓ Super Admin created: superadmin@arenamatriks.com');

        // ===================================================================
        // ADMIN USERS
        // ===================================================================
        $admin1 = User::firstOrCreate(
            ['email' => 'admin@arenamatriks.com'],
            [
                'name' => 'Janahan Arumugam',
                'phone' => '0146488869',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'last_login_at' => now(),
            ]
        );
        $admin1->assignRole('admin');

        // Create Staff profile for Admin
        Staff::firstOrCreate(
            ['user_id' => $admin1->id],
            [
                'staff_id' => 'ADM-' . str_pad($admin1->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '850101145001',
                'position' => 'Centre Manager',
                'department' => 'Administration',
                'join_date' => '2020-01-01',
                'address' => 'Wisma Arena Matriks, No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor',
                'emergency_contact' => 'Emergency Contact',
                'emergency_phone' => '0123456788',
            ]
        );
        $this->command->info('✓ Admin created: admin@arenamatriks.com');

        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@arenamatriks.com'],
            [
                'name' => 'Siti Aminah',
                'phone' => '0123456788',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $admin2->assignRole('admin');

        Staff::firstOrCreate(
            ['user_id' => $admin2->id],
            [
                'staff_id' => 'ADM-' . str_pad($admin2->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '880215145002',
                'position' => 'Operations Manager',
                'department' => 'Administration',
                'join_date' => '2021-03-15',
                'address' => 'Shah Alam, Selangor',
            ]
        );
        $this->command->info('✓ Admin 2 created: admin2@arenamatriks.com');

        // ===================================================================
        // STAFF USERS
        // ===================================================================
        $staff1 = User::firstOrCreate(
            ['email' => 'staff@arenamatriks.com'],
            [
                'name' => 'Ahmad Razak',
                'phone' => '0112345678',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $staff1->assignRole('staff');

        Staff::firstOrCreate(
            ['user_id' => $staff1->id],
            [
                'staff_id' => 'STF-' . str_pad($staff1->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '900515145003',
                'position' => 'Front Desk Officer',
                'department' => 'Operations',
                'join_date' => '2022-06-01',
                'address' => 'Klang, Selangor',
                'emergency_contact' => 'Fatimah',
                'emergency_phone' => '0123456787',
            ]
        );
        $this->command->info('✓ Staff 1 created: staff@arenamatriks.com');

        $staff2 = User::firstOrCreate(
            ['email' => 'staff2@arenamatriks.com'],
            [
                'name' => 'Nurul Huda',
                'phone' => '0112345679',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $staff2->assignRole('staff');

        Staff::firstOrCreate(
            ['user_id' => $staff2->id],
            [
                'staff_id' => 'STF-' . str_pad($staff2->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '920820145004',
                'position' => 'Administrative Assistant',
                'department' => 'Operations',
                'join_date' => '2023-01-15',
                'address' => 'Subang Jaya, Selangor',
            ]
        );
        $this->command->info('✓ Staff 2 created: staff2@arenamatriks.com');

        $staff3 = User::firstOrCreate(
            ['email' => 'cashier@arenamatriks.com'],
            [
                'name' => 'Lee Wei Ming',
                'phone' => '0112345680',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $staff3->assignRole('staff');

        Staff::firstOrCreate(
            ['user_id' => $staff3->id],
            [
                'staff_id' => 'STF-' . str_pad($staff3->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '950312145005',
                'position' => 'Cafeteria Cashier',
                'department' => 'Cafeteria',
                'join_date' => '2023-06-01',
                'address' => 'Petaling Jaya, Selangor',
            ]
        );
        $this->command->info('✓ Cashier Staff created: cashier@arenamatriks.com');

        // ===================================================================
        // TEACHER USERS
        // ===================================================================
        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher@arenamatriks.com'],
            [
                'name' => 'Dr. Rajesh Kumar',
                'phone' => '0132345678',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $teacher1->assignRole('teacher');

        Teacher::firstOrCreate(
            ['user_id' => $teacher1->id],
            [
                'teacher_id' => 'TCH-' . str_pad($teacher1->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '800101145006',
                'qualification' => 'PhD in Mathematics, University of Malaya',
                'experience_years' => 15,
                'specialization' => 'Mathematics, Additional Mathematics',
                'bio' => 'Experienced mathematics teacher with 15 years of teaching SPM students.',
                'join_date' => '2019-01-01',
                'employment_type' => 'full_time',
                'pay_type' => 'monthly',
                'monthly_salary' => 5000.00,
                'address' => 'Bangsar, Kuala Lumpur',
                'bank_name' => 'Maybank',
                'bank_account' => '1234567890',
                'status' => 'active',
            ]
        );
        $this->command->info('✓ Teacher 1 created: teacher@arenamatriks.com');

        $teacher2 = User::firstOrCreate(
            ['email' => 'teacher2@arenamatriks.com'],
            [
                'name' => 'Puan Noraini Binti Hassan',
                'phone' => '0132345679',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $teacher2->assignRole('teacher');

        Teacher::firstOrCreate(
            ['user_id' => $teacher2->id],
            [
                'teacher_id' => 'TCH-' . str_pad($teacher2->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '820515145007',
                'qualification' => 'Masters in Malay Literature, UKM',
                'experience_years' => 12,
                'specialization' => 'Bahasa Melayu',
                'bio' => 'Dedicated Bahasa Melayu teacher specializing in SPM literature and essay writing.',
                'join_date' => '2020-06-01',
                'employment_type' => 'full_time',
                'pay_type' => 'monthly',
                'monthly_salary' => 4500.00,
                'address' => 'Shah Alam, Selangor',
                'bank_name' => 'CIMB',
                'bank_account' => '2345678901',
                'status' => 'active',
            ]
        );
        $this->command->info('✓ Teacher 2 created: teacher2@arenamatriks.com');

        $teacher3 = User::firstOrCreate(
            ['email' => 'teacher3@arenamatriks.com'],
            [
                'name' => 'Mr. James Tan',
                'phone' => '0132345680',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $teacher3->assignRole('teacher');

        Teacher::firstOrCreate(
            ['user_id' => $teacher3->id],
            [
                'teacher_id' => 'TCH-' . str_pad($teacher3->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '850820145008',
                'qualification' => 'BSc in Physics, UM',
                'experience_years' => 8,
                'specialization' => 'Physics, Science',
                'bio' => 'Passionate physics teacher making complex concepts easy to understand.',
                'join_date' => '2021-01-15',
                'employment_type' => 'part_time',
                'pay_type' => 'hourly',
                'hourly_rate' => 80.00,
                'address' => 'Damansara, Selangor',
                'bank_name' => 'Public Bank',
                'bank_account' => '3456789012',
                'status' => 'active',
            ]
        );
        $this->command->info('✓ Teacher 3 created: teacher3@arenamatriks.com');

        $teacher4 = User::firstOrCreate(
            ['email' => 'teacher4@arenamatriks.com'],
            [
                'name' => 'Cik Aishah Binti Abdullah',
                'phone' => '0132345681',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $teacher4->assignRole('teacher');

        Teacher::firstOrCreate(
            ['user_id' => $teacher4->id],
            [
                'teacher_id' => 'TCH-' . str_pad($teacher4->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '880910145009',
                'qualification' => 'BA in English Literature, IIUM',
                'experience_years' => 6,
                'specialization' => 'English',
                'bio' => 'Creative English teacher focusing on communication and writing skills.',
                'join_date' => '2022-03-01',
                'employment_type' => 'part_time',
                'pay_type' => 'per_class',
                'per_class_rate' => 150.00,
                'address' => 'Klang, Selangor',
                'bank_name' => 'RHB',
                'bank_account' => '4567890123',
                'status' => 'active',
            ]
        );
        $this->command->info('✓ Teacher 4 created: teacher4@arenamatriks.com');

        // ===================================================================
        // PARENT USERS
        // ===================================================================
        $parent1User = User::firstOrCreate(
            ['email' => 'parent@arenamatriks.com'],
            [
                'name' => 'Encik Mohd Faizal',
                'phone' => '0142345678',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $parent1User->assignRole('parent');

        $parent1 = Parents::firstOrCreate(
            ['user_id' => $parent1User->id],
            [
                'parent_id' => 'PAR-' . str_pad($parent1User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '780505145010',
                'occupation' => 'Engineer',
                'address' => '123, Jalan Mawar, Taman Bunga',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postcode' => '40400',
                'relationship' => 'father',
                'whatsapp_number' => '60142345678',
                'emergency_contact' => 'Puan Siti',
                'emergency_phone' => '0142345679',
                'notification_preference' => json_encode(['whatsapp' => true, 'email' => true, 'sms' => false]),
            ]
        );
        $this->command->info('✓ Parent 1 created: parent@arenamatriks.com');

        $parent2User = User::firstOrCreate(
            ['email' => 'parent2@arenamatriks.com'],
            [
                'name' => 'Puan Siti Nurhaliza',
                'phone' => '0142345679',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $parent2User->assignRole('parent');

        $parent2 = Parents::firstOrCreate(
            ['user_id' => $parent2User->id],
            [
                'parent_id' => 'PAR-' . str_pad($parent2User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '800815145011',
                'occupation' => 'Teacher',
                'address' => '456, Jalan Melati, Taman Indah',
                'city' => 'Klang',
                'state' => 'Selangor',
                'postcode' => '41200',
                'relationship' => 'mother',
                'whatsapp_number' => '60142345679',
                'notification_preference' => json_encode(['whatsapp' => true, 'email' => true, 'sms' => true]),
            ]
        );
        $this->command->info('✓ Parent 2 created: parent2@arenamatriks.com');

        $parent3User = User::firstOrCreate(
            ['email' => 'parent3@arenamatriks.com'],
            [
                'name' => 'Mr. Lim Ah Kow',
                'phone' => '0142345680',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $parent3User->assignRole('parent');

        $parent3 = Parents::firstOrCreate(
            ['user_id' => $parent3User->id],
            [
                'parent_id' => 'PAR-' . str_pad($parent3User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '750220145012',
                'occupation' => 'Business Owner',
                'address' => '789, Jalan Cempaka, Taman Harmoni',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'postcode' => '46000',
                'relationship' => 'father',
                'whatsapp_number' => '60142345680',
                'notification_preference' => json_encode(['whatsapp' => true, 'email' => false, 'sms' => false]),
            ]
        );
        $this->command->info('✓ Parent 3 created: parent3@arenamatriks.com');

        // ===================================================================
        // STUDENT USERS
        // ===================================================================
        $student1User = User::firstOrCreate(
            ['email' => 'student@arenamatriks.com'],
            [
                'name' => 'Muhammad Aiman',
                'phone' => '0152345678',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $student1User->assignRole('student');

        Student::firstOrCreate(
            ['user_id' => $student1User->id],
            [
                'parent_id' => $parent1->id,
                'student_id' => 'STU-' . date('Y') . '-' . str_pad($student1User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '080515145013',
                'date_of_birth' => '2008-05-15',
                'gender' => 'male',
                'school_name' => 'SMK Shah Alam',
                'grade_level' => 'Form 5',
                'address' => '123, Jalan Mawar, Taman Bunga, Shah Alam',
                'registration_type' => 'offline',
                'registration_date' => '2024-01-15',
                'enrollment_date' => '2024-01-20',
                'referral_code' => strtoupper(Str::random(8)),
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
        $this->command->info('✓ Student 1 created: student@arenamatriks.com');

        $student2User = User::firstOrCreate(
            ['email' => 'student2@arenamatriks.com'],
            [
                'name' => 'Nurul Aisyah',
                'phone' => '0152345679',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $student2User->assignRole('student');

        Student::firstOrCreate(
            ['user_id' => $student2User->id],
            [
                'parent_id' => $parent2->id,
                'student_id' => 'STU-' . date('Y') . '-' . str_pad($student2User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '090720145014',
                'date_of_birth' => '2009-07-20',
                'gender' => 'female',
                'school_name' => 'SMK Klang',
                'grade_level' => 'Form 4',
                'address' => '456, Jalan Melati, Taman Indah, Klang',
                'registration_type' => 'online',
                'registration_date' => '2024-02-01',
                'enrollment_date' => '2024-02-05',
                'referral_code' => strtoupper(Str::random(8)),
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
        $this->command->info('✓ Student 2 created: student2@arenamatriks.com');

        $student3User = User::firstOrCreate(
            ['email' => 'student3@arenamatriks.com'],
            [
                'name' => 'Lim Wei Jie',
                'phone' => '0152345680',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]
        );
        $student3User->assignRole('student');

        Student::firstOrCreate(
            ['user_id' => $student3User->id],
            [
                'parent_id' => $parent3->id,
                'student_id' => 'STU-' . date('Y') . '-' . str_pad($student3User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '080312145015',
                'date_of_birth' => '2008-03-12',
                'gender' => 'male',
                'school_name' => 'SMK PJ',
                'grade_level' => 'Form 5',
                'address' => '789, Jalan Cempaka, Taman Harmoni, PJ',
                'registration_type' => 'offline',
                'registration_date' => '2024-01-10',
                'enrollment_date' => '2024-01-15',
                'referral_code' => strtoupper(Str::random(8)),
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );
        $this->command->info('✓ Student 3 created: student3@arenamatriks.com');

        // Pending Student (for testing approval workflow)
        $student4User = User::firstOrCreate(
            ['email' => 'student.pending@arenamatriks.com'],
            [
                'name' => 'Ahmad Pending',
                'phone' => '0152345681',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'status' => 'pending',
            ]
        );
        $student4User->assignRole('student');

        Student::firstOrCreate(
            ['user_id' => $student4User->id],
            [
                'student_id' => 'STU-' . date('Y') . '-' . str_pad($student4User->id, 4, '0', STR_PAD_LEFT),
                'ic_number' => '090101145016',
                'date_of_birth' => '2009-01-01',
                'gender' => 'male',
                'school_name' => 'SMK Test School',
                'grade_level' => 'Form 4',
                'registration_type' => 'online',
                'registration_date' => now()->toDateString(),
                'approval_status' => 'pending',
            ]
        );
        $this->command->info('✓ Pending Student created: student.pending@arenamatriks.com');

        // ===================================================================
        // SUMMARY
        // ===================================================================
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('✓ All demo users created successfully!');
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('Login Credentials (Default password: password123)');
        $this->command->info('-------------------------------------------');
        $this->command->info('Super Admin : superadmin@arenamatriks.com');
        $this->command->info('Admin       : admin@arenamatriks.com');
        $this->command->info('Admin 2     : admin2@arenamatriks.com');
        $this->command->info('Staff       : staff@arenamatriks.com');
        $this->command->info('Staff 2     : staff2@arenamatriks.com');
        $this->command->info('Cashier     : cashier@arenamatriks.com');
        $this->command->info('Teacher     : teacher@arenamatriks.com');
        $this->command->info('Teacher 2   : teacher2@arenamatriks.com');
        $this->command->info('Teacher 3   : teacher3@arenamatriks.com');
        $this->command->info('Teacher 4   : teacher4@arenamatriks.com');
        $this->command->info('Parent      : parent@arenamatriks.com');
        $this->command->info('Parent 2    : parent2@arenamatriks.com');
        $this->command->info('Parent 3    : parent3@arenamatriks.com');
        $this->command->info('Student     : student@arenamatriks.com');
        $this->command->info('Student 2   : student2@arenamatriks.com');
        $this->command->info('Student 3   : student3@arenamatriks.com');
        $this->command->info('Pending     : student.pending@arenamatriks.com');
        $this->command->info('===========================================');
    }
}
