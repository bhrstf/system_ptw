document.addEventListener("DOMContentLoaded", function () {

    console.log("PTW Script Loaded!");

    /* =========================
       1. QUILL EDITOR
    ========================= */
    const quillTools = new Quill('#editor-tools', {
        theme: 'snow',
        placeholder: 'Tuliskan alat dan peralatan yang akan digunakan...'
    });

    const quillScope = new Quill('#editor-scope', {
        theme: 'snow',
        placeholder: 'Tuliskan deskripsi lingkup kerja secara detail...'
    });


    /* =========================
       2. PERMIT TYPE & CHECKLIST (LOGIK BARU)
    ========================= */
    // Fungsi ini dipanggil saat ikon Permit Type diklik
    window.togglePWT = function (input) {
        const type = input.value;
        // Ubah "Hot Work" menjadi "hot-work" agar sesuai dengan ID di Blade
        const slug = type.toLowerCase().replace(/\s+/g, '-');
        const section = document.getElementById('section-' + slug);
        const label = input.closest('.pwt-item');

        if (input.checked) {
            // Aktifkan visual ikon
            if (label) label.classList.add('active');
            // Munculkan section checklist yang sesuai
            if (section) section.classList.remove('d-none');
        } else {
            // Matikan visual ikon
            if (label) label.classList.remove('active');
            // Sembunyikan section checklist
            if (section) {
                section.classList.add('d-none');
                // Uncheck semua box di dalamnya agar tidak terkirim data sampah
                section.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            }
        }
    };

    /* =========================
       3. SIGNATURE PAD (FIX RESIZE)
    ========================= */
    const canvasM = document.getElementById('padM');
    const canvasA = document.getElementById('padA');

    let padM = null;
    let padA = null;

    if (canvasM && canvasA) {
        padM = new SignaturePad(canvasM);
        padA = new SignaturePad(canvasA);
    }

    function resizeCanvas() {
        if (!canvasM || !canvasA) return;

        [canvasM, canvasA].forEach(c => {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const pad = (c.id === 'padM') ? padM : padA;
            
            // Simpan data lama agar tidak hilang saat resize
            const data = pad.toData();
            
            c.width = c.offsetWidth * ratio;
            c.height = c.offsetHeight * ratio;
            c.getContext("2d").scale(ratio, ratio);
            
            pad.clear(); 
            pad.fromData(data);
        });
    }

    window.addEventListener('resize', resizeCanvas);
    // Jalankan resize saat pertama kali muat
    setTimeout(resizeCanvas, 500); 

    /* =========================
       4. FORM SUBMIT
    ========================= */
    const permitForm = document.getElementById('permitForm');

    if (permitForm) {
        permitForm.addEventListener("submit", function (e) {
            const selectedPermit = document.querySelectorAll('input[name="permit_type[]"]:checked');

            // Validasi Minimal 1 Permit
            if (selectedPermit.length === 0) {
                e.preventDefault();
                Swal.fire('Oops!', 'Harap pilih minimal satu jenis permit!', 'warning');
                return;
            }

            // Validasi Tanda Tangan
            if (!padM || !padA || padM.isEmpty() || padA.isEmpty()) {
                e.preventDefault();
                Swal.fire('Perhatian', 'Harap lengkapi tanda tangan Manager dan Applicant!', 'warning');
                return;
            }

            /* Copy Quill Data ke Hidden Input */
            const toolsInput = document.getElementById('tools_used');
            const scopeInput = document.getElementById('work_scope_detail');
            if (toolsInput) toolsInput.value = quillTools.root.innerHTML;
            if (scopeInput) scopeInput.value = quillScope.root.innerHTML;

            /* Copy Signature ke Hidden Input */
            const sm = document.getElementById('sm');
            const sa = document.getElementById('sa');
            if (sm) sm.value = padM.toDataURL();
            if (sa) sa.value = padA.toDataURL();

            /* Loading Popup */
            Swal.fire({
                title: 'Mengirim Pengajuan...',
                text: 'Mohon tunggu sebentar ya!',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            return true;
        });
    }

    /* =========================
       5. SUCCESS MESSAGE
    ========================= */
    const successInput = document.getElementById('session-success');
    if (successInput && successInput.value) {
        Swal.fire({
            title: 'Berhasil!',
            text: successInput.value,
            icon: 'success',
            confirmButtonText: 'Ke Dashboard'
        }).then(() => {
            window.location.href = "/dashboard";
        });
    }
});