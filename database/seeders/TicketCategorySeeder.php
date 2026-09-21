<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Umum & RT (dept_id=1)
            ['nama' => 'Pengadaan ATK & Perlengkapan Kantor',   'deskripsi' => 'Permintaan alat tulis kantor, kertas, tinta, dan perlengkapan umum lainnya',    'department_id' => 1],
            ['nama' => 'Pemeliharaan Gedung & Fasilitas',        'deskripsi' => 'Perbaikan dan perawatan gedung, fasilitas kantor, dan sarana prasarana',         'department_id' => 1],
            ['nama' => 'Permintaan Kendaraan Dinas',             'deskripsi' => 'Penggunaan kendaraan operasional untuk keperluan dinas',                          'department_id' => 1],

            // Aset & Logistik (dept_id=2)
            ['nama' => 'Permintaan & Pemindahan Aset',           'deskripsi' => 'Pengajuan kebutuhan aset baru atau pemindahan aset antar unit',                  'department_id' => 2],
            ['nama' => 'Penghapusan & Penilaian Aset',           'deskripsi' => 'Proses penghapusan atau revaluasi aset yang sudah habis masa manfaatnya',        'department_id' => 2],

            // Pengadaan (dept_id=3)
            ['nama' => 'Pengadaan Barang / Jasa',                'deskripsi' => 'Permintaan pengadaan barang atau jasa baru melalui proses tender/penunjukan langsung', 'department_id' => 3],
            ['nama' => 'Pemeliharaan & Perbaikan Aset IT',       'deskripsi' => 'Permintaan servis, upgrade, atau penggantian perangkat IT dan infrastruktur',    'department_id' => 3],

            // Umum (lintas bagian)
            ['nama' => 'Lainnya',                                'deskripsi' => 'Permintaan layanan lain yang tidak termasuk kategori di atas',                   'department_id' => null],
        ];

        foreach ($categories as $cat) {
            DB::table('ticket_categories')->insertOrIgnore(array_merge($cat, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}