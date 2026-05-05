<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OtpVerifyController extends Controller
{
    /**
     * Tampilkan halaman input OTP
     */
    public function show()
    {
        // Pastikan ada sesi verifikasi: bisa berupa token untuk user existing atau pending_registration untuk registrasi baru
        if (!session('otp_token') && !session('pending_registration')) {
            return redirect()->route('register')->withErrors(['error' => 'Sesi verifikasi tidak ditemukan.']);
        }

        return view('auth.verify-otp');
    }

    /**
     * Proses pengecekan kode OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|array|min:6',
            'otp.*' => 'required|numeric',
        ]);

        // Gabungkan array [1,2,3,4,5,6] jadi string "123456"
        $otpCode = implode('', $request->otp);

        // 1) Jika ada pending_registration di session => buat user baru setelah OTP cocok
        if (session('pending_registration')) {
            $pending = session('pending_registration');
            $expires = Carbon::parse($pending['otp_expires_at']);

            if ($pending['otp_code'] === $otpCode && Carbon::now()->before($expires)) {
                // Buat user di DB
                $newUser = User::create([
                    'name' => $pending['name'],
                    'username' => $pending['username'],
                    'email' => $pending['email'],
                    'role' => $pending['role'],
                    'company' => $pending['company'] ?? null,
                    'password' => $pending['password'],
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]);

                // Hapus sesi pending dan kembalikan pesan sukses
                session()->forget('pending_registration');

                return redirect()->route('login')->with('success', 'Akun berhasil diverifikasi dan disimpan. Silakan login.');
            }

            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);
        }

        // 2) Jika tidak ada pending, coba alur untuk user existing (token JWT)
        try {
            // Ambil user berdasarkan token JWT dari session
            $user = JWTAuth::setToken(session('otp_token'))->toUser();

            // Pastikan kolom otp_code & otp_expires_at ada di DB
            if ($user->otp_code === $otpCode && Carbon::now()->before($user->otp_expires_at)) {
                $user->update([
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'otp_code' => null,
                    'otp_expires_at' => null
                ]);

                session()->forget('otp_token');

                return redirect()->route('login')->with('success', 'Akun berhasil diverifikasi! Silakan login menggunakan NPK/Nama Perusahaan Anda.');
            }

            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);

        } catch (\Exception $e) {
            return redirect()->route('register')->withErrors(['error' => 'Sesi verifikasi berakhir atau tidak valid.']);
        }
    }
}