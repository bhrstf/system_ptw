@php $hideNotification = true; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Permit - PTW System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

        :root {
            --navy-sidebar: #003380;
            --bg-soft: #f4f7fa;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #EEF2F7; margin: 0; overflow-x: hidden; }

        /* --- FIX MAIN WRAPPER FLEKSIBEL --- */
        .main-wrapper { 
            margin-left: 260px; 
            min-height: 100vh; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex; 
            flex-direction: column; 
        }

        /* Saat Sidebar Menciut */
        .main-wrapper.expanded { 
            margin-left: 85px !important; 
        }

        /* HEADER FIX (Gak lebar kali) */
        .header-container { 
            background: white; 
            border-bottom: 1px solid #e2e8f0; 
            padding: 10px 45px; /* Sesuaikan dengan dashboard */
        }

        .content-body { padding: 40px 45px; }

        .audit-card { background: white; border-radius: 20px; padding: 30px; margin-bottom: 25px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .section-title { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: var(--navy-sidebar); }

        .form-label { font-weight: 700; color: #475569; font-size: 13px; margin-bottom: 8px; }
        .form-control, .form-select { border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px 15px; font-size: 14px; background: #f8fafc; transition: all 0.2s; }
        .form-control:focus { border-color: var(--navy-sidebar); box-shadow: 0 0 0 4px rgba(0, 51, 128, 0.1); background: white; }
        
        /* TOMBOL NAVY */
        .btn-submit { background: var(--navy-sidebar); color: white; padding: 12px 30px; border-radius: 12px; font-weight: 700; border: none; transition: 0.3s; }
        .btn-submit:hover { background: #001D4D; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 51, 128, 0.3); }

        .table thead th { background: #F8FAFC; border: none; color: #64748B; text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; border-color: #F1F5F9; }
        .badge-completed { background: #dcfce7; color: #166534; font-size: 10px; font-weight: 800; padding: 6px 12px; border-radius: 8px; }

        /* --- RESPONSIVE BREAKPOINTS --- */
        @media (max-width: 991px) {
            .main-wrapper { margin-left: 0 !important; }
            .main-wrapper.expanded { margin-left: 0 !important; }
            .header-container { padding: 10px 20px; }
            .content-body { padding: 25px 20px; }
            .d-flex.justify-content-between { flex-direction: column; align-items: flex-start !important; gap: 15px; }
        }
    </style>
</head>

<body>
    @include('layouts.sidebar')

    <div class="main-wrapper" id="mainWrapper">
    @include('layouts.header')

        <div class="content-body">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 rounded-4 d-flex align-items-center" style="background: #dcfce7; color: #166534;">
                    <i class="fas fa-check-circle me-3"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Audit Permit</h2>
                    <p class="text-muted mb-0">Monitoring inspeksi keselamatan dan dokumentasi temuan lapangan.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="badge bg-white text-dark border p-2 px-4 rounded-pill shadow-sm fw-bold d-flex align-items-center" style="color: var(--navy-sidebar) !important;">
                        <i class="fas fa-calendar-alt me-2"></i> {{ now()->translatedFormat('l, d M Y') }}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <form action="{{ route('audits.store') }}" method="POST">
                        @csrf
                        <div class="audit-card">
                            <div class="section-title"><i class="fas fa-search-plus"></i> Informasi Inspeksi</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Nomor Permit</label>
                                    <select class="form-select shadow-none" id="permitSelect" name="permit_id" required>
                                        <option selected disabled>Pilih Permit yang akan diaudit</option>
                                        @foreach($permits as $permit)
                                            <option value="{{ $permit->id }}" data-location="{{ $permit->location }}">
                                                {{-- LOGIKA BARU: Kalau ada nomor resmi tampilkan, kalau kosong langsung jenis pekerjaan --}}
                                                @if($permit->ptw_number)
                                                    {{ $permit->ptw_number }} - 
                                                @endif
                                                {{ is_array($permit->permit_type) ? implode(', ', $permit->permit_type) : $permit->permit_type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Lokasi Inspeksi</label>
                                    <input type="text" id="locationInput" class="form-control" placeholder="Lokasi akan terisi otomatis" readonly style="background: #f1f5f9;">
                                </div>
                            </div>
                        </div>

                        <div class="audit-card">
                            <div class="section-title text-danger"><i class="fas fa-exclamation-triangle"></i> Temuan Potensi Bahaya</div>
                            <div class="mb-4">
                                <textarea name="finding" class="form-control shadow-none" rows="5" placeholder="Tulis temuan minimal 5 karakter..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn-submit">Simpan Hasil Audit</button>
                            </div>
                        </div>
                    </form>

                    <div class="audit-card">
                        <div class="section-title"><i class="fas fa-history"></i> Log Audit Terbaru</div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Permit ID</th>
                                        <th>Detail Temuan</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($audits as $item)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">PTW-{{ str_pad($item->permit_id, 3, '0', STR_PAD_LEFT) }}</span><br>
                                                <small class="text-muted">{{ $item->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td><p class="mb-0 small text-truncate" style="max-width: 250px;">{{ $item->finding }}</p></td>
                                            <td><span class="badge-completed">{{ strtoupper($item->status) }}</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('audits.show', $item->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted small">Belum ada data audit.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="audit-card text-center py-4" style="background: linear-gradient(135deg, var(--navy-sidebar) 0%, #001D4D 100%); color: white;">
                        <h6 class="fw-bold mb-3">Total Audit Bulan Ini</h6>
                        <h1 class="display-4 fw-bold mb-0" style="font-weight: 800;">{{ $audits->count() }}</h1>
                        <p class="small mb-0 opacity-75">Update Real-time</p>
                    </div>

                    <div class="audit-card">
                        <div class="section-title"><i class="fas fa-lightbulb"></i> Safety Tip</div>
                        <p class="small text-muted mb-0">Pastikan melakukan audit secara objektif. Gunakan JSA sebagai acuan utama saat melakukan inspeksi lapangan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar Toggle Logic
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
            }

            // Auto-fill Location
            $('#permitSelect').on('change', function() {
                var lokasi = $(this).find(':selected').data('location');
                $('#locationInput').val(lokasi || '');
            });
        });
    </script>
</body>
</html>