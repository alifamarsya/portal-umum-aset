@extends('layouts.app')
@section('title', 'Detail Tiket — ' . $tiket->nomor_tiket)

@section('content')
@php $user = auth()->user(); @endphp

{{-- Header --}}
<div class="mb-5">
    <a href="{{ route('tiket.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-brand transition mb-3">
        @include('partials.icon', ['name' => 'chevron-left', 'class' => 'w-3.5 h-3.5'])
        Kembali ke Daftar Tiket
    </a>
    <div class="flex flex-wrap items-start gap-3 justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $tiket->jenisBadgeClass() }}">
                    {{ $tiket->jenis_pengajuan ?? 'Permintaan' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $tiket->prioritasBadgeClass() }}">
                    Prioritas: {{ $tiket->prioritas ?? 'Sedang' }}
                </span>
                {{-- Badge Response SLA --}}
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $tiket->responseSlaBadgeClass() }}">
                    <span>Response SLA (2j): {{ $tiket->responseSlaText() }}</span>
                </span>
                {{-- Badge Resolution SLA --}}
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $tiket->resolutionSlaBadgeClass() }}">
                    <span>Resolusi SLA: {{ $tiket->resolutionSlaText() }}</span>
                </span>
                @if ($tiket->isSlaBreached())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-600 text-white animate-pulse">
                        ⚠️ Lewat SLA
                    </span>
                @endif
            </div>
            <h1 class="text-xl font-extrabold text-ink leading-tight">{{ $tiket->judul }}</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-mono">{{ $tiket->nomor_tiket }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ $tiket->statusBadgeClass() }}">
            @include('partials.icon', ['name' => $tiket->statusIcon(), 'class' => 'w-3.5 h-3.5'])
            {{ $tiket->status }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- ===== LEFT COLUMN ===== --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- ===== CARD MONITORING SLA DUA TINGKAT ===== --}}
        <div class="bg-white rounded-2xl border border-blue-200/80 shadow-2xs overflow-hidden">
            <div class="bg-gradient-to-r from-[#114E84] to-[#1D63A3] px-5 py-3 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5 text-blue-200'])
                    <h2 class="text-sm font-bold tracking-wide">Monitoring Service Level Agreement (SLA)</h2>
                </div>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/20 font-semibold">Jam Kerja 08.00–17.00 WIB</span>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                {{-- SLA 1: Response Time --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#114E84]">1. Response Time</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $tiket->responseSlaBadgeClass() }}">
                            {{ $tiket->responseSlaText() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Maksimal 2 jam kerja sejak tiket dibuat hingga diteruskan Operator.</p>
                    
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Waktu Diajukan:</span>
                            <span class="font-semibold text-slate-700">{{ $tiket->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Batas Response:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $tiket->response_due_at ? $tiket->response_due_at->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Waktu Diteruskan:</span>
                            <span class="font-semibold {{ $tiket->responded_at ? 'text-slate-700' : 'text-amber-600 italic' }}">
                                {{ $tiket->responded_at ? $tiket->responded_at->format('d/m/Y H:i') : 'Belum Diteruskan' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500">Status Timer:</span>
                            <span class="font-bold {{ $tiket->isResponseBreached() ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $tiket->responseRemainingFormatted() }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- SLA 2: Resolution Time --}}
                <div class="space-y-3 pt-4 md:pt-0 md:pl-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#114E84]">2. Resolution Time</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $tiket->resolutionSlaBadgeClass() }}">
                            {{ $tiket->resolutionSlaText() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Timer berjalan otomatis setelah disetujui Kabag sesuai kategori dan prioritas.</p>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Faktor Penentu:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $tiket->kategori?->nama ?? 'Standar' }} • Prioritas {{ $tiket->prioritas ?? 'Sedang' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Target Durasi:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $tiket->resolution_hours ?? $tiket->sla_jam ?? 24 }} Jam Kerja
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Disetujui Kabag:</span>
                            <span class="font-semibold {{ $tiket->resolution_started_at ? 'text-slate-700' : 'text-slate-400 italic' }}">
                                {{ $tiket->resolution_started_at ? $tiket->resolution_started_at->format('d/m/Y H:i') : 'Menunggu Persetujuan' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500">Batas Resolusi:</span>
                            <span class="font-semibold text-slate-700">
                                {{ $tiket->resolution_due_at ? $tiket->resolution_due_at->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500">Status Pengerjaan:</span>
                            <span class="font-bold {{ $tiket->isResolutionBreached() ? 'text-rose-600' : 'text-indigo-700' }}">
                                {{ $tiket->resolutionRemainingFormatted() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informasi Tiket --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Informasi Tiket</h2>
            <dl class="space-y-3 text-sm">
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Pemohon</dt>
                    <dd class="col-span-2 font-semibold text-ink">{{ $tiket->pemohon?->nama_lengkap ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Jenis Pengajuan</dt>
                    <dd class="col-span-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $tiket->jenisBadgeClass() }}">
                            {{ $tiket->jenis_pengajuan ?? 'Permintaan' }}
                        </span>
                    </dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Tingkat Prioritas</dt>
                    <dd class="col-span-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $tiket->prioritasBadgeClass() }}">
                            {{ $tiket->prioritas ?? 'Sedang' }}
                        </span>
                    </dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Kategori</dt>
                    <dd class="col-span-2">{{ $tiket->kategori?->nama ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Bagian</dt>
                    <dd class="col-span-2">{{ $tiket->department?->nama ?? 'Belum dialokasikan' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Operator</dt>
                    <dd class="col-span-2">{{ $tiket->operator?->nama_lengkap ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Kepala Bagian</dt>
                    <dd class="col-span-2">{{ $tiket->kabag?->nama_lengkap ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Staf Pengerjaan</dt>
                    <dd class="col-span-2">{{ $tiket->assignedStaf?->nama_lengkap ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <dt class="text-slate-500 font-medium">Tanggal Dibuat</dt>
                    <dd class="col-span-2">{{ $tiket->created_at->translatedFormat('d F Y, H:i') }}</dd>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <dt class="text-slate-500 font-medium mb-1.5">Deskripsi</dt>
                    <dd class="text-ink leading-relaxed whitespace-pre-line">{{ $tiket->deskripsi }}</dd>
                </div>
                @if ($tiket->alasan_penolakan)
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200">
                        <p class="text-xs font-bold text-rose-700 mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-rose-800">{{ $tiket->alasan_penolakan }}</p>
                    </div>
                @endif
                @if ($tiket->attachment)
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100">
                        <dt class="text-slate-500 font-medium">Lampiran</dt>
                        <dd class="col-span-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('tiket.attachment.view', $tiket) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition border border-blue-200">
                                    @include('partials.icon', ['name' => 'eye', 'class' => 'w-3.5 h-3.5'])
                                    Lihat Lampiran
                                </a>
                                <a href="{{ route('tiket.attachment.download', $tiket) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition border border-slate-200">
                                    @include('partials.icon', ['name' => 'download', 'class' => 'w-3.5 h-3.5'])
                                    Unduh Lampiran
                                </a>
                                <span class="text-[11px] text-slate-400">
                                    {{ basename($tiket->attachment) }}
                                </span>
                            </div>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Riwayat Status --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
            <h2 class="text-sm font-bold text-slate-700 mb-4">Riwayat Tiket</h2>
            @if ($tiket->histories->isEmpty())
                <p class="text-xs text-slate-400">Belum ada riwayat.</p>
            @else
                <div class="relative pl-5">
                    <div class="absolute left-1.5 top-0 bottom-0 w-px bg-slate-200"></div>
                    <div class="space-y-4">
                        @foreach ($tiket->histories as $history)
                            <div class="relative">
                                <div class="absolute -left-[17px] top-1 w-3 h-3 rounded-full border-2 border-white
                                    @if ($history->status_baru === 'Ditolak') bg-rose-400
                                    @elseif ($history->status_baru === 'Selesai' || $history->status_baru === 'Ditutup') bg-emerald-400
                                    @elseif ($history->status_baru === 'Dalam Proses') bg-indigo-400
                                    @else bg-blue-400 @endif
                                "></div>
                                <div class="text-xs text-slate-400 mb-0.5">
                                    {{ $history->created_at->translatedFormat('d M Y, H:i') }} •
                                    <span class="font-semibold text-slate-600">{{ $history->user?->nama_lengkap }}</span>
                                    <span class="text-slate-400">({{ $history->user?->role?->label }})</span>
                                </div>
                                <p class="text-sm font-semibold text-ink">{{ $history->aksi }}</p>
                                @if ($history->status_lama)
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        <span>{{ $history->status_lama }}</span>
                                        <span class="mx-1">→</span>
                                        <span class="font-semibold text-slate-600">{{ $history->status_baru }}</span>
                                    </p>
                                @endif
                                @if ($history->catatan)
                                    <p class="text-xs text-slate-500 mt-1 italic">{{ $history->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== RIGHT COLUMN: Aksi ===== --}}
    <div class="space-y-4">

        {{-- Aksi: Operator — Edit Klasifikasi (Prioritas, Jenis) --}}
        @if ($user->isOperator() && !in_array($tiket->status, ['Ditutup', 'Ditolak']))
            <div class="bg-white rounded-2xl border border-amber-200 shadow-2xs p-5">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        @include('partials.icon', ['name' => 'wrench', 'class' => 'w-4 h-4 text-amber-500'])
                        Ubah Klasifikasi &amp; SLA
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">Operator</span>
                </div>
                <p class="text-xs text-slate-500 mb-3">Operator dapat menyesuaikan parameter klasifikasi dan penentuan SLA:</p>
                <form action="{{ route('tiket.klasifikasi', $tiket) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" required class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-amber-400 bg-white">
                            <option value="Permintaan" {{ ($tiket->jenis_pengajuan ?? 'Permintaan') === 'Permintaan' ? 'selected' : '' }}>
                                Permintaan
                            </option>
                            <option value="Permasalahan" {{ ($tiket->jenis_pengajuan ?? '') === 'Permasalahan' ? 'selected' : '' }}>
                                Permasalahan
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tingkat Prioritas</label>
                        <select name="prioritas" required class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-amber-400 bg-white">
                            <option value="Rendah" {{ ($tiket->prioritas ?? 'Sedang') === 'Rendah' ? 'selected' : '' }}>
                                Rendah
                            </option>
                            <option value="Sedang" {{ ($tiket->prioritas ?? 'Sedang') === 'Sedang' ? 'selected' : '' }}>
                                Sedang
                            </option>
                            <option value="Tinggi" {{ ($tiket->prioritas ?? 'Sedang') === 'Tinggi' ? 'selected' : '' }}>
                                Tinggi
                            </option>
                            <option value="Kritis" {{ ($tiket->prioritas ?? 'Sedang') === 'Kritis' ? 'selected' : '' }}>
                                Kritis (Darurat)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan Perubahan (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Alasan penyesuaian..."
                                  class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-amber-400 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition shadow-2xs">
                        Simpan Klasifikasi &amp; SLA
                    </button>
                </form>
            </div>
        @endif

        {{-- Aksi: Operator — Alokasikan (Selesai Response Time) --}}
        @if ($user->isOperator() && $tiket->status === 'Menunggu Verifikasi')
            <div class="bg-white rounded-2xl border border-blue-200 shadow-2xs p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4 text-blue-500'])
                    Teruskan &amp; Alokasikan Tiket
                </h3>
                <p class="text-xs text-slate-500 mb-3">Tindakan ini akan menghentikan timer Response Time (SLA maks 2 jam kerja).</p>
                <form action="{{ route('tiket.alokasi', $tiket) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tujukan ke Bagian <span class="text-rose-500">*</span></label>
                        <select name="department_id" required class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-blue-400 bg-white">
                            <option value="">— Pilih Bagian —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan Alokasi (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan untuk Kepala Bagian..."
                                  class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-blue-400 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition">
                        Teruskan Tiket (Catat Response)
                    </button>
                </form>
            </div>
        @endif

        {{-- Aksi: Kabag — Setujui / Tolak (Memulai Timer Resolution Time) --}}
        @if ($user->isKabag() && $tiket->status === 'Dialokasikan' && $tiket->department_id === $user->effectiveDepartmentId())
            <div class="bg-white rounded-2xl border border-emerald-200 shadow-2xs p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-500'])
                    Persetujuan &amp; Mulai Resolusi
                </h3>
                <p class="text-xs text-slate-500 mb-3">Timer Resolution Time akan otomatis berjalan setelah disetujui.</p>

                {{-- Setujui --}}
                <form action="{{ route('tiket.setujui', $tiket) }}" method="POST" class="space-y-3 mb-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tugaskan ke Staf <span class="text-rose-500">*</span></label>
                        @if ($stafList->isEmpty())
                            <p class="text-xs text-amber-600 italic p-2 bg-amber-50 rounded-lg border border-amber-200">
                                ⚠ Belum ada staf aktif di bagian ini. Hubungi Admin untuk menambahkan staf.
                            </p>
                        @else
                            <select name="staf_id" required class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-blue-400 bg-white">
                                <option value="">— Pilih Staf —</option>
                                @foreach ($stafList as $staf)
                                    <option value="{{ $staf->id }}">{{ $staf->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan / Instruksi (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Instruksi penanganan untuk staf..."
                                  class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-blue-400 resize-none"></textarea>
                    </div>
                    <button type="submit" {{ $stafList->isEmpty() ? 'disabled' : '' }}
                            class="w-full py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Setujui &amp; Mulai Resolusi
                    </button>
                </form>

                <div class="border-t border-slate-100 pt-4">
                    <form action="{{ route('tiket.tolak', $tiket) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                            <textarea name="alasan" rows="2" required placeholder="Jelaskan alasan penolakan..."
                                      class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-rose-400 resize-none"></textarea>
                        </div>
                        <button type="submit"
                                onclick="return confirm('Yakin ingin menolak tiket ini?')"
                                class="w-full py-2 rounded-xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700 transition">
                            Tolak Tiket
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Aksi: Staf — Selesaikan (Selesai Resolution Time) --}}
        @if ($user->isStaf() && $tiket->status === 'Dalam Proses' && $tiket->department_id === $user->effectiveDepartmentId())
            <div class="bg-white rounded-2xl border border-indigo-200 shadow-2xs p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-1 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-indigo-500'])
                    Tandai Selesai
                </h3>
                <p class="text-xs text-slate-500 mb-3">Tindakan ini akan menghentikan timer Resolution Time dan mencatat kepatuhan SLA.</p>
                <form action="{{ route('tiket.selesai', $tiket) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan Penyelesaian (opsional)</label>
                        <textarea name="catatan" rows="3" placeholder="Deskripsikan pekerjaan yang telah diselesaikan..."
                                  class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:border-indigo-400 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition">
                        Tandai Selesai Pengerjaan
                    </button>
                </form>
            </div>
        @endif

        {{-- Aksi: User/Pemohon — Tutup --}}
        @if ($user->isUser() && $tiket->pemohon_id === $user->id && in_array($tiket->status, ['Selesai', 'Ditolak']))
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    @include('partials.icon', ['name' => 'archive', 'class' => 'w-4 h-4 text-slate-500'])
                    Tutup Tiket
                </h3>
                @if ($tiket->status === 'Selesai')
                    <p class="text-xs text-slate-500 mb-3">Tiket telah diselesaikan. Silakan konfirmasi dan tutup tiket jika Anda sudah puas dengan hasilnya.</p>
                @else
                    <p class="text-xs text-slate-500 mb-3">Tiket Anda telah ditolak. Silakan tutup tiket ini dan ajukan tiket baru jika diperlukan.</p>
                @endif
                <form action="{{ route('tiket.tutup', $tiket) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Catatan (opsional)</label>
                        <textarea name="catatan" rows="2" placeholder="Masukan atau catatan..."
                                  class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl bg-slate-700 text-white text-sm font-bold hover:bg-slate-800 transition">
                        Tutup Tiket
                    </button>
                </form>
            </div>
        @endif

        {{-- Status sudah final --}}
        @if ($tiket->status === 'Ditutup')
            <div class="flex flex-col items-center gap-2 bg-slate-50 rounded-2xl border border-slate-200 p-6 text-center">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-8 h-8 text-slate-400'])
                <p class="text-sm font-semibold text-slate-600">Tiket Ditutup</p>
                <p class="text-xs text-slate-400">Tiket ini telah selesai dan ditutup.</p>
            </div>
        @endif
    </div>
</div>
@endsection