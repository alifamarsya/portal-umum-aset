<?php

namespace Database\Seeders;

use App\Models\AsAmortisasi;
use App\Models\AsAset;
use App\Models\AsDisposalAset;
use App\Models\AsDistribusiBarang;
use App\Models\AsInvoiceSewa;
use App\Models\AsMutasiAset;
use App\Models\AsPenerimaanBarang;
use App\Models\AsPks;
use App\Models\AsTemuan;
use App\Models\PgDraftDokumen;
use App\Models\PgMemoInternal;
use App\Models\PgNegosiasi;
use App\Models\PgPenawaran;
use App\Models\PgReminder;
use App\Models\PgSpk;
use App\Models\PmJadwalPemeliharaan;
use App\Models\PmMonitoringKondisi;
use App\Models\PmPerencanaanKebutuhan;
use App\Models\PmTindakLanjutPerbaikan;
use App\Models\UmBiayaHarian;
use App\Models\UmChecklistKebersihan;
use App\Models\UmFasilitasKantor;
use App\Models\UmK3Insiden;
use App\Models\UmKendaraan;
use App\Models\UmPemeliharaanGedung;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * DummyDataSeeder — mengisi data operasional dummy untuk 6 bulan terakhir
 * sehingga chart & KPI di Dashboard Pimpinan menampilkan data nyata.
 *
 * Adaptasi dari repo referensi:
 *   - Tidak ada alur maker-checker (semua kolom approval diisi null)
 *   - ETL dw:etl dipanggil otomatis di akhir untuk mengisi fact tables
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('=== DummyDataSeeder mulai ===');

        $adminId = User::first()?->id ?? 1;

        // ──────────────────────────────────────────────────────────────────────
        // 1. KENDARAAN OPERASIONAL
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[1/14] Kendaraan operasional...');

        $kendaraanList = [
            ['no_polisi' => 'DN 1001 SB', 'jenis' => 'Minibus',      'merk' => 'Toyota Innova Zenix 2.0 V',       'tahun' => '2024', 'peruntukan' => 'Kendaraan Dinas Direksi',         'driver' => 'Ahmad Fauzi',    'status' => 'Aktif'],
            ['no_polisi' => 'DN 1245 AB', 'jenis' => 'Double Cabin', 'merk' => 'Toyota Hilux 2.4 D-Cab',          'tahun' => '2023', 'peruntukan' => 'Operasional Logistik & Kas',       'driver' => 'Budi Santoso',   'status' => 'Aktif'],
            ['no_polisi' => 'DN 1789 CB', 'jenis' => 'MPV',          'merk' => 'Toyota Avanza 1.5 G',             'tahun' => '2023', 'peruntukan' => 'Operasional Umum & RT',            'driver' => 'I Made Suartana','status' => 'Aktif'],
            ['no_polisi' => 'DN 1122 SB', 'jenis' => 'SUV',          'merk' => 'Mitsubishi Pajero Sport Dakar',   'tahun' => '2024', 'peruntukan' => 'Dinas Pimpinan Kantor Pusat',       'driver' => 'Rizal Pratama',  'status' => 'Aktif'],
            ['no_polisi' => 'DN 1334 DB', 'jenis' => 'Blind Van',    'merk' => 'Daihatsu Gran Max 1.5',           'tahun' => '2022', 'peruntukan' => 'Distribusi Logistik ATK',          'driver' => 'Hendra Wijaya',  'status' => 'Aktif'],
        ];

        foreach ($kendaraanList as $k) {
            UmKendaraan::updateOrCreate(['no_polisi' => $k['no_polisi']], $k);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 2. BIAYA HARIAN OPERASIONAL — 6 Bulan Terakhir
        //    (diisi null untuk kolom maker/checker/approval agar ETL bisa baca semua)
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[2/14] Biaya harian 6 bulan...');

        $biayaTemplates = [
            ['kategori' => 'BBM',          'kendaraan' => 'DN 1001 SB', 'nama_beban' => 'Beban BBM Operasional',         'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Pengisian Pertamax Turbo Dinas Direksi',            'jumlah' => 450000],
            ['kategori' => 'BBM',          'kendaraan' => 'DN 1245 AB', 'nama_beban' => 'Beban BBM Logistik',            'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'BBM Dexlite Pengawalan Kas Luwuk - Palu',           'jumlah' => 850000],
            ['kategori' => 'BBM',          'kendaraan' => 'DN 1789 CB', 'nama_beban' => 'Beban BBM Operasional',         'rekening_debet' => '5.1.01.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'BBM Pertalite Kurir dan Arsip',                     'jumlah' => 250000],
            ['kategori' => 'Perawatan',    'kendaraan' => 'DN 1001 SB', 'nama_beban' => 'Beban Pemeliharaan Kendaraan',  'rekening_debet' => '5.1.02.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Servis berkala 20.000 KM & ganti oli',              'jumlah' => 2150000],
            ['kategori' => 'Perawatan',    'kendaraan' => 'DN 1245 AB', 'nama_beban' => 'Beban Pemeliharaan Kendaraan',  'rekening_debet' => '5.1.02.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Penggantian ban depan 2 unit Bridgestone',          'jumlah' => 3400000],
            ['kategori' => 'Rumah Tangga', 'kendaraan' => null,         'nama_beban' => 'Beban Keperluan Kantor',        'rekening_debet' => '5.1.03.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Pembelian air mineral galon & konsumsi pantry',     'jumlah' => 1250000],
            ['kategori' => 'Rumah Tangga', 'kendaraan' => null,         'nama_beban' => 'Beban Pemeliharaan Gedung',     'rekening_debet' => '5.1.02.02', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Perbaikan & cuci AC Ruang Server lantai 2',         'jumlah' => 1800000],
            ['kategori' => 'Lainnya',      'kendaraan' => null,         'nama_beban' => 'Beban Jamuan & Rapat',          'rekening_debet' => '5.1.04.01', 'rekening_kredit' => '1.1.01.01', 'uraian' => 'Konsumsi Rapat Evaluasi Kinerja Triwulan',          'jumlah' => 2850000],
        ];

        UmBiayaHarian::truncate();

        for ($m = 5; $m >= 0; $m--) {
            $monthDate = now()->copy()->subMonths($m);
            foreach ($biayaTemplates as $idx => $tmpl) {
                $day  = min(3 + ($idx * 3), 27);
                $date = Carbon::create($monthDate->year, $monthDate->month, $day);

                // Variasi jumlah sedikit agar grafik lebih dinamis
                $multiplier  = 1 + (($idx % 3) * 0.15) - ($m * 0.05);
                $finalJumlah = (int) round($tmpl['jumlah'] * max($multiplier, 0.75) / 1000) * 1000;

                UmBiayaHarian::create([
                    'tanggal'         => $date->toDateString(),
                    'kategori'        => $tmpl['kategori'],
                    'kendaraan'       => $tmpl['kendaraan'],
                    'nama_beban'      => $tmpl['nama_beban'],
                    'rekening_debet'  => $tmpl['rekening_debet'],
                    'rekening_kredit' => $tmpl['rekening_kredit'],
                    'uraian'          => $tmpl['uraian'],
                    'jumlah'          => $finalJumlah,
                    'no_nota'         => 'NOTA-' . $date->format('ymd') . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'status'          => 'Draft',
                    'dibuat_oleh'     => 'Dummy Seeder',
                    // maker_id, checker_id, approval_status, approved_at, catatan_approval
                    // dibiarkan default DB (kolom masih ada tapi alur tidak dipakai)
                ]);
            }
        }

        // ──────────────────────────────────────────────────────────────────────
        // 3. FASILITAS KANTOR
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[3/14] Fasilitas kantor...');

        $fasilitasList = [
            ['nama_fasilitas' => 'Ruang Rapat Utama',    'kode' => 'FAR-001', 'kategori' => 'Ruang',      'lokasi' => 'Lt. 3',    'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Ruang Server',          'kode' => 'SRV-001', 'kategori' => 'IT',         'lokasi' => 'Lt. 2',    'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Pantry Lantai 1',       'kode' => 'PNT-001', 'kategori' => 'Dapur',      'lokasi' => 'Lt. 1',    'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Musholla',              'kode' => 'MSH-001', 'kategori' => 'Ibadah',     'lokasi' => 'Lt. 1',    'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Parkir Karyawan',       'kode' => 'PRK-001', 'kategori' => 'Area',       'lokasi' => 'Basement', 'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Gudang ATK',            'kode' => 'GDG-001', 'kategori' => 'Gudang',     'lokasi' => 'Lt. B1',   'kondisi' => 'Baik'],
            ['nama_fasilitas' => 'Ruang Arsip Dokumen',   'kode' => 'ARS-001', 'kategori' => 'Arsip',      'lokasi' => 'Lt. 2',    'kondisi' => 'Cukup Baik'],
            ['nama_fasilitas' => 'Genset Backup',         'kode' => 'GNS-001', 'kategori' => 'Mekanikal',  'lokasi' => 'Belakang', 'kondisi' => 'Baik'],
        ];

        foreach ($fasilitasList as $f) {
            UmFasilitasKantor::updateOrCreate(['kode' => $f['kode']], [
                ...$f,
                'tanggal_perolehan' => now()->subYears(2)->toDateString(),
                'penanggung_jawab'  => 'Bagian Umum & RT',
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 4. PEMELIHARAAN GEDUNG
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[4/14] Pemeliharaan gedung...');

        $pemeliharaanList = [
            ['jenis_pekerjaan' => 'Pengecatan Ulang',   'uraian' => 'Cat ulang dinding lobby utama',              'lokasi' => 'Lt. 1 Lobby',    'biaya' => 12500000, 'status' => 'Selesai'],
            ['jenis_pekerjaan' => 'Perbaikan Plafon',   'uraian' => 'Penggantian plafon bocor pasca hujan deras',  'lokasi' => 'Lt. 2 Ruang Ops','biaya' => 7800000,  'status' => 'Selesai'],
            ['jenis_pekerjaan' => 'Servis HVAC',        'uraian' => 'Service & isi freon AC split 12 unit',        'lokasi' => 'Seluruh gedung', 'biaya' => 9200000,  'status' => 'Selesai'],
            ['jenis_pekerjaan' => 'Perbaikan Lift',     'uraian' => 'Maintenance & kalibrasi lift utama',          'lokasi' => 'Liftshaft',      'biaya' => 18750000, 'status' => 'Dijadwalkan'],
            ['jenis_pekerjaan' => 'Fumigasi',           'uraian' => 'Fumigasi ruang arsip dan gudang ATK',         'lokasi' => 'Lt. 2 & B1',     'biaya' => 3500000,  'status' => 'Selesai'],
        ];

        foreach ($pemeliharaanList as $idx => $p) {
            UmPemeliharaanGedung::create([
                ...$p,
                'tanggal_rencana'    => now()->subMonths(5 - $idx)->startOfMonth()->toDateString(),
                'tanggal_realisasi'  => $p['status'] === 'Selesai' ? now()->subMonths(5 - $idx)->addDays(5)->toDateString() : null,
                'vendor'             => ['PT. Cipta Karya Mandiri', 'CV. Bangunan Jaya', 'PT. Klima Teknik', 'PT. Lift Nusantara', 'CV. Pestindo'][$idx] ?? 'CV. Kontraktor',
                'dibuat_oleh'        => 'Dummy Seeder',
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 5. CHECKLIST KEBERSIHAN (2 per bulan × 6 bulan)
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[5/14] Checklist kebersihan...');

        UmChecklistKebersihan::truncate();
        for ($m = 5; $m >= 0; $m--) {
            $md = now()->subMonths($m);
            foreach ([5, 20] as $day) {
                UmChecklistKebersihan::create([
                    'tanggal'    => Carbon::create($md->year, $md->month, $day)->toDateString(),
                    'jenis'      => $day === 5 ? 'Harian' : 'Mingguan',
                    'area'       => 'Seluruh Lantai',
                    'petugas'    => 'Tim Cleaning Service',
                    'status'     => 'Sudah Dilakukan',
                    'catatan'    => 'Kondisi umum bersih dan tertata.',
                    'dibuat_oleh' => 'Dummy Seeder',
                ]);
            }
        }

        // ──────────────────────────────────────────────────────────────────────
        // 6. INSIDEN K3
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[6/14] Insiden K3...');

        $insidenList = [
            ['jenis' => 'Terpeleset',    'lokasi' => 'Lobby Lt. 1',    'uraian' => 'Karyawan terpeleset saat lantai basah pasca hujan',        'korban' => 'Karyawan (luka ringan)', 'tindak_lanjut' => 'Pasang tanda peringatan dan karpet anti-slip.',  'status' => 'Closed'],
            ['jenis' => 'Korslet Listrik','lokasi' => 'Ruang Server',   'uraian' => 'Korslet pada UPS unit 2, menyebabkan shutdown server',     'korban' => 'Tidak ada',              'tindak_lanjut' => 'Penggantian UPS dan audit instalasi listrik.',    'status' => 'Closed'],
            ['jenis' => 'Kebakaran Kecil','lokasi' => 'Gudang ATK B1', 'uraian' => 'Kebakaran kecil dari karton yang terlalu dekat stop kontak','korban' => 'Tidak ada',              'tindak_lanjut' => 'Pengecekan semua titik stop kontak di gudang.',   'status' => 'Open'],
        ];

        foreach ($insidenList as $idx => $ins) {
            UmK3Insiden::create([
                ...$ins,
                'tanggal'     => now()->subMonths(5 - $idx * 2)->toDateString(),
                'dibuat_oleh' => 'Dummy Seeder',
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 7. ASET
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[7/14] Aset...');

        $asetList = [
            ['kode_aset' => 'AST-IT-001', 'nama_aset' => 'Server HPE ProLiant DL380 Gen10',    'kategori' => 'IT Hardware',    'lokasi' => 'Ruang Server Lt.2',  'nilai_perolehan' => 185000000, 'umur_ekonomis' => 48, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-IT-002', 'nama_aset' => 'NAS Synology RS3621RPxs 16-Bay',      'kategori' => 'IT Hardware',    'lokasi' => 'Ruang Server Lt.2',  'nilai_perolehan' => 72000000,  'umur_ekonomis' => 48, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-IT-003', 'nama_aset' => 'Laptop Dell Latitude 5540 (x10)',     'kategori' => 'IT Hardware',    'lokasi' => 'Seluruh Departemen', 'nilai_perolehan' => 145000000, 'umur_ekonomis' => 36, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-FUR-001','nama_aset' => 'Set Meja Kerja Workstation (x20)',    'kategori' => 'Furniture',      'lokasi' => 'Open Area Lt.1',     'nilai_perolehan' => 60000000,  'umur_ekonomis' => 60, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-FUR-002','nama_aset' => 'Kursi Ergonomis Herman Miller (x15)', 'kategori' => 'Furniture',      'lokasi' => 'Seluruh Departemen', 'nilai_perolehan' => 82500000,  'umur_ekonomis' => 60, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-AC-001', 'nama_aset' => 'AC Split Daikin 2 PK (x12)',          'kategori' => 'Mekanikal',      'lokasi' => 'Seluruh Lantai',     'nilai_perolehan' => 96000000,  'umur_ekonomis' => 60, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-GNS-001','nama_aset' => 'Genset Cummins 150 KVA',              'kategori' => 'Mekanikal',      'lokasi' => 'Area Belakang',      'nilai_perolehan' => 210000000, 'umur_ekonomis' => 120,'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-SEC-001','nama_aset' => 'CCTV Hikvision 64-Channel System',    'kategori' => 'Keamanan',       'lokasi' => 'Seluruh Gedung',     'nilai_perolehan' => 58000000,  'umur_ekonomis' => 48, 'kondisi' => 'Baik'],
            ['kode_aset' => 'AST-TLP-001','nama_aset' => 'PABX Panasonic KX-TDA200',            'kategori' => 'Telekomunikasi', 'lokasi' => 'Ruang IT Lt.2',      'nilai_perolehan' => 35000000,  'umur_ekonomis' => 60, 'kondisi' => 'Cukup Baik'],
            ['kode_aset' => 'AST-OFF-001','nama_aset' => 'Proyektor Epson EB-L210W (x3)',       'kategori' => 'AV Equipment',   'lokasi' => 'Ruang Rapat',        'nilai_perolehan' => 42000000,  'umur_ekonomis' => 48, 'kondisi' => 'Baik'],
        ];

        $asetIds = [];
        foreach ($asetList as $idx => $a) {
            $aset = AsAset::updateOrCreate(['kode_aset' => $a['kode_aset']], [
                ...$a,
                'tanggal_perolehan' => now()->subMonths(18 + $idx * 2)->toDateString(),
                'penanggung_jawab'  => 'Bagian Umum & RT',
            ]);
            $asetIds[] = $aset->id;
        }

        // ──────────────────────────────────────────────────────────────────────
        // 8. AMORTISASI BIAYA (untuk ETL fact_amortisasi_aset)
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[8/14] Amortisasi biaya...');

        AsAmortisasi::truncate();

        $amortisasiList = [
            ['nama_biaya' => 'Lisensi Microsoft 365 Enterprise (Annual)',    'nilai_perolehan' => 48000000, 'umur_bulan' => 12],
            ['nama_biaya' => 'Software Akuntansi Oracle Financials',         'nilai_perolehan' => 120000000,'umur_bulan' => 36],
            ['nama_biaya' => 'Lisensi Antivirus Kaspersky Endpoint (3 Thn)', 'nilai_perolehan' => 18000000, 'umur_bulan' => 36],
            ['nama_biaya' => 'Subscription Adobe Creative Cloud (Annual)',   'nilai_perolehan' => 9600000,  'umur_bulan' => 12],
            ['nama_biaya' => 'Lisensi ERP SAP Basis Module',                 'nilai_perolehan' => 240000000,'umur_bulan' => 60],
        ];

        foreach ($amortisasiList as $a) {
            $nilaiPerBulan = (int) round($a['nilai_perolehan'] / $a['umur_bulan']);
            // Simulasi sudah berjalan 6 bulan
            $akumulasi = $nilaiPerBulan * 6;
            $nilaiPerolehan = $a['nilai_perolehan'];
            $nilaiBuku = max($nilaiPerolehan - $akumulasi, 0);

            AsAmortisasi::create([
                'nama_biaya'      => $a['nama_biaya'],
                'nilai_perolehan' => $nilaiPerolehan,
                'tanggal_mulai'   => now()->subMonths(6)->startOfMonth()->toDateString(),
                'umur_bulan'      => $a['umur_bulan'],
                'nilai_per_bulan' => $nilaiPerBulan,
                'akumulasi'       => $akumulasi,
                'nilai_buku'      => $nilaiBuku,
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 9. PKS (Perjanjian Kerjasama)
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[9/14] PKS...');

        $pksList = [
            ['no_pks' => 'PKS-2026/001', 'judul' => 'Sewa Gedung Cabang Palu Barat',        'vendor' => 'PT. Griya Property',      'div_owner' => 'Aset', 'nilai' => 360000000, 'status' => 'Aktif',         'bulan_mulai' => 12, 'bulan_durasi' => 12],
            ['no_pks' => 'PKS-2026/002', 'judul' => 'Pemeliharaan AC & HVAC Tahunan',      'vendor' => 'CV. Klima Nusantara',     'div_owner' => 'Umum', 'nilai' => 75000000,  'status' => 'Aktif',         'bulan_mulai' => 11, 'bulan_durasi' => 12],
            ['no_pks' => 'PKS-2026/003', 'judul' => 'Layanan Internet Fiber Optic',        'vendor' => 'PT. Telkom Indonesia',    'div_owner' => 'IT',   'nilai' => 120000000, 'status' => 'Aktif',         'bulan_mulai' => 10, 'bulan_durasi' => 12],
            ['no_pks' => 'PKS-2026/004', 'judul' => 'Jasa Cleaning Service Gedung',        'vendor' => 'CV. Bersih Jaya Mandiri', 'div_owner' => 'Umum', 'nilai' => 96000000,  'status' => 'Aktif',         'bulan_mulai' =>  8, 'bulan_durasi' => 12],
        ];

        foreach ($pksList as $p) {
            $mulai = now()->subMonths($p['bulan_mulai'])->startOfMonth();
            AsPks::updateOrCreate(['no_pks' => $p['no_pks']], [
                'no_pks'       => $p['no_pks'],
                'judul'        => $p['judul'],
                'vendor'       => $p['vendor'],
                'div_owner'    => $p['div_owner'],
                'tanggal_mulai'=> $mulai->toDateString(),
                'jatuh_tempo'  => $mulai->copy()->addMonths($p['bulan_durasi'])->toDateString(),
                'nilai'        => $p['nilai'],
                'status'       => $p['status'],
                'memo_dibuat'  => false,
                // maker_id, checker_id, approval_status, approved_at dibiarkan default DB
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 10. INVOICE SEWA
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[10/14] Invoice sewa...');

        $invoiceList = [
            ['no_invoice' => 'INV-2026/001', 'vendor' => 'PT. Griya Property',   'jenis_sewa' => 'Gedung Cabang', 'nilai' => 30000000, 'status' => 'Lunas'],
            ['no_invoice' => 'INV-2026/002', 'vendor' => 'PT. Griya Property',   'jenis_sewa' => 'Gedung Cabang', 'nilai' => 30000000, 'status' => 'Lunas'],
            ['no_invoice' => 'INV-2026/003', 'vendor' => 'CV. Bersih Jaya Mandiri','jenis_sewa' => 'Jasa Kebersihan','nilai' => 8000000, 'status' => 'Belum Bayar'],
        ];

        foreach ($invoiceList as $idx => $inv) {
            AsInvoiceSewa::create([
                ...$inv,
                'periode_mulai'  => now()->subMonths(3 - $idx)->startOfMonth()->toDateString(),
                'periode_selesai'=> now()->subMonths(3 - $idx)->endOfMonth()->toDateString(),
                'jatuh_tempo'    => now()->subMonths(2 - $idx)->toDateString(),
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 11. TEMUAN AUDIT
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[11/14] Temuan audit...');

        $temuanList = [
            ['no_temuan' => 'TEM-2026/01', 'sumber' => 'Audit Internal',   'uraian' => 'Dokumen inventaris aset tidak update selama Q1 2026',    'status' => 'Closed',    'penanggung_jawab' => 'Bagian Aset'],
            ['no_temuan' => 'TEM-2026/02', 'sumber' => 'Audit Eksternal',  'uraian' => 'Beberapa kontrak vendor belum ditandatangani',            'status' => 'Open',      'penanggung_jawab' => 'Bagian Pengadaan'],
            ['no_temuan' => 'TEM-2026/03', 'sumber' => 'Review Internal',  'uraian' => 'BPKB kendaraan DN 1334 DB belum diserahkan ke kantor',    'status' => 'Open',      'penanggung_jawab' => 'Bagian Umum'],
        ];

        foreach ($temuanList as $idx => $t) {
            AsTemuan::create([
                ...$t,
                'tanggal_temuan'        => now()->subMonths(4 - $idx)->toDateString(),
                'batas_tindak_lanjut'   => now()->subMonths(4 - $idx)->addMonths(2)->toDateString(),
                'tindak_lanjut'         => $t['status'] === 'Closed' ? 'Inventaris telah diperbarui dan diverifikasi.' : null,
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 12. PENERIMAAN & DISTRIBUSI BARANG
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[12/14] Penerimaan & distribusi barang...');

        $penerimaanList = [
            ['nama_barang' => 'Kertas HVS A4 80gr (500 rim)',      'jumlah' => 500, 'vendor' => 'CV. Sumber Kertas',      'satuan' => 'Rim'],
            ['nama_barang' => 'Tinta Printer Epson L3210 (12 set)','jumlah' => 12,  'vendor' => 'Toko Digital Express',   'satuan' => 'Set'],
            ['nama_barang' => 'Meja Kerja Modular Baru',           'jumlah' => 5,   'vendor' => 'PT. Furniture Pro',      'satuan' => 'Unit'],
            ['nama_barang' => 'Laptop Dell Latitude 5540',         'jumlah' => 3,   'vendor' => 'PT. Dell Indonesia',     'satuan' => 'Unit'],
        ];

        foreach ($penerimaanList as $idx => $pen) {
            AsPenerimaanBarang::create([
                ...$pen,
                'no_penerimaan' => 'PBR-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'tanggal'       => now()->subMonths(4 - $idx)->toDateString(),
                'kondisi'       => 'Baik',
                'penerima'      => 'Staf Gudang',
                'status'        => 'Diterima',
            ]);
        }

        $distribusiList = [
            ['nama_barang' => 'Kertas HVS A4 (100 rim)',     'jumlah' => 100, 'satuan' => 'Rim',  'tujuan_unit' => 'Divisi Kredit',      'penerima' => 'Staf Kredit'],
            ['nama_barang' => 'Tinta Printer Epson (4 set)', 'jumlah' => 4,   'satuan' => 'Set',  'tujuan_unit' => 'Divisi Keuangan',    'penerima' => 'Staf Keuangan'],
            ['nama_barang' => 'Meja Kerja Modular',          'jumlah' => 3,   'satuan' => 'Unit', 'tujuan_unit' => 'Divisi IT',          'penerima' => 'Staf IT'],
            ['nama_barang' => 'Laptop Dell Latitude',        'jumlah' => 2,   'satuan' => 'Unit', 'tujuan_unit' => 'Divisi Operasional', 'penerima' => 'Kepala Bagian Ops'],
        ];

        foreach ($distribusiList as $idx => $dis) {
            AsDistribusiBarang::create([
                ...$dis,
                'no_distribusi' => 'DBR-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'tanggal'       => now()->subMonths(3 - $idx)->toDateString(),
                'status'        => 'Terkirim',
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // 13. PENGADAAN: NEGOSIASI (6 vendor × 6 bulan = 36 baris)
        //     Ini yang dipakai ETL untuk mengisi fact_pengadaan
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[13/14] Pengadaan: negosiasi, SPK, penawaran, memo...');

        PgNegosiasi::truncate();

        $negosiasiBases = [
            ['vendor' => 'PT. Sinar Teknik Utama',      'barang_jasa' => 'Pengadaan UPS & Stabilizer',         'nilai_awal' => 85000000, 'nilai_nego' => 78000000],
            ['vendor' => 'CV. Bangunan Jaya Makmur',    'barang_jasa' => 'Renovasi Ruang Tunggu Lt.1',          'nilai_awal' => 62000000, 'nilai_nego' => 57500000],
            ['vendor' => 'PT. Komputer Global',         'barang_jasa' => 'Pengadaan PC Desktop All-in-One',     'nilai_awal' => 45000000, 'nilai_nego' => 42000000],
            ['vendor' => 'PT. Furniture Premium',       'barang_jasa' => 'Pengadaan Kursi & Meja Workstation',  'nilai_awal' => 78000000, 'nilai_nego' => 73500000],
            ['vendor' => 'CV. Klima Nusantara',         'barang_jasa' => 'Pengadaan & Pasang AC Cassette',      'nilai_awal' => 38000000, 'nilai_nego' => 35000000],
            ['vendor' => 'PT. Telekomunikasi Mandiri',  'barang_jasa' => 'Layanan VSAT Cabang Prioritas',       'nilai_awal' => 55000000, 'nilai_nego' => 51000000],
        ];

        for ($m = 5; $m >= 0; $m--) {
            $monthDate = now()->copy()->subMonths($m);
            foreach ($negosiasiBases as $idx => $neg) {
                $variasi = 1 + (($idx % 3) * 0.1) - ($m * 0.03);
                $day     = min(5 + ($idx * 4), 28);
                $date    = Carbon::create($monthDate->year, $monthDate->month, $day);

                PgNegosiasi::create([
                    'no_berita_acara' => 'BANE-' . $date->format('ym') . '-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'vendor'          => $neg['vendor'],
                    'barang_jasa'     => $neg['barang_jasa'],
                    'nilai_awal'      => (int) round($neg['nilai_awal'] * max($variasi, 0.8)),
                    'nilai_nego'      => (int) round($neg['nilai_nego'] * max($variasi, 0.8)),
                    'tanggal'         => $date->toDateString(),
                    'hasil'           => 'Kesepakatan harga dicapai. Lanjut ke penerbitan SPK.',
                ]);
            }
        }

        // SPK
        $spkList = [
            ['no_spk' => 'SPK-2026/001', 'vendor' => 'PT. Sinar Teknik Utama',   'pekerjaan' => 'Pengadaan & Instalasi UPS Server Room',  'nilai' => 78000000, 'status' => 'Selesai'],
            ['no_spk' => 'SPK-2026/002', 'vendor' => 'CV. Bangunan Jaya Makmur', 'pekerjaan' => 'Renovasi Ruang Tunggu Lantai 1',          'nilai' => 57500000, 'status' => 'Berjalan'],
            ['no_spk' => 'SPK-2026/003', 'vendor' => 'PT. Komputer Global',      'pekerjaan' => 'Pengadaan PC All-in-One 10 Unit',          'nilai' => 42000000, 'status' => 'Selesai'],
            ['no_spk' => 'SPK-2026/004', 'vendor' => 'PT. Furniture Premium',    'pekerjaan' => 'Pengadaan Kursi Ergonomis 20 Unit',        'nilai' => 73500000, 'status' => 'Berjalan'],
            ['no_spk' => 'SPK-2026/005', 'vendor' => 'CV. Klima Nusantara',      'pekerjaan' => 'Pemasangan AC Cassette 4 PK Lt.3',        'nilai' => 35000000, 'status' => 'Selesai'],
        ];

        foreach ($spkList as $idx => $spk) {
            $terbit   = now()->subMonths(5 - $idx)->toDateString();
            $selesai  = now()->subMonths(5 - $idx)->addMonths(2)->toDateString();
            PgSpk::updateOrCreate(['no_spk' => $spk['no_spk']], [
                ...$spk,
                'tanggal_terbit'  => $terbit,
                'tanggal_selesai' => $selesai,
                // maker_id, checker_id, approval_status, approved_at dibiarkan default DB
            ]);
        }

        // Penawaran
        $penawaranList = [
            ['vendor' => 'PT. Sinar Teknik Utama',   'barang_jasa' => 'UPS & Stabilizer Server',      'nilai' => 85000000, 'status' => 'Diterima'],
            ['vendor' => 'CV. Bangunan Jaya Makmur', 'barang_jasa' => 'Pekerjaan Renovasi Gedung',    'nilai' => 62000000, 'status' => 'Diterima'],
            ['vendor' => 'PT. Komputer Global',      'barang_jasa' => 'PC Desktop All-in-One',        'nilai' => 45000000, 'status' => 'Diterima'],
            ['vendor' => 'CV. Elektronik Murah',     'barang_jasa' => 'PC Desktop Merk Lokal',        'nilai' => 38000000, 'status' => 'Ditolak'],
        ];

        foreach ($penawaranList as $idx => $pen) {
            PgPenawaran::create([
                ...$pen,
                'no_penawaran' => 'PEN-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'tanggal'      => now()->subMonths(4 - $idx)->toDateString(),
            ]);
        }

        // Memo Internal
        $memoList = [
            ['no_memo' => 'MEMO-2026/001', 'dari_unit' => 'Pengadaan',    'ke_unit' => 'Keuangan',     'perihal' => 'Permohonan Pencairan Anggaran Pengadaan PC',  'jenis' => 'Permohonan', 'status' => 'Masuk'],
            ['no_memo' => 'MEMO-2026/002', 'dari_unit' => 'Umum & RT',    'ke_unit' => 'Pengadaan',    'perihal' => 'Kebutuhan Pengadaan ATK Q3 2026',             'jenis' => 'Permintaan', 'status' => 'Masuk'],
            ['no_memo' => 'MEMO-2026/003', 'dari_unit' => 'Pengadaan',    'ke_unit' => 'Direksi',      'perihal' => 'Laporan Realisasi Pengadaan Semester I 2026', 'jenis' => 'Laporan',    'status' => 'Keluar'],
            ['no_memo' => 'MEMO-2026/004', 'dari_unit' => 'Aset',         'ke_unit' => 'Keuangan',     'perihal' => 'Laporan Penyusutan Aset Q2 2026',            'jenis' => 'Laporan',    'status' => 'Keluar'],
        ];

        foreach ($memoList as $idx => $memo) {
            PgMemoInternal::create([
                ...$memo,
                'tanggal'    => now()->subMonths(3 - $idx)->toDateString(),
            ]);
        }

        // Reminder
        PgReminder::create(['judul' => 'Perpanjangan PKS Sewa Gedung Cabang Palu Barat', 'kategori' => 'PKS',          'tanggal_jatuh_tempo' => now()->addMonths(2)->toDateString(), 'status' => 'Aktif',    'catatan' => 'Negosiasi ulang atau perpanjang sebelum jatuh tempo.']);
        PgReminder::create(['judul' => 'Tenggat Laporan Realisasi Anggaran Q3',          'kategori' => 'Administrasi', 'tanggal_jatuh_tempo' => now()->addMonths(1)->toDateString(), 'status' => 'Aktif',    'catatan' => 'Kumpulkan data realisasi dari semua bagian.']);

        // ──────────────────────────────────────────────────────────────────────
        // 14. PM: Jadwal, Monitoring, Perencanaan
        // ──────────────────────────────────────────────────────────────────────
        $this->command->line('[14/14] PM: jadwal pemeliharaan, monitoring, perencanaan...');

        $asetIdSample = count($asetIds) > 0 ? $asetIds[0] : null;

        $jadwalList = [
            ['jenis' => 'Preventif',     'nama_aset' => 'Genset Cummins 150 KVA',     'pelaksana' => 'Teknisi Cummins',           'status' => 'Selesai'],
            ['jenis' => 'Korektif',      'nama_aset' => 'AC Daikin Ruang Server',      'pelaksana' => 'CV. Klima Nusantara',       'status' => 'Selesai'],
            ['jenis' => 'Preventif',     'nama_aset' => 'Server HPE ProLiant',         'pelaksana' => 'IT Support Internal',       'status' => 'Direncanakan'],
            ['jenis' => 'Kalibrasi',     'nama_aset' => 'UPS APC Tower Lt.2',          'pelaksana' => 'PT. APC Indonesia',         'status' => 'Direncanakan'],
        ];

        foreach ($jadwalList as $idx => $j) {
            PmJadwalPemeliharaan::create([
                'no_jadwal'          => 'JPM-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'aset_id'            => $asetIdSample,
                'nama_aset'          => $j['nama_aset'],
                'jenis_pemeliharaan' => $j['jenis'],
                'tanggal_rencana'    => now()->subMonths(3 - $idx)->toDateString(),
                'tanggal_realisasi'  => $j['status'] === 'Selesai' ? now()->subMonths(3 - $idx)->addDays(3)->toDateString() : null,
                'pelaksana'          => $j['pelaksana'],
                'status'             => $j['status'],
            ]);
        }

        $kondisiList = [
            ['nama_aset' => 'Server HPE ProLiant',   'kondisi' => 'Baik',      'temuan' => 'Tidak ada temuan signifikan.',      'rekomendasi' => 'Lanjutkan monitoring rutin.'],
            ['nama_aset' => 'Genset Cummins 150 KVA','kondisi' => 'Cukup Baik','temuan' => 'Filter udara perlu diganti.',       'rekomendasi' => 'Ganti filter sebelum musim hujan.'],
            ['nama_aset' => 'AC Split Daikin Lt.3',  'kondisi' => 'Baik',      'temuan' => 'Drainase tersumbat sebagian.',      'rekomendasi' => 'Bersihkan drainase bulanan.'],
            ['nama_aset' => 'CCTV Hikvision System', 'kondisi' => 'Baik',      'temuan' => '2 kamera sudut perlu dikalibrasi.', 'rekomendasi' => 'Kalibrasi kamera sudut lobby.'],
        ];

        foreach ($kondisiList as $idx => $k) {
            PmMonitoringKondisi::create([
                'aset_id'          => $asetIdSample,
                'nama_aset'        => $k['nama_aset'],
                'tanggal_inspeksi' => now()->subMonths(3 - $idx)->toDateString(),
                'kondisi'          => $k['kondisi'],
                'temuan'           => $k['temuan'],
                'rekomendasi'      => $k['rekomendasi'],
                'petugas'          => 'Tim Pemeliharaan Internal',
                'status'           => 'Selesai',
            ]);
        }

        $perencanaanList = [
            ['jenis_kebutuhan' => 'IT Hardware',  'nama_item' => 'NAS Backup Storage 32TB',     'jumlah' => 1, 'satuan' => 'Unit', 'estimasi_harga' => 95000000, 'prioritas' => 'Tinggi'],
            ['jenis_kebutuhan' => 'Furniture',    'nama_item' => 'Kursi Ergonomis Tambahan',    'jumlah' => 10,'satuan' => 'Unit', 'estimasi_harga' => 55000000, 'prioritas' => 'Normal'],
            ['jenis_kebutuhan' => 'Kendaraan',    'nama_item' => 'Kendaraan Operasional Baru',  'jumlah' => 1, 'satuan' => 'Unit', 'estimasi_harga' => 320000000,'prioritas' => 'Tinggi'],
            ['jenis_kebutuhan' => 'ATK',          'nama_item' => 'Kertas HVS A4 (Stok Tahunan)','jumlah' => 2000,'satuan' => 'Rim','estimasi_harga' => 80000000, 'prioritas' => 'Normal'],
        ];

        foreach ($perencanaanList as $idx => $p) {
            PmPerencanaanKebutuhan::create([
                ...$p,
                'no_rencana' => 'RKN-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'periode'    => 'Semester II 2026',
                'status'     => 'Draft',
            ]);
        }

        // Mutasi Aset
        if (count($asetIds) >= 2) {
            $mutasiList = [
                ['aset_id' => $asetIds[2], 'dari_lokasi' => 'Divisi IT',      'ke_lokasi' => 'Divisi Operasional', 'dari_penanggung_jawab' => 'Staf IT',   'ke_penanggung_jawab' => 'Staf Ops',  'alasan' => 'Relokasi laptop ke divisi operasional'],
                ['aset_id' => $asetIds[3], 'dari_lokasi' => 'Open Area Lt.1', 'ke_lokasi' => 'Workshop',           'dari_penanggung_jawab' => 'Kabag Umum', 'ke_penanggung_jawab' => 'Teknisi',   'alasan' => 'Meja rusak, dikirim ke workshop perbaikan'],
                ['aset_id' => $asetIds[5], 'dari_lokasi' => 'Lt. 2',          'ke_lokasi' => 'Lt. 3',              'dari_penanggung_jawab' => 'Staf Umum',  'ke_penanggung_jawab' => 'Staf Umum', 'alasan' => 'Pemindahan AC ke lantai 3 yang baru direnovasi'],
            ];

            foreach ($mutasiList as $idx => $mut) {
                AsMutasiAset::create([
                    'no_mutasi'              => 'MUT-2026/' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                    'aset_id'                => $mut['aset_id'],
                    'dari_lokasi'            => $mut['dari_lokasi'],
                    'ke_lokasi'              => $mut['ke_lokasi'],
                    'dari_penanggung_jawab'  => $mut['dari_penanggung_jawab'],
                    'ke_penanggung_jawab'    => $mut['ke_penanggung_jawab'],
                    'alasan'                 => $mut['alasan'],
                    'status'                 => 'Selesai',
                    'keterangan'             => 'Mutasi aset dummy untuk keperluan demo.',
                ]);
            }
        }

        // Disposal Aset
        if (count($asetIds) >= 2) {
            AsDisposalAset::create([
                'no_disposal'         => 'DSP-2026/001',
                'aset_id'             => $asetIds[8], // PABX lama
                'tanggal_pengajuan'   => now()->subMonths(2)->toDateString(),
                'alasan_penghapusan'  => 'Sudah tidak efisien & teknologi lama',
                'metode'              => 'Lelang Internal',
                'nilai_buku_terakhir' => 5000000,
                'status'              => 'Diajukan',
            ]);
            AsDisposalAset::create([
                'no_disposal'         => 'DSP-2026/002',
                'aset_id'             => $asetIds[9], // Proyektor
                'tanggal_pengajuan'   => now()->subMonths(1)->toDateString(),
                'alasan_penghapusan'  => 'Projector lama digantikan unit baru',
                'metode'              => 'Hibah ke Sekolah',
                'nilai_buku_terakhir' => 3500000,
                'status'              => 'Diajukan',
            ]);
        }

        // ──────────────────────────────────────────────────────────────────────
        // ETL — Isi fact tables untuk 6 bulan terakhir
        // ──────────────────────────────────────────────────────────────────────
        $this->command->info('=== Menjalankan ETL dw:etl untuk 6 bulan terakhir ===');

        for ($m = 5; $m >= 0; $m--) {
            $d = now()->copy()->subMonths($m);
            $this->command->line("  ETL bulan {$d->translatedFormat('F Y')}...");
            Artisan::call('dw:etl', [
                '--tahun' => $d->year,
                '--bulan' => $d->month,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('=== DummyDataSeeder selesai! ===');
        $this->command->line('  Cek dashboard Pimpinan di /');
    }
}
