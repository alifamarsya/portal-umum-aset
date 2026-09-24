<?php

namespace App\Console\Commands;

use App\Models\AsTemuan;
use App\Models\UmBiayaHarian;
use Illuminate\Console\Command;

class DeteksiAnomaliTransaksiCommand extends Command
{
    protected $signature = 'transaksi:deteksi-anomali {--ambang=2.0 : Ambang batas z-score}';
    protected $description = 'Deteksi transaksi biaya harian yang nilainya jauh di luar kebiasaan (outlier statistik) per kategori';

    public function handle(): int
    {
        $ambang = (float) $this->option('ambang');
        $kategoriList = UmBiayaHarian::select('kategori')->distinct()->pluck('kategori');
        $totalAnomali = 0;

        foreach ($kategoriList as $kategori) {
            $transaksi = UmBiayaHarian::where('kategori', $kategori)
                ->where('approval_status', 'Disetujui')
                ->get(['id', 'jumlah', 'tanggal', 'uraian']);

            if ($transaksi->count() < 5) {
                // data terlalu sedikit untuk hitung stddev yang bermakna
                continue;
            }

            $nilai = $transaksi->pluck('jumlah')->map(fn ($v) => (float) $v);
            $mean = $nilai->avg();
            $variance = $nilai->reduce(fn ($carry, $v) => $carry + (($v - $mean) ** 2), 0) / $nilai->count();
            $stddev = sqrt($variance);

            if ($stddev == 0.0) {
                continue;
            }

            foreach ($transaksi as $t) {
                $zScore = (((float) $t->jumlah) - $mean) / $stddev;

                if (abs($zScore) > $ambang) {
                    $totalAnomali++;
                    $sudahAda = AsTemuan::where('uraian', 'like', "%UmBiayaHarian#{$t->id}%")->exists();

                    if (!$sudahAda) {
                        AsTemuan::create([
                            'sumber' => 'Lainnya',
                            'uraian' => sprintf(
                                'Deteksi outlier statistik pada transaksi UmBiayaHarian#%d (kategori: %s, tanggal: %s, nilai: Rp%s, z-score: %.2f, rata-rata kategori: Rp%s)',
                                $t->id,
                                $kategori,
                                $t->tanggal,
                                number_format((float) $t->jumlah, 2),
                                $zScore,
                                number_format($mean, 2)
                            ),
                            'tanggal_temuan' => now()->toDateString(),
                            'status' => 'Open',
                        ]);
                        $this->warn("Anomali ditemukan: transaksi #{$t->id} kategori {$kategori}, z-score " . round($zScore, 2));
                    }
                }
            }
        }

        $this->info("Selesai. Total anomali baru yang dicatat sebagai Temuan: {$totalAnomali}.");
        $this->line('Catatan: ini deteksi statistik (z-score), bukan machine learning/AI.');

        return self::SUCCESS;
    }
}