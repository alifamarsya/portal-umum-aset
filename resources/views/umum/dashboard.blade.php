@extends('layouts.app')

@section('title', 'Dashboard Staf Umum & Rumah Tangga')

@section('content')
<div class="mb-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">
                Dashboard Staf Umum & Rumah Tangga
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Monitoring layanan umum, rumah tangga, dan kebutuhan operasional
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('modul.create', 'biaya_harian') }}"
               class="px-3 py-2 text-xs font-semibold rounded-lg bg-brand text-white hover:bg-slate-800 transition shadow-2xs">
                + Buat Biaya Harian
            </a>
        </div>
    </div>
</div>

{{-- Statistik --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">

    <a href="{{ route('modul.index', 'biaya_harian') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-blue-300 transition">
        <p class="text-[11px] font-medium text-slate-500">
            Pengajuan Biaya Harian
        </p>
        <p class="text-2xl font-bold text-ink mt-2">
            {{ $menunggu ?? 0 }}
        </p>
        <p class="text-[10px] text-blue-600 mt-1">
            Lihat pengajuan &rarr;
        </p>
    </a>

    <a href="{{ route('modul.index', 'biaya_harian') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-amber-300 transition">
        <p class="text-[11px] font-medium text-slate-500">
            Menunggu Proses
        </p>
        <p class="text-2xl font-bold text-ink mt-2">
            {{ $menunggu ?? 0 }}
        </p>
        <p class="text-[10px] text-amber-600 mt-1">
            Perlu ditindaklanjuti &rarr;
        </p>
    </a>

    <a href="{{ route('modul.index', 'kendaraan') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-emerald-300 transition">
        <p class="text-[11px] font-medium text-slate-500">
            Data Kendaraan
        </p>
        <p class="text-2xl font-bold text-ink mt-2">
            {{ $totalAset ?? 0 }}
        </p>
        <p class="text-[10px] text-emerald-600 mt-1">
            Monitoring kendaraan &rarr;
        </p>
    </a>

    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs">
        <p class="text-[11px] font-medium text-slate-500">
            Aktivitas Terbaru
        </p>
        <p class="text-2xl font-bold text-ink mt-2">
            {{ isset($activities) ? $activities->count() : 0 }}
        </p>
        <p class="text-[10px] text-purple-600 mt-1">
            Aktivitas sistem
        </p>
    </div>

</div>

{{-- Bagian utama --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Aktivitas terbaru --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-2xs p-5">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-ink">
                Aktivitas Terbaru
            </h2>
            <a href="{{ route('risalah.index') }}"
               class="text-xs font-semibold text-brand hover:underline">
                Lihat Semua
            </a>
        </div>

        <div class="space-y-3">
            @forelse($activities ?? [] as $activity)
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50">
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center flex-shrink-0">
                        @include('partials.icon', [
                            'name' => 'clock',
                            'class' => 'w-4 h-4 text-brand'
                        ])
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink">
                            {{ $activity->aksi ?? $activity->action ?? $activity->aktivitas ?? 'Aktivitas Sistem' }}
                        </p>
                        <p class="text-[10px] text-slate-500 mt-0.5">
                            {{ $activity->keterangan ?? $activity->description ?? '-' }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">
                            {{ $activity->created_at ? $activity->created_at->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    Belum ada aktivitas terbaru.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Aksi cepat --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <h2 class="text-sm font-bold text-ink mb-3">
            Aksi Cepat Staf Umum
        </h2>

        <div class="space-y-2">
            <a href="{{ route('modul.create', 'biaya_harian') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', [
                    'name' => 'plus',
                    'class' => 'w-4 h-4 text-brand flex-shrink-0'
                ])
                Buat Biaya Harian
            </a>

            <a href="{{ route('modul.create', 'kendaraan') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', [
                    'name' => 'plus',
                    'class' => 'w-4 h-4 text-brand flex-shrink-0'
                ])
                Tambah Kendaraan
            </a>

            <a href="{{ route('modul.index', 'biaya_harian') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', [
                    'name' => 'clipboard',
                    'class' => 'w-4 h-4 text-brand flex-shrink-0'
                ])
                Daftar Biaya Harian
            </a>

            <a href="{{ route('modul.index', 'kendaraan') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', [
                    'name' => 'clipboard',
                    'class' => 'w-4 h-4 text-brand flex-shrink-0'
                ])
                Daftar Kendaraan
            </a>

            <a href="{{ route('risalah.index') }}"
               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', [
                    'name' => 'file',
                    'class' => 'w-4 h-4 text-brand flex-shrink-0'
                ])
                Risalah Rapat
            </a>
        </div>
    </div>
</div>

{{-- Informasi monitoring --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <p class="text-[11px] font-medium text-slate-500">
            Reminder Aktif
        </p>
        <p class="text-xl font-bold text-ink mt-2">
            {{ $reminderAktif ?? 0 }}
        </p>
        <p class="text-[10px] text-slate-400 mt-1">
            Perlu monitoring
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <p class="text-[11px] font-medium text-slate-500">
            PKS Mendekati Jatuh Tempo
        </p>
        <p class="text-xl font-bold text-ink mt-2">
            {{ $pksJatuhTempo ?? 0 }}
        </p>
        <p class="text-[10px] text-slate-400 mt-1">
            Dalam periode monitoring
        </p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <p class="text-[11px] font-medium text-slate-500">
            Total Nilai Pengadaan
        </p>
        <p class="text-xl font-bold text-ink mt-2">
            Rp {{ number_format(($totalPengadaan ?? 0) / 1000000, 1, ',', '.') }} Jt
        </p>
        <p class="text-[10px] text-slate-400 mt-1">
            Informasi keseluruhan sistem
        </p>
    </div>
</div>
@endsection