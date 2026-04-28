@extends('layouts.app')

@php
    $hideNotification = true;

    // ==========================================
    // LOGIKA PENARIKAN DATA RIIL DARI DATABASE
    // ==========================================
    $semuaPermit = \App\Models\Permit::orderBy('created_at', 'asc')->get();

    // 1. UPDATE: Hitung Pengguna (HANYA HSE/Safety dan Kontraktor)
    // Sesuai dengan value role di database kamu
    $totalUsers = \App\Models\User::whereIn('role', ['HSE/Safety', 'Kontraktor'])->count(); 
    
    // Status Permit tetap hitung per dokumen
    $activeCount = $semuaPermit->filter(function($p) { return $p->status === \App\Models\Permit::STATUS_ACTIVE; })->count();
    $pendingCount = $semuaPermit->filter(function($p) { return $p->status === \App\Models\Permit::STATUS_PENDING; })->count();
    $closedCount = $semuaPermit->filter(function($p) { return $p->status === \App\Models\Permit::STATUS_CLOSED; })->count();

    // 2. Rekap Data untuk Grafik Jenis PTW
    $rekapJenis = [
        'Cold Work' => 0, 'Hot Work' => 0, 'Electrical' => 0,
        'Working at Height' => 0, 'Lifting Operation' => 0,
        'Excavation' => 0, 'Confined Space' => 0
    ];

    // 3. Rekap Data Man Power
    $rekapManPower = [
        '1-5 Orang' => 0, '6-10 Orang' => 0, '11-20 Orang' => 0, '> 20 Orang' => 0
    ];

    // Persiapan Timeline untuk Grafik Tren
    $trenHarian = [];
    for ($i = 6; $i >= 0; $i--) { $trenHarian[\Carbon\Carbon::now()->subDays($i)->format('d M Y')] = 0; }
    $trenBulanan = [];
    $tahunIni = \Carbon\Carbon::now()->format('Y');
    for ($i = 1; $i <= 12; $i++) { $trenBulanan[\Carbon\Carbon::createFromDate($tahunIni, $i, 1)->format('M Y')] = 0; }
    $trenTahunan = [];
    for ($i = 2; $i >= 0; $i--) { $trenTahunan[\Carbon\Carbon::now()->subYears($i)->format('Y')] = 0; }

    // Looping Utama
    foreach($semuaPermit as $p) {
        // --- Logic Jenis PTW ---
        $types = is_array($p->permit_type) ? $p->permit_type : json_decode($p->permit_type, true) ?? [];
        foreach($types as $t) {
            if(array_key_exists($t, $rekapJenis)) { $rekapJenis[$t]++; } 
            else { $rekapJenis[$t] = 1; }
        }

        // --- Logic Man Power ---
        $jumlahOrang = (int) $p->man_power; 
        if ($jumlahOrang >= 1 && $jumlahOrang <= 5) { $rekapManPower['1-5 Orang']++; } 
        elseif ($jumlahOrang >= 6 && $jumlahOrang <= 10) { $rekapManPower['6-10 Orang']++; } 
        elseif ($jumlahOrang >= 11 && $jumlahOrang <= 20) { $rekapManPower['11-20 Orang']++; } 
        elseif ($jumlahOrang > 20) { $rekapManPower['> 20 Orang']++; }

        // --- Logic Tren Waktu ---
        if($p->created_at) {
            $hari = $p->created_at->format('d M Y'); 
            $bulan = $p->created_at->format('M Y');  
            $tahun = $p->created_at->format('Y');    
            if(isset($trenHarian[$hari])) $trenHarian[$hari]++;
            if(isset($trenBulanan[$bulan])) $trenBulanan[$bulan]++;
            if(isset($trenTahunan[$tahun])) $trenTahunan[$tahun]++;
        }
    }

    // UPDATE: Total Permit dihitung dari jumlah jenis pekerjaan yang diceklis
    $totalPermits = array_sum($rekapJenis);

@endphp

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
    .dashboard-container {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .stat-card { background: white; border-radius: 24px; padding: 25px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); height: 100%; border-bottom: 4px solid transparent; transition: all 0.2s ease; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
    .stat-icon { width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0; }
    .stat-content h4 { color: #0f172a; font-weight: 800; margin: 0; font-size: 1.8rem; }
    .stat-content small { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.7rem; }
    
    .chart-card { background: white; border-radius: 24px; box-shadow: 0 15px 50px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; padding: 25px; height: 100%; display: flex; flex-direction: column; }
    .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed #e2e8f0; }
    .chart-title { font-weight: 800; color: #003380; font-size: 1.1rem; margin: 0; display: flex; align-items: center; gap: 10px; }
    .chart-container { position: relative; flex-grow: 1; min-height: 300px; width: 100%; display: flex; justify-content: center; align-items: center;}
</style>

<div class="dashboard-container container-fluid px-4 py-3">

    {{-- KARTU STATISTIK MASTER --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-bottom-color: #6366f1;">
                <div class="stat-icon" style="background: #6366f1;"><i class="fas fa-users"></i></div>
                <div class="stat-content">
                    <h4>{{ $totalUsers }}</h4>
                    <small>Total Pengguna (HSE/Kontraktor)</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-bottom-color: #003380;">
                <div class="stat-icon" style="background: #003380;"><i class="fas fa-file-contract"></i></div>
                <div class="stat-content">
                    <h4>{{ $totalPermits }}</h4>
                    <small>Total Permit (Per Jenis)</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-bottom-color: #10b981;">
                <div class="stat-icon" style="background: #10b981;"><i class="fas fa-check-double"></i></div>
                <div class="stat-content">
                    <h4>{{ $activeCount }}</h4>
                    <small>Permit Aktif</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-bottom-color: #f59e0b;">
                <div class="stat-icon" style="background: #f59e0b;"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <h4>{{ $pendingCount }}</h4>
                    <small>Pending Approval</small>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 1: GRAFIK JENIS & STATUS --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-column text-primary"></i> Distribusi Jenis Pekerjaan (PTW)</h5>
                </div>
                <div class="chart-container"><canvas id="ptwTypeChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-pie text-success"></i> Rasio Status Permit</h5>
                </div>
                <div class="chart-container"><canvas id="ptwStatusChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- BARIS 2: TREN PENGUNGGAHAN & MAN POWER --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-chart-line text-warning"></i> Tren Pengajuan Permit</h5>
                    <select id="trendFilter" class="form-select form-select-sm w-auto shadow-sm" style="border-radius: 12px; font-weight: bold; color: #475569;" onchange="updateTrendChart(this.value)">
                        <option value="bulanan" selected>Bulanan</option>
                        <option value="harian">Harian (7 Hari)</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>
                <div class="chart-container"><canvas id="ptwTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="chart-card">
                <div class="chart-header">
                    <h5 class="chart-title"><i class="fas fa-users-gear text-info"></i> Distribusi Man Power per PTW</h5>
                </div>
                <div class="chart-container"><canvas id="manPowerChart"></canvas></div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = '#64748b';

        // 1. Jenis PTW
        const ctxType = document.getElementById('ptwTypeChart').getContext('2d');
        let gradBlue = ctxType.createLinearGradient(0, 0, 0, 400);
        gradBlue.addColorStop(0, '#3b82f6'); gradBlue.addColorStop(1, '#003380');
        new Chart(ctxType, { type: 'bar', data: { labels: @json(array_keys($rekapJenis)), datasets: [{ data: @json(array_values($rekapJenis)), backgroundColor: gradBlue, borderRadius: 8, barThickness: 30 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { borderDash: [5, 5] }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } } });

        // 2. Status Permit
        new Chart(document.getElementById('ptwStatusChart'), { type: 'doughnut', data: { labels: ['Active', 'Pending', 'Closed'], datasets: [{ data: [{{ $activeCount }}, {{ $pendingCount }}, {{ $closedCount }}], backgroundColor: ['#10b981', '#f59e0b', '#64748b'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 20 } } } } });

        // 3. Tren
        const trendDataMaster = { harian: { labels: @json(array_keys($trenHarian)), data: @json(array_values($trenHarian)) }, bulanan: { labels: @json(array_keys($trenBulanan)), data: @json(array_values($trenBulanan)) }, tahunan: { labels: @json(array_keys($trenTahunan)), data: @json(array_values($trenTahunan)) } };
        const ctxTrend = document.getElementById('ptwTrendChart').getContext('2d');
        let gradLine = ctxTrend.createLinearGradient(0, 0, 0, 400);
        gradLine.addColorStop(0, 'rgba(245, 158, 11, 0.4)'); gradLine.addColorStop(1, 'rgba(245, 158, 11, 0.0)');
        let trendChart = new Chart(ctxTrend, { type: 'line', data: { labels: trendDataMaster.bulanan.labels, datasets: [{ data: trendDataMaster.bulanan.data, borderColor: '#f59e0b', backgroundColor: gradLine, borderWidth: 3, tension: 0.4, fill: true, pointBackgroundColor: '#ffffff', pointBorderColor: '#f59e0b', pointBorderWidth: 2, pointRadius: 5 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { borderDash: [5, 5] }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } } });
        
        window.updateTrendChart = function(v) { trendChart.data.labels = trendDataMaster[v].labels; trendChart.data.datasets[0].data = trendDataMaster[v].data; trendChart.update(); }

        // 4. Man Power
        const ctxManPower = document.getElementById('manPowerChart').getContext('2d');
        let gradInfo = ctxManPower.createLinearGradient(0, 0, 0, 400);
        gradInfo.addColorStop(0, '#0ea5e9'); gradInfo.addColorStop(1, '#0369a1');
        new Chart(ctxManPower, { type: 'bar', data: { labels: @json(array_keys($rekapManPower)), datasets: [{ data: @json(array_values($rekapManPower)), backgroundColor: gradInfo, borderRadius: 8, barThickness: 30 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { borderDash: [5, 5] }, border: { display: false } }, x: { grid: { display: false }, border: { display: false } } } } });
    });
</script>
@endsection