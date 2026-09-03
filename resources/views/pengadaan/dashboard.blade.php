@extends('layouts.app')
@section('title', 'Dashboard Staf Pengadaan')

@section('content')
<div class="mb-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Dashboard Staf Pengadaan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring realisasi pengadaan, penawaran, negosiasi &amp; SPK</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('modul.create', 'spk') }}"
               class="px-3 py-2 text-xs font-semibold rounded-lg bg-brand text-white hover:bg-slate-800 transition shadow-2xs">
                + Draft SPK
            </a>
            <a href="{{ route('modul.create', 'memo_internal') }}"
               class="px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                + Memo Pengadaan
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('analitik') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-blue-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Total Nilai Pengadaan</p>
        <p class="text-base font-bold text-ink mt-2">Rp {{ number_format($totalPengadaan / 1000000, 1, ',', '.') }} Jt</p>
        <p class="text-[10px] text-blue-600 mt-1">Analisis belanja &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'spk') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-amber-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Surat Perintah Kerja</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\PgSpk::count() }}</p>
        <p class="text-[10px] text-amber-600 mt-1">Daftar SPK terbit &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'penawaran') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-emerald-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Penawaran Vendor</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\PgPenawaran::count() }}</p>
        <p class="text-[10px] text-emerald-600 mt-1">Evaluasi penawaran &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'reminder') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-purple-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Reminder / Garansi</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\PgReminder::count() }}</p>
        <p class="text-[10px] text-purple-600 mt-1">Monitoring garansi &rarr;</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-ink">SPK Terbaru</h2>
            <a href="{{ route('modul.index', 'spk') }}" class="text-xs font-semibold text-brand hover:underline">Lihat Semua</a>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-left">
                    <th class="pb-2">No SPK</th>
                    <th class="pb-2">Pekerjaan</th>
                    <th class="pb-2 text-right">Nilai</th>
                    <th class="pb-2 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse(\App\Models\PgSpk::latest('id')->limit(6)->get() as $spk)
                    <tr>
                        <td class="py-2 font-mono text-slate-600">{{ $spk->no_spk ?? '-' }}</td>
                        <td class="py-2 text-ink truncate max-w-[160px]">{{ $spk->pekerjaan }}</td>
                        <td class="py-2 text-right font-mono">Rp {{ number_format((float)$spk->nilai_spk, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium
                                {{ $spk->approval_status === 'Disetujui' ? 'bg-emerald-50 text-emerald-700' :
                                   ($spk->approval_status === 'Ditolak'  ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                {{ $spk->approval_status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada data SPK.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <h2 class="text-sm font-bold text-ink mb-3">Aksi Cepat Staf Pengadaan</h2>
        <div class="space-y-2">
            <a href="{{ route('modul.create', 'memo_internal') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Memo Internal Pengadaan
            </a>
            <a href="{{ route('modul.create', 'penawaran') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Input Penawaran Vendor
            </a>
            <a href="{{ route('modul.create', 'negosiasi') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Catat Hasil Negosiasi
            </a>
            <a href="{{ route('modul.create', 'spk') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Buat Draft SPK
            </a>
        </div>
    </div>
</div>
@endsection