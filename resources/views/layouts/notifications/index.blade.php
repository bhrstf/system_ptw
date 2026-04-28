@extends('layouts.app')

@section('content')
<div class="content-body">
    <div class="container-fluid">
        
        {{-- Row Judul - Disesuaikan agar manis di HP & Desktop --}}
        <div class="row page-titles mx-0 mb-4 align-items-center">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h3 class="fw-bold text-dark mb-1">Pusat Aktivitas</h3>
                    <p class="mb-0 text-muted small">Riwayat notifikasi dan pembaruan sistem kamu</p>
                </div>
            </div>
            <div class="col-sm-6 p-md-0 text-sm-end mt-2 mt-sm-0">
                <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill">
                    <i class="fas fa-bell me-1"></i> Notifikasi Terbaru
                </span>
            </div>
        </div>

        {{-- Row Konten Utama --}}
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    {{-- Header Card Tambahan biar makin pro --}}
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">Log Notifikasi</h5>
                        <button class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">
                            Terbaru
                        </button>
                    </div>

                    <div class="card-body p-0">
                        @forelse($notifications as $notification)
                            <div class="notif-item p-4 border-bottom d-flex align-items-start {{ $notification->read_at ? 'bg-light bg-opacity-25' : 'unread-notif' }}">
                                
                                {{-- Icon Notif - Fleksibel Size --}}
                                <div class="notif-icon me-3 d-flex align-items-center justify-content-center rounded-circle {{ $notification->read_at ? 'bg-secondary text-white' : 'bg-primary text-white shadow-sm' }}">
                                    <i class="fas {{ $notification->read_at ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                                </div>

                                {{-- Detail Pesan --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-1">
                                        <h6 class="fw-bold mb-0 text-dark">{{ $notification->data['message'] ?? 'Aktivitas Baru' }}</h6>
                                        <small class="text-muted mt-1 mt-md-0 d-flex align-items-center">
                                            <i class="far fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted small lh-base">
                                        {!! $notification->data['detail'] ?? 'Klik untuk melihat detail aktivitas lebih lanjut.' !!}
                                    </p>
                                </div>

                                {{-- Status Dot buat yang Unread --}}
                                @if(!$notification->read_at)
                                    <div class="ms-2">
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
                                <p class="text-muted small px-5">Semua pemberitahuan sistem kamu akan tercatat secara otomatis di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination yang rapi --}}
                @if($notifications && $notifications->hasPages())
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<style>
    /* Styling khusus biar makin cantik tapi gak ganggu layout sidebar */
    .bg-primary-light { background-color: rgba(0, 51, 128, 0.1); }
    
    .notif-item {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .notif-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .unread-notif {
        border-left: 4px solid #003380;
    }
    
    .notif-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        font-size: 16px;
    }

    /* Penyesuaian Pagination Laravel */
    .pagination {
        gap: 5px;
    }
    .page-item .page-link {
        border-radius: 8px !important;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* FIX RESPONSIVE: Mastiin konten nggak kaku di iPad/HP */
    @media (max-width: 768px) {
        .notif-item { padding: 1.25rem !important; }
        .page-titles h3 { font-size: 1.5rem; }
        .notif-icon { width: 36px; height: 36px; min-width: 36px; font-size: 14px; }
    }
</style>
@endsection