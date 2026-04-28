<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAccountNotification extends Notification
{
    use Queueable;

    private $pesan;
    private $detailPesan;

    // Supaya dinamis, bisa dipake buat ngabarin akun baru ATAU ptw yg udah di-approve
    public function __construct($pesan, $detailPesan)
    {
        $this->pesan = $pesan;
        $this->detailPesan = $detailPesan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->pesan,
            'detail' => $this->detailPesan,
        ];
    }
}