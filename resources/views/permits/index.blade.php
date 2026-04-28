@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold">Daftar Permit</h3>
        <a href="{{ route('permits.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Buat Permit Baru
        </a>
    </div>

    @if($permits->count())
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Reference ID</th>
                                <th>Nama Pemohon</th>
                                <th>Lokasi</th>
                                <th>Alat & Peralatan</th>
                                <th>Deskripsi Rinci</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permits as $permit)
                            <tr>
                                <td class="ps-3">{{ $loop->iteration + ($permits->currentPage()-1) * $permits->perPage() }}</td>
                                <td><span class="badge bg-secondary">PTW-{{ str_pad($permit->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                                <td class="fw-semibold">{{ $permit->applicant_name }}</td>
                                <td>{{ $permit->location }}</td>
                                
                                <td class="small">{!! $permit->tools_used !!}</td>
                                <td class="small">{!! $permit->job_description_detail !!}</td>

                                <td class="text-capitalize">
                                    @if($permit->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($permit->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger text-white">{{ $permit->status }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($permit->valid_from)->format('d M Y') }}</td>
                                
                                <td class="text-center pe-3">
                                    {{-- Tombol Lihat --}}
                                    <a href="{{ route('permits.show', $permit->id) }}" class="btn btn-sm btn-outline-primary px-3 mb-1">
                                        Lihat
                                    </a>
                                    {{-- Tombol Aksi (Partial) --}}
                                    <div class="mt-1">
                                        @include('permits.partials._action-buttons', ['permit' => $permit])
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $permits->links() }}
        </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
            Belum ada permit yang diajukan dalam sistem.
        </div>
    @endif
</div>
@endsection