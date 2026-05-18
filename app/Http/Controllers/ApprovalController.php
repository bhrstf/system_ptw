<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PtwStatusNotification;

class ApprovalController extends Controller
{
    /**
     * Menampilkan daftar permit yang butuh persetujuan
     */
    public function index()
    {
        $approvals = Permit::whereIn('status', ['pending', 'approved'])
                          ->latest()
                          ->paginate(10);

        return view('layouts.approvals.approval', compact('approvals'));
    }

    /**
     * Tahap 1: Verifikasi oleh Kantor (HSE/Admin)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_notes' => 'nullable|string' 
        ]);

        try {
            $permit = Permit::findOrFail($id);
            
            // Update status ke 'approved' (Artinya lolos tahap kantor)
            $permit->update([
                'status' => $request->status,
                'manager_name' => Auth::user()->name, 
                'manager_notes' => $request->manager_notes,
                'updated_at' => now()
            ]);

            // Kirim Notifikasi ke Kontraktor
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                // Bahasa lebih formal sesuai standar perusahaan
                $statusIndo = $request->status == 'approved' 
                    ? 'Diverifikasi (Menunggu Validasi Lapangan)' 
                    : 'Permohonan Ditolak';
                $kontraktor->notify(new PtwStatusNotification($permit, $statusIndo));
            }

            // Pesan sukses tanpa nomor PTW karena belum aktif
            $message = $request->status == 'approved' 
                ? 'Permit Berhasil Diverifikasi! Silakan lanjut ke tahap Validasi Lapangan (PJA).' 
                : 'Permit Telah Ditolak.';

            return redirect()->route('approvals.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses verifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Tahap 2: Validasi Lapangan oleh PJA (Penerbitan Nomor & Status ACTIVE)
     */
    public function validateField(Request $request, $id)
    {
        $request->validate([
            'pja_name' => 'required|string|max:255',
            'signature_pja' => 'required', 
        ]);

        try {
            $permit = Permit::findOrFail($id);

            // LOGIKA PENOMORAN: Nomor baru dibuat SAAT INI (ketika divalidasi lapangan)
            // Menghitung permit yang sudah punya nomor untuk urutan selanjutnya
            $count = Permit::whereNotNull('ptw_number')->count() + 1;
            $newPtwNumber = 'PTW-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            // Update data validasi lapangan
            $permit->pja_name = $request->pja_name;
            $permit->signature_pja = $request->signature_pja;
            $permit->validated_at = now();
            $permit->status = 'active'; 
            $permit->ptw_number = $newPtwNumber; // Nomor resmi terbit di sini
            
            $permit->save();

            // Notifikasi status ACTIVE ke Kontraktor
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                $kontraktor->notify(new PtwStatusNotification($permit, 'active'));
            }

            return redirect()->route('approvals.index')->with('success', "Validasi Lapangan Berhasil! Nomor Permit {$newPtwNumber} telah terbit dan berstatus ACTIVE.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan validasi lapangan: ' . $e->getMessage());
        }
    }
}