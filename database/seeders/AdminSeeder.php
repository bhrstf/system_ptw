<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Jalankan database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data dari .env (pake key yang konsisten ya bebe)
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        // 2. Alarm: Cek apakah .env sudah diisi atau belum
        if (!$email || !$password) {
            $this->command->error("Gagal: SUPER_ADMIN_EMAIL atau SUPER_ADMIN_PASSWORD di file .env belum diisi!");
            return;
        }

        // 3. Eksekusi: Masukkan ke Database
        User::updateOrCreate(
            ['email' => $email], 
            [
                'name' => 'Master Account', // Nama panggilannya biar keren
                'username' => 'masteradmin',
                'password' => Hash::make($password),
                'role' => 'master', // Ganti ke master biar dapet fitur 'edit semua'
                // 'nik' => '000000', // Tambahin field lain kalau di tabel user-mu ada yang required
            ]
        );

        $this->command->info("Master Account dengan email {$email} berhasil didaftarkan.");
    }
}