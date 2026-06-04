<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

// --- TAMBAHAN IMPORT UNTUK NOTIFIKASI ---
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewPtwSubmittedNotification;
use App\Notifications\PtwStatusNotification;
// ----------------------------------------

class PermitController extends Controller
{
    /**
     * Tampilan Dashboard (Ada Card Statistik)
     */
    public function index()
    {
        $user = Auth::user();
        $role = strtolower(trim($user->role ?? ''));

        $query = Permit::query();
        if ($role === 'kontraktor') {
            $query->where('user_id', $user->id);
        }

        // Load semua data untuk ditampilkan di table dengan scrollbar
        $permits = $query->latest()->get();

        $statAktifQuery = Permit::where('status', Permit::STATUS_ACTIVE);
        $statTotalQuery = Permit::query();

        if ($role === 'kontraktor') {
            $statAktifQuery->where('user_id', $user->id);
            $statTotalQuery->where('user_id', $user->id);
        }

        $statAktif = $statAktifQuery->count();
        $statTotal = $statTotalQuery->count();

        $totalPekerjaan = 0;
        $allDataForStats = ($role === 'kontraktor') ? Permit::where('user_id', $user->id)->get() : Permit::all();
        
        foreach($allDataForStats as $p) {
            $types = is_array($p->permit_type) ? $p->permit_type : json_decode($p->permit_type, true);
            $totalPekerjaan += is_array($types) ? count($types) : 0;
        }

        if ($role === 'kontraktor') {
            $totalKontraktor = 1; 
        } else {
            $totalKontraktor = Permit::distinct('user_id')->count('user_id');
        }

        // --- 4. LOGIC UNTUK TOTAL AKUN KONTRAKTOR TERDAFTAR ---
        if ($role === 'kontraktor') {
            $totalKontraktor = 1; 
        } elseif (in_array($role, ['hse', 'hse/safety', 'master', 'admin', 'superadmin'])) {
            $totalKontraktor = User::where('role', 'LIKE', '%kontraktor%')->count();
        } else {
            $totalKontraktor = 0;
        }
        

        return view('dashboard', compact(
            'permits', 
            'statAktif', 
            'statTotal', 
            'totalPekerjaan', 
            'totalKontraktor'
        ));
    }

    /**
     * API Endpoint untuk Infinite Scroll di Dashboard
     */
    public function getPermitsAjax(Request $request)
    {
        $user = Auth::user();
        $role = strtolower(trim($user->role ?? ''));
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = Permit::query();
        if ($role === 'kontraktor') {
            $query->where('user_id', $user->id);
        }

        $permits = $query->latest()->paginate($perPage, ['*'], 'page', $page);

        $html = '';
        foreach($permits->items() as $permit) {
            $html .= view('components.permit-row', compact('permit'))->render();
        }

        return response()->json([
            'html' => $html,
            'current_page' => $permits->currentPage(),
            'last_page' => $permits->lastPage(),
            'has_more' => $permits->hasMorePages()
        ]);
    }

    /**
     * --- FITUR KHUSUS SUPERADMIN (LIST TABEL) ---
     */
    public function managementUpdate()
    {
        $permits = Permit::whereIn('status', [Permit::STATUS_ACTIVE, Permit::STATUS_APPROVED])->latest()->get();
        $permit = null; // Untuk tampilan list, bukan form edit individual
        return view('superadmin.edit_ptw_form', compact('permits', 'permit'));
    }

    /**
     * --- FITUR KHUSUS SUPERADMIN (FORM EDIT) ---
     */
    public function superadminEdit($id)
    {
        $permit = Permit::findOrFail($id);
        $safetyChecklists = Permit::getMasterChecklist(); 
        return view('superadmin.edit_ptw_form', compact('permit', 'safetyChecklists'));
    }

