@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">
    {{-- Header Page --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase bg-[#114E84]/10 text-[#114E84]">
                    Modul Administrasi
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Sub-Modul User</span>
            </div>
            <h1 class="text-2xl font-black text-ink tracking-tight mt-1">Manajemen Pengguna (User)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola akun, hak akses role, status keaktifan, dan kredensial login tanpa perlu akses database manual.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-2xs">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4 text-slate-500'])
                <span>Kelola Role &amp; Hak Akses</span>
            </a>
            <button type="button" onclick="openCreateUserModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold transition shadow-sm hover:shadow">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                <span>+ Tambah User Baru</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan Statistik KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'users', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total User</p>
                <p class="text-2xl font-black text-ink leading-tight">{{ $items->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">User Aktif</p>
                <p class="text-2xl font-black text-emerald-600 leading-tight">{{ $items->where('is_active', true)->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Non-Aktif</p>
                <p class="text-2xl font-black text-rose-600 leading-tight">{{ $items->where('is_active', false)->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Role</p>
                <p class="text-2xl font-black text-ink leading-tight">{{ $roles->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
        {{-- Toolbar Filter & Search --}}
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    @include('partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                </span>
                <input type="text"
                       id="userSearchInput"
                       placeholder="Cari berdasarkan nama, username, email, atau jabatan..."
                       class="w-full bg-white text-xs rounded-xl pl-9 pr-3 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] text-slate-700 placeholder-slate-400 outline-none transition">
            </div>

            <div class="flex items-center gap-2">
                <select id="userRoleFilter" class="bg-white text-xs rounded-xl px-3 py-2.5 border border-slate-200 focus:border-[#114E84] text-slate-700 outline-none transition">
                    <option value="">Semua Role ({{ $roles->count() }})</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->label }}</option>
                    @endforeach
                </select>

                <select id="userStatusFilter" class="bg-white text-xs rounded-xl px-3 py-2.5 border border-slate-200 focus:border-[#114E84] text-slate-700 outline-none transition">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>
        </div>

        {{-- Tabel Pengguna --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 border-collapse" id="userTable">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/70 text-[11px] uppercase tracking-wider font-bold text-slate-500">
                        <th class="px-5 py-3.5">Nama &amp; Akun</th>
                        <th class="px-4 py-3.5">Role</th>
                        <th class="px-4 py-3.5">Jabatan &amp; Bagian</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Terakhir Login</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-normal">
                    @forelse ($items as $u)
                        @php
                            $roleColors = [
                                'superadmin' => 'bg-purple-50 text-purple-700 border-purple-200',
                                'pimpinan'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                'umum_rt'    => 'bg-amber-50 text-amber-700 border-amber-200',
                                'aset'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'pengadaan'  => 'bg-teal-50 text-teal-700 border-teal-200',
                            ];
                            $badgeColor = $roleColors[$u->role?->nama] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            $isSelf = auth()->id() === $u->id;
                        @endphp
                        <tr class="user-row hover:bg-slate-50/80 transition"
                            data-name="{{ strtolower($u->nama_lengkap) }}"
                            data-username="{{ strtolower($u->username) }}"
                            data-email="{{ strtolower($u->email ?? '') }}"
                            data-jabatan="{{ strtolower($u->jabatan ?? '') }}"
                            data-role-id="{{ $u->role_id }}"
                            data-status="{{ $u->is_active ? '1' : '0' }}">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#114E84] to-[#072440] text-white flex items-center justify-center font-bold text-xs shadow-2xs flex-shrink-0">
                                        {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-ink text-sm">{{ $u->nama_lengkap }}</span>
                                            @if ($isSelf)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-extrabold bg-blue-100 text-blue-700">Anda</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-400 font-mono text-[11px] mt-0.5">
                                            <span>@<span>{{ $u->username }}</span></span>
                                            @if ($u->email)
                                                <span>•</span>
                                                <span class="truncate max-w-[180px] font-sans text-slate-500">{{ $u->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border {{ $badgeColor }}">
                                    @include('partials.icon', ['name' => 'shield', 'class' => 'w-3 h-3'])
                                    {{ $u->role?->label ?? '-' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-ink font-medium">{{ $u->jabatan ?: '-' }}</div>
                                <div class="text-slate-400 text-[11px]">{{ $u->bagian ?: '-' }}</div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($u->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Non-Aktif
                                    </span>
                                @endif
                                @if ($u->must_change_pwd)
                                    <div class="text-[10px] text-amber-600 font-medium mt-1">Wajib ganti pwd</div>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-400 font-mono text-[11px]">
                                {{ $u->last_login ? $u->last_login->translatedFormat('d M Y, H:i') : 'Belum pernah login' }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Tombol Edit User --}}
                                    <button type="button"
                                            onclick='openEditUserModal(@json($u))'
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-[#114E84] hover:bg-blue-50 transition"
                                            title="Ubah Data Pengguna">
                                        @include('partials.icon', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                                    </button>

                                    {{-- Tombol Ganti / Reset Password --}}
                                    <button type="button"
                                            onclick='openPasswordModal(@json($u))'
                                            class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-amber-50 transition"
                                            title="Setel / Reset Password">
                                        @include('partials.icon', ['name' => 'key', 'class' => 'w-4 h-4'])
                                    </button>

                                    {{-- Tombol Hapus User --}}
                                    @if ($isSelf)
                                        <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Anda tidak dapat menghapus akun Anda sendiri">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                        </span>
                                    @else
                                        <button type="button"
                                                onclick='confirmDeleteUser(@json($u))'
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="Hapus Pengguna">
                                            @include('partials.icon', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada data pengguna yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: TAMBAH USER BARU --}}
{{-- ========================================================================= --}}
<div id="createUserModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink">Tambah Pengguna Baru</h3>
                    <p class="text-[11px] text-slate-400">Buat akun login baru dan tentukan password serta hak akses langsung.</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateUserModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-5 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" required placeholder="misal: ahmad_it"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" required placeholder="misal: Ahmad Fauzi, S.Kom"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email (Opsional)</label>
                    <input type="email" name="email" placeholder="ahmad@banksulteng.co.id"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Role / Peran <span class="text-rose-500">*</span></label>
                    <select name="role_id" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none bg-white">
                        <option value="">Pilih Role...</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->label }} ({{ $r->nama }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Jabatan</label>
                    <input type="text" name="jabatan" placeholder="misal: Staf IT / Operator"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Bagian / Divisi</label>
                    <input type="text" name="bagian" placeholder="misal: Divisi Umum & Logistik"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11.5px] font-bold text-slate-700 uppercase tracking-wider">
                        Password Akun
                    </label>
                    <button type="button" onclick="generateCreatePassword()" class="text-[11px] font-bold text-[#114E84] hover:underline">
                        Generate Acak
                    </button>
                </div>
                <div class="relative">
                    <input type="password" id="createPasswordInput" name="password" minlength="6"
                           placeholder="Kosongkan jika ingin password acak otomatis (min. 6 karakter)"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 pr-10 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                    <button type="button" onclick="togglePasswordVisibility('createPasswordInput')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        @include('partials.icon', ['name' => 'key', 'class' => 'w-4 h-4'])
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Jika Anda mengisi password manual (misal: <code>12345678</code>), pengguna bisa langsung login dengan password tersebut.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                    <span>Akun Aktif (Bisa Login)</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" name="must_change_pwd" value="1" class="rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                    <span>Wajib ganti password pada login pertama</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#114E84] hover:bg-[#0E4272] text-white transition shadow-sm">
                    Simpan User Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: EDIT USER LENGKAP --}}
{{-- ========================================================================= --}}
<div id="editUserModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold">
                    @include('partials.icon', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink">Ubah Data Pengguna</h3>
                    <p class="text-[11px] text-slate-400" id="editModalSubtitle">Perbarui informasi profil, role, atau ubah password.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditUserModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <form id="editUserForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Username <span class="text-rose-500">*</span></label>
                    <input type="text" id="editUsername" name="username" required
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="editNamaLengkap" name="nama_lengkap" required
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" id="editEmail" name="email"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Role / Peran <span class="text-rose-500">*</span></label>
                    <select id="editRoleId" name="role_id" required class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none bg-white">
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->label }} ({{ $r->nama }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Jabatan</label>
                    <input type="text" id="editJabatan" name="jabatan"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>

                <div>
                    <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Bagian / Divisi</label>
                    <input type="text" id="editBagian" name="bagian"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11.5px] font-bold text-slate-700 uppercase tracking-wider">
                        Ubah Password (Opsional)
                    </label>
                    <span class="text-[11px] text-slate-400">Kosongkan jika tidak ingin mengubah</span>
                </div>
                <div class="relative">
                    <input type="password" id="editPasswordInput" name="password" minlength="6"
                           placeholder="Ketik password baru jika ingin diganti langsung..."
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 pr-10 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
                    <button type="button" onclick="togglePasswordVisibility('editPasswordInput')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        @include('partials.icon', ['name' => 'key', 'class' => 'w-4 h-4'])
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" id="editIsActive" name="is_active" value="1" class="rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                    <span class="font-semibold">Akun Aktif (Dapat Login)</span>
                </label>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" id="editMustChangePwd" name="must_change_pwd" value="1" class="rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                    <span>Wajib ganti password saat login berikutnya</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#114E84] hover:bg-[#0E4272] text-white transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: GANTI / RESET PASSWORD CEPAT --}}
{{-- ========================================================================= --}}
<div id="passwordModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 font-bold">
                    @include('partials.icon', ['name' => 'key', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink">Setel / Reset Password</h3>
                    <p class="text-[11px] text-slate-400" id="pwdModalSubtitle">Tentukan password baru untuk akun pengguna.</p>
                </div>
            </div>
            <button type="button" onclick="closePasswordModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <form id="passwordForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[11.5px] font-bold text-slate-700 uppercase tracking-wider">
                        Password Baru
                    </label>
                    <button type="button" onclick="generateResetPassword()" class="text-[11px] font-bold text-[#114E84] hover:underline">
                        Generate Acak
                    </button>
                </div>
                <div class="relative">
                    <input type="text" id="resetPasswordInput" name="new_password" minlength="6"
                           placeholder="Ketik password baru (misal: 12345678)"
                           class="w-full text-xs rounded-xl px-3.5 py-2.5 pr-10 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none font-mono">
                    <button type="button" onclick="copyPasswordValue('resetPasswordInput')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Salin Password">
                        @include('partials.icon', ['name' => 'file-text', 'class' => 'w-4 h-4'])
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin sistem membuat password acak 10 karakter secara otomatis.</p>
            </div>

            <div>
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" name="must_change_pwd" value="1" class="rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                    <span>Wajib ganti password saat user login berikutnya</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePasswordModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#114E84] hover:bg-[#0E4272] text-white transition shadow-sm">
                    Simpan Password Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 4: KONFIRMASI HAPUS USER --}}
{{-- ========================================================================= --}}
<div id="deleteUserModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                @include('partials.icon', ['name' => 'trash', 'class' => 'w-6 h-6'])
            </div>
            <h3 class="text-base font-bold text-ink">Hapus Akun Pengguna?</h3>
            <p class="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menghapus akun <span class="font-bold text-ink" id="deleteTargetName"></span> (<span class="font-mono text-slate-600" id="deleteTargetUsername"></span>)?
            </p>
            <p class="text-[11px] text-rose-500 font-medium mt-2 bg-rose-50 p-2 rounded-xl border border-rose-100">
                Tindakan ini tidak dapat dibatalkan. Riwayat audit log aktivitas yang pernah dilakukan tetap tersimpan.
            </p>
        </div>

        <form id="deleteUserForm" method="POST" action="" class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteUserModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/60 transition">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm">
                Ya, Hapus Pengguna
            </button>
        </form>
    </div>
</div>

<script>
    // Live Filter Table
    const searchInput = document.getElementById('userSearchInput');
    const roleFilter = document.getElementById('userRoleFilter');
    const statusFilter = document.getElementById('userStatusFilter');
    const rows = document.querySelectorAll('.user-row');

    function filterUsers() {
        const query = (searchInput.value || '').toLowerCase().trim();
        const roleId = roleFilter.value;
        const statusVal = statusFilter.value;

        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const username = row.getAttribute('data-username') || '';
            const email = row.getAttribute('data-email') || '';
            const jabatan = row.getAttribute('data-jabatan') || '';
            const rowRole = row.getAttribute('data-role-id') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchQuery = !query || name.includes(query) || username.includes(query) || email.includes(query) || jabatan.includes(query);
            const matchRole = !roleId || rowRole === roleId;
            const matchStatus = statusVal === '' || rowStatus === statusVal;

            if (matchQuery && matchRole && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput?.addEventListener('input', filterUsers);
    roleFilter?.addEventListener('change', filterUsers);
    statusFilter?.addEventListener('change', filterUsers);

    // Helper Generator Password Acak
    function getRandomPassword(len = 10) {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
        let res = '';
        for (let i = 0; i < len; i++) {
            res += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return res;
    }

    function togglePasswordVisibility(inputId) {
        const el = document.getElementById(inputId);
        if (el) {
            el.type = el.type === 'password' ? 'text' : 'password';
        }
    }

    function generateCreatePassword() {
        const el = document.getElementById('createPasswordInput');
        if (el) {
            el.type = 'text';
            el.value = getRandomPassword(10);
        }
    }

    function generateResetPassword() {
        const el = document.getElementById('resetPasswordInput');
        if (el) {
            el.value = getRandomPassword(10);
        }
    }

    function copyPasswordValue(inputId) {
        const el = document.getElementById(inputId);
        if (el && el.value) {
            navigator.clipboard.writeText(el.value);
            alert('Password berhasil disalin ke clipboard: ' + el.value);
        }
    }

    // Modal Create
    function openCreateUserModal() {
        const modal = document.getElementById('createUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeCreateUserModal() {
        const modal = document.getElementById('createUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Edit
    function openEditUserModal(user) {
        document.getElementById('editModalSubtitle').innerText = 'Mengubah data pengguna: ' + user.nama_lengkap + ' (@' + user.username + ')';
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;

        document.getElementById('editUsername').value = user.username || '';
        document.getElementById('editNamaLengkap').value = user.nama_lengkap || '';
        document.getElementById('editEmail').value = user.email || '';
        document.getElementById('editJabatan').value = user.jabatan || '';
        document.getElementById('editBagian').value = user.bagian || '';
        document.getElementById('editRoleId').value = user.role_id || '';
        document.getElementById('editIsActive').checked = Boolean(user.is_active);
        document.getElementById('editMustChangePwd').checked = Boolean(user.must_change_pwd);
        document.getElementById('editPasswordInput').value = '';

        const modal = document.getElementById('editUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeEditUserModal() {
        const modal = document.getElementById('editUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Password
    function openPasswordModal(user) {
        document.getElementById('pwdModalSubtitle').innerText = 'Akun: ' + user.nama_lengkap + ' (@' + user.username + ')';
        document.getElementById('passwordForm').action = '/admin/users/' + user.id + '/reset-password';
        document.getElementById('resetPasswordInput').value = '';

        const modal = document.getElementById('passwordModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closePasswordModal() {
        const modal = document.getElementById('passwordModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Delete
    function confirmDeleteUser(user) {
        document.getElementById('deleteTargetName').innerText = user.nama_lengkap;
        document.getElementById('deleteTargetUsername').innerText = '@' + user.username;
        document.getElementById('deleteUserForm').action = '/admin/users/' + user.id;

        const modal = document.getElementById('deleteUserModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeDeleteUserModal() {
        const modal = document.getElementById('deleteUserModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateUserModal();
            closeEditUserModal();
            closePasswordModal();
            closeDeleteUserModal();
        }
    });
</script>
@endsection
