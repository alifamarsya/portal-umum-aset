@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('dashboard') }}" class="hover:text-[#114E84] transition-colors">Dashboard</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">Profil Saya</span>
        </nav>

        {{-- Status Alert --}}
        @if(session('status'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('status') }}
        </div>
        @endif

        {{-- Profile Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Header Banner --}}
            <div class="h-28 bg-gradient-to-br from-[#114E84] to-[#1D6FB8] relative">
                <div class="absolute inset-0 opacity-10" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Ccircle cx='4' cy='4' r='2'/%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
            </div>

            {{-- Content --}}
            <div class="px-8 pb-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between -mt-12 mb-6 gap-4">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-[#114E84] to-[#1D6FB8] flex items-center justify-center shadow-lg border-4 border-white flex-shrink-0">
                        <span class="text-white text-3xl font-bold tracking-wider select-none">
                            {{ strtoupper(substr($user->nama_lengkap ?? $user->username, 0, 1)) }}{{ strtoupper(substr(strstr($user->nama_lengkap ?? '', ' ') ?: '', 1, 1)) }}
                        </span>
                    </div>
                    <a href="{{ route('profile.change-password') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#114E84] text-white text-sm font-medium hover:bg-[#0d3d67] transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Ubah Password
                    </a>
                </div>

                {{-- Info --}}
                <div class="space-y-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->nama_lengkap ?? $user->username }}</h1>
                        <span class="inline-flex items-center mt-1 px-3 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-[#114E84]">
                            {{ $user->role?->label ?? $user->role?->nama ?? 'Pengguna' }}
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Username --}}
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#114E84]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Username</p>
                                <p class="text-gray-800 font-semibold mt-0.5">{{ $user->username }}</p>
                            </div>
                        </div>

                        {{-- Email --}}
                        @if($user->email)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#114E84]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Email</p>
                                <p class="text-gray-800 font-semibold mt-0.5">{{ $user->email }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Jabatan --}}
                        @if($user->jabatan)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#114E84]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Jabatan</p>
                                <p class="text-gray-800 font-semibold mt-0.5">{{ $user->jabatan }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Bagian --}}
                        @if($user->bagian)
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#114E84]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Bagian</p>
                                <p class="text-gray-800 font-semibold mt-0.5">{{ $user->bagian }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Last Login --}}
                        @if($user->last_login)
                        <div class="flex items-start gap-3 sm:col-span-2">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#114E84]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Login Terakhir</p>
                                <p class="text-gray-800 font-semibold mt-0.5">{{ $user->last_login->format('d F Y, H:i') }} WIB</p>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Security Note --}}
        <div class="mt-4 flex items-center gap-2 text-xs text-gray-400 justify-center">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Data profil Anda terenkripsi dan aman
        </div>

    </div>
</div>
@endsection
