<section>
    <header class="mb-4 d-flex align-items-center gap-2">
        {{-- Ikon Kunci Navy --}}
        <i class="fa-solid fa-key" style="color: #003380; font-size: 1.2rem;"></i>
        <h5 class="fw-bold mb-0" style="color: #003380;">Keamanan Password</h5>
    </header>

    <p class="text-muted small mb-4">
        {{ __('Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.') }}
    </p>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="row g-4">
            {{-- Password Saat Ini --}}
            <div class="col-12">
                <label for="update_password_current_password" class="form-label fw-bold small">{{ __('Password Saat Ini') }}</label>
                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-end-0 border-gray-300">
                        <i class="fa-solid fa-lock-open text-muted"></i>
                    </span>
                    <input type="password" id="update_password_current_password" name="current_password" 
                           class="form-control border-start-0 py-2 shadow-none border-gray-300" 
                           autocomplete="current-password" placeholder="••••••••" style="border-radius: 0 10px 10px 0;">
                </div>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-danger small" />
            </div>

            {{-- Password Baru --}}
            <div class="col-md-6">
                <label for="update_password_password" class="form-label fw-bold small">{{ __('Password Baru') }}</label>
                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-end-0 border-gray-300">
                        <i class="fa-solid fa-shield-halved text-muted"></i>
                    </span>
                    <input type="password" id="update_password_password" name="password" 
                           class="form-control border-start-0 py-2 shadow-none border-gray-300" 
                           autocomplete="new-password" placeholder="Min. 8 Karakter" style="border-radius: 0 10px 10px 0;">
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-danger small" />
            </div>

            {{-- Konfirmasi Password --}}
            <div class="col-md-6">
                <label for="update_password_password_confirmation" class="form-label fw-bold small">{{ __('Konfirmasi Password') }}</label>
                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-end-0 border-gray-300">
                        <i class="fa-solid fa-circle-check text-muted"></i>
                    </span>
                    <input type="password" id="update_password_password_confirmation" name="password_confirmation" 
                           class="form-control border-start-0 py-2 shadow-none border-gray-300" 
                           autocomplete="new-password" placeholder="Ulangi Password Baru" style="border-radius: 0 10px 10px 0;">
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-danger small" />
            </div>
        </div>

        {{-- Tombol Simpan Navy --}}
        <div class="mt-5 d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-navy px-4 py-2 fw-bold shadow-sm rounded-pill text-white" 
                    style="background: #003380; border: none; transition: 0.3s;">
                <i class="fa-solid fa-save me-2"></i>{{ __('Simpan Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" 
                     class="text-success small fw-bold">
                    <i class="fa-solid fa-circle-check me-1"></i> {{ __('Berhasil diperbarui.') }}
                </div>
            @endif
        </div>
    </form>
</section>