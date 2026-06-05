<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        // Alarm jika env di Railway lupa belum terisi
        if (!$email || !$password) {
            $this->command->error("Gagal: SUPER_ADMIN_EMAIL atau SUPER_ADMIN_PASSWORD di environment variable belum diisi!");
            return;
        }

        // Eksekusi pembuatan Master Admin
        User::updateOrCreate(
            [
                'email' => $email, 
            ],
            [
                'name'              => 'Master Account',
                'username'          => 'masteradmin', 
                'password'          => Hash::make($password), 
                'role'              => 'master', // Menyesuaikan kebutuhan fitur 'edit semua' kamu
                'email_verified_at' => now(), 
            ]
        );

        $this->command->info("Master Account dengan email {$email} berhasil didaftarkan ke database.");
    }
}