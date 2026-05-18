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
        // Menggunakan ptw_number agar konsisten dengan sistem yang sedang kamu bangun
        $ptwNumber = $this->ptw->ptw_number ?? $this->ptw->nomor_ptw;

        return [
            'title' => 'Pengajuan Permit Baru',
            'message' => "Permohonan PTW <strong>{$ptwNumber}</strong> telah diajukan oleh <strong>{$this->namaKontraktor}</strong>. Mohon segera lakukan tinjauan dokumen.",
            'ptw_id' => $this->ptw->id,
            'type' => 'submission',
            'icon' => 'fa-file-import text-warning',
        ];
    }
}