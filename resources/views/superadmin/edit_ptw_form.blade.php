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
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #f59e0b; padding-left: 10px;">Work Details & Personnel</div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="small fw-bold text-muted">Start Date</label><input type="datetime-local" name="valid_from" class="form-control" value="{{ $permit->valid_from->format('Y-m-d\TH:i') }}" required></div>
                        <div class="col-md-3"><label class="small fw-bold text-muted">Until Date</label><input type="datetime-local" name="valid_until" class="form-control" value="{{ $permit->valid_until->format('Y-m-d\TH:i') }}" required></div>
                        <div class="col-md-6"><label class="small fw-bold text-muted">PIC Lead</label><input type="text" name="pic_lead" class="form-control" value="{{ $permit->pic_lead }}" required></div>
                        <div class="col-md-6"><label class="small fw-bold text-muted">PIC Batamindo</label><input type="text" name="pic_batamindo" class="form-control" value="{{ $permit->pic_batamindo }}" required></div>
                        
                        <div class="col-md-4"><label class="small fw-bold text-muted">Applicant Name</label><input type="text" name="applicant_name" class="form-control" value="{{ $permit->applicant_name }}" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Company</label><input type="text" name="company" class="form-control" value="{{ $permit->company }}" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Email</label><input type="email" name="email" class="form-control" value="{{ $permit->email }}" required></div>
                        
                        <div class="col-md-4"><label class="small fw-bold text-muted">Phone Number</label><input type="text" name="phone" class="form-control" value="{{ $permit->phone }}" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Man Power</label><input type="number" name="man_power" class="form-control" value="{{ $permit->man_power }}" required></div>
                        <div class="col-md-8"><label class="small fw-bold text-muted">Work Location</label><input type="text" name="location" class="form-control" value="{{ $permit->location }}" required></div>
                        <div class="col-md-8"><label class="small fw-bold text-muted">General Work Scope</label><input type="text" name="work_scope_general" class="form-control" value="{{ $permit->work_scope_general }}" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Referensi Dokumen</label><input type="text" name="ref_doc" class="form-control" value="{{ $permit->ref_doc }}"></div>

                        <div class="col-md-12 mt-3">
                            <label class="small fw-bold text-muted">Tools / Equipment Used</label>
                            <div id="editor-tools" style="height:120px;background:#fff; border-radius: 0 0 10px 10px;">{!! $permit->tools_used !!}</div>
                            <input type="hidden" name="tools_used" id="tools_used">
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="small fw-bold text-muted">Detailed Work Scope</label>
                            <div id="editor-scope" style="height:120px;background:#fff; border-radius: 0 0 10px 10px;">{!! $permit->work_scope_detail !!}</div>
                            <input type="hidden" name="work_scope_detail" id="work_scope_detail">
                        </div>
                    </div>
                </div>

            {{-- Section 3: Risk Mitigation --}}
                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #f59e0b; padding-left: 10px;">Risk Mitigation</div>
                    
                    <label class="fw-bold mb-3">Hazards Identification</label>
                    <div class="row mb-4">
                        @php $curHazards = is_array($permit->hazards) ? $permit->hazards : (json_decode($permit->hazards, true) ?: []); @endphp
                        @foreach(\App\Models\Permit::getHazardList() as $hazard)
                        <div class="col-md-4 col-6 mb-2 hazard-container">
                            <div class="form-check">
                                <input type="checkbox" name="hazards[]" value="{{ $hazard }}" class="form-check-input risk-checkbox" {{ in_array($hazard, $curHazards) ? 'checked' : '' }}>
                                <label class="form-check-label small">{{ $hazard }}</label>
                            </div>
                            @if(str_contains(strtolower($hazard), 'lainnya'))
                            @php 
                                $hazardsOther = is_array($permit->hazards_other) ? implode(', ', $permit->hazards_other) : ($permit->hazards_other ?? '');
                            @endphp
                            <div class="other-input-container {{ in_array($hazard, $curHazards) ? '' : 'd-none' }}">
                                <input type="text" name="hazards_other" class="form-control form-control-sm" value="{{ $hazardsOther }}" placeholder="Sebutkan lainnya...">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <label class="fw-bold mb-3 border-top pt-4">PPE (Personal Protective Equipment)</label>
                    <div class="row mb-4">
                        @php 
                            $curPpe = is_array($permit->ppe) ? $permit->ppe : (json_decode($permit->ppe, true) ?: []);
                            $ppeOtherMap = $permit->ppe_other_map ?? [];
                        @endphp
                        @foreach(\App\Models\Permit::getPpeList() as $category=>$items)
                        <div class="col-12 fw-bold small text-primary mt-2 mb-2">{{ $category }}</div>
                        @php $catSlug = Str::slug($category); @endphp
                        @foreach($items as $ppe)
                        <div class="col-md-4 col-6 mb-1 ppe-container">
                            <div class="form-check">
                                @php 
                                    $isLainnya = str_contains(strtolower($ppe), 'lainnya');
                                    $uniqueValue = $isLainnya ? 'Lainnya_' . $catSlug : $ppe;
                                    
                                    if ($isLainnya) {
                                        $otherValue = $ppeOtherMap[$catSlug] ?? '';
                                        $isChecked = in_array($uniqueValue, $curPpe) || !empty($otherValue);
                                    } else {
                                        $isChecked = in_array($uniqueValue, $curPpe);
                                    }
                                @endphp
                                <input type="checkbox" name="ppe[]" value="{{ $uniqueValue }}" class="form-check-input risk-checkbox" {{ $isChecked ? 'checked' : '' }}>
                                <label class="form-check-label small">{{ $ppe }}</label>
                            </div>
                            @if($isLainnya)
                            @php 
                                $ppeOtherVal = $ppeOtherMap[$catSlug] ?? '';
                                $ppeOtherVal = is_array($ppeOtherVal) ? implode(', ', $ppeOtherVal) : $ppeOtherVal;
                            @endphp
                            <div class="other-input-container {{ $isChecked ? '' : 'd-none' }}">
                                <input type="text" name="ppe_other[{{ $catSlug }}]" class="form-control form-control-sm" value="{{ $ppeOtherVal }}" placeholder="Sebutkan lainnya...">
                            </div>
                            @endif
                        </div>
                        @endforeach
                        @endforeach
                    </div>

                    <div id="checklist-master-wrapper">
                        @php
                            $curChecklist = is_array($permit->safety_checklists) ? $permit->safety_checklists : (json_decode($permit->safety_checklists, true) ?: []);
                            $curChecklistOther = is_array($permit->safety_checklists_other) 
                                ? $permit->safety_checklists_other 
                                : (json_decode($permit->safety_checklists_other, true) ?: []);
                        @endphp
                        @foreach(\App\Models\Permit::getMasterChecklist() as $ptwType => $categories)
                            @php 
                                $theme = \App\Models\Permit::getPermitTheme($ptwType); 
                                $isSectionActive = in_array($ptwType, $currentTypes);
                            @endphp
                            <div class="checklist-section {{ $isSectionActive ? '' : 'd-none' }} mb-4" id="section-{{ Str::slug($ptwType) }}">
                                <div class="p-3 mb-3 rounded-3 text-center fw-bold shadow-sm" style="background-color: {{ $theme['bg'] }} !important; color: {{ $theme['text'] }} !important;">
                                    Permit to Work - {{ $theme['label'] }} Checklist
                                </div>
                                @foreach($categories as $subJudul => $questions)
                                    @php
                                        $isBagian2 = str_contains(strtolower($subJudul), 'bagian 2');
                                        $isColdWorkSection = str_contains(strtolower($ptwType), 'cold work');
                                        $anyChecked = false;
                                        foreach($questions as $q) {
                                            $qVal = is_array($q) ? ($q['text'] ?? 'Tanpa Nama') : $q;
                                            if(in_array($qVal, $curChecklist)) { 
                                                $anyChecked = true; 
                                                break; 
                                            }
                                        }
                                        $chkSlug = Str::slug($subJudul);
                                    @endphp
                                    <div class="sub-judul-checklist" style="color: {{ $theme['bg'] }}; filter: brightness(0.6);">{{ $subJudul }}</div>
                                    <div class="row" @if($isBagian2) style="display: {{ $anyChecked ? 'flex' : 'none' }};" data-bagian="bagian2" @endif>
                                        @foreach($questions as $q)
                                            @php
                                                $textValue = is_array($q) ? ($q['text'] ?? 'Tanpa Nama') : $q;
                                                $hasAdditional = is_array($q) && isset($q['input_tambahan']);
                                                $isLainnya = str_contains(strtolower($textValue), 'lainnya');
                                                $isBypass = str_contains(strtolower($textValue), 'bypass');
                                                $isChecked = in_array($textValue, $curChecklist);
                                            @endphp
                                            <div class="col-md-6 mb-2 checklist-container">
                                                <div class="form-check">
                                                    <input type="checkbox" name="safety_checklists[]" value="{{ $textValue }}" class="form-check-input risk-checkbox" {{ $isChecked ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-muted">{{ $textValue }}</label>
                                                </div>
                                                
                                                @php 
                                                    if($hasAdditional) {
                                                        $fieldName = $q['input_tambahan']['name'];
                                                        $oldValue = $curChecklistOther[$fieldName] ?? ($permit->$fieldName ?? '');
                                                        $shouldShow = $isChecked;
                                                    } elseif($isLainnya) {
                                                        $oldValue = $curChecklistOther[$chkSlug] ?? '';
                                                        $shouldShow = $isChecked;
                                                    } else {
                                                        $shouldShow = false;
                                                        $oldValue = '';
                                                    }
                                                @endphp
                                                
                                                <div class="other-input-container mt-1 {{ $shouldShow ? '' : 'd-none' }}">
                                                    @if($hasAdditional)
                                                        @if(!$isBypass)
                                                            <label class="small fw-bold text-muted d-block">{{ $q['input_tambahan']['label'] }}</label>
                                                            @if($q['input_tambahan']['type'] === 'textarea')
                                                                <textarea name="safety_checklists_other[{{ $fieldName }}]" class="form-control form-control-sm">{{ old('safety_checklists_other.'.$fieldName, $oldValue) }}</textarea>
                                                            @else
                                                                <input type="{{ $q['input_tambahan']['type'] ?? 'text' }}" name="safety_checklists_other[{{ $fieldName }}]" class="form-control form-control-sm" value="{{ old('safety_checklists_other.'.$fieldName, $oldValue) }}">
                                                            @endif
                                                        @endif
                                                    @elseif($isLainnya)
                                                        <input type="text" name="safety_checklists_other[{{ $chkSlug }}]" class="form-control form-control-sm" value="{{ old('safety_checklists_other.'.$chkSlug, $oldValue) }}" placeholder="Sebutkan lainnya...">
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Shared Bypass Container untuk Cold Work --}}
                                        @if(str_contains(strtolower($ptwType), 'cold work'))
                                            @php
                                                $bypassValue = old('rencana_durasi_bypass_jam', $permit->rencana_durasi_bypass_jam);
                                                $hasAnyBypassChecked = false;
                                                foreach($questions as $q) {
                                                    $qText = is_array($q) ? ($q['text'] ?? '') : $q;
                                                    if(str_contains(strtolower($qText), 'bypass') && in_array($qText, $curChecklist)) {
                                                        $hasAnyBypassChecked = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <div class="col-12 shared-bypass-container d-none mt-3 p-3 border rounded bg-light">
                                                <label class="fw-bold small text-muted d-block">Rencana durasi Bypass (Jam) *</label>
                                                <input type="number" name="rencana_durasi_bypass_jam" class="form-control form-control-sm" value="{{ $bypassValue }}">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

            {{-- 4. Documents Management --}}
            <div class="section-card">
                    <div class="section-title mb-2 fw-bold" style="color: #003380; border-left: 4px solid #f59e0b; padding-left: 10px;">Documents Update</div>
                    <div class="text-muted small mb-3">* Seluruh dokumen wajib diunggah dalam format <strong>PDF</strong>.</div>
                    
                    <div class="alert alert-info py-2 small mb-4" style="background-color: #d1f4ff; border: 1px solid #bce8f1; color: #31708f;">
                        Biarkan kosong jika tidak ingin mengganti dokumen lama.
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">JSA File</label>
                            <input type="file" name="jsa_file" class="form-control" accept=".pdf">
                            @if($permit->jsa_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->jsa_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">HIRADC File</label>
                            <input type="file" name="hiradc_file" class="form-control" accept=".pdf">
                            @if($permit->hiradc_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->hiradc_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">Daftar Pekerja</label>
                            <input type="file" name="worker_list_file" class="form-control" accept=".pdf">
                            @if($permit->worker_list_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->worker_list_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">Sertifikat Kompetensi</label>
                            <input type="file" name="competency_cert_file" class="form-control" accept=".pdf">
                            @if($permit->competency_cert_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->competency_cert_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">Prosedur Kerja</label>
                            <input type="file" name="work_procedure_file" class="form-control" accept=".pdf">
                            @if($permit->work_procedure_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->work_procedure_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <label class="small fw-bold text-dark mb-2">Sertifikat Alat, Bahan, dan Inspeksi Peralatan Listrik</label>
                            <input type="file" name="tool_cert_file" class="form-control" accept=".pdf">
                            <div class="text-muted mt-2" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i>Contoh dokumen: SLO, MSDS, atau hasil Inspeksi Mesin Las.</div>
                            @if($permit->tool_cert_file)
                                <div class="mt-2"><small><a href="{{ Storage::url($permit->tool_cert_file) }}" target="_blank" class="text-decoration-none"><i class="fas fa-file-pdf text-primary me-1"></i>Lihat File Lama</a></small></div>
                            @endif
                        </div>
                    </div>
                </div>

            {{-- 5. Verification Signatures --}}
            <div class="section-card">
                <div class="section-title">Verification Signatures</div>
                <div class="row g-4">
                    <div class="col-md-6 text-center">
                        <label class="fw-bold small mb-2">Nama Pimpinan / Manager</label>
                        <input type="text" name="manager_name" class="form-control text-center mb-2" value="{{ $permit->manager_name }}" readonly style="background-color: #f8fafc;">
                        <div class="signature-box shadow-inner">
                            <canvas id="padM" height="150" class="w-100" style="cursor: not-allowed;"></canvas>
                        </div>
                        <div class="mt-2 text-muted small"><i class="fas fa-lock me-1"></i>Digitally Signed & Locked</div>
                        <input type="hidden" name="signature_manager" id="sm" value="{{ $permit->signature_manager }}">
                    </div>
                    <div class="col-md-6 text-center">
                        <label class="fw-bold small mb-2">Nama Permit Applicant</label>
                        <input type="text" name="applicant_confirm_name" class="form-control text-center mb-2" value="{{ $permit->applicant_confirm_name }}" readonly style="background-color: #f8fafc;">
                        <div class="signature-box shadow-inner">
                            <canvas id="padA" height="150" class="w-100" style="cursor: not-allowed;"></canvas>
                        </div>
                        <div class="mt-2 text-muted small"><i class="fas fa-lock me-1"></i>Digitally Signed & Locked</div>
                        <input type="hidden" name="signature_applicant" id="sa" value="{{ $permit->signature_applicant }}">
                    </div>
                </div>
            </div>

            {{-- 6. Revision Note --}}
            <div class="section-card" style="background-color: #fffcf0; border: 1px solid #ffeeba;">
                <div class="section-title mb-3 fw-bold text-dark"><i class="fas fa-pen-nib me-2"></i>Revision Note</div>
                <textarea name="revision_note" class="form-control border-warning bg-white" rows="4" placeholder="Describe the specific changes made to this permit document...">{{ $permit->last_revision_note }}</textarea>
            </div>

            <button type="submit" class="btn btn-update mt-5 shadow">SIMPAN PERUBAHAN PERMIT</button>
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
            const initPlugin = (id, Class) => {
                const el = document.getElementById(id);
                return el ? new Class(el, { theme: 'snow' }) : null;
            };

            const quillTools = initPlugin('editor-tools', Quill);
            const quillScope = initPlugin('editor-scope', Quill);

            const canvasM = document.getElementById('padM');
            const canvasA = document.getElementById('padA');
            const padM = canvasM ? new SignaturePad(canvasM) : null;
            const padA = canvasA ? new SignaturePad(canvasA) : null;

            const oldSigM = document.getElementById('sm').value;
            const oldSigA = document.getElementById('sa').value;
            if (padM && oldSigM) padM.fromDataURL(oldSigM);
            if (padA && oldSigA) padA.fromDataURL(oldSigA);
            if (padM) padM.off();
            if (padA) padA.off();
            
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

            function handleRiskCheckbox(checkbox) {
                const container = checkbox.closest('.hazard-container, .ppe-container, .checklist-container');
                if (!container) return;
                
                const otherInput = container.querySelector('.other-input-container');
                if (otherInput) {
                    if (checkbox.checked) {
                        otherInput.classList.remove('d-none');
                    } else {
                        otherInput.classList.add('d-none');
                    }
                }
            }

            function handleChecklistLogic(element) {
                const checklistSection = element.closest('.checklist-section');
                if (!checklistSection) return;

                const bagian2Rows = checklistSection.querySelectorAll('[data-bagian="bagian2"]');
                if (bagian2Rows.length === 0) {
                    return;
                }

                let bagian1Checked = false;
                checklistSection.querySelectorAll('.risk-checkbox').forEach(cb => {
                    const parentRow = cb.closest('.row');
                    if(parentRow && parentRow.getAttribute('data-bagian') !== 'bagian2' && cb.checked) {
                        bagian1Checked = true;
                    }
                });

                bagian2Rows.forEach(row => {
                    row.style.display = bagian1Checked ? 'flex' : 'none';
                });
            }

            // --- EVENT LISTENERS ---
            document.querySelectorAll('.risk-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    handleRiskCheckbox(this);
                    handleChecklistLogic(this);
                });
            });

            document.querySelectorAll('input[name="permit_type[]"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelectorAll('[id^="section-"]').forEach(el => el.classList.add('d-none'));
                    togglePWT(this);
                });
            });

            // --- Toggle centralized Bypass container when relevant checklist changes ---
            function toggleBypassContainer() {
                const bypassContainer = document.querySelector('.shared-bypass-container');
                if (!bypassContainer) return;
                const anyBypassChecked = Array.from(document.querySelectorAll('input[name="safety_checklists[]"]:checked')).some(cb => {
                    return cb.value.toLowerCase().includes('bypass');
                });
                if (anyBypassChecked) {
                    bypassContainer.classList.remove('d-none');
                } else {
                    bypassContainer.classList.add('d-none');
                }
            }

            toggleBypassContainer();
            document.querySelectorAll('input[name="safety_checklists[]"]').forEach(cb => cb.addEventListener('change', toggleBypassContainer));

            document.getElementById('permitForm').onsubmit = function() {
                if (quillTools) document.getElementById('tools_used').value = quillTools.root.innerHTML;
                if (quillScope) document.getElementById('work_scope_detail').value = quillScope.root.innerHTML;
                // Signatures are read-only, do not update them
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