@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit Detail - {{ $permit->ptw_number ?? 'PTW-'.str_pad($permit->id, 5, '0', STR_PAD_LEFT) }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body { background-color: #f4f7f6; color: #2d3436; font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
        
        /* --- LAYOUT UTAMA (ANTI NABRAK) --- */
        #mainWrapper { 
            margin-left: 260px; /* Selalu beri jarak selebar sidebar */
            padding: 30px 45px; 
            transition: all 0.3s ease-in-out; 
            min-height: 100vh;
        }
        #mainWrapper.expanded { margin-left: 85px; } /* Jarak saat sidebar dikecilkan */
        
        .detail-card { 
            background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); 
            padding: 40px; margin-bottom: 50px; border: 1px solid #eef2f7;
        }

        .section-header { 
            border-left: 5px solid #0d6efd; padding-left: 15px; margin-top: 40px;
            margin-bottom: 20px; font-weight: 800; text-transform: uppercase; 
            font-size: 0.8rem; letter-spacing: 1.5px; color: #64748b;
        }

        .info-label { font-size: 0.65rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { 
            font-size: 0.9rem; font-weight: 600; background: #f8fafc; padding: 12px 18px; 
            border-radius: 12px; border: 1px solid #f1f5f9; min-height: 48px; color: #1e293b;
        }

        .badge-type { padding: 10px 20px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; border: 2px solid #f1f5f9; background: #fff; color: #94a3b8; }
        .badge-type.active { background: #eff6ff; border-color: #3b82f6; color: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
        .status-badge { padding: 10px 25px; border-radius: 50px; font-weight: 800; font-size: 0.85rem; letter-spacing: 0.5px; }

        .doc-card { border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; background: #fff; transition: 0.3s; position: relative; z-index: 10; }
        .doc-card:hover { border-color: #3b82f6; background: #f8fafc; }

        .signature-box { border: 2px dashed #e2e8f0; border-radius: 18px; padding: 20px; background: #fcfcfc; text-align: center; height: 100%; transition: 0.3s; }
        .signature-box.active-pja { border-color: #10b981; background: #f0fdf4; }
        .signature-img { max-height: 90px; width: auto; mix-blend-mode: multiply; filter: contrast(150%); }

        .box-fill { background: #f8fafc; border-radius: 16px; border: 1px solid #eef2f7; padding: 20px; height: 100%; }

        /* --- RESPONSIVE IPAD & MOBILE FIX --- */
        /* TABLET & MOBILE (≤ 991px) */
@media (max-width: 991px) { 
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 260px !important;
        height: 100%;
        z-index: 1050;
        transform: translateX(-100%); /* sembunyiin */
        transition: 0.3s;
    }

    .sidebar.collapsed {
        transform: translateX(0); /* muncul pas diklik */
    }

    #mainWrapper {
        margin-left: 0 !important; /* biar full width */
        padding: 20px 15px;
    }
}

        @media (max-width: 768px) {
            /* Di HP, sidebar disembunyikan keluar layar, baru konten bisa full width */
            .sidebar { transform: translateX(-100%); z-index: 1050; width: 260px !important; }
            .sidebar.collapsed { transform: translateX(0); } /* Munculkan kalau tombol menu diklik */
            
            #mainWrapper, #mainWrapper.expanded { margin-left: 0 !important; padding: 15px 10px; }
            .detail-card { padding: 25px 15px; }
        }

        @media print { .no-print { display: none !important; } #mainWrapper { margin-left: 0 !important; } }
    </style>
</head>

<body>
    @include('layouts.sidebar')

    <div id="mainWrapper">
        @include('layouts.header')

        <div class="container-fluid p-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 no-print" style="padding-top: 15px;">
                <h3 class="fw-bold mb-0">Permit Overview</h3>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-light border px-4 fw-bold shadow-sm" style="position: relative; z-index: 50;">
                        <i class="fas fa-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="detail-card">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                    <div>
                        @if($permit->ptw_number)
                            <h2 class="fw-extrabold text-primary mb-1">{{ $permit->ptw_number }}</h2>
                        @else
                            <h2 class="fw-extrabold text-dark mb-1">Permit to Work Application</h2>
                        @endif
                    </div>
                    <div>
                        @if($permit->status == 'pending')
                            <span class="status-badge bg-warning text-dark"><i class="fas fa-clock me-2"></i>PENDING</span>
                        @elseif($permit->status == 'approved')
                            <span class="status-badge bg-info text-white"><i class="fas fa-file-signature me-2"></i>APPROVED</span>
                        @elseif($permit->status == 'active')
                            <span class="status-badge bg-success text-white"><i class="fas fa-check-double me-2"></i>ACTIVE</span>
                        @elseif($permit->status == 'rejected')
                            <span class="status-badge bg-danger text-white"><i class="fas fa-times me-2"></i>REJECTED</span>
                        @elseif($permit->status == 'completed')
                            <span class="status-badge bg-primary text-white"><i class="fas fa-check-circle me-2"></i>COMPLETED</span>
                        @elseif($permit->status == 'closed')
                            <span class="status-badge bg-secondary text-white"><i class="fas fa-lock me-2"></i>CLOSED</span>
                        @endif
                    </div>
                </div>

                <div class="section-header">1. Permit Type Selection</div>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    @php $selectedTypes = is_array($permit->permit_type) ? $permit->permit_type : (json_decode($permit->permit_type, true) ?? []); @endphp
                    @foreach(['Cold Work', 'Hot Work', 'Electrical', 'Working at Height', 'Lifting Operation', 'Excavation', 'Confined Space'] as $t)
                        <div class="badge-type {{ in_array($t, $selectedTypes) ? 'active' : '' }}">
                            <i class="fas {{ in_array($t, $selectedTypes) ? 'fa-check-circle' : 'fa-circle' }} me-2"></i> {{ $t }}
                        </div>
                    @endforeach
                </div>

                <div class="section-header">2. Basic Information & Work Scope</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="info-label">Mulai Berlaku</div>
                        <div class="info-value text-primary">{{ \Carbon\Carbon::parse($permit->valid_from)->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Berakhir Pada</div>
                        <div class="info-value text-danger">{{ \Carbon\Carbon::parse($permit->valid_until)->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">PIC / Pengawas Lapangan</div>
                        <div class="info-value">{{ $permit->pic_lead }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">PIC / Pengawas Batamindo</div>
                        <div class="info-value">{{ $permit->pic_batamindo }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-label">Nama Pemohon</div>
                        <div class="info-value">{{ $permit->applicant_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Nama Perusahaan</div>
                        <div class="info-value">{{ $permit->company }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-label">Lokasi Pekerjaan</div>
                        <div class="info-value">{{ $permit->location }}</div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-label">Email Kontak</div>
                        <div class="info-value">{{ $permit->email }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Nomor Telepon</div>
                        <div class="info-value">{{ $permit->phone }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Man Power</div>
                        <div class="info-value">{{ $permit->man_power }} Orang</div>
                    </div>

                    @php
                        $rawTools = $permit->tools_used ?? '';
                        $toolsArr = [];
                        if (is_array($rawTools)) {
                            $toolsArr = $rawTools;
                        } else {
                            $decoded = json_decode($rawTools, true);
                            if (is_array($decoded)) {
                                $toolsArr = $decoded;
                            } else {
                                if (strpos($rawTools, '<') !== false) {
                                    preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $rawTools, $m);
                                    if (!empty($m[1])) {
                                        $toolsArr = array_map('trim', array_map('strip_tags', $m[1]));
                                    } else {
                                        $txt = trim(strip_tags($rawTools));
                                        if ($txt !== '') $toolsArr = [$txt];
                                    }
                                } elseif (strpos($rawTools, ',') !== false) {
                                    $toolsArr = array_map('trim', explode(',', $rawTools));
                                } elseif ($rawTools !== '') {
                                    $toolsArr = [trim($rawTools)];
                                }
                            }
                        }
                        $toolsDisplay = !empty($toolsArr) ? implode(', ', $toolsArr) : '-';
                        $detail = $permit->work_scope_detail ?? '';
                        $allowedTags = '<p><br><ul><ol><li><strong><b><em><u>';
                    @endphp

                    <div class="col-md-12">
                        <div class="info-label">Alat & Peralatan yang Digunakan</div>
                        <div class="info-value">{{ $toolsDisplay }}</div>
                    </div>

                    <div class="col-md-12">
                        <div class="info-label">Deskripsi Pekerjaan (Umum)</div>
                        <div class="info-value bg-white text-dark">{{ $permit->work_scope_general }}</div>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="info-label">Referensi Dokumen</div>
                        <div class="info-value">{{ $permit->ref_doc ?? '-' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="info-label">Detail Lingkup Pekerjaan</div>
                        <div class="info-value bg-white" style="height: auto; min-height: 100px;">
                            @if($detail && $detail !== strip_tags($detail))
                                {!! strip_tags($detail, $allowedTags) !!}
                            @else
                                {!! nl2br(e($detail)) !!}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    {{-- HAZARDS SECTION FIX: flex-column & flex-grow-1 --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="section-header text-danger mt-0">3. Identified Hazards</div>
                        <div class="box-fill flex-grow-1">
                            @php 
                                $hazards = is_array($permit->hazards) ? $permit->hazards : (json_decode($permit->hazards, true) ?? []); 
                                $hazards = array_unique(array_map('trim', $hazards)); 
                                
                                $stdHazards = [];
                                $otherHazardText = '';
                                
                                foreach($hazards as $hz) {
                                    if(in_array(strtolower($hz), ['lainnya', 'other', 'others'])) {
                                        $rawOther = is_array($permit->hazard_other) ? $permit->hazard_other : (json_decode($permit->hazard_other ?? $permit->hazards_other, true) ?? [$permit->hazard_other ?? $permit->hazards_other]);
                                        $cleanOther = array_filter(array_map('trim', (array)$rawOther));
                                        $otherHazardText = !empty($cleanOther) ? implode(', ', array_unique($cleanOther)) : '';
                                    } else {
                                        $stdHazards[] = $hz;
                                    }
                                }
                            @endphp
                            
                            @if(!empty($stdHazards))
                                <div class="info-label mb-2 border-bottom pb-1 border-secondary-subtle">Bahaya Teridentifikasi</div>
                                @foreach($stdHazards as $hz)
                                    <div class="mb-2 small fw-bold text-dark"><i class="fas fa-exclamation-triangle text-warning me-2"></i>{{ $hz }}</div>
                                @endforeach
                            @endif
                            
                            @if($otherHazardText)
                                <div class="info-label mt-3 mb-2 border-bottom pb-1 border-secondary-subtle">Bahaya Lainnya</div>
                                <div class="mb-2 small fw-bold text-dark"><i class="fas fa-exclamation-triangle text-warning me-2"></i><span class="text-muted fw-normal">{{ $otherHazardText }}</span></div>
                            @endif
                            
                            @if(empty($stdHazards) && empty($otherHazardText))
                                <div class="text-muted small italic">Tidak ada hazard dipilih.</div>
                            @endif
                        </div>
                    </div>

                    {{-- PPE SECTION FIX: flex-column & flex-grow-1 --}}
                    <div class="col-md-6 d-flex flex-column">
                        <div class="section-header text-success mt-0">4. Required PPE</div>
                        <div class="box-fill flex-grow-1">
                            @php 
                                $ppes = is_array($permit->ppe) ? $permit->ppe : (json_decode($permit->ppe, true) ?? []); 
                                $ppes = array_unique(array_map('trim', $ppes)); 
                                
                                $stdPpes = [];
                                $otherPpeText = '';
                                
                                foreach($ppes as $ppe) {
                                    if(in_array(strtolower($ppe), ['lainnya', 'other', 'others'])) {
                                        $rawOtherPpe = is_array($permit->ppe_other) ? $permit->ppe_other : (json_decode($permit->ppe_other, true) ?? [$permit->ppe_other]);
                                        $cleanOtherPpe = array_filter(array_map('trim', (array)$rawOtherPpe)); 
                                        $otherPpeText = !empty($cleanOtherPpe) ? implode(', ', array_unique($cleanOtherPpe)) : '';
                                    } else {
                                        $stdPpes[] = $ppe;
                                    }
                                }
                            @endphp

                            @if(!empty($stdPpes))
                                <div class="info-label mb-2 border-bottom pb-1 border-secondary-subtle">PPE Utama</div>
                                @foreach($stdPpes as $ppe)
                                    <div class="mb-2 small fw-bold text-dark"><i class="fas fa-shield-alt text-success me-2"></i>{{ $ppe }}</div>
                                @endforeach
                            @endif

                            @if($otherPpeText)
                                <div class="info-label mt-3 mb-2 border-bottom pb-1 border-secondary-subtle">PPE Tambahan</div>
                                <div class="mb-2 small fw-bold text-dark"><i class="fas fa-plus-circle text-success me-2"></i><span class="text-muted fw-normal">{{ $otherPpeText }}</span></div>
                            @endif

                            @if(empty($stdPpes) && empty($otherPpeText))
                                <div class="text-muted small italic">Tidak ada PPE dipilih.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="section-header">5. Safety Checklists & Personnel</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-8 d-flex flex-column">
                        <div class="box-fill flex-grow-1">
                            <label class="info-label mb-3 border-bottom pb-1 w-100 border-secondary-subtle">Checklist Keselamatan yang Dikonfirmasi</label>
                            <div class="row g-2">
                                @php
                                    $selectedChecks = is_array($permit->safety_checklists) ? $permit->safety_checklists : (json_decode($permit->safety_checklists, true) ?? []);
                                    $masterData = \App\Models\Permit::getMasterChecklist();
                                    $handledChecks = [];
                                    $flatSelectedChecks = \Illuminate\Support\Arr::flatten($selectedChecks);
                                @endphp

                                @if(empty($flatSelectedChecks))
                                    <div class="col-12 small text-muted italic">Tidak ada checklist yang dipilih.</div>
                                @else
                                    @foreach($selectedTypes as $type)
                                        @php
                                            $typeChecklists = $masterData[$type] ?? [];
                                            $flatTypeChecklists = \Illuminate\Support\Arr::flatten($typeChecklists);
                                            $userSelectedForThisType = array_intersect($flatTypeChecklists, $flatSelectedChecks);
                                        @endphp

                                        @if(!empty($userSelectedForThisType))
                                            <div class="col-12 mt-2 mb-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                    <i class="fas fa-tag me-1"></i> {{ $type }}
                                                </span>
                                            </div>
                                            @foreach($userSelectedForThisType as $check)
                                                <div class="col-md-6 small fw-semibold mb-2">
                                                    <i class="fas fa-check-square text-primary me-2"></i>{{ $check }}
                                                </div>
                                                @php $handledChecks[] = $check; @endphp
                                            @endforeach
                                        @endif
                                    @endforeach

                                    @php
                                        $unmappedChecks = array_diff($flatSelectedChecks, $handledChecks);
                                    @endphp
                                    
                                    @if(!empty($unmappedChecks))
                                        <div class="col-12 mt-2 mb-1">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-3 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                <i class="fas fa-list me-1"></i> Lainnya
                                            </span>
                                        </div>
                                        @foreach($unmappedChecks as $check)
                                            <div class="col-md-6 small fw-semibold mb-2">
                                                <i class="fas fa-check-square text-primary me-2"></i>{{ $check }}
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column">
                        <div class="box-fill flex-grow-1">
                            <div class="mb-3">
                                <label class="info-label border-bottom pb-1 w-100 border-secondary-subtle">Personil K3 (HSE Rep)</label>
                                <div class="fw-bold text-dark mt-2"><i class="fas fa-user-shield me-2 text-primary"></i>{{ $permit->hse_representative ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="info-label border-bottom pb-1 w-100 border-secondary-subtle">Instruksi Tambahan</label>
                                <div class="small text-muted italic mt-2">{{ $permit->additional_instructions ?? 'Tidak ada instruksi tambahan.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-header">6. Required Attachments (Digital Files)</div>
                <div class="row g-2 mb-5">
                    @php
                        $docs = [
                            'jsa_file' => 'JSA Document', 'hiradc_file' => 'HIRADC', 
                            'worker_list_file' => 'Worker List', 'competency_cert_file' => 'Sertifikat Kompetensi',
                            'work_procedure_file' => 'Prosedur Kerja', 'tool_cert_file' => 'Sertifikat Alat'
                        ];
                    @endphp
                    @foreach($docs as $key => $label)
                        <div class="col-md-4">
                            <div class="doc-card d-flex justify-content-between align-items-center shadow-sm">
                                <span class="small fw-bold text-dark">{{ $label }}</span>
                                @if($permit->$key)
                                    <a href="{{ asset('storage/' . str_replace('public/', '', $permit->$key)) }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer" 
                                       class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-bold">
                                       Lihat File
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted fw-normal border" style="font-size: 0.6rem;">N/A</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="section-header">7. Authorization & Field Validation</div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="signature-box">
                            <div class="info-label mb-2">Pemohon (Applicant)</div>
                            @if($permit->signature_applicant)
                                <img src="{{ $permit->signature_applicant }}" class="signature-img">
                            @else
                                <div class="py-4 text-muted small">Tanpa Tanda Tangan</div>
                            @endif
                            <div class="fw-bold mt-2 border-top pt-2 small text-dark">{{ $permit->applicant_name }}</div>
                            <div class="text-muted fw-bold" style="font-size: 0.6rem;">STATUS: SUBMITTED</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="signature-box">
                            <div class="info-label mb-2">Persetujuan HSE Office</div>
                            @if($permit->signature_manager)
                                <img src="{{ $permit->signature_manager }}" class="signature-img">
                            @else
                                <div class="py-4 text-muted small italic">Menunggu Review...</div>
                            @endif
                            <div class="fw-bold mt-2 border-top pt-2 small text-dark">{{ $permit->manager_name ?? '....................' }}</div>
                            <div class="text-muted fw-bold" style="font-size: 0.6rem;">STATUS: {{ $permit->status == 'pending' ? 'DITINJAU' : 'APPROVED' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="signature-box active-pja">
                            <div class="info-label mb-2 text-success">Validasi Area Lapangan (PJA)</div>
                            @if($permit->signature_pja)
                                <img src="{{ $permit->signature_pja }}" class="signature-img">
                                <div class="fw-bold mt-2 border-top pt-2 small text-dark">{{ $permit->pja_name }}</div>
                                <div class="text-success fw-bold" style="font-size: 0.6rem;">VALIDATED: {{ \Carbon\Carbon::parse($permit->validated_at)->format('d M Y, H:i') }}</div>
                            @else
                                <div class="py-4 text-muted small italic">Menunggu Validasi Area</div>
                                <div class="fw-bold mt-2 border-top pt-2 small text-muted">....................</div>
                                <div class="text-muted fw-bold" style="font-size: 0.6rem;">STATUS: INACTIVE</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-top text-center text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                    Dokumen ini sah secara digital melalui Batamindo PTW System. Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ENGINE SIDEBAR TOGGLE
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const mainWrapper = document.getElementById('mainWrapper');

            if (sidebarToggle && sidebar) {
                sidebarToggle.onclick = function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('collapsed');
                    if (mainWrapper) {
                        mainWrapper.classList.toggle('expanded');
                    }
                };
            }
        });
    </script>
</body>
</html>