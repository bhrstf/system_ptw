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
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        // Menentukan judul dan pesan berdasarkan status secara dinamis
        $title = 'Pembaruan Status Permit';
        $message = '';

        switch ($this->status) {
            case 'approved':
                $title = 'Permit Disetujui (Office)';
                $message = "Permit <strong>{$this->ptw->ptw_number}</strong> telah disetujui oleh HSE Office dan menunggu validasi lapangan.";
                break;
            case 'active':
                $title = 'Permit Telah Aktif';
                $message = "Validasi lapangan selesai. Permit <strong>{$this->ptw->ptw_number}</strong> kini berstatus aktif.";
                break;
            case 'rejected':
                $title = 'Permit Ditolak';
                $message = "Permit <strong>{$this->ptw->ptw_number}</strong> telah ditolak oleh tim HSE. Silakan cek detail revisi.";
                break;
            default:
                $message = "Status permit <strong>{$this->ptw->ptw_number}</strong> telah diperbarui menjadi " . strtoupper($this->status) . ".";
                break;
        }

        return [
            'title' => $title, // Tambahkan title supaya di UI bisa dibedakan
            'message' => $message,
            'ptw_id' => $this->ptw->id,
            'status' => $this->status,
            'icon' => $this->getIcon(), // Tambahkan helper icon kalau mau lebih pro
        ];
    }

    private function getIcon()
    {
        return match($this->status) {
            'approved' => 'fa-check-circle text-success',
            'active' => 'fa-running text-info',
            'rejected' => 'fa-times-circle text-danger',
            default => 'fa-info-circle text-primary',
        };
    }
}