@extends('layouts.app')
@section('title', 'Dashboard Superadmin')

@section('content')
<div class="mb-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Dashboard Administrator</h1>
            <p class="text-xs text-slate-500 mt-0.5">Ringkasan pengelolaan sistem &amp; kepatuhan audit Bank Sulteng</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-xs font-semibold rounded-lg bg-brand text-white hover:bg-slate-800 transition shadow-2xs">
                + Kelola User
            </a>
            <a href="{{ route('admin.roles.index') }}" class="px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                Matriks Role
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <a href="{{ route('admin.users.index') }}" class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-blue-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Pengguna Aktif</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\User::where('is_active', true)->count() }}</p>
        <p class="text-[10px] text-blue-600 mt-1">Kelola data user &rarr;</p>
    </a>
    <a href="{{ route('admin.roles.index') }}" class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-indigo-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Total Role</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\Role::count() }} Role</p>
        <p class="text-[10px] text-indigo-600 mt-1">Atur hak akses &rarr;</p>
    </a>
    <a href="{{ route('admin.audit-log.index') }}" class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-emerald-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Jejak Audit Trail</p>
        <p class="text-2xl font-bold text-ink mt-2">{{ \App\Models\AuditLog::count() }}</p>
        <p class="text-[10px] text-emerald-600 mt-1">Cek rantai hash &rarr;</p>
    </a>
    <a href="{{ route('analitik') }}" class="bg-white rounded-xl p-4 border border-slate-200 shadow-2xs hover:border-purple-300 transition">
        <p class="text-[11px] font-medium text-slate-500">Data Warehouse</p>
        <p class="text-2xl font-bold text-ink mt-2">Analitik</p>
        <p class="text-[10px] text-purple-600 mt-1">Buka analitik DW &rarr;</p>
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-ink">Aktivitas Sistem Terbaru (Audit Trail)</h2>
        <a href="{{ route('admin.audit-log.index') }}" class="text-xs font-semibold text-brand hover:underline">Lihat Semua</a>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($activities as $act)
            <div class="py-2.5 flex items-center justify-between text-xs">
                <div>
                    <span class="font-semibold text-ink">{{ $act->username ?: 'Sistem' }}</span>
                    <span class="text-slate-500">&mdash; {{ $act->keterangan ?: $act->aksi }} ({{ $act->modul }})</span>
                </div>
                <span class="text-slate-400 font-mono text-[11px]">{{ optional($act->created_at)->format('d M H:i') }}</span>
            </div>
        @empty
            <p class="py-4 text-center text-slate-400 text-xs">Belum ada aktivitas.</p>
        @endforelse
    </div>
</div>
@endsection