@php $hideNotification = false; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Permit - PTW System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root {
            --navy-sidebar: #003380;
            --bg-soft: #f4f7fa;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-soft); color: #334155; margin: 0; overflow-x: hidden; }

        .main-wrapper { 
            margin-left: 260px; 
            min-height: 100vh; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        .main-wrapper.expanded { margin-left: 85px !important; }

        .header-container {
            background: white;
            padding: 15px 45px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }

        .content-body { padding: 0 45px 35px 45px; }

        .page-header h3 { font-weight: 800; color: #1e293b; margin-bottom: 5px; }
        .date-badge { 
            background: white; padding: 8px 15px; border-radius: 12px; 
            font-size: 0.8rem; font-weight: 700; color: var(--navy-sidebar); 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;
        }

        .approval-grid { display: grid; grid-template-columns: 1fr 350px; gap: 25px; align-items: start; }

        .ptw-card {
            background: white; border-radius: 24px; padding: 30px; margin-bottom: 25px;
            border: 1px solid #f1f5f9; box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            transition: 0.3s;
        }
        .ptw-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        
        .ptw-id { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
        
        /* Status Badges */
        .status-badge { font-size: 11px; font-weight: 800; padding: 6px 15px; border-radius: 50px; text-transform: uppercase; }
        .status-waiting { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .status-approved { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
        .status-active { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; }

        .type-label { color: var(--navy-sidebar); font-weight: 700; font-size: 0.9rem; margin-bottom: 20px; display: block; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 0.85rem; color: #475569; }
        .info-item i { width: 20px; text-align: center; font-size: 1rem; }
        .icon-loc { color: #ef4444; }
        .icon-comp { color: var(--navy-sidebar); }

        .btn-group-ptw { display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; margin-top: 10px; padding-top: 25px;}
        .btn-custom { border-radius: 12px; padding: 10px 22px; font-weight: 700; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; transition: 0.2s; text-decoration: none; border: none; }
        .btn-approve { background: #10b981; color: white; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
        .btn-reject { background: #ef4444; color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }
        .btn-validasi { background: var(--navy-sidebar); color: white; box-shadow: 0 4px 12px rgba(0, 51, 128, 0.2); }
        .btn-review { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        .signature-wrapper {
            border: 2px dashed #e2e8f0;
            background: #f8fafc;
            border-radius: 16px;
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
        }
        canvas { width: 100% !important; height: 100% !important; cursor: crosshair; touch-action: none; }

        .info-sidebar { background: white; border-radius: 24px; padding: 25px; border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .hse-icon { width: 45px; height: 45px; background: #eff6ff; color: var(--navy-sidebar); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .safety-link { color: var(--navy-sidebar); text-decoration: none; font-weight: 700; font-size: 0.85rem; }

        @media (max-width: 991px) {
            .main-wrapper { margin-left: 0 !important; }
            .header-container { padding: 15px 20px; }
            .content-body { padding: 20px; }
        }
    </style>
</head>
<body>
    @include('layouts.sidebar')

    <div class="main-wrapper" id="mainWrapper">
        @include('layouts.header')

        <div class="content-body">
            {{-- Alert Notifikasi --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 15px;">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">Approval & Validasi Permit</h3>
                    <p class="text-muted small mb-0">Review dokumen kantor dan validasi area kerja di lapangan.</p>
                </div>
                <div class="date-badge">
                    <i class="fas fa-calendar-alt me-2"></i> {{ now()->format('l, d M Y') }}
                </div>
            </div>

            <div class="approval-grid">
                <div class="permit-feed">
                    @forelse($approvals as $p)
                    <div class="ptw-card">
                        <div class="ptw-header flex-wrap gap-2 d-flex justify-content-between align-items-start">
                        <div>
                            {{-- LOGIKA NOMOR PTW: Kalau ada tampilkan, kalau nggak ada biarin aja kosong --}}
                            @if($p->ptw_number)
                                <h2 class="ptw-id" style="color: var(--navy-sidebar);">{{ $p->ptw_number }}</h2>
                            @endif

                            <span class="type-label mt-2"><i class="fas fa-tag me-1"></i> {{ $p->work_scope_general }}</span>
                        </div>
                            
                            @if($p->status == 'pending')
                                <span class="status-badge status-waiting"><i class="fas fa-clock me-1"></i> Waiting HSE Office</span>
                            @elseif($p->status == 'approved')
                                <span class="status-badge status-approved"><i class="fas fa-check-circle me-1"></i> Office Approved</span>
                            @elseif($p->status == 'active')
                                <span class="status-badge status-active"><i class="fas fa-running me-1"></i> Active Work</span>
                            @endif
                        </div>

                        <div class="info-grid">
                            <div class="info-item"><i class="fas fa-map-marker-alt icon-loc"></i> <span class="label-text">Lokasi:</span> {{ $p->location }}</div>
                            <div class="info-item"><i class="fas fa-building icon-comp"></i> <span class="label-text">Perusahaan:</span> {{ $p->company }}</div>
                            <div class="info-item"><i class="fas fa-calendar-check text-success"></i> <span class="label-text">Dibuat:</span> {{ $p->created_at->format('d M Y') }}</div>
                            <div class="info-item"><i class="fas fa-user-tie text-info"></i> <span class="label-text">PIC Lead:</span> {{ $p->applicant_name }}</div>
                        </div>

                        <div class="btn-group-ptw">
                            <a href="{{ route('permits.show', $p->id) }}" class="btn-custom btn-review">Review Detail</a>
                            
                            @if($p->status == 'pending')
                                <form action="{{ route('approvals.update', $p->id) }}" method="POST" class="m-0 d-flex gap-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-custom btn-approve" onclick="return confirm('Setujui dokumen ini?')">Approve Office</button>
                                </form>

                                <form action="{{ route('approvals.update', $p->id) }}" method="POST" class="m-0">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn-custom btn-reject" onclick="return confirm('Tolak permit ini?')">Reject</button>
                                </form>
                            @elseif($p->status == 'approved')
                                <button type="button" class="btn-custom btn-validasi" data-bs-toggle="modal" data-bs-target="#modalValidasi{{ $p->id }}">
                                    <i class="fas fa-user-check"></i> Validasi Lapangan (PJA)
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Modal Validasi Lapangan (PJA) --}}
                    <div class="modal fade" id="modalValidasi{{ $p->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow" style="border-radius: 24px;">
                                <div class="modal-header border-0 px-4 pt-4">
                                    <h5 class="fw-bold text-navy">Validasi PJA Lapangan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('approvals.validateField', $p->id) }}" method="POST" onsubmit="return prepareSubmit(event, {{ $p->id }})">
                                    @csrf
                                    <div class="modal-body px-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">Nama Pejabat Area (PJA)</label>
                                            <input type="text" name="pja_name" class="form-control rounded-3" required placeholder="Contoh: Pak Budi - Produksi">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-muted">Tanda Tangan Lapangan</label>
                                            <div class="signature-wrapper">
                                                <canvas id="canvas{{ $p->id }}"></canvas>
                                            </div>
                                            <input type="hidden" name="signature_pja" id="signatureInput{{ $p->id }}">
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none text-danger p-0" onclick="clearPad({{ $p->id }})">
                                            <i class="fas fa-eraser me-1"></i> Hapus Coretan
                                        </button>
                                    </div>
                                    <div class="modal-footer border-0 px-4 pb-4">
                                        <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary rounded-3 fw-bold px-4">Aktifkan Permit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <div class="text-center py-5 bg-white rounded-4 border border-dashed">
                        <i class="fas fa-folder-open fa-3x mb-3 text-muted" style="opacity: 0.2;"></i>
                        <p class="text-muted">Tidak ada pengajuan permit yang perlu diproses.</p>
                    </div>
                    @endforelse
                </div>

                <div class="info-sidebar shadow-sm">
                    <div class="hse-header d-flex align-items-center gap-3 mb-3">
                        <div class="hse-icon"><i class="fas fa-user-shield"></i></div>
                        <h6 class="fw-bold mb-0">HSE Protocol</h6>
                    </div>
                    <p class="sidebar-text mb-4 small text-muted">
                        1. Approve Office dilakukan untuk cek kelengkapan dokumen.<br>
                        2. Validasi Lapangan dilakukan HSE bersama PJA di lokasi kerja.
                    </p>
                    <hr class="my-3" style="opacity: 0.05;">
                    <a href="#" class="safety-link d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle"></i> Batamindo Safety Standards
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pads = {};

        // Inisialisasi Canvas saat modal muncul
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function (event) {
                const id = this.id.replace('modalValidasi', '');
                const canvas = document.getElementById('canvas' + id);
                
                if (canvas) {
                    // Reset pad kalau sudah ada biar gak error
                    if (pads[id]) { pads[id].off(); }

                    pads[id] = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'rgb(0, 0, 0)'
                    });
                    
                    // Sesuaikan ukuran canvas
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);
                    pads[id].clear();
                }
            });
        });

        function clearPad(id) { 
            if (pads[id]) pads[id].clear(); 
        }

        // Fungsi Ambil Tanda Tangan sebelum Form dikirim
        function prepareSubmit(e, id) {
            if (!pads[id] || pads[id].isEmpty()) {
                alert("Tanda tangan PJA wajib diisi sebelum mengaktifkan permit!");
                return false;
            } else {
                // Ambil data Base64
                const data = pads[id].toDataURL('image/png');
                const input = document.getElementById('signatureInput' + id);
                
                if (input) {
                    input.value = data;
                    return true;
                } else {
                    alert("Gagal memproses tanda tangan!");
                    return false;
                }
            }
        }
    </script>
    <script>
        // Canonical sidebar toggle for this standalone layout
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('mainWrapper');

            if (!btn || !sidebar) return;

            const canonicalToggle = function(e) {
                e && e.preventDefault();
                if (window.innerWidth > 991.98) {
                    sidebar.classList.toggle('collapsed');
                    if (mainWrapper) mainWrapper.classList.toggle('expanded');
                } else {
                    sidebar.classList.toggle('active');
                }
            };

            // Replace node to remove other listeners, then attach canonical handler
            try {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                newBtn.addEventListener('click', canonicalToggle);
            } catch (err) {
                // fallback: attach directly
                btn.addEventListener('click', canonicalToggle);
            }

            // mark installed so any guarded page scripts won't re-add
            window.__sidebarHandlerInstalled = true;
        });
    </script>
</body>
</html>