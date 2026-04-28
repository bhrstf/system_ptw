@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - PTW System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root { 
            --navy-sidebar: #003380; 
            --bg-soft: #f8fafc; 
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-soft); margin: 0; overflow-x: hidden; }
        
        .main-wrapper { 
            margin-left: 260px; 
            min-height: 100vh; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex;
            flex-direction: column;
        }
        .main-wrapper.expanded { margin-left: 85px !important; }
        
        .header-box { 
            background: white; 
            border-bottom: 1px solid #e2e8f0; 
            padding: 5px 45px; 
            margin-bottom: 30px;
        }

        .content-body { padding: 0 45px 40px 45px; }

        .card-custom { border-radius: 20px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.03); background: white; }
        
        /* BADGE STATUS DYNAMICS */
        .status-badge { padding: 6px 14px; border-radius: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; display: inline-block; }
        .bg-pending { background: #fef3c7; color: #92400e; }
        .bg-approved { background: #dcfce7; color: #15803d; }
        .bg-active { background: #eff6ff; color: #1d4ed8; }
        .bg-rejected { background: #fee2e2; color: #b91c1c; }
        .bg-closed { background: #f1f5f9; color: #64748b; }
        
        .audit-tag { font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; }
        .pass-tag { background: #ecfdf5; color: #059669; }
        
        .btn-action { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: none; transition: 0.2s; text-decoration: none; cursor: pointer; }
        .btn-view { background: #f1f5f9; color: #64748b; }
        .btn-view:hover { background: var(--navy-sidebar); color: white; }
        
        .btn-navy { background: var(--navy-sidebar); color: white; border: none; transition: 0.3s; }
        .btn-navy:hover { background: #001D4D; color: white; transform: translateY(-2px); }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table { min-width: 900px; }
        .table thead th { white-space: nowrap; font-weight: 800; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; }

        @media (max-width: 991px) {
            .main-wrapper { margin-left: 0 !important; }
            .main-wrapper.expanded { margin-left: 0 !important; }
            .header-box { padding: 5px 20px; }
            .content-body { padding: 0 20px 40px 20px; }
        }
    </style>
</head>
<body>

    @include('layouts.sidebar')

    <div class="main-wrapper" id="mainWrapper">
    @include('layouts.header')

        <div class="content-body">
            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1" style="font-weight: 800;">History Log</h2>
                    <p class="text-muted small mb-0">Kelola riwayat izin kerja dan selesaikan pekerjaan yang aktif.</p>
                </div>
                @if($isApprover)
                    <a href="{{ route('history.export_pdf') }}" class="btn btn-navy rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                        <i class="fas fa-file-pdf"></i> Export Masterlist PDF
                    </a>
                @endif
            </div>

            {{-- FILTER FORM --}}
            <div class="card-custom p-4 mb-4">
                <form action="{{ route('history.history') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group bg-light rounded-pill px-3 py-1">
                            <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control bg-transparent border-0 shadow-none" placeholder="Cari Nomor PTW, Lokasi, atau Pemohon..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select bg-light border-0 rounded-pill px-3 shadow-none py-2">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Office</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Work</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed/Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark rounded-pill w-100 fw-bold py-2">Terapkan Filter</button>
                    </div>
                </form>
            </div>

            {{-- TABLE SECTION --}}
            <div class="card-custom p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="text-muted small">
                            <tr>
                                <th>NOMOR PTW</th>
                                @if($isApprover) <th>PEMOHON</th> @endif
                                <th>JENIS PEKERJAAN</th>
                                <th>LOKASI</th>
                                <th>STATUS</th>
                                <th>HASIL AUDIT</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $h)
                            <tr>
                                <td class="fw-bold">
                                    {{-- UPDATE: Tampilkan nomor resmi jika ada --}}
                                    @if($h->ptw_number)
                                        <span class="text-primary">{{ $h->ptw_number }}</span>
                                    @else
                                        <span class="text-muted opacity-50">-</span>
                                    @endif
                                </td>
                                
                                @if($isApprover)
                                    <td>
                                        <div class="fw-bold small">{{ $h->applicant_name }}</div>
                                        <div class="text-muted" style="font-size: 10px;">{{ $h->company }}</div>
                                    </td>
                                @endif

                                <td class="small">
                                    @php 
                                        $types = is_array($h->permit_type) ? $h->permit_type : (json_decode($h->permit_type, true) ?? []); 
                                    @endphp
                                    {{ is_array($types) ? implode(', ', $types) : $h->permit_type }}
                                </td>
                                <td class="small text-muted">{{ $h->location }}</td>
                                <td>
                                    <span class="status-badge bg-{{ $h->status }}">
                                        {{ strtoupper(str_replace('_', ' ', $h->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($h->audit)
                                        <span class="audit-tag pass-tag" title="{{ $h->audit->finding }}">
                                            <i class="fas fa-check-circle me-1"></i> AUDITED
                                        </span>
                                    @else
                                        <span class="text-muted small opacity-50">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Tombol View --}}
                                        <a href="{{ route('permits.show', $h->id) }}" class="btn-action btn-view shadow-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- UPDATE: Tombol Selesaikan (Hanya untuk Admin & Status Active) --}}
                                        @if($h->status === 'active' && $isApprover)
                                            <form action="{{ route('history.complete', $h->id) }}" method="POST" onsubmit="return confirm('Apakah pekerjaan ini sudah selesai dan permit siap ditutup?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-action shadow-sm bg-success bg-opacity-10 text-success" title="Complete Permit">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Tombol PDF --}}
                                        @if($h->status === 'approved' || $h->status === 'active' || $h->status === 'closed')
                                            <a href="{{ route('history.pdf_ptw', $h->id) }}" target="_blank" class="btn-action shadow-sm bg-danger bg-opacity-10 text-danger" title="Download PDF PTW">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $isApprover ? '7' : '6' }}" class="text-center py-5">
                                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-20">
                                    <p class="text-muted fw-bold">Belum ada riwayat permit yang ditemukan.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById('sidebarToggle');
            const wrapper = document.getElementById('mainWrapper');
            const side = document.getElementById('sidebar');

            if (!window.__sidebarHandlerInstalled) {
                if (btn && wrapper) {
                    btn.onclick = function(e) {
                        e.preventDefault();
                        wrapper.classList.toggle('expanded');
                        if(side) side.classList.toggle('collapsed');
                    };
                }
                window.__sidebarHandlerInstalled = true;
            }
        });
    </script>
</body>
</html>