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
        // Kita pakai updateOrCreate supaya kalau emailnya sudah ada, 
        // datanya cuma di-update (nggak bikin error duplicate).
        User::updateOrCreate(
            [
                'email' => env('SUPER_ADMIN_EMAIL'), 
            ],
            [
                'name'              => 'Super Admin Minerva',
                'username'          => 'NPK001', 
                'password'          => Hash::make(env('SUPER_ADMIN_PASSWORD')), 
                'role'              => 'Superadmin', 
                'email_verified_at' => now(), 
            ]
        );
    }
}