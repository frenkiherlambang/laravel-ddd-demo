<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder — data awal untuk demo.
 *
 * Membuat 1 admin, beberapa mahasiswa, lalu memanggil CourseSeeder untuk
 * mengisi katalog kursus (Admin "Bikin Kursus").
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // --- Admin (pembuat kursus) ---
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Kampus',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        // --- Beberapa mahasiswa ---
        $students = [
            ['name' => 'Budi Mahasiswa', 'email' => 'budi@example.com'],
            ['name' => 'Siti Mahasiswa', 'email' => 'siti@example.com'],
            ['name' => 'Andi Mahasiswa', 'email' => 'andi@example.com'],
        ];

        foreach ($students as $student) {
            User::query()->updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Student,
                    'email_verified_at' => now(),
                ],
            );
        }

        // --- Kursus untuk katalog ---
        $this->call(CourseSeeder::class);
    }
}
