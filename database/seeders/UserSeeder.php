<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    // Password sementara menggunakan pola username + '2026'.
    // Flag must_change_pwd = true memaksa ganti password saat pertama login.
    public function run(): void
    {
        $users = [
            // Manajemen
            ['username' => 'admin',           'nama_lengkap' => 'Administrator Sistem',              'email' => 'admin@banksulteng.co.id',          'jabatan' => 'IT Admin',               'bagian' => 'Divisi Umum', 'role_id' => 1],
            ['username' => 'pimpinan',         'nama_lengkap' => 'Pemimpin Divisi Umum',              'email' => 'pimdiv.umum@banksulteng.co.id',    'jabatan' => 'Pemimpin Divisi',         'bagian' => 'Divisi Umum', 'role_id' => 2],

            // User / Pemohon (multi-user — demonstrasi banyak akun satu role)
            ['username' => 'user1',            'nama_lengkap' => 'Budi Santoso',                      'email' => 'budi.santoso@banksulteng.co.id',   'jabatan' => 'Staf TI',                'bagian' => 'Divisi TI',   'role_id' => 6],
            ['username' => 'user2',            'nama_lengkap' => 'Siti Rahayu',                       'email' => 'siti.rahayu@banksulteng.co.id',    'jabatan' => 'Staf Keuangan',          'bagian' => 'Divisi Keuangan', 'role_id' => 6],
            ['username' => 'user3',            'nama_lengkap' => 'Ahmad Fauzi',                       'email' => 'ahmad.fauzi@banksulteng.co.id',    'jabatan' => 'Staf Kredit',            'bagian' => 'Divisi Kredit', 'role_id' => 6],

            // Operator / Helpdesk
            ['username' => 'operator',         'nama_lengkap' => 'Operator Helpdesk',                 'email' => 'helpdesk@banksulteng.co.id',        'jabatan' => 'Helpdesk',               'bagian' => 'Divisi Umum', 'role_id' => 7],

            // Kepala Bagian
            ['username' => 'kabag_umum',       'nama_lengkap' => 'Kepala Bagian Umum & Rumah Tangga', 'email' => 'kabag.umum@banksulteng.co.id',     'jabatan' => 'Kepala Bagian',          'bagian' => 'Umum & RT',   'role_id' => 10],
            ['username' => 'kabag_aset',       'nama_lengkap' => 'Kepala Bagian Aset & Logistik',     'email' => 'kabag.aset@banksulteng.co.id',     'jabatan' => 'Kepala Bagian',          'bagian' => 'Aset',        'role_id' => 11],
            ['username' => 'kabag_pengadaan',  'nama_lengkap' => 'Kepala Bagian Pengadaan',           'email' => 'kabag.pengadaan@banksulteng.co.id','jabatan' => 'Kepala Bagian',          'bagian' => 'Pengadaan',   'role_id' => 12],

            // Staf Bagian (multi-user per bagian)
            ['username' => 'staf_umum',        'nama_lengkap' => 'Fajar Nugraha (Staf Umum 1)',       'email' => 'staf.umum@banksulteng.co.id',      'jabatan' => 'Staf Umum & RT',         'bagian' => 'Umum & RT',   'role_id' => 13],
            ['username' => 'staf_umum2',       'nama_lengkap' => 'Rian Pratama (Staf Umum 2)',        'email' => 'staf.umum2@banksulteng.co.id',     'jabatan' => 'Staf Umum & RT',         'bagian' => 'Umum & RT',   'role_id' => 13],
            ['username' => 'staf_aset',        'nama_lengkap' => 'Hendra Gunawan (Staf Aset 1)',      'email' => 'staf.aset@banksulteng.co.id',      'jabatan' => 'Staf Aset & Logistik',   'bagian' => 'Aset',        'role_id' => 14],
            ['username' => 'staf_aset2',       'nama_lengkap' => 'Dewi Lestari (Staf Aset 2)',        'email' => 'staf.aset2@banksulteng.co.id',     'jabatan' => 'Staf Aset & Logistik',   'bagian' => 'Aset',        'role_id' => 14],
            ['username' => 'staf_pengadaan',   'nama_lengkap' => 'Bayu Saputra (Staf Pengadaan 1)',    'email' => 'staf.pengadaan@banksulteng.co.id', 'jabatan' => 'Staf Pengadaan',         'bagian' => 'Pengadaan',   'role_id' => 15],
            ['username' => 'staf_pengadaan2',  'nama_lengkap' => 'Anisa Putri (Staf Pengadaan 2)',    'email' => 'staf.pengadaan2@banksulteng.co.id','jabatan' => 'Staf Pengadaan',         'bagian' => 'Pengadaan',   'role_id' => 15],
        ];

        foreach ($users as $u) {
            $plain = $u['username'] . '2026';
            User::updateOrCreate(
                ['username' => $u['username']],
                [...$u, 'password' => Hash::make($plain), 'must_change_pwd' => true, 'is_active' => true]
            );
            $this->command?->line("{$u['username']} → password sementara: {$plain}");
        }
    }
}
