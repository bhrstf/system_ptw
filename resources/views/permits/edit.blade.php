@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Permit - PTW System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/ptw.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    
    <style>
        /* ============================================================
            SIDEBAR & CONTENT ENGINE (UTUH)
           ============================================================ */
        body.ptw-page { 
            margin: 0; padding: 0; overflow-x: hidden; 
            display: flex; background-color: #f4f7f6; 
        }

        .sidebar { 
            width: 260px; height: 100vh; position: fixed; 
            left: 0; top: 0; z-index: 1100; 
            transition: all 0.35s; background: #003380; 
            overflow-y: auto; 
        }

        .sidebar.collapsed { width: 85px; }

        #main-wrapper { 
            flex: 1; margin-left: 260px; min-height: 100vh; 
            transition: all 0.35s; width: calc(100% - 260px); 
        }

        .sidebar.collapsed + #main-wrapper { 
            margin-left: 85px; width: calc(100% - 85px); 
        }

        .content-body { padding: 30px 45px !important; }

        /* ============================================================
            UI COMPONENTS (PWT ITEMS, CARDS, OTHERS)
           ============================================================ */
        .section-card { 
            background: white; padding: 30px; border-radius: 18px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.04); margin-bottom: 25px; 
            border: 1px solid #edf2f7; 
        }

        .pwt-item { 
            cursor: pointer; border: 1px solid #e2e8f0; border-radius: 15px; 
            padding: 20px; transition: 0.3s; background: #fff; text-align: center; 
        }

        .pwt-item.active { 
            border: 2.5px solid #f59e0b; 
            background-color: #fffbeb; 
        }

        .checklist-section { 
            border: 1px dashed #cbd5e1; padding: 25px; 
            border-radius: 15px; background-color: #fafbfc; 
        }

        .sub-judul-checklist { 
            font-weight: 800; border-bottom: 2px solid #eee; 
            margin-bottom: 12px; margin-top: 20px; 
            text-transform: uppercase; font-size: 0.75rem; 
            letter-spacing: 0.5px;
        }

        .signature-box { 
            border: 1px solid #cbd5e1; background: #f8fafc; 
            border-radius: 12px; position: relative; 
        }

        canvas { width: 100% !important; height: auto !important; }

        .btn-update { 
            background-color: #f59e0b; border: none; padding: 15px; 
            border-radius: 12px; font-weight: 800; color: #fff; 
            width: 100%; transition: 0.3s; 
        }

        .btn-update:hover { 
            background-color: #d97706; transform: translateY(-2px); 
        }

        .other-input-container { margin-top: 8px; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body class="ptw-page">

    @include('layouts.sidebar')

    <div id="main-wrapper">
        @include('layouts.header')

        <div class="content-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0" style="color: #003380;">Edit Permit <span class="text-warning">#PTW-00{{ $permit->id }}</span></h3>
                <span class="badge bg-warning text-dark p-2 px-3 rounded-pill fw-bold">Status: {{ $permit->status }}</span>
            </div>

            <form id="permitForm" action="{{ route('permits.update', $permit->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Section 1: Permit Type --}}
                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #f59e0b; padding-left: 10px;">Select Permit Type</div>
                    <div class="row g-3">
                        @php
                        $types=[
                            ['Cold Work','❄️'], ['Hot Work','🔥'], ['Electrical','⚡'], 
                            ['Working at Height','🪜'], ['Lifting Operation','🏗️'], 
                            ['Excavation','🚜'], ['Confined Space','📦']
                        ];
                        $currentTypes = is_array($permit->permit_type) ? $permit->permit_type : json_decode($permit->permit_type, true) ?? [];
                        @endphp
                        @foreach($types as $type)
                        <div class="col-lg-3 col-md-4 col-6">
                            <label class="pwt-item w-100 {{ in_array($type[0], $currentTypes) ? 'active' : '' }}" id="label-{{ Str::slug($type[0]) }}">
                                <input type="checkbox" name="permit_type[]" value="{{ $type[0] }}" class="d-none" onchange="togglePWT(this)" {{ in_array($type[0], $currentTypes) ? 'checked' : '' }}>
                                <div style="font-size:2.5rem">{{ $type[1] }}</div>
                                <div class="small fw-bold mt-2">PTW - {{ $type[0] }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Section 2: Work Details --}}
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
                        @php $curHazards = $permit->hazards ?? []; @endphp
                        @foreach(\App\Models\Permit::getHazardList() as $hazard)
                        <div class="col-md-4 col-6 mb-2 hazard-container">
                            <div class="form-check">
                                <input type="checkbox" name="hazards[]" value="{{ $hazard }}" class="form-check-input risk-checkbox" {{ in_array($hazard, $curHazards) ? 'checked' : '' }}>
                                <label class="form-check-label small">{{ $hazard }}</label>
                            </div>
                            @if(str_contains(strtolower($hazard), 'lainnya'))
                            <div class="other-input-container {{ $permit->hazards_other ? '' : 'd-none' }}">
                                <input type="text" name="hazards_other" class="form-control form-control-sm" value="{{ $permit->hazards_other }}" placeholder="Sebutkan lainnya...">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <label class="fw-bold mb-3 border-top pt-4">PPE (Personal Protective Equipment)</label>
                    <div class="row mb-4">
                        @php 
                            $curPpe = $permit->ppe ?? []; 
                            // Pastikan ppe_other adalah array, jika null buat array kosong
                            $ppeOtherData = $permit->ppe_other ?? []; 
                        @endphp

                        @foreach(\App\Models\Permit::getPpeList() as $category => $items)
                            <div class="col-12 fw-bold small text-primary mt-2 mb-2">{{ $category }}</div>
                            @php $catSlug = Str::slug($category); @endphp
                            
                            @foreach($items as $ppe)
                                @php
                                    $isLainnya = str_contains(strtolower($ppe), 'lainnya');
                                    // Jika Lainnya, tambahkan slug kategori agar unik (misal: "Lainnya_kepala-wajah")
                                    $uniqueValue = $isLainnya ? 'Lainnya_' . $catSlug : $ppe;
                                    
                                    // Cek apakah data ini ada di array $curPpe
                                    $isChecked = in_array($uniqueValue, $curPpe);
                                @endphp

                                <div class="col-md-4 col-6 mb-1 ppe-container">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                            name="ppe[]" 
                                            value="{{ $uniqueValue }}" 
                                            class="form-check-input risk-checkbox" 
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label small">{{ $ppe }}</label>
                                    </div>
                                    
                                    @if($isLainnya)
                                        {{-- Input teks ini hanya tampil jika checkboxnya tercentang --}}
                                        <div class="other-input-container {{ $isChecked ? '' : 'd-none' }}">
                                            <input type="text" 
                                                name="ppe_other[{{ $catSlug }}]" 
                                                class="form-control form-control-sm" 
                                                value="{{ $permit->ppe_other[$catSlug] ?? '' }}" 
                                                placeholder="Sebutkan lainnya...">
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div id="checklist-master-wrapper">
                    @php $curChecklist = is_array($permit->safety_checklists) ? $permit->safety_checklists : json_decode($permit->safety_checklists, true) ?? []; @endphp

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
                                    
                                    // Ini logika untuk menentukan apakah section ini adalah Cold Work yang spesifik
                                    $isColdWorkSection = str_contains(strtolower($ptwType), 'cold work') && 
                                        (str_contains(strtolower($subJudul), 'manual excavation') || 
                                        str_contains(strtolower($subJudul), 'non-critical lifting') || 
                                        str_contains(strtolower($subJudul), 'simple isolation'));

                                    // PERBAIKAN LOGIKA VISIBILITAS: Cek apakah ada yang tercentang di section ini
                                    $anyChecked = false;
                                    foreach($questions as $q) {
                                        $qVal = is_array($q) ? ($q['text'] ?? 'Tanpa Nama') : $q;
                                        if(in_array($qVal, $curChecklist)) { 
                                            $anyChecked = true; 
                                            break; 
                                        }
                                    }
                                @endphp
                                
                                <div class="sub-judul-checklist" style="color: {{ $theme['bg'] }}; filter: brightness(0.6);">{{ $subJudul }}</div>
                                
                                {{-- Perbaikan: Gunakan logic $anyChecked agar data yg sudah tersimpan tidak tersembunyi --}}
                                <div class="row" @if($isBagian2) style="display: {{ $anyChecked ? 'flex' : 'none' }};" data-bagian="bagian2" @endif data-cold-work-section="{{ $isColdWorkSection ? 'true' : 'false' }}">
                                    @foreach($questions as $q)
                                        @php
                                            $textValue = is_array($q) ? ($q['text'] ?? 'Tanpa Nama') : $q;
                                            $hasAdditional = is_array($q) && isset($q['input_tambahan']);
                                            $isLainnya = str_contains(strtolower($textValue), 'lainnya');
                                        @endphp
                                        
                                        <div class="col-md-6 mb-2 checklist-container">
                                            <div class="form-check">
                                                <input type="checkbox" name="safety_checklists[]" value="{{ $textValue }}" class="form-check-input risk-checkbox" {{ in_array($textValue, $curChecklist) ? 'checked' : '' }} @if($isColdWorkSection) data-cold-work-check="true" @endif>
                                                <label class="form-check-label small text-muted">{{ $textValue }}</label>
                                            </div>
                                            
                                            <div class="other-input-container mt-1 {{ in_array($textValue, $curChecklist) ? '' : 'd-none' }}">
                                                @if($hasAdditional)
                                                    @php $fieldName = $q['input_tambahan']['name']; @endphp
                                                    <label class="small fw-bold text-muted d-block">{{ $q['input_tambahan']['label'] }}</label>
                                                    <input type="text" name="{{ $fieldName }}" class="form-control form-control-sm" value="{{ old($fieldName, $permit->$fieldName ?? '') }}">
                                                @elseif($isLainnya)
                                                    @php $chkSlug = Str::slug($subJudul); @endphp
                                                    <input type="text" name="safety_checklists_other[{{ $chkSlug }}]" class="form-control form-control-sm" value="{{ $permit->safety_checklists_other[$chkSlug] ?? '' }}" placeholder="Sebutkan lainnya...">
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                            
                            {{-- Perbaikan: ID unik agar tidak konflik --}}
                            @if(str_contains(strtolower($ptwType), 'cold work'))
                                <div id="cold-work-bypass-container-{{ Str::slug($ptwType) }}" style="display: {{ in_array('Bypass/Isolation', $curChecklist) ? 'block' : 'none' }}; margin-top: 20px; padding: 15px; background: #e8f4f8; border-radius: 8px;">
                                    <label class="small fw-bold text-muted mb-2">Rencana Durasi Bypass (Jam)</label>
                                    <input type="number" name="rencana_durasi_bypass_jam" class="form-control form-control-sm" value="{{ old('rencana_durasi_bypass_jam', $permit->rencana_durasi_bypass_jam) }}">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Section 4: Documents --}}
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

                {{-- Section 5: Signature --}}
                <div class="section-card">
                    <div class="section-title mb-4 fw-bold" style="color: #003380; border-left: 4px solid #f59e0b; padding-left: 10px;">Pernyataan & Tanda Tangan</div>
                    <div class="row g-4">
                        <div class="col-md-6 text-center">
                            <label class="fw-bold small mb-2">Nama Pimpinan / Manager</label>
                            <input type="text" name="manager_name" class="form-control text-center mb-2" value="{{ $permit->manager_name }}" required>
                            <div class="signature-box">
                                <canvas id="padM" height="150"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm text-danger mt-2" onclick="clearPad('M')"><i class="fas fa-trash me-1"></i>Hapus</button>
                            <input type="hidden" name="signature_manager" id="sm" value="{{ $permit->signature_manager }}">
                        </div>
                        <div class="col-md-6 text-center">
                            <label class="fw-bold small mb-2">Nama Permit Applicant</label>
                            <input type="text" name="applicant_confirm_name" class="form-control text-center mb-2" value="{{ $permit->applicant_confirm_name }}" required>
                            <div class="signature-box">
                                <canvas id="padA" height="150"></canvas>
                            </div>
                            <button type="button" class="btn btn-sm text-danger mt-2" onclick="clearPad('A')"><i class="fas fa-trash me-1"></i>Hapus</button>
                            <input type="hidden" name="signature_applicant" id="sa" value="{{ $permit->signature_applicant }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-update mt-5 shadow">SIMPAN PERUBAHAN PERMIT</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

    <script>
        // INIT QUILL
        const quillTools = new Quill('#editor-tools', { theme: 'snow' });
        const quillScope = new Quill('#editor-scope', { theme: 'snow' });

        // INIT SIGNATURE PADS
        const canvasM = document.getElementById('padM');
        const canvasA = document.getElementById('padA');
        const padM = new SignaturePad(canvasM);
        const padA = new SignaturePad(canvasA);

        // FUNGSI RESIZE AGAR GOESAN PAS SAMA KURSOR
        function resizeCanvas() {
            [canvasM, canvasA].forEach(canvas => {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                
                // Simpan data lama sebelum resize supaya gak hilang waktu di-scale
                let data = null;
                let pad = canvas.id === 'padM' ? padM : padA;
                if (!pad.isEmpty()) {
                    data = pad.toDataURL();
                }

                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                pad.clear(); 

                // Balikin datanya kalau ada
                if (data) {
                    pad.fromDataURL(data);
                }
            });
        }

        window.addEventListener("resize", resizeCanvas);

        // LOAD SIGNATURE DATA
        window.onload = function() {
            // Panggil resize di awal biar ukuran canvas langsung benar
            resizeCanvas();

            const sigM = document.getElementById('sm').value;
            const sigA = document.getElementById('sa').value;
            if (sigM) padM.fromDataURL(sigM);
            if (sigA) padA.fromDataURL(sigA);
        };

        function clearPad(type) { type === 'M' ? padM.clear() : padA.clear(); }

        // TOGGLE LAINNYA
        document.querySelectorAll('.risk-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const container = this.closest('.hazard-container, .ppe-container, .checklist-container');
                if (container) {
                    const otherInput = container.querySelector('.other-input-container');
                    if (otherInput) {
                        if (this.checked) {
                            otherInput.classList.remove('d-none');
                            const inputField = otherInput.querySelector('input, textarea');
                            if (inputField) inputField.focus();
                        } else {
                            otherInput.classList.add('d-none');
                            const inputField = otherInput.querySelector('input, textarea');
                            if (inputField) inputField.value = '';
                        }
                    }
                }

                // Logic untuk Bagian II: tampilkan ketika ada checkbox di Bagian I yang di-check
                const checklistSection = this.closest('.checklist-section');
                if (checklistSection) {
                    // Cari semua row di dalam section ini
                    const rows = checklistSection.querySelectorAll('.row');
                    
                    rows.forEach(row => {
                        const subJudulElem = row.previousElementSibling;
                        if (subJudulElem && subJudulElem.classList.contains('sub-judul-checklist')) {
                            const subJudulText = subJudulElem.innerText.toLowerCase();
                            
                            // Jika row ini adalah "Bagian 2"
                            if (subJudulText.includes('bagian 2')) {
                                // Cek apakah ada checkbox di "Bagian 1" yang di-check
                                const allCheckboxes = checklistSection.querySelectorAll('.risk-checkbox');
                                let bagian1HasChecked = false;
                                
                                allCheckboxes.forEach(cb => {
                                    const cbRow = cb.closest('.row');
                                    const cbSubJudul = cbRow.previousElementSibling;
                                    if (cbSubJudul && cbSubJudul.innerText.toLowerCase().includes('bagian 1')) {
                                        if (cb.checked) {
                                            bagian1HasChecked = true;
                                        }
                                    }
                                });
                                
                                // Tampilkan/sembunyikan Bagian 2
                                if (bagian1HasChecked) {
                                    row.style.display = 'flex';
                                } else {
                                    row.style.display = 'none';
                                }
                            }
                        }
                    });
                }
            });
        });

        // TOGGLE PTW TYPE
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

        // FORM SUBMIT HANDLING
        document.getElementById('permitForm').onsubmit = function() {
            document.getElementById('tools_used').value = quillTools.root.innerHTML;
            document.getElementById('work_scope_detail').value = quillScope.root.innerHTML;
            document.getElementById('sm').value = padM.isEmpty() ? (document.getElementById('sm').value) : padM.toDataURL();
            document.getElementById('sa').value = padA.isEmpty() ? (document.getElementById('sa').value) : padA.toDataURL();
        };
    </script>
</body>
</html>