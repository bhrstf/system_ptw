<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsApprover
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Cek apakah user sudah login
        if (Auth::check()) {
            
            /**
             * 2. Ambil role user, bersihkan spasi di awal/akhir, 
             * dan buat huruf kecil semua (strtolower) agar tidak sensitif typo.
             */
            $role = strtolower(trim(Auth::user()->role ?? ''));

            /**
             * 3. DAFTAR ROLE YANG DIIZINKAN (LENGKAP)
             * Bub sesuaikan dengan pilihan role di web.php kamu tadi:
             * 'HSE/Safety', 'Maintenance', 'Kontraktor', 'Penanggung Jawab Area'
             */
            $allowedRoles = [
                'hse/safety', 
                'penanggung jawab area', 
                'maintenance', 
                'kontraktor',
                'superadmin', // Jaga-jaga kalau ada role admin
                'admin'
            ];

            // 4. Cek apakah role user ada di dalam daftar izin
            if (in_array($role, $allowedRoles)) {
                return $next($request);
            }

            /**
             * 5. JIKA DITOLAK:
             * Balikkan ke dashboard dengan pesan error yang jelas.
             * Ini penting biar kita tahu role apa yang sebenarnya terbaca di sistem.
             */
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak! Role [' . Auth::user()->role . '] tidak diizinkan untuk melakukan Approval/Validasi Lapangan.');
        }

        // 6. Jika belum login, tendang ke login
        return redirect()->route('login');
    }
}