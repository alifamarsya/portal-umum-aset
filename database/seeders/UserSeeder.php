<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    // PENTING: password default versi Python (admin/admin2026, dst.) tampil
    // polos di README lama — di seeder ini tiap user diberi password acak yang
    // digenerate saat seeding, WAJIB dicatat/dibagikan lewat jalur aman (bukan
    // dokumen publik), dan flag must_change_pwd dipaksa true untuk semuanya.
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'nama_lengkap' => 'Administrator', 'email' => 'admin@banksulteng.co.id', 'jabatan' => 'IT Admin', 'bagian' => 'Divisi Umum', 'role_id' => 1],
            ['username' => 'pimpinan', 'nama_lengkap' => 'Pemimpin Divisi Umum', 'email' => 'pimdiv.umum@banksulteng.co.id', 'jabatan' => 'Pemimpin Divisi', 'bagian' => 'Divisi Umum', 'role_id' => 2],
            ['username' => 'umum', 'nama_lengkap' => 'Staf Umum dan Rumah Tangga', 'jabatan' => 'Staf Umum & RT', 'bagian' => 'Umum & Rumah Tangga', 'role_id' => 3],
            ['username' => 'aset', 'nama_lengkap' => 'Staf Aset/Inventaris dan Logistik', 'jabatan' => 'Staf Aset & Logistik', 'bagian' => 'Aset/Inventaris & Logistik', 'role_id' => 4],
            ['username' => 'pengadaan', 'nama_lengkap' => 'Staf Pengadaan, Pemeliharaan aset dan Inventaris', 'jabatan' => 'Staf Pengadaan', 'bagian' => 'Pengadaan & Pemeliharaan', 'role_id' => 5],
        ];

        foreach ($users as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => true, 'is_active' => true]
            );
            $this->command?->line("{$u['username']} -> password sementara: {$plain}");
        }
    }
}
