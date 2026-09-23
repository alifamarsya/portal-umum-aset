<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Bank Sulteng</title>
    @include('partials.head-assets')
    <style>
        @media (min-width: 1024px) {
            .portum-sidebar-rail { position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 50; }
            .portum-main { margin-left: 270px; min-height: 100vh; }
        }
        .portum-nav-details > summary { list-style: none; }
        .portum-nav-details > summary::-webkit-details-marker { display: none; }
        .portum-nav-details[open] > summary .portum-chevron { transform: rotate(90deg); }
        .portum-chevron { transition: transform .18s ease; }
        .portum-submenu { animation: portumSubmenu .18s ease-out; }
        @keyframes portumSubmenu { from { opacity: .3; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-canvas text-ink antialiased min-h-full flex flex-col selection:bg-brand selection:text-gold">
@php
    $user = auth()->user();
    $permissions = $user?->role?->permissions?->keyBy('perm_key') ?? collect();

    $canAccess = fn (string $key) => $user ? $user->canAccessModule($key) : false;
    $canWrite  = fn (string $key) => $user ? $user->canWriteModule($key) : false;
    $isChecker = fn () => $user?->role?->nama === 'pimpinan';

    $canAccessDashboard = $canAccess('dashboard');
    $canAccessTiket     = $canAccess('tiket');
    $hasPengajuanMonitoring = $canAccessDashboard || $canAccessTiket;

    $operationalGroups = [
        [
            'perm' => 'umum_rt',
            'label' => 'Umum & Rumah Tangga',
            'icon' => 'building',
            'children' => [
                ['key' => 'kendaraan', 'label' => 'Kendaraan & Driver'],
                ['key' => 'biaya_harian', 'label' => 'Biaya BBM & Perawatan RT'],
                ['key' => 'fasilitas_kantor', 'label' => 'Master Fasilitas Kantor'],
                ['key' => 'pemeliharaan_gedung', 'label' => 'Pemeliharaan Gedung & Utilitas'],
                ['key' => 'checklist_kebersihan', 'label' => 'Checklist Kebersihan & K3'],
                ['key' => 'k3_insiden', 'label' => 'Catatan Insiden K3'],
            ],
        ],
        [
            'perm' => 'aset_logistik',
            'label' => 'Aset & Logistik',
            'icon' => 'layers',
            'children' => [
                ['key' => 'aset', 'label' => 'Data Aset & Inventaris'],
                ['key' => 'aset_history', 'label' => 'Riwayat Pergerakan Aset'],
                ['key' => 'mutasi_aset', 'label' => 'Mutasi Aset'],
                ['key' => 'disposal_aset', 'label' => 'Penghapusan Aset (Disposal)'],
                ['key' => 'rekonsiliasi_aset', 'label' => 'Rekonsiliasi & Reklasifikasi'],
                ['key' => 'penerimaan_barang', 'label' => 'Penerimaan Barang / Jasa'],
                ['key' => 'distribusi_barang', 'label' => 'Distribusi Barang / Jasa'],
                ['key' => 'pembayaran_tagihan', 'label' => 'Pembayaran Tagihan Logistik'],
                ['key' => 'invoice_sewa', 'label' => 'Invoice Sewa'],
                ['key' => 'amortisasi', 'label' => 'Amortisasi Aset'],
                ['key' => 'pks', 'label' => 'PKS & Jatuh Tempo'],
                ['key' => 'memo_sewa_cabang', 'label' => 'Memo Sewa Cabang'],
                ['key' => 'temuan', 'label' => 'Temuan Aset'],
            ],
        ],
        [
            'perm' => 'pengadaan',
            'label' => 'Pengadaan & Pemeliharaan',
            'icon' => 'cart',
            'children' => [
                ['key' => 'perencanaan_kebutuhan', 'label' => 'Perencanaan Kebutuhan'],
                ['key' => 'memo_internal', 'label' => 'Memo Internal'],
                ['key' => 'penawaran', 'label' => 'Penawaran Vendor'],
                ['key' => 'negosiasi', 'label' => 'Negosiasi Harga'],
                ['key' => 'draft_dokumen', 'label' => 'Draft Dokumen SPK'],
                ['key' => 'spk', 'label' => 'Surat Perintah Kerja (SPK)'],
                ['key' => 'jadwal_pemeliharaan', 'label' => 'Jadwal Pemeliharaan Rutin'],
                ['key' => 'monitoring_kondisi', 'label' => 'Monitoring Kondisi Fisik'],
                ['key' => 'pengawasan_penggunaan', 'label' => 'Pengawasan Penggunaan'],
                ['key' => 'tindak_lanjut_perbaikan', 'label' => 'Tindak Lanjut Perbaikan'],
                ['key' => 'reminder', 'label' => 'Reminder & Monitoring'],
            ],
        ],
        [
            'perm' => 'risalah',
            'label' => 'Dokumen & Kearsipan',
            'icon' => 'archive',
            'children' => [
                ['key' => 'arsip_dokumen', 'label' => 'Master Arsip Dokumen'],
                ['key' => 'dokumen_legalitas', 'label' => 'Dokumen Legalitas'],
                ['key' => 'surat_masuk', 'label' => 'Surat Masuk'],
                ['key' => 'surat_keluar', 'label' => 'Surat Keluar'],
                ['key' => 'memo_masuk', 'label' => 'Memo Masuk'],
                ['key' => 'memo_keluar', 'label' => 'Memo Keluar'],
            ],
        ],
    ];

    $visibleOperationalGroups = collect($operationalGroups)->filter(fn ($group) => $canAccess($group['perm']));

    $referenceItems = [
        ['perm' => 'risalah', 'label' => 'Risalah Rapat', 'route' => 'risalah.index', 'active' => request()->routeIs('risalah.*'), 'icon' => 'file-text'],
        ['perm' => 'ref_akun', 'label' => 'Referensi Akun (COA)', 'route' => 'modul.index', 'parameter' => 'ref_akun', 'active' => request()->route('key') === 'ref_akun', 'icon' => 'sliders'],
    ];
    $visibleReferences = collect($referenceItems)->filter(fn ($item) => $canAccess($item['perm']));
@endphp

{{-- ================= DESKTOP SIDEBAR ================= --}}
<aside class="portum-sidebar-rail hidden lg:flex flex-col bg-canvas select-none">
    {{-- 1. Top Logo Area (Seamless with Dashboard Background) --}}
    <div class="h-[72px] px-4 flex items-center justify-center bg-canvas flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center" style="max-width:170px;">
            <img src="{{ asset('images/bank-sulteng.png') }}"
                 alt="Bank Sulteng"
                 style="max-height:44px; width:auto; max-width:155px; object-fit:contain; display:block;">
        </a>
    </div>

    {{-- 2. Blue Sidebar Body with Rounded Top-Right Corner --}}
    <div class="flex-1 flex flex-col bg-gradient-to-b from-[#114E84] via-[#0E4272] to-[#0A335A] text-white rounded-tr-[36px] rounded-br-[36px] overflow-hidden shadow-2xl">
        {{-- Navigation Menu --}}
        <nav class="flex-1 overflow-y-auto pt-4 pb-2 space-y-4 text-[13px]">
            {{-- Modul Pengajuan & Monitoring --}}
            @if ($hasPengajuanMonitoring)
                <div>
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Pengajuan &amp; Monitoring</p>
                    <div class="space-y-1">
                        {{-- Submodul Dashboard --}}
                        @if ($canAccessDashboard)
                            <div class="px-3">
                                <a href="{{ route('dashboard') }}"
                                   class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('dashboard') ? 'text-[#114E84]' : 'text-white/80' }}">
                                            @include('partials.icon', ['name' => 'home', 'class' => 'w-[18px] h-[18px]'])
                                        </div>
                                        <span>Dashboard</span>
                                    </div>
                                    <span class="{{ request()->routeIs('dashboard') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                </a>
                            </div>
                        @endif

                        {{-- Submodul Sistem Tiket --}}
                        @if ($canAccessTiket)
                            <div class="px-3">
                                <a href="{{ route('tiket.index') }}"
                                   class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('tiket.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('tiket.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                            @include('partials.icon', ['name' => 'inbox', 'class' => 'w-[18px] h-[18px]'])
                                        </div>
                                        <span>Sistem Tiket</span>
                                    </div>
                                    <span class="{{ request()->routeIs('tiket.*') ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Modul Operasional (Hanya tampil jika role memiliki hak akses) --}}
            @if ($visibleOperationalGroups->isNotEmpty())
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Operasional</p>
                    <div class="space-y-0.5 px-3">
                        @foreach ($visibleOperationalGroups as $group)
                            @php
                                $groupActive = collect($group['children'])->contains(fn ($child) => request()->route('key') === $child['key']);
                            @endphp
                            <details class="portum-nav-details group" {{ $groupActive ? 'open' : '' }}>
                                <summary class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl cursor-pointer select-none transition text-white/90 hover:bg-white/10 hover:text-white {{ $groupActive ? 'bg-white/15 font-semibold text-white' : '' }}">
                                    <span class="flex items-center gap-3 min-w-0">
                                        <div class="w-6 h-6 flex items-center justify-center text-white/80 flex-shrink-0">
                                            @include('partials.icon', ['name' => $group['icon'], 'class' => 'w-[17px] h-[17px]'])
                                        </div>
                                        <span class="truncate text-[12.5px]">{{ $group['label'] }}</span>
                                    </span>
                                    <span class="portum-chevron text-white/50 text-xs leading-none">
                                        @include('partials.icon', ['name' => 'chevron-right', 'class' => 'w-3.5 h-3.5'])
                                    </span>
                                </summary>
                                <div class="portum-submenu mt-0.5 ml-5 pl-3 border-l border-white/20 space-y-0.5 py-1">
                                    @foreach ($group['children'] as $child)
                                        @php $active = request()->route('key') === $child['key']; @endphp
                                        <a href="{{ route('modul.index', $child['key']) }}"
                                           class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[12px] transition {{ $active ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-[#114E84]' : 'bg-white/40' }} flex-shrink-0"></span>
                                            <span class="truncate">{{ $child['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Referensi & Panduan --}}
            @if ($visibleReferences->isNotEmpty())
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Referensi</p>
                    <div class="space-y-0.5 px-3">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ $item['active'] ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-6 h-6 flex items-center justify-center {{ $item['active'] ? 'text-[#114E84]' : 'text-white/80' }} flex-shrink-0">
                                        @include('partials.icon', ['name' => $item['icon'], 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px] truncate">{{ $item['label'] }}</span>
                                </div>
                                <span class="{{ $item['active'] ? 'text-[#114E84]' : 'text-white/40' }} text-xs">▸</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Administrasi --}}
            @if ($canAccess('user_mgmt') || $canAccess('role_mgmt') || $canAccess('audit_log'))
                <div class="pt-1">
                    <p class="px-5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white/40">Administrasi</p>
                    <div class="space-y-0.5 px-3">
                        @if ($canAccess('user_mgmt'))
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.users.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.users.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'users', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">User</span>
                                </div>
                                <span class="text-white/40 text-xs">▸</span>
                            </a>
                        @endif
                        @if ($canAccess('role_mgmt'))
                            <a href="{{ route('admin.roles.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.roles.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.roles.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'shield', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">Role</span>
                                </div>
                                <span class="text-white/40 text-xs">▸</span>
                            </a>
                        @endif
                        @if ($canAccess('audit_log'))
                            <a href="{{ route('admin.audit-log.index') }}"
                               class="flex items-center justify-between gap-3 py-2 px-3 rounded-xl transition {{ request()->routeIs('admin.audit-log.*') ? 'bg-canvas text-[#114E84] font-bold shadow-2xs' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 flex items-center justify-center {{ request()->routeIs('admin.audit-log.*') ? 'text-[#114E84]' : 'text-white/80' }}">
                                        @include('partials.icon', ['name' => 'lock', 'class' => 'w-[17px] h-[17px]'])
                                    </div>
                                    <span class="text-[12.5px]">Audit Log</span>
                                </div>
                                <span class="text-white/40 text-xs">▸</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>

        {{-- 3. Footer: Powered by Bank Sulteng & User/Logout --}}
        <div class="p-3.5 border-t border-white/10 bg-black/15">
            <div class="flex items-center justify-between gap-2 mb-2 px-1">
                <div class="text-[11px] text-white/70">
                    Powered by <strong class="text-white font-bold">Bank Sulteng</strong>
                </div>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-white/10 backdrop-blur-xs">
                <div class="w-7 h-7 rounded-lg bg-white/20 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                    {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11.5px] font-bold text-white truncate leading-tight">{{ $user->nama_lengkap }}</p>
                    <p class="text-[9.5px] text-white/70 truncate">{{ $user->role->label }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-1 rounded-lg text-white/70 hover:text-white hover:bg-white/20 transition" title="Keluar" aria-label="Keluar">
                        @include('partials.icon', ['name' => 'logout', 'class' => 'w-3.5 h-3.5'])
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

{{-- ================= MOBILE HEADER ================= --}}
<div class="lg:hidden bg-gradient-to-r from-[#114E84] via-[#0E4272] to-[#0A335A] text-white px-4 h-14 flex items-center justify-between sticky top-0 z-40 shadow-sm">
    <div class="flex items-center gap-3">
        <button id="mobileMenuBtn" class="p-1.5 rounded-lg bg-white/10 text-white hover:bg-white/20 transition flex items-center justify-center" aria-label="Buka Menu">
            @include('partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
        </button>
        <div class="bg-white rounded-full px-3 py-1 shadow-xs flex items-center h-8">
            <img src="{{ asset('images/bank-sulteng.png') }}"
                 alt="Bank Sulteng"
                 class="h-5 w-auto max-w-[120px] object-contain"
                 style="max-height: 20px; width: auto;">
        </div>
    </div>
    <div class="flex items-center gap-2">
        {{-- Mobile Notification Bell --}}
        <button id="mobileNotifBtn"
                type="button"
                aria-label="Notifikasi"
                class="relative flex items-center justify-center w-8 h-8 rounded-lg text-white/80 hover:text-white hover:bg-white/20 transition"
                onclick="toggleMobileNotifSheet()">
            <svg class="w-4.5 h-4.5" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </button>
        {{-- Mobile Profile Button --}}
        <button id="mobileProfileBtn"
                type="button"
                aria-label="Profil"
                class="w-8 h-8 rounded-lg text-white font-bold flex items-center justify-center text-xs hover:bg-white/30 transition"
                style="background: rgba(255,255,255,0.2);"
                onclick="toggleMobileProfileSheet()">
            {{ strtoupper(substr($user->nama_lengkap, 0, 1)) }}
        </button>
    </div>
</div>

{{-- Mobile Sidebar Drawer --}}
<div id="mobileDrawer" class="lg:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden transition-opacity">
    <div class="fixed inset-y-0 left-0 w-[280px] bg-gradient-to-b from-[#114E84] to-[#0A335A] text-white flex flex-col shadow-2xl overflow-y-auto">
        <div class="pt-4 pb-3 pl-0 pr-3 flex items-center justify-between border-b border-white/10">
            <div class="bg-white rounded-r-full py-2 px-4 shadow-sm inline-flex items-center">
                <img src="{{ asset('images/bank-sulteng.png') }}" alt="Bank Sulteng" class="h-5 w-auto" style="max-height: 22px; width: auto;">
            </div>
            <button id="closeMobileDrawer" class="p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10" aria-label="Tutup Menu">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>
        <nav class="flex-1 p-3 space-y-3 text-xs">
            {{-- Pengajuan & Monitoring --}}
            @if ($hasPengajuanMonitoring)
                <div>
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Pengajuan &amp; Monitoring</p>
                    <div class="space-y-1">
                        @if ($canAccessDashboard)
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-2 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white font-bold' : 'text-white/80' }}">
                                @include('partials.icon', ['name' => 'home', 'class' => 'w-4 h-4'])
                                <span>Dashboard</span>
                            </a>
                        @endif
                        @if ($canAccessTiket)
                            <a href="{{ route('tiket.index') }}" class="flex items-center gap-3 p-2 rounded-xl {{ request()->routeIs('tiket.*') ? 'bg-white/20 text-white font-bold' : 'text-white/80' }}">
                                @include('partials.icon', ['name' => 'inbox', 'class' => 'w-4 h-4'])
                                <span>Sistem Tiket</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Modul Operasional (Hanya tampil jika ada akses) --}}
            @if ($visibleOperationalGroups->isNotEmpty())
                <div class="pt-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Operasional</p>
                    <div class="space-y-2">
                        @foreach ($visibleOperationalGroups as $group)
                            <div>
                                <p class="text-[10.5px] font-semibold text-white/70 px-2">{{ $group['label'] }}</p>
                                <div class="space-y-0.5 pl-2">
                                    @foreach ($group['children'] as $child)
                                        <a href="{{ route('modul.index', $child['key']) }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ request()->route('key') === $child['key'] ? 'text-white font-bold bg-white/20' : '' }}">
                                            • {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Referensi --}}
            @if ($visibleReferences->isNotEmpty())
                <div class="pt-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Referensi</p>
                    <div class="space-y-0.5 pl-2">
                        @foreach ($visibleReferences as $item)
                            <a href="{{ isset($item['parameter']) ? route($item['route'], $item['parameter']) : route($item['route']) }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ $item['active'] ? 'text-white font-bold bg-white/20' : '' }}">
                                • {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Administrasi --}}
            @if ($canAccess('user_mgmt') || $canAccess('role_mgmt') || $canAccess('audit_log'))
                <div class="pt-1">
                    <p class="text-[10px] font-bold text-white/50 uppercase px-2 mb-1">Administrasi</p>
                    <div class="space-y-0.5 pl-2">
                        @if ($canAccess('user_mgmt'))
                            <a href="{{ route('admin.users.index') }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ request()->routeIs('admin.users.*') ? 'text-white font-bold bg-white/20' : '' }}">• User</a>
                        @endif
                        @if ($canAccess('role_mgmt'))
                            <a href="{{ route('admin.roles.index') }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ request()->routeIs('admin.roles.*') ? 'text-white font-bold bg-white/20' : '' }}">• Role</a>
                        @endif
                        @if ($canAccess('audit_log'))
                            <a href="{{ route('admin.audit-log.index') }}" class="block px-2 py-1.5 rounded-lg text-white/80 hover:text-white {{ request()->routeIs('admin.audit-log.*') ? 'text-white font-bold bg-white/20' : '' }}">• Audit Log</a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>
        <div class="p-3 border-t border-white/10 flex items-center justify-between bg-black/10">
            <div class="min-w-0">
                <p class="text-xs font-bold text-white truncate">{{ $user->nama_lengkap }}</p>
                <p class="text-[10px] text-white/70 truncate">{{ $user->role->label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-white/80 hover:text-white p-2">
                    @include('partials.icon', ['name' => 'logout', 'class' => 'w-4 h-4'])
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ================= MAIN CONTENT WRAPPER ================= --}}
<div class="portum-main min-w-0 flex-1 flex flex-col bg-canvas">
    {{-- Clean Top Header --}}
    <header class="glass-header sticky top-0 z-30 h-[72px] border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3">
        {{-- Breadcrumb / Current Route Context --}}
        <div class="flex items-center gap-2 min-w-0">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium min-w-0">
                <a href="{{ route('dashboard') }}" class="hover:text-brand transition-colors flex-shrink-0">Bank Sulteng</a>
                <span class="text-slate-300 flex-shrink-0">/</span>
                <span class="text-ink font-semibold truncate">@yield('title', 'Dashboard')</span>
            </div>
        </div>

        {{-- Right Controls --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            {{-- Widget Tanggal --}}
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 font-medium px-2 py-1.5 bg-white border border-slate-200 rounded-lg">
                @include('partials.icon', ['name' => 'calendar', 'class' => 'w-3.5 h-3.5 text-slate-400'])
                <span class="hidden md:inline">{{ now()->translatedFormat('d M Y') }}</span>
                <span class="md:hidden">{{ now()->format('d/m') }}</span>
            </div>

            {{-- Notifikasi Lonceng --}}
            @include('partials.notification-dropdown')

            {{-- Profil Dropdown --}}
            @include('partials.profile-dropdown')
        </div>
    </header>

    {{-- Main View Body --}}
    <main class="flex-1 px-5 lg:px-8 py-6 max-w-7xl w-full mx-auto">
        @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5 text-emerald-600 flex-shrink-0', 'stroke' => 2])
                <span class="font-medium">{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-center gap-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5 text-rose-600 flex-shrink-0', 'stroke' => 2])
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 text-sm animate-enter">
                @include('partials.icon', ['name' => 'alert', 'class' => 'w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5', 'stroke' => 2])
                <ul class="list-disc pl-4 space-y-0.5 font-medium">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="animate-enter">
            @yield('content')
        </div>
    </main>
</div>

{{-- ===== Mobile Notification Sheet ===== --}}
<div id="mobileNotifSheet"
     class="lg:hidden fixed inset-0 z-[60] bg-black/50 backdrop-blur-xs hidden"
     onclick="if(event.target===this)closeMobileNotifSheet()">
    <div class="fixed bottom-0 inset-x-0 bg-white rounded-t-2xl shadow-2xl p-5 pb-8" style="max-height: 60vh; overflow-y:auto;">
        <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto mb-4"></div>
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="font-semibold text-slate-800">Notifikasi</span>
        </div>
        <div class="flex flex-col items-center py-8 text-center">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-600">Belum ada notifikasi</p>
            <p class="text-xs text-slate-400 mt-1">Notifikasi baru akan muncul di sini</p>
        </div>
    </div>
</div>

{{-- ===== Mobile Profile Sheet ===== --}}
<div id="mobileProfileSheet"
     class="lg:hidden fixed inset-0 z-[60] bg-black/50 backdrop-blur-xs hidden"
     onclick="if(event.target===this)closeMobileProfileSheet()">
    <div class="fixed bottom-0 inset-x-0 bg-white rounded-t-2xl shadow-2xl pb-8">
        <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto mt-4 mb-0"></div>
        {{-- User Info --}}
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background: linear-gradient(135deg, #1D6FB8 0%, #0F487F 100%);">
                    {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ $user->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $user->username }}</p>
                    <span class="inline-flex items-center mt-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-semibold text-white" style="background:#1D6FB8;">
                        {{ $user->role?->label }}
                    </span>
                </div>
            </div>
        </div>
        {{-- Menu --}}
        <div class="px-3 py-2 space-y-0.5">
            <a href="{{ route('profile.show') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-50 transition text-slate-700">
                <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span class="font-medium text-sm">Data Pribadi</span>
            </a>
            <a href="{{ route('profile.change-password') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-50 transition text-slate-700">
                <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </span>
                <span class="font-medium text-sm">Ubah Password</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="px-0">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-red-50 transition text-red-600">
                    <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span class="font-medium text-sm">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // ── Mobile Sidebar Drawer ────────────────────────────────────────────────
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const closeMobileDrawer = document.getElementById('closeMobileDrawer');

    if (mobileMenuBtn && mobileDrawer && closeMobileDrawer) {
        mobileMenuBtn.addEventListener('click', () => mobileDrawer.classList.remove('hidden'));
        closeMobileDrawer.addEventListener('click', () => mobileDrawer.classList.add('hidden'));
        mobileDrawer.addEventListener('click', (e) => {
            if (e.target === mobileDrawer) mobileDrawer.classList.add('hidden');
        });
    }

    // ── Shared Dropdown Utils ────────────────────────────────────────────────
    function closeAllDropdowns() {
        var ids = ['notifDropdown', 'profileDropdown'];
        ids.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        var chevron = document.getElementById('profileChevron');
        if (chevron) chevron.style.transform = '';
    }

    // Click-outside to close dropdowns
    document.addEventListener('click', function(e) {
        var notifWrapper = document.getElementById('notifDropdownWrapper');
        var profileWrapper = document.getElementById('profileDropdownWrapper');

        if (notifWrapper && !notifWrapper.contains(e.target)) {
            var nd = document.getElementById('notifDropdown');
            if (nd) nd.classList.add('hidden');
        }
        if (profileWrapper && !profileWrapper.contains(e.target)) {
            var pd = document.getElementById('profileDropdown');
            if (pd) pd.classList.add('hidden');
            var chevron = document.getElementById('profileChevron');
            if (chevron) chevron.style.transform = '';
        }
    });

    // ── Mobile Notification Sheet ────────────────────────────────────────────
    function toggleMobileNotifSheet() {
        var sheet = document.getElementById('mobileNotifSheet');
        if (sheet) sheet.classList.toggle('hidden');
    }
    function closeMobileNotifSheet() {
        var sheet = document.getElementById('mobileNotifSheet');
        if (sheet) sheet.classList.add('hidden');
    }

    // ── Mobile Profile Sheet ─────────────────────────────────────────────────
    function toggleMobileProfileSheet() {
        var sheet = document.getElementById('mobileProfileSheet');
        if (sheet) sheet.classList.toggle('hidden');
    }
    function closeMobileProfileSheet() {
        var sheet = document.getElementById('mobileProfileSheet');
        if (sheet) sheet.classList.add('hidden');
    }
</script>
</body>
</html>