    /**
     * --- PDF EDITOR (KETIK ULANG) ---
     */
    public function editDocument($id, $field)
    {
        $permit = Permit::findOrFail($id);
        $path = storage_path('app/public/' . $permit->$field);

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        $fileData = base64_encode(file_get_contents($path));
        $documentUrl = 'data:application/pdf;base64,' . $fileData;

        return view('superadmin.document_editor', compact('permit', 'documentUrl', 'field'));
    }

    /**
     * --- SAVE & OVERWRITE PDF ---
     */
    public function saveDocument(Request $request, $id, $field)
    {
        $permit = Permit::findOrFail($id);
        $originalPath = $permit->$field; 

        if ($request->has('edited_image')) {
            try {
                $imageData = $request->input('edited_image');
                $image = str_replace('data:image/png;base64,', '', $imageData);
                $image = str_replace(' ', '+', $image);

                $pdf = Pdf::loadHTML('<img src="data:image/png;base64,'.$image.'" style="width:100%; height:auto;">');
                Storage::disk('public')->put($originalPath, $pdf->output());

                return redirect()->route('superadmin.edit_ptw')->with('success', 'Konten Dokumen Berhasil Diperbarui!');
            } catch (\Exception $e) {
                Log::error("Error Overwrite Document: " . $e->getMessage());
                return back()->with('error', 'Gagal mengintegrasikan perubahan.');
            }
        }
        return back()->with('error', 'Data editan tidak ditemukan.');
    }

    /**
     * --- FORM CREATE ---
     */
    public function create()
    {
        $role = strtolower(trim(Auth::user()->role ?? ''));
        if ($role !== 'kontraktor') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak!');
        }
        $safetyChecklists = Permit::getMasterChecklist(); 
        return view('permits.create', compact('safetyChecklists'));
    }

    /**
     * --- STORE PERMIT (SIMPAN DATA BARU) ---
     */
    /**
     * --- STORE PERMIT (SIMPAN DATA BARU) ---
     */
    public function store(Request $request)
    {
        $fileFields = ['jsa_file', 'hiradc_file', 'worker_list_file', 'competency_cert_file', 'work_procedure_file', 'tool_cert_file'];
        $uploadedFiles = [];

        try {
            DB::beginTransaction();
            $request->merge([
                'tools_text' => strip_tags($request->tools_used),
                'scope_text' => strip_tags($request->work_scope_detail),
            ]);
            
            $request->validate([
                'pic_lead' => 'required|string|max:255',
                'pic_batamindo' => 'nullable|string|max:255',
                'hazards_other' => 'nullable|string|max:255',
                'ppe_other' => 'nullable|array',
                'safety_checklists_other' => 'nullable|array', // Validasi array terstruktur
                'man_power' => 'required|integer|min:1',
                'ref_doc' => 'nullable|string|max:255',
                'valid_from' => 'required|date',
                'valid_until' => 'required|date|after:valid_from',
                'rencana_durasi_bypass_jam' => 'nullable|integer',
                'jumlah_titik_isolasi'      => 'nullable|integer',
                'penjelasan_zero_energy'    => 'nullable|string',
                'tools_text' => 'required',
                'scope_text' => 'required',
            ], [
                'tools_text.required' => 'Kolom Tools/Equipment Used wajib diisi!',
                'scope_text.required' => 'Kolom Detailed Work Scope wajib diisi!',
            ]);

            $data = $request->all();
            
            // --- Pemetaan Nilai Standar ---
            $data['check_content_identified']    = $request->has('check_content_identified');
            $data['check_isolation_diagram']     = $request->has('check_isolation_diagram');
            $data['check_zero_energy_achieved']  = $request->has('check_zero_energy_achieved');

            $data['permit_type'] = $request->input('permit_type', []);
            $data['hazards'] = $request->input('hazards', []);
            $data['ppe'] = $request->input('ppe', []);
            $data['safety_checklists'] = $request->input('safety_checklists', []);
            
            $hazardsOtherRaw = $request->input('hazards_other');
            $data['hazards_other'] = $hazardsOtherRaw ? [$hazardsOtherRaw] : [];
            $data['ppe_other'] = $request->input('ppe_other', []);

            // SOLUSI: Tangkap array text tambahan kustom & mapping manual
            $checklistOther = $request->input('safety_checklists_other', []);
            $data['safety_checklists_other'] = $checklistOther;

            // Sinkronisasi data ke kolom flat database jika field tersebut ada/eksis di input kustom
            $data['rencana_durasi_bypass_jam']   = $request->input('rencana_durasi_bypass_jam') ?? ($checklistOther['rencana_durasi_bypass_jam'] ?? null);
            $data['jumlah_titik_isolasi']        = $request->input('jumlah_titik_isolasi') ?? ($checklistOther['jumlah_titik_isolasi'] ?? null);
            $data['penjelasan_zero_energy']      = $request->input('penjelasan_zero_energy') ?? ($checklistOther['penjelasan_zero_energy'] ?? null);

            $data['agreed_to_terms'] = $request->has('agreed_to_terms') ? 1 : 0;
            $data['applicant_confirmation'] = $request->has('applicant_confirmation') ? 1 : 0;
            $data['user_id'] = Auth::id();
            $data['status'] = Permit::STATUS_PENDING; 
            $data['company'] = Auth::user()->company ?? Auth::user()->name; 

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('permits/documents', 'public');
                    $data[$field] = $path;
                    $uploadedFiles[] = $path;
                }
            }

            Permit::create($data);
            DB::commit();

            $ptwBaru = Permit::where('user_id', Auth::id())->latest()->first();
            $hseUsers = User::whereIn('role', ['hse', 'hse/safety'])->get(); 
            
            if($ptwBaru && $hseUsers->count() > 0) {
                Notification::send($hseUsers, new NewPtwSubmittedNotification($ptwBaru, Auth::user()->name));
            }

            return redirect()->route('dashboard')->with('success', 'Pengajuan Permit Berhasil!');
        } catch (\Exception $e) {
            DB::rollback();
            foreach ($uploadedFiles as $filePath) { Storage::disk('public')->delete($filePath); }
            Log::error("Error Store Permit: " . $e->getMessage());
            return back()->withInput()->withErrors(['msg' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * --- FORM EDIT ---
     */
    public function edit($id)
    {
        $permit = Permit::findOrFail($id);
        $user = Auth::user();
        $role = strtolower(trim($user->role ?? ''));

        $isOwner = ($permit->user_id === $user->id && $permit->status === Permit::STATUS_PENDING);
        $isAdmin = in_array($role, ['master', 'admin', 'superadmin']);

        if (!$isOwner && !$isAdmin) {
            return redirect()->route('dashboard')->with('error', 'Aksi tidak diizinkan.');
        }

        $safetyChecklists = Permit::getMasterChecklist(); 
        return view('permits.edit', compact('permit', 'safetyChecklists'));
    }

    /**
     * --- UPDATE PERMIT (SIMPAN PERUBAHAN) ---
     */
    /**
     * --- UPDATE PERMIT (SIMPAN PERUBAHAN) ---
     */
    public function update(Request $request, $id)
    {
        $permit = Permit::findOrFail($id);
        $user = Auth::user();
        $role = strtolower(trim($user->role ?? ''));

        try {
            DB::beginTransaction();
            
            $request->validate([
                'pic_lead' => 'sometimes|required|string|max:255',
                'pic_batamindo' => 'nullable|string|max:255',
                'hazards_other' => 'nullable|string|max:255',
                'man_power' => 'sometimes|required|integer|min:1',
                'ppe_other' => 'nullable|array',
                'safety_checklists_other' => 'nullable|array', // Validasi array data kustom
                'ref_doc' => 'nullable|string|max:255',
                'valid_from' => 'sometimes|required|date',
                'valid_until' => 'sometimes|required|date|after:valid_from',
                'rencana_durasi_bypass_jam' => 'nullable|integer',
                'jumlah_titik_isolasi'      => 'nullable|integer',
                'penjelasan_zero_energy'    => 'nullable|string',
            ]);

            $data = $request->all();

            // SOLUSI: Ambil seluruh data input teks tambahan checklist
            $checklistOther = $request->input('safety_checklists_other', []);
            
            // Satukan data flat lama ke dalam text kustom jika diinput lewat modal/komponen lain
            $data['rencana_durasi_bypass_jam']   = $request->input('rencana_durasi_bypass_jam') ?? ($checklistOther['rencana_durasi_bypass_jam'] ?? null);
            $data['jumlah_titik_isolasi']        = $request->input('jumlah_titik_isolasi') ?? ($checklistOther['jumlah_titik_isolasi'] ?? null);
            $data['penjelasan_zero_energy']      = $request->input('penjelasan_zero_energy') ?? ($checklistOther['penjelasan_zero_energy'] ?? null);
            
            $data['check_content_identified']    = $request->has('check_content_identified');
            $data['check_isolation_diagram']     = $request->has('check_isolation_diagram');
            $data['check_zero_energy_achieved']  = $request->has('check_zero_energy_achieved');

            $data['permit_type'] = $request->input('permit_type', $permit->permit_type);
            $data['hazards'] = $request->input('hazards', []);
            $data['ppe'] = $request->input('ppe', []);
            $data['safety_checklists'] = $request->input('safety_checklists', []);
            
            $hazardsOtherRaw = $request->input('hazards_other');
            $data['hazards_other'] = $hazardsOtherRaw ? [$hazardsOtherRaw] : [];
            $data['ppe_other'] = $request->input('ppe_other', []);
            
            // Pastikan array checklist kustom disimpan kembali utuh ke database
            $data['safety_checklists_other'] = $checklistOther;

            $data['agreed_to_terms'] = $request->has('agreed_to_terms') ? 1 : 0;
            $data['applicant_confirmation'] = $request->has('applicant_confirmation') ? 1 : 0;

            $fileFields = ['jsa_file', 'hiradc_file', 'worker_list_file', 'competency_cert_file', 'work_procedure_file', 'tool_cert_file'];
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    if ($permit->$field) { 
                        Storage::disk('public')->delete($permit->$field); 
                    }
                    $data[$field] = $request->file($field)->store('permits/documents', 'public');
                } else {
                    unset($data[$field]); 
                }
            }

            if (in_array($role, ['master', 'superadmin', 'admin', 'hse/safety', 'hse'])) {
                $data['last_revision_note'] = $request->revision_note ?? 'Diperbarui oleh Administrator pada ' . now()->format('d/m/Y H:i');
            }

            // SOLUSI UTAMA: Menggunakan metode forceFill atau update bypass jika properti tidak ada di $fillable model
            $permit->forceFill([
                'safety_checklists_other' => $data['safety_checklists_other']
            ]);
            
            $permit->update($data);
            
            DB::commit();
            
            $targetRoute = in_array($role, ['master', 'admin', 'superadmin']) ? 'superadmin.edit_ptw' : 'dashboard';
            return redirect()->route($targetRoute)->with('success', 'Perubahan Permit ID: ' . $id . ' Berhasil Disimpan!');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Gagal Update Permit: " . $e->getMessage());
            return back()->withInput()->withErrors(['msg' => 'Gagal Update: ' . $e->getMessage()]);
        }
    }

    /**
     * --- DETAIL PERMIT ---
     */
    public function show(Permit $permit)
    {
        $role = strtolower(trim(Auth::user()->role ?? ''));
        if ($role === 'kontraktor' && $permit->user_id !== Auth::id()) { abort(403); }

        $selectedTypes = is_array($permit->permit_type) ? $permit->permit_type : json_decode($permit->permit_type, true);
        $primaryType = $selectedTypes[0] ?? 'Cold Work';
        $theme = Permit::getPermitTheme($primaryType);
        $masterData = Permit::getMasterChecklist();
        
        $checklistGrouped = [];
        if(is_array($selectedTypes)) {
            foreach ($selectedTypes as $type) {
                if (isset($masterData[$type])) { $checklistGrouped = array_merge($checklistGrouped, $masterData[$type]); }
            }
        }
        return view('permits.show', compact('permit', 'theme', 'checklistGrouped'));
    }

    /**
     * --- DOWNLOAD PDF ---
     */
    public function download($id)
    {
        $permit = Permit::findOrFail($id);
        $role = strtolower(trim(Auth::user()->role ?? ''));
        if ($role === 'kontraktor' && $permit->user_id !== Auth::id()) { abort(403); }

        $selectedTypes = is_array($permit->permit_type) ? $permit->permit_type : json_decode($permit->permit_type, true);
        $masterData = Permit::getMasterChecklist();
        
        $checklistGrouped = [];
        if(is_array($selectedTypes)) {
            foreach ($selectedTypes as $type) {
                if (isset($masterData[$type])) { $checklistGrouped = array_merge($checklistGrouped, $masterData[$type]); }
            }
        }

        $pdf = Pdf::loadView('layouts.history.pdf_ptw', compact('permit', 'checklistGrouped'));
        
        $fileName = $permit->ptw_number ? str_replace('/', '-', $permit->ptw_number) : 'PTW-' . $permit->id;
        return $pdf->stream($fileName . '.pdf');
    }

    /**
     * --- APPROVE PERMIT (SET TO ACTIVE) ---
     */
    public function approve(Request $request, $id)
    {
        $role = strtolower(trim(Auth::user()->role ?? ''));
        if (!in_array($role, ['hse', 'hse/safety', 'master', 'admin', 'superadmin'])) {
            return back()->with('error', 'Bukan role HSE/Admin.');
        }

        try {
            $permit = Permit::findOrFail($id);
            
            $permit->update(['status' => Permit::STATUS_ACTIVE]); 
            
            // --- KODE NOTIFIKASI TAMBAHAN: KIRIM KE KONTRAKTOR ---
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                $kontraktor->notify(new PtwStatusNotification($permit, 'Disetujui / Aktif'));
            }
            // -----------------------------------------------------
            
            $msg = $permit->ptw_number ? "PTW Aktif dengan nomor: " . $permit->ptw_number : "PTW Berhasil Diaktifkan!";
            return redirect()->route('dashboard')->with('success', $msg);
        } catch (\Exception $e) {
            Log::error("Error Approve Permit: " . $e->getMessage());
            return back()->with('error', 'Gagal mengaktifkan permit.');
        }
    }

    /**
     * --- REJECT PERMIT ---
     */
    public function reject(Request $request, $id)
    {
        $role = strtolower(trim(Auth::user()->role ?? ''));
        if (!in_array($role, ['hse', 'hse/safety', 'master', 'admin', 'superadmin'])) {
            return back()->with('error', 'Bukan role HSE/Admin.');
        }

        try {
            $permit = Permit::findOrFail($id);
            $permit->update(['status' => Permit::STATUS_REJECTED]);
            
            // --- KODE NOTIFIKASI TAMBAHAN: KIRIM KE KONTRAKTOR ---
            $kontraktor = User::find($permit->user_id);
            if ($kontraktor) {
                $kontraktor->notify(new PtwStatusNotification($permit, 'Ditolak'));
            }
            // -----------------------------------------------------

            return back()->with('error', 'Permit ditolak.');
        } catch (\Exception $e) {
            Log::error("Error Reject Permit: " . $e->getMessage());
            return back()->with('error', 'Gagal menolak permit.');
        }
    }

    /**
     * --- COMPLETE WORK ---
     */
    public function complete($id)
    {
        try {
            $permit = Permit::findOrFail($id);
            $permit->update(['status' => Permit::STATUS_COMPLETED]);
            return back()->with('success', 'Pekerjaan telah dinyatakan SELESAI.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status.');
        }
    }

    /**
     * --- CLOSE PERMIT ---
     */
    public function close($id)
    {
        try {
            $permit = Permit::findOrFail($id);
            $permit->update(['status' => Permit::STATUS_CLOSED]);
            return back()->with('success', 'Permit resmi DITUTUP.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status.');
        }
    }
}