@php
    use Illuminate\Support\Str;
    
    $allTypes = is_array($permit->permit_type) ? $permit->permit_type : [$permit->permit_type];
    $masterChecklist = App\Models\Permit::getMasterChecklist();
    $hazards = is_array($permit->hazards) ? $permit->hazards : json_decode($permit->hazards, true) ?? [];
    $apd = is_array($permit->ppe) ? $permit->ppe : json_decode($permit->ppe, true) ?? [];
    
    // Parse tools_used
    if (is_array($permit->tools_used)) {
        $tools = $permit->tools_used;
    } else {
        $decodedTools = json_decode($permit->tools_used, true);
        if (is_array($decodedTools)) {
            $tools = $decodedTools;
        } else {
            $rawTools = $permit->tools_used ?? '';
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

    // Decode Hazards Other (Sumber Bahaya Lainnya)
    $hazardsOther = '';
    if (!empty($permit->hazards_other)) {
        if (is_array($permit->hazards_other)) {
            $hazardsOther = implode(', ', array_filter($permit->hazards_other));
        } elseif (is_string($permit->hazards_other)) {
            $decodedHaz = json_decode($permit->hazards_other, true);
            if (is_array($decodedHaz)) {
                $hazardsOther = implode(', ', array_filter($decodedHaz));
            } else {
                $hazardsOther = trim($permit->hazards_other);
            }
        }
    }

    // Decode PPE Other (APD Lainnya per Kategori)
    $ppeOther = [];
    if (!empty($permit->ppe_other)) {
        if (is_array($permit->ppe_other)) {
            $ppeOther = $permit->ppe_other;
        } elseif (is_string($permit->ppe_other)) {
            $ppeOther = json_decode($permit->ppe_other, true) ?? [];
            if (!is_array($ppeOther)) {
                $ppeOther = [$permit->ppe_other];
            }
        }
    }

    // Format Nomor PTW Dinamis
    $year = $permit->created_at ? $permit->created_at->format('Y') : date('Y');
    $dynamicPtwNumber = $permit->ptw_number ?? ("PTW-OHSS-" . str_pad($permit->id, 3, '0', STR_PAD_LEFT) . "-" . $year);
@endphp

<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.15; color: #000; margin: 0; }

        .page-container { page-break-after: always; page-break-inside: avoid; width: 100%; box-sizing: border-box; }
        .page-container:last-child { page-break-after: avoid; }

        .table-full { width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: fixed; font-size: 14px; }
        .table-full td, .table-full th { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; word-wrap: break-word; }

        .header-main { text-align: center; font-weight: bold; font-size: 16px; vertical-align: middle !important; }
        .section-blue { text-align: center; font-weight: bold; font-size: 15px; padding: 5px 0; }

        .bg-grey { background-color: #f2f2f2; font-weight: bold; }
        .check-cell { width: 34px; text-align: center; font-weight: bold; font-size: 14px; }

        .tools-list { margin: 0; padding-left: 20px; list-style-type: decimal; }
        .tools-list li { margin-bottom: 1px; }

        .light-sub { background: #eeeeee; font-size: 13px; font-weight: bold; text-align: center; }
        
        /* Style text jawaban dinamis lainnya */
        .inline-answer { font-weight: normal; color: #000; margin-left: 4px; }
        
        .input-textbox-wrapper { margin-top: 4px; margin-left: 20px; padding: 5px 8px; background-color: #f9f9f9; border: 1px solid #bbb; border-radius: 2px; width: 90%; }
        .input-textbox-label { font-weight: bold; color: #333; margin-bottom: 2px; display: block; font-size: 11px; text-transform: uppercase; }
        .textbox-content { font-weight: normal; color: #000; font-size: 12px; word-break: break-word; line-height: 1.3; }

        .compact-h2 .table-full { font-size: 13px; margin-bottom: 10px; }
        .compact-h2 .table-full td { padding: 3px 5px; }
        .compact-h2 .check-cell { width: 28px; }

        .compact-h4 .table-full td { padding: 8px 10px; }
        .inner-status-table { width: 100%; border-collapse: collapse; }
        .inner-status-table td { border: none !important; text-align: center; padding: 5px !important; }
        .inner-status-table tr:first-child td { border-bottom: 1px solid #000 !important; font-weight: bold; background-color: #f2f2f2; }
    </style>
</head>
<body>

@foreach($allTypes as $type)
    @php $theme = App\Models\Permit::getPermitTheme($type); @endphp
    
    {{-- HALAMAN 1: INFORMASI UMUM --}}
    <div class="page-container">
        <table class="table-full">
            <tr>
                <td width="220px" style="text-align: center; background: #fff;">
                    <img src="{{ public_path('images/logo-batamindo.png') }}" style="width: 200px;">
                </td>
                <td class="header-main" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    PERMIT TO WORK - {{ strtoupper($type) }}
                    <br>
                    <span style="font-size: 13px; font-weight: normal;">{{ $dynamicPtwNumber }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Informasi Umum (Diisi dan dilengkapi oleh Permit Applicant)
                </td>
            </tr>
            <tr><td class="bg-grey" width="30%">Permit to Work Number</td><td>{{ $dynamicPtwNumber }}</td></tr>
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
                    @if(is_array($tools) && count($tools) > 0)
                        <ol class="tools-list">
                            @foreach($tools as $t)
                                <li>{{ trim($t) }}</li>
                            @endforeach
                        </ol>
                    @elseif(!empty($permit->tools_used) && strpos($permit->tools_used, '<') !== false)
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
                <td>
                    @if($detail && $detail !== strip_tags($detail))
                        {!! $detail !!}
                    @else
                        {!! nl2br(e($detail)) !!}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- HALAMAN 2: SUMBER BAHAYA & APD --}}
    <div class="page-container compact-h2">
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
                    @php
                        $cleanH = trim(str_replace(':', '', $h));
                        // Checkbox khusus Lainnya di Sumber Bahaya hanya aktif jika teks hazardsOther tidak kosong
                        if ($cleanH === 'Lainnya') {
                            $isCheck = !empty($hazardsOther);
                        } else {
                            $isCheck = in_array($h, $hazards) || in_array($cleanH, $hazards);
                        }
                    @endphp
                    <td>
                        {{ $h }}
                        @if(($cleanH === 'Lainnya' || $h === 'Lainnya:') && !empty($hazardsOther))
                            <span class="inline-answer">: {{ $hazardsOther }}</span>
                        @endif
                    </td>
                    <td class="check-cell">{{ $isCheck ? 'X' : '' }}</td>
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
                        <td rowspan="{{ count($chunks) }}" class="bg-grey" style="width: 140px;">{{ $category }}</td>
                        <td rowspan="{{ count($chunks) }}" class="check-cell" style="width: 34px;">{{ array_intersect($items, $apd) ? 'X' : '' }}</td>
                    @endif
                    @foreach($chunk as $item)
                        @php
                            $cleanItem = trim($item);
                            
                            // Ambil nilai text tambahan dinamis dari ppe_other berdasarkan kategori saat ini
                            $currentPpeOtherValue = '';
                            if (!empty($ppeOther)) {
                                if (isset($ppeOther[$category])) {
                                    $currentPpeOtherValue = $ppeOther[$category];
                                } else {
                                    foreach($ppeOther as $k => $v) {
                                        if (strtolower(trim($k)) === strtolower(trim($category))) {
                                            $currentPpeOtherValue = $v;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (is_array($currentPpeOtherValue)) {
                                $currentPpeOtherValue = implode(', ', array_filter($currentPpeOtherValue));
                            }
                            $currentPpeOtherValue = trim($currentPpeOtherValue);

                            // KUNCI SOLUSI: Jika nama item adalah 'Lainnya', 
                            // dia HANYA boleh dicentang X jika text box kategori tersebut memang ada isinya di database.
                            if ($cleanItem === 'Lainnya' || $cleanItem === 'Lain-lain') {
                                $isPpeCheck = !empty($currentPpeOtherValue);
                            } else {
                                $isPpeCheck = in_array($item, $apd) || in_array($cleanItem, $apd);
                            }
                        @endphp
                        <td>
                            {{ $item }}
                            @if(($cleanItem === 'Lainnya' || $cleanItem === 'Lain-lain') && !empty($currentPpeOtherValue))
                                <span class="inline-answer">: {{ $currentPpeOtherValue }}</span>
                            @endif
                        </td>
                        <td class="check-cell">{{ $isPpeCheck ? 'X' : '' }}</td>
                    @endforeach
                    @for($i = count($chunk); $i < 4; $i++) <td></td><td class="check-cell"></td> @endfor
                </tr>
                @endforeach
            @endforeach
        </table>
    </div>

    {{-- HALAMAN 3: SAFETY CHECKLIST --}}
    <div class="page-container">
        <table class="table-full" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }};">
                    Safety Checklist: {{ strtoupper($type) }}
                </td>
            </tr>
            @if(isset($masterChecklist[$type]))
                @foreach($masterChecklist[$type] as $subJudul => $questions)
                <tr class="light-sub"><td colspan="3">{{ strtoupper($subJudul) }}</td></tr>
                    @foreach($questions as $q)
                        @php
                            // 1. Ambil teks asli pertanyaan dan bersihkan untuk pencarian data
                            $textPertanyaan = is_array($q) ? ($q['text'] ?? '') : $q;
                            
                            $cleanText = str_replace('&nbsp;', ' ', strip_tags($textPertanyaan));
                            $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText)); 
                            $lowerText = strtolower($cleanText);

                            $hasInputTambahan = is_array($q) && isset($q['input_tambahan']);
                            $inputTambahan = $hasInputTambahan ? $q['input_tambahan'] : null;
                            
                            $fieldValue = null;
                            $inputLabel = 'Keterangan';

                            // Ambil data dari safety_checklists_other
                            $checklistOtherData = [];
                            if (!empty($permit->safety_checklists_other)) {
                                $checklistOtherData = is_array($permit->safety_checklists_other) ? $permit->safety_checklists_other : (json_decode($permit->safety_checklists_other, true) ?? []);
                            }

                            // Cek jawaban user dengan teks yang sudah dibersihkan
                            $isRowChecked = false;
                            if (isset($userAnswers) && is_array($userAnswers)) {
                                foreach ($userAnswers as $ans) {
                                    $cleanAns = str_replace('&nbsp;', ' ', strip_tags($ans));
                                    $cleanAns = trim(preg_replace('/\s+/', ' ', $cleanAns));
                                    if (strtolower($cleanAns) === $lowerText) {
                                        $isRowChecked = true;
                                        break;
                                    }
                                }
                            }

                            // Proses pencarian data jika memiliki input tambahan
                            if ($hasInputTambahan && $inputTambahan) {
                                $inputName = $inputTambahan['name'] ?? '';
                                $inputLabel = $inputTambahan['label'] ?? 'Keterangan';

                                if (!empty($checklistOtherData) && is_array($checklistOtherData)) {
                                    if (isset($checklistOtherData[$inputName]) && !empty($checklistOtherData[$inputName])) {
                                        $fieldValue = $checklistOtherData[$inputName];
                                    } elseif (isset($checklistOtherData[$inputLabel]) && !empty($checklistOtherData[$inputLabel])) {
                                        $fieldValue = $checklistOtherData[$inputLabel];
                                    } else {
                                        foreach ($checklistOtherData as $key => $val) {
                                            if (empty($val)) continue;
                                            $lowKey = strtolower($key);

                                            if (strpos($lowerText, 'tangki') !== false || strpos($lowerText, 'peralatan telah diidentifikasi') !== false) {
                                                if (strpos($lowKey, 'tangki') !== false || strpos($lowKey, 'isi') !== false || strpos($lowKey, 'sebelumnya') !== false) {
                                                    $fieldValue = $val;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }

                                // Fallback database root attributes
                                if (empty($fieldValue)) {
                                    switch (strtolower($inputName)) {
                                        case 'rencana_durasi_bypass_jam':
                                        case 'bypass_duration':
                                            $fieldValue = $permit->rencana_durasi_bypass_jam ?? $permit->bypass_duration ?? null;
                                            break;
                                        case 'jumlah_titik_isolasi':
                                        case 'isolation_points_count':
                                            $fieldValue = $permit->jumlah_titik_isolasi ?? $permit->isolation_points_count ?? null;
                                            break;
                                        case 'penjelasan_zero_energy':
                                        case 'zero_energy_explanation':
                                            $fieldValue = $permit->penjelasan_zero_energy ?? $permit->zero_energy_explanation ?? null;
                                            break;
                                        default:
                                            if (isset($permit->{$inputName}) && !empty($permit->{$inputName})) {
                                                $fieldValue = $permit->{$inputName};
                                            }
                                    }
                                }
                            } elseif (stripos($lowerText, 'lainnya') !== false || stripos($lowerText, 'lain-lain') !== false) {
                                $subJudulSlug = Str::slug($subJudul);
                                if (isset($checklistOtherData[$subJudulSlug]) && !empty($checklistOtherData[$subJudulSlug])) {
                                    $fieldValue = $checklistOtherData[$subJudulSlug];
                                    $inputLabel = 'Lainnya';
                                } else {
                                    foreach ($checklistOtherData as $key => $val) {
                                        if (empty($val)) continue;
                                        $lowKey = strtolower($key);
                                        if (strpos($lowKey, 'lain') !== false && (strpos($lowKey, strtolower($type)) !== false || count($checklistOtherData) == 1)) {
                                            $fieldValue = $val;
                                            $inputLabel = 'Lainnya';
                                            break;
                                        }
                                    }
                                }
                            }

                            if (stripos($lowerText, 'lainnya') !== false || stripos($lowerText, 'lain-lain') !== false) {
                                if (!empty($fieldValue)) {
                                    $isRowChecked = true;
                                }
                            }
                        @endphp
                        
                        {{-- JIKA ADA DATA KETERANGAN / TEXTBOX --}}
                        @if(!empty($fieldValue))
                            @php $isRowChecked = true; @endphp
                            <tr>
                                <td class="check-cell" rowspan="2" style="width: 40px; text-align: center; vertical-align: middle; padding: 8px 4px; font-size: 13px; border-bottom: 1px solid #000;">X</td>
                                
                                <td colspan="2" style="padding: 8px 8px; line-height: 1.4; vertical-align: middle; font-size: 13px; color: #000; border-bottom: none;">
                                    {!! $textPertanyaan !!}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding: 6px 8px; font-size: 13px; color: #000; font-weight: normal; background-color: #fff; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                                    {{ $inputLabel }} : {{ $fieldValue }}
                                </td>
                            </tr>
                        @else
                            {{-- JIKA TIDAK ADA KETERANGAN (BARIS BIASA KONDISI NORMAL) --}}
                            <tr>
                                <td class="check-cell" style="width: 40px; text-align: center; vertical-align: middle; padding: 8px 4px; font-size: 13px;">{{ $isRowChecked ? 'X' : '' }}</td>
                                <td colspan="2" style="padding: 8px 8px; line-height: 1.4; vertical-align: middle; font-size: 13px; color: #000;">
                                    {!! $textPertanyaan !!}
                                </td>
                            </tr>
                        @endif

                    @endforeach
                @endforeach
            @endif
        </table>
    </div>

    {{-- HALAMAN 4: INFORMASI LAINNYA & PERNYATAAN DAN PERSETUJUAN --}}
    <div class="page-container compact-h4">
        <table class="table-full" style="border: 1.5px solid #000;">
            {{-- I. INFORMASI LAINNYA --}}
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; border-bottom: 1px solid #000;">
                    Informasi Lainnya
                </td>
            </tr>
            <tr>
                <td width="25%" style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #f2f2f2;">Instruksi Tambahan</td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; min-height: 40px; vertical-align: top;">
                    {{ $permit->additional_instructions ?? '-' }}
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 10px; font-weight: bold; background-color: #f2f2f2;">Nama Personil K3 sebagai Perwakilan K3 perusahaan yang ditunjuk oleh Permit Applicant</td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; vertical-align: middle;">
                    {{ $permit->hse_representative ?? '-' }}
                </td>
            </tr>

            {{-- II. PERNYATAAN DAN PERSETUJUAN --}}
            <tr>
                <td colspan="3" class="section-blue" style="background-color: {{ $theme['bg'] }}; color: {{ $theme['text'] }}; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                    Pernyataan dan Persetujuan
                </td>
            </tr>
            
            {{-- Baris Pernyataan Perusahaan --}}
            <tr>
                <td style="border: 1px solid #000; text-align: left; padding: 12px; vertical-align: middle; width: 85%;">
                    <strong style="display: block; margin-bottom: 4px; font-size: 14px;">Pernyataan Perusahaan</strong>
                    <div style="font-size: 13px; line-height: 1.4; text-align: justify;">
                        Bahwa dengan segala ketentuan diatas maka kami dari Perusahaan Pelaksana Pekerjaan terkait Izin Kerja ini akan bertanggung jawab atas segala kepatuhan akan ketentuan dan aturan UU dan akan melaksanakan semua kegiatan di lokasi kerja BIC dengan tetap mempertahankan nilai-nilai moral kemanusiaan serta penerapan K3 Perusahaan dengan sebaik-baiknya.
                    </div>
                </td>
                <td colspan="2" style="padding: 0; border: 1px solid #000; width: 15%; vertical-align: stretch;">
                    <table class="inner-status-table">
                        <tr><td>Status</td></tr>
                        <tr><td style="padding: 15px 0 !important; font-weight: bold; font-size: 14px;">{{ $permit->agreed_to_terms ? 'SETUJU' : '-' }}</td></tr>
                    </table>
                </td>
            </tr>

            {{-- Baris Permit Applicant --}}
            <tr>
                <td style="border: 1px solid #000; text-align: left; padding: 12px; vertical-align: middle;">
                    <strong style="display: block; margin-bottom: 4px; font-size: 14px;">Permit Applicant</strong>
                    <div style="font-size: 13px; line-height: 1.4; text-align: justify;">
                        (Saya sudah memeriksa Kondisi kerja dan memastikan aspek keselamatannya. Semua kondisi yang ditentukan dalam permit ini harus dilengkapi di lokasi kerja sebelum pekerjaan dimulai)
                    </div>
                </td>
                <td colspan="2" style="padding: 0; border: 1px solid #000; width: 15%; vertical-align: stretch;">
                    <table class="inner-status-table">
                        <tr><td>Status</td></tr>
                        <tr><td style="padding: 15px 0 !important; font-weight: bold; font-size: 14px;">{{ $permit->applicant_confirmation ? 'SETUJU' : '-' }}</td></tr>
                    </table>
                </td>
            </tr>

            {{-- III. TANDA TANGAN --}}
            <tr>
                <td colspan="3" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <tr style="text-align: center; font-weight: bold; font-size: 13px; background-color: #f2f2f2;">
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 6px; width: 50%;">Nama Pimpinan / Manager Perusahaan Pelaksana Pekerjaan:</td>
                            <td style="border-bottom: 1px solid #000; padding: 6px; width: 50%;">Nama Permit Applicant:</td>
                        </tr>
                        <tr style="text-align: center; font-weight: bold;">
                            <td style="border-right: 1px solid #000; padding: 6px; font-size: 13px;">{{ $permit->manager_name ?? '-' }}</td>
                            <td style="padding: 6px; font-size: 13px;">{{ $permit->applicant_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; height: 65px; text-align: center; vertical-align: middle;">
                                @if($permit->signature_manager)
                                    <img src="{{ $permit->signature_manager }}" style="height: 45px;">
                                @endif
                            </td>
                            <td style="text-align: center; vertical-align: middle; height: 65px;">
                                @if($permit->signature_applicant)
                                    <img src="{{ $permit->signature_applicant }}" style="height: 45px;">
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