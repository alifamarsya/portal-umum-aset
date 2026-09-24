@extends('layouts.app')
@section('title', 'Dashboard Administrator')

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

{{-- ================= PAGE HEADER ================= --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1.5">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#114E84]/10 text-[#114E84] border border-[#114E84]/20">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3 text-[#114E84]'])
                <span>Administrator Workspace</span>
            </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
            {{ $greeting }}, {{ $firstName }} 👋
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pusat kendali administrasi sistem, pengawasan layanan tiket, manajemen pengguna, dan rekam jejak audit.
        </p>
    </div>

    {{-- Shortcuts / Quick Actions --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('tiket.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-2xs transition">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-3.5 h-3.5 text-white'])
            <span>Semua Tiket</span>
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition">
            @include('partials.icon', ['name' => 'users', 'class' => 'w-3.5 h-3.5 text-slate-500'])
            <span>Kelola User</span>
        </a>
        <a href="{{ route('admin.audit-log.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-2xs transition">
            @include('partials.icon', ['name' => 'activity', 'class' => 'w-3.5 h-3.5 text-slate-500'])
            <span>Audit Log</span>
        </a>
    </div>
</div>

{{-- ================= 5 KPI METRIC CARDS ================= --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    {{-- Total Tiket --}}
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
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Seluruh pengajuan tiket</p>
    </a>

    {{-- Menunggu Verifikasi --}}
    <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
       class="group bg-white rounded-xl p-4 border border-amber-200 shadow-2xs hover:border-amber-400 transition-all flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Menunggu Verifikasi</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-amber-700 tracking-tight">{{ $menungguVerifikasi }}</p>
        </div>
        <div class="flex items-center gap-1 text-[11px] font-semibold text-amber-600 mt-2">
            @if ($menungguVerifikasi > 0)
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
            @endif
            <span>Perlu verifikasi operator</span>
        </div>
    </a>

    {{-- Dalam Proses --}}
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
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Sedang dikerjakan staf</p>
    </a>

    {{-- Selesai Hari Ini --}}
    <div class="bg-white rounded-xl p-4 border border-emerald-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Selesai Hari Ini</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $selesaiHariIni }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">{{ now()->translatedFormat('d F Y') }}</p>
    </div>

    {{-- Total User Aktif --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-[#114E84]/40 transition-all flex flex-col justify-between col-span-2 sm:col-span-1">
        <div>
            <div class="flex items-start justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">User Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">{{ $totalUser }}</p>
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2">
            <span>{{ $inactiveUsers ?? 0 }} Nonaktif</span>
            <span class="text-[#114E84] font-bold">Kelola &rarr;</span>
        </div>
    </a>
</div>

{{-- ================= LOWER SECTION: 2 COLUMNS ================= --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    {{-- LEFT COLUMN: Daftar Tiket Terbaru (7 cols) --}}
    <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center">
                    @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h2 class="text-sm font-bold text-ink">Tiket Layanan Terbaru</h2>
                    <p class="text-[11px] text-slate-400">Pengajuan tiket terkini yang masuk ke dalam sistem</p>
                </div>
            </div>
            <a href="{{ route('tiket.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
                <span>Lihat Semua</span>
                @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3">No. Tiket</th>
                        <th class="px-4 py-3">Pemohon</th>
                        <th class="px-4 py-3">Bagian</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($tiketTerbaru as $tiket)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                                <a href="{{ route('tiket.show', $tiket) }}" class="hover:underline">
                                    {{ $tiket->nomor_tiket }}
                                </a>
                                <p class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $tiket->created_at->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-ink">{{ $tiket->pemohon?->nama_lengkap ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $tiket->pemohon?->bagian ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="text-slate-600 font-medium">
                                    {{ $tiket->department?->nama ?? 'Belum dialokasi' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $tiket->statusBadgeClass() }}">
                                    {{ $tiket->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('tiket.show', $tiket) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-[11px]">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3 h-3 text-slate-500'])
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Belum ada data tiket layanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RIGHT COLUMN: Aktivitas Audit Log Terbaru (5 cols) --}}
    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden flex flex-col justify-between">
        <div>
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        @include('partials.icon', ['name' => 'activity', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-ink">Aktivitas Audit Log Terkini</h2>
                        <p class="text-[11px] text-slate-400">Jejak audit peristiwa sistem dan transaksi</p>
                    </div>
                </div>
                <a href="{{ route('admin.audit-log.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
                    <span>Semua Log</span>
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                </a>
            </div>

            <div class="p-5 space-y-4">
                @forelse ($activities as $log)
                    <div class="flex items-start gap-3 pb-3 border-b border-slate-100 last:border-0 last:pb-0">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="text-xs font-bold text-ink truncate">{{ $log->action }} &bull; {{ $log->module }}</p>
                                <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-0.5 line-clamp-1">{{ $log->description ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Oleh: <strong class="text-slate-700">{{ $log->user?->nama_lengkap ?? 'Sistem' }}</strong>
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada catatan aktivitas audit.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer shortcut --}}
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500">Integritas Audit: Hash-Chain SHA-256</span>
            <a href="{{ route('admin.audit-log.index') }}" class="font-semibold text-[#114E84] hover:underline">
                Buka Audit Trail &rarr;
            </a>
        </div>
    </div>

</div>
@endsection