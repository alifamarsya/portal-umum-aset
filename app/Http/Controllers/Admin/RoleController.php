<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use LogsAudit;

    const MODULE_GROUPS = [
        'Pengajuan & Monitoring' => [
            'dashboard' => [
                'label' => 'Dashboard Monitoring',
                'desc' => 'Ringkasan eksekutif, widget ringkasan status operasional dan statistik',
                'icon' => 'home'
            ],
            'tiket' => [
                'label' => 'Sistem Tiket Pengajuan',
                'desc' => 'Layanan tiket pengajuan operasional, alokasi/disposisi, verifikasi, dan pengerjaan',
                'icon' => 'inbox'
            ],
        ],
        'Operasional' => [
            'umum_rt' => [
                'label' => 'Umum & Rumah Tangga',
                'desc' => 'Kendaraan, BBM, perawatan RT, dan permintaan cabang ATK/inventaris',
                'icon' => 'building'
            ],
            'aset_logistik' => [
                'label' => 'Aset & Logistik',
                'desc' => 'Invoice sewa, aset & inventaris, amortisasi, PKS jatuh tempo, memo sewa cabang, temuan aset',
                'icon' => 'layers'
            ],
            'pengadaan' => [
                'label' => 'Pengadaan & Pemeliharaan',
                'desc' => 'Memo internal, penawaran vendor, negosiasi harga, draft SPK, SPK, reminder jatuh tempo',
                'icon' => 'cart'
            ],
            'risalah' => [
                'label' => 'Arsip Surat & Memo',
                'desc' => 'Arsip surat masuk/keluar, memo masuk/keluar, dan risalah rapat',
                'icon' => 'archive'
            ],
        ],
        'Referensi & SOP' => [
            'ref_akun' => [
                'label' => 'Referensi Akun & Sandi',
                'desc' => 'Master data kode akun anggaran (COA) dan referensi transaksi',
                'icon' => 'file-text'
            ],
        ],
        'Administrasi' => [
            'user_mgmt' => [
                'label' => 'Manajemen User',
                'desc' => 'Pengelolaan akun pengguna, password, dan status aktif',
                'icon' => 'users'
            ],
            'role_mgmt' => [
                'label' => 'Manajemen Role',
                'desc' => 'Konfigurasi role pengguna dan matriks hak akses modul',
                'icon' => 'shield'
            ],
            'audit_log' => [
                'label' => 'Audit Log Trail',
                'desc' => 'Audit trail tak terputus, rantai integritas SHA-256',
                'icon' => 'lock'
            ],
        ],
    ];

    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('id')->get();
        $moduleGroups = self::MODULE_GROUPS;

        $permKeys = [];
        foreach ($moduleGroups as $group) {
            foreach (array_keys($group) as $k) {
                $permKeys[] = $k;
            }
        }

        return view('admin.roles.index', compact('roles', 'moduleGroups', 'permKeys'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:50|regex:/^[a-z0-9_]+$/|unique:roles,nama',
            'label' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ], [
            'nama.regex' => 'Kode role hanya boleh huruf kecil, angka, dan garis bawah (_), tanpa spasi.',
            'nama.unique' => 'Kode role sudah digunakan.',
        ]);

        $role = Role::create($data);

        // Pasang izin awal jika ada dicentang
        foreach ($request->input('perms', []) as $key => $permVal) {
            $hasAccess = !empty($permVal['access']) || !empty($permVal['write']);
            if ($hasAccess) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'perm_key' => $key],
                    ['can_write' => !empty($permVal['write'])]
                );
            }
        }

        $this->audit('CREATE', 'Manajemen Role', 'Role', $role->id, "Menambah role baru {$role->label} ({$role->nama})");

        return back()->with('status', "Role baru '{$role->label}' berhasil ditambahkan ke sistem.");
    }

    public function update(Request $request, Role $role)
    {
        $rules = [
            'label' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
        ];

        $isSystemRole = in_array($role->nama, ['admin', 'superadmin', 'pimpinan']) || $role->id <= 2;
        if (!$isSystemRole) {
            $rules['nama'] = 'required|string|max:50|regex:/^[a-z0-9_]+$/|unique:roles,nama,' . $role->id;
        }

        $data = $request->validate($rules, [
            'nama.regex' => 'Kode role hanya boleh huruf kecil, angka, dan garis bawah (_), tanpa spasi.',
        ]);

        $role->update($data);
        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah informasi role {$role->nama}");

        return back()->with('status', "Informasi role '{$role->label}' berhasil diperbarui.");
    }

    public function destroy(Role $role)
    {
        // Proteksi 1: Role sistem bawaan tidak boleh dihapus
        if (in_array($role->nama, ['admin', 'superadmin', 'pimpinan']) || $role->id <= 2) {
            return back()->with('error', "Role sistem bawaan '{$role->label}' dilindungi dan tidak dapat dihapus.");
        }

        // Proteksi 2: Role yang masih memiliki user tidak boleh dihapus
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return back()->with('error', "Role '{$role->label}' tidak dapat dihapus karena masih digunakan oleh {$userCount} pengguna. Harap pindahkan user terlebih dahulu.");
        }

        $id = $role->id;
        $label = $role->label;

        // Hapus izin terkait terlebih dahulu
        RolePermission::where('role_id', $id)->delete();
        $role->delete();

        $this->audit('DELETE', 'Manajemen Role', 'Role', $id, "Menghapus role {$label}");

        return back()->with('status', "Role '{$label}' berhasil dihapus dari sistem.");
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $permKeys = [];
        foreach (self::MODULE_GROUPS as $group) {
            foreach (array_keys($group) as $k) {
                $permKeys[] = $k;
            }
        }

        foreach ($permKeys as $key) {
            $hasAccess = $request->boolean("access_{$key}") || $request->boolean("write_{$key}");
            $canWrite = $request->boolean("write_{$key}");

            if ($hasAccess) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'perm_key' => $key],
                    ['can_write' => $canWrite]
                );
            } else {
                // Jika tidak diberi akses sama sekali, hapus dari tabel role_permissions
                DB::table('role_permissions')
                    ->where('role_id', $role->id)
                    ->where('perm_key', $key)
                    ->delete();
            }
        }

        $this->audit('UPDATE', 'Manajemen Role', 'Role', $role->id, "Mengubah konfigurasi hak akses modul role {$role->nama}");

        return back()->with('status', "Hak akses modul untuk role '{$role->label}' berhasil diperbarui.");
    }
}
