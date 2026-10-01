<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class BlockchainAnchorService
{
    /**
     * Anchor batch audit log untuk periode tertentu ke Sepolia Testnet.
     */
    public function anchorPeriod(?CarbonInterface $awal = null, ?CarbonInterface $akhir = null): ?AuditLogAnchor
    {
        // Default ke pekan berjalan atau pekan sebelumnya
        $awal = $awal ? Carbon::parse($awal)->startOfDay() : now()->startOfWeek();
        $akhir = $akhir ? Carbon::parse($akhir)->endOfDay() : now()->endOfWeek();

        // 1. Ambil seluruh hash log pada rentang tanggal
        $hashes = AuditLog::whereBetween('created_at', [$awal, $akhir])
            ->orderBy('id')
            ->pluck('hash');

        if ($hashes->isEmpty()) {
            Log::info("Blockchain Anchoring: Tidak ada audit log pada periode {$awal->toDateString()} s/d {$akhir->toDateString()}.");
            return null;
        }

        // 2. Buat Merkle root / composite root hash dari seluruh hash baris log
        $rootHash = '0x' . hash('sha256', $hashes->implode(''));

        // Cek jika sudah pernah ter-anchor dengan sukses dan hash yang sama
        $existing = AuditLogAnchor::where('periode_awal', $awal->toDateString())
            ->where('periode_akhir', $akhir->toDateString())
            ->first();

        if ($existing && $existing->isSuccess() && $existing->root_hash === $rootHash) {
            Log::info("Blockchain Anchoring: Periode {$awal->toDateString()} s/d {$akhir->toDateString()} sudah ter-anchor dengan status sukses ({$existing->tx_hash}).");
            return $existing;
        }

        // 3. Simpan / update record dengan status pending
        $anchor = AuditLogAnchor::updateOrCreate(
            [
                'periode_awal'  => $awal->toDateString(),
                'periode_akhir' => $akhir->toDateString(),
            ],
            [
                'root_hash'     => $rootHash,
                'total_logs'    => $hashes->count(),
                'status'        => AuditLogAnchor::STATUS_PENDING,
                'error_message' => null,
            ]
        );

        // 4. Jalankan anchoring ke blockchain Sepolia via node script
        try {
            $anchorScriptDir = base_path('blockchain-anchor');
            $result = Process::path($anchorScriptDir)
                ->timeout(180)
                ->run(['node', 'anchor.js', $rootHash]);

            $rawOutput = $result->output();
            $output = null;

            // Cari JSON response yang valid berisi field "success"
            if (preg_match('/\{[^{}]*"success"[^{}]*\}/s', $rawOutput, $matches)) {
                $output = json_decode($matches[0], true);
            }

            if (!$output) {
                // Fallback: telusuri per baris dari bawah
                $lines = array_reverse(explode("\n", trim($rawOutput)));
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
                        $decoded = json_decode($trimmed, true);
                        if (is_array($decoded) && isset($decoded['success'])) {
                            $output = $decoded;
                            break;
                        }
                    }
                }
            }

            if (($output['success'] ?? false) && !empty($output['txHash'])) {
                $anchor->update([
                    'tx_hash'      => $output['txHash'],
                    'block_number' => $output['blockNumber'] ?? null,
                    'status'       => AuditLogAnchor::STATUS_SUCCESS,
                    'anchored_at'  => now(),
                    'error_message'=> null,
                ]);

                Log::info("Blockchain Anchoring SUKSES: Periode {$awal->toDateString()} s/d {$akhir->toDateString()}, RootHash: {$rootHash}, Tx: {$output['txHash']}");
            } else {
                $err = $output['error'] ?? ($result->errorOutput() ?: 'Gagal berkomunikasi dengan RPC Sepolia testnet');
                $anchor->update([
                    'status'        => AuditLogAnchor::STATUS_FAILED,
                    'error_message' => $err,
                ]);

                Log::error("Blockchain Anchoring GAGAL: Periode {$awal->toDateString()} s/d {$akhir->toDateString()}: {$err}");
            }
        } catch (\Throwable $e) {
            $anchor->update([
                'status'        => AuditLogAnchor::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Blockchain Anchoring Exception: " . $e->getMessage());
        }

        return $anchor->fresh();
    }

    /**
     * Otomatis mencari pekan-pekan audit log yang belum di-anchor dan memprosesnya.
     *
     * @return AuditLogAnchor[]
     */
    public function anchorUnanchoredBatches(): array
    {
        $minDateLog = AuditLog::min('created_at');
        if (!$minDateLog) {
            return [];
        }

        $startCursor = Carbon::parse($minDateLog)->startOfWeek();
        $currentEndOfWeek = now()->endOfWeek();
        $anchored = [];

        while ($startCursor->lte($currentEndOfWeek)) {
            $endCursor = $startCursor->copy()->endOfWeek();

            // Cek apakah pekan ini memiliki log
            $count = AuditLog::whereBetween('created_at', [
                $startCursor->copy()->startOfDay(),
                $endCursor->copy()->endOfDay(),
            ])->count();

            if ($count > 0) {
                // Cek apakah sudah sukses ter-anchor
                $isAlreadyAnchored = AuditLogAnchor::where('periode_awal', $startCursor->toDateString())
                    ->where('periode_akhir', $endCursor->toDateString())
                    ->where('status', AuditLogAnchor::STATUS_SUCCESS)
                    ->exists();

                if (!$isAlreadyAnchored) {
                    $result = $this->anchorPeriod($startCursor, $endCursor);
                    if ($result) {
                        $anchored[] = $result;
                    }
                }
            }

            $startCursor->addWeek();
        }

        return $anchored;
    }
}
