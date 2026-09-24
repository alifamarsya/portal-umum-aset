@extends('layouts.app')
@section('title', 'Dashboard Staf Aset & Logistik')

@section('content')
<div class="mb-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Dashboard Staf Aset &amp; Logistik</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring inventaris, PKS jatuh tempo, sewa &amp; kalkulasi amortisasi</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('modul.create', 'aset') }}"
               class="px-3 py-2 text-xs font-semibold rounded-lg bg-brand text-white hover:bg-slate-800 transition shadow-2xs">
                + Data Aset
            </a>
            <a href="{{ route('modul.create', 'pks') }}"
               class="px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                + Kontrak PKS
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('modul.index', 'aset') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-emerald-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Total Unit Aset</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ number_format($totalAset) }}</p>
        <p class="text-[10px] text-emerald-600 mt-1">Inventaris kantor &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'pks') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-rose-300 transition">
        <p class="text-[11px] font-medium text-slate-500">PKS Jatuh Tempo</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ $pksJatuhTempo }}</p>
        <p class="text-[10px] text-rose-600 mt-1">&le; 90 hari ke depan &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'invoice_sewa') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-blue-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Invoice Sewa</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\AsInvoiceSewa::count() }}</p>
        <p class="text-[10px] text-blue-600 mt-1">Gedung / ATM &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'amortisasi') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-purple-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Amortisasi Aset</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\AsAmortisasi::count() }}</p>
        <p class="text-[10px] text-purple-600 mt-1">Hitung amortisasi &rarr;</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-ink">Kontrak PKS Mendekati Jatuh Tempo</h2>
            <a href="{{ route('modul.index', 'pks') }}" class="text-xs font-semibold text-brand hover:underline">Lihat Semua</a>
        </div>
        <div class="space-y-2.5">
            @forelse ($pksNearDue as $pks)
                @php
                    $daysLeft = now()->diffInDays($pks->jatuh_tempo, false);
                    $color = $daysLeft <= 30 ? 'bg-rose-100 text-rose-700' : 'bg-amber-50 text-amber-700';
                @endphp
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-ink">{{ $pks->judul }}</p>
                        <p class="text-[11px] text-slate-400">Vendor: {{ $pks->vendor ?? '-' }}</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $color }}">{{ $daysLeft }} hari lagi</span>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">Tidak ada PKS yang mendekati jatuh tempo.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <h2 class="text-sm font-bold text-ink mb-3">Aksi Cepat Staf Aset</h2>
        <div class="space-y-2">
            <a href="{{ route('modul.create', 'aset') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Data Aset &amp; Inventaris
            </a>
            <a href="{{ route('modul.create', 'pks') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Registrasi Kontrak PKS
            </a>
            <a href="{{ route('modul.create', 'invoice_sewa') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Invoice Sewa Cabang
            </a>
            <a href="{{ route('modul.create', 'amortisasi') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-ink">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4 text-brand flex-shrink-0'])
                Hitung Amortisasi Baru
            </a>
        </div>
    </div>
</div>
@endsection