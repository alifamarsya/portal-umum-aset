<?php

namespace App\Console\Commands;

use App\Models\FactBiayaBulanan;
use App\Models\UmBiayaHarian;
use Illuminate\Console\Command;

class VerifikasiAkurasiEtlCommand extends Command
{
    protected $signature = 'dw:etl:verify {--bulan=} {--tahun=}';
    protected $description = 'Bandingkan total biaya di OLTP (um_biaya_harian) vs fact_biaya_bulanan untuk deteksi selisih akurasi ETL';

    public function handle(): int
    {
        $bulan = (int) ($this->option('bulan') ?? now()->month);
        $tahun = (int) ($this->option('tahun') ?? now()->year);

        $rawTotal = (float) UmBiayaHarian::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('approval_status', 'Disetujui')
            ->sum('jumlah');

        $factTotal = (float) FactBiayaBulanan::whereHas('waktu', function ($q) use ($bulan, $tahun) {
            $q->where('bulan', $bulan)->where('tahun', $tahun);
        })->sum('total_biaya');

        $selisih = round(abs($rawTotal - $factTotal), 2);
        $persenSelisih = $rawTotal > 0 ? round(($selisih / $rawTotal) * 100, 2) : 0;

        $this->table(
            ['Bulan', 'Tahun', 'Total OLTP', 'Total Fact Table', 'Selisih', 'Selisih %'],
            [[$bulan, $tahun, number_format($rawTotal, 2), number_format($factTotal, 2), number_format($selisih, 2), $persenSelisih . '%']]
        );

        if ($selisih > 0) {
            $this->warn("Ditemukan selisih Rp{$selisih} ({$persenSelisih}%) antara data mentah dan data warehouse.");
            $this->warn('Kemungkinan penyebab: ETL belum dijalankan ulang setelah ada transaksi baru.');
            return self::FAILURE;
        }

        $this->info('Data warehouse akurat, tidak ada selisih dengan data mentah.');
        return self::SUCCESS;
    }
}