@extends('layouts.app')
@section('title', 'Daftar Pengeluaran BBM & RT')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-ink">Biaya Harian BBM &amp; Rumah Tangga</h1>
        <p class="text-xs text-slate-500">Pencatatan pengeluaran bensin, servis, dan kebutuhan rumah tangga kantor</p>
    </div>
    @if ($canWrite('umum_rt'))
        <a href="{{ route('modul.create', 'biaya_harian') }}"
           class="px-3.5 py-2 rounded-lg bg-brand text-white text-xs font-semibold hover:bg-slate-800 transition">
            + Tambah Pengeluaran
        </a>
    @endif
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
    <div class="p-4 border-b border-slate-100">
        <form method="GET" action="{{ route('modul.index', 'biaya_harian') }}" class="max-w-xs">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari uraian atau kategori..."
                   class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:ring-1 focus:ring-brand">
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                <tr>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Kategori</th>
                    <th class="p-3">Uraian</th>
                    <th class="p-3 text-right">Jumlah (Rp)</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $item)
                    <tr class="hover:bg-slate-50/50">
                        <td class="p-3 text-slate-600">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td class="p-3 font-semibold text-ink">{{ $item->kategori }}</td>
                        <td class="p-3 text-slate-700">{{ $item->uraian }}</td>
                        <td class="p-3 text-right font-mono font-medium">Rp {{ number_format((float)$item->jumlah, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                {{ $item->status ?? 'Tercatat' }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            @if ($canWrite('umum_rt'))
                                <a href="{{ route('modul.edit', ['key' => 'biaya_harian', 'id' => $item->id]) }}"
                                   class="text-xs font-semibold text-brand hover:underline mr-2">Ubah</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada data pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    @endif
</div>
@endsection