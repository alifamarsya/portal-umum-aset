@extends('layouts.app')
@section('title', 'Dashboard ' . ($department?->nama ?? $user->role?->label ?? 'Kepala Bagian'))

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
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-amber-300 mb-2">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5 text-amber-300'])
                <span>Dashboard Kepala Bagian &bull; {{ $deptNama }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Selamat Datang, {{ $user->nama_lengkap }}
            </h1>
            <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-2xl leading-relaxed">
                Pusat kendali evaluasi tiket layanan, verifikasi kebutuhan &amp; disposisi penugasan staf pelaksana tim {{ $deptNama }}.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-shrink-0">
            <a href="{{ route('tiket.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-xs sm:text-sm font-semibold backdrop-blur-md transition shadow-sm">
                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4 text-amber-300'])
                <span>Semua Tiket Bagian</span>
            </a>
        </div>
    </div>
</div>

{{-- ================= 4 STATS CARDS ================= --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Card 1: Butuh Disposisi (Dialokasikan) --}}
    <div class="bg-gradient-to-br from-amber-50 to-orange-50/40 rounded-xl p-5 border border-amber-200/90 shadow-2xs relative overflow-hidden flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-900">Butuh Disposisi</span>
                <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-800 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-amber-700'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-amber-900 tracking-tight">{{ $dialokasikan }}</p>
        </div>
        <div class="flex items-center gap-1.5 mt-2 text-[11px] font-semibold text-amber-700">
            @if ($dialokasikan > 0)
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
            @endif
            <span>Menunggu persetujuan Anda</span>
        </div>
    </div>

    {{-- Card 2: Dikerjakan Staf (Dalam Proses) --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dikerjakan Staf</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $dalamProses }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Sedang ditangani staf pelaksana</p>
    </div>

    {{-- Card 3: Tiket Selesai --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tiket Selesai</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">{{ $selesai }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Pekerjaan telah rampung</p>
    </div>

    {{-- Card 4: Total Tiket Masuk Bagian --}}
    <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-2xs flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Tiket Masuk</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4'])
                </span>
            </div>
            <p class="text-3xl font-extrabold text-ink tracking-tight">{{ $totalTiketBagian }}</p>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 font-medium">Total dialokasikan ke {{ $deptNama }}</p>
    </div>
</div>

{{-- ================= MAIN SECTIONS ================= --}}
<div class="space-y-6">

    {{-- 1. Antrean Disposisi Tiket (Status = Dialokasikan) --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-amber-600'])
                </div>
                <div>
                    <h2 class="text-sm font-bold text-ink flex items-center gap-2">
                        <span>Antrean Disposisi &amp; Persetujuan Tiket</span>
                        @if ($dialokasikan > 0)
                            <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10.5px] font-bold px-2 py-0.5 rounded-full">
                                {{ $dialokasikan }} Menunggu
                            </span>
                        @endif
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Tiket dari Operator yang membutuhkan verifikasi dan penunjukan staf pelaksana</p>
                </div>
            </div>
        </div>

        @if ($tiketMenungguKeputusan->isEmpty())
            <div class="p-8 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                </div>
                <h3 class="font-bold text-slate-800 text-sm">Semua Tiket Telah Didisposisikan!</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    Tidak ada tiket yang tertunda di meja Anda saat ini. Tiket baru dari Operator akan langsung tampil di sini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b border-slate-200/70 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">No. Tiket</th>
                            <th class="px-4 py-3">Waktu Masuk</th>
                            <th class="px-4 py-3">Pemohon</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Uraian Masalah</th>
                            <th class="px-4 py-3">Prioritas</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($tiketMenungguKeputusan as $t)
                            <tr class="hover:bg-amber-50/40 transition">
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
                                <td class="px-4 py-3.5 min-w-[200px]">
                                    <p class="line-clamp-2 text-slate-700 leading-relaxed">{{ $t->deskripsi }}</p>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold border {{ $t->prioritasBadgeClass() }}">
                                        {{ $t->prioritas ?? 'Sedang' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('tiket.show', $t) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-xs transition">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3.5 h-3.5 text-white'])
                                        <span>Review &amp; Disposisi</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- 2. Tiket Sedang Dikerjakan Staf (Status = Dalam Proses) --}}
    @if (isset($tiketDikerjakan) && $tiketDikerjakan->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#114E84] flex items-center justify-center">
                        @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4'])
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-ink">Tiket Sedang Dikerjakan Tim Staf</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Pemantauan progres pelaksanaan tiket aktif di {{ $deptNama }}</p>
                    </div>
                </div>
                <a href="{{ route('tiket.index', ['status' => 'Dalam Proses']) }}" class="text-xs font-semibold text-[#114E84] hover:underline flex items-center gap-1">
                    <span>Semua Dalam Proses</span>
                    @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3 h-3'])
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="px-4 py-3">No. Tiket</th>
                            <th class="px-4 py-3">Pemohon</th>
                            <th class="px-4 py-3">Uraian Masalah</th>
                            <th class="px-4 py-3">Staf Pelaksana</th>
                            <th class="px-4 py-3">SLA Resolusi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($tiketDikerjakan as $t)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 font-mono font-bold text-[#114E84] whitespace-nowrap">
                                    <a href="{{ route('tiket.show', $t) }}" class="hover:underline">
                                        {{ $t->nomor_tiket }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-ink">{{ $t->pemohon?->nama_lengkap ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 min-w-[200px]">
                                    <p class="line-clamp-1 text-slate-700">{{ $t->deskripsi }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($t->assignedStaf)
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                            @include('partials.icon', ['name' => 'users', 'class' => 'w-3 h-3 text-[#114E84]'])
                                            {{ $t->assignedStaf->nama_lengkap }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded border {{ $t->resolutionSlaBadgeClass() }}">
                                        {{ $t->resolutionRemainingFormatted() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('tiket.show', $t) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-[11px]">
                                        @include('partials.icon', ['name' => 'eye', 'class' => 'w-3 h-3 text-slate-500'])
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection