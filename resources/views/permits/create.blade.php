@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit to Work (PTW) - Application</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ptw.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    
    <style>
        body.ptw-page { margin: 0; padding: 0; overflow-x: hidden; display: flex; background-color: #f4f7f6; }
        .sidebar { width: 260px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1100; transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); background: #003380; overflow-y: auto; }
        .sidebar.collapsed { width: 85px; }
        #main-wrapper { flex: 1; margin-left: 260px; min-height: 100vh; transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); width: calc(100% - 260px); }
        .sidebar.collapsed + #main-wrapper { margin-left: 85px; width: calc(100% - 85px); }
        .content-body { padding: 30px 45px !important; }
        
        @media (max-width: 991px) { 
            .sidebar { position: fixed; top: 0; left: 0; width: 260px !important; height: 100%; z-index: 1050; transform: translateX(-100%); transition: 0.3s; } 
            .sidebar.collapsed { transform: translateX(0); } 
            #mainWrapper { margin-left: 0 !important; padding: 20px 15px; } 
        } 

        .section-card { background: white; padding: 30px; border-radius: 18px; box-shadow: 0 5px 20px rgba(0,0,0,0.04); margin-bottom: 25px; border: 1px solid #edf2f7; }
        .pwt-item { cursor: pointer; border: 1px solid #e2e8f0; border-radius: 15px; padding: 20px; transition: all 0.3s ease; background: #fff; text-align: center; }
        .pwt-item:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.08); border-color: #3b82f6; }
        .pwt-item.active { border: 2.5px solid #3b82f6; background-color: #eff6ff; }
        .checklist-section { border: 1px dashed #cbd5e1; padding: 25px; border-radius: 15px; background-color: #fafbfc; }
        .sub-judul-checklist { font-weight: 800; border-bottom: 2px solid #eee; margin-bottom: 12px; margin-top: 20px; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .pdf-frame { width: 100%; height: 500px; border: 1px solid #ddd; border-radius: 12px; }
        .signature-box { border: 1px solid #cbd5e1; background: #ffffff; border-radius: 12px; margin-top: 10px; }
        canvas { width: 100% !important; height: auto !important; }
        .other-input-container { margin-top: 8px; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .btn-primary { background-color: #0070c0; border: none; padding: 12px; border-radius: 10px; font-weight: 700; transition: 0.3s; }
        .btn-primary:hover { background-color: #005a9c; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,112,192,0.3); }
        .d-none { display: none !important; }
    </style>
</head>

<body class="ptw-page">
    @include('layouts.sidebar')
    
    <div id="main-wrapper">
        @include('layouts.header')
        
        <div class="content-body">
            <h3 class="fw-bold mb-4" style="color: #003380;">Permit To Work Application</h3>
            
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm mb-4 border-0" style="border-radius: 12px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="permitForm" action="{{ route('permits.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #3b82f6; padding-left: 10px;">Select Permit Type</div>
                    <div class="row g-3">
                        @php
                        $types=[['Cold Work','❄️'], ['Hot Work','🔥'], ['Electrical','⚡'], ['Working at Height','🪜'], ['Lifting Operation','🏗️'], ['Excavation','🚜'], ['Confined Space','📦']];
                        @endphp
                        @foreach($types as $type)
                        <div class="col-lg-3 col-md-4 col-6">
                            <label class="pwt-item w-100" id="label-{{ Str::slug($type[0]) }}">
                                <input type="checkbox" name="permit_type[]" value="{{ $type[0] }}" class="d-none" onchange="togglePWT(this)">
                                <div style="font-size:2.5rem">{{ $type[1] }}</div>
                                <div class="small fw-bold mt-2">PTW - {{ $type[0] }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #3b82f6; padding-left: 10px;">Work Details & Personnel</div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="small fw-bold text-muted">Start Date</label><input type="datetime-local" name="valid_from" class="form-control" required></div>
                        <div class="col-md-3"><label class="small fw-bold text-muted">Until Date</label><input type="datetime-local" name="valid_until" class="form-control" required></div>
                        <div class="col-md-6"><label class="small fw-bold text-muted">PIC Lead</label><input type="text" name="pic_lead" class="form-control" required></div>
                        <div class="col-md-6"><label class="small fw-bold text-muted">PIC Batamindo</label><input type="text" name="pic_batamindo" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Applicant Name</label><input type="text" name="applicant_name" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Company</label><input type="text" name="company" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Phone Number</label><input type="text" name="phone" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Man Power</label><input type="number" name="man_power" class="form-control" required></div>
                        <div class="col-md-8"><label class="small fw-bold text-muted">Work Location</label><input type="text" name="location" class="form-control" required></div>
                        <div class="col-md-8"><label class="small fw-bold text-muted">General Work Scope</label><input type="text" name="work_scope_general" class="form-control" required></div>
                        <div class="col-md-4"><label class="small fw-bold text-muted">Referensi Dokumen</label><input type="text" name="ref_doc" class="form-control" placeholder="(SOP, IK, ERP, dll)"></div>
                        <div class="col-md-12 mt-3"><label class="small fw-bold text-muted">Tools / Equipment Used</label><div id="editor-tools" style="height:120px;background:#fff; border-radius: 0 0 10px 10px;"></div><input type="hidden" name="tools_used" id="tools_used"></div>
                        <div class="col-md-12 mt-3"><label class="small fw-bold text-muted">Detailed Work Scope</label><div id="editor-scope" style="height:120px;background:#fff; border-radius: 0 0 10px 10px;"></div><input type="hidden" name="work_scope_detail" id="work_scope_detail"></div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #3b82f6; padding-left: 10px;">Risk Mitigation</div>
                    
                    <label class="fw-bold mb-3">Hazards Identification <span class="text-danger fw-bold">*</span></label>
                    <div class="row mb-4">
                        @foreach(\App\Models\Permit::getHazardList() as $hazard)
                        <div class="col-md-4 col-6 mb-2 hazard-container">
                            <div class="form-check"><input type="checkbox" name="hazards[]" value="{{ $hazard }}" class="form-check-input risk-checkbox"><label class="form-check-label small">{{ $hazard }}</label></div>
                            @if(str_contains(strtolower($hazard), 'lainnya'))
                            <div class="other-input-container d-none">
                                <input type="text" name="hazards_other" class="form-control form-control-sm" placeholder="Sebutkan lainnya...">
                            </div>
                            <script>
                            (function() {
                                const container = document.currentScript.parentElement.querySelector('.other-input-container');
                                if (!container) return;
                                const inputField = container.querySelector('input, textarea');
                                const hasValue = inputField && inputField.value.trim() !== '';
                                if (hasValue) {
                                    container.classList.remove('d-none');
                                } else {
                                    container.classList.add('d-none');
                                }
                            })();
                            </script>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <label class="fw-bold mb-3 border-top pt-4">PPE (Personal Protective Equipment) <span class="text-danger fw-bold">*</span></label>
                    <div class="row mb-4">
                        @foreach(\App\Models\Permit::getPpeList() as $category=>$items)
                        @php $catSlug = Str::slug($category); @endphp
                        
                        {{-- Category Header with N/A Checkbox --}}
                        <div class="col-12 mb-2 d-flex align-items-center justify-content-between">
                            <div class="fw-bold small text-primary">{{ $category }}</div>
                            <div class="form-check">
                                <input type="checkbox" 
                                    name="ppe_na[]" 
                                    value="{{ $category }}" 
                                    id="ppe_na_{{ $catSlug }}" 
                                    class="form-check-input ppe-na-checkbox"
                                    data-category="{{ $catSlug }}">
                                <label class="form-check-label small" for="ppe_na_{{ $catSlug }}">N/A</label>
                            </div>
                        </div>
                        
                        {{-- PPE Items Wrapper --}}
                        <div class="col-12 ppe-items-wrapper" data-category="{{ $catSlug }}">
                            @foreach($items as $ppe)
                            <div class="col-md-4 col-6 mb-1 ppe-container d-inline-block" style="width: calc(33.333% - 8px); margin-right: 8px;">
                                <div class="form-check"><input type="checkbox" name="ppe[]" value="{{ $ppe }}" class="form-check-input risk-checkbox"><label class="form-check-label small">{{ $ppe }}</label></div>
                                @if(str_contains(strtolower($ppe), 'lainnya'))
                                <div class="other-input-container d-none">
                                    <input type="text" name="ppe_other[{{ $catSlug }}]" class="form-control form-control-sm" placeholder="Sebutkan lainnya...">
                                </div>
                                <script>
                                (function() {
                                    const container = document.currentScript.parentElement.querySelector('.other-input-container');
                                    if (!container) return;
                                    const inputField = container.querySelector('input, textarea');
                                    const hasValue = inputField && inputField.value.trim() !== '';
                                    if (hasValue) {
                                        container.classList.remove('d-none');
                                    } else {
                                        container.classList.add('d-none');
                                    }
                                })();
                                </script>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>

                    <div id="checklist-master-wrapper">
                        <label class="fw-bold mb-3 d-block">Safety Checklist <span class="text-danger fw-bold">*</span></label>
                        @foreach(\App\Models\Permit::getMasterChecklist() as $ptwType => $categories)
                            @php $theme = \App\Models\Permit::getPermitTheme($ptwType); @endphp
                            
                            <div class="checklist-section d-none mb-4" id="section-{{ Str::slug($ptwType) }}">
                                <div class="p-3 mb-3 rounded-3 text-center fw-bold shadow-sm" style="background-color: {{ $theme['bg'] }} !important; color: {{ $theme['text'] }} !important;">
                                    Permit to Work - {{ $theme['label'] }} Checklist
                                </div>
                                
                                @foreach($categories as $subJudul => $questions)
                                    @php
                                        $isTrigger = str_contains($subJudul, 'Bagian 1');
                                        $isTarget = str_contains($subJudul, 'Bagian 2');
                                    @endphp
                                    {{-- Wrapper untuk logika Trigger/Target --}}
                                    <div class="sub-category-wrapper {{ $isTarget ? 'd-none' : '' }}" data-role="{{ $isTrigger ? 'trigger' : ($isTarget ? 'target' : 'none') }}">
                                        <div class="sub-judul-checklist" style="color: {{ $theme['bg'] }}; filter: brightness(0.6);">{{ $subJudul }}</div>
                                        <div class="row">
                                            @foreach($questions as $q)
                                                @php
                                                    $textValue = is_array($q) ? ($q['text'] ?? 'Tanpa Nama') : $q;
                                                    $hasAdditional = is_array($q) && isset($q['input_tambahan']);
                                                    $isLainnya = str_contains(strtolower($textValue), 'lainnya');
                                                    $isBypass = str_contains(strtolower($textValue), 'bypass'); 
                                                @endphp
                                                
                                                <div class="col-md-6 mb-2 checklist-container">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="safety_checklists[]" value="{{ $textValue }}" class="form-check-input risk-checkbox" @if($isBypass) data-is-bypass="true" @endif> 
                                                        <label class="form-check-label small text-muted">{{ $textValue }}</label>
                                                    </div>
                                                    
                                                    @if(!$isBypass || !$hasAdditional)
                                                        <div class="other-input-container d-none mt-1">
                                                            @if($hasAdditional)
                                                                <label class="small fw-bold text-muted d-block">{{ $q['input_tambahan']['label'] }}</label>
                                                                @php $fieldName = $q['input_tambahan']['name']; @endphp
                                                                @if($q['input_tambahan']['type'] === 'textarea')<textarea name="safety_checklists_other[{{ $fieldName }}]" class="form-control form-control-sm"></textarea>
                                                                @else<input type="text" name="safety_checklists_other[{{ $fieldName }}]" class="form-control form-control-sm">@endif
                                                            @elseif($isLainnya)
                                                                @php $chkSlug = Str::slug($subJudul); @endphp
                                                                <input type="text" name="safety_checklists_other[{{ $chkSlug }}]" class="form-control form-control-sm" placeholder="Sebutkan lainnya...">
                                                            @endif
                                                        </div>
                                                        <script>
                                                        (function() {
                                                            const container = document.currentScript.parentElement.querySelector('.other-input-container');
                                                            if (!container) return;
                                                            const inputField = container.querySelector('input, textarea');
                                                            const hasValue = inputField && inputField.value.trim() !== '';
                                                            if (hasValue) {
                                                                container.classList.remove('d-none');
                                                            } else {
                                                                container.classList.add('d-none');
                                                            }
                                                        })();
                                                        </script>
                                                    @elseif($isBypass && $hasAdditional)
                                                        {{-- Bypass with input_tambahan akan di-render sekali saja di bawah semua items --}}
                                                    @endif
                                                </div>
                                            @endforeach

                                            {{-- Render Shared Bypass Container sekali saja setelah semua items --}}
                                            @php
                                                $hasBypassWithAdditional = collect($questions)->contains(function($q) {
                                                    $text = is_array($q) ? ($q['text'] ?? '') : $q;
                                                    $isBypass = str_contains(strtolower($text), 'bypass');
                                                    $hasAdditional = is_array($q) && isset($q['input_tambahan']);
                                                    return $isBypass && $hasAdditional;
                                                });
                                            @endphp
                                            
                                            @if($hasBypassWithAdditional)
                                                <div class="col-12 shared-bypass-container d-none mt-3 p-3 border rounded bg-light">
                                                    <label class="fw-bold small text-muted d-block">Rencana durasi Bypass (Jam) *</label>
                                                    <input type="number" name="rencana_durasi_bypass_jam" class="form-control form-control-sm" placeholder="Masukkan durasi dalam jam...">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <label class="small fw-bold">Instruksi Tambahan</label>
                        <textarea name="additional_instructions" class="form-control mb-3" rows="3"></textarea>
                        <label class="small fw-bold">HSE Representative</label>
                        <input type="text" name="hse_representative" class="form-control" required>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title mb-1 fw-bold" style="color: #003380; border-left: 4px solid #3b82f6; padding-left: 10px;">Documents Upload</div>
                    <div class="mb-4" style="padding-left: 14px;"><small class="text-muted italic">* Seluruh dokumen wajib diunggah dalam format <strong>PDF</strong>.</small></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="small fw-bold">JSA File</label><input type="file" name="jsa_file" class="form-control" accept=".pdf" required></div>
                        <div class="col-md-6"><label class="small fw-bold">HIRADC File</label><input type="file" name="hiradc_file" class="form-control" accept=".pdf"required></div>
                        <div class="col-md-6"><label class="small fw-bold">Daftar Pekerja</label><input type="file" name="worker_list_file" class="form-control" accept=".pdf" required></div>
                        <div class="col-md-6"><label class="small fw-bold">Sertifikat Kompetensi</label><input type="file" name="competency_cert_file" class="form-control" accept=".pdf" required></div>
                        <div class="col-md-6"><label class="small fw-bold">Prosedur Kerja</label><input type="file" name="work_procedure_file" class="form-control" accept=".pdf" required></div>
                        <div class="col-md-6 mb-3"><label class="small fw-bold">Sertifikat Alat, Bahan, dan Inspeksi Peralatan Listrik</label><input type="file" name="tool_cert_file" class="form-control" accept=".pdf" required></div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #3b82f6; padding-left: 10px;">Pernyataan dan Persetujuan</div>
                    <div class="mb-4"><iframe id="pdf-frame" class="pdf-frame" src="{{ asset('documents/peraturan_k3.pdf') }}"></iframe></div>
                    <div class="text-center py-4 bg-light rounded mb-4 shadow-sm border">
                        <h6 class="fw-bold mb-2">Pernyataan Perusahaan</h6>
                        <div class="form-check d-flex justify-content-center"><input type="checkbox" name="agreed_to_terms" class="form-check-input me-2" id="agreeCompany" required><label class="form-check-label fw-bold" for="agreeCompany">Setuju</label></div>
                    </div>
                    <div class="text-center py-4 bg-light rounded mb-5 shadow-sm border">
                        <h6 class="fw-bold mb-2">Permit Applicant</h6>
                        <div class="form-check d-flex justify-content-center"><input type="checkbox" name="applicant_confirmation" class="form-check-input me-2" id="agreeApplicant" required><label class="form-check-label fw-bold" for="agreeApplicant">Setuju</label></div>
                    </div>
                    <div class="row g-4 pt-4 border-top">
                        <div class="col-md-6 text-center"><label class="fw-bold small mb-2">Nama Pimpinan / Manager</label><input type="text" name="manager_name" class="form-control text-center mb-2" required><div class="signature-box"><canvas id="padM" height="150"></canvas></div><button type="button" class="btn btn-sm text-danger mt-2" onclick="clearPad('M')"><i class="fas fa-trash me-1"></i>Hapus</button><input type="hidden" name="signature_manager" id="sm"></div>
                        <div class="col-md-6 text-center"><label class="fw-bold small mb-2">Nama Permit Applicant</label><input type="text" name="applicant_confirm_name" class="form-control text-center mb-2" required><div class="signature-box"><canvas id="padA" height="150"></canvas></div><button type="button" class="btn btn-sm text-danger mt-2" onclick="clearPad('A')"><i class="fas fa-trash me-1"></i>Hapus</button><input type="hidden" name="signature_applicant" id="sa"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 mt-5 fw-bold shadow">KIRIM PENGAJUAN PERMIT</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
    // 1. Logika Checkbox & Visibility (Bagian 1 -> 2, Input Lainnya, Bypass)
    document.addEventListener('change', function(e) {
    
    // ===== N/A CHECKBOX LOGIC (NEW) =====
    if (e.target.classList.contains('ppe-na-checkbox')) {
        const naCheckbox = e.target;
        const category = naCheckbox.dataset.category;
        const wrapper = document.querySelector(`.ppe-items-wrapper[data-category="${category}"]`);
        
        if (wrapper) {
            if (naCheckbox.checked) {
                // Hide items & uncheck all checkboxes
                wrapper.style.display = 'none';
                wrapper.querySelectorAll('input[type="checkbox"]:not([name="ppe_na[]"])').forEach(cb => {
                    cb.checked = false;
                });
            } else {
                // Show items again
                wrapper.style.display = 'block';
            }
        }
    }
    
    if (e.target.classList.contains('risk-checkbox')) {
        const checkbox = e.target;
        const section = checkbox.closest('.checklist-section');
        const parentWrapper = checkbox.closest('.sub-category-wrapper');

        // 1. Logika Trigger BAGIAN 1 -> BAGIAN 2
        if (parentWrapper && parentWrapper.dataset.role === 'trigger') {
            const target = section.querySelector('.sub-category-wrapper[data-role="target"]');
            if (target) {
                const hasChecked = Array.from(parentWrapper.querySelectorAll('.risk-checkbox')).some(cb => cb.checked);
                target.classList.toggle('d-none', !hasChecked);
                if (!hasChecked) target.querySelectorAll('.risk-checkbox').forEach(cb => cb.checked = false);
            }
        }

        // 2. Logika Input Lainnya (Versi Gabungan/Clean)
        // Kita gunakan koma untuk menyeleksi semua jenis container sekaligus
        const riskItem = checkbox.closest('.checklist-container, .hazard-container, .ppe-container');
        if (riskItem) {
            const otherInput = riskItem.querySelector('.other-input-container');
            if (otherInput) {
                otherInput.classList.toggle('d-none', !checkbox.checked);
            }
        }

        // 3. Logika Shared Bypass Container - Toggle jika ada bypass item yang checked
        if (checkbox.dataset.isBypass === 'true') {
            const section = checkbox.closest('.checklist-section');
            if (section) {
                const bypassContainer = section.querySelector('.shared-bypass-container');
                if (bypassContainer) {
                    const anyBypassChecked = Array.from(section.querySelectorAll('[data-is-bypass="true"]')).some(cb => cb.checked);
                    bypassContainer.classList.toggle('d-none', !anyBypassChecked);
                }
            }
        }
    }
});

    // 2. Initial Setup untuk PWT Type & Signatures
    function togglePWT(input) {
        const slug = input.value.toLowerCase().replace(/ /g, '-');
        const section = document.getElementById('section-' + slug);
        const label = document.getElementById('label-' + slug);
        if (input.checked) {
            label.classList.add('active');
            if (section) section.classList.remove('d-none');
        } else {
            label.classList.remove('active');
            if (section) section.classList.add('d-none');
        }
    }

    const canvasM = document.getElementById('padM');
    const canvasA = document.getElementById('padA');
    const padM = canvasM ? new SignaturePad(canvasM) : null;
    const padA = canvasA ? new SignaturePad(canvasA) : null;

    function resizeCanvas() {
        [canvasM, canvasA].forEach(canvas => {
            if (!canvas) return;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
        });
    }
    window.addEventListener("resize", resizeCanvas);
    resizeCanvas();

    // Initialize all shared bypass containers on page load
    document.querySelectorAll('.shared-bypass-container').forEach(container => {
        const section = container.closest('.checklist-section');
        if (section) {
            const anyBypassChecked = Array.from(section.querySelectorAll('[data-is-bypass="true"]:checked')).length > 0;
            if (anyBypassChecked) {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
    });

    // Initialize checklist .other-input-container visibility on page load
    document.querySelectorAll('.checklist-container').forEach(container => {
        const checkbox = container.querySelector('input[type="checkbox"]');
        const otherInputContainer = container.querySelector('.other-input-container');
        
        if (!otherInputContainer || !checkbox) return;
        
        // Show container ONLY jika checkbox is checked
        if (checkbox.checked) {
            otherInputContainer.classList.remove('d-none');
        } else {
            otherInputContainer.classList.add('d-none');
        }
    });

    const quillTools = new Quill('#editor-tools', { theme: 'snow' });
    const quillScope = new Quill('#editor-scope', { theme: 'snow' });

    function clearPad(type) {
        if (type === 'M' && padM) padM.clear();
        if (type === 'A' && padA) padA.clear();
    }

    // 3. Logika Submit (VALIDASI DITAMBAHKAN DI SINI)
    // Pastikan ID form benar
    const permitForm = document.getElementById('permitForm');
    if (permitForm) {
        permitForm.addEventListener('submit', function(e) {
            // Debugging: Cek apakah script jalan
            console.log("Submit ditekan, memulai validasi...");

            // 0. Validasi Hazards, PPE, dan Safety Checklists (BARU)
            const hazardsChecked = document.querySelectorAll('input[name="hazards[]"]:checked').length;
            const ppeChecked = document.querySelectorAll('input[name="ppe[]"]:checked').length;
            const safetyChecklistsChecked = document.querySelectorAll('input[name="safety_checklists[]"]:checked').length;

            if (hazardsChecked === 0) {
                e.preventDefault();
                alert('⚠️ Gagal: Minimal harus memilih 1 Hazard (Risiko) di bagian "3. IDENTIFIED HAZARDS"!');
                document.querySelector('input[name="hazards[]"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (ppeChecked === 0) {
                e.preventDefault();
                alert('⚠️ Gagal: Minimal harus memilih 1 PPE (Alat Pelindung Diri) di bagian "4. REQUIRED PPE"!');
                document.querySelector('input[name="ppe[]"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (safetyChecklistsChecked === 0) {
                e.preventDefault();
                alert('⚠️ Gagal: Minimal harus memilih 1 Safety Checklist di bagian "5. SAFETY CHECKLISTS & PERSONNEL"!');
                document.querySelector('input[name="safety_checklists[]"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // 1. Validasi Quill
            const toolsText = quillTools.getText().trim();
            const scopeText = quillScope.getText().trim();

            if (toolsText === '') {
                e.preventDefault(); // Harus ada
                alert('Gagal: Kolom "Tools / Equipment Used" wajib diisi!');
                quillTools.focus();
                return false; // Berhenti di sini
            }

            if (scopeText === '') {
                e.preventDefault();
                alert('Gagal: Kolom "Detailed Work Scope" wajib diisi!');
                quillScope.focus();
                return false;
            }

            // 2. Validasi Tanda Tangan
            if (padM && padM.isEmpty()) {
                e.preventDefault();
                alert('Gagal: Tanda tangan Manager wajib diisi!');
                return false;
            }
            
            if (padA && padA.isEmpty()) {
                e.preventDefault();
                alert('Gagal: Tanda tangan Applicant wajib diisi!');
                return false;
            }

            // 3. Masukkan data ke input hidden (PENTING)
            document.getElementById('tools_used').value = quillTools.root.innerHTML;
            document.getElementById('work_scope_detail').value = quillScope.root.innerHTML;
            
            // Tanda tangan
            document.getElementById('sm').value = padM.toDataURL('image/png');
            document.getElementById('sa').value = padA.toDataURL('image/png');

            // 4. Mencegah Double Submit
            const btnSubmit = document.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerText = "Processing...";
            }
            
            console.log("Validasi lolos, form dikirim.");
        });
    }
</script>
</body>
</html>