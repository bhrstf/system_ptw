<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\User; // <-- Wajib dipanggil biar bisa nyari kontraktor & admin
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// --- TAMBAHAN IMPORT UNTUK NOTIFIKASI ---
use Illuminate\Support\Facades\Notification;
use App\Notifications\PtwStatusNotification;
use App\Notifications\NewAccountNotification;
// ----------------------------------------

class ApprovalController extends Controller
{
    /**
     * Menampilkan daftar permit yang butuh persetujuan (Pending & Approved)
     */
    public function index()
    {
        // Ambil data permit yang statusnya pending (kantor) atau approved (lapangan)
        $approvals = Permit::whereIn('status', ['pending', 'approved'])
                          ->latest()
                          ->paginate(10);

        return view('layouts.approvals.approval', compact('approvals'));
    }

    /**
     * Update status permit dari kantor (Approve/Reject Office)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_notes' => 'nullable|string' 
        ]);

        try {
            $permit = Permit::findOrFail($id);
            
            // Proses Update menggunakan Mass Assignment
            $permit->update([
                'status' => $request->status,
                'manager_name' => Auth::user()->name, 
                'manager_notes' => $request->manager_notes,
                'updated_at' => now()
            ]);

            // --- KODE NOTIFIKASI: KIRIM KE KONTRAKTOR (OFFICE APPROVAL) ---
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                $statusIndo = $request->status == 'approved' ? 'Disetujui Kantor (Menunggu Validasi Lapangan)' : 'Ditolak';
                $kontraktor->notify(new PtwStatusNotification($permit, $statusIndo));
            }
            // --------------------------------------------------------------

            $message = $request->status == 'approved' 
                ? 'Permit PTW-' . str_pad($permit->id, 5, '0', STR_PAD_LEFT) . ' Berhasil Disetujui Kantor!' 
                : 'Permit Telah Ditolak.';

            return redirect()->route('approvals.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses approval: ' . $e->getMessage());
        }
    }

    /**
     * Validasi Lapangan oleh PJA (Mengubah status dari Approved menjadi ACTIVE)
     */
    public function validateField(Request $request, $id)
    {
        // 1. Validasi input dari form modal
        $request->validate([
            'pja_name' => 'required|string|max:255',
            'signature_pja' => 'required', 
        ]);

        try {
            $permit = Permit::findOrFail($id);

            // 2. Update manual satu per satu agar lebih "galak" dan pasti ke database
            $permit->pja_name = $request->pja_name;
            $permit->signature_pja = $request->signature_pja;
            $permit->validated_at = now();
            $permit->status = 'active'; // Status berubah jadi ACTIVE agar pekerjaan bisa dimulai
            
            // 3. Simpan perubahan
            $permit->save();

            // --- KODE NOTIFIKASI: KIRIM KE KONTRAKTOR ---
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                $kontraktor->notify(new PtwStatusNotification($permit, 'ACTIVE (Sudah Divalidasi Lapangan)'));
            }

            // --- KODE NOTIFIKASI: KIRIM KE MASTER / SUPERADMIN ---
            $masters = User::whereIn('role', ['master', 'superadmin'])->get();
            if ($masters->count() > 0) {
                $nomorSurat = $permit->ptw_number ?? 'PTW-' . str_pad($permit->id, 5, '0', STR_PAD_LEFT);
                Notification::send(
                    $masters, 
                    new NewAccountNotification(
                        'PTW Telah Aktif', 
                        'Pengajuan dengan nomor <strong>' . $nomorSurat . '</strong> telah berhasil divalidasi lapangan dan berstatus ACTIVE.'
                    )
                );
            }
            // -----------------------------------------------------

            return redirect()->route('approvals.index')->with('success', 'Validasi Lapangan Berhasil! Status Permit PTW-' . str_pad($permit->id, 5, '0', STR_PAD_LEFT) . ' sekarang ACTIVE.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan validasi: ' . $e->getMessage());
        }
    }
}