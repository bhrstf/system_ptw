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
use App\Mail\SendOtpMail; // <--- WAJIB IMPORT INI

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // 1. VALIDASI
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:HSE,Safety,Kontraktor'], 
            'verification_code' => ['nullable', 'string'],
        ]);

        $role = $request->role;

        // Cek Role Kontraktor & HSE (Logika tetap sama)
        if ($role === 'Kontraktor') {
            if (!$request->filled('company')) {
                return back()->withErrors(['company' => 'Nama Perusahaan wajib diisi!'])->withInput();
            }
        }

        if (in_array($role, ['HSE', 'Safety'])) {
            if ($request->verification_code !== env('HSE_SECRET_CODE')) {
                return back()->withErrors(['verification_code' => 'Kode Verifikasi HSE Salah!'])->withInput();
            }
        }

        $otpCode = rand(100000, 999999);

        // ===================================================================
        // 2. LOGIKA USER LAMA TAPI BELUM VERIFIKASI
        // ===================================================================
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $existingUser->is_verified == false) {
            $existingUser->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            try {
                // GANTI DISINI: Pakai Mailable biar formal
                Mail::to($existingUser->email)->send(new SendOtpMail($otpCode));
            } catch (\Throwable $e) {
                Log::error('OTP email resend failed: ' . $e->getMessage());
                return back()->withErrors(['email' => 'Gagal mengirim OTP.']);
            }

            $token = JWTAuth::fromUser($existingUser);
            session(['otp_token' => $token]);
            return redirect()->route('otp.verify')->with('success', 'OTP baru telah dikirim!');
        }

        // ===================================================================
        // 3. SIMPAN SEMENTARA DI SESSION (USER BARU)
        // ===================================================================
        $usernameValue = in_array($role, ['HSE', 'Safety']) ? $request->username : $request->email;
        
        $pending = [
            'name' => $request->name,
            'username' => $usernameValue,
            'email' => $request->email,
            'role' => $role,
            'company' => $request->company ?? null,
            'password' => $request->password, 
            'otp_code' => (string) $otpCode,
            'otp_expires_at' => Carbon::now()->addMinutes(10)->toDateTimeString(),
        ];

        session(['pending_registration' => $pending]);

        try {
            // GANTI DISINI JUGA: Pakai Mailable biar formal sesuai template PTW Official
            Mail::to($request->email)->send(new SendOtpMail($otpCode));
        } catch (\Throwable $e) {
            Log::error('OTP email send failed: ' . $e->getMessage());
            session()->forget('pending_registration');
            return back()->withErrors(['email' => 'Gagal mengirim OTP.']);
        }

        return redirect()->route('otp.verify')->with('success', 'Registrasi berhasil! Cek email untuk kode OTP.');
    }
}