@php
    $allTypes = is_array($permit->permit_type) ? $permit->permit_type : [$permit->permit_type];
    $masterChecklist = App\Models\Permit::getMasterChecklist();
    $hazards = is_array($permit->hazards) ? $permit->hazards : json_decode($permit->hazards, true) ?? [];
    $apd = is_array($permit->ppe) ? $permit->ppe : json_decode($permit->ppe, true) ?? [];
    
    // Parse tools_used which may be stored as JSON array, plain string, or HTML from rich-editor (Quill)
    if (is_array($permit->tools_used)) {
        $tools = $permit->tools_used;
    } else {
        $decodedTools = json_decode($permit->tools_used, true);
        if (is_array($decodedTools)) {
            $tools = $decodedTools;
        } else {
            $rawTools = $permit->tools_used ?? '';
            // If HTML list (Quill), extract <li> contents
            if (strpos($rawTools, '<') !== false) {
                preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $rawTools, $matches);
                if (!empty($matches[1])) {
                    $tools = array_map('trim', array_map(function($v){ return strip_tags($v); }, $matches[1]));
                } else {
                    $text = trim(strip_tags($rawTools));
                    $tools = $text === '' ? [] : [$text];
                }
            } elseif (strpos($rawTools, ',') !== false) {
                $tools = array_map('trim', explode(',', $rawTools));
            } elseif ($rawTools === '') {
                $tools = [];
            } else {
                $tools = [trim($rawTools)];
            }
        }
    }
    $userAnswers = is_array($permit->safety_checklists) ? $permit->safety_checklists : json_decode($permit->safety_checklists, true) ?? [];

    // BIKIN FORMAT NOMOR PTW YANG DINAMIS
    $year = $permit->created_at ? $permit->created_at->format('Y') : date('Y');
    $dynamicPtwNumber = "PTW-OHSS-" . str_pad($permit->id, 3, '0', STR_PAD_LEFT) . "-" . $year;
@endphp

<!DOCTYPE html>
<html>
<head>
    <style>
        /* Gunakan landscape dan margin lebih kecil agar muat seperti referensi */
        @page { size: A4 landscape; margin: 14mm; }
        /* Set global font to 16 as requested */
        body { font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.15; color: #000; margin: 0; }

        .page-container { page-break-after: always; width: 100%; }
        .table-full { width: 100%; border-collapse: collapse; margin-bottom: 6px; table-layout: fixed; font-size: 16px; }
        .table-full td, .table-full th { border: 1px solid #000; padding: 3px 6px; vertical-align: middle; word-wrap: break-word; }

        /* Header dan section */
        .header-main { text-align: center; font-weight: bold; font-size: 16px; vertical-align: middle !important; }
        .section-blue { text-align: center; font-weight: bold; font-size: 16px; padding: 6px 0; }
        .section-blue-theme { background-color: #0b63a7; color: #fff; }

        .bg-grey { background-color: #f2f2f2; font-weight: bold; }
        .check-cell { width: 34px; text-align: center; font-weight: bold; font-size: 16px; }
        .signature-box { height: 70px; text-align: center; vertical-align: middle !important; }

        img.logo { max-width: 140px; height: auto; }
        .no-break { page-break-inside: avoid; }

        /* Smaller cells for dense tables */
        .small { font-size: 13px; padding: 3px 5px; }

        /* Compact mode for dense two-table page to force fit into single sheet */
        /* Compact scaling: keep font at 16 but slightly scale layout to fit one page */
        .compact { transform: scale(0.92); transform-origin: top left; }
        .compact .table-full { font-size: 16px; }
        .compact .table-full td, .compact .table-full th { padding: 2px 5px; }
        .compact .section-blue { font-size: 16px; padding: 3px 0; }
        .compact tr, .compact td, .compact th { page-break-inside: avoid; }

        /* Light grey subheading rows */
        .light-sub { background: #eeeeee; font-size: 14px; font-weight: bold; text-align: center; }

        /* Styling Tabel Persetujuan mirip Gambar 2 */
        .agreement-table { width: 100%; border-collapse: collapse; }
        .agreement-table td { border: 1px solid #000; padding: 10px; }

        .col-statement { width: 80%; text-align: center; vertical-align: middle !important; }
        .col-side { width: 20%; padding: 0 !important; }

        /* Tabel kecil di dalam kolom kanan agar Status & Setuju bertumpuk */
        .side-inner-table { width: 100%; border-collapse: collapse; height: 100%; }
        .side-inner-table td { 
            border: none !important; 
            border-bottom: 1px solid #000 !important; 
            text-align: center; 
            padding: 8px; 
            font-size: 14px;
        }
        .side-inner-table tr:last-child td { border-bottom: none !important; }

        .statement-title { font-weight: bold; font-size: 16px; margin-bottom: 5px; display: block; }
        .statement-body { font-size: 14px; line-height: 1.4; color: #333; }

        /* Aggressive compact layout for Pernyataan page to ensure single-sheet output */
        .compact-agreement { transform: scale(0.86); transform-origin: top left; }
        /* Prevent page breaks inside this container */
        .page-container.compact-agreement { page-break-after: avoid; page-break-inside: avoid; break-inside: avoid; }
        .compact-agreement .table-full td, .compact-agreement .table-full th { padding: 0px 2px; }
        .compact-agreement .section-blue { padding: 1px 0; font-size: 12px; }
        .compact-agreement .agreement-table { font-size: 12px; }
        .compact-agreement .col-statement { width: 82%; }
        .compact-agreement .col-side { width: 18%; }
        .compact-agreement .side-inner-table td { padding: 2px 4px; font-size: 11px; }
        .compact-agreement .side-inner-table tr:first-child td { font-size: 12px; padding: 4px 4px; }
        .compact-agreement .side-inner-table tr:last-child td { padding: 6px 4px; }
        .compact-agreement .statement-body { font-size: 12px; line-height: 1.05; }
        .accept { color: #1e9b28; font-weight: 700; font-size: 12px; }
        .compact-agreement .signature-box { height: 28px; }
        .compact-agreement .agreement-table { page-break-inside: avoid; break-inside: avoid; }
        .compact-agreement .table-full { page-break-inside: avoid; break-inside: avoid; }
        /* Single-table layout for info+agreement+signatures to avoid page breaks */
        .agreement-full { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .agreement-full td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        .agreement-full .info-left { width: 70%; }
        .agreement-full .info-right { width: 30%; }
        .agreement-full .no-border-top { border-top: none; }
        .agreement-full .sign-name { font-weight: bold; text-align: center; }
        .agreement-full { page-break-inside: avoid; break-inside: avoid; }
        
        /* CSS Khusus List Alat */
        .tools-list { margin: 0; padding-left: 20px; list-style-type: decimal; }
        .tools-list li { margin-bottom: 2px; }
    </style>
</head>
<body>

@foreach($allTypes as $type)
    @php $theme = App\Models\Permit::getPermitTheme($type); @endphp
    
    {{-- HALAMAN 1: INFORMASI UMUM --}}
    <div class="page-container">
        <table class="table-full">
            <tr>
                <td width="200px" style="text-align: center; background: #fff;">
                    <img src="{{ public_path('images/logo-batamindo.png') }}" style="width: 250px;">
                </td>
                <td class="header-main" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    PERMIT TO WORK - {{ strtoupper($type) }}
                    <br>
                    <span style="font-size: 14px;">{{ $dynamicPtwNumber }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Informasi Umum (Diisi dan dilengkapi oleh Permit Applicant)
                </td>
            </tr>
            <tr><td class="bg-grey">Permit to Work Number</td><td>{{ $dynamicPtwNumber }}</td></tr>
            <tr><td class="bg-grey">Berlaku Mulai</td><td>{{ optional($permit->valid_from)->format('m/d/Y h:i A') }}</td></tr>
            <tr><td class="bg-grey">Berlaku Sampai</td><td>{{ optional($permit->valid_until)->format('m/d/Y h:i A') }}</td></tr>
            <tr><td class="bg-grey">Nama Permit Applicant</td><td>{{ $permit->applicant_name }}</td></tr>
            <tr><td class="bg-grey">Perusahaan</td><td>{{ $permit->company }}</td></tr>
            <tr><td class="bg-grey">Pengguna (User)</td><td>{{ $permit->pic_batamindo ?? '-' }}</td></tr>
            <tr><td class="bg-grey">No. Handphone</td><td>{{ $permit->phone ?? '-' }}</td></tr>
            <tr><td class="bg-grey">Lokasi Pekerjaan</td><td>{{ $permit->location }}</td></tr>
            <tr>
                <td class="bg-grey">Alat dan Peralatan yang digunakan</td>
                <td>
                    {{-- UPDATE ALAT MENJADI LIST BERURUT (ANGKA) --}}
                    @if(is_array($tools) && count($tools) > 0)
                        <ol class="tools-list">
                            @foreach($tools as $t)
                                <li>{{ trim($t) }}</li>
                            @endforeach
                        </ol>
                    @elseif(!empty($permit->tools_used) && strpos($permit->tools_used, '<') !== false)
                        {{-- Jika input memang html dari quill --}}
                        {!! $permit->tools_used !!}
                    @else
                        {{ $permit->tools_used ?? '-' }}
                    @endif
                </td>
            </tr>
            <tr><td class="bg-grey">Referensi Dokumen</td><td>{{ $permit->ref_doc ?? 'Work Procedure' }}</td></tr>
            <tr><td class="bg-grey">Deskripsi lingkup kerja (besar)</td><td>{{ $permit->work_scope_general }}</td></tr>
            @php $detail = $permit->work_scope_detail ?? ''; @endphp
            <tr>
                <td class="bg-grey">Deskripsi lingkup kerja (rinci)</td>
                <td style="font-size: 16px;">
                    @if($detail && $detail !== strip_tags($detail))
                        {{-- Jika user menyimpan HTML (rich text), render HTML apa adanya --}}
                        {!! $detail !!}
                    @else
                        {{-- Jika plain text, escape lalu ubah newline menjadi <br> --}}
                        {!! nl2br(e($detail)) !!}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- HALAMAN 2: SUMBER BAHAYA & APD --}}
    <div class="page-container compact">
        <table class="table-full">
            <tr>
                <td colspan="10" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Sumber Bahaya
                </td>
            </tr>
            @php 
                $hazardList = ["Pengangkatan", "Mudah Meledak", "Listrik", "Kebisingan", "Benda Tajam", "Suhu Panas", "Mudah Terbakar", "Licin", "Longsor", "Benda Jatuh", "Air Bertekanan Tinggi", "Biologi", "Getaran", "Beracun", "Benda Bergerak", "Gas Bertekanan", "Psikologi", "Ergonomi", "Titik Buta", "Korosif", "Radiasi", "Kadar Oksigen", "Titik Jepit", "Angin", "Lainnya:"];
                $hazChunks = array_chunk($hazardList, 5); 
            @endphp
            @foreach($hazChunks as $row)
            <tr>
                @foreach($row as $h)
                    <td style="font-size: 16px;">{{ $h }}</td>
                    <td class="check-cell">{{ in_array($h, $hazards) ? 'X' : '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </table>

        <table class="table-full">
            <tr>
                <td colspan="10" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Alat Pelindung Diri
                </td>
            </tr>
            @foreach(App\Models\Permit::getPpeList() as $category => $items)
                @php $chunks = array_chunk($items, 4); @endphp
                @foreach($chunks as $idx => $chunk)
                <tr>
                    @if($idx == 0)
                        <td rowspan="{{ count($chunks) }}" class="bg-grey" style="width: 120px;">{{ $category }}</td>
                        <td rowspan="{{ count($chunks) }}" class="check-cell">{{ array_intersect($items, $apd) ? 'X' : '' }}</td>
                    @endif
                    @foreach($chunk as $item)
                        <td style="font-size: 16px;">{{ $item }}</td>
                        <td class="check-cell">{{ in_array($item, $apd) ? 'X' : '' }}</td>
                    @endforeach
                    @for($i = count($chunk); $i < 4; $i++) <td></td><td class="check-cell"></td> @endfor
                </tr>
                @endforeach
            @endforeach
        </table>
    </div>

    {{-- HALAMAN 3: SAFETY CHECKLIST --}}
    <div class="page-container">
        <table class="table-full">
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Safety Checklist: {{ strtoupper($type) }}
                </td>
            </tr>
            @if(isset($masterChecklist[$type]))
                @foreach($masterChecklist[$type] as $subJudul => $questions)
                <tr class="light-sub"><td colspan="3">{{ strtoupper($subJudul) }}</td></tr>
                    @foreach($questions as $q)
                    <tr>
                        <td class="check-cell" style="width: 40px;">{{ in_array($q, $userAnswers) ? 'X' : '' }}</td>
                        <td colspan="2">{{ $q }}</td>
                    </tr>
                    @endforeach
                @endforeach
            @endif
        </table>
    </div>

    {{-- HALAMAN 4: SATU KOTAK UTUH (Pernyataan Lengkap & Satu Halaman) --}}
    <div class="page-container compact-agreement" style="page-break-after: always; page-break-inside: avoid;">
        <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000;">
            {{-- I. INFORMASI LAINNYA --}}
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; border-bottom: 1px solid #000; text-align: center; font-weight: bold; padding: 10px; font-size: 16px;">
                    Informasi Lainnya
                </td>
            </tr>
            <tr>
                <td width="30%" style="border: 1px solid #000; padding: 15px; font-weight: bold; background-color: #f2f2f2;">Instruksi Tambahan</td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; min-height: 60px; vertical-align: top;">
                    {{ $permit->additional_instructions ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 15px; font-weight: bold; background-color: #f2f2f2;">Nama Personil K3 sebagai Perwakilan K3 perusahaan yang ditunjuk oleh Permit Applicant</td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; vertical-align: middle;">
                    {{ $permit->hse_representative ?? '-' }}
                </td>
            </tr>

            {{-- II. PERNYATAAN DAN PERSETUJUAN --}}
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; border: 1px solid #000; text-align: center; font-weight: bold; padding: 10px; font-size: 16px;">
                    Pernyataan dan Persetujuan
                </td>
            </tr>
            
            {{-- Baris Pernyataan Perusahaan --}}
            <tr>
                <td style="border: 2px solid #000; text-align: center; padding: 20px; vertical-align: middle; width: 80%;">
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Pernyataan Perusahaan</strong>
                    <div style="font-size: 14px; line-height: 1.5; text-align: justify; padding: 0 10px;">
                        Bahwa dengan segala ketentuan diatas maka kami dari Perusahaan Pelaksana Pekerjaan terkait Izin Kerja ini akan bertanggung jawab atas segala kepatuhan akan ketentuan dan aturan UU dan akan melaksanakan semua kegiatan di lokasi kerja BIC dengan tetap mempertahankan nilai-nilai moral kemanusiaan serta penerapan K3 Perusahaan dengan sebaik-baiknya.
                    </div>
                </td>
                <td colspan="2" style="padding: 0; border: 2px solid #000; width: 80px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="border-bottom: 2px solid #000; text-align: center; padding: 4px; font-weight: bold; font-size:12px;">Status</td></tr>
                        <tr><td style="text-align: center; padding: 6px; font-weight: 700; font-size:12px; color: #000000;">{{ $permit->agreed_to_terms ? 'SETUJU' : '-' }}</td></tr>
                    </table>
                </td>
            </tr>

            {{-- Baris Permit Applicant --}}
            <tr>
                <td style="border: 2px solid #000; text-align: center; padding: 20px; vertical-align: middle;">
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Permit Applicant</strong>
                    <div style="font-size: 14px; line-height: 2; text-align: justify; padding: 0 10px;">
                        (Saya sudah memeriksa Kondisi kerja dan memastikan aspek keselamatannya. Semua kondisi yang ditentukan dalam permit ini harus dilengkapi di lokasi kerja sebelum pekerjaan dimulai)
                    </div>
                </td>
                <td colspan="2" style="padding: 0; border: 2px solid #000; width: 80px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="border-bottom: 2px solid #000; text-align: center; padding: 4px; font-weight: bold; font-size:12px;">Status</td></tr>
                        <tr><td style="text-align: center; padding: 6px; font-weight: 700; font-size:12px; color: #000000;">{{ $permit->applicant_confirmation ? 'SETUJU' : '-' }}</td></tr>
                    </table>
                </td>
            </tr>

            {{-- III. TANDA TANGAN --}}
            <tr>
                <td colspan="3" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="text-align: center; font-weight: bold; font-size: 14px; background-color: #f2f2f2;">
                            <td style="border-right: 2px solid #000; border-bottom: 1px solid #000; padding: 8px; width: 50%;">Nama Pimpinan / Manager Perusahaan Pelaksana Pekerjaan:</td>
                            <td style="border-bottom: 2px solid #000; padding: 8px; width: 50%;">Nama Permit Applicant:</td>
                        </tr>
                        <tr style="text-align: center; font-weight: bold;">
                            <td style="border-right: 2px solid #000; padding: 10px;">{{ $permit->manager_name ?? '-' }}</td>
                            <td style="border-left: 2px solid #000; padding: 10px;">{{ $permit->applicant_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border-right: 2px solid #000; height: 80px; text-align: center; vertical-align: middle;">
                                @if($permit->signature_manager)
                                        <img src="{{ $permit->signature_manager }}" style="height: 40px;">
                                @endif
                            </td>
                            <td style="border-left: 2px solid #000; height: 80px; text-align: center; vertical-align: middle;">
                                @if($permit->signature_applicant)
                                    <img src="{{ $permit->signature_applicant }}" style="height: 40px;">
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
@endforeach

</body>
</html>