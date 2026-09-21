@extends('layouts.app')
@section('title', 'Dashboard Admin')

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

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight">
            {{ $greeting }}, {{ $firstName }} 👋
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Dashboard Administrator — {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tiket.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-2xs transition">
            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-3.5 h-3.5'])
            <span>Semua Tiket</span>
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition">
            @include('partials.icon', ['name' => 'users', 'class' => 'w-3.5 h-3.5 text-slate-400'])
            <span>Manajemen User</span>
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('tiket.index') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-[#114E84]/40 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Total<br>Tiket</p>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-ink tracking-tight">{{ $totalTiket }}</p>
    </a>

    <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-amber-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Menunggu<br>Verifikasi</p>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-amber-600 tracking-tight">{{ $menungguVerifikasi }}</p>
    </a>

    <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}"
       class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-indigo-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Dalam<br>Proses</p>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'loader', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-indigo-600 tracking-tight">{{ $dalamProses }}</p>
    </a>

    <div class="group bg-white rounded-xl p-4 border border-slate-200/80 shadow-2xs hover:border-emerald-300 transition-all">
        <div class="flex items-start justify-between mb-3">
            <p class="text-[11px] font-medium text-slate-500 leading-tight">Selesai<br>Hari Ini</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
            </div>
        </div>
        <p class="text-2xl font-bold text-emerald-600 tracking-tight">{{ $selesaiHariIni }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Tiket Terbaru --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-700">Tiket Terbaru</h2>
            <a href="{{ route('tiket.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">Lihat semua</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($tiketTerbaru as $tiket)
                <a href="{{ route('tiket.show', $tiket) }}"
                   class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-mono text-[#114E84] font-semibold">{{ $tiket->nomor_tiket }}</p>
                        <p class="text-sm font-medium text-ink truncate">{{ $tiket->judul }}</p>
                        <p class="text-xs text-slate-400">{{ $tiket->pemohon?->nama_lengkap }}</p>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full flex-shrink-0 {{ $tiket->statusBadgeClass() }}">{{ $tiket->status }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-sm text-slate-400 text-center">Belum ada tiket</p>
            @endforelse
        </div>
    </div>

    {{-- Statistik Status --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
        <h2 class="text-sm font-bold text-slate-700 mb-4">Distribusi Status Tiket</h2>
        <div class="space-y-3">
            @foreach ($statsByStatus as $status => $count)
                @php
                    $pct = $totalTiket > 0 ? round(($count / $totalTiket) * 100) : 0;
                    $colors = ['Menunggu Verifikasi' => 'bg-amber-400','Dialokasikan' => 'bg-blue-400','Dalam Proses' => 'bg-indigo-400','Selesai' => 'bg-emerald-400','Ditolak' => 'bg-rose-400','Ditutup' => 'bg-slate-300'];
                    $color = $colors[$status] ?? 'bg-gray-300';
                @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600">{{ $status }}</span>
                        <span class="font-bold text-ink">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="{{ $color }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">Total Pengguna Aktif</p>
            <p class="text-sm font-bold text-ink">{{ $totalUser }}</p>
        </div>
    </div>
</div>

{{-- Aktivitas Audit Log --}}
<div class="mt-5 bg-white rounded-2xl border border-slate-200 shadow-2xs">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-slate-700">Aktivitas Terbaru</h2>
        <a href="{{ route('admin.audit-log.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline">Lihat log →</a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($activities as $log)
            <div class="flex items-start gap-3 px-5 py-3">
                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5 text-slate-400">
                    @include('partials.icon', ['name' => 'lock', 'class' => 'w-3 h-3'])
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-ink">{{ $log->aksi }} — {{ $log->modul }}</p>
                    <p class="text-[11px] text-slate-400">{{ $log->username }} · {{ $log->created_at?->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="px-5 py-6 text-sm text-slate-400 text-center">Belum ada aktivitas</p>
        @endforelse
    </div>
</div>
@endsection