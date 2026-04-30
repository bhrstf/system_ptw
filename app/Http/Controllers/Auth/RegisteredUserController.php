<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; // Penting untuk mencatat error email
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewAccountNotification;

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
        // --- 1. LOGIKA PINTAR: CEK USER YANG SUDAH DAFTAR TAPI BELUM VERIFIKASI ---
        // Jika email sudah ada tapi belum diverifikasi, kirim ulang OTP saja tanpa error "Email sudah digunakan"
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser && $existingUser->is_verified == false) {
            $otpCode = rand(100000, 999999);
            
            // Update OTP baru dan perpanjang waktu kadaluarsa jadi 10 menit
            $existingUser->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            // Kirim ulang email OTP
            try {
                Mail::raw("Kode OTP verifikasi akun PTW System Anda adalah: $otpCode. Kode ini berlaku selama 10 menit.", function ($message) use ($existingUser) {
                    $message->to($existingUser->email)->subject('Kode Verifikasi OTP Baru - PTW System');
                });
            } catch (\Exception $e) {
                // Catat error di log Railway tapi jangan hentikan proses agar tidak muncul error 500
                Log::error("Gagal kirim ulang email ke " . $existingUser->email . ": " . $e->getMessage());
            }

            // Simpan JWT token baru ke session untuk verifikasi
            $token = JWTAuth::fromUser($existingUser);
            session(['otp_token' => $token]);

            return redirect()->route('otp.verify')->with('success', 'Email ini sudah terdaftar sebelumnya. Kode OTP baru telah dikirim!');
        }

        // --- 2. VALIDASI INPUT (Hanya jalan jika email belum ada di database) ---
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required'],
            'verification_code' => ['nullable', 'string'],
        ], [
            'username.unique' => 'NPK atau Nama Perusahaan ini sudah terdaftar!',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // --- 3. FILTER KHUSUS ROLE HSE ---
        if ($request->role === 'HSE/Safety') {
            $secretKey = env('HSE_SECRET_CODE');
            if ($request->verification_code !== $secretKey) {
                return back()
                    ->withErrors(['verification_code' => 'Kode Verifikasi HSE Salah!'])
                    ->withInput();
            }
        }

        // --- 4. GENERATE DATA USER BARU ---
        $otpCode = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'otp_code' => $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10), 
            'is_verified' => false,
        ]);

        // --- 5. NOTIFIKASI KE MASTER ADMIN ---
        try {
            $masters = User::whereIn('role', ['master', 'superadmin'])->get();
            if ($masters->count() > 0) {
                Notification::send(
                    $masters, 
                    new NewAccountNotification(
                        'Pendaftaran Akun Baru', 
                        'Pengguna baru atas nama <strong>' . $user->name . '</strong> telah mendaftar dengan role <strong>' . strtoupper($user->role) . '</strong>.'
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi admin: " . $e->getMessage());
        }

        // --- 6. KIRIM OTP VIA EMAIL (Bungkus try-catch agar tidak crash jika SMTP bermasalah) ---
        try {
            Mail::raw("Kode OTP verifikasi akun PTW System Anda adalah: $otpCode. Kode ini berlaku selama 10 menit.", function ($message) use ($user) {
                $message->to($user->email)->subject('Kode Verifikasi OTP - PTW System');
            });
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email pendaftaran ke " . $user->email . ": " . $e->getMessage());
        }

        // --- 7. GENERATE TOKEN & REDIRECT ---
        $token = JWTAuth::fromUser($user);
        session(['otp_token' => $token]);

        return redirect()->route('otp.verify')->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP.');
    }
}