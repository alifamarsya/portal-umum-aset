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

{{-- =================== EXECUTIVE BANNER =================== --}}
<div class="relative overflow-hidden rounded-2xl mb-6 p-6 sm:p-7 text-white shadow-lg"
     style="background: linear-gradient(135deg, #071A2F 0%, #0C2F52 40%, #114E84 70%, #1564A8 100%);">
    <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full opacity-10"
         style="background: radial-gradient(circle, #D4A038 0%, transparent 70%); pointer-events:none;"></div>
    <div class="absolute left-0 top-0 w-1.5 h-full" style="background: linear-gradient(to bottom, #D4A038, transparent);"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold mb-3"
                 style="background:rgba(212,160,56,0.15); border:1px solid rgba(212,160,56,0.3); color:#D4A038;">
                @include('partials.icon', ['name'=>'shield', 'class'=>'w-3.5 h-3.5'])
                <span>Executive Dashboard • Pimpinan Divisi Umum</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                Selamat Datang, <span style="color:#D4A038;">{{ $user->nama_lengkap }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-xl leading-relaxed">
                Monitoring terpadu seluruh aktivitas tiket layanan, kepatuhan SLA, serta analitik data warehouse Bank Sulteng.
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs text-blue-200/80">
                <span class="px-2.5 py-0.5 rounded-full bg-white/10 font-medium">Periode: {{ ucfirst($periode ?? 'bulanan') }}</span>
                <span>•</span>
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
            <a href="{{ route('tiket.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white/10 hover:bg-white/20 border border-white/20 text-white backdrop-blur-md transition shadow-sm">
                @include('partials.icon', ['name'=>'inbox', 'class'=>'w-4 h-4 text-amber-300'])
                <span>Monitoring Tiket</span>
            </a>
            <a href="{{ route('analitik.export-csv') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold bg-white text-slate-800 hover:bg-slate-100 transition shadow-sm">
                @include('partials.icon', ['name'=>'download', 'class'=>'w-4 h-4 text-[#114E84]'])
                <span>Export CSV DW</span>
            </a>
        </div>
    </div>
</div>

{{-- =================== 6 KPI STATUS TIKET =================== --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Tiket</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-ink mt-1">{{ $totalTiket }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Semua pengajuan</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-amber-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Menunggu</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1">{{ $menungguVerifikasi }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Antrean operator</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-blue-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Dialokasikan</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-blue-600 mt-1">{{ $dialokasikan }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Menunggu kabag</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-indigo-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-800">Dalam Proses</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-indigo-600 mt-1">{{ $dalamProses }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Dikerjakan staf</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-emerald-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Selesai</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1">{{ $selesai }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Siap konfirmasi</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Ditutup / Tolak</p>
        <p class="text-2xl sm:text-3xl font-extrabold text-slate-700 mt-1">{{ $ditutup + $ditolak }}</p>
        <p class="text-[10px] text-slate-400 mt-1">{{ $ditutup }} tutup &bull; {{ $ditolak }} tolak</p>
    </div>
</div>

{{-- =================== SLA ALERT & MONITORING PER BAGIAN =================== --}}
<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
    {{-- SLA Compliance Card --}}
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-xl p-5 shadow-card border border-slate-700 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Status SLA Tiket</span>
                <span class="w-2.5 h-2.5 rounded-full {{ ($tiketOverdue ?? 0) > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-400' }}"></span>
            </div>
            <p class="text-xs text-slate-300 mb-3">Batas waktu respon awal (Tier 1) dan penyelesaian (Tier 2)</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs bg-white/5 px-3 py-2 rounded-lg border border-white/10">
                    <span class="text-slate-300">Overdue Penyelesaian:</span>
                    <span class="font-bold font-mono {{ ($tiketOverdue ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ $tiketOverdue ?? 0 }} Tiket
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs bg-white/5 px-3 py-2 rounded-lg border border-white/10">
                    <span class="text-slate-300">Overdue Respon Awal:</span>
                    <span class="font-bold font-mono {{ ($tiketOverdueResponse ?? 0) > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                        {{ $tiketOverdueResponse ?? 0 }} Tiket
                    </span>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-[11px]">
            <span class="text-slate-400">Pusat Persetujuan</span>
            <span class="text-amber-300 font-semibold">100% Sistem Tiket</span>
        </div>
    </div>

    {{-- Breakdown 3 Bagian Tujuan --}}
    @foreach ($deptTickets ?? [] as $dept)
        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs flex flex-col justify-between hover:border-[#114E84]/40 transition">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-ink truncate">{{ $dept['nama'] }}</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                        {{ $dept['total'] }} Tiket
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mb-3">Penyelesaian layanan divisi</p>
                <div class="grid grid-cols-3 gap-1.5 text-center">
                    <div class="bg-amber-50 rounded-lg py-1.5 px-1 border border-amber-100">
                        <p class="text-[10px] text-amber-700 font-semibold">Alokasi</p>
                        <p class="text-sm font-bold text-amber-800">{{ $dept['menunggu'] }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg py-1.5 px-1 border border-indigo-100">
                        <p class="text-[10px] text-indigo-700 font-semibold">Proses</p>
                        <p class="text-sm font-bold text-indigo-800">{{ $dept['proses'] }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg py-1.5 px-1 border border-emerald-100">
                        <p class="text-[10px] text-emerald-700 font-semibold">Selesai</p>
                        <p class="text-sm font-bold text-emerald-800">{{ $dept['selesai'] }}</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('tiket.index', ['department_id' => $dept['id']]) }}"
               class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-medium text-[#114E84] hover:underline">
                <span>Filter Tiket {{ $dept['slug'] }}</span>
                @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3.5 h-3.5'])
            </a>
        </div>
    @endforeach
</div>

{{-- ========================================================================= --}}
{{-- PUSAT MONITORING CATATAN OPERASIONAL INTERNAL SEMUA BAGIAN --}}
{{-- ========================================================================= --}}
<div class="mb-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Catatan Internal
                </span>
                <span class="text-xs text-slate-400">•</span>
                <h2 class="text-sm font-bold text-ink">Catatan Operasional Internal Bagian</h2>
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Pencatatan aktivitas kerja internal tanpa maker-checker (approval terpusat pada Sistem Tiket)</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-500 bg-slate-100 px-3 py-1 rounded-full font-medium">
                38 Modul Terdaftar
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- 1. Bagian Umum & Rumah Tangga --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Umum &amp; Rumah Tangga</h3>
                        <p class="text-[11px] text-slate-400">Kendaraan, fasilitas, K3 &amp; biaya harian</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Kendaraan &amp; Driver</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['umum']['kendaraan'] ?? 0 }} Unit ({{ $operasional['umum']['kendaraan_aktif'] ?? 0 }} Aktif)</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Biaya Harian (Bln Ini)</span>
                        <span class="font-bold text-ink font-mono">Rp {{ number_format($operasional['umum']['biaya_bulan_ini'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Fasilitas Kantor</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['umum']['fasilitas_kantor'] ?? 0 }} Terdata</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Checklist Kebersihan &amp; K3</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['umum']['kebersihan_bln'] ?? 0 }} Log ({{ $operasional['umum']['insiden_k3'] ?? 0 }} K3)</span>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Akses Cepat:</span>
                <div class="flex gap-2 font-medium text-[#114E84]">
                    <a href="{{ route('modul.index', 'kendaraan') }}" class="hover:underline">Kendaraan</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'biaya_harian') }}" class="hover:underline">Biaya Harian</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'fasilitas_kantor') }}" class="hover:underline">Fasilitas</a>
                </div>
            </div>
        </div>

        {{-- 2. Bagian Aset & Logistik --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'layers', 'class' => 'w-5 h-5'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Aset &amp; Logistik</h3>
                        <p class="text-[11px] text-slate-400">Inventaris, mutasi, disposal, PKS &amp; sewa</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Total Inventaris Aset</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['aset']['total_aset'] ?? 0 }} Item (Rp {{ number_format($operasional['aset']['nilai_perolehan'] ?? 0, 0, ',', '.') }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Mutasi &amp; Disposal Aset</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['aset']['mutasi'] ?? 0 }} Mutasi / {{ $operasional['aset']['disposal'] ?? 0 }} Disposal</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">PKS &amp; Jatuh Tempo</span>
                        <span class="font-bold text-ink font-mono">
                            {{ $operasional['aset']['pks_aktif'] ?? 0 }} PKS
                            @if(($operasional['aset']['pks_near_due'] ?? 0) > 0)
                                <span class="text-amber-600 font-semibold">({{ $operasional['aset']['pks_near_due'] }} &le;60 hr)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Penerimaan &amp; Distribusi</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['aset']['penerimaan'] ?? 0 }} Terima / {{ $operasional['aset']['distribusi'] ?? 0 }} Salur</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Temuan Aset Terbuka</span>
                        <span class="font-bold font-mono {{ ($operasional['aset']['temuan_terbuka'] ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ $operasional['aset']['temuan_terbuka'] ?? 0 }} Kasus
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Akses Cepat:</span>
                <div class="flex gap-2 font-medium text-[#114E84]">
                    <a href="{{ route('modul.index', 'aset') }}" class="hover:underline">Aset</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'mutasi_aset') }}" class="hover:underline">Mutasi</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'pks') }}" class="hover:underline">PKS</a>
                </div>
            </div>
        </div>

        {{-- 3. Bagian Pengadaan & Pemeliharaan --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden flex flex-col justify-between">
            <div class="p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', ['name' => 'cart', 'class' => 'w-5 h-5'])
                    </div>
                    <div>
                        <h3 class="font-bold text-ink text-sm">Pengadaan &amp; Pemeliharaan</h3>
                        <p class="text-[11px] text-slate-400">SPK, pemeliharaan rutin, perbaikan &amp; kondisi</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Surat Perintah Kerja (SPK)</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['pengadaan']['total_spk'] ?? 0 }} Dok (Rp {{ number_format($operasional['pengadaan']['nilai_spk'] ?? 0, 0, ',', '.') }})</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Perencanaan Kebutuhan</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['pengadaan']['perencanaan'] ?? 0 }} Rencana</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Jadwal Pemeliharaan Rutin</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['pengadaan']['jadwal_pemeliharaan'] ?? 0 }} Agenda</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Monitoring Kondisi Fisik</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['pengadaan']['monitoring_kondisi'] ?? 0 }} Laporan</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Tindak Lanjut Perbaikan</span>
                        <span class="font-bold text-ink font-mono">{{ $operasional['pengadaan']['tindak_lanjut'] ?? 0 }} Berjalan</span>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400">Akses Cepat:</span>
                <div class="flex gap-2 font-medium text-[#114E84]">
                    <a href="{{ route('modul.index', 'spk') }}" class="hover:underline">SPK</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'perencanaan_kebutuhan') }}" class="hover:underline">Rencana</a>
                    <span>•</span>
                    <a href="{{ route('modul.index', 'jadwal_pemeliharaan') }}" class="hover:underline">Jadwal</a>
                </div>
            </div>
        </div>
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
