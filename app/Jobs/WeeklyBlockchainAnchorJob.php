<?php

namespace App\Jobs;

use App\Services\BlockchainAnchorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class WeeklyBlockchainAnchorJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;

    /**
     * Optional custom date range for targeted anchoring.
     */
    public function __construct(
        public ?string $periodeAwal = null,
        public ?string $periodeAkhir = null
    ) {}

    public function handle(BlockchainAnchorService $anchorService): void
    {
        Log::info("Menjalankan WeeklyBlockchainAnchorJob...");

        if ($this->periodeAwal && $this->periodeAkhir) {
            $awal = \Carbon\Carbon::parse($this->periodeAwal);
            $akhir = \Carbon\Carbon::parse($this->periodeAkhir);
            $anchorService->anchorPeriod($awal, $akhir);
        } else {
            // Otomatis proses pekan yang belum ter-anchor
            $anchors = $anchorService->anchorUnanchoredBatches();
            Log::info("WeeklyBlockchainAnchorJob selesai. Total batch diproses: " . count($anchors));
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error("WeeklyBlockchainAnchorJob gagal dieksekusi: " . ($exception ? $exception->getMessage() : 'Unknown error'));
    }
}
