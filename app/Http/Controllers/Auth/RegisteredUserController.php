<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log; 
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

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
        // ===================================================================
        // 1. VALIDASI UTAMA: CEK EMAIL, NPK, DAN PERUSAHAAN
        // ===================================================================
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:HSE,Safety,Kontraktor'], 
            'verification_code' => ['nullable', 'string'],
        ], [
            'email.required' => 'Kolom email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = $request->role;

        // Cek Nama Perusahaan: Khusus role Kontraktor
        if ($role === 'Kontraktor') {
            if (!$request->filled('company')) {
                return back()->withErrors(['company' => 'Nama Perusahaan wajib diisi untuk Kontraktor!'])->withInput();
            }

            // Memastikan nama perusahaan dicek secara case-insensitive (tidak sensitif huruf besar/kecil)
            $companyExists = User::whereRaw('LOWER(company) = ?', [strtolower($request->company)])->exists();
            if ($companyExists) {
                return back()->withErrors(['company' => 'Perusahaan ini sudah terdaftar di sistem. Gunakan akun yang sudah ada.'])->withInput();
            }
        }

        // Cek NPK: Khusus role HSE/Safety
        if (in_array($role, ['HSE', 'Safety'])) {
            if (!$request->filled('username')) {
                return back()->withErrors(['username' => 'NPK wajib diisi untuk role HSE/Safety!'])->withInput();
            }

            $npkExists = User::where('username', $request->username)->exists();
            if ($npkExists) {
                return back()->withErrors(['username' => 'NPK ini sudah terdaftar di sistem!'])->withInput();
            }

            // Validasi Kode Rahasia HSE
            $secretKey = env('HSE_SECRET_CODE');
            if ($request->verification_code !== $secretKey) {
                return back()->withErrors(['verification_code' => 'Kode Verifikasi HSE Salah!'])->withInput();
            }
        }

        // ===================================================================
        // 2. LOGIKA USER YANG SUDAH DAFTAR TAPI BELUM VERIFIKASI (OTP)
        // ===================================================================
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser && $existingUser->is_verified == false) {
            $otpCode = rand(100000, 999999);
            
            $existingUser->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            try {
                Mail::raw("Kode OTP verifikasi akun PTW System Anda adalah: $otpCode. Kode ini berlaku selama 10 menit.", function ($message) use ($existingUser) {
                    $message->to($existingUser->email)->subject('Kode Verifikasi OTP Baru - PTW System');
                });
            } catch (\Throwable $e) {
                Log::error('OTP email resend failed', [
                    'email' => $existingUser->email,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Gagal mengirim OTP ke email. Silakan coba lagi beberapa saat, atau hubungi admin.']);
            }

            $token = JWTAuth::fromUser($existingUser);
            session(['otp_token' => $token]);

            return redirect()->route('otp.verify')->with('success', 'Email ini sudah terdaftar sebelumnya. Kode OTP baru telah dikirim!');
        }

        // ===================================================================
        // 3. SIMPAN SEMENTARA DATA PENDAFTARAN DI SESSION (PENDING)
        //    -> User TIDAK langsung dibuat di DB sampai verifikasi OTP berhasil
        // ===================================================================
        $otpCode = rand(100000, 999999);

        $usernameValue = in_array($role, ['HSE', 'Safety']) ? $request->username : $request->email;
        $companyValue = $role === 'Kontraktor' ? $request->company : null;

        // Simpan data pendaftaran di session (pending)
        $pending = [
            'name' => $request->name,
            'username' => $usernameValue,
            'email' => $request->email,
            'role' => $role,
            'company' => $companyValue,
            'password' => $request->password, // simpan plain, akan di-hash saat buat user (model cast 'password' => 'hashed')
            'otp_code' => (string) $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10)->toDateTimeString(),
        ];

        session(['pending_registration' => $pending]);

        // Kirim OTP ke email pendaftar
        try {
            Mail::raw("Kode OTP verifikasi akun PTW System Anda adalah: $otpCode. Kode ini berlaku selama 10 menit.", function ($message) use ($request) {
                $message->to($request->email)->subject('Kode Verifikasi OTP - PTW System');
            });
        } catch (\Throwable $e) {
            Log::error('OTP email send failed', [
                'email' => $request->email,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            session()->forget('pending_registration');

            return back()
                ->withInput()
                ->withErrors(['email' => 'Gagal mengirim OTP ke email. Pastikan email valid, lalu coba lagi.']);
        }

        // Redirect ke halaman verifikasi OTP (session berisi pending_registration)
        return redirect()->route('otp.verify')->with('success', 'Registrasi berhasil! Silakan cek email Anda untuk kode OTP.');
    }
}