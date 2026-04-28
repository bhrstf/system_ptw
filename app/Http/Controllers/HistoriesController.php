<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi; 

class HistoriesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        /**
         * PERBAIKAN 1: Cek Role Approver yang Benar
         */
        $role = strtolower(trim($user->role ?? ''));
        $isApprover = in_array($role, ['hse/safety', 'maintenance', 'penanggung jawab area', 'admin', 'superadmin']);

        // Load relasi audit agar hasil audit muncul di tabel
        $query = Permit::with(['user', 'audit']);

        /**
         * PERBAIKAN 2: Filter Data
         */
        if (!$isApprover) {
            $query->where('user_id', $user->id);
        }

        // Fitur Search (Sekarang bisa search berdasarkan Nomor PTW resmi juga)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('ptw_number', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%')
                  ->orWhere('applicant_name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $histories = $query->latest()->get();

        return view('layouts.history.history', compact('histories', 'isApprover'));
    }

    /**
     * FUNGSI BARU: Selesaikan Pekerjaan (Close Permit)
     */
    public function completePermit($id)
    {
        $permit = Permit::findOrFail($id);
        
        // Pastikan hanya role HSE/Admin yang bisa close (Opsional, sesuai kebijakanmu)
        $permit->update([
            'status' => Permit::STATUS_CLOSED // Menggunakan konstanta dari Model
        ]);

        return redirect()->back()->with('success', 'Pekerjaan telah diselesaikan dan Permit berhasil ditutup.');
    }

    public function exportPDF(Request $request)
    {
        // Ambil data histories untuk laporan masterlist
        $histories = Permit::with(['user', 'audit'])->latest()->get();
        $pdf = Pdf::loadView('layouts.history.export_pdf', compact('histories'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('OHS_Masterlist_'.date('Y').'.pdf');
    }

    /**
     * CETAK PDF PTW + MERGE DOKUMEN LAMPIRAN
     */
    public function pdf_ptw($id)
    {
        $permit = Permit::findOrFail($id);

        // Generate Halaman Utama
        $htmlPdf = Pdf::loadView('layouts.history.pdf_ptw', compact('permit'))
                      ->setPaper('a4', 'landscape') 
                      ->output();

        $pdf = new Fpdi();

        // Gabungkan Halaman Utama
        $tempMain = tempnam(sys_get_temp_dir(), 'main_ptw');
        file_put_contents($tempMain, $htmlPdf);
        
        $pageCount = $pdf->setSourceFile($tempMain);
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplId);
            $pdf->AddPage('L', [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
        }
        unlink($tempMain); 

        // Gabungkan Lampiran (JSA, dll)
        $attachments = [
            $permit->jsa_file,
            $permit->worker_list_file,
            $permit->competency_cert_file,
            $permit->work_procedure_file,
            $permit->tool_cert_file,
            $permit->hiradc_file
        ];

        foreach ($attachments as $filePath) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                $fullPath = storage_path('app/public/' . $filePath);
                
                if (pathinfo($fullPath, PATHINFO_EXTENSION) === 'pdf') {
                    try {
                        $count = $pdf->setSourceFile($fullPath);
                        for ($i = 1; $i <= $count; $i++) {
                            $tplIdx = $pdf->importPage($i);
                            $s = $pdf->getTemplateSize($tplIdx);
                            $ort = ($s['width'] > $s['height']) ? 'L' : 'P';
                            $pdf->AddPage($ort, [$s['width'], $s['height']]);
                            $pdf->useTemplate($tplIdx);
                        }
                    } catch (\Exception $e) { continue; }
                }
            }
        }

        // UPDATE: Nama file PDF pakai nomor PTW resmi kalau ada
        $fileName = $permit->ptw_number ? $permit->ptw_number . '.pdf' : 'PTW-' . str_pad($permit->id, 5, '0', STR_PAD_LEFT) . '.pdf';
        
        return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }
}