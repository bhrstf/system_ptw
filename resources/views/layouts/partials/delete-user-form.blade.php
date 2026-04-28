<section>
    <header class="mb-4 d-flex align-items-center gap-2">
        {{-- Ikon Peringatan Merah --}}
        <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 1.2rem;"></i>
        <h5 class="fw-bold mb-0 text-danger">{{ __('Hapus Akun') }}</h5>
    </header>

    <div class="alert alert-danger border-0 rounded-4 p-3 mb-4 shadow-sm" style="background-color: #fff5f5;">
        <p class="text-danger small mb-0 fw-500">
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Sebelum menghapus akun, silakan unduh data atau informasi apa pun yang ingin Anda simpan.') }}
        </p>
    </div>

    {{-- Tombol Buka Modal --}}
    <button 
        class="btn btn-outline-danger px-4 py-2 fw-bold rounded-pill shadow-sm transition-all"
        style="border-width: 2px;"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        <i class="fa-solid fa-user-xmark me-2"></i>{{ __('Delete Account') }}
    </button>

    {{-- Modal Konfirmasi --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 p-md-5">
            @csrf
            @method('delete')

            <div class="text-center mb-4">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-circle-exclamation text-danger fs-3"></i>
                </div>
                <h4 class="fw-bold text-dark">{{ __('Apakah Anda yakin?') }}</h4>
                <p class="text-muted small">
                    {{ __('Masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun ini secara permanen.') }}
                </p>
            </div>

            <div class="mt-4">
                <label for="password" class="form-label fw-bold small text-muted">{{ __('Konfirmasi Password') }}</label>
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        class="form-control border-start-0 py-2 shadow-none border-gray-300" 
                        placeholder="{{ __('Masukkan Password Anda') }}" 
                    />
                </div>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-danger small" />
            </div>

            <div class="mt-5 d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" x-on:click="$dispatch('close')">
                    {{ __('Batal') }}
                </button>

                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-trash-can me-2"></i>{{ __('Hapus Akun Sekarang') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>