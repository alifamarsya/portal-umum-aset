<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternalDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['id' => 1, 'nama' => 'Umum & Rumah Tangga',   'slug' => 'umum',      'deskripsi' => 'Bagian Umum dan Rumah Tangga — mengelola kebutuhan operasional kantor, kendaraan, dan ATK'],
            ['id' => 2, 'nama' => 'Aset & Logistik',        'slug' => 'aset',      'deskripsi' => 'Bagian Aset dan Logistik — mengelola inventaris, PKS, invoice sewa, dan aset tetap'],
            ['id' => 3, 'nama' => 'Pengadaan',              'slug' => 'pengadaan', 'deskripsi' => 'Bagian Pengadaan — mengelola proses pengadaan barang/jasa dan pemeliharaan aset'],
        ];

        foreach ($departments as $d) {
            DB::table('internal_departments')->updateOrInsert(
                ['id' => $d['id']],
                array_merge($d, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}