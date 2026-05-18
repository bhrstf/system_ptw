@extends('layouts.app')

@section('content')
<div class="content-body">
    <div class="container-fluid">
        
        {{-- Row Judul --}}
        <div class="row page-titles mx-0 mb-4 align-items-center">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h3 class="fw-bold text-dark mb-1">Pusat Aktivitas</h3>
                    <p class="mb-0 text-muted small">Riwayat notifikasi dan pembaruan sistem secara real-time</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 text-sm-end mt-2 mt-sm-0">
                <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-bell me-1"></i> Log Aktivitas
                </span>
            </div>
        </div>

        {{-- Row Konten Utama --}}
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">Log Notifikasi Terbaru</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li><a class="dropdown-item small" href="#">Semua Aktivitas</a></li>
                                <li><a class="dropdown-item small" href="#">Belum Dibaca</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @forelse($notifications as $notification)
                            <div class="notif-item p-4 border-bottom d-flex align-items-start {{ $notification->read_at ? 'bg-light bg-opacity-25' : 'unread-notif' }}" 
                                 onclick="markAsRead('{{ $notification->id }}', this)">
                                
                                {{-- Icon Notif Dinamis dari Backend --}}
                                <div class="notif-icon me-3 d-flex align-items-center justify-content-center rounded-circle {{ $notification->read_at ? 'bg-secondary text-white' : 'bg-primary text-white shadow-sm' }}">
                                    <i class="fas {{ $notification->data['icon'] ?? ($notification->read_at ? 'fa-envelope-open' : 'fa-envelope') }}"></i>
                                </div>

                                {{-- Detail Pesan --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-1">
                                        {{-- Menggunakan title dinamis yang kita buat di Notification Class --}}
                                        <h6 class="fw-bold mb-0 text-dark">
                                            {{ $notification->data['title'] ?? ($notification->data['message'] ?? 'Informasi Sistem') }}
                                        </h6>
                                        <small class="text-muted mt-1 mt-md-0 d-flex align-items-center" style="font-size: 0.75rem;">
                                            <i class="far fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted small lh-base">
                                        {!! $notification->data['message'] ?? ($notification->data['detail'] ?? 'Klik untuk melihat detail aktivitas lebih lanjut.') !!}
                                    </p>
                                </div>

                                {{-- Status Dot buat yang Unread --}}
                                @if(!$notification->read_at)
                                    <div class="ms-2 unread-indicator">
                                        <div class="bg-primary rounded-circle" style="width: 10px; height: 10px;"></div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            {{-- Tampilan Kosong yang Estetik --}}
                            <div class="p-5 text-center py-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle shadow-sm" style="width: 100px; height: 100px;">
                                        <i class="fas fa-bell-slash fa-3x text-muted opacity-50"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-dark">Belum ada aktivitas</h5>
                                <p class="text-muted small px-5">Semua pemberitahuan sistem Anda akan tercatat secara otomatis di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination --}}
                @if($notifications && $notifications->hasPages())
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-light { background-color: rgba(0, 51, 128, 0.08); }
    
    .notif-item {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }
    
    .notif-item:hover {
        background-color: #fcfdfe !important;
        transform: translateX(4px);
    }

    .notif-item:active {
        background-color: #f0f3f7 !important;
        transform: scale(0.995);
    }
    
    .unread-notif {
        border-left: 4px solid #003380 !important;
        background-color: #ffffff;
    }
    
    .notif-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        font-size: 16px;
        transition: 0.3s;
    }

    .notif-item:hover .notif-icon {
        transform: rotate(-10deg) scale(1.1);
    }

    /* Override Laravel Pagination */
    .pagination .page-item.active .page-link {
        background-color: #003380;
        border-color: #003380;
    }

    @media (max-width: 768px) {
        .notif-item { padding: 1.25rem !important; }
        .page-titles h3 { font-size: 1.4rem; }
        .notif-icon { width: 38px; height: 38px; min-width: 38px; font-size: 14px; }
    }
</style>

<script>
    function markAsRead(id, element) {
        // Efek visual langsung
        if (element.classList.contains('unread-notif')) {
            element.classList.remove('unread-notif');
            element.classList.add('bg-light', 'bg-opacity-25');
            const indicator = element.querySelector('.unread-indicator');
            if (indicator) indicator.remove();
            
            // Panggil API mark read lewat AJAX jika perlu
            // fetch(`/notifications/${id}/mark-read`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
        }
    }
</script>
@endsection