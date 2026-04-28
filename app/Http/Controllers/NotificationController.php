<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // <-- Tambahkan ini sayang biar dia kenal Model User

class NotificationController extends Controller
{
    /**
     * Fungsi buat tandai semua sudah dibaca
     */
    public function markAllRead()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user) {
            // Ini akan merubah unread menjadi read di database
            $user->unreadNotifications->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai telah dibaca'
            ]);
        }

        return response()->json(['success' => false], 401);
    }

    /**
     * Fungsi buat nampilin halaman "Lihat Semua Aktivitas"
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Ambil semua notifikasi dengan pagination (10 per halaman)
        $notifications = $user->notifications()->paginate(10);

        return view('layouts.notifications.index', compact('notifications'));
    }
}