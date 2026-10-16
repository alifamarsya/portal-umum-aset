@extends('layouts.app')

@section('title', 'Pengaturan — Profil')

@section('content')
<div class="w-full max-w-full">

    {{-- Breadcrumb & Page Header --}}
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5">
            <a href="{{ route('dashboard') }}" class="hover:text-[#114E84] transition">Dashboard</a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-600 font-medium">Pengaturan</span>
            <span class="text-slate-300">/</span>
            <span class="text-slate-600 font-medium">Profil</span>
        </nav>
        <h1 class="text-2xl font-bold text-ink">Pengaturan</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi akun dan preferensi Anda di Portal Umum &amp; Aset Bank Sulteng</p>
    </div>

    {{-- Status Alert --}}
    @if (session('status'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-xs font-medium mb-6">
            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600 flex-shrink-0'])
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[230px_minmax(0,1fr)] gap-6 items-start">

        {{-- Settings Sidebar --}}
        <nav class="bg-white rounded-2xl border border-slate-200 p-3 lg:sticky lg:top-6" aria-label="Navigasi pengaturan">
            <p class="px-3 pt-1 pb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Umum</p>
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-blue-50 text-[#114E84]">
                @include('partials.icon', ['name' => 'users', 'class' => 'w-[18px] h-[18px]'])
                Profil
            </a>

            <p class="px-3 pt-4 pb-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Keamanan</p>
            <a href="{{ route('profile.change-password') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-[18px] h-[18px]'])
                Ubah Password
            </a>
        </nav>

        {{-- Profile Panel --}}
        <section class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 sm:px-8 pt-6 sm:pt-8 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    {{-- Avatar (inisial, tanpa foto) --}}
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#114E84] to-[#1D6FB8] flex items-center justify-center text-white font-extrabold text-xl select-none flex-shrink-0">
                        {{ strtoupper(substr($user->nama_lengkap ?? $user->username, 0, 1)) }}{{ strtoupper(substr(strstr($user->nama_lengkap ?? '', ' ') ?: '', 1, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-bold text-ink leading-tight truncate">
                            {{ $user->nama_lengkap ?? $user->username }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $user->email ?: $user->username }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-[#114E84] border border-blue-200">
                                {{ $user->role?->label ?? $user->role?->nama ?? 'Pengguna' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Akun Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flat attribute rows --}}
            <dl class="divide-y divide-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Nama Lengkap</p>
                        <p class="text-xs text-slate-400 mt-0.5">Nama yang ditampilkan di seluruh aplikasi.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->nama_lengkap ?? $user->username }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Username</p>
                        <p class="text-xs text-slate-400 mt-0.5">Digunakan untuk masuk ke akun.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->username }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Alamat Email</p>
                        <p class="text-xs text-slate-400 mt-0.5">Digunakan untuk notifikasi akun.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->email ?: 'Tidak dicantumkan' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Jabatan</p>
                        <p class="text-xs text-slate-400 mt-0.5">Ditampilkan di samping nama di seluruh aplikasi.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->jabatan ?: 'Staf Bank Sulteng' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Bagian / Unit Kerja</p>
                        <p class="text-xs text-slate-400 mt-0.5">Unit kerja tempat akun terdaftar.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->bagian ?: 'Divisi Umum & Aset' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Hak Akses Sistem</p>
                        <p class="text-xs text-slate-400 mt-0.5">Peran yang menentukan menu dan fitur yang dapat diakses.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">{{ $user->role?->label ?? $user->role?->nama ?? '—' }}</dd>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[220px_minmax(0,1fr)] gap-1 sm:gap-6 px-6 sm:px-8 py-4">
                    <dt>
                        <p class="text-sm font-semibold text-slate-800">Login Terakhir</p>
                        <p class="text-xs text-slate-400 mt-0.5">Waktu terakhir akun ini masuk ke sistem.</p>
                    </dt>
                    <dd class="text-sm text-slate-600 sm:pt-0.5">
                        @if ($user->last_login)
                            {{ $user->last_login->format('d F Y, H:i') }} WITA
                        @else
                            Sesi ini
                        @endif
                    </dd>
                </div>
            </dl>
        </section>
    </div>

</div>
@endsection
