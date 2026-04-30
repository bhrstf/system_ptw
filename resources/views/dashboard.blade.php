<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PTW System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --p-blue: #003380; --bg-soft: #f8fafc; }
        * { font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.2s ease; }
        body { background: var(--bg-soft); margin: 0; overflow-x: hidden; }

        /* ENGINE SIDEBAR & WRAPPER */
        .sidebar { width: 260px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1100; transition: all 0.35s ease; background: var(--p-blue); }
        .sidebar.collapsed { width: 85px; }

        #mainWrapper { 
            margin-left: 260px; 
            min-height: 100vh; 
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); 
            padding: 30px 45px; 
        }

        #mainWrapper.expanded { margin-left: 85px !important; }

        /* STATS CARD */
        .stat-card { background: white; border-radius: 24px; padding: 25px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; }
        .stat-icon { width: 60px; height: 60px; background: linear-gradient(135deg, var(--p-blue), #0056b3); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0; }
        .stat-content h4 { color: #0f172a; font-weight: 800; margin: 0; font-size: 1.8rem; }
        .stat-content small { color: #64748b; font-weight: 600; }

        /* TABLE SECTION */
        .permit-section { background: white; border-radius: 28px; box-shadow: 0 15px 50px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; position: relative; overflow: visible !important; }
        .table-head { background: linear-gradient(90deg, var(--p-blue), #004aad); padding: 22px 35px; color: white; font-weight: 800; border-radius: 28px 28px 0 0; position: relative; z-index: 10; }

        .btn-filter { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2); color: white; backdrop-filter: blur(8px); padding: 8px 18px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; text-decoration: none; }
        
        /* DROPDOWN FILTER */
        .dropdown-menu-filter { 
            width: 320px !important; 
            max-width: 90vw; /* Cegah luber di HP */
            border: none !important; 
            border-radius: 24px !important; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important; 
            padding: 22px !important; 
            z-index: 9999 !important; 
        }
        
        .status-badge { padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; border: 1px solid transparent; }
        
        /* SINKRONISASI WARNA BADGE DENGAN MODEL */
        .badge-primary { background: #ecfdf5; color: #059669; border-color: #d1fae5; } /* ACTIVE */
        .badge-warning { background: #fffbeb; color: #d97706; border-color: #fef3c7; } /* PENDING */
        .badge-danger  { background: #fef2f2; color: #dc2626; border-color: #fee2e2; } /* REJECTED */
        .badge-info    { background: #eff6ff; color: #2563eb; border-color: #dbeafe; } /* COMPLETED */
        .badge-success { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; } /* CLOSED */

        .table-responsive { overflow-x: auto; overflow-y: visible !important; }

        /* --- RESPONSIVE ADJUSTMENTS (FIXED) --- */

        /* 1. KHUSUS IPAD / TABLET (Layar Sedang) */
        @media (max-width: 1024px) { 
            #mainWrapper { 
                padding: 25px 20px; /* Kurangi padding, tapi jangan margin-left biar nggak ketimpa sidebar */
            } 
        }

        /* 2. KHUSUS HP (Layar Kecil) */
        @media (max-width: 768px) {
            #mainWrapper { 
                margin-left: 0 !important; /* Tarik full ke kiri */
                padding: 15px 10px; 
            }
            
            .sidebar { 
                transform: translateX(-100%); /* Sembunyikan sidebar ke kiri luar layar */
            }
            
            .sidebar.show, .sidebar.collapsed { 
                transform: translateX(0); /* Munculkan kalau tombol di klik */
            }

            .stat-card { padding: 18px; } 
            .stat-content h4 { font-size: 1.5rem; } 
            .stat-icon { width: 50px; height: 50px; font-size: 1.2rem; }
            
            .table-head { 
                flex-direction: column; 
                align-items: stretch !important; 
                gap: 15px; 
                padding: 20px; 
                text-align: center; 
            }
            .table-head > div { justify-content: center; }
        }
    </style>
</head>
<body>

    @include('layouts.sidebar')

    <div id="mainWrapper">
        @include('layouts.header')

        @php
            use App\Models\Permit;
            $role = strtolower(trim(auth()->user()->role ?? ''));
            $isKontraktor = $role === 'kontraktor';

            $totalPekerjaan = 0;
            foreach($permits as $p) {
                $types = is_array($p->permit_type) ? $p->permit_type : json_decode($p->permit_type, true);
                if (is_array($types)) { $totalPekerjaan += count($types); }
            }
        @endphp

        {{-- KARTU STATISTIK --}}
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #34d399);"><i class="fas fa-check-double"></i></div>
                    <div class="stat-content">
                        <h4>{{ $permits->filter(fn($p) => $p->status === Permit::STATUS_ACTIVE)->count() }}</h4>
                        <small>Total Permit Aktif</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-content"><h4>{{ $permits->count() }}</h4><small>Total Pekerjaan</small></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);"><i class="fas fa-tasks"></i></div>
                    <div class="stat-content"><h4>{{ $totalPekerjaan }}</h4><small>Total Permit</small></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);"><i class="fas fa-hard-hat"></i></div>
                        <div class="stat-content">
                        <h4>{{ $totalKontraktor ?? ($isKontraktor ? 1 : $permits->unique('company')->count()) }}</h4>
                        <small>Total Kontraktor</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL MONITORING --}}
        <div class="permit-section">
                <div class="table-head d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <span><i class="fas fa-list-ul me-2"></i> {{ $isKontraktor ? 'Daftar Pengajuan Saya' : 'Monitoring Seluruh Permit' }}</span>
                
                <div class="d-flex gap-2 justify-content-center">
                    @if($isKontraktor)
                        <a href="{{ route('permits.create') }}" class="btn-filter shadow-sm text-decoration-none"><i class="fas fa-plus me-1"></i> Request PTW</a>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-filter dropdown-toggle shadow-sm" type="button" id="filterBtnDashboard" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-filter shadow-lg p-4">
                            <div class="mb-3">
                                <label class="fw-bold mb-2 small text-uppercase text-dark">Cari Lokasi</label>
                                <input type="text" class="form-control bg-light border-0" placeholder="Ketik lokasi..." id="inputLokasi">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="fw-bold mb-1 small text-uppercase text-dark">Start Date</label>
                                    <input type="date" class="form-control bg-light border-0 small" id="startDate">
                                </div>
                                <div class="col-6">
                                    <label class="fw-bold mb-1 small text-uppercase text-dark">End Date</label>
                                    <input type="date" class="form-control bg-light border-0 small" id="endDate">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold mb-2 small text-uppercase text-dark">Status</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <input type="checkbox" class="btn-check check-status" id="s1" value="ACTIVE"><label class="btn btn-outline-primary btn-sm rounded-pill" for="s1">Active</label>
                                    <input type="checkbox" class="btn-check check-status" id="s2" value="PENDING"><label class="btn btn-outline-primary btn-sm rounded-pill" for="s2">Pending</label>
                                    <input type="checkbox" class="btn-check check-status" id="s3" value="CLOSED"><label class="btn btn-outline-primary btn-sm rounded-pill" for="s3">Closed</label>
                                    <input type="checkbox" class="btn-check check-status" id="s4" value="REJECTED"><label class="btn btn-outline-primary btn-sm rounded-pill" for="s4">Rejected</label>
                                </div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <button type="button" id="btnResetAkhir" class="btn btn-link btn-sm text-danger text-decoration-none fw-bold">RESET</button>
                                <button type="button" id="btnTerapkanAkhir" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold" style="background: var(--p-blue);">TERAPKAN</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4"># Nomor PTW</th>
                            <th>Jenis Kerja</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permits as $permit)
                            <tr class="baris-data">
                                <td class="ps-4">
                                    @if($permit->ptw_number)
                                        <div class="fw-bold text-primary" style="font-size: 1.05rem;">{{ $permit->ptw_number }}</div>
                                    @endif
                                </td>
                                <td>{{ is_array($permit->permit_type) ? implode(', ', $permit->permit_type) : $permit->permit_type }}</td>
                                <td class="kolom-lokasi">{{ $permit->location }}</td>
                                <td class="kolom-status">
                                    <span class="status-badge {{ $permit->status_badge }}">{{ strtoupper($permit->status) }}</span>
                                </td>
                                <td class="kolom-tanggal" data-raw="{{ $permit->created_at->format('Y-m-d') }}">{{ $permit->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- DETAIL --}}
                                        <a href="{{ route('permits.show', $permit->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- EDIT --}}
                                        @if($isKontraktor && $permit->status === Permit::STATUS_PENDING)
                                            <a href="{{ route('permits.edit', $permit->id) }}" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        {{-- PDF --}}
                                        <a href="{{ route('history.pdf_ptw', $permit->id) }}" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data permit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. ENGINE SIDEBAR TOGGLE ---
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

            // --- 2. ENGINE FILTER TABEL (JS CLIENT SIDE) ---
            const applyBtn = document.getElementById('btnTerapkanAkhir');
            const resetBtn = document.getElementById('btnResetAkhir');
            const baris = document.querySelectorAll('.baris-data');

            if(applyBtn) {
                applyBtn.onclick = function() {
                    const valLokasi = document.getElementById('inputLokasi').value.toLowerCase().trim();
                    const valStart = document.getElementById('startDate').value;
                    const valEnd = document.getElementById('endDate').value;
                    const statusTerpilih = Array.from(document.querySelectorAll('.check-status:checked')).map(c => c.value);

                    baris.forEach(row => {
                        const txtLokasi = row.querySelector('.kolom-lokasi').innerText.toLowerCase();
                        const txtStatus = row.querySelector('.kolom-status').innerText.toUpperCase().trim();
                        const txtTanggal = row.querySelector('.kolom-tanggal').getAttribute('data-raw');

                        const matchL = valLokasi === "" || txtLokasi.includes(valLokasi);
                        const matchS = statusTerpilih.length === 0 || statusTerpilih.includes(txtStatus);
                        
                        let matchT = true;
                        if (valStart && valEnd) {
                            matchT = (txtTanggal >= valStart && txtTanggal <= valEnd);
                        } else if (valStart) {
                            matchT = (txtTanggal >= valStart);
                        } else if (valEnd) {
                            matchT = (txtTanggal <= valEnd);
                        }
                        row.style.display = (matchL && matchS && matchT) ? "" : "none";
                    });

                    // Tutup dropdown otomatis setelah apply
                    const dropdownBtn = document.getElementById('filterBtnDashboard');
                    if (dropdownBtn) {
                        const instance = bootstrap.Dropdown.getOrCreateInstance(dropdownBtn);
                        instance.hide();
                    }
                };
            }

            if(resetBtn) {
                resetBtn.onclick = function() {
                    document.getElementById('inputLokasi').value = "";
                    document.getElementById('startDate').value = "";
                    document.getElementById('endDate').value = "";
                    document.querySelectorAll('.check-status').forEach(c => c.checked = false);
                    baris.forEach(r => r.style.display = "");
                };
            }
        });
    </script>
</body>
</html>