@extends('layouts.app')
@section('title', 'Detail Biaya — ' . $kategori)
@section('content')
    <div class="mb-6">
        <a href="{{ route('analitik') }}" class="text-xs text-slate-500 hover:text-gold mb-2 inline-block">&larr; Kembali ke Analitik</a>
        <h1 class="text-xl font-bold text-ink">Detail Biaya: {{ $kategori }}</h1>
        <p class="text-sm text-slate-500">Rincian transaksi harian yang membentuk agregat kategori ini (drill-down dari dashboard).</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-card mb-6">
        <p class="text-[12.5px] font-semibold text-slate-500 mb-1">Total {{ $kategori }} (Seluruh Periode Disetujui)</p>
        <p class="text-2xl font-bold text-ink">Rp {{ number_format($totalKategori, 0, ',', '.') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr>
                    <th class="px-4 py-2.5">Tanggal</th>
                    <th class="px-4 py-2.5">Nama Beban</th>
                    <th class="px-4 py-2.5 text-right">Jumlah</th>
                    <th class="px-4 py-2.5">Uraian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transaksi as $t)
                    <tr>
                        <td class="px-4 py-2.5">{{ $t->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-2.5">{{ $t->nama_beban ?: '-' }}</td>
                        <td class="px-4 py-2.5 text-right">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $t->uraian ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi disetujui untuk kategori ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection