@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Audit - PTW System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

        :root {
            --navy-sidebar: #003380;
            --bg-soft: #EEF2F7;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-soft); margin: 0; overflow-x: hidden; }

        /* --- WRAPPER FLEKSIBEL --- */
        .main-wrapper { 
            margin-left: 260px; 
            min-height: 100vh; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        .main-wrapper.expanded { margin-left: 85px !important; }

        .header-box {
            background: white; padding: 5px 45px; border-bottom: 1px solid #e2e8f0; margin-bottom: 30px;
        }

        .content-body { padding: 0 45px 35px 45px; }

        .report-card { 
            background: white; border-radius: 24px; padding: 40px; border: none; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; overflow: hidden;
        }

        /* Watermark Verified */
        .report-card::before {
            content: 'VERIFIED';
            position: absolute; top: 50px; right: -30px; transform: rotate(30deg);
            font-size: 80px; font-weight: 900; color: rgba(0, 51, 128, 0.03); pointer-events: none;
        }

        .info-label { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .info-value { font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 25px; }
        
        .text-navy { color: var(--navy-sidebar) !important; }

        .finding-box { background: #F8FAFC; border: 2px dashed #E2E8F0; border-radius: 20px; padding: 30px; margin-top: 10px; }

        .btn-back { background: white; color: #475569; border-radius: 12px; padding: 10px 20px; font-weight: 700; text-decoration: none; border: 1px solid #e2e8f0; transition: 0.3s; display: inline-block; }
        .btn-back:hover { background: #f8fafc; color: var(--navy-sidebar); border-color: var(--navy-sidebar); }

        .btn-print-custom { background: var(--navy-sidebar); border: none; color: white; border-radius: 50px; padding: 12px 30px; font-weight: 700; transition: 0.3s; box-shadow: 0 4px 15px rgba(0, 51, 128, 0.2); }
        .btn-print-custom:hover { background: #001D4D; transform: translateY(-2px); color: white; }

        /* RESPONSIVE */
        @media (max-width: 991px) {
            .main-wrapper { margin-left: 0 !important; }
            .main-wrapper.expanded { margin-left: 0 !important; }
            .header-box { padding: 5px 20px; }
            .content-body { padding: 0 20px 35px 20px; }
            .report-card { padding: 25px; }
        }

        @media print {
            .btn-back, .btn-print-custom, .sidebar, #sidebar, .header-box, #sidebarToggle { display: none !important; }
            .main-wrapper { margin-left: 0 !important; padding: 0 !important; }
            body { background: white; }
            .report-card { box-shadow: none !important; border: 1px solid #eee !important; margin: 0; }
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')

    <div class="main-wrapper" id="mainWrapper">
    @include('layouts.header')

        <div class="content-body">
            <div class="d-flex align-items-center justify-content-between mb-4 gap-2">
                <a href="{{ route('audits.index') }}" class="btn-back shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke List
                </a>
            </div>

            <div class="report-card">
                <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-4 flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold text-dark mb-1" style="font-weight: 800;">LAPORAN INSPEKSI AUDIT</h3>
                        <p class="text-muted mb-0 small">Sistem Manajemen Izin Kerja (Permit to Work)</p>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted fw-bold mb-1">STATUS AUDIT</div>
                        <span class="badge bg-success bg-opacity-10 text-success px-4 py-2 rounded-pill fw-bold" style="font-size: 12px; border: 1px solid rgba(22, 101, 52, 0.2);">
                            <i class="fas fa-check-circle me-1"></i> {{ strtoupper($audit->status) }}
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Nomor Izin Kerja (PTW)</div>
                        <div class="info-value text-navy" style="font-size: 18px;">
                            {{-- Kalau belum ada nomor resmi, biarkan kosong --}}
                            {{ $audit->permit->ptw_number }}
                        </div>
                        
                        <div class="info-label">Jenis Pekerjaan</div>
                        <div class="info-value">
                            {{-- Karena sudah di-cast di Model, kita tinggal cek & implode saja --}}
                            @if(is_array($audit->permit->permit_type))
                                {{ implode(', ', $audit->permit->permit_type) }}
                            @else
                                {{ $audit->permit->permit_type }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Lokasi Area Kerja</div>
                        <div class="info-value">{{ $audit->permit->location }}</div>
                        <div class="info-label">Waktu Inspeksi Selesai</div>
                        <div class="info-value">{{ $audit->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                    </div>
                </div>

                <div class="mt-4 mb-3">
                    <div class="info-label text-danger fw-bold">
                        <i class="fas fa-exclamation-circle me-1"></i> Temuan Unsafe Action
                    </div>
                    <div class="finding-box">
                        <p class="mb-0 text-dark" style="line-height: 1.8; font-size: 16px; font-style: italic;">
                            "{{ $audit->finding }}"
                        </p>
                    </div>
                </div>

                <div class="mt-5 p-4 border rounded-4 bg-light">
                    <div class="row align-items-center gap-3">
                        <div class="col-auto">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm text-white fw-bold" style="width: 55px; height: 55px; font-size: 20px; background-color: var(--navy-sidebar) !important;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col">
                            <div class="small text-muted fw-bold">Auditor (HSE Inspector)</div>
                            <div class="fw-bold text-dark fs-5">{{ Auth::user()->name }}</div>
                            <div class="small text-muted">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnToggle = document.getElementById('sidebarToggle');
            const wrapper = document.getElementById('mainWrapper');
            const side = document.getElementById('sidebar');

            if (!window.__sidebarHandlerInstalled) {
                if (btnToggle && wrapper) {
                    btnToggle.onclick = function(e) {
                        e.preventDefault();
                        wrapper.classList.toggle('expanded');
                        if(side) side.classList.toggle('collapsed');
                    };
                }
            }
        });
    </script>
</body>
</html>