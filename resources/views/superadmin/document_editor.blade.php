@extends('layouts.app')

@section('content')
<style>
    /* Styling agar editor terlihat profesional */
    .pdf-editor-header { background: #1e293b; color: white; border-radius: 10px 10px 0 0; }
    .editor-body { background: #475569; min-height: 85vh; display: flex; justify-content: center; padding: 30px; overflow: auto; }
    .canvas-wrapper { background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
</style>

<div class="container-fluid py-4">
    {{-- Header Editor --}}
    <div class="pdf-editor-header d-flex justify-content-between p-3 shadow">
        <div>
            <i class="fas fa-file-pdf text-danger me-2"></i>
            <strong>PRO Master Editor</strong> | Mengedit: {{ strtoupper(str_replace('_', ' ', $field)) }}
        </div>
        <div class="d-flex gap-2">
            <button onclick="addText()" class="btn btn-sm btn-outline-light"><i class="fas fa-font"></i> Tambah Teks</button>
            <button onclick="addEraser()" class="btn btn-sm btn-outline-warning"><i class="fas fa-eraser"></i> Hapus / Tutup Isi</button>
            <button onclick="saveDocument()" class="btn btn-sm btn-success fw-bold px-3">SIMPAN PERMANEN</button>
        </div>
    </div>

    {{-- Area Meja Kerja --}}
    <div class="editor-body shadow-inner">
        <div class="canvas-wrapper">
            <canvas id="mainCanvas"></canvas>
        </div>
    </div>
</div>

{{-- Form untuk Kirim ke Controller --}}
<form id="saveForm" action="{{ route('superadmin.save_document', [$permit->id, $field]) }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="edited_image" id="edited_image">
</form>

{{-- Library Renderer (WAJIB ADA) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>

<script>
    // 1. Ambil data PDF dari Controller (Pastikan Controller sudah pakai Base64)
    const pdfData = '{{ $documentUrl }}'; 
    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    var canvas = new fabric.Canvas('mainCanvas');

    // 2. FUNGSI RENDER: Bongkar PDF jadi Background (Solusi Tanda Silang)
    async function loadPDF() {
    try {
        // Ambil data Base64 dari Controller
        const loadingTask = pdfjsLib.getDocument(pdfData);
        const pdf = await loadingTask.promise;
        const page = await pdf.getPage(1); 

        const scale = 2.0; // Biar rincian pekerjaan "Modification pipe line" tajam [cite: 2]
        const viewport = page.getViewport({ scale: scale });

        const tempCanvas = document.createElement('canvas');
        const context = tempCanvas.getContext('2d');
        tempCanvas.height = viewport.height;
        tempCanvas.width = viewport.width;

        await page.render({ canvasContext: context, viewport: viewport }).promise;

        // Ubah hasil render PDF jadi PNG
        const bgImage = tempCanvas.toDataURL('image/png');
        
        // Pasang ke Meja Kerja (Fabric Canvas)
        fabric.Image.fromURL(bgImage, function(img) {
            canvas.setDimensions({ width: img.width / 1.5, height: img.height / 1.5 });
            img.set({ scaleX: 1/1.5, scaleY: 1/1.5 });
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
        });
    } catch (error) {
        console.error("Gagal render: ", error);
        alert("Aduh bebe, cek terminal VS Code kamu, apakah sudah jalan 'php artisan storage:link'?");
    }
}

    renderPDF(); // Jalankan renderer!

    // 3. FITUR KETIK REVISI
    function addText() {
        var text = new fabric.IText('Ketik revisi di sini...', {
            left: 50, top: 50, fontSize: 18, fill: '#000', backgroundColor: '#fff'
        });
        canvas.add(text);
        canvas.setActiveObject(text);
    }

    // 4. FITUR HAPUS (PAKAI KOTAK PUTIH)
    function addEraser() {
        var rect = new fabric.Rect({
            left: 100, top: 100, width: 150, height: 35,
            fill: '#fff', stroke: '#007bff', strokeWidth: 1, strokeDashArray: [5, 5]
        });
        canvas.add(rect);
        canvas.setActiveObject(rect);
    }

    // 5. SIMPAN DAN TIMPA FILE
    function saveDocument() {
        if (confirm('Simpan perubahan dan timpa file asli secara permanen?')) {
            canvas.getObjects('rect').forEach(obj => obj.set({ strokeWidth: 0 }));
            
            var dataURL = canvas.toDataURL({ format: 'png', multiplier: 2 });
            document.getElementById('edited_image').value = dataURL;
            document.getElementById('saveForm').submit();
        }
    }

    // Shortcut Hapus stiker yang salah
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Delete') { canvas.remove(canvas.getActiveObject()); }
    });
</script>
@endsection