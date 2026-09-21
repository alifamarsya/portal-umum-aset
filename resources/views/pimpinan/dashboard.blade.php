@extends('layouts.app')
@section('title', 'Dashboard Pimpinan')

@section('content')
@php
    $user = auth()->user();
    $firstName = explode(' ', $user->nama_lengkap)[0];
    $hour = (int) now()->format('H');
    $greeting = match(true) {
        $hour >= 4 && $hour < 11 => 'Selamat Pagi',
        $hour >= 11 && $hour < 15 => 'Selamat Siang',
        $hour >= 15 && $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };
@endphp

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight">
            {{ $greeting }}, {{ $firstName }} 👋
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Monitoring Seluruh Layanan, Tiket Operasional &amp; Analitik Data Warehouse Bank Sulteng</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tiket.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-2xs transition">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-3.5 h-3.5'])
            <span>Monitoring Tiket</span>
        </a>
        <a href="{{ route('analitik.export-csv') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition">
            @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5 text-slate-400'])
            <span>Export CSV DW</span>
        </a>
    </div>
</div>

{{-- KPI Metrics Grid Tiket --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-slate-500">Total Tiket</p>
        <p class="text-2xl font-bold text-ink mt-1.5">{{ $totalTiket }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Seluruh pengajuan</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-amber-600">Menunggu</p>
        <p class="text-2xl font-bold text-amber-600 mt-1.5">{{ $menungguVerifikasi }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Verifikasi operator</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-blue-600">Dialokasikan</p>
        <p class="text-2xl font-bold text-blue-600 mt-1.5">{{ $dialokasikan }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Menunggu kabag</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-indigo-600">Dalam Proses</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1.5">{{ $dalamProses }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Dikerjakan staf</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-emerald-600">Selesai</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1.5">{{ $selesai }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Menunggu penutupan</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs">
        <p class="text-[11px] font-medium text-slate-600">Ditutup</p>
        <p class="text-2xl font-bold text-slate-700 mt-1.5">{{ $ditutup }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Siklus rampung</p>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- ANALITIK DATA WAREHOUSE EKSEKUTIF (Khusus Tampil di Pimpinan Divisi) --}}
{{-- ========================================================================= --}}
<div class="mb-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold uppercase tracking-wider bg-blue-50 text-[#114E84]">
                    Eksekutif
                </span>
                <span class="text-xs text-slate-400">•</span>
                <h2 class="text-sm font-bold text-ink">Analitik Data Warehouse &amp; Keuangan</h2>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Visualisasi agregasi biaya operasional, pengadaan vendor, dan amortisasi aset</p>
        </div>
        <div class="flex items-center gap-1.5">
            <a href="{{ route('dashboard', ['periode' => 'bulanan']) }}"
               class="text-xs px-2.5 py-1 rounded-lg font-medium transition {{ ($periode ?? 'bulanan') === 'bulanan' ? 'bg-[#114E84] text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">Bulanan</a>
            <a href="{{ route('dashboard', ['periode' => 'kuartalan']) }}"
               class="text-xs px-2.5 py-1 rounded-lg font-medium transition {{ ($periode ?? '') === 'kuartalan' ? 'bg-[#114E84] text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">Kuartalan</a>
            <a href="{{ route('dashboard', ['periode' => 'tahunan']) }}"
               class="text-xs px-2.5 py-1 rounded-lg font-medium transition {{ ($periode ?? '') === 'tahunan' ? 'bg-[#114E84] text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">Tahunan</a>
        </div>
    </div>

    {{-- KPI Summary Cards DW --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'bank', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] font-semibold text-slate-400">Total Biaya Operasional (ETL)</p>
                <p class="text-xl font-bold text-ink mt-0.5">Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5 truncate">Akumulasi biaya harian disetujui</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'cart', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] font-semibold text-slate-400">Total Nilai Pengadaan (ETL)</p>
                <p class="text-xl font-bold text-ink mt-0.5">Rp {{ number_format($totalPengadaan ?? 0, 0, ',', '.') }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5 truncate">Akumulasi negosiasi pengadaan</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'trend-up', 'class' => 'w-5 h-5'])
            </div>
            <div class="min-w-0">
                <p class="text-[11.5px] font-semibold text-slate-400">Total Nilai Buku Aset</p>
                <p class="text-xl font-bold text-ink mt-0.5">Rp {{ number_format($totalNilaiBuku ?? 0, 0, ',', '.') }}</p>
                <p class="text-[10.5px] text-slate-400 mt-0.5 truncate">Sisa buku amortisasi aktif</p>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- 1. Biaya per Kategori --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs lg:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-ink text-sm">Biaya Operasional per Kategori</h3>
                    <p class="text-[11px] text-slate-400">Pengelompokan biaya BBM, perawatan, dan RT</p>
                </div>
                @if (!empty($kategoriList))
                    <div class="hidden sm:flex flex-wrap gap-1">
                        @foreach ($kategoriList as $kat)
                            <a href="{{ route('analitik.detail-kategori', $kat) }}" class="text-[10.5px] px-2 py-0.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50">
                                {{ $kat }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="relative min-h-[260px] flex items-center justify-center">
                @if (empty($biayaLabels))
                    <div class="text-center text-slate-400 py-8">
                        <p class="text-xs font-medium">Belum ada data biaya untuk ditampilkan</p>
                        <p class="text-[10.5px] mt-0.5">Jalankan proses ETL data warehouse</p>
                    </div>
                @else
                    <canvas id="biayaChart" class="w-full max-h-[260px]"></canvas>
                @endif
            </div>
        </div>

        {{-- 2. Distribusi Pengadaan per Vendor --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="mb-3">
                <h3 class="font-bold text-ink text-sm">Distribusi Nilai Pengadaan Vendor</h3>
                <p class="text-[11px] text-slate-400">Porsi pembagian nilai pengadaan rekanan</p>
            </div>
            <div class="relative min-h-[260px] flex items-center justify-center">
                @if (empty($vendorLabels))
                    <div class="text-center text-slate-400 py-8">
                        <p class="text-xs font-medium">Belum ada data vendor pengadaan</p>
                    </div>
                @else
                    <canvas id="pengadaanChart" class="w-full max-h-[260px]"></canvas>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Amortisasi Aset --}}
    <div class="bg-white rounded-xl border border-slate-200/80 p-5 shadow-2xs">
        <div class="mb-3">
            <h3 class="font-bold text-ink text-sm">Tren Amortisasi &amp; Nilai Buku Aset</h3>
            <p class="text-[11px] text-slate-400">Tren kumulatif penyusutan dibandingkan dengan nilai buku aset</p>
        </div>
        <div class="relative min-h-[220px] flex items-center justify-center">
            @if (empty($amortisasiLabels))
                <div class="text-center text-slate-400 py-6">
                    <p class="text-xs font-medium">Belum ada data tren amortisasi aset</p>
                </div>
            @else
                <canvas id="amortisasiChart" class="w-full max-h-[220px]"></canvas>
            @endif
        </div>
    </div>
</div>

{{-- Recent Tickets Table --}}
<div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-ink">Monitoring Pengajuan Tiket Terkini</h2>
            <p class="text-xs text-slate-400 mt-0.5">Daftar tiket terbaru yang diajukan oleh pengguna internal</p>
        </div>
        <a href="{{ route('tiket.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
            <span>Lihat Semua Tiket</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50/75 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">No. Tiket</th>
                    <th class="px-4 py-3">Pemohon</th>
                    <th class="px-4 py-3">Bagian Tujuan</th>
                    <th class="px-4 py-3">Subjek</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketTerbaru as $t)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3 font-mono font-bold text-[#114E84]">
                            <a href="{{ route('tiket.show', $t->id) }}" class="hover:underline">
                                {{ $t->nomor_tiket }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-ink">{{ $t->pemohon?->nama_lengkap ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $t->department?->nama ?? 'Belum Ditentukan' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate font-medium text-ink">
                            {{ $t->judul }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($t->prioritas === 'Darurat')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Darurat</span>
                            @elseif ($t->prioritas === 'Tinggi')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Tinggi</span>
                            @elseif ($t->prioritas === 'Sedang')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Sedang</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Rendah</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $t->statusBadgeClass() }}">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('tiket.show', $t->id) }}" class="inline-flex items-center gap-1 text-[#114E84] font-semibold hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            Belum ada tiket pengajuan di sistem.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (!empty($biayaLabels) || !empty($vendorLabels) || !empty($amortisasiLabels))
    <!-- ChartJS Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formatRupiah = (value) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(value);
            };

            // 1. Chart Biaya Bulanan
            @if(!empty($biayaLabels))
            const ctxBiaya = document.getElementById('biayaChart')?.getContext('2d');
            if (ctxBiaya) {
                new Chart(ctxBiaya, {
                    type: 'bar',
                    data: {
                        labels: @json($biayaLabels),
                        datasets: @json($biayaDatasets)
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Inter, system-ui', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Inter, system-ui', size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Inter, system-ui', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @endif

            // 2. Chart Pengadaan (Doughnut)
            @if(!empty($vendorLabels))
            const ctxPengadaan = document.getElementById('pengadaanChart')?.getContext('2d');
            if (ctxPengadaan) {
                new Chart(ctxPengadaan, {
                    type: 'doughnut',
                    data: {
                        labels: @json($vendorLabels),
                        datasets: [{
                            data: @json($vendorTotals),
                            backgroundColor: [
                                '#114E84', '#0E4272', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#6b7280'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { family: 'Inter, system-ui', size: 10 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
            @endif

            // 3. Chart Amortisasi (Line Chart)
            @if(!empty($amortisasiLabels))
            const ctxAmortisasi = document.getElementById('amortisasiChart')?.getContext('2d');
            if (ctxAmortisasi) {
                new Chart(ctxAmortisasi, {
                    type: 'line',
                    data: {
                        labels: @json($amortisasiLabels),
                        datasets: [
                            {
                                label: 'Nilai Buku Aset',
                                data: @json($nilaiBukuData),
                                borderColor: '#114E84',
                                backgroundColor: 'rgba(17, 78, 132, 0.08)',
                                fill: true,
                                tension: 0.3,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#114E84'
                            },
                            {
                                label: 'Nilai Penyusutan Bulanan',
                                data: @json($penyusutanData),
                                borderColor: '#f59e0b',
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                borderWidth: 2,
                                borderDash: [5, 5],
                                pointBackgroundColor: '#f59e0b'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { family: 'Inter, system-ui', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + formatRupiah(context.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Inter, system-ui', size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Inter, system-ui', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @endif
        });
    </script>
@endif
@endsection
