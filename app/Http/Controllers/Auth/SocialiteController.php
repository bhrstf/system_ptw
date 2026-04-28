<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect ke Provider (Google/Azure/Outlook)
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Ambil data balik dari Provider
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Cari user berdasarkan email
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // JIKA USER SUDAH ADA: Update data social-nya saja
                $user->update([
                    'social_id'   => $socialUser->getId(),
                    'social_type' => $provider,
                ]);
            } else {
                // JIKA USER BELUM ADA (REGISTER OTOMATIS):
                $user = User::create([
                    'name'        => $socialUser->getName(),
                    'email'       => $socialUser->getEmail(),
                    'social_id'   => $socialUser->getId(),
                    'social_type' => $provider,
                    'password'    => Hash::make(Str::random(16)), // Password acak aman
                    'role'        => null, // Role dikosongkan agar wajib isi nanti
                ]);
            }

            // Login-kan user
            Auth::login($user);

            /**
             * --- BAGIAN PENENTUAN ARAH (REDIRECT) ---
             */
            if (is_null($user->role)) {
                // Jika role masih kosong, lempar ke Edit Profil (seperti maumu)
                return redirect()->route('profile.edit')->with('info', 'Selamat datang! Silakan lengkapi data pribadi dan pilih peran Anda.');
            }

            // Jika sudah punya role, langsung ke dashboard
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            // Jika ada error (misal user cancel login)
            return redirect('/login')->with('error', 'Gagal login menggunakan ' . $provider);
        }
    }
}