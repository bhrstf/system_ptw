<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Menandai seluruh notifikasi pengguna sebagai telah dibaca.
     */
    public function markAllRead()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->unreadNotifications->count() > 0) {
            $user->unreadNotifications->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Seluruh notifikasi telah berhasil diperbarui.'
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Tidak ada notifikasi baru.'
        ]);
    }

    /**
     * Menampilkan daftar riwayat aktivitas dan notifikasi sistem.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Menggunakan simplePaginate atau paginate untuk performa di produksi
        $notifications = $user->notifications()->latest()->paginate(15);

        return view('layouts.notifications.index', compact('notifications'));
    }
}