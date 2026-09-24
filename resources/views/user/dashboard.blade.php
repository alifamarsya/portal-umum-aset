@extends('layouts.app')
@section('title', 'Portal Layanan Pengguna')

@section('content')
@php
    $user = auth()->user();
    $firstName = explode(' ', $user->nama_lengkap)[0];
@endphp

{{-- ================= HEADER AREA ================= --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-[12px] font-bold uppercase tracking-wider text-amber-600 mb-1">Helpdesk &amp; Layanan Operasional</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
            Selamat Datang, {{ $user->nama_lengkap }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            {{ $user->bagian ?? 'Pemohon Layanan' }} &bull; {{ $user->jabatan ?? 'Pengguna' }} &mdash; Pantau status permohonan layanan dan kendala operasional Anda.
        </p>
    </div>

    <a href="{{ route('tiket.create') }}"
       class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs sm:text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition duration-200">
        @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
        <span>Buat Tiket Baru</span>
    </a>
</div>

{{-- ================= 4 STATS CARDS ================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Tiket Diajukan --}}
    <a href="{{ route('tiket.index') }}"
       class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs hover:border-[#114E84]/40 transition-all flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Diajukan</p>
            <p class="text-3xl font-extrabold text-ink">{{ $totalTiket }}</p>
            <p class="text-[11px] text-slate-500 mt-1 font-medium">Seluruh permohonan</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-5 h-5'])
        </div>
    </a>

    {{-- Menunggu Verifikasi --}}
    <a href="{{ route('tiket.index', ['status' => 'Menunggu Verifikasi']) }}"
       class="bg-gradient-to-br from-amber-50 to-orange-50/40 p-5 rounded-xl border border-amber-200 shadow-2xs hover:border-amber-300 transition-all flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1">Menunggu Verifikasi</p>
            <p class="text-3xl font-extrabold text-amber-800">{{ $menunggu }}</p>
            <p class="text-[11px] text-amber-600 mt-1 font-medium">Sedang antre di Operator</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
            @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
        </div>
    </a>

    {{-- Sedang Diproses --}}
    <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}"
       class="bg-gradient-to-br from-blue-50 to-indigo-50/40 p-5 rounded-xl border border-blue-200 shadow-2xs hover:border-blue-300 transition-all flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 mb-1">Sedang Diproses</p>
            <p class="text-3xl font-extrabold text-blue-800">{{ $dalamProses + $dialokasikan }}</p>
            <p class="text-[11px] text-blue-600 mt-1 font-medium">Ditangani tim teknis</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-blue-100 text-[#114E84] flex items-center justify-center">
            @include('partials.icon', ['name' => 'wrench', 'class' => 'w-5 h-5'])
        </div>
    </a>

    {{-- Tiket Selesai --}}
    <a href="{{ route('tiket.index', ['status' => 'Selesai']) }}"
       class="bg-gradient-to-br from-emerald-50 to-teal-50/40 p-5 rounded-xl border border-emerald-200 shadow-2xs hover:border-emerald-300 transition-all flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1">Siap Dikonfirmasi</p>
            <p class="text-3xl font-extrabold text-emerald-800">{{ $selesai }}</p>
            <p class="text-[11px] text-emerald-700 font-bold mt-1">Konfirmasi &amp; tutup tiket &rarr;</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
        </div>
    </a>
</div>

{{-- ================= DAFTAR TIKET TERAKHIR DIAJUKAN ================= --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden mb-6">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-ink">5 Tiket Terakhir Diajukan</h2>
            <p class="text-xs text-slate-400 mt-0.5">Pantau status terkini dari permohonan layanan dan kendala Anda</p>
        </div>
        <a href="{{ route('tiket.index') }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
            <span>Lihat Semua Tiket</span>
            @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">Nomor Tiket</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Uraian / Permohonan</th>
                    <th class="px-4 py-3">Prioritas</th>
                    <th class="px-4 py-3">Bagian Penanganan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($tiketSaya as $t)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                            <a href="{{ route('tiket.show', $t) }}" class="hover:underline">
                                {{ $t->nomor_tiket }}
                            </a>
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">
                            {{ $t->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">
                                {{ $t->kategori?->nama ?? 'Umum' }}
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
                        <td class="px-4 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                            @if ($t->department)
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                    {{ $t->department->nama }}
                                </span>
                            @else
                                <span class="text-amber-600 font-medium">Menunggu Verifikasi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $t->statusBadgeClass() }}">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            @if ($t->status === 'Selesai')
                                <a href="{{ route('tiket.show', $t) }}"
                                   class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-2xs transition animate-pulse">
                                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-white'])
                                    <span>Konfirmasi</span>
                                </a>
                            @else
                                <a href="{{ route('tiket.show', $t) }}"
                                   class="inline-flex items-center gap-1 bg-[#114E84]/10 hover:bg-[#114E84]/20 text-[#114E84] text-xs font-semibold px-2.5 py-1 rounded-lg transition">
                                    <span>Lacak</span>
                                    <span>&rarr;</span>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-0.5">Belum Ada Tiket yang Diajukan</p>
                            <p class="text-xs">Sampaikan permohonan atau kendala fasilitas kantor melalui tombol di bawah.</p>
                            <a href="{{ route('tiket.create') }}"
                               class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold text-[#114E84] hover:underline">
                                + Buat Tiket Pertama Anda
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection