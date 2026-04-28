<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PtwStatusNotification extends Notification
{
    use Queueable;

    private $ptw;
    private $status;

    public function __construct($ptw, $status)
    {
        $this->ptw = $ptw;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database']; // Ngirim lewat database
    }

    public function toDatabase($notifiable)
    {
        // Format ini yang ditangkap oleh $notification->data['message'] di Blade kamu
        return [
            'message' => 'Status PTW Diperbarui',
            'detail' => 'Pengajuan PTW dengan nomor <strong>' . $this->ptw->nomor_ptw . '</strong> telah diubah statusnya menjadi: <strong>' . strtoupper($this->status) . '</strong>.',
        ];
    }
}