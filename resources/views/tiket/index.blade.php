@extends('layouts.app')
@section('title', 'Sistem Tiket')

@section('content')
@php
    $user = auth()->user();
@endphp

{{-- ================= PAGE HEADER ================= --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight">Sistem Tiket</h1>
        <p class="text-xs text-slate-500 mt-0.5">Pengajuan &amp; Monitoring Layanan Internal (Response Time 2 Jam &amp; Resolution SLA)</p>
    </div>
    @if ($user->isUser())
        <a href="{{ route('tiket.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#114E84] text-white hover:bg-[#0E4272] shadow-sm transition-all">
            @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
            <span>Ajukan Tiket Baru</span>
        </a>
    @endif
</div>

{{-- ================= FILTER BAR ================= --}}
<form method="GET" action="{{ route('tiket.index') }}"
      class="flex flex-wrap items-center gap-2 mb-5 p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Cari nomor atau judul tiket..."
           class="flex-1 min-w-[180px] text-sm rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-400 focus:ring-1 focus:ring-blue-300 outline-none">
    
    <select name="status" class="text-sm rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-400 outline-none bg-white">
        <option value="">— Semua Status —</option>
        @foreach ($statuses as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>

    <select name="sla_status" class="text-sm rounded-lg border border-slate-200 px-3 py-2 focus:border-blue-400 outline-none bg-white">
        <option value="">— Semua Kondisi SLA —</option>
        <option value="overdue" {{ request('sla_status') === 'overdue' ? 'selected' : '' }}>Lewat SLA (Overdue)</option>
        <option value="warning" {{ request('sla_status') === 'warning' ? 'selected' : '' }}>Mendekati Deadline</option>
        <option value="on_track" {{ request('sla_status') === 'on_track' ? 'selected' : '' }}>On Track (Aman)</option>
        <option value="completed" {{ request('sla_status') === 'completed' ? 'selected' : '' }}>Selesai Tepat Waktu</option>
    </select>

    <button type="submit" class="px-4 py-2 text-sm font-semibold bg-[#114E84] text-white rounded-lg hover:bg-[#0E4272] transition">
        Filter
    </button>
    @if (request()->hasAny(['q','status','sla_status']))
        <a href="{{ route('tiket.index') }}" class="px-3 py-2 text-sm text-slate-500 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
            Reset
        </a>
    @endif
</form>

{{-- ================= TIKET TABLE ================= --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
    @if ($tikets->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-slate-400">
            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-10 h-10 mb-3 opacity-40'])
            <p class="text-sm font-medium">Belum ada tiket</p>
            @if ($user->isUser())
                <a href="{{ route('tiket.create') }}" class="mt-3 text-xs text-blue-600 font-semibold hover:underline">+ Ajukan tiket pertama Anda</a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-xs font-bold text-slate-500 uppercase tracking-wide">
                        <th class="text-left px-4 py-3">Nomor Tiket</th>
                        <th class="text-left px-4 py-3">Jenis Pengajuan</th>
                        <th class="text-left px-4 py-3">Prioritas</th>
                        <th class="text-left px-4 py-3 hidden sm:table-cell">SLA Response (2j)</th>
                        <th class="text-left px-4 py-3 hidden md:table-cell">SLA Resolution</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Username</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Ditangani</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($tikets as $tiket)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Nomor Tiket --}}
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-bold text-[#114E84]">{{ $tiket->nomor_tiket }}</span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $tiket->created_at->format('d/m/Y H:i') }}</div>
                            </td>

                            {{-- Judul & Metadata --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $tiket->jenisBadgeClass() }}">
                                        {{ $tiket->jenis_pengajuan ?? 'Permintaan' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold border {{ $tiket->prioritasBadgeClass() }}">
                                        {{ $tiket->prioritas ?? 'Sedang' }}
                                    </span>
                                </div>
                            </td>

                            {{-- SLA Response Badge --}}
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $tiket->responseSlaBadgeClass() }}">
                                    @if ($tiket->isResponseBreached())
                                        <span>⚠️</span>
                                    @endif
                                    <span>{{ $tiket->responseSlaText() }}</span>
                                </span>
                            </td>

                            {{-- SLA Resolution Badge --}}
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $tiket->resolutionSlaBadgeClass() }}">
                                    @if ($tiket->isResolutionBreached())
                                        <span>⚠️</span>
                                    @endif
                                    <span>{{ $tiket->resolutionSlaText() }}</span>
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                </div>
                            </td>

                            {{-- Pemohon & Bagian --}}
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-xs font-medium text-slate-700">{{ $tiket->pemohon?->nama_lengkap ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-[11px] text-slate-400 mt-0.5">{{ $tiket->department?->nama ?? 'Belum dialokasikan' }}</p>
                            </td>

                            {{-- Status Tiket --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tiket->statusBadgeClass() }}">
                                    {{ $tiket->status }}
                                </span>
                                @if ($tiket->isSlaBreached())
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700">
                                            Overdue SLA
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('tiket.show', $tiket) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-[#114E84] bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($tikets->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $tikets->links() }}
            </div>
        @endif
    @endif
</div>
@endsection