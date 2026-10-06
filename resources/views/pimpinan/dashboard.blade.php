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
    $perluTindakLanjut = ($menungguVerifikasi ?? 0) + ($dialokasikan ?? 0);
@endphp

{{-- ========================================================================= --}}
{{-- BLOK A: HEADER SINGKAT (MONITORING EKSEKUTIF - TANPA TOMBOL CTA BESAR)   --}}
{{-- ========================================================================= --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
                <span>Executive Dashboard • Divisi Umum</span>
            </span>
            <span class="text-xs text-slate-300">•</span>
            <span class="text-xs text-slate-500 font-medium">{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">
            {{ $greeting }}, {{ $user->nama_lengkap }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
            Monitoring terpadu status tiket, SLA, dan biaya operasional.
        </p>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- BLOK B: 5 KPI INTI (SATU BARIS DI DESKTOP, SUBTEXT 2-4 KATA)               --}}
{{-- ========================================================================= --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-6">
    {{-- 1. Total Tiket --}}
    <a href="{{ route('tiket.index') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-[#114E84]/40 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Tiket</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">{{ $totalTiket }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Semua pengajuan</p>
    </a>

    {{-- 2. Menunggu & Perlu Tindak Lanjut --}}
    <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
       class="group bg-white rounded-xl p-4 border border-amber-200 shadow-2xs hover:border-amber-400 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Menunggu Tindak Lanjut</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-amber-700 tracking-tight">{{ $perluTindakLanjut }}</p>
        </div>
        <div class="flex items-center gap-1.5 text-[11px] font-semibold text-amber-600 mt-2">
            @if ($perluTindakLanjut > 0)
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
            @endif
            <span>{{ $menungguVerifikasi }} Menunggu &bull; {{ $dialokasikan }} Dialokasikan</span>
        </div>
    </a>

    {{-- 3. Dalam Proses --}}
    <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}"
       class="group bg-white rounded-xl p-4 border border-indigo-200 shadow-2xs hover:border-indigo-400 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800">Dalam Proses</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-indigo-700 tracking-tight">{{ $dalamProses }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Sedang dikerjakan</p>
    </a>

    {{-- 4. Selesai --}}
    <a href="{{ route('tiket.index', ['status' => 'Selesai']) }}"
       class="group bg-white rounded-xl p-4 border border-emerald-200 shadow-2xs hover:border-emerald-400 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $selesai }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Layanan tuntas &bull; {{ $ditutup + $ditolak }} Ditutup / Tolak</p>
    </a>

    {{-- 5. Total Biaya Operasional (DW) --}}
    <a href="{{ route('analitik') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-[#114E84]/40 transition-all flex flex-col justify-between col-span-2 sm:col-span-1">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Biaya Operasional (DW)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'bank', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight truncate">
                Rp {{ number_format($totalBiaya ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Periode {{ ucfirst($periode ?? 'bulanan') }}</p>
    </a>
</div>

{{-- ========================================================================= --}}
{{-- BLOK C: BEBAN KERJA TIKET (3 BAGIAN) + ANALITIK BIAYA DW                  --}}
{{-- ========================================================================= --}}
<div class="space-y-6">

    {{-- C1: Beban Kerja Tiket per Bagian + Overdue (Ringkas, Minim Teks) --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center">
                    @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-[#114E84]'])
                </div>
                <div>
                    <h2 class="text-sm font-bold text-ink">Beban Kerja Tiket</h2>
                    <p class="text-[11px] text-slate-400">Distribusi tiket aktif dan kepatuhan SLA per bagian</p>
                </div>
            </div>
            <a href="{{ route('tiket.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#114E84] hover:underline">
                <span>Lihat tiket</span>
                @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
            </a>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($deptTickets ?? [] as $dept)
                <div class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/40 hover:bg-slate-50/80 hover:border-[#114E84]/30 transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="text-sm font-bold text-ink truncate">{{ $dept['nama'] }}</h3>
                            @if (($dept['overdue'] ?? 0) > 0)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                                    {{ $dept['overdue'] }} Overdue
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    0 Overdue
                                </span>
                            @endif
                        </div>

                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-3xl font-extrabold text-ink font-mono">{{ $dept['aktif'] ?? 0 }}</span>
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Aktif</span>
                        </div>

                        <div class="flex items-center gap-3 text-xs text-slate-500 font-medium">
                            <span>Selesai: <strong class="text-slate-700 font-mono">{{ $dept['selesai'] ?? 0 }}</strong></span>
                            <span>&bull;</span>
                            <span>Total: <strong class="text-slate-700 font-mono">{{ $dept['total'] ?? 0 }}</strong></span>
                        </div>
                    </div>

                    <a href="{{ route('tiket.index', ['department_id' => $dept['id']]) }}"
                       class="mt-3.5 pt-2.5 border-t border-slate-200/70 flex items-center justify-between text-xs font-semibold text-[#114E84] hover:underline">
                        <span>Filter tiket {{ $dept['slug'] ?? 'bagian' }}</span>
                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- C2: Analitik Biaya & SLA (Ringkas & Clean) --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    @include('partials.icon', ['name' => 'trend-up', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h2 class="text-sm font-bold text-ink">Analitik Biaya</h2>
                    <p class="text-[11px] text-slate-400">Tren biaya operasional DW dan kepatuhan batas waktu SLA</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Filter Chip Periode --}}
                <div class="flex items-center gap-1 bg-slate-50 p-1 rounded-lg border border-slate-200 text-xs">
                    <a href="{{ route('dashboard', ['periode' => 'bulanan']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($periode ?? 'bulanan') === 'bulanan' ? 'bg-[#114E84] text-white shadow-2xs' : 'text-slate-600 hover:text-ink' }}">Bulanan</a>
                    <a href="{{ route('dashboard', ['periode' => 'kuartalan']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($periode ?? '') === 'kuartalan' ? 'bg-[#114E84] text-white shadow-2xs' : 'text-slate-600 hover:text-ink' }}">Kuartalan</a>
                    <a href="{{ route('dashboard', ['periode' => 'tahunan']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($periode ?? '') === 'tahunan' ? 'bg-[#114E84] text-white shadow-2xs' : 'text-slate-600 hover:text-ink' }}">Tahunan</a>
                </div>

                <a href="{{ route('analitik') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#114E84] hover:underline pl-2">
                    <span>Laporan Lengkap</span>
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                </a>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- KIRI: Chart Utama Biaya Bulanan per Kategori (8 cols) --}}
            <div class="lg:col-span-8 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-ink text-xs sm:text-sm">Tren Biaya Operasional per Kategori</h3>
                    <span class="text-[11px] text-slate-400">Data Agregasi DW</span>
                </div>
                <div class="relative min-h-[250px] w-full flex items-center justify-center">
                    @if (empty($biayaLabels))
                        <div class="text-center text-slate-400 py-8">
                            <p class="text-xs font-medium">Belum ada data biaya untuk ditampilkan</p>
                            <p class="text-[10.5px] mt-0.5">Jalankan proses ETL data warehouse</p>
                        </div>
                    @else
                        <canvas id="biayaChart" class="w-full max-h-[250px]"></canvas>
                    @endif
                </div>
            </div>

            {{-- KANAN: Ringkasan Metrik Finansial & SLA Kepatuhan (4 cols) --}}
            <div class="lg:col-span-4 space-y-3.5">
                {{-- Status SLA Tiket --}}
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Status SLA Tiket</span>
                        <span class="w-2 h-2 rounded-full {{ ($tiketOverdue ?? 0) > 0 ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200">
                            <p class="text-[10.5px] text-slate-400 font-medium">Overdue Respon</p>
                            <p class="text-sm font-bold font-mono mt-0.5 {{ ($tiketOverdueResponse ?? 0) > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ $tiketOverdueResponse ?? 0 }} Tiket
                            </p>
                        </div>
                        <div class="bg-white p-2.5 rounded-lg border border-slate-200">
                            <p class="text-[10.5px] text-slate-400 font-medium">Overdue Solusi</p>
                            <p class="text-sm font-bold font-mono mt-0.5 {{ ($tiketOverdue ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $tiketOverdue ?? 0 }} Tiket
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Nilai Pengadaan & Buku Aset --}}
                <div class="p-3.5 rounded-xl border border-slate-200 bg-white space-y-2.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div>
                            <span class="text-slate-500 text-[11px] block">Nilai Pengadaan (SPK)</span>
                            <span class="font-bold text-ink font-mono text-sm">Rp {{ number_format($totalPengadaan ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('analitik') }}" class="text-[#114E84] text-[11px] font-semibold hover:underline">Detail</a>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-slate-500 text-[11px] block">Nilai Buku Aset (Bulan Ini)</span>
                            <span class="font-bold text-ink font-mono text-sm">Rp {{ number_format($totalNilaiBuku ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('analitik') }}" class="text-[#114E84] text-[11px] font-semibold hover:underline">Detail</a>
                    </div>
                </div>

                {{-- Link Shortcut Export --}}
                <div class="pt-1">
                    <a href="{{ route('analitik.export-csv') }}"
                       class="w-full inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5 text-[#114E84]'])
                        <span>Unduh CSV Agregasi DW</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- COLLAPSIBLE: Catatan Operasional Internal Bagian (Closed by default) --}}
    <details class="group bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden transition">
        <summary class="p-4 flex items-center justify-between cursor-pointer hover:bg-slate-50 transition list-none select-none">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'layers', 'class' => 'w-4 h-4 text-slate-600'])
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-ink">Catatan Operasional Internal Bagian</h3>
                    <p class="text-[11px] text-slate-400">Pencatatan aktivitas kerja internal submodul operasional (klik untuk melihat rincian)</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-semibold text-[#114E84]">
                <span class="group-open:hidden">Lihat Rincian Modul &darr;</span>
                <span class="hidden group-open:inline">Tutup Rincian Modul &uarr;</span>
            </div>
        </summary>
        <div class="p-5 border-t border-slate-100 bg-slate-50/40 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            {{-- Umum & RT --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="font-bold text-ink">Umum &amp; RT</span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $operasional['umum']['kendaraan'] ?? 0 }} Kendaraan</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Bulan Ini:</span>
                    <span class="font-bold font-mono">Rp {{ number_format($operasional['umum']['biaya_bulan_ini'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Fasilitas Kantor:</span>
                    <span class="font-mono">{{ $operasional['umum']['fasilitas_kantor'] ?? 0 }} Terdata</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex gap-2 font-medium text-[#114E84] text-[11px]">
                    <a href="{{ route('modul.index', 'kendaraan') }}" class="hover:underline">Kendaraan</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'biaya_harian') }}" class="hover:underline">Biaya</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'fasilitas_kantor') }}" class="hover:underline">Fasilitas</a>
                </div>
            </div>

            {{-- Aset & Logistik --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="font-bold text-ink">Aset &amp; Logistik</span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $operasional['aset']['total_aset'] ?? 0 }} Aset</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Nilai Perolehan:</span>
                    <span class="font-bold font-mono">Rp {{ number_format($operasional['aset']['nilai_perolehan'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PKS Aktif:</span>
                    <span class="font-mono">{{ $operasional['aset']['pks_aktif'] ?? 0 }} PKS</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex gap-2 font-medium text-[#114E84] text-[11px]">
                    <a href="{{ route('modul.index', 'aset') }}" class="hover:underline">Aset</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'mutasi_aset') }}" class="hover:underline">Mutasi</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'pks') }}" class="hover:underline">PKS</a>
                </div>
            </div>

            {{-- Pengadaan & Pemeliharaan --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <span class="font-bold text-ink">Pengadaan</span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $operasional['pengadaan']['total_spk'] ?? 0 }} SPK</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Nilai SPK:</span>
                    <span class="font-bold font-mono">Rp {{ number_format($operasional['pengadaan']['nilai_spk'] ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Jadwal Pemeliharaan:</span>
                    <span class="font-mono">{{ $operasional['pengadaan']['jadwal_pemeliharaan'] ?? 0 }} Agenda</span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex gap-2 font-medium text-[#114E84] text-[11px]">
                    <a href="{{ route('modul.index', 'spk') }}" class="hover:underline">SPK</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'perencanaan_kebutuhan') }}" class="hover:underline">Rencana</a>
                    <span>&bull;</span>
                    <a href="{{ route('modul.index', 'jadwal_pemeliharaan') }}" class="hover:underline">Jadwal</a>
                </div>
            </div>
        </div>
    </details>

</div>

@if (!empty($biayaLabels))
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
                                labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans, system-ui', size: 11 } }
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
                                ticks: { font: { family: 'Plus Jakarta Sans, system-ui', size: 10 } }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Plus Jakarta Sans, system-ui', size: 10 },
                                    callback: function(value) {
                                        return 'Rp ' + value.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endif
@endsection
