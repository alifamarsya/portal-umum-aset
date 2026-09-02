<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    // Data role & matriks permission dipindahkan 1:1 dari portum.py (fungsi init_db),
    // supaya perilaku hak akses tidak berubah saat cutover ke Laravel.
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nama' => 'superadmin', 'label' => 'Super Administrator', 'deskripsi' => 'Akses pengaturan sistem & lihat semua modul (read-only)'],
            ['id' => 2, 'nama' => 'pimpinan', 'label' => 'Pimpinan Divisi', 'deskripsi' => 'Akses checker (approval) & monitoring seluruh modul'],
            ['id' => 3, 'nama' => 'umum_rt', 'label' => 'Staf Umum & Rumah Tangga', 'deskripsi' => 'Maker Bagian Umum & Rumah Tangga'],
            ['id' => 4, 'nama' => 'aset', 'label' => 'Staf Aset & Logistik', 'deskripsi' => 'Maker Bagian Aset/Inventaris & Logistik'],
            ['id' => 5, 'nama' => 'pengadaan', 'label' => 'Staf Pengadaan', 'deskripsi' => 'Maker Bagian Pengadaan & Pemeliharaan Aset'],
        ];
        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        $perms = [
            [1, 'dashboard', 1], [1, 'umum_rt', 0], [1, 'aset_logistik', 0], [1, 'pengadaan', 0], [1, 'risalah', 0],
            [1, 'panduan', 0], [1, 'analytics_dw', 0], [1, 'user_mgmt', 1], [1, 'role_mgmt', 1], [1, 'audit_log', 1], [1, 'ref_akun', 0],
            [2, 'dashboard', 0], [2, 'analytics_dw', 0], [2, 'umum_rt', 0], [2, 'aset_logistik', 0], [2, 'pengadaan', 0], [2, 'risalah', 0],
            [2, 'panduan', 0], [2, 'audit_log', 0], [2, 'ref_akun', 0],
            [3, 'dashboard', 1], [3, 'umum_rt', 1], [3, 'risalah', 1], [3, 'panduan', 0], [3, 'ref_akun', 0],
            [4, 'dashboard', 1], [4, 'aset_logistik', 1], [4, 'risalah', 1], [4, 'panduan', 0], [4, 'ref_akun', 0],
            [5, 'dashboard', 1], [5, 'pengadaan', 1], [5, 'risalah', 1], [5, 'panduan', 0], [5, 'ref_akun', 0],
        ];
        foreach ($perms as [$roleId, $key, $write]) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'perm_key' => $key],
                ['can_write' => $write]
            );
        }
    }
}
