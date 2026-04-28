{{-- resources/views/permits/partials/_action-buttons.blade.php --}}

<div class="d-flex gap-2">
    @csrf
    
    {{-- Tombol Approve (Muncul jika status Pending) --}}
    @if($permit->status == 'pending')
        <form action="{{ route('permits.approve', $permit->id) }}" method="POST" onsubmit="return confirm('Setujui permit ini?');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-success">Approve</button>
        </form>
        
        <form action="{{ route('permits.reject', $permit->id) }}" method="POST" onsubmit="return confirm('Tolak permit ini?');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
        </form>
    @endif

    {{-- Tombol Activate (Muncul jika status Approved) --}}
    @if($permit->status == 'approved')
        <form action="{{ route('permits.activate', $permit->id) }}" method="POST" onsubmit="return confirm('Mulai pekerjaan sekarang?');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-primary">Start Job (Active)</button>
        </form>
    @endif

    {{-- Tombol Complete (Muncul jika status Active) --}}
    @if($permit->status == 'active')
        <form action="{{ route('permits.complete', $permit->id) }}" method="POST" onsubmit="return confirm('Selesaikan pekerjaan?');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-info text-white">Complete</button>
        </form>
    @endif

    {{-- Tombol Close (Muncul jika status Completed) --}}
    @if($permit->status == 'completed')
        <form action="{{ route('permits.close', $permit->id) }}" method="POST" onsubmit="return confirm('Tutup permit ini secara permanen?');">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-dark">Close Permit</button>
        </form>
    @endif
</div>