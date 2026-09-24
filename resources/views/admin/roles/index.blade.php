@extends('layouts.app')
@section('title', 'Manajemen Role & Hak Akses')

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
                <span class="text-xs text-slate-500 font-medium">Sub-Modul Role &amp; Permission</span>
            </div>
            <h1 class="text-2xl font-black text-ink tracking-tight mt-1">Manajemen Role &amp; Hak Akses</h1>
            <p class="text-xs text-slate-500 mt-0.5">Atur peran pengguna, tambah role kustom baru, dan kustomisasi matriks hak akses per modul secara dinamis.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition shadow-2xs">
                @include('partials.icon', ['name' => 'users', 'class' => 'w-4 h-4 text-slate-500'])
                <span>Kelola Data User</span>
            </a>
            <button type="button" onclick="openCreateRoleModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold transition shadow-sm hover:shadow">
                @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                <span>+ Tambah Role Baru</span>
            </button>
        </div>
    </div>

    {{-- Statistik Cepat Role --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'shield', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Role</p>
                <p class="text-2xl font-black text-ink leading-tight">{{ $roles->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'lock', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Role Sistem Bawaan</p>
                <p class="text-2xl font-black text-purple-700 leading-tight">2</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'layers', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Role Kustom / Maker</p>
                <p class="text-2xl font-black text-emerald-600 leading-tight">{{ max(0, $roles->count() - 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                @include('partials.icon', ['name' => 'file-text', 'class' => 'w-5 h-5'])
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Modul Terdaftar</p>
                <p class="text-2xl font-black text-ink leading-tight">{{ count($permKeys) }} Modul</p>
            </div>
        </div>
    </div>

    {{-- Daftar Role & Pengaturan Hak Akses --}}
    <div class="space-y-6">
        @foreach ($roles as $role)
            @php
                $isSystem = in_array($role->nama, ['superadmin', 'pimpinan']) || $role->id <= 2;
                $userCount = $role->users->count();
                $permCollection = $role->permissions->keyBy('perm_key');
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden transition hover:border-slate-300">
                {{-- Role Header Card --}}
                <div class="p-5 bg-gradient-to-r from-slate-50/80 to-white border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $isSystem ? 'bg-purple-100 text-purple-700' : 'bg-[#114E84]/10 text-[#114E84]' }} flex items-center justify-center flex-shrink-0 font-bold mt-0.5">
                            @include('partials.icon', ['name' => $isSystem ? 'lock' : 'shield', 'class' => 'w-5 h-5'])
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base font-extrabold text-ink">{{ $role->label }}</h2>
                                <span class="px-2 py-0.5 rounded-md font-mono text-[11px] bg-slate-100 text-slate-600 font-semibold">
                                    {{ $role->nama }}
                                </span>
                                @if ($isSystem)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                                        Role Sistem
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kustom
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-[#114E84]">
                                    {{ $userCount }} Pengguna Terdaftar
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $role->deskripsi ?: 'Tidak ada deskripsi wewenang tambahan.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Aksi Info Role --}}
                    <div class="flex items-center gap-2 self-end md:self-center">
                        <button type="button"
                                onclick='openEditRoleModal(@json($role), {{ $isSystem ? "true" : "false" }})'
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition">
                            @include('partials.icon', ['name' => 'pencil', 'class' => 'w-3.5 h-3.5 text-slate-500'])
                            <span>Ubah Info</span>
                        </button>

                        @if (!$isSystem && $userCount === 0)
                            <button type="button"
                                    onclick='confirmDeleteRole(@json($role))'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-rose-200 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                @include('partials.icon', ['name' => 'trash', 'class' => 'w-3.5 h-3.5'])
                                <span>Hapus Role</span>
                            </button>
                        @elseif (!$isSystem && $userCount > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-300 cursor-not-allowed"
                                  title="Role ini masih digunakan oleh {{ $userCount }} pengguna. Pindahkan pengguna terlebih dahulu sebelum menghapus.">
                                @include('partials.icon', ['name' => 'trash', 'class' => 'w-3.5 h-3.5'])
                                <span>Hapus Role</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Matriks Hak Akses Form --}}
                <form method="POST" action="{{ route('admin.roles.permissions', $role) }}" class="p-5" id="roleForm-{{ $role->id }}">
                    @csrf
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Konfigurasi Hak Akses Modul</h3>
                            <p class="text-[11.5px] text-slate-500">Pilih modul yang dapat dibuka (Lihat) dan hak untuk membuat/mengubah transaksi (Maker/Tulis).</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="selectAllRolePerms({{ $role->id }})" class="text-[11px] font-semibold text-[#114E84] hover:bg-blue-50 px-2 py-1 rounded-lg transition">
                                Pilih Semua
                            </button>
                            <span class="text-slate-300">•</span>
                            <button type="button" onclick="selectReadRolePerms({{ $role->id }})" class="text-[11px] font-semibold text-slate-600 hover:bg-slate-100 px-2 py-1 rounded-lg transition">
                                Lihat Saja
                            </button>
                            <span class="text-slate-300">•</span>
                            <button type="button" onclick="clearAllRolePerms({{ $role->id }})" class="text-[11px] font-semibold text-rose-600 hover:bg-rose-50 px-2 py-1 rounded-lg transition">
                                Hapus Semua
                            </button>
                        </div>
                    </div>

                    {{-- Grouped Permissions --}}
                    <div class="space-y-4 mb-5">
                        @foreach ($moduleGroups as $groupTitle => $groupItems)
                            <div>
                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#114E84]"></span>
                                    {{ $groupTitle }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                    @foreach ($groupItems as $key => $meta)
                                        @php
                                            $perm = $permCollection->get($key);
                                            $hasAccess = (bool) $perm;
                                            $canWrite = (bool) optional($perm)->can_write;
                                        @endphp
                                        <div class="p-3 rounded-xl border border-slate-200/80 bg-slate-50/40 hover:bg-white hover:border-slate-300 transition">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-600 flex-shrink-0">
                                                        @include('partials.icon', ['name' => $meta['icon'], 'class' => 'w-3.5 h-3.5'])
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-ink truncate">{{ $meta['label'] }}</p>
                                                        <p class="text-[10.5px] text-slate-400 truncate max-w-xs">{{ $meta['desc'] }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-4 mt-2.5 pt-2 border-t border-slate-200/60">
                                                {{-- Checkbox Akses Baca / Buka Modul --}}
                                                <label class="flex items-center gap-1.5 text-[11.5px] font-medium text-slate-700 cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           id="acc_{{ $role->id }}_{{ $key }}"
                                                           name="access_{{ $key }}"
                                                           value="1"
                                                           @checked($hasAccess)
                                                           class="perm-access-cb rounded border-slate-300 text-[#114E84] focus:ring-[#114E84]">
                                                    <span>Buka Modul</span>
                                                </label>

                                                {{-- Checkbox Akses Tulis / Maker --}}
                                                <label class="flex items-center gap-1.5 text-[11.5px] font-medium text-slate-700 cursor-pointer select-none">
                                                    <input type="checkbox"
                                                           id="write_{{ $role->id }}_{{ $key }}"
                                                           name="write_{{ $key }}"
                                                           value="1"
                                                           @checked($canWrite)
                                                           onchange="handleWriteChange(this, 'acc_{{ $role->id }}_{{ $key }}')"
                                                           class="perm-write-cb rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                                    <span>Tulis / Maker</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tombol Simpan Hak Akses --}}
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400">Perubahan hak akses langsung aktif setelah disimpan ke database.</span>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#114E84] hover:bg-[#0E4272] text-white text-xs font-bold transition shadow-sm hover:shadow">
                            @include('partials.icon', ['name' => 'check-circle', 'class' => 'w-4 h-4'])
                            <span>Simpan Hak Akses Role {{ $role->label }}</span>
                        </button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL TAMBAH ROLE BARU --}}
{{-- ========================================================================= --}}
<div id="createRoleModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#114E84]/10 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold">
                    @include('partials.icon', ['name' => 'plus', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink">Tambah Role Baru</h3>
                    <p class="text-[11px] text-slate-400">Buat peran pengguna baru untuk disematkan pada akun pegawai.</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateRoleModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Identifier Role (Slug) <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" required placeholder="misal: staf_auditor" pattern="[a-z0-9_]+"
                       class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Hanya huruf kecil, angka, dan garis bawah (_). Digunakan untuk identifikasi internal sistem.</p>
            </div>

            <div>
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Resmi Role (Label) <span class="text-rose-500">*</span></label>
                <input type="text" name="label" required placeholder="misal: Staf Auditor Internal"
                       class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
            </div>

            <div>
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi &amp; Wewenang</label>
                <textarea name="deskripsi" rows="2.5" placeholder="Penjelasan tugas atau ruang lingkup kewenangan role..."
                          class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCreateRoleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#114E84] hover:bg-[#0E4272] text-white transition shadow-sm">
                    Buat Role Baru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL EDIT INFO ROLE --}}
{{-- ========================================================================= --}}
<div id="editRoleModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#114E84] flex items-center justify-center flex-shrink-0 font-bold">
                    @include('partials.icon', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink">Ubah Informasi Role</h3>
                    <p class="text-[11px] text-slate-400" id="editRoleSubtitle">Perbarui nama resmi atau deskripsi peran.</p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                @include('partials.icon', ['name' => 'x-circle', 'class' => 'w-5 h-5'])
            </button>
        </div>

        <form id="editRoleForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div id="editRoleNamaGroup">
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Identifier Role (Slug) <span class="text-rose-500">*</span></label>
                <input type="text" id="editRoleNama" name="nama" pattern="[a-z0-9_]+"
                       class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Hanya huruf kecil, angka, dan garis bawah (_).</p>
            </div>

            <div>
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Resmi Role (Label) <span class="text-rose-500">*</span></label>
                <input type="text" id="editRoleLabel" name="label" required
                       class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none">
            </div>

            <div>
                <label class="block text-[11.5px] font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi &amp; Wewenang</label>
                <textarea id="editRoleDeskripsi" name="deskripsi" rows="2.5"
                          class="w-full text-xs rounded-xl px-3.5 py-2.5 border border-slate-200 focus:border-[#114E84] focus:ring-1 focus:ring-[#114E84] outline-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditRoleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#114E84] hover:bg-[#0E4272] text-white transition shadow-sm">
                    Simpan Perubahan Role
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL KONFIRMASI HAPUS ROLE --}}
{{-- ========================================================================= --}}
<div id="deleteRoleModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl overflow-hidden animate-enter">
        <div class="p-5 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                @include('partials.icon', ['name' => 'trash', 'class' => 'w-6 h-6'])
            </div>
            <h3 class="text-base font-bold text-ink">Hapus Role?</h3>
            <p class="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menghapus role <span class="font-bold text-ink" id="deleteRoleTargetLabel"></span> (<span class="font-mono text-slate-600" id="deleteRoleTargetNama"></span>)?
            </p>
            <p class="text-[11px] text-rose-500 font-medium mt-2 bg-rose-50 p-2 rounded-xl border border-rose-100">
                Seluruh konfigurasi hak akses untuk role ini akan dihapus dari sistem.
            </p>
        </div>

        <form id="deleteRoleForm" method="POST" action="" class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteRoleModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200/60 transition">
                Batal
            </button>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm">
                Ya, Hapus Role
            </button>
        </form>
    </div>
</div>

<script>
    // Penanganan otomatis saat tombol write dicentang
    function handleWriteChange(writeCb, accessCbId) {
        const accessCb = document.getElementById(accessCbId);
        if (writeCb.checked && accessCb) {
            accessCb.checked = true;
        }
    }

    // Helper Action Matriks Hak Akses
    function selectAllRolePerms(roleId) {
        const form = document.getElementById('roleForm-' + roleId);
        if (!form) return;
        form.querySelectorAll('.perm-access-cb').forEach(cb => cb.checked = true);
        form.querySelectorAll('.perm-write-cb').forEach(cb => cb.checked = true);
    }

    function selectReadRolePerms(roleId) {
        const form = document.getElementById('roleForm-' + roleId);
        if (!form) return;
        form.querySelectorAll('.perm-access-cb').forEach(cb => cb.checked = true);
        form.querySelectorAll('.perm-write-cb').forEach(cb => cb.checked = false);
    }

    function clearAllRolePerms(roleId) {
        const form = document.getElementById('roleForm-' + roleId);
        if (!form) return;
        form.querySelectorAll('.perm-access-cb').forEach(cb => cb.checked = false);
        form.querySelectorAll('.perm-write-cb').forEach(cb => cb.checked = false);
    }

    // Modal Create Role
    function openCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeCreateRoleModal() {
        const modal = document.getElementById('createRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Edit Role
    function openEditRoleModal(role, isSystem) {
        document.getElementById('editRoleSubtitle').innerText = 'Mengubah data role: ' + role.label + ' (' + role.nama + ')';
        document.getElementById('editRoleForm').action = '/admin/roles/' + role.id;

        document.getElementById('editRoleLabel').value = role.label || '';
        document.getElementById('editRoleDeskripsi').value = role.deskripsi || '';

        const namaGroup = document.getElementById('editRoleNamaGroup');
        const namaInput = document.getElementById('editRoleNama');
        if (isSystem) {
            namaGroup.style.display = 'none';
            namaInput.removeAttribute('required');
        } else {
            namaGroup.style.display = 'block';
            namaInput.value = role.nama || '';
            namaInput.setAttribute('required', 'required');
        }

        const modal = document.getElementById('editRoleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeEditRoleModal() {
        const modal = document.getElementById('editRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Delete Role
    function confirmDeleteRole(role) {
        document.getElementById('deleteRoleTargetLabel').innerText = role.label;
        document.getElementById('deleteRoleTargetNama').innerText = role.nama;
        document.getElementById('deleteRoleForm').action = '/admin/roles/' + role.id;

        const modal = document.getElementById('deleteRoleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeDeleteRoleModal() {
        const modal = document.getElementById('deleteRoleModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Escape key listener
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateRoleModal();
            closeEditRoleModal();
            closeDeleteRoleModal();
        }
    });
</script>
@endsection
