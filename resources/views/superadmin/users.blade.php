@extends('layouts.app')

@section('content')
<style>
    :root { --p-blue: #003380; --bg-soft: #f8fafc; }
    
    /* Title Style (Sesuai Halaman History) */
    .page-header h1 { font-weight: 800; color: #0f172a; font-size: 2.5rem; }
    .page-header p { color: #64748b; font-size: 1rem; }

    /* Search & Filter Bar Style */
    .filter-wrapper { background: white; border-radius: 20px; padding: 15px 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
    .search-input { background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 20px; font-size: 0.9rem; width: 100%; }
    .btn-filter { background: #1e293b; color: white; border-radius: 12px; padding: 10px 25px; font-weight: 600; border: none; }
    .btn-filter:hover { background: #0f172a; }

    /* Table Style */
    .permit-section { background: white; border-radius: 24px; box-shadow: 0 15px 50px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; overflow: hidden; }
    .table-head { background: var(--p-blue); padding: 20px 30px; color: white; font-weight: 700; }
    .avatar-mini { width: 40px; height: 40px; background: #e2e8f0; color: #475569; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; }
</style>

<div class="container-fluid py-4">

    {{-- 1. HEADER (Dibuat Besar & Jelas Seperti History) --}}
    <div class="page-header mb-5">
        <h1>Manajemen Pengguna</h1>
        <p>Direktori lengkap seluruh akun yang terdaftar dalam sistem PTW App.</p>
    </div>

    {{-- 2. SEARCH BAR (Fitur Pencarian Modern) --}}
    <div class="filter-wrapper mb-4">
        <form action="" method="GET" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control search-input" placeholder="Cari nama pengguna, email, atau role...">
                </div>
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select search-input">
                    <option value="">Semua Role</option>
                    <option value="kontraktor">Kontraktor</option>
                    <option value="hse/safety">HSE/Safety</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-filter w-100">Cari Pengguna</button>
            </div>
        </form>
    </div>

    {{-- 3. TABEL --}}
    <div class="permit-section">
        <div class="table-head">
            <div class="d-flex align-items-center">
                <i class="fas fa-address-book me-3"></i>
                <span>Daftar Akun Pengguna</span>
            </div>
            {{-- Badge Total sudah dihapus sesuai request --}}
        </div>

        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr class="text-secondary" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
                        <th class="ps-4 py-3">Nama Lengkap</th>
                        <th class="text-center">Email Perusahaan</th>
                        <th class="text-center">Level Akses</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-mini">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    <span class="fw-bold text-dark">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-center text-muted" style="font-size: 0.9rem;">{{ $user->email }}</td>
                            <td class="text-center">
                                @php
                                    $badgeColor = match(strtolower($user->role)) {
                                        'superadmin', 'master' => 'bg-danger',
                                        'admin' => 'bg-primary',
                                        'hse/safety' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none fw-bold" style="font-size: 0.85rem;">
                                        <i class="fas fa-trash-alt me-1"></i> Hapus Akun
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Data pengguna tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection