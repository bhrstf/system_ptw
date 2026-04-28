@extends('layouts.app')

@section('content')
{{-- CSS Mewah Khusus Profile --}}
<style>
    :root { --navy-ptw: #003380; --blue-banner: #0056ff; }
    
    #profileContentWrapper { 
        transition: all 0.3s ease; 
        margin-left: 260px; /* Sesuaikan dengan lebar sidebar default kamu */
    }
    
    .profile-banner-blue {
        background: linear-gradient(135deg, var(--blue-banner) 0%, #0041c2 100%);
        border-radius: 20px; padding: 40px; color: white; margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(0, 86, 255, 0.15);
    }
    
    .img-profile-header { 
        width: 110px; height: 110px; border-radius: 50%; 
        border: 4px solid rgba(255,255,255,0.3); object-fit: cover; background: white; 
    }
    
    .card-profile { 
        background: white; border-radius: 20px; border: none; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 35px; margin-bottom: 30px; 
    }
    
    .alert-formal {
        border-radius: 12px; border: none; border-left: 5px solid #198754;
        background-color: #f8f9fa; color: #333; font-weight: 500;
    }

    /* Penyesuaian Responsif Sidebar */
    @media (max-width: 991.98px) {
        #profileContentWrapper { margin-left: 0 !important; }
    }
</style>

<div id="profileContentWrapper">
    <div class="container-fluid py-4">
        
        {{-- Alert Notifikasi Formal --}}
        @if (session('status') === 'profile-updated')
            <div class="alert alert-formal alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-check text-success fs-4 me-3"></i>
                    <div>
                        <strong>Pembaruan Berhasil</strong>
                        <p class="mb-0 small">Informasi profil Anda telah berhasil diperbarui dalam sistem administrasi PTW.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- Sisi Kiri: Banner & Form Utama --}}
            <div class="col-lg-8">
                {{-- Banner Profile --}}
                <div class="profile-banner-blue d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
                    {{-- id="banner-preview" agar sinkron dengan script preview di partials --}}
                    <img id="banner-preview" 
                         src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                         class="img-profile-header shadow">
                    
                    <div>
                        <h2 class="fw-bold mb-1">Hello, {{ Auth::user()->name }}!</h2>
                        <p class="mb-0 opacity-90 small">
                            <i class="fa-solid fa-envelope me-1"></i> {{ Auth::user()->email }} | 
                            <span class="badge bg-white bg-opacity-25">{{ strtoupper(Auth::user()->role) }}</span>
                        </p>
                        {{-- Nama Perusahaan di Banner --}}
                        <div class="d-flex align-items-center mt-2">
                            <i class="fa-solid fa-building me-2 opacity-75"></i>
                            <span class="small opacity-75">
                                {{ Auth::user()->company?? 'Perusahaan belum diatur' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Form Informasi Data Diri --}}
                <div class="card-profile">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <i class="fa-solid fa-address-card text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0">Informasi Data Diri</h5>
                    </div>
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Sisi Kanan: Status Sidebar Info --}}
            <div class="col-lg-4">
                <div class="card-profile" style="padding: 25px;">
                    <h6 class="fw-bold mb-3" style="color: var(--navy-ptw);">Status Verifikasi</h6>
                    
                    {{-- Status Email --}}
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 mb-3 text-start">
                        <i class="fa-solid fa-circle-check text-success fs-3"></i>
                        <div>
                            <p class="mb-0 fw-bold small">Email Terverifikasi</p>
                            <p class="mb-0 text-muted" style="font-size: 10px;">Otomatis via Google Workspace</p>
                        </div>
                    </div>

                    {{-- Status Unit Kerja (Sinkron dengan company_name) --}}
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 mb-3 text-start">
                        <i class="fa-solid fa-building-user text-primary fs-3"></i>
                        <div>
                            <p class="mb-0 fw-bold small">Instansi / Unit Kerja</p>
                            <p class="mb-0 text-muted small" style="font-size: 11px;">
                                {{ Auth::user()->company ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <hr class="my-4 opacity-50">
                    
                    <div class="text-center">
                        <p class="text-muted small italic">Pastikan data Anda valid untuk kemudahan verifikasi dokumen PTW.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script JS untuk Sidebar & Responsivitas --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('sidebarToggle');
        const wrapper = document.getElementById('profileContentWrapper');
        const sidebar = document.getElementById('sidebar');
        
        if (btn && wrapper && sidebar) {
            btn.addEventListener('click', function() {
                setTimeout(() => {
                    if (sidebar.classList.contains('collapsed')) {
                        wrapper.style.marginLeft = "85px";
                    } else {
                        wrapper.style.marginLeft = "260px";
                    }
                }, 50);
            });
        }
    });
</script>
@endsection