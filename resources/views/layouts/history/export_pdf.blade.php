<!DOCTYPE html>
<html>
<head>
    <title>OHS Masterlist - PTW System</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 3mm; 
        }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 6px;
            color: #000; 
            margin: 0; 
            padding: 0; 
        }

        .header-container {
            text-align: center;
            margin-bottom: 5px;
            border-bottom: 1.5px solid #003380;
            padding-bottom: 2px;
        }
        .header-container h2 {
            margin: 0;
            font-size: 11px;
            color: #003380;
            text-transform: uppercase;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; 
        }
        
        th { 
            background-color: #003380; 
            color: #ffffff; 
            font-weight: bold;
            padding: 2px 1px;
            border: 0.3px solid #000;
            text-align: center;
            vertical-align: middle;
            font-size: 5px;
        }

        td { 
            border: 0.3px solid #000; 
            padding: 2px 1px; 
            text-align: center; 
            vertical-align: middle;
            font-size: 5.5px;
            word-wrap: break-word;
        }

        tbody tr:nth-child(even) { background-color: #f2f2f2; }

        /* WARNA STATUS BARU */
        .status-box {
            font-weight: bold;
            padding: 2px;
            display: block;
            border-radius: 2px;
            font-size: 5px;
        }
        .bg-active { background-color: #27ae60 !important; color: #fff !important; } /* HIJAU */
        .bg-pending { background-color: #f39c12 !important; color: #fff !important; } /* ORANYE */
        .bg-closed { background-color: #7f8c8d !important; color: #fff !important; } /* ABU-ABU */
        .bg-default { background-color: #bdc3c7; color: #000; }

        /* Pengaturan Lebar Kolom */
        .w-yr { width: 2%; }
        .w-mo { width: 2.5%; }
        .w-st { width: 4.5%; }
        .w-no { width: 7%; } /* Lebar sedikit ditambah untuk format PTW-OHSS... */
        .w-dt { width: 4.5%; }
        .w-ac { width: 9%; text-align: left !important; padding-left: 2px !important; }
        .w-in { width: 6%; }
        .w-tm { width: 3%; }
        .w-cl { width: 2.5%; }
        .w-is { width: 5%; }
        .w-ch { width: 2.3%; font-weight: bold; }
        .w-re { width: 5%; }
    </style>
</head>
<body>

    <div class="header-container">
        <h2>OHS MASTERLIST - PERMIT TO WORK SYSTEM</h2>
        <p style="font-size: 6px; margin: 1px 0;">Generated: {{ date('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="w-yr">Year</th>
                <th rowspan="2" class="w-mo">Month</th>
                <th rowspan="2" class="w-st">Status</th>
                <th rowspan="2" class="w-no">PTW Number</th>
                <th rowspan="2" class="w-dt">Date</th>
                <th rowspan="2" class="w-ac">Work Activities</th>
                <th rowspan="2" class="w-in">Company</th>
                <th rowspan="2" class="w-in">Location</th>
                <th rowspan="2" class="w-in">Applicant</th>
                <th colspan="2">Time</th>
                <th colspan="4">Duration Details</th>
                <th rowspan="2" class="w-is">Inspector</th>
                <th rowspan="2" class="w-in">Man Hours</th>
                <th colspan="8">Permit Types</th>
                <th rowspan="2" class="w-re">Remark</th>
            </tr>
            <tr>
                <th class="w-tm">Open</th>
                <th class="w-tm">Close</th>
                <th class="w-cl">TWD</th>
                <th class="w-cl">Wknd</th>
                <th class="w-cl">Hrs</th>
                <th class="w-cl">Days</th>
                <th class="w-ch">Hot</th>
                <th class="w-ch">Elec</th>
                <th class="w-ch">Exc</th>
                <th class="w-ch">Rad</th>
                <th class="w-ch">Cld</th>
                <th class="w-ch">Hgt</th>
                <th class="w-ch">Lft</th>
                <th class="w-ch">Tot</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $index => $h)
            @php
                // Logika Inspektor (Joel/Gilang bergantian)
                $group = floor($index / 2);
                $inspector = ($group % 2 == 0) ? 'Gilang' : 'Joel';

                // Hitung Waktu
                $start = \Carbon\Carbon::parse($h->valid_from);
                $end = \Carbon\Carbon::parse($h->valid_until);
                $totalDays = $start->diffInDays($end) <= 0 ? 1 : $start->diffInDays($end) + 1;
                $hoursExp = $start->diffInHours($end);

                // Hitung Weekend
                $weekends = 0;
                $tempDate = $start->copy();
                while($tempDate <= $end) {
                    if($tempDate->isWeekend()) $weekends++;
                    $tempDate->addDay();
                }

                // Logika Jenis Permit
                $types = is_array($h->permit_type) ? $h->permit_type : json_decode($h->permit_type, true) ?? [];
                $hasType = function($keyword) use ($types) {
                    foreach($types as $t) {
                        if(str_contains(strtolower($t), strtolower($keyword))) return '1';
                    }
                    return '';
                };

                // LOGIKA WARNA STATUS SESUAI REQUEST
                $statusClass = 'bg-default';
                $statusName = strtoupper($h->status);
                
                if($statusName == 'ACTIVE') {
                    $statusClass = 'bg-active';
                } elseif(in_array($statusName, ['PROGRESS', 'PENDING', 'APPROVED'])) {
                    $statusClass = 'bg-pending';
                } elseif(in_array($statusName, ['CLOSED', 'COMPLETED', 'FINISHED'])) {
                    $statusClass = 'bg-closed';
                }
            @endphp
            <tr>
                <td>{{ $h->created_at->format('Y') }}</td>
                <td>{{ $h->created_at->format('M') }}</td>
                <td><span class="status-box {{ $statusClass }}">{{ $statusName }}</span></td>
                
                {{-- LOGIKA NOMOR: Hanya tampilkan jika ptw_number ada --}}
                <td style="font-weight: bold; color: #003380;">
                    {{ $h->ptw_number ?? '-' }}
                </td>

                <td>{{ $h->created_at->format('d-m-y') }}</td>
                <td class="w-ac"><strong>{{ Str::limit($h->work_scope_general, 25) }}</strong></td>
                <td>{{ Str::limit($h->company, 15) }}</td>
                <td>{{ Str::limit($h->location, 15) }}</td>
                <td>{{ Str::limit($h->applicant_name, 15) }}</td>
                <td>{{ $start->format('H:i') }}</td>
                <td>{{ $end->format('H:i') }}</td>
                <td>{{ $totalDays }}</td>
                <td>{{ $weekends }}</td>
                <td>{{ $hoursExp }}</td>
                <td>{{ $totalDays }}</td>
                <td style="font-weight: bold;">{{ $inspector }}</td>
                <td>{{ $h->total_manhours ?? ($hoursExp * 1) }}</td>
                <td>{{ $hasType('Hot') }}</td>
                <td>{{ $hasType('Electrical') }}</td>
                <td>{{ $hasType('Excavation') }}</td>
                <td>{{ $hasType('Radioactive') }}</td>
                <td>{{ $hasType('Cold') }}</td>
                <td>{{ $hasType('Height') }}</td>
                <td>{{ $hasType('Lifting') }}</td>
                <td style="background-color: #ddd;">{{ count($types) }}</td>
                <td>{{ Str::limit($h->last_revision_note ?? '-', 15) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>