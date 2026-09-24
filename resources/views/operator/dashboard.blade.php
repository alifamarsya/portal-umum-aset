@extends('layouts.app')
@section('title', 'Dashboard Operator Helpdesk')

@section('content')
@php
    $user = auth()->user();
    $firstName = explode(' ', $user->nama_lengkap)[0];
@endphp

{{-- ================= PAGE HEADER ================= --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-[12px] font-bold uppercase tracking-wider text-amber-600 mb-1">Monitoring &amp; Dispatching</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
            Dashboard Operator Helpdesk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pantau lalu lintas tiket masuk dan distribusikan permohonan ke Kepala Bagian yang berwenang (SLA Respon 2 Jam).
        </p>
    </div>

    <a href="{{ route('tiket.index') }}"
       class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition duration-200">
        @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-white'])
        <span>Lihat Semua Tiket</span>
    </a>
</div>

{{-- ================= 4 STAT CARDS ================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- 1. Tiket Baru Masuk (Antrean Utama) --}}
    <div class="relative bg-gradient-to-br from-rose-500 to-orange-500 text-white p-5 rounded-2xl shadow-md overflow-hidden flex flex-col justify-between">
        <div class="relative z-10">
            <p class="text-xs font-bold uppercase tracking-wider text-white/80 mb-1">Antrean Tiket Baru</p>
            <p class="text-3xl sm:text-4xl font-extrabold">{{ $menungguVerifikasi }}</p>
            <p class="text-[11px] text-white/80 mt-1 font-medium">Menunggu alokasi operator</p>
        </div>
        <div class="relative z-10 mt-3 pt-3 border-t border-white/20">
            <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
               class="inline-flex items-center gap-1 text-xs font-bold text-white hover:underline">
                <span>Alokasikan Sekarang</span>
                <span>&rarr;</span>
            </a>
        </div>
        <div class="absolute top-3 right-3 w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center pointer-events-none">
            @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5 text-white'])
        </div>
        @if ($menungguVerifikasi > 0)
            <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-white rounded-full animate-ping opacity-75"></span>
        @endif
    </div>

    {{-- 2. Sedang Diproses --}}
    <div class="bg-white rounded-xl p-5 border border-blue-200 shadow-2xs flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Sedang Diproses</p>
                <p class="text-3xl font-extrabold text-blue-900 mt-1">{{ $dalamProses + $dialokasikan }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-5 h-5'])
            </div>
        </div>
        <div class="text-[11px] text-slate-500 font-medium">
            <span>{{ $dialokasikan }} Dialokasi &bull; {{ $dalamProses }} Dikerjakan</span>
        </div>
    </div>

    {{-- 3. Melebihi SLA Respon --}}
    <div class="bg-white rounded-xl p-5 border border-rose-200 shadow-2xs flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Melebihi SLA Respon</p>
                <p class="text-3xl font-extrabold text-rose-800 mt-1">{{ $overdueResponse }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5'])
            </div>
        </div>
        <p class="text-[11px] text-rose-600 font-medium">&gt; 2 jam kerja belum diteruskan</p>
    </div>

    {{-- 4. Selesai Bulan Ini --}}
    <div class="bg-white rounded-xl p-5 border border-emerald-200 shadow-2xs flex flex-col justify-between">
        <div class="flex items-start justify-between mb-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Selesai Bulan Ini</p>
                <p class="text-3xl font-extrabold text-emerald-800 mt-1">{{ $selesaiBulanIni }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
            </div>
        </div>
        <p class="text-[11px] text-slate-400 font-medium">{{ now()->translatedFormat('F Y') }}</p>
    </div>
</div>

{{-- ================= ANTREAN TIKET MENUNGGU VERIFIKASI ================= --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
            </div>
            <div>
                <h2 class="text-sm font-bold text-ink flex items-center gap-2">
                    <span>Antrean Tiket Menunggu Verifikasi &amp; Alokasi</span>
                    @if ($menungguVerifikasi > 0)
                        <span class="bg-rose-100 text-rose-700 border border-rose-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full">
                            {{ $menungguVerifikasi }} Menunggu
                        </span>
                    @endif
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Segera tinjau rincian kebutuhan dan alokasikan ke bagian yang sesuai</p>
            </div>
        </div>
        <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
            <span>Filter Menunggu</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">No. Tiket</th>
                    <th class="px-4 py-3">Waktu Masuk</th>
                    <th class="px-4 py-3">Pemohon</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Jenis Pengajuan</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">SLA Respon (Maks 2j)</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketMenunggu as $t)
                    <tr class="hover:bg-amber-50/40 transition {{ $t->isResponseBreached() ? 'bg-rose-50/30' : '' }}">
                        <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                            <a href="{{ route('tiket.show', $t) }}" class="hover:underline">
                                {{ $t->nomor_tiket }}
                            </a>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">
                            <span class="block font-medium text-slate-700">{{ $t->created_at->format('d M Y, H:i') }}</span>
                            <span class="text-slate-400 text-[10px]">({{ $t->created_at->diffForHumans() }})</span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-semibold text-ink">{{ $t->pemohon?->nama_lengkap ?? '-' }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ $t->pemohon?->bagian ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="text-slate-700 font-medium">
                                {{ $t->kategori?->nama ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $t->jenisBadgeClass() }}">
                                {{ $t->jenis_pengajuan ?? 'Permintaan' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold border {{ $t->prioritasBadgeClass() }}">
                                {{ $t->prioritas ?? 'Sedang' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border {{ $t->responseSlaBadgeClass() }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $t->isResponseBreached() ? 'bg-rose-500 animate-ping' : 'bg-emerald-500' }}"></span>
                                {{ $t->responseRemainingFormatted() }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('tiket.show', $t) }}"
                               class="inline-flex items-center gap-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-2xs transition">
                                @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-3 h-3 text-white'])
                                <span>Alokasikan</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6 text-emerald-500'])
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-0.5">Antrean Kosong!</p>
                            <p class="text-xs">Tidak ada tiket yang menunggu verifikasi saat ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection