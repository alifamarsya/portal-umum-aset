@extends('layouts.app')
@section('title', 'Dashboard Kepala Bagian')

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
        <p class="text-xs text-slate-500 mt-0.5">Keputusan &amp; Disposisi Tiket — {{ $user->role->label }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tiket.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-2xs transition">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-3.5 h-3.5'])
            <span>Tiket Bagian Saya</span>
        </a>
    </div>
</div>

{{-- KPI Metrics Grid --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-2xs flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-amber-700">Menunggu Keputusan</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $dialokasikan }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Tiket dialokasikan ke bagian Anda</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            @include('partials.icon', ['name' => 'clock', 'class' => 'w-6 h-6'])
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-2xs flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-indigo-700">Sedang Dikerjakan Staf</p>
            <p class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $dalamProses }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Tiket dalam proses pengerjaan</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            @include('partials.icon', ['name' => 'wrench', 'class' => 'w-6 h-6'])
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-2xs flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-emerald-700">Selesai Dikerjakan</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $selesai }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Menunggu konfirmasi penutupan pemohon</p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
        </div>
    </div>
</div>

{{-- Pending Decision Tickets List --}}
<div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-ink flex items-center gap-2">
                <span>Tiket Menunggu Persetujuan / Disposisi</span>
                @if ($dialokasikan > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                        {{ $dialokasikan }} Perlu Respon
                    </span>
                @endif
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Silakan setujui &amp; tugaskan ke staf, atau tolak dengan mencantumkan alasan</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50/75 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">No. Tiket</th>
                    <th class="px-4 py-3">Pemohon</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Judul Pengajuan</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Tanggal Masuk</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketMenungguKeputusan as $t)
                    <tr class="hover:bg-amber-50/30 transition">
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
                                {{ $t->kategori?->nama ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate">
                            <span class="font-medium text-ink">{{ $t->judul }}</span>
                            <div class="mt-0.5">
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $t->jenisBadgeClass() }}">
                                    {{ $t->jenis_pengajuan ?? 'Permintaan' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $t->prioritasBadgeClass() }}">
                                {{ $t->prioritas ?? 'Sedang' }}
                            </span>
                            <p class="text-[10px] text-slate-400 mt-0.5">SLA {{ $t->sla_jam ?? 48 }}j</p>
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('tiket.show', $t->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1 rounded bg-[#114E84] text-white font-semibold hover:bg-[#0E4272] transition text-[11px]">
                                Beri Keputusan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            Tidak ada tiket yang menunggu keputusan di bagian Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection