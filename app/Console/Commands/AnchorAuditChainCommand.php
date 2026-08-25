<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

// CPMK Blockchain: mengirim "root hash" gabungan seluruh AuditLog periode
// berjalan ke smart contract di testnet Ethereum Sepolia, sebagai bukti
// tamper-evidence tambahan di ledger publik (anchoring), bukan cuma di
// database internal.
class AnchorAuditChainCommand extends Command
{
    protected $signature = 'audit:anchor-chain';
    protected $description = 'Kirim root hash audit log periode berjalan ke testnet Ethereum';

    public function handle(): int
    {
        $awal = now()->startOfWeek();
        $akhir = now()->endOfWeek();

        $hashes = AuditLog::whereBetween('created_at', [$awal, $akhir])
            ->orderBy('id')
            ->pluck('hash');

        if ($hashes->isEmpty()) {
            $this->info('Tidak ada audit log baru minggu ini, skip anchoring.');
            return self::SUCCESS;
        }

        // Root hash = SHA-256 dari gabungan seluruh hash minggu ini
        $rootHash = '0x' . hash('sha256', $hashes->implode(''));

        $this->info("Mengirim root hash: {$rootHash}");

       $result = Process::path(base_path('blockchain-anchor'))
    ->timeout(120)
    ->run(['node', 'anchor.js', $rootHash]);

$rawOutput = $result->output();

// Ambil substring hanya dari karakter '{' sampai '}' (mengabaikan teks log biasa)
$jsonStart = strpos($rawOutput, '{');
$jsonEnd = strrpos($rawOutput, '}');

if ($jsonStart !== false && $jsonEnd !== false) {
    $jsonString = substr($rawOutput, $jsonStart, $jsonEnd - $jsonStart + 1);
    $output = json_decode($jsonString, true);
} else {
    $output = null;
}

if (!($output['success'] ?? false)) {
    $this->error('Gagal anchoring: ' . ($output['error'] ?? $result->errorOutput()));
    return self::FAILURE;
}

AuditLogAnchor::create([
    'root_hash'    => $rootHash,
    'tx_hash'      => $output['txHash'],
    'block_number' => $output['blockNumber'],
    'periode_awal' => $awal,
    'periode_akhir' => $akhir,
]);

$this->info("Berhasil! Tx: {$output['txHash']}");
return self::SUCCESS;
    }
}