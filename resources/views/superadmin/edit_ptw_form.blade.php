@extends('layouts.app')

@php 
    $hideNotification = true; 

    // ==========================================
    // FILTER LOGIC DI BLADE
    // ==========================================
    if(!isset($permits)) {
        $permits = \App\Models\Permit::orderBy('created_at', 'desc')->get();
    }
    
    $filteredPermits = collect($permits);

    // Filter Tanggal
    if(request('filter_date')) {
        $filteredPermits = $filteredPermits->filter(function($p) {
            return $p->created_at && $p->created_at->format('Y-m-d') == request('filter_date');
        });
    }

    // Filter Lokasi
    if(request('filter_location')) {
        $searchLoc = strtolower(request('filter_location'));
        $filteredPermits = $filteredPermits->filter(function($p) use ($searchLoc) {
            return $p->location && str_contains(strtolower($p->location), $searchLoc);
        });
    }

    // Filter Jenis PTW
    if(request('filter_type')) {
        $searchType = request('filter_type');
        $filteredPermits = $filteredPermits->filter(function($p) use ($searchType) {
            $types = is_array($p->permit_type) ? $p->permit_type : json_decode($p->permit_type, true) ?? [];
            return in_array($searchType, $types);
        });
    }
@endphp

@section('content')

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ==========================================
       HANYA CSS UNTUK KONTEN (TIDAK GANGGU SIDEBAR TEMPLATE)
       ========================================== */
    .ptw-revisi-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .section-card { 
        background: white; padding: 30px; border-radius: 20px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
        margin-bottom: 25px; border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.3s ease;
    }

    .section-title {
        font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;
        color: #003380; border-left: 4px solid #003380;
        padding-left: 15px; margin-bottom: 25px !important;
    }

    /* --- PTW Type Selection --- */
    .pwt-item { 
        cursor: pointer; border: 2px solid #f1f5f9; border-radius: 18px; 
        padding: 25px 15px; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); 
        background: #fff; text-align: center; position: relative; display: block;
    }
    .pwt-item:hover { border-color: #cbd5e1; transform: translateY(-4px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }
    .pwt-item.active { border: 2px solid #003380; background-color: #f0f7ff; box-shadow: 0 0 0 4px rgba(0, 51, 128, 0.1); }
    .pwt-item div[style*="font-size:2.5rem"] { filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); margin-bottom: 10px; }

    /* --- Input Styles --- */
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #e2e8f0; background-color: #fcfcfd; font-size: 0.95rem; }
    .form-control:focus { border-color: #003380; box-shadow: 0 0 0 4px rgba(0, 51, 128, 0.1); background-color: #fff; }

    /* --- Table Monitoring --- */
    .monitoring-wrapper { background-color: #ffffff; border-radius: 12px; overflow: visible; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03); border: 1px solid #f1f5f9; }
    .monitoring-header { background-color: #0044aa; color: #ffffff; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; }
    .monitoring-title { font-size: 1.05rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 10px; }

    .btn-filter-custom { background-color: rgba(255, 255, 255, 0.15); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 20px; padding: 6px 16px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; }
    .btn-filter-custom:hover { background-color: rgba(255, 255, 255, 0.25); color: #ffffff; }

    .filter-dropdown-menu { width: 320px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.08); padding: 20px; margin-top: 10px !important; }

    .monitoring-table { margin-bottom: 0; width: 100%; }
    .monitoring-table thead th { background-color: #ffffff; color: #1e293b; font-weight: 700; font-size: 0.85rem; padding: 16px 24px; border-bottom: 2px solid #f1f5f9; white-space: nowrap; }
    .monitoring-table tbody td { padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 0.9rem; }
    .monitoring-table tbody tr:hover { background-color: #f8fafc; }

    .status-label { padding: 6px 14px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: inline-block; }
    .status-label.active { background-color: #eafff0; color: #00b84c; border: 1px solid #bcf0d1; }
    .status-label.pending { background-color: #fff9e6; color: #f5a623; border: 1px solid #fde4a9; }

    .btn-action-pill { border-radius: 50px; padding: 6px 14px; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .btn-view-outline { background-color: #ffffff; color: #3b82f6; border: 1px solid #3b82f6; }
    .btn-view-outline:hover { background-color: #eff6ff; color: #2563eb; }
    .btn-pdf-solid { background-color: #ef4444; color: #ffffff; border: 1px solid #ef4444; }
    .btn-pdf-solid:hover { background-color: #dc2626; color: #ffffff; }

    /* --- Misc UI --- */
    .checklist-section { border: 1px solid #e2e8f0; padding: 30px; border-radius: 20px; background-color: #ffffff; margin-top: 15px; }
    .signature-box { border: 2px dashed #cbd5e1; background: #fafbfc; border-radius: 15px; overflow: hidden; position: relative; }
    .btn-update { background: linear-gradient(135deg, #003380 0%, #0052cc 100%); border: none; padding: 18px; border-radius: 15px; font-weight: 800; color: #fff; width: 100%; transition: all 0.3s; }
    .btn-update:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0, 51, 128, 0.3); color: #fff; }
</style>

<div class="ptw-revisi-container container-fluid px-4 py-3">

    @if(isset($permit))
        {{-- ========================================== --}}
        {{-- TAMPILAN MODE EDIT (FORM REVISI)           --}}
        {{-- ========================================== --}}
        @php
            // PENOMORAN PTW-OHSS
            $yearEdit = $permit->created_at ? $permit->created_at->format('Y') : date('Y');
            $ptwNumberEdit = "PTW-OHSS-" . str_pad($permit->id, 3, '0', STR_PAD_LEFT) . "-" . $yearEdit;
        @endphp
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-extrabold m-0" style="color: #003380; font-weight: 800;">Superadmin Revision</h2>
                <p class="text-muted">Modify content for Permit <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">#{{ $ptwNumberEdit }}</span></p>
            </div>
            <a href="{{ route('superadmin.edit_ptw') }}" class="btn btn-outline-secondary px-4 rounded-pill shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <form id="permitForm" action="{{ route('permits.update', $permit->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 1. Select Permit Type --}}
            <div class="section-card">
                <div class="section-title">Select Permit Type</div>
                <div class="row g-4">
                    @php
                    $types=[
                        ['Cold Work','❄️'], ['Hot Work','🔥'], ['Electrical','⚡'], 
                        ['Working at Height','🪜'], ['Lifting Operation','🏗️'], 
                        ['Excavation','🚜'], ['Confined Space','📦']
                    ];
                    $currentTypes = is_array($permit->permit_type) ? $permit->permit_type : json_decode($permit->permit_type, true) ?? [];
                    @endphp
                    @foreach($types as $type)
                    @php $isChecked = in_array($type[0], $currentTypes); @endphp
                    <div class="col-lg-3 col-md-4 col-6">
                        <label class="pwt-item w-100 {{ $isChecked ? 'active' : '' }}" id="label-{{ Str::slug($type[0]) }}">
                            <input type="checkbox" name="permit_type[]" value="{{ $type[0] }}" class="d-none" onchange="togglePWT(this)" {{ $isChecked ? 'checked' : '' }}>
                            <div style="font-size:2.5rem">{{ $type[1] }}</div>
                            <div class="small fw-bold mt-2 text-dark">PTW - {{ $type[0] }}</div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. Work Details --}}
            <div class="section-card">
                <div class="section-title">Work Details & Personnel</div>
                <div class="row g-4">
                    @php
                        $from = $permit->valid_from instanceof \Carbon\Carbon ? $permit->valid_from : \Carbon\Carbon::parse($permit->valid_from);
                        $until = $permit->valid_until instanceof \Carbon\Carbon ? $permit->valid_until : \Carbon\Carbon::parse($permit->valid_until);
                    @endphp
                    <div class="col-md-3"><label class="small fw-bold text-muted mb-2">Start Date</label><input type="datetime-local" name="valid_from" class="form-control shadow-sm" value="{{ $from->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-md-3"><label class="small fw-bold text-muted mb-2">Until Date</label><input type="datetime-local" name="valid_until" class="form-control shadow-sm" value="{{ $until->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-md-6"><label class="small fw-bold text-muted mb-2">PIC Lead</label><input type="text" name="pic_lead" class="form-control shadow-sm" value="{{ $permit->pic_lead }}" required></div>
                    
                    <div class="col-md-4"><label class="small fw-bold text-muted mb-2">Applicant Name</label><input type="text" name="applicant_name" class="form-control shadow-sm" value="{{ $permit->applicant_name }}" required></div>
                    <div class="col-md-4"><label class="small fw-bold text-muted mb-2">Company</label><input type="text" name="company" class="form-control shadow-sm" value="{{ $permit->company }}" required></div>
                    <div class="col-md-4"><label class="small fw-bold text-muted mb-2">Email</label><input type="email" name="email" class="form-control shadow-sm" value="{{ $permit->email }}" required></div>
                    
                    <div class="col-md-4"><label class="small fw-bold text-muted mb-2">Phone Number</label><input type="text" name="phone" class="form-control shadow-sm" value="{{ $permit->phone }}" required></div>
                    <div class="col-md-8"><label class="small fw-bold text-muted mb-2">Work Location</label><input type="text" name="location" class="form-control shadow-sm" value="{{ $permit->location }}" required></div>
                    <div class="col-md-8"><label class="small fw-bold text-muted mb-2">General Work Scope</label><input type="text" name="work_scope_general" class="form-control shadow-sm" value="{{ $permit->work_scope_general }}" required></div>
                    <div class="col-md-4"><label class="small fw-bold text-muted mb-2">Referensi Dokumen</label><input type="text" name="ref_doc" class="form-control shadow-sm" value="{{ $permit->ref_doc }}"></div>

                    <div class="col-md-12 mt-3">
                        <label class="small fw-bold text-muted mb-2">Tools / Equipment Used</label>
                        <div id="editor-tools" style="height:150px; border-radius:12px;">{!! $permit->tools_used !!}</div>
                        <input type="hidden" name="tools_used" id="tools_used">
                    </div>

                    <div class="col-md-12 mt-4">
                        <label class="small fw-bold text-muted mb-2">Detailed Work Scope</label>
                        <div id="editor-scope" style="height:150px; border-radius:12px;">{!! $permit->work_scope_detail !!}</div>
                        <input type="hidden" name="work_scope_detail" id="work_scope_detail">
                    </div>
                </div>
            </div>

            {{-- 3. Risk Mitigation & Checklist --}}
            <div class="section-card">
                <div class="section-title">Risk Mitigation & Checklist</div>
                <div id="checklist-master-wrapper">
                    @php $curChecklist = is_array($permit->safety_checklists) ? $permit->safety_checklists : json_decode($permit->safety_checklists, true) ?? []; @endphp
                    @foreach(\App\Models\Permit::getMasterChecklist() as $ptwType => $categories)
                        @php 
                            $theme = \App\Models\Permit::getPermitTheme($ptwType); 
                            $isSectionActive = in_array($ptwType, $currentTypes);
                        @endphp
                        <div class="checklist-section {{ $isSectionActive ? '' : 'd-none' }} mb-4" id="section-{{ Str::slug($ptwType) }}">
                            <div class="p-3 mb-4 rounded-4 text-center fw-bold shadow-sm" style="background-color: {{ $theme['bg'] }} !important; color: {{ $theme['text'] }} !important; font-size: 1.1rem;">
                                <i class="fas fa-clipboard-check me-2"></i> {{ $theme['label'] }} Checklist
                            </div>
                            @foreach($categories as $subJudul => $questions)
                                <div class="fw-bold mb-3 mt-4 text-uppercase small ls-wide" style="color: {{ $theme['bg'] }};">{{ $subJudul }}</div>
                                <div class="row g-2">
                                    @foreach($questions as $q)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check p-2 border rounded-3 hover-bg-light transition-all" style="padding-left: 2.5rem !important;">
                                            <input type="checkbox" name="safety_checklists[]" value="{{ $q }}" class="form-check-input" {{ in_array($q, $curChecklist) ? 'checked' : '' }}>
                                            <label class="form-check-label small text-muted fw-medium">{{ $q }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 4. Documents Management --}}
            <div class="section-card">
                <div class="section-title">Documents Management</div>
                <div class="row g-4">
                    @php
                    $fileLabels = [
                        'jsa_file' => 'JSA File', 
                        'hiradc_file' => 'HIRADC File', 
                        'worker_list_file' => 'Daftar Pekerja', 
                        'competency_cert_file' => 'Sertifikat Kompetensi',
                        'work_procedure_file' => 'Prosedur Kerja',
                        'tool_cert_file' => 'Sertifikat Alat (Opsional)'
                    ];
                    @endphp
                    @foreach($fileLabels as $field => $label)
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 bg-light bg-opacity-50">
                            <label class="small fw-bold text-muted mb-2">{{ $label }}</label>
                            <input type="file" name="{{ $field }}" class="form-control shadow-none">
                            @if($permit->$field)
                                <div class="mt-2 d-flex align-items-center">
                                    <span class="badge bg-white text-primary border shadow-sm">
                                        <i class="fas fa-file-pdf me-2 text-danger"></i>
                                        <a href="{{ Storage::url($permit->$field) }}" target="_blank" class="text-decoration-none fw-bold">Review Current Document</a>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 5. Verification Signatures --}}
            <div class="section-card">
                <div class="section-title">Verification Signatures</div>
                <div class="row g-4">
                    <div class="col-md-6 text-center">
                        <label class="fw-bold text-muted small mb-3">Manager Authorization</label>
                        <input type="text" class="form-control text-center mb-3 fw-bold bg-light" value="{{ $permit->manager_name }}" readonly>
                        <div class="signature-box shadow-inner">
                            <canvas id="padM" height="150" class="w-100"></canvas>
                        </div>
                        <div class="mt-2 text-muted small"><i class="fas fa-lock me-1"></i> Digitally Signed & Locked</div>
                        <input type="hidden" name="signature_manager" id="sm" value="{{ $permit->signature_manager }}">
                    </div>
                    <div class="col-md-6 text-center">
                        <label class="fw-bold text-muted small mb-3">Applicant Acknowledgement</label>
                        <input type="text" class="form-control text-center mb-3 fw-bold bg-light" value="{{ $permit->applicant_confirm_name }}" readonly>
                        <div class="signature-box shadow-inner">
                            <canvas id="padA" height="150" class="w-100"></canvas>
                        </div>
                        <div class="mt-2 text-muted small"><i class="fas fa-lock me-1"></i> Digitally Signed & Locked</div>
                        <input type="hidden" name="signature_applicant" id="sa" value="{{ $permit->signature_applicant }}">
                    </div>
                </div>
            </div>

            {{-- 6. Revision Note --}}
            <div class="section-card" style="background-color: #fffcf0; border: 1px solid #ffeeba;">
                <div class="section-title mb-3 fw-bold text-dark"><i class="fas fa-pen-nib me-2"></i>Revision Note</div>
                <textarea name="revision_note" class="form-control border-warning bg-white" rows="4" placeholder="Describe the specific changes made to this permit document...">{{ $permit->last_revision_note }}</textarea>
            </div>

            <button type="submit" class="btn btn-update shadow-lg mb-5 py-3">
                <i class="fas fa-save me-2"></i> UPDATE & RE-GENERATE DOCUMENT
            </button>
        </form>

    @else
        {{-- ========================================== --}}
        {{-- TAMPILAN MODE TABEL (STYLE DASHBOARD)      --}}
        {{-- ========================================== --}}
        
        <div class="mb-4">
            <h2 class="fw-bold m-0" style="color: #003380; letter-spacing: -0.5px;">Update & Revisi Dokumen</h2>
            <p class="text-muted mb-0">Kelola dan perbarui data izin kerja (PTW) yang sedang aktif.</p>
        </div>

        <div class="monitoring-wrapper mt-4 mb-5">
            
            <div class="monitoring-header">
                <h5 class="monitoring-title">
                    <i class="fas fa-list-ul"></i> Monitoring Seluruh Permit
                </h5>
                
                <div class="dropdown">
                    <button class="btn btn-filter-custom dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-end filter-dropdown-menu" aria-labelledby="filterDropdown">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 fw-bold text-dark" style="font-size: 1rem;"><i class="fas fa-sliders-h me-2 text-primary"></i> Filter Dokumen</h6>
                            <button type="button" class="btn-close" style="font-size: 0.7rem;" data-bs-dismiss="dropdown" aria-label="Close"></button>
                        </div>
                        <hr class="mt-0 mb-3 text-muted">
                        
                        <form action="{{ url()->current() }}" method="GET"> 
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Tanggal Permit</label>
                                <input type="date" name="filter_date" class="form-control form-control-sm" value="{{ request('filter_date') }}">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Jenis Pekerjaan (PTW)</label>
                                <select name="filter_type" class="form-select form-select-sm">
                                    <option value="">-- Semua Jenis Pekerjaan --</option>
                                    <option value="Cold Work" {{ request('filter_type') == 'Cold Work' ? 'selected' : '' }}>Cold Work</option>
                                    <option value="Hot Work" {{ request('filter_type') == 'Hot Work' ? 'selected' : '' }}>Hot Work</option>
                                    <option value="Electrical" {{ request('filter_type') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="Working at Height" {{ request('filter_type') == 'Working at Height' ? 'selected' : '' }}>Working at Height</option>
                                    <option value="Lifting Operation" {{ request('filter_type') == 'Lifting Operation' ? 'selected' : '' }}>Lifting Operation</option>
                                    <option value="Excavation" {{ request('filter_type') == 'Excavation' ? 'selected' : '' }}>Excavation</option>
                                    <option value="Confined Space" {{ request('filter_type') == 'Confined Space' ? 'selected' : '' }}>Confined Space</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-muted">Lokasi / Area</label>
                                <input type="text" name="filter_location" class="form-control form-control-sm" placeholder="Contoh: Laguboti..." value="{{ request('filter_location') }}">
                            </div>
                            
                            <div class="d-flex justify-content-between gap-2">
                                <a href="{{ url()->current() }}" class="btn btn-light btn-sm w-50 fw-bold border">Reset</a>
                                <button type="submit" class="btn btn-primary btn-sm w-50 fw-bold" style="background-color: #0044aa; border: none;">Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto; min-height: 300px;"> 
                <table class="table monitoring-table align-middle">
                    <thead>
                        <tr>
                            <th># Nomor PTW</th>
                            <th>Jenis Kerja</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredPermits as $item)
                            @php
                                // Gabungin jenis PTW pake koma
                                $currentTypes = is_array($item->permit_type) ? $item->permit_type : json_decode($item->permit_type, true) ?? [];
                                $jenisKerjaStr = !empty($currentTypes) ? implode(', ', $currentTypes) : '-';
                                
                                // Set class status
                                $statusClass = strtolower($item->status) == 'pending' ? 'pending' : 'active';

                                // FORMAT PENOMORAN PTW-OHSS
                                $year = $item->created_at ? $item->created_at->format('Y') : date('Y');
                                $ptwNumberTemplate = "PTW-OHSS-" . str_pad($item->id, 3, '0', STR_PAD_LEFT) . "-" . $year;
                            @endphp
                            <tr>
                                <td class="fw-bold text-primary">
                                    {{ $ptwNumberTemplate }}
                                </td>
                                
                                <td style="max-width: 300px; white-space: normal;">
                                    {{ $jenisKerjaStr }}
                                </td>
                                
                                <td style="max-width: 250px; white-space: normal;">
                                    {{-- UPDATE: Sekarang pake data asli dari user --}}
                                    {{ $item->location ?? '-' }}
                                </td>
                                
                                <td>
                                    <span class="status-label {{ $statusClass }}">
                                        {{ strtoupper($item->status ?? 'ACTIVE') }}
                                    </span>
                                </td>
                                
                                <td>
                                    {{-- UPDATE: Sekarang pake tanggal asli inputan, kalau error tampil strip --}}
                                    {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                </td>
                                
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('superadmin.edit_ptw_form', $item->id) }}" class="btn btn-action-pill btn-view-outline" title="View/Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        {{-- Tombol PDF sesuaikan dengan route punyamu --}}
                                        <a href="{{ route('history.pdf_ptw', $item->id) }}" class="btn btn-action-pill btn-pdf-solid" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="opacity-50 mb-3">
                                        <i class="fas fa-folder-open fa-3x text-muted"></i>
                                    </div>
                                    <span class="text-muted fw-bold">Data tidak ditemukan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ==========================================
        // SCRIPT SIDEBAR DIKENDALIKAN OLEH APP.BLADE
        // ==========================================
        // Sengaja tidak menambahkan script toggle sidebar di sini agar tidak konflik.
        // Cukup biarkan app.blade.php / ptw.css yang mengerjakannya secara otomatis.
        
        @if(isset($permit))
            const quillTools = new Quill('#editor-tools', { 
                theme: 'snow',
                placeholder: 'Specify tools used...'
            });
            const quillScope = new Quill('#editor-scope', { 
                theme: 'snow',
                placeholder: 'Describe detailed work scope...'
            });
            const padM = new SignaturePad(document.getElementById('padM'));
            const padA = new SignaturePad(document.getElementById('padA'));

            const oldSigM = document.getElementById('sm').value;
            const oldSigA = document.getElementById('sa').value;
            if (oldSigM) padM.fromDataURL(oldSigM);
            if (oldSigA) padA.fromDataURL(oldSigA);
            padM.off(); padA.off();
            
            // Styling adjustment for quill
            document.querySelectorAll('.ql-container').forEach(el => {
                el.style.borderRadius = "0 0 12px 12px";
                el.style.backgroundColor = "#fff";
            });
            document.querySelectorAll('.ql-toolbar').forEach(el => {
                el.style.borderRadius = "12px 12px 0 0";
                el.style.backgroundColor = "#f8fafc";
            });

            window.togglePWT = function(input) {
                const slug = input.value.toLowerCase().replace(/ /g, '-');
                const section = document.getElementById('section-' + slug);
                const label = document.getElementById('label-' + slug);
                if (input.checked) {
                    label.classList.add('active');
                    if (section) {
                        section.classList.remove('d-none');
                        section.classList.add('animate__animated', 'animate__fadeInDown');
                    }
                } else {
                    label.classList.remove('active');
                    if (section) section.classList.add('d-none');
                }
            }

            document.getElementById('permitForm').onsubmit = function() {
                document.getElementById('tools_used').value = quillTools.root.innerHTML;
                document.getElementById('work_scope_detail').value = quillScope.root.innerHTML;
            };
        @endif
        
        // Mencegah dropdown nutup kalau user klik di dalam form filter
        document.querySelectorAll('.dropdown-menu').forEach(function(element) {
            element.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    });
</script>
@endsection