<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

// --- TAMBAHAN IMPORT UNTUK NOTIFIKASI ---
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewAccountNotification;
// ----------------------------------------

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Proses pendaftaran user baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required'],
            'verification_code' => ['nullable', 'string'], // Tambahan untuk input kode HSE
        ], [
            'username.unique' => 'NPK atau Nama Perusahaan ini sudah terdaftar!',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // --- TAMBAHAN LOGIKA FILTER HSE ---
        if ($request->role === 'HSE/Safety') {
            $secretKey = env('HSE_SECRET_CODE');

            if ($request->verification_code !== $secretKey) {
                return back()
                    ->withErrors(['verification_code' => 'Kode Verifikasi HSE Salah! Kamu tidak diizinkan mendaftar role ini.'])
                    ->withInput();
            }
        }
        // --- SELESAI LOGIKA FILTER ---

        // 2. Generate OTP 6 Digit
        $otpCode = rand(100000, 999999);

        // 3. Simpan data ke Database
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(2), 
            'is_verified' => false,
        ]);

        // --- KODE NOTIFIKASI: KIRIM KE MASTER ADMIN SAAT ADA AKUN BARU ---
        $masters = User::whereIn('role', ['master', 'superadmin'])->get();
        if ($masters->count() > 0) {
            Notification::send(
                $masters, 
                new NewAccountNotification(
                    'Pendaftaran Akun Baru', 
                    'Pengguna baru atas nama <strong>' . $user->name . '</strong> telah mendaftar di sistem dengan role <strong>' . strtoupper($user->role) . '</strong>.'
                )
            );
        }
        // -----------------------------------------------------------------

        // 4. Kirim OTP via Email
        try {
            Mail::raw("Kode OTP verifikasi akun PTW System Anda adalah: $otpCode. Kode ini berlaku selama 2 menit.", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode Verifikasi OTP - PTW System');
            });
        } catch (\Exception $e) {
            // Log error jika email gagal kirim (opsional)
        }

        // 5. Generate JWT Token Sementara
        $token = JWTAuth::fromUser($user);
        session(['otp_token' => $token]);

        // 6. Redirect ke halaman verifikasi OTP
        return redirect()->route('otp.verify')->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP.');
    }
}