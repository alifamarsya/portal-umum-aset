<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    // Struktur role baru v2.0 — mendukung sistem tiket multi-bagian.
    // ID sengaja tidak berurutan agar mudah dikelompokkan:
    //   1-2   = manajemen (admin, pimpinan)
    //   6-7   = end-user (user/pemohon, operator)
    //   10-12 = kepala bagian
    //   13-15 = staf bagian
    public function run(): void
    {
        $roles = [
            ['id' => 1,  'nama' => 'admin',           'label' => 'Admin',                              'deskripsi' => 'Manajemen user, role, audit log, dan supervisi tiket'],
            ['id' => 2,  'nama' => 'pimpinan',         'label' => 'Pimpinan Divisi',                   'deskripsi' => 'Monitoring seluruh tiket dan laporan'],
            ['id' => 6,  'nama' => 'user',             'label' => 'User / Pemohon',                    'deskripsi' => 'Mengajukan tiket layanan (dapat banyak akun)'],
            ['id' => 7,  'nama' => 'operator',         'label' => 'Operator / Helpdesk',               'deskripsi' => 'Menerima, mereview, dan mengalokasikan tiket ke bagian'],
            ['id' => 10, 'nama' => 'kabag_umum',       'label' => 'Kepala Bagian Umum & Rumah Tangga', 'deskripsi' => 'Memberi keputusan tiket di Bagian Umum & RT'],
            ['id' => 11, 'nama' => 'kabag_aset',       'label' => 'Kepala Bagian Aset & Logistik',     'deskripsi' => 'Memberi keputusan tiket di Bagian Aset & Logistik'],
            ['id' => 12, 'nama' => 'kabag_pengadaan',  'label' => 'Kepala Bagian Pengadaan',           'deskripsi' => 'Memberi keputusan tiket di Bagian Pengadaan'],
            ['id' => 13, 'nama' => 'staf_umum',        'label' => 'Staf Bagian Umum & Rumah Tangga',   'deskripsi' => 'Mengerjakan tiket yang dialokasikan ke Bagian Umum & RT'],
            ['id' => 14, 'nama' => 'staf_aset',        'label' => 'Staf Bagian Aset & Logistik',       'deskripsi' => 'Mengerjakan tiket yang dialokasikan ke Bagian Aset & Logistik'],
            ['id' => 15, 'nama' => 'staf_pengadaan',   'label' => 'Staf Bagian Pengadaan',             'deskripsi' => 'Mengerjakan tiket yang dialokasikan ke Bagian Pengadaan'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['id' => $r['id']], $r);
        }

        // Hapus role lama yang tidak terpakai (umum_rt=3, aset=4, pengadaan=5)
        Role::whereIn('id', [3, 4, 5])->delete();

        // Matriks permission:
        // [role_id, perm_key, can_write]
        $perms = [
            // Admin: akses penuh ke manajemen
            [1, 'dashboard',     1], [1, 'tiket',      1], [1, 'user_mgmt',  1],
            [1, 'role_mgmt',     1], [1, 'audit_log',  1], [1, 'analytics_dw', 0],

            // Pimpinan: monitoring semua, tidak bisa write
            [2, 'dashboard',     0], [2, 'tiket',      0], [2, 'analytics_dw', 0],
            [2, 'audit_log',     0],

            // User / Pemohon: hanya buat dan lihat tiket sendiri
            [6, 'dashboard',     0], [6, 'tiket',      1],

            // Operator: alokasikan tiket
            [7, 'dashboard',     1], [7, 'tiket',      1],

            // Kabag: approve/reject tiket di bagiannya
            [10, 'dashboard',    1], [10, 'tiket',     1],
            [11, 'dashboard',    1], [11, 'tiket',     1],
            [12, 'dashboard',    1], [12, 'tiket',     1],

            // Staf: kerjakan tiket di bagiannya
            [13, 'dashboard',    1], [13, 'tiket',     1],
            [14, 'dashboard',    1], [14, 'tiket',     1],
            [15, 'dashboard',    1], [15, 'tiket',     1],
        ];

        foreach ($perms as [$roleId, $key, $write]) {
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'perm_key' => $key],
                ['can_write' => $write]
            );
        }
    }
}
