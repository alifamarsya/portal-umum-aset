<?php

namespace App\Console\Commands;

use App\Jobs\WeeklyBlockchainAnchorJob;
use App\Models\AuditLogAnchor;
use App\Services\BlockchainAnchorService;
use Illuminate\Console\Command;

class AnchorAuditChainCommand extends Command
{
    protected $signature = 'audit:anchor-chain {--all : Anchor seluruh periode audit log yang belum ter-anchor} {--queue : Jalankan di background queue job}';
    protected $description = 'Kirim root hash audit log periode mingguan ke testnet Ethereum Sepolia (manual/debug fallback)';

    public function handle(BlockchainAnchorService $anchorService): int
    {
        if ($this->option('queue')) {
            WeeklyBlockchainAnchorJob::dispatch();
            $this->info('WeeklyBlockchainAnchorJob berhasil dikirim ke antrean queue!');
            return self::SUCCESS;
        }

        $this->info('Memulai proses blockchain anchoring ke Sepolia testnet...');

        if ($this->option('all')) {
            $this->info('Memeriksa seluruh pekan yang belum ter-anchor...');
            $anchors = $anchorService->anchorUnanchoredBatches();

            if (empty($anchors)) {
                $this->info('Semua aktivitas audit log sudah ter-anchor dengan status sukses.');
                return self::SUCCESS;
            }

            $rows = [];
            foreach ($anchors as $a) {
                $rows[] = [
                    $a->periode_awal?->format('d/m/Y') . ' - ' . $a->periode_akhir?->format('d/m/Y'),
                    $a->total_logs,
                    $a->status,
                    $a->tx_hash ?? '-',
                    $a->block_number ?? '-',
                ];
            }

            $this->table(['Periode', 'Total Log', 'Status', 'Tx Hash', 'Block Number'], $rows);
            return self::SUCCESS;
        }

        // Default: anchor periode pekan ini
        $anchor = $anchorService->anchorPeriod(now()->startOfWeek(), now()->endOfWeek());

        if (!$anchor) {
            $this->warn('Tidak ada audit log baru pada periode pekan ini.');
            return self::SUCCESS;
        }

        if ($anchor->isSuccess()) {
            $this->info("Berhasil di-anchor ke Sepolia!");
            $this->line("Root Hash    : {$anchor->root_hash}");
            $this->line("Tx Hash      : {$anchor->tx_hash}");
            $this->line("Block Number : {$anchor->block_number}");
            $this->line("Explorer     : https://sepolia.etherscan.io/tx/{$anchor->tx_hash}");
            return self::SUCCESS;
        }

        $this->error("Anchoring gagal: {$anchor->error_message}");
        return self::FAILURE;
    }
}