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
        // Pastikan ada token JWT di session
        if (!session('otp_token')) {
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

        try {
            // Ambil user berdasarkan token JWT dari session
            $user = JWTAuth::setToken(session('otp_token'))->toUser();

            // 1. Cek kecocokan kode & waktu expired
            // Pastikan kolom otp_code & otp_expires_at ada di DB (Lihat Migration di bawah)
            if ($user->otp_code === $otpCode && Carbon::now()->before($user->otp_expires_at)) {
                
                // 2. Update Status Verifikasi
                $user->update([
                    'is_verified' => true,
                    'email_verified_at' => now(), // Opsional jika ingin sinkron dengan Laravel
                    'otp_code' => null,
                    'otp_expires_at' => null
                ]);

                // 3. Hapus token sementara agar tidak bisa dipakai lagi
                session()->forget('otp_token');

                // 4. Redirect ke Login (Sesuai diskusi: Biar user login manual demi keamanan)
                return redirect()->route('login')->with('success', 'Akun berhasil diverifikasi! Silakan login menggunakan NPK/Nama Perusahaan Anda.');
            }

            return back()->withErrors(['otp' => 'Kode OTP salah atau sudah kadaluarsa.']);

        } catch (\Exception $e) {
            return redirect()->route('register')->withErrors(['error' => 'Sesi verifikasi berakhir atau tidak valid.']);
        }
    }
}