<?php

namespace App\Console\Commands;

use App\Jobs\CheckJatuhTempoReminderJob;
use App\Jobs\GenerateLaporanBiayaBulananJob;
use Illuminate\Console\Command;

class BenchmarkOtomasiCommand extends Command
{
    protected $signature = 'transformasi:benchmark {--estimasi-manual-menit=30 : Estimasi waktu manual per proses, dalam menit, berdasarkan wawancara staf}';
    protected $description = 'Ukur waktu eksekusi proses otomatis (job) dan bandingkan dengan estimasi waktu manual, untuk evaluasi kinerja transformasi digital';

    public function handle(): int
    {
        $estimasiManualDetik = ((float) $this->option('estimasi-manual-menit')) * 60;
        $hasil = [];

        // --- Benchmark 1: Generate Laporan Biaya Bulanan ---
        $awal = microtime(true);
        (new GenerateLaporanBiayaBulananJob(
            periodeAwal: now()->startOfMonth()->toDateString(),
            periodeAkhir: now()->endOfMonth()->toDateString(),
            dibuatOleh: 'benchmark-command'
        ))->handle();
        $durasiOtomatis1 = microtime(true) - $awal;

        $hasil[] = [
            'Proses' => 'Generate Laporan Biaya Bulanan',
            'Waktu Otomatis' => round($durasiOtomatis1, 4) . ' detik',
            'Estimasi Manual' => round($estimasiManualDetik / 60, 1) . ' menit',
            'Percepatan' => $this->hitungPercepatan($durasiOtomatis1, $estimasiManualDetik),
        ];

        // --- Benchmark 2: Check Jatuh Tempo Reminder ---
        $awal = microtime(true);
        (new CheckJatuhTempoReminderJob())->handle();
        $durasiOtomatis2 = microtime(true) - $awal;

        $hasil[] = [
            'Proses' => 'Check Jatuh Tempo Reminder',
            'Waktu Otomatis' => round($durasiOtomatis2, 4) . ' detik',
            'Estimasi Manual' => round($estimasiManualDetik / 60, 1) . ' menit',
            'Percepatan' => $this->hitungPercepatan($durasiOtomatis2, $estimasiManualDetik),
        ];

        $this->table(['Proses', 'Waktu Otomatis', 'Estimasi Manual (asumsi wawancara staf)', 'Percepatan'], $hisil = $hasil);

        $this->warn('Catatan: "Estimasi Manual" adalah asumsi, bukan hasil pengukuran langsung -- dokumentasikan sumber asumsi ini (mis. wawancara staf) di docs/transformasi-digital/02-evaluasi-kinerja.md.');

        return self::SUCCESS;
    }

    private function hitungPercepatan(float $detikOtomatis, float $detikManual): string
    {
        if ($detikOtomatis <= 0) {
            return 'n/a';
        }

        $kali = $detikManual / $detikOtomatis;

        return round($kali, 1) . 'x lebih cepat';
    }
}