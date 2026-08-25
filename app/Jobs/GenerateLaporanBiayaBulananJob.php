<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateLaporanBiayaBulananJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $periodeAwal,
        public string $periodeAkhir,
        public string $dibuatOleh
    ) {
        //
    }

    public function handle(): void
    {
        //
    }
}