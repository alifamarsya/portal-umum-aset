@extends('layouts.app')
@section('title', 'Dashboard Pimpinan Divisi')

@section('content')
<div class="mb-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Dashboard Pimpinan Divisi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring &amp; antrean persetujuan transaksi operasional (Checker)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('modul.index', 'biaya_harian') }}"
               class="px-4 py-2 text-xs font-bold rounded-lg bg-amber-500 text-white hover:bg-amber-600 transition shadow-2xs flex items-center gap-1.5">
                @include('partials.icon', ['name' => 'clock', 'class' => 'w-3.5 h-3.5'])
                Antrean Persetujuan ({{ $menunggu }})
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('modul.index', 'biaya_harian') }}"
       class="bg-amber-500 text-white rounded-xl p-4 shadow-2xs hover:bg-amber-600 transition">
        <p class="text-[11px] font-semibold text-amber-100">Perlu Disetujui</p>
        <p class="text-3xl font-extrabold mt-2">{{ $menunggu }}</p>
        <p class="text-[10px] text-amber-100 mt-1">Transaksi menunggu checker &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'pks') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-rose-300 transition">
        <p class="text-[11px] font-medium text-slate-500">PKS Jatuh Tempo</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ $pksJatuhTempo }}</p>
        <p class="text-[10px] text-rose-600 mt-1">&le; 90 hari ke depan &rarr;</p>
    </a>
    <a href="{{ route('modul.index', 'aset') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-emerald-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Total Unit Aset</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ number_format($totalAset) }}</p>
        <p class="text-[10px] text-emerald-600 mt-1">Inventaris Bank Sulteng &rarr;</p>
    </a>
    <a href="{{ route('analitik') }}"
       class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-blue-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Total Pengadaan</p>
        <p class="text-base font-bold text-ink mt-2">Rp {{ number_format($totalPengadaan / 1000000, 1, ',', '.') }} Jt</p>
        <p class="text-[10px] text-blue-600 mt-1">Lihat analitik DW &rarr;</p>
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
    <section class="xl:col-span-2 bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-ink">Realisasi Biaya Operasional</h2>
                <p class="text-[11px] text-slate-400">Tren 6 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-3 text-[10px] font-medium text-slate-500">
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#3b82f6]"></span> BBM</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#f59e0b]"></span> Perawatan</span>
                <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm inline-block bg-[#10b981]"></span> RT</span>
            </div>
        </div>
        <div style="position:relative; height:180px;">
            <canvas id="biayaChart"></canvas>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[11px] text-slate-500">Total 6 bulan:
                <span class="font-bold text-ink">Rp {{ number_format($totalBiaya6Bln / 1000000, 1, ',', '.') }} Jt</span>
            </p>
            <a href="{{ route('analitik') }}" class="text-[11px] font-semibold text-brand hover:underline">Detail Analitik DW &rarr;</a>
        </div>
    </section>

    <section class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-ink">PKS Segera Berakhir</h2>
            <a href="{{ route('modul.index', 'pks') }}" class="text-[11px] text-slate-400 hover:text-brand font-semibold">Lihat semua</a>
        </div>
        <div class="space-y-2.5">
            @forelse ($pksNearDue as $pks)
                @php
                    $daysLeft = now()->diffInDays($pks->jatuh_tempo, false);
                    $color = $daysLeft <= 30 ? 'bg-rose-100 text-rose-700' : 'bg-amber-50 text-amber-700';
                    $dot   = $daysLeft <= 30 ? 'bg-rose-500' : 'bg-amber-400';
                @endphp
                <div class="flex items-start gap-2.5 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="w-2 h-2 rounded-full {{ $dot }} mt-1.5 flex-shrink-0"></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink truncate">{{ $pks->judul }}</p>
                        <p class="text-[10px] text-slate-400">{{ $pks->vendor ?? 'Vendor tidak dicatat' }}</p>
                    </div>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $color }}">{{ $daysLeft }} hr</span>
                </div>
            @empty
                <p class="py-6 text-center text-xs text-slate-400">Tidak ada kontrak yang akan berakhir.</p>
            @endforelse
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const el = document.getElementById('biayaChart');
    if (!el) return;
    const labels = @json($chartLabels ?? []);
    const bbm    = @json($chartBbm ?? []);
    const rawat  = @json($chartPerawatan ?? []);
    const rt     = @json($chartRt ?? []);
    const toJt   = v => +(v / 1_000_000).toFixed(2);

    new Chart(el, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'BBM', data: bbm.map(toJt), backgroundColor: '#3b82f6cc' },
                { label: 'Perawatan', data: rawat.map(toJt), backgroundColor: '#f59e0bcc' },
                { label: 'Rumah Tangga', data: rt.map(toJt), backgroundColor: '#10b981cc' },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { stacked: true }, y: { stacked: true } }
        }
    });
})();
</script>
@endsection

