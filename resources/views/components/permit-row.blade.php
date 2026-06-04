@php
    use App\Models\Permit;
    $isKontraktor = strtolower(trim(auth()->user()->role ?? '')) === 'kontraktor';
@endphp

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
