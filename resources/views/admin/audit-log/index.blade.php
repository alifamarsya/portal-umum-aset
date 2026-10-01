@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-[12px] font-semibold uppercase tracking-wide text-gold mb-0.5">Pengaturan</p>
            <h1 class="text-xl font-bold text-ink">Audit Log &amp; Verifikasi Integritas Blockchain</h1>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh aktivitas sistem dicatat dalam rantai hash internal dan otomatis di-anchor berkala setiap minggu ke Ethereum Sepolia testnet.</p>
        </div>
    </div>

    {{-- Flash Status Messages --}}
    @if (session('success'))
        <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-xs flex items-center justify-between text-xs text-emerald-800">
            <div class="flex items-center gap-2">
                <span class="text-base">✓</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-xs flex items-center justify-between text-xs text-rose-800">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif
    @if (session('info'))
        <div class="mb-5 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-xs flex items-center justify-between text-xs text-[#114E84]">
            <div class="flex items-center gap-2">
                <span class="text-base">ℹ</span>
                <span class="font-medium">{{ session('info') }}</span>
            </div>
        </div>
    @endif

    {{-- Banner Peringatan Jika Ada Manipulasi --}}
    @if (count($tamperedIds) > 0)
        <div class="mb-5 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm flex items-start gap-3">
            <div class="text-red-500 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-red-800">PERINGATAN: TERDETEKSI ANOMALI / MANIPULASI DATA!</h4>
                <p class="text-xs text-red-700 mt-0.5">
                    Sistem mendeteksi ketidakcocokan nilai hash pada <strong>{{ count($tamperedIds) }} baris data</strong> di bawah. Data pada baris bergaris merah telah diubah secara ilegal di database internal dan tidak lagi cocok dengan bukti di Blockchain.
                </p>
            </div>
        </div>
    @endif

    {{-- Form Filter & Tombol Tindakan --}}
    <div class="mb-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2 w-full lg:w-auto">
            <div class="relative flex-1 sm:w-80">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </span>
                <input name="search" value="{{ request('search') }}" placeholder="Cari username atau modul..."
                       class="w-full border border-slate-300 rounded-xl pl-9 pr-3.5 py-2 text-sm focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] transition">
            </div>
            <button type="submit" class="bg-[#114E84] text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-[#0E4272] transition whitespace-nowrap">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.audit-log.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">Reset</a>
            @endif
        </form>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Tombol Trigger Anchoring Sekarang --}}
            <form action="{{ route('admin.audit-log.trigger-anchor') }}" method="POST"
                  onsubmit="return confirm('Jalankan proses anchoring sekarang ke Sepolia testnet?')">
                @csrf
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                    <span>⚡ Jalankan Anchoring Sekarang</span>
                </button>
            </form>

            {{-- Tombol Rehash --}}
            <form action="{{ route('admin.audit-log.rehash') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-xl hover:bg-slate-700 transition flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                    <span>🔄 Sinkronkan Ulang Hash</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Tabel Utama Audit Log --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-[11px] uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">User</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Aksi</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Modul</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Status Hash &amp; Blockchain</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $log)
                        <tr class="transition {{ $log->is_tampered ? 'bg-red-50/70 border-l-4 border-l-red-500' : 'hover:bg-slate-50/60' }}">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500 font-mono text-[12px]">{{ $log->created_at }}</td>
                            <td class="px-4 py-3 text-ink font-medium">{{ $log->username }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'px-2.5 py-0.5 rounded-full text-[11px] font-medium capitalize',
                                    'bg-emerald-50 text-emerald-700' => str_contains(strtolower($log->aksi), 'tambah') || str_contains(strtolower($log->aksi), 'create'),
                                    'bg-blue-50 text-blue-700' => str_contains(strtolower($log->aksi), 'ubah') || str_contains(strtolower($log->aksi), 'update'),
                                    'bg-red-50 text-red-700' => str_contains(strtolower($log->aksi), 'hapus') || str_contains(strtolower($log->aksi), 'delete'),
                                    'bg-slate-100 text-slate-600' => !str_contains(strtolower($log->aksi), 'tambah') && !str_contains(strtolower($log->aksi), 'create') && !str_contains(strtolower($log->aksi), 'ubah') && !str_contains(strtolower($log->aksi), 'update') && !str_contains(strtolower($log->aksi), 'hapus') && !str_contains(strtolower($log->aksi), 'delete'),
                                ])>{{ $log->aksi }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->modul }} / {{ $log->entitas }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ \Illuminate\Support\Str::limit($log->keterangan, 50) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    @if ($log->is_tampered)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700 border border-red-300">
                                            ⚠️ Rusak / Dimanipulasi
                                        </span>
                                    @else
                                        <span class="font-mono text-[11px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md" title="{{ $log->hash }}">
                                            {{ \Illuminate\Support\Str::limit($log->hash, 10) }}
                                        </span>
                                    @endif

                                    @if ($log->anchored)
                                        <a href="https://sepolia.etherscan.io/tx/{{ $log->anchor_tx }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition"
                                           title="Aktivitas ini telah di-anchor ke Sepolia testnet">
                                            <span>🔗 Ter-anchor</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-slate-400">
                                Belum ada aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Section Riwayat Anchoring Blockchain --}}
    <div class="mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h3 class="font-bold text-ink text-base">Bukti Publik Blockchain (Sepolia Testnet)</h3>
                <p class="text-xs text-slate-500">Proses anchoring dijalankan otomatis setiap minggu oleh sistem (Laravel Scheduler + Queue Job).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                    <span>⏰ Jadwal: Setiap Senin 01:00 WITA</span>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-[11px] uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Periode Audit</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Jumlah Log</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Root Hash Terkunci</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Status Anchoring</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Block &amp; Waktu</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Integritas</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap">Bukti Publik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($anchors as $a)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-700 font-medium">
                                    {{ \Illuminate\Support\Carbon::parse($a->periode_awal)->format('d M') }} - {{ \Illuminate\Support\Carbon::parse($a->periode_akhir)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold text-slate-700">
                                    {{ $a->total_logs ?: '-' }} aktivitas
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">
                                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md" title="{{ $a->root_hash }}">
                                        {{ \Illuminate\Support\Str::limit($a->root_hash, 18) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($a->status === 'success')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sukses (Sepolia)
                                        </span>
                                    @elseif ($a->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Sedang Diproses
                                        </span>
                                    @elseif ($a->status === 'failed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $a->error_message }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Gagal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200">
                                            {{ ucfirst($a->status ?? 'Tersimpan') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500 font-mono">
                                    @if ($a->block_number)
                                        <div>Block #{{ $a->block_number }}</div>
                                    @endif
                                    @if ($a->anchored_at || $a->created_at)
                                        <div class="text-[11px] text-slate-400">{{ ($a->anchored_at ?? $a->created_at)->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($a->is_valid)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                            ✓ Sesuai Database
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 animate-pulse">
                                            ⚠ Tidak Cocok!
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($a->tx_hash)
                                        <a href="https://sepolia.etherscan.io/tx/{{ $a->tx_hash }}" target="_blank"
                                           class="text-[#114E84] font-medium hover:underline inline-flex items-center gap-1 text-xs">
                                            <span>Etherscan Explorer ↗</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum ada tx</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                                    Belum ada batch anchoring tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection