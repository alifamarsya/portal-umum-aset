@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('content')
<div class="space-y-6 w-full max-w-full">

    {{-- Breadcrumb & Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-[#114E84] transition">Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-600 font-medium">Profil Pengguna</span>
            </nav>
            <h1 class="text-2xl font-bold text-ink">Profil Pengguna</h1>
            <p class="text-xs text-slate-500 mt-0.5">Informasi akun, unit kerja, dan hak akses pengguna di Portal Umum &amp; Aset Bank Sulteng</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('profile.change-password') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold shadow-2xs transition">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-4 h-4 text-white'])
                <span>Ubah Password</span>
            </a>
        </div>
    </div>

    {{-- Status Alert --}}
    @if (session('status'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-xs font-medium animate-enter">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600 flex-shrink-0'])
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Main Profile Card (Full Width) --}}
    <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">

        {{-- Header Banner --}}
        <div class="h-36 sm:h-44 bg-gradient-to-r from-[#114E84] via-[#0E4272] to-[#1D6FB8] relative overflow-hidden">
            {{-- Decorative pattern overlay --}}
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1.5px, transparent 1.5px); background-size: 20px 20px;"></div>
            <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="absolute right-24 -top-12 w-48 h-48 rounded-full bg-white/5 pointer-events-none"></div>
        </div>

        {{-- Profile Content Area --}}
        <div class="px-6 sm:px-8 pb-8">
            {{-- Top Identity Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between -mt-16 sm:-mt-20 mb-8 gap-4 border-b border-slate-100 pb-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 text-center sm:text-left">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-br from-[#114E84] to-[#1D6FB8] flex items-center justify-center shadow-lg border-4 border-white flex-shrink-0 text-white font-extrabold text-3xl sm:text-4xl select-none">
                        {{ strtoupper(substr($user->nama_lengkap ?? $user->username, 0, 1)) }}{{ strtoupper(substr(strstr($user->nama_lengkap ?? '', ' ') ?: '', 1, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-xl sm:text-2xl font-bold text-ink leading-tight">
                            {{ $user->nama_lengkap ?? $user->username }}
                        </h2>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#114E84] border border-blue-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                {{ $user->role?->label ?? $user->role?->nama ?? 'Pengguna' }}
                            </span>
                            @if ($user->bagian)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $user->bagian }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Akun Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Attributes Grid (Full Width) --}}
            <div class="space-y-6">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        Informasi Akun &amp; Kredensial
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Username --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Username</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">{{ $user->username }}</p>
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Alamat Email</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    {{ $user->email ?: 'Tidak dicantumkan' }}
                                </p>
                            </div>
                        </div>

                        {{-- Jabatan --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'building', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Jabatan</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    {{ $user->jabatan ?: 'Staf Bank Sulteng' }}
                                </p>
                            </div>
                        </div>

                        {{-- Bagian / Unit Kerja --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'layers', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Bagian / Unit Kerja</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    {{ $user->bagian ?: 'Divisi Umum & Aset' }}
                                </p>
                            </div>
                        </div>

                        {{-- Role / Hak Akses --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Hak Akses Sistem</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    {{ $user->role?->label ?? $user->role?->nama }}
                                </p>
                            </div>
                        </div>

                        {{-- Login Terakhir --}}
                        <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-[#114E84] flex-shrink-0 shadow-2xs">
                                @include('partials.icon', ['name' => 'clock', 'class' => 'w-5 h-5'])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Login Terakhir</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5 truncate">
                                    @if ($user->last_login)
                                        {{ $user->last_login->format('d F Y, H:i') }} WITA
                                    @else
                                        Sesi ini
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Security & Governance Note --}}
                <div class="rounded-xl bg-blue-50/70 border border-blue-200/80 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 text-[#114E84] flex items-center justify-center flex-shrink-0 mt-0.5">
                            @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#114E84]">Keamanan Akun &amp; Rekam Jejak Audit</p>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Seluruh aktivitas transaksi tiket dan mutasi aset tercatat dalam audit log sistem yang di-anchor secara kriptografis ke blockchain Ethereum Sepolia demi transparansi dan integritas data Bank Sulteng.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('profile.change-password') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-semibold shadow-2xs transition whitespace-nowrap self-stretch sm:self-auto justify-center">
                        @include('partials.icon', ['name' => 'lock', 'class' => 'w-3.5 h-3.5 text-white'])
                        <span>Ganti Password</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
