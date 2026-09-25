@extends('layouts.app')
@section('title', 'Detail Tiket — ' . $tiket->nomor_tiket)

@section('content')
@php
    $user = auth()->user();

    // ── 1. Response SLA Calculations (Target 2 Jam = 120 Menit) ──
    $responseTargetMinutes = 120;
    $responseStart = $tiket->created_at;
    $responseDue = $tiket->response_due_at ?? $responseStart->copy()->addHours(2);
    $isResponded = (bool) $tiket->responded_at;

    if ($isResponded) {
        $responseElapsedMinutes = (int) $responseStart->diffInMinutes($tiket->responded_at);
    } else {
        $responseElapsedMinutes = (int) max(0, $responseStart->diffInMinutes(now()));
    }

    $responsePercent = (int) min(100, round(($responseElapsedMinutes / $responseTargetMinutes) * 100));
    $isResponseBreached = $tiket->isResponseBreached();
    $isResponseWarning = !$isResponseBreached && !$isResponded && $responsePercent >= 75;

    // Format elapsed response
    if ($responseElapsedMinutes < 60) {
        $responseElapsedFormatted = $responseElapsedMinutes . ' mnt';
    } else {
        $responseElapsedFormatted = floor($responseElapsedMinutes / 60) . ' jam ' . ($responseElapsedMinutes % 60) . ' mnt';
    }

    // Format remaining / result
    if ($isResponded) {
        if ($isResponseBreached) {
            $responseRemainingText = 'Terlambat ' . ($responseElapsedMinutes < 60 ? $responseElapsedMinutes . ' mnt' : floor($responseElapsedMinutes / 60) . ' jam ' . ($responseElapsedMinutes % 60) . ' mnt');
        } else {
            $responseRemainingText = 'Diselesaikan dalam ' . ($responseElapsedMinutes < 60 ? $responseElapsedMinutes . ' mnt' : floor($responseElapsedMinutes / 60) . ' jam ' . ($responseElapsedMinutes % 60) . ' mnt');
        }
    } else {
        if ($isResponseBreached) {
            $responseRemainingText = 'Lewat ' . now()->diffForHumans($responseDue, true);
        } else {
            $responseRemainingText = 'Sisa ' . now()->diffForHumans($responseDue, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
        }
    }

    // ── 2. Resolution SLA Calculations ──
    $resolutionHours = (int) ($tiket->resolution_hours ?? $tiket->sla_jam ?? 48);
    $resolutionTargetMinutes = max(60, $resolutionHours * 60);
    $isResolutionStarted = (bool) $tiket->resolution_started_at;
    $isResolved = (bool) $tiket->resolved_at;
    $isResolutionBreached = $tiket->isResolutionBreached();

    if ($isResolutionStarted) {
        if ($isResolved) {
            $resolutionElapsedMinutes = (int) $tiket->resolution_started_at->diffInMinutes($tiket->resolved_at);
        } else {
            $resolutionElapsedMinutes = (int) max(0, $tiket->resolution_started_at->diffInMinutes(now()));
        }

        $resolutionPercent = (int) min(100, round(($resolutionElapsedMinutes / $resolutionTargetMinutes) * 100));
        $isResolutionWarning = !$isResolutionBreached && !$isResolved && $resolutionPercent >= 80;

        if ($resolutionElapsedMinutes < 60) {
            $resolutionElapsedFormatted = $resolutionElapsedMinutes . ' mnt';
        } else {
            $resolutionElapsedFormatted = floor($resolutionElapsedMinutes / 60) . ' jam ' . ($resolutionElapsedMinutes % 60) . ' mnt';
        }

        if ($isResolved) {
            if ($isResolutionBreached) {
                $resolutionRemainingText = 'Selesai Terlambat (' . floor($resolutionElapsedMinutes / 60) . 'j ' . ($resolutionElapsedMinutes % 60) . 'm)';
            } else {
                $resolutionRemainingText = 'Tuntas dalam ' . floor($resolutionElapsedMinutes / 60) . ' jam ' . ($resolutionElapsedMinutes % 60) . ' mnt';
            }
        } else {
            if ($isResolutionBreached) {
                $resolutionRemainingText = 'Lewat ' . now()->diffForHumans($tiket->resolution_due_at, true);
            } else {
                $resolutionRemainingText = 'Sisa ' . now()->diffForHumans($tiket->resolution_due_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
            }
        }
    } else {
        $resolutionElapsedMinutes = 0;
        $resolutionPercent = 0;
        $resolutionElapsedFormatted = '0 mnt';
        $resolutionRemainingText = 'Menunggu persetujuan';
        $isResolutionWarning = false;
    }
@endphp

{{-- ================= HEADER AREA ================= --}}
<div class="mb-6">
    <a href="{{ route('tiket.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#114E84] transition mb-2">
        &larr; Kembali ke Daftar Tiket
    </a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-[12px] font-bold uppercase tracking-wider text-amber-600 mb-0.5">Pelacakan &amp; Detail Tiket</p>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl font-bold font-mono text-[#114E84]">{{ $tiket->nomor_tiket }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $tiket->statusBadgeClass() }}">
                    @include('partials.icon', ['name' => $tiket->statusIcon(), 'class' => 'w-3.5 h-3.5 mr-1'])
                    {{ $tiket->status }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold border {{ $tiket->prioritasBadgeClass() }}">
                    Prioritas: {{ $tiket->prioritas ?? 'Sedang' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $tiket->jenisBadgeClass() }}">
                    {{ $tiket->jenis_pengajuan ?? 'Permintaan' }}
                </span>
                @if ($tiket->isSlaBreached())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-rose-600 text-white animate-pulse">
                        ⚠️ Lewat Batas SLA
                    </span>
                @endif
            </div>
        </div>

        {{-- Action Button in Header if Operator with Pending Ticket --}}
        @if ($user->isOperator() && $tiket->status === 'Menunggu Verifikasi')
            <div class="flex items-center gap-2">
                <a href="#panel-alokasi"
                   class="inline-flex items-center gap-2 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md transition">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                    <span>Verifikasi &amp; Alokasikan Tiket &rarr;</span>
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ================= FULL-WIDTH STACKED CARDS ================= --}}
<div class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- 1. CARD SLA RESPONSE TIME (Maksimal 2 Jam Kerja Operator)                 --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 overflow-hidden relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $isResponseBreached ? 'bg-rose-100 text-rose-700' : ($isResponseWarning ? 'bg-amber-100 text-amber-700' : ($isResponded ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-[#114E84]')) }} flex items-center justify-center flex-shrink-0">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-ink">SLA Response Time (Verifikasi Operator)</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border {{ $tiket->responseSlaBadgeClass() }}">
                            {{ $tiket->responseSlaText() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Target respon verifikasi: <strong>maksimal 2 jam kerja</strong> (08:00 - 17:00 WITA, Senin - Jumat)
                    </p>
                </div>
            </div>

            @if ($user->isOperator() && $tiket->status === 'Menunggu Verifikasi')
                <a href="#panel-alokasi"
                   class="inline-flex items-center gap-1.5 bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-2xs transition flex-shrink-0">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-white'])
                    <span>Verifikasi Sekarang &rarr;</span>
                </a>
            @endif
        </div>

        {{-- Progress Bar --}}
        <div class="mb-4">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-slate-500 font-medium">Penggunaan Kuota Waktu SLA</span>
                <span class="font-bold {{ $isResponseBreached ? 'text-rose-600' : ($isResponseWarning ? 'text-amber-600' : 'text-slate-700') }}">
                    {{ $responseElapsedFormatted }} / 2 jam ({{ $responsePercent }}%)
                </span>
            </div>
            <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $isResponseBreached ? 'bg-rose-500' : ($isResponseWarning ? 'bg-amber-500' : 'bg-[#114E84]') }}"
                     style="width: {{ $responsePercent }}%"></div>
            </div>
        </div>

        {{-- Key SLA Metrics Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
            <div>
                <span class="text-slate-400 block font-medium">SLA Mulai Dihitung</span>
                <span class="font-semibold text-slate-700 font-mono">{{ $responseStart->format('d M, H:i') }} WITA</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Batas Waktu (Due)</span>
                <span class="font-semibold {{ $isResponseBreached ? 'text-rose-700 font-bold' : 'text-slate-700' }} font-mono">
                    {{ $responseDue ? $responseDue->format('d M, H:i') . ' WITA' : '—' }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Waktu Terpakai</span>
                <span class="font-bold text-slate-800">{{ $responseElapsedFormatted }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">{{ $isResponded ? 'Hasil Evaluasi' : 'Sisa Waktu' }}</span>
                <span class="font-bold {{ $isResponseBreached ? 'text-rose-600' : ($isResponseWarning ? 'text-amber-600' : 'text-emerald-700') }}">
                    {{ $responseRemainingText }}
                </span>
            </div>
        </div>

        @if ($isResponded && $tiket->operator)
            <div class="mt-3 text-[11px] text-slate-500 flex items-center gap-1.5">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                <span>Diverifikasi oleh <strong>{{ $tiket->operator->nama_lengkap }}</strong> pada {{ $tiket->responded_at->format('d M Y, H:i') }} WITA.</span>
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. CARD SLA RESOLUTION TIME (Waktu Penyelesaian Penanganan Tiket)        --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6 overflow-hidden relative">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $isResolutionBreached ? 'bg-rose-100 text-rose-700' : ($isResolutionWarning ? 'bg-amber-100 text-amber-700' : ($isResolved ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-[#114E84]')) }} flex items-center justify-center flex-shrink-0">
                    @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-ink">SLA Resolution Time (Penyelesaian Tiket)</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold border {{ $tiket->resolutionSlaBadgeClass() }}">
                            {{ $tiket->resolutionSlaText() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Target Resolusi: <strong>{{ $resolutionHours }} Jam Kerja</strong> &bull; Prioritas: <strong>{{ $tiket->prioritas ?? 'Sedang' }}</strong> (Kritis 4j, Tinggi 12j, Sedang 48j, Rendah 72j)
                    </p>
                </div>
            </div>

            @if (!$isResolutionStarted)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Menunggu Persetujuan Kabag</span>
                </span>
            @elseif ($isResolved)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-3.5 h-3.5 text-emerald-600'])
                    <span>Telah Diselesaikan</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#114E84] border border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Timer Sedang Berjalan</span>
                </span>
            @endif
        </div>

        {{-- Progress Bar --}}
        @if ($isResolutionStarted)
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="text-slate-500 font-medium">Penggunaan Kuota Waktu SLA Resolusi</span>
                    <span class="font-bold {{ $isResolutionBreached ? 'text-rose-600' : ($isResolutionWarning ? 'text-amber-600' : 'text-slate-700') }}">
                        {{ $resolutionElapsedFormatted }} / {{ $resolutionHours }} jam ({{ $resolutionPercent }}%)
                    </span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $isResolutionBreached ? 'bg-rose-500' : ($isResolutionWarning ? 'bg-amber-500' : 'bg-emerald-500') }}"
                         style="width: {{ $resolutionPercent }}%"></div>
                </div>
            </div>

            {{-- Key SLA Metrics Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50/70 p-3.5 rounded-xl border border-slate-100">
                <div>
                    <span class="text-slate-400 block font-medium">Mulai (Disetujui Kabag)</span>
                    <span class="font-semibold text-slate-700 font-mono">
                        {{ $tiket->resolution_started_at ? $tiket->resolution_started_at->format('d M, H:i') . ' WITA' : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Batas Waktu (Due)</span>
                    <span class="font-semibold {{ $isResolutionBreached ? 'text-rose-700 font-bold' : 'text-slate-700' }} font-mono">
                        {{ $tiket->resolution_due_at ? $tiket->resolution_due_at->format('d M, H:i') . ' WITA' : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Waktu Terpakai</span>
                    <span class="font-bold text-slate-800">{{ $resolutionElapsedFormatted }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ $isResolved ? 'Hasil Evaluasi' : 'Sisa Waktu' }}</span>
                    <span class="font-bold {{ $isResolutionBreached ? 'text-rose-600' : ($isResolutionWarning ? 'text-amber-600' : 'text-emerald-700') }}">
                        {{ $resolutionRemainingText }}
                    </span>
                </div>
            </div>
        @else
            <div class="p-4 bg-amber-50/70 rounded-xl border border-amber-200 text-xs text-amber-900 leading-relaxed">
                <div class="flex items-center gap-2 font-bold mb-1 text-amber-900">
                    @include('partials.icon', ['name' => 'info', 'class' => 'w-4 h-4 text-amber-600'])
                    <span>Timer SLA Resolution belum dimulai</span>
                </div>
                <p class="text-amber-800">
                    Timer akan mulai berjalan otomatis secara resmi saat Kepala Bagian <strong>{{ $tiket->department?->nama ?? 'Terkait' }}</strong> menyetujui tiket ini dan menugaskannya kepada staf pelaksana.
                </p>
            </div>
        @endif

        {{-- Parameter Durasi / Biaya jika ada --}}
        @if ($tiket->kategori_pekerjaan || $tiket->skala_eselonisasi || $tiket->estimasi_biaya)
            <div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-wrap gap-2 text-[11px] items-center">
                <span class="text-slate-400 font-semibold uppercase text-[10px]">Parameter Durasi:</span>
                @if ($tiket->kategori_pekerjaan)
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                        Kategori: {{ $tiket->kategori_pekerjaan }}
                    </span>
                @endif
                @if ($tiket->skala_eselonisasi)
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 font-medium">
                        Skala: {{ $tiket->skala_eselonisasi }}
                    </span>
                @endif
                @if ($tiket->estimasi_biaya)
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">
                        Estimasi: Rp {{ number_format($tiket->estimasi_biaya, 0, ',', '.') }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. CARD INFORMASI UTAMA PERMINTAAN                                       --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
        <h2 class="text-base font-bold text-ink mb-4 pb-2.5 border-b border-slate-100 flex items-center gap-2">
            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4 text-[#114E84]'])
            <span>Informasi Utama Permintaan</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6 text-sm">
            <div>
                <span class="text-xs text-slate-400 block font-medium">Nomor Tiket</span>
                <span class="font-bold font-mono text-[#114E84] text-base">{{ $tiket->nomor_tiket }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Tanggal Pengajuan</span>
                <span class="font-semibold text-slate-700">{{ $tiket->created_at->format('d F Y, H:i') }} WITA</span>
                <span class="text-[11px] text-slate-400 block font-normal">({{ $tiket->created_at->diffForHumans() }})</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Nama Pemohon</span>
                <span class="font-bold text-ink">{{ $tiket->pemohon?->nama_lengkap ?? '-' }}</span>
                <span class="text-xs text-slate-500 block font-normal">{{ $tiket->pemohon?->bagian ?? 'Pemohon' }} &bull; {{ $tiket->pemohon?->jabatan ?? 'Karyawan' }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Kategori Layanan</span>
                <span class="font-semibold text-ink">{{ $tiket->kategori?->nama ?? '-' }}</span>
                <span class="text-xs text-slate-500 block font-normal">{{ $tiket->kategori?->department?->nama ?? 'Umum' }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Bagian Penanganan</span>
                @if ($tiket->department)
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-[#114E84]"></span>
                        {{ $tiket->department->nama }}
                    </span>
                @else
                    <span class="text-xs text-amber-600 italic">Belum dialokasikan</span>
                @endif
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Staf Pelaksana yang Ditugaskan</span>
                @if ($tiket->assignedStaff)
                    <div class="flex items-center gap-2 mt-0.5">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            {{ substr($tiket->assignedStaff->nama_lengkap, 0, 1) }}
                        </div>
                        <div>
                            <span class="font-bold text-ink block text-xs">{{ $tiket->assignedStaff->nama_lengkap }}</span>
                            <span class="text-[10.5px] text-slate-400 block">({{ $tiket->assignedStaff->username }})</span>
                        </div>
                    </div>
                @elseif ($tiket->status === 'Dialokasikan')
                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg mt-1 font-medium border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Menunggu penetapan oleh Kepala Bagian
                    </span>
                @elseif (in_array($tiket->status, ['Menunggu Verifikasi']))
                    <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg mt-1 font-medium border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Sedang antre di Operator Helpdesk
                    </span>
                @else
                    <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                @endif
            </div>
        </div>

        {{-- Deskripsi Masalah --}}
        <div class="mb-6">
            <span class="text-xs text-slate-400 block font-medium mb-1.5">Deskripsi Lengkap / Uraian Masalah:</span>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm whitespace-pre-line leading-relaxed">
                {{ $tiket->deskripsi }}
            </div>
        </div>

        {{-- Alasan Penolakan (jika ditolak) --}}
        @if ($tiket->alasan_penolakan)
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200">
                <div class="flex items-center gap-2 font-bold text-rose-800 text-xs mb-1">
                    @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-4 h-4 text-rose-600'])
                    <span>Tiket Ditolak oleh Kepala Bagian:</span>
                </div>
                <p class="text-xs text-rose-700 leading-relaxed">{{ $tiket->alasan_penolakan }}</p>
            </div>
        @endif

        {{-- Lampiran Dokumen / Berkas --}}
        @if ($tiket->attachment)
            @php
                $ext = strtolower(pathinfo($tiket->attachment, PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                $fileName = basename($tiket->attachment);
            @endphp
            <div>
                <span class="text-xs text-slate-400 block font-medium mb-2">Berkas / Dokumen Lampiran:</span>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-[#114E84] flex-shrink-0">
                            @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800 truncate" title="{{ $fileName }}">
                                {{ $fileName }}
                            </p>
                            <p class="text-[11px] text-slate-400 uppercase font-mono">{{ $ext ?: 'File' }} &bull; Lampiran Permohonan</p>
                        </div>
                    </div>

                    @if ($isImg)
                        <div class="mb-3 rounded-lg overflow-hidden border border-slate-200 bg-white max-h-72 flex items-center justify-center">
                            <img src="{{ route('tiket.attachment.view', $tiket) }}" alt="Pratinjau Lampiran" class="w-full h-auto object-contain max-h-72">
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2.5">
                        <a href="{{ route('tiket.attachment.view', $tiket) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-blue-50 text-[#114E84] text-xs font-semibold transition border border-blue-200 shadow-2xs">
                            @include('partials.icon', ['name' => 'eye', 'class' => 'w-4 h-4'])
                            <span>Buka Berkas</span>
                        </a>
                        <a href="{{ route('tiket.attachment.download', $tiket) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold transition shadow-2xs">
                            @include('partials.icon', ['name' => 'download', 'class' => 'w-4 h-4 text-white'])
                            <span>Unduh Berkas</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ========================================================================= --}}
    {{-- 4. ACTION PANELS (OPERATOR / KABAG / STAF / PEMOHON) - FULL WIDTH         --}}
    {{-- ========================================================================= --}}

    {{-- 4.1 OPERATOR: Teruskan & Alokasikan Tiket (Status = Menunggu Verifikasi) --}}
    @if ($user->isOperator() && $tiket->status === 'Menunggu Verifikasi')
        <div id="panel-alokasi" class="bg-gradient-to-br from-blue-50/80 via-white to-blue-50/30 rounded-2xl border-2 border-blue-300 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center gap-3 pb-4 mb-4 border-b border-blue-200">
                <div class="w-10 h-10 rounded-xl bg-[#114E84] text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                    @include('partials.icon', ['name' => 'arrow-right', 'class' => 'w-5 h-5 text-white'])
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-100 text-[#114E84] border border-blue-200 uppercase tracking-wider">
                        Wewenang Operator Helpdesk
                    </span>
                    <h3 class="text-base font-bold text-ink mt-0.5">Alokasikan ke Bagian</h3>
                    <p class="text-xs text-slate-500">Tindakan ini akan menghentikan timer Response Time (maks 2 jam) dan meneruskan tiket ke Kepala Bagian.</p>
                </div>
            </div>

            <form action="{{ route('tiket.alokasi', $tiket) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tujukan ke Bagian <span class="text-rose-500">*</span>
                        </label>
                        <select name="department_id" required
                                class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] bg-white">
                            <option value="">— Pilih Bagian Tujuan —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $tiket->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pengajuan</label>
                        <select name="jenis_pengajuan" class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] bg-white">
                            <option value="Permintaan" {{ ($tiket->jenis_pengajuan ?? 'Permintaan') === 'Permintaan' ? 'selected' : '' }}>Permintaan</option>
                            <option value="Permasalahan" {{ ($tiket->jenis_pengajuan ?? '') === 'Permasalahan' ? 'selected' : '' }}>Permasalahan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Prioritas (SLA Resolusi)</label>
                        <select name="prioritas" class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] bg-white">
                            <option value="Rendah" {{ ($tiket->prioritas ?? 'Sedang') === 'Rendah' ? 'selected' : '' }}>Rendah (SLA 72 Jam)</option>
                            <option value="Sedang" {{ ($tiket->prioritas ?? 'Sedang') === 'Sedang' ? 'selected' : '' }}>Sedang (SLA 48 Jam)</option>
                            <option value="Tinggi" {{ ($tiket->prioritas ?? 'Sedang') === 'Tinggi' ? 'selected' : '' }}>Tinggi (SLA 12 Jam)</option>
                            <option value="Kritis" {{ ($tiket->prioritas ?? 'Sedang') === 'Kritis' ? 'selected' : '' }}>Kritis (SLA 4 Jam)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Alokasi untuk Kabag (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan atau instruksi pendahuluan untuk Kepala Bagian..."
                              class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] resize-none leading-relaxed"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold transition shadow">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-white'])
                        <span>Teruskan Tiket (Catat Response &rarr;)</span>
                    </button>
                </div>
            </form>
        </div>
    @endif


    {{-- 4.3 KEPALA BAGIAN: Persetujuan Disposisi & Penolakan (Status = Dialokasikan) --}}
    @if ($user->isKabag() && $tiket->status === 'Dialokasikan' && $tiket->department_id === $user->effectiveDepartmentId())
        <div class="bg-gradient-to-br from-amber-50/90 via-white to-orange-50/50 rounded-2xl border-2 border-amber-300 shadow-sm p-6 relative overflow-hidden">
            <div class="flex items-center gap-3 pb-4 mb-4 border-b border-amber-200">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5 text-white'])
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider">
                        Wewenang Kepala Bagian
                    </span>
                    <h3 class="text-base font-bold text-ink mt-0.5">Disposisi ke Staf Pelaksana</h3>
                    <p class="text-xs text-slate-500">Timer Resolution Time otomatis mulai berjalan resmi setelah Anda menyetujui dan menugaskan staf.</p>
                </div>
            </div>

            <form action="{{ route('tiket.setujui', $tiket) }}" method="POST" class="space-y-4 mb-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Pilih Staf Pelaksana yang Ditugaskan <span class="text-rose-500">*</span>
                    </label>
                    @if ($stafList->isEmpty())
                        <p class="text-xs text-amber-700 italic p-3 bg-amber-100/60 rounded-xl border border-amber-200">
                            ⚠ Belum ada staf aktif di bagian ini. Hubungi Admin untuk menambahkan staf pelaksana.
                        </p>
                    @else
                        <select name="staf_id" required
                                class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] bg-white">
                            <option value="">— Pilih Staf Pelaksana dari Tim {{ $tiket->department?->nama }} —</option>
                            @foreach ($stafList as $staf)
                                <option value="{{ $staf->id }}">
                                    {{ $staf->nama_lengkap }} ({{ $staf->username }}) — {{ $staf->jabatan ?? 'Staf Pelaksana' }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Instruksi / Catatan Pengerjaan untuk Staf (Opsional)</label>
                    <textarea name="catatan" rows="3" placeholder="Deskripsikan arahan teknis atau skala penanganan..."
                              class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-[#114E84] resize-none leading-relaxed"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" {{ $stafList->isEmpty() ? 'disabled' : '' }}
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition disabled:opacity-50 disabled:cursor-not-allowed">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                        <span>Setujui &amp; Mulai Resolusi</span>
                    </button>
                </div>
            </form>

            {{-- Form Penolakan oleh Kabag --}}
            <div class="border-t border-amber-200 pt-4">
                <details class="group">
                    <summary class="cursor-pointer text-xs font-bold text-rose-600 hover:text-rose-700 select-none flex items-center gap-1.5">
                        @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-3.5 h-3.5'])
                        <span>Tolak Permintaan Ini?</span>
                    </summary>
                    <form action="{{ route('tiket.tolak', $tiket) }}" method="POST" class="mt-3 space-y-3 p-4 rounded-xl bg-rose-50/80 border border-rose-200">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-rose-900 mb-1">Alasan Penolakan <span class="text-rose-600">*</span></label>
                            <textarea name="alasan" rows="2" required
                                      placeholder="Jelaskan alasan penolakan secara jelas (misal: di luar wewenang bagian atau data tidak lengkap)..."
                                      class="w-full border border-rose-300 rounded-xl px-3.5 py-2 text-xs text-ink bg-white focus:ring-1 focus:ring-rose-500 outline-none resize-none"></textarea>
                        </div>
                        <button type="submit"
                                onclick="return confirm('Yakin ingin menolak tiket ini?')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                            @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-3.5 h-3.5'])
                            <span>Tolak Tiket</span>
                        </button>
                    </form>
                </details>
            </div>
        </div>
    @endif

    {{-- 4.4 STAF PELAKSANA: Selesaikan Pekerjaan (Status = Dalam Proses) --}}
    @if ($user->isStaf() && $tiket->status === 'Dalam Proses' && ($tiket->assigned_to === $user->id || $tiket->department_id === $user->effectiveDepartmentId()))
        <div class="bg-white rounded-2xl border-2 border-indigo-300 shadow-sm p-6">
            <div class="flex items-center gap-3 pb-4 mb-4 border-b border-indigo-100">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-xs">
                    @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-white'])
                </div>
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                        Wewenang Staf Pelaksana
                    </span>
                    <h3 class="text-base font-bold text-ink mt-0.5">Selesaikan Pekerjaan</h3>
                    <p class="text-xs text-slate-500">Tindakan ini akan menghentikan timer Resolution Time dan meneruskan tiket ke Pemohon untuk konfirmasi penutupan.</p>
                </div>
            </div>

            <form action="{{ route('tiket.selesai', $tiket) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Laporan Hasil Pengerjaan (Opsional)</label>
                    <textarea name="catatan" rows="3"
                              placeholder="Deskripsikan tindakan perbaikan, pergantian suku cadang, atau fasilitas yang telah disiapkan..."
                              class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-indigo-400 resize-none leading-relaxed"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-indigo-200'])
                        <span>Tandai Selesai Pengerjaan</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- 4.5 USER / PEMOHON: Konfirmasi & Tutup Tiket (Status = Selesai atau Ditolak) --}}
    @if ($user->isUser() && $tiket->pemohon_id === $user->id && in_array($tiket->status, ['Selesai', 'Ditolak']))
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50/40 to-white border-2 border-emerald-300 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-emerald-200">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
                    </div>
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                            Konfirmasi Pemohon Layanan
                        </span>
                        <h3 class="text-base font-bold text-emerald-950 mt-0.5">Tutup &amp; Konfirmasi Tiket</h3>
                        <p class="text-xs text-emerald-800">
                            {{ $tiket->status === 'Selesai' ? 'Pekerjaan telah diselesaikan oleh staf pelaksana. Silakan konfirmasi untuk menutup tiket.' : 'Tiket Anda ditolak oleh Kepala Bagian. Silakan konfirmasi penutupan tiket ini.' }}
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('tiket.tutup', $tiket) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Umpan Balik Pemohon (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Sampaikan kepuasan layanan atau masukan untuk perbaikan..."
                              class="w-full text-xs rounded-xl border border-slate-300 px-3.5 py-2.5 outline-none focus:border-emerald-500 resize-none leading-relaxed"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            onclick="return confirm('Konfirmasi dan tutup tiket ini secara resmi?')"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow">
                        @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4 text-emerald-200'])
                        <span>Konfirmasi &amp; Tutup Tiket</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- 4.6 STATUS FINAL: DITUTUP --}}
    @if ($tiket->status === 'Ditutup')
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-6 h-6'])
            </div>
            <div>
                <h4 class="text-sm font-bold text-ink">Tiket Telah Ditutup Resmi</h4>
                <p class="text-xs text-slate-400 mt-0.5">Seluruh rangkaian penanganan, resolusi, dan konfirmasi tiket layanan ini telah selesai.</p>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. CARD RIWAYAT & JEJAK LANGKAH (Timeline History)                        --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
        <div class="mb-5 pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-ink flex items-center gap-2">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-4 h-4 text-[#114E84]'])
                <span>Riwayat &amp; Jejak Langkah</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Pantau alur pengerjaan dan status tiket dari waktu ke waktu</p>
        </div>

        @if ($tiket->histories->isEmpty())
            <p class="text-xs text-slate-400 py-4 text-center">Belum ada jejak riwayat untuk tiket ini.</p>
        @else
            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                @foreach ($tiket->histories as $index => $h)
                    <div class="relative group">
                        {{-- Step Marker Dot --}}
                        <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $index === 0 ? 'bg-[#114E84] ring-4 ring-blue-100' : 'bg-slate-300' }} shadow-xs"></div>

                        <div>
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs font-bold text-ink">{{ $h->aksi }}</span>
                                <span class="text-[11px] text-slate-400 font-mono whitespace-nowrap">{{ $h->created_at->format('d M, H:i') }} WITA</span>
                            </div>

                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Oleh: <strong class="text-slate-700 font-medium">{{ $h->user?->nama_lengkap ?? 'Sistem' }}</strong>
                                <span class="text-slate-400">({{ $h->user?->role?->label ?? 'Petugas' }})</span>
                            </div>

                            @if ($h->status_lama && $h->status_baru)
                                <p class="text-xs text-slate-400 mt-0.5">
                                    <span>{{ $h->status_lama }}</span>
                                    <span class="mx-1">&rarr;</span>
                                    <span class="font-semibold text-slate-600">{{ $h->status_baru }}</span>
                                </p>
                            @endif

                            @if ($h->catatan)
                                <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 mt-2 leading-relaxed">
                                    {{ $h->catatan }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection