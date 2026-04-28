<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Atribut yang bisa diisi secara massal.
     * PENTING: profile_photo_path bub tambahin di sini biar bisa kesimpen!
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',      
        'role',    
        'company',      
        'is_verified',   
        'otp_code',      
        'otp_expires_at',
        'profile_photo_path' // <-- WAJIB ADA INI SAYANG
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Accessor Custom: Kita bikin profile_photo_url sendiri
     * biar nempel otomatis pas kita panggil $user->profile_photo_url
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'otp_expires_at' => 'datetime',
        ];
    }

    // =========================================================================
    // LOGIKA FOTO PROFIL (CUSTOM)
    // =========================================================================

    /**
     * Fungsi ajaib buat nentuin URL foto.
     * Kalau ada foto di storage, pake itu. Kalau gak ada, pake inisial.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path && Storage::disk('public')->exists($this->profile_photo_path)) {
            return asset('storage/' . $this->profile_photo_path);
        }

        // Default inisial nama pake warna biru cakep
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0056ff&color=fff';
    }

    // =========================================================================
    // FUNGSI WAJIB JWT
    // =========================================================================

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * RELASI KE PERMIT
     */
    public function permits()
    {
        return $this->hasMany(Permit::class);
    }
}