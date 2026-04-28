<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\Audit; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    /**
     * Menampilkan halaman audit dan daftar permit yang bisa diaudit
     */
    public function index()
    {
        /**
         * 1. PERBAIKAN DI SINI:
         * Kita ambil yang 'approved' (kantor) DAN 'active' (lapangan)
         * agar semua permit yang sedang berjalan muncul di pilihan.
         */
        $permits = Permit::whereIn('status', ['approved', 'active'])
                    ->whereDoesntHave('audit') 
                    ->latest()
                    ->get();

        // 2. Ambil semua data log audit terbaru untuk tabel history
        $audits = Audit::with('permit')->latest()->get(); 

        return view('layouts.audits.audit', compact('permits', 'audits'));
    }

    /**
     * Menyimpan hasil audit lapangan ke database
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'permit_id' => 'required|exists:permits,id',
            'finding'   => 'required|string|min:5',
        ]);

        try {
            // Simpan data ke database
            Audit::create([
                'permit_id' => $request->permit_id,
                'finding'   => $request->finding,
                'status'    => 'verified'
            ]);

            /**
             * PERBAIKAN ROUTE: 
             * Pastikan mengarah ke 'audits.index' sesuai web.php kamu tadi
             */
            return redirect()->route('audits.index')->with('success', 'Hasil audit berhasil disimpan!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan audit: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan detail audit di halaman terpisah
     */
    public function show($id)
    {
        $audit = Audit::with('permit')->findOrFail($id);
        return view('layouts.audits.show', compact('audit'));
    }
}