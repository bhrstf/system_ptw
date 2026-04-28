<div class="content-body px-4 pb-5">
        <section>
            <header class="mb-4">
                <h5 class="fw-bold mb-1" style="color: #003380;">Informasi Data Diri</h5>
                <p class="text-muted small">Lengkapi biodata Anda untuk keperluan administrasi Permit to Work (PTW).</p>
            </header>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                @method('patch')

                <div class="row g-4">
                    <div class="col-12 mb-3">
                        <div class="card border-0 bg-light rounded-4 p-4 shadow-sm">
                            <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap justify-content-center justify-content-md-start text-center text-md-start">
                                <div class="position-relative">
                                     {{-- Preview Foto Utama --}}
                                     <img id="preview" 
                                         src="{{ Auth::user()->profile_photo_url }}" 
                                         class="rounded-circle border border-4 border-white shadow-sm" 
                                         width="120" height="120" style="object-fit: cover; transition: 0.3s;">
                                    
                                    <label for="photo" class="position-absolute bottom-0 end-0 text-white rounded-circle shadow d-flex align-items-center justify-content-center" 
                                           style="width: 38px; height: 38px; cursor: pointer; background: #003380; border: 3px solid white;">
                                        <i class="fa-solid fa-camera fa-sm"></i>
                                    </label>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1" style="color: #003380;">Foto Identitas <span class="text-danger">*</span></h6>
                                    <p class="text-muted small mb-3">Gunakan foto formal latar belakang polos. Maks 2MB.</p>
                                    <input type="file" name="photo" id="photo" class="form-control form-control-sm rounded-pill border-0 shadow-sm px-3" accept="image/*" onchange="previewImageSync(this)">
                                    @error('photo')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-color: #dee2e6;"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" id="name" name="name" class="form-control border-start-0 rounded-end-3 py-2 shadow-none border-gray-300" value="{{ old('name', $user->name) }}" required style="border-color: #dee2e6;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-bold small">Nomor Telepon / WA</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-color: #dee2e6;"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="text" id="phone" name="phone" class="form-control border-start-0 rounded-end-3 py-2 shadow-none border-gray-300" value="{{ old('phone', $user->phone) }}" style="border-color: #dee2e6;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold small text-muted">Email Perusahaan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3" style="border-color: #dee2e6;"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" id="email" name="email" class="form-control bg-light border-start-0 rounded-end-3 py-2 border-gray-300" value="{{ old('email', $user->email) }}" readonly style="border-color: #dee2e6;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="company" class="form-label fw-bold small">Nama Perusahaan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3" style="border-color: #dee2e6;">
                                <i class="fa-solid fa-building text-muted"></i>
                            </span>
                            <input type="text" 
                                id="company" 
                                name="company" 
                                class="form-control border-start-0 rounded-end-3 py-2 shadow-none" 
                                style="border-color: #dee2e6;"
                                placeholder="Masukkan nama perusahaan"
                                value="{{ old('company', $user->company) }}" 
                                required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-bold small">Alamat Lengkap / Departemen</label>
                        <textarea id="address" name="address" class="form-control rounded-3 shadow-none border-gray-300" rows="3" style="border-color: #dee2e6;">{{ old('address', $user->address) }}</textarea>
                    </div>
                </div>

                <div class="mt-5 d-flex align-items-center gap-3">
                    <button type="submit" class="btn px-4 py-2 fw-bold shadow-sm rounded-pill text-white" style="background: #003380; border: none;">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan
                    </button>
                    @if (session('status') === 'profile-updated')
                        <div class="text-success small fw-bold">
                            <i class="fa-solid fa-circle-check me-1"></i> Berhasil disimpan.
                        </div>
                    @endif
                </div>
            </form>
        </section>
    </div>

<script>
    // Preview foto sinkron ke semua tempat (Sidebar, Header, Form)
    function previewImageSync(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Update foto di form ini (preview kecil)
                const p = document.getElementById('preview');
                if (p) p.src = e.target.result;

                // Update banner profil (besar di halaman edit)
                const bp = document.getElementById('banner-preview');
                if (bp) bp.src = e.target.result;

                // Update avatar kecil di header
                const hp = document.getElementById('header-preview');
                if (hp) hp.src = e.target.result;

                // Update avatar besar di dropdown header
                const hl = document.getElementById('header-large-preview');
                if (hl) hl.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>