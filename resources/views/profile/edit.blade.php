@extends('layouts.app')

@section('content')
<style>
    /* CSS Mewah Bebe tetap dipertahankan */
    :root { --navy-ptw: #003380; --blue-banner: #0056ff; }
    
    .profile-banner-blue {
        background: linear-gradient(135deg, var(--blue-banner) 0%, #0041c2 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(0, 86, 255, 0.15);
    }

    .img-profile-header {
        width: 100px; height: 100px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
        object-fit: cover;
        background: white;
    }

    .card-profile {
        background: white;
        border-radius: 20px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 35px;
        margin-bottom: 30px;
        text-align: left;
    }
</style>

<div class="container-fluid">
    <div class="row g-4">
        
        <div class="col-lg-8">
            {{-- BANNER PROFILE --}}
            <div class="profile-banner-blue d-flex align-items-center gap-4 flex-wrap flex-md-nowrap justify-content-center justify-content-md-start">
                <div class="position-relative">
                    {{-- id="banner-preview" biar preview foto sinkron pas upload --}}
                    <img id="banner-preview" 
                         src="{{ Auth::user()->profile_photo_path ? asset('storage/' . Auth::user()->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                         class="img-profile-header shadow">
                </div>
                <div class="text-center text-md-start">
                    <h2 class="fw-bold mb-1">Hello, {{ Auth::user()->name }}!</h2>
                    <p class="mb-0 opacity-90 small">
                        <i class="fa-solid fa-envelope me-1"></i> {{ Auth::user()->email }} 
                        <span class="mx-2">|</span>
                        <span class="badge bg-white bg-opacity-25 rounded-pill px-3 py-1">{{ strtoupper(Auth::user()->role) }}</span>
                    </p>
                    {{-- TAMBAHKAN INI: Menampilkan Nama Perusahaan di Banner --}}
                    <div class="mt-2 opacity-75 small">
                        <i class="fa-solid fa-building me-1"></i> 
                        {{ Auth::user()->company ?? 'Perusahaan belum diatur' }}
                    </div>
                </div>
            </div>

            <div class="card-profile">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i class="fa-solid fa-address-card text-primary fs-5"></i>
                    <h5 class="fw-bold mb-0">Informasi Data Diri</h5>
                </div>
                @include('layouts.partials.update-profile-information-form')
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-profile">
                <h6 class="fw-bold mb-3" style="color: var(--navy-ptw);">Status Verifikasi</h6>
                
                {{-- Status Email --}}
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border-0 mb-3 text-start">
                    <i class="fa-solid fa-circle-check text-success fs-3"></i>
                    <div>
                        <p class="mb-0 fw-bold small">Email Terverifikasi</p>
                        <p class="mb-0 text-muted extra-small" style="font-size: 10px;">Otomatis via Google</p>
                    </div>
                </div>

                {{-- TAMBAHKAN INI: Info Instansi di Sidebar Kanan --}}
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border-0 mb-3 text-start">
                    <i class="fa-solid fa-building-user text-primary fs-3"></i>
                    <div>
                        <p class="mb-0 fw-bold small">Instansi / Unit Kerja</p>
                        <p class="mb-0 text-muted extra-small" style="font-size: 10px;">
                            {{ Auth::user()->company ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-3 text-start" style="background: rgba(0, 86, 255, 0.05); border-left: 4px solid var(--blue-banner);">
                    <p class="mb-0 small text-primary" style="font-size: 11px; line-height: 1.5;">
                        <i class="fa-solid fa-circle-info me-1"></i> Peran Anda menentukan akses menu pembuatan Permit ke sistem.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- SCRIPT PENYELAMAT HAMBURGER --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (!window.__sidebarHandlerInstalled) {
            if (btnToggle && sidebar) {
                btnToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); 
                    sidebar.classList.toggle('active');
                    console.log('Hamburger Profile Aktif, Bub!');
                });
                window.__sidebarHandlerInstalled = true;
            }
        }
    });
</script>
@endsection