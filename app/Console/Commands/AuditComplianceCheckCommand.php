<?php

namespace App\Console\Commands;

use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\AsTemuan;
use App\Models\AuditLog;
use App\Models\PgSpk;
use App\Models\RolePermission;
use App\Models\UmBiayaHarian;
use App\Models\UmPermintaanCabang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AuditComplianceCheckCommand extends Command
{
    protected $signature = 'audit:compliance-check';
    protected $description = 'Jalankan 4 kontrol kepatuhan atas sistem: permission hantu, self-approval, approval tanpa audit log, dan integritas hash chain';

    private array $permKeySistem = ['dashboard', 'panduan', 'user_mgmt', 'role_mgmt', 'audit_log'];

    private array $modelMakerChecker = [
        'UmBiayaHarian' => ['class' => UmBiayaHarian::class, 'kolom_status' => 'approval_status'],
        'AsPks' => ['class' => AsPks::class, 'kolom_status' => 'approval_status'],
        'AsMemoSewaCabang' => ['class' => AsMemoSewaCabang::class, 'kolom_status' => 'status_persetujuan'],
        'PgSpk' => ['class' => PgSpk::class, 'kolom_status' => 'approval_status'],
        'UmPermintaanCabang' => ['class' => UmPermintaanCabang::class, 'kolom_status' => 'approval_status'],
    ];

    public function handle(): int
    {
        $adaGagal = false;

        $this->info('=== Kontrol 1: Permission Hantu (perm_key tidak dikenal) ===');
        $adaGagal = $this->cekPermissionHantu() || $adaGagal;

        $this->newLine();
        $this->info('=== Kontrol 2: Self-Approval pada Transaksi Maker-Checker ===');
        $adaGagal = $this->cekSelfApproval() || $adaGagal;

        $this->newLine();
        $this->info('=== Kontrol 3: Approval Tanpa Jejak di Audit Log ===');
        $adaGagal = $this->cekApprovalTanpaAuditLog() || $adaGagal;

        $this->newLine();
        $this->info('=== Kontrol 4: Integritas Hash Chain (audit:verify-chain) ===');
        $kodeVerifyChain = Artisan::call('audit:verify-chain');
        $this->line(Artisan::output());
        $adaGagal = ($kodeVerifyChain !== self::SUCCESS) || $adaGagal;

        $this->newLine();
        if ($adaGagal) {
            $this->error('COMPLIANCE CHECK: GAGAL -- ada kontrol yang tidak lolos, lihat detail di atas.');
            return self::FAILURE;
        }

        $this->info('COMPLIANCE CHECK: LOLOS -- seluruh kontrol yang diuji patuh.');
        return self::SUCCESS;
    }

    /**
     * Catat temuan ke modul Temuan (as_temuan) supaya tidak hilang begitu
     * terminal ditutup -- ini yang membuat compliance-check jadi "hidup"
     * di aplikasi, bukan cuma teks di layar.
     */
    private function catatTemuan(string $uraian): void
    {
        $sudahAda = AsTemuan::where('uraian', $uraian)
            ->where('status', '!=', 'Selesai')
            ->exists();

        if ($sudahAda) {
            return; // hindari duplikat kalau command dijalankan berkali-kali
        }

        AsTemuan::create([
            'sumber' => 'Audit Internal',
            'uraian' => $uraian,
            'tanggal_temuan' => now()->toDateString(),
            'status' => 'Open',
        ]);
    }

    private function cekPermissionHantu(): bool
    {
        $permValid = collect(config('modules'))
            ->pluck('perm')
            ->unique()
            ->merge($this->permKeySistem)
            ->unique()
            ->values();

        $permHantu = RolePermission::select('perm_key')
            ->distinct()
            ->pluck('perm_key')
            ->diff($permValid);

        if ($permHantu->isEmpty()) {
            $this->info('Tidak ditemukan perm_key hantu. Semua permission di role_permissions valid.');
            return false;
        }

        foreach ($permHantu as $perm) {
            $uraian = "Permission hantu ditemukan: '{$perm}' -- tidak terdaftar di config('modules') maupun daftar permission sistem.";
            $this->error($uraian);
            $this->catatTemuan($uraian);
        }
        return true;
    }

    private function cekSelfApproval(): bool
    {
        $ditemukan = false;

        foreach ($this->modelMakerChecker as $nama => $info) {
            $kelas = $info['class'];
            $kolomStatus = $info['kolom_status'];

            $pelanggaran = $kelas::where($kolomStatus, 'Disetujui')
                ->whereColumn('maker_id', 'checker_id')
                ->get(['id', 'maker_id', 'checker_id']);

            foreach ($pelanggaran as $row) {
                $ditemukan = true;
                $uraian = "Self-approval ditemukan: {$nama}#{$row->id} -- maker_id dan checker_id sama-sama {$row->maker_id}.";
                $this->error($uraian);
                $this->catatTemuan($uraian);
            }
        }

        if (!$ditemukan) {
            $this->info('Tidak ditemukan transaksi dengan maker_id === checker_id yang lolos ke status Disetujui.');
        }

        return $ditemukan;
    }

    private function cekApprovalTanpaAuditLog(): bool
    {
        $ditemukan = false;

        foreach ($this->modelMakerChecker as $nama => $info) {
            $kelas = $info['class'];
            $kolomStatus = $info['kolom_status'];

            $transaksiApproved = $kelas::whereIn($kolomStatus, ['Disetujui', 'Ditolak'])->get(['id', $kolomStatus]);

            foreach ($transaksiApproved as $t) {
                $statusNilai = $t->{$kolomStatus};
                $aksi = $statusNilai === 'Disetujui' ? 'APPROVE' : 'REJECT';

                $adaLog = AuditLog::where('entitas', $nama)
                    ->where('entitas_id', $t->id)
                    ->where('aksi', $aksi)
                    ->exists();

                if (!$adaLog) {
                    $ditemukan = true;
                    $uraian = "Approval tanpa jejak audit: {$nama}#{$t->id} berstatus {$statusNilai} tapi tidak ada baris audit_log dengan aksi {$aksi}.";
                    $this->error($uraian);
                    $this->catatTemuan($uraian);
                }
            }
        }

        if (!$ditemukan) {
            $this->info('Semua approve/reject pada transaksi maker-checker punya jejak yang cocok di audit_log.');
        }

        return $ditemukan;
    }
}