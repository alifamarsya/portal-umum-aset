@extends('layouts.app')
@section('title', 'Daftar Tiket Layanan')

@section('content')
@php
    $user = auth()->user();
@endphp

{{-- ================= HEADER AREA ================= --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-[12px] font-bold uppercase tracking-wider text-amber-600 mb-1">Layanan Terpusat &amp; Helpdesk</p>
        <h1 class="text-2xl font-bold text-ink flex flex-wrap items-center gap-2.5">
            <span>Daftar Tiket Layanan</span>
            @if ($user->isKabag())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                    Kepala Bagian: {{ $user->role?->label ?? 'Internal' }}
                </span>
            @elseif ($user->isStaf())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800">
                    Staf Pelaksana: {{ $user->role?->label ?? 'Internal' }}
                </span>
            @elseif ($user->isUser())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    Tiket Saya
                </span>
            @elseif ($user->isOperator())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                    Operator Helpdesk
                </span>
            @elseif ($user->isPimpinan())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                    Monitoring Pimpinan
                </span>
            @elseif ($user->isAdmin())
                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800">
                    Administrator
                </span>
            @endif
        </h1>
    </div>

    {{-- Actions: Khusus role 'user' yang berhak membuat tiket --}}
    @if ($user->isUser())
        <a href="{{ route('tiket.create') }}"
           class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-md transition duration-200">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-white'])
            <span>Buat Tiket Baru</span>
        </a>
    @endif
</div>

{{-- ================= STATS OVERVIEW CARDS ================= --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
        <p class="text-xs font-medium text-slate-500 mb-1">Total Tiket</p>
        <p class="text-2xl font-bold text-ink">{{ $stats['total'] ?? 0 }}</p>
    </div>
    <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200 shadow-2xs">
        <p class="text-xs font-medium text-amber-700 mb-1">Menunggu Verifikasi</p>
        <p class="text-2xl font-bold text-amber-800">{{ $stats['menunggu'] ?? 0 }}</p>
    </div>
    <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-200 shadow-2xs">
        <p class="text-xs font-medium text-blue-700 mb-1">Dalam Proses / Dialokasikan</p>
        <p class="text-2xl font-bold text-blue-800">{{ $stats['dalam_proses'] ?? 0 }}</p>
    </div>
    <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-200 shadow-2xs">
        <p class="text-xs font-medium text-emerald-700 mb-1">Tiket Selesai</p>
        <p class="text-2xl font-bold text-emerald-800">{{ $stats['selesai'] ?? 0 }}</p>
    </div>
</div>

{{-- ================= FILTER & SEARCH FORM ================= --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-4 mb-6">
    <form method="GET" action="{{ route('tiket.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Tiket / Uraian</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search', request('q')) }}"
                       placeholder="No. tiket, uraian, pemohon..."
                       class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-[#114E84] focus:border-[#114E84] outline-none">
                <div class="absolute left-3 top-2.5 text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
            <select name="status" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-[#114E84] focus:border-[#114E84] outline-none bg-white">
                <option value="">— Semua Status —</option>
                @foreach ($statuses as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                        {{ $st }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Pengajuan</label>
            <select name="jenis_pengajuan" class="w-full py-2 px-3 text-sm border border-slate-300 rounded-lg focus:ring-1 focus:ring-[#114E84] focus:border-[#114E84] outline-none bg-white">
                <option value="">— Semua Jenis —</option>
                <option value="Permintaan" {{ request('jenis_pengajuan') === 'Permintaan' ? 'selected' : '' }}>Permintaan</option>
                <option value="Permasalahan" {{ request('jenis_pengajuan') === 'Permasalahan' ? 'selected' : '' }}>Permasalahan</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="w-full bg-[#114E84] hover:bg-[#0E4272] text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                Terapkan Filter
            </button>
            @if (request()->hasAny(['search', 'q', 'status', 'jenis_pengajuan', 'sla_status']))
                <a href="{{ route('tiket.index') }}" class="px-3 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition whitespace-nowrap">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

{{-- ================= MAIN TICKETS TABLE ================= --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-left text-[12px] uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">No. Tiket</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Pemohon</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Jenis Pengajuan</th>
                    <th class="px-4 py-3.5 font-semibold">Uraian / Masalah</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Prioritas</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Tujuan Bagian</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Staf Pelaksana</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Status</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">SLA Respon / Resolusi</th>
                    <th class="px-4 py-3.5 font-semibold whitespace-nowrap">Tanggal</th>
                    <th class="px-4 py-3.5 font-semibold text-right whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tikets as $tiket)
                    <tr class="hover:bg-slate-50/70 transition">
                        {{-- No. Tiket --}}
                        <td class="px-4 py-3.5 font-mono font-bold text-[#114E84] whitespace-nowrap">
                            <a href="{{ route('tiket.show', $tiket) }}" class="hover:underline">
                                {{ $tiket->nomor_tiket }}
                            </a>
                        </td>

                        {{-- Pemohon --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-ink">{{ $tiket->pemohon?->nama_lengkap ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $tiket->pemohon?->bagian ?? '-' }}</div>
                        </td>

                        {{-- Jenis Pengajuan --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $tiket->jenisBadgeClass() }}">
                                {{ $tiket->jenis_pengajuan ?? 'Permintaan' }}
                            </span>
                        </td>

                        {{-- Uraian / Masalah --}}
                        <td class="px-4 py-3.5 min-w-[200px]">
                            <p class="text-slate-700 text-xs line-clamp-2 leading-relaxed" title="{{ $tiket->deskripsi }}">
                                {{ $tiket->deskripsi }}
                            </p>
                        </td>

                        {{-- Prioritas --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $tiket->prioritasBadgeClass() }}">
                                {{ $tiket->prioritas ?? 'Sedang' }}
                            </span>
                        </td>

                        {{-- Tujuan Bagian --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                            @if ($tiket->department)
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                    {{ $tiket->department->nama }}
                                </span>
                            @else
                                <span class="text-slate-400 italic">Belum dialokasikan</span>
                            @endif
                        </td>

                        {{-- Staf Pelaksana --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                            @if ($tiket->assignedStaf)
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-md bg-[#114E84]/10 text-[#114E84] font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($tiket->assignedStaf->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="leading-tight">
                                        <p class="font-semibold text-slate-800 text-xs">{{ $tiket->assignedStaf->nama_lengkap }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $tiket->assignedStaf->username }}</p>
                                    </div>
                                </div>
                            @elseif ($tiket->status === 'Dialokasikan')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Disposisi Kabag
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11.5px] font-medium border {{ $tiket->statusBadgeClass() }}">
                                {{ $tiket->status }}
                            </span>
                            @if ($tiket->isSlaBreached())
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-700">
                                        ⚠️ Overdue SLA
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- SLA Respon / Resolusi --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if ($tiket->status === 'Menunggu Verifikasi')
                                <div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border {{ $tiket->responseSlaBadgeClass() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $tiket->isResponseBreached() ? 'bg-rose-500 animate-ping' : 'bg-emerald-500' }}"></span>
                                        Respon: {{ $tiket->responseRemainingFormatted() }}
                                    </span>
                                </div>
                            @elseif (in_array($tiket->status, ['Dialokasikan', 'Dalam Proses', 'Selesai']))
                                <div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border {{ $tiket->resolutionSlaBadgeClass() }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $tiket->isResolutionBreached() ? 'bg-rose-500 animate-ping' : 'bg-indigo-500' }}"></span>
                                        Resolusi: {{ $tiket->resolutionRemainingFormatted() }}
                                    </span>
                                </div>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                            {{ $tiket->created_at->format('d M Y H:i') }}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-2">
                                @if ($user->isKabag() && $user->effectiveDepartmentId() == $tiket->department_id && $tiket->status === 'Dialokasikan')
                                    <a href="{{ route('tiket.show', $tiket) }}"
                                       class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-xs transition">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3 h-3'])
                                        <span>Disposisi</span>
                                    </a>
                                @elseif ($user->isUser() && $tiket->pemohon_id === $user->id && $tiket->status === 'Selesai')
                                    <a href="{{ route('tiket.show', $tiket) }}"
                                       class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-xs transition animate-pulse">
                                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3 h-3 text-white'])
                                        <span>Konfirmasi</span>
                                    </a>
                                @elseif ($user->isStaf() && $tiket->status === 'Dalam Proses' && ($tiket->assigned_to === $user->id || $tiket->department_id === $user->effectiveDepartmentId()))
                                    <a href="{{ route('tiket.show', $tiket) }}"
                                       class="inline-flex items-center gap-1 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-xs transition">
                                        @include('partials.icon', ['name' => 'wrench', 'class' => 'w-3 h-3 text-white'])
                                        <span>Selesaikan</span>
                                    </a>
                                @else
                                    <a href="{{ route('tiket.show', $tiket) }}"
                                       class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition">
                                        @include('partials.icon', ['name' => 'eye', 'class' => 'w-3 h-3 text-slate-500'])
                                        <span>Detail</span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-6 h-6'])
                            </div>
                            <p class="text-slate-600 font-bold text-sm mb-1">Tidak ada data tiket.</p>
                            <p class="text-slate-400 text-xs">Tiket layanan yang dibuat akan muncul di daftar ini.</p>
                            @if ($user->isUser())
                                <a href="{{ route('tiket.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-xs text-[#114E84] font-bold hover:underline">
                                    + Ajukan tiket pertama Anda
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= PAGINATION ================= --}}
@if ($tikets->hasPages())
    <div class="mt-4">
        {{ $tikets->links() }}
    </div>
@endif

@endsection