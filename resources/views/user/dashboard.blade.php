@extends('layouts.app')
@section('title', 'Portal Layanan Pengguna')

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
        <p class="text-xs text-slate-500 mt-0.5">Portal Pengajuan Tiket &amp; Monitoring Layanan Internal Bank Sulteng</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tiket.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-2xs transition">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-3.5 h-3.5'])
            <span>Buat Tiket Baru</span>
        </a>
    </div>
</div>

{{-- KPI Metrics Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('tiket.index') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-[#114E84]/40 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Total Tiket<br>Diajukan</p>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-ink tracking-tight">{{ $totalTiket }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Seluruh riwayat tiket Anda</p>
    </a>

    <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-amber-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Menunggu<br>Verifikasi</p>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-amber-600 tracking-tight">{{ $menunggu }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Menunggu alokasi operator</p>
    </a>

    <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-indigo-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Dalam<br>Proses</p>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-indigo-600 tracking-tight">{{ $dalamProses }}</p>
        <p class="text-[10px] text-slate-400 mt-1">Sedang dikerjakan tim teknis</p>
    </a>

    <a href="{{ route('tiket.index', ['status' => 'Selesai']) }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-emerald-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Siap<br>Dikonfirmasi</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600 tracking-tight">{{ $selesai }}</p>
        <p class="text-[10px] text-emerald-600 font-medium mt-1">Klik untuk menutup tiket &rarr;</p>
    </a>
</div>

{{-- My Tickets Table --}}
<div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-ink">Tiket Pengajuan Saya Terkini</h2>
            <p class="text-xs text-slate-400 mt-0.5">Status dan perkembangan tiket permohonan yang Anda ajukan</p>
        </div>
        <a href="{{ route('tiket.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
            <span>Semua Tiket Saya</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50/75 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">No. Tiket</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Bagian</th>
                    <th class="px-4 py-3">Judul Pengajuan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal Pengajuan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketSaya as $t)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3 font-mono font-bold text-[#114E84]">
                            <a href="{{ route('tiket.show', $t->id) }}" class="hover:underline">
                                {{ $t->nomor_tiket }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $t->kategori?->nama ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $t->department?->nama ?? 'Menunggu Alokasi' }}
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate">
                            <span class="font-medium text-ink">{{ $t->judul }}</span>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $t->jenisBadgeClass() }}">
                                    {{ $t->jenis_pengajuan ?? 'Permintaan' }}
                                </span>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $t->prioritasBadgeClass() }}">
                                    {{ $t->prioritas ?? 'Sedang' }}
                                </span>
                            </div>
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
                                Detail &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            Anda belum memiliki tiket pengajuan.
                            <div class="mt-2">
                                <a href="{{ route('tiket.create') }}" class="inline-flex items-center gap-1 text-xs text-[#114E84] font-bold hover:underline">
                                    + Buat Tiket Sekarang
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection