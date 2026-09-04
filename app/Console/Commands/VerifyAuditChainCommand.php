<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

// CPMK Teknologi Blockchain: verifikasi integritas rantai hash di audit_log.
// Menghitung ulang hash tiap baris dari datanya + prev_hash yang tersimpan,
// lalu membandingkan dengan hash yang tercatat -- kalau ada baris yang
// datanya diubah langsung di database (bypass Eloquent), rantai akan putus
// dan langsung ketahuan di baris tempat manipulasi terjadi.
class VerifyAuditChainCommand extends Command
{
    protected $signature = 'audit:verify-chain';
    protected $description = 'Verifikasi integritas rantai hash pada tabel audit_log';

    public function handle(): int
    {
        $expectedPrev = null;
        $rusak = 0;

        AuditLog::orderBy('id')->chunk(500, function ($logs) use (&$expectedPrev, &$rusak) {
            foreach ($logs as $log) {
                if ($log->prev_hash !== $expectedPrev) {
                    $this->error("Rantai putus di log #{$log->id}: prev_hash tidak sesuai urutan sebelumnya.");
                    $rusak++;
                }

                $createdAtStr = is_string($log->created_at)
                    ? $log->created_at
                    : $log->created_at->format('Y-m-d H:i:s');

                $hitung = app(\App\Services\AuditHasher::class)->hitungHash(
                    $log->prev_hash,
                    (string) $log->aksi,
                    (string) $log->modul,
                    (string) $log->entitas,
                    $log->entitas_id,
                    $log->keterangan,
                    $createdAtStr
                );

                if (!app(\App\Services\AuditHasher::class)->cocok($log->hash, $hitung)) {
                    $this->error("Hash tidak cocok di log #{$log->id} -- kemungkinan data dimodifikasi langsung di database.");
                    $rusak++;
                }

                $expectedPrev = $log->hash;
            }
        });

        if ($rusak === 0) {
            $this->info('Rantai audit_log valid, tidak ada indikasi manipulasi.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$rusak} anomali pada rantai audit_log.");
        return self::FAILURE;
    }
}
