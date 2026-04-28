<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna dengan fitur pencarian dan filter.
     * Mengarah ke: resources/views/superadmin/index-users.blade.php
     */
    public function index(Request $request) // Tambahkan Request $request di sini
    {
        // 1. Ambil data input dari form pencarian di Blade
        $search = $request->input('search');
        $role = $request->input('role');

        // 2. Inisialisasi query model User
        $query = User::query();

        // 3. Logika Pencarian: Cek apakah user sedang mencari nama atau email
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 4. Logika Filter: Cek apakah user memfilter berdasarkan Role
        if ($role) {
            $query->where('role', $role);
        }

        // 5. Ambil data final (kecuali akun sendiri) dan urutkan dari yang terbaru
        $users = $query->where('id', '!=', Auth::id())
                       ->latest()
                       ->get();

        // 6. Siapkan statistik (tetap dihitung dari total database)
        $stats = [
            'total_users'      => User::count(),
            'admin_count'      => User::whereIn('role', ['admin', 'superadmin', 'master'])->count(),
            'kontraktor_count' => User::where('role', 'kontraktor')->count(),
            'hse_count'        => User::where('role', 'HSE/Safety')->count(),
        ];

        // 7. Kembalikan ke view penuh yang menggunakan layout (`resources/views/superadmin/users.blade.php`)
        return view('superadmin.users', compact('users', 'stats'));
    }

    /**
     * Menghapus data akun pengguna secara permanen dari sistem.
     */
    public function destroy(User $user)
    {
        // Validasi keamanan: Dilarang menghapus akun sendiri
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Tindakan ditolak: Anda tidak diperkenankan menghapus akun utama administrator.');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Data akun pengguna telah berhasil dihapus dari database sistem.');
    }
}