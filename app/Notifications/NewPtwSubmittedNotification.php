<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPtwSubmittedNotification extends Notification
{
    use Queueable;

    private $ptw;
    private $namaKontraktor;

    public function __construct($ptw, $namaKontraktor)
    {
        $this->ptw = $ptw;
        $this->namaKontraktor = $namaKontraktor;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Pengajuan PTW Baru Masuk',
            'detail' => 'Ada pengajuan PTW baru (Nomor: ' . $this->ptw->nomor_ptw . ') dari <strong>' . $this->namaKontraktor . '</strong>. Silakan segera ditinjau.',
        ];
    }
}