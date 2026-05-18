<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAccountNotification extends Notification
{
    use Queueable;

    private $pesan;
    private $detailPesan;
    private $title;

    /**
     * @param string $pesan Judul ringkas (misal: Registrasi Berhasil)
     * @param string $detailPesan Penjelasan lengkap
     * @param string|null $title Kategori notifikasi (opsional)
     */
    public function __construct($pesan, $detailPesan, $title = 'Informasi Sistem')
    {
        $this->pesan = $pesan;
        $this->detailPesan = $detailPesan;
        $this->title = $title;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => "<strong>{$this->pesan}</strong>", // Membuat pesan utama lebih menonjol
            'detail' => $this->detailPesan,
            'icon' => 'fa-user-shield text-navy', // Ikon default yang profesional
            'action_url' => url('/profile'), // Contoh link tujuan yang berguna bagi user
        ];
    }
}