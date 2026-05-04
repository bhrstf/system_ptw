<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Carbon\Carbon;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validasi dan buat user baru dengan sistem OTP & JWT.
     */
    public function create(array $input): User
    {
        // 1. Validasi Input (Pastikan field username dan role ada)
        Validator::make($input, [
            'role' => ['required', 'string', function ($attribute, $value, $fail) {
                $allowed = ['hse','safety','maintenance','area','kontraktor','kontraktor'];
                if (!in_array(strtolower($value), $allowed)) {
                    $fail('Role tidak valid.');
                }
            }],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            // company: wajib jika role kontraktor; bila diisi harus unik
            'company' => [Rule::requiredIf(function () use ($input) {
                return isset($input['role']) && strtolower($input['role']) === 'kontraktor';
            }), 'nullable', 'string', 'max:255', Rule::unique('users', 'company')],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // 2. Generate Kode OTP (6 Digit) dan Waktu Expired (5 Menit)
        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(5);

        // 3. Simpan User ke Database
        $user = User::create([
            'name' => $input['name'],
            'username' => $input['username'], // NPK atau Nama Perusahaan
            'email' => $input['email'],
            'role' => $input['role'],
            'company' => $input['company'] ?? null,
            'password' => Hash::make($input['password']),
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
        ]);

        // 4. Kirim Email OTP (Pastikan kamu sudah buat SendOtpMail tadi)
        Mail::to($user->email)->send(new SendOtpMail($otpCode));

        // 5. Generate Token JWT untuk proses verifikasi
        // Kita simpan di session agar halaman verifikasi OTP tahu user mana yang sedang diproses
        $token = JWTAuth::fromUser($user);
        session(['otp_token' => $token]);

        return $user;
    }
}