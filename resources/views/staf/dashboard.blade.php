@extends('layouts.app')
@section('title', 'Dashboard Staf Pelaksana ' . ($department?->nama ?? $user->role?->label ?? ''))

@section('content')
@php
    $user = auth()->user();
    $deptNama = $department?->nama ?? $user->role?->label ?? 'Bagian Terkait';
@endphp

{{-- ================= HEADER BANNER ================= --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0C3860] via-[#114E84] to-[#1A62A2] p-6 sm:p-7 text-white shadow-lg mb-6">
    <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-blue-200 mb-2">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-3.5 h-3.5 text-blue-200'])
                <span>Tim Staf Pelaksana &bull; {{ $deptNama }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Selamat Datang, {{ $user->nama_lengkap }}
            </h1>
            <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                Pusat penanganan dan tindak lanjut tiket layanan permohonan yang ditugaskan kepada tim pelaksana {{ $deptNama }}.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            <a href="{{ route('tiket.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-semibold backdrop-blur-md transition shadow-sm">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-blue-200'])
                <span>Semua Tiket Bagian</span>
            </a>
        </div>
    </div>
</div>

{{-- ================= 4 STATS CARDS ================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Tugas Aktif Tim --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50/40 rounded-xl p-5 border border-blue-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-900">Tugas Aktif Bagian</span>
                <span class="w-8 h-8 rounded-lg bg-blue-500/15 text-[#114E84] flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-[#114E84] tracking-tight">{{ $ditugaskan }}</p>
        </div>
        <div class="flex items-center gap-1.5 mt-2 text-[11px] font-semibold text-blue-700">
            @if ($ditugaskan > 0)
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-ping"></span>
            @endif
            <span>Tiket dalam proses tim</span>
        </div>
    </div>

    {{-- Card 2: Tugas Saya Personal --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tugas Ditugaskan ke Saya</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-indigo-700 tracking-tight">{{ $tugasSayaPersonal }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Beban kerja personal Anda</p>
    </div>

    {{-- Card 3: Tiket Selesai Bagian --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tugas Diselesaikan</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $selesai }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Tuntas dikerjakan tim</p>
    </div>

    {{-- Card 4: Total Beban Kerja Masuk --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Ditugaskan</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $totalTugas }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Riwayat pengerjaan {{ $deptNama }}</p>
    </div>
</div>

{{-- ================= LIST TIKET TUGAS YANG HARUS DIKERJAKAN ================= --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4'])
            </div>
            <div>
                <h2 class="text-sm font-bold text-ink flex items-center gap-2">
                    <span>Tiket Pelaksanaan Tugas Aktif</span>
                    @if ($ditugaskan > 0)
                        <span class="bg-indigo-100 text-indigo-800 border border-indigo-200 text-[10.5px] font-bold px-2 py-0.5 rounded-full">
                            {{ $ditugaskan }} Aktif
                        </span>
                    @endif
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar permohonan dalam tahap pengerjaan di {{ $deptNama }}</p>
            </div>
        </div>

        <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
            <span>Lihat Semua</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">No. Tiket</th>
                    <th class="px-4 py-3">Pemohon</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Uraian Pekerjaan</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Staf Pelaksana</th>
                    <th class="px-4 py-3">SLA Resolusi</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketSaya as $t)
                    @php
                        $isMyTicket = $t->assigned_to === $user->id;
                    @endphp
                    <tr class="hover:bg-indigo-50/30 transition {{ $isMyTicket ? 'bg-blue-50/20' : '' }}">
                        <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                            <a href="{{ route('tiket.show', $t) }}" class="hover:underline">
                                {{ $t->nomor_tiket }}
                            </a>
                            @if ($isMyTicket)
                                <span class="block text-[9.5px] font-sans font-bold text-[#114E84] mt-0.5">
                                    ★ Ditugaskan ke Anda
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-ink">{{ $t->pemohon?->nama_lengkap ?? '-' }}</div>
                            <div class="text-[10.5px] text-slate-400">{{ $t->pemohon?->bagian ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="text-slate-700 font-medium">
                                {{ $t->kategori?->nama ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 min-w-[200px]">
                            <p class="line-clamp-2 text-slate-700 leading-relaxed">{{ $t->deskripsi }}</p>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold border {{ $t->prioritasBadgeClass() }}">
                                {{ $t->prioritas ?? 'Sedang' }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if ($t->assignedStaf)
                                <span class="inline-flex items-center gap-1 font-semibold {{ $isMyTicket ? 'text-[#114E84]' : 'text-slate-700' }}">
                                    @include('partials.icon', ['name' => 'users', 'class' => 'w-3 h-3 ' . ($isMyTicket ? 'text-[#114E84]' : 'text-slate-400')])
                                    {{ $t->assignedStaf->nama_lengkap }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Tim {{ $deptNama }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border {{ $t->resolutionSlaBadgeClass() }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $t->isResolutionBreached() ? 'bg-rose-500 animate-ping' : 'bg-indigo-500' }}"></span>
                                {{ $t->resolutionRemainingFormatted() }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('tiket.show', $t) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $isMyTicket ? 'bg-[#114E84] hover:bg-[#0E4272] text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} font-bold transition text-xs shadow-2xs">
                                @if ($isMyTicket)
                                    @include('partials.icon', ['name' => 'wrench', 'class' => 'w-3 h-3 text-white'])
                                    <span>Selesaikan</span>
                                @else
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3 h-3 text-slate-500'])
                                    <span>Detail</span>
                                @endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6 text-emerald-500'])
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-0.5">Tidak Ada Tugas Aktif</p>
                            <p class="text-xs">Seluruh tiket dalam proses pengerjaan di bagian Anda telah tuntas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection