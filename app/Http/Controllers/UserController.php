<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna dengan fitur pencarian dan filter.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->where('id', '!=', Auth::id())
                       ->latest()
                       ->get();

        $stats = [
            'total_users'      => User::count(),
            'admin_count'      => User::whereIn('role', ['admin', 'superadmin', 'master'])->count(),
            // Case-insensitive, trimmed role comparison to include variations like "Kontraktor" or trailing spaces
            'kontraktor_count' => User::whereRaw("LOWER(TRIM(role)) = ?", ['kontraktor'])->count(),
            // HSE can be stored as 'hse', 'HSE/Safety', etc. Match any role containing 'hse' (case-insensitive)
            'hse_count'        => User::whereRaw("LOWER(role) LIKE ?", ['%hse%'])->count(),
        ];

        return view('superadmin.users', compact('users', 'stats'));
    }

    /**
     * Menghapus data akun pengguna secara permanen dari sistem.
     */
    public function destroy(User $user)
    {
        // 1. Validasi keamanan: Dilarang menghapus akun sendiri
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Tindakan ditolak: Anda tidak diperkenankan menghapus akun sendiri.');
        }

        try {
            // 2. LOGIKA PERBAIKAN: Hapus semua data terkait di tabel permits dulu
            // Ini untuk menghindari error "Foreign Key Constraint" di Railway
            $user->permits()->delete(); 

            // 3. Baru hapus usernya
            $user->delete();

            return redirect()->route('superadmin.users.index')
                ->with('success', 'Akun pengguna dan seluruh riwayat pengajuannya telah berhasil dihapus.');
        } catch (\Exception $e) {
            // Jika terjadi error, catat di log dan beri tahu user
            Log::error("Gagal menghapus user ID {$user->id}: " . $e->getMessage());
            return back()->with('error', 'Gagal menghapus akun karena masih terhubung dengan data lain di sistem.');
        }
    }
}