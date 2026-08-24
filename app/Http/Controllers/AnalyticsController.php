<?php

namespace App\Http\Controllers;

use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\FactAmortisasiAset;
use App\Models\UmBiayaHarian;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
public function index(Request $request)
{
    $periode = $request->query('periode', 'bulanan'); // bulanan | kuartalan | tahunan

    $totalBiaya = (float) FactBiayaBulanan::sum('total_biaya');
    $totalPengadaan = (float) FactPengadaan::sum('total_nilai');

    $latestWaktuId = FactAmortisasiAset::max('dim_waktu_id');
    $totalNilaiBuku = $latestWaktuId
        ? (float) FactAmortisasiAset::where('dim_waktu_id', $latestWaktuId)->sum('nilai_buku')
        : 0.0;

    $biayaQuery = FactBiayaBulanan::join('dim_waktu', 'fact_biaya_bulanan.dim_waktu_id', '=', 'dim_waktu.id')
        ->join('dim_kategori', 'fact_biaya_bulanan.dim_kategori_id', '=', 'dim_kategori.id');

    if ($periode === 'tahunan') {
        $biaya = (clone $biayaQuery)
            ->selectRaw('dim_waktu.tahun as label_tahun, dim_kategori.nama_kategori, sum(fact_biaya_bulanan.total_biaya) as total')
            ->groupBy('dim_waktu.tahun', 'dim_kategori.nama_kategori')
            ->orderBy('dim_waktu.tahun')
            ->get();
        $labelFn = fn ($row) => (string) $row->label_tahun;
    } elseif ($periode === 'kuartalan') {
        $biaya = (clone $biayaQuery)
            ->selectRaw('dim_waktu.tahun, dim_waktu.kuartal, dim_kategori.nama_kategori, sum(fact_biaya_bulanan.total_biaya) as total')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.kuartal', 'dim_kategori.nama_kategori')
            ->orderBy('dim_waktu.tahun')->orderBy('dim_waktu.kuartal')
            ->get();
        $labelFn = fn ($row) => 'Q' . $row->kuartal . ' ' . $row->tahun;
    } else {
        $periode = 'bulanan';
        $biaya = (clone $biayaQuery)
            ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, dim_kategori.nama_kategori, sum(fact_biaya_bulanan.total_biaya) as total')
            ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan', 'dim_kategori.nama_kategori')
            ->orderBy('dim_waktu.tahun')->orderBy('dim_waktu.bulan')
            ->get();
        $labelFn = fn ($row) => $row->nama_bulan . ' ' . $row->tahun;
    }

    $biayaLabels = [];
    $kategoriList = [];
    $biayaMatrix = [];

    foreach ($biaya as $row) {
        $label = $labelFn($row);
        if (!in_array($label, $biayaLabels)) $biayaLabels[] = $label;
        $kategori = $row->nama_kategori;
        if (!in_array($kategori, $kategoriList)) $kategoriList[] = $kategori;
        $biayaMatrix[$kategori][$label] = (float) $row->total;
    }

    $biayaDatasets = [];
    $colors = ['BBM' => '#3b82f6', 'Perawatan' => '#f59e0b', 'Rumah Tangga' => '#10b981', 'Lainnya' => '#6b7280'];
    $defaultColors = ['#3b82f6', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6', '#6b7280'];
    $colorIndex = 0;

    foreach ($kategoriList as $kategori) {
        $data = [];
        foreach ($biayaLabels as $lbl) {
            $data[] = $biayaMatrix[$kategori][$lbl] ?? 0;
        }
        $color = $colors[$kategori] ?? ($defaultColors[$colorIndex++ % count($defaultColors)]);
        $biayaDatasets[] = ['label' => $kategori, 'data' => $data, 'backgroundColor' => $color, 'borderColor' => $color, 'borderWidth' => 1];
    }

    $pengadaan = FactPengadaan::join('dim_vendor', 'fact_pengadaan.dim_vendor_id', '=', 'dim_vendor.id')
        ->selectRaw('dim_vendor.nama_vendor, sum(fact_pengadaan.total_nilai) as total')
        ->groupBy('dim_vendor.nama_vendor')
        ->get();

    $vendorLabels = [];
    $vendorTotals = [];
    foreach ($pengadaan as $row) {
        $vendorLabels[] = $row->nama_vendor ?: 'Lainnya';
        $vendorTotals[] = (float) $row->total;
    }

    $amortisasi = FactAmortisasiAset::join('dim_waktu', 'fact_amortisasi_aset.dim_waktu_id', '=', 'dim_waktu.id')
        ->selectRaw('dim_waktu.nama_bulan, dim_waktu.tahun, dim_waktu.bulan, sum(fact_amortisasi_aset.nilai_penyusutan_bulan) as total_penyusutan, sum(fact_amortisasi_aset.nilai_buku) as total_nilai_buku')
        ->groupBy('dim_waktu.tahun', 'dim_waktu.bulan', 'dim_waktu.nama_bulan')
        ->orderBy('dim_waktu.tahun')->orderBy('dim_waktu.bulan')
        ->get();

    $amortisasiLabels = [];
    $penyusutanData = [];
    $nilaiBukuData = [];
    foreach ($amortisasi as $row) {
        $amortisasiLabels[] = $row->nama_bulan . ' ' . $row->tahun;
        $penyusutanData[] = (float) $row->total_penyusutan;
        $nilaiBukuData[] = (float) $row->total_nilai_buku;
    }

        return view('analitik', compact(
            'totalBiaya',
            'totalPengadaan',
            'totalNilaiBuku',
            'biayaLabels',
            'biayaDatasets',
            'kategoriList',
            'vendorLabels',
            'vendorTotals',
            'amortisasiLabels',
            'penyusutanData',
            'nilaiBukuData',
            'periode'
        ));
    }

    public function detailKategori(string $kategori)
    {
        $transaksi = UmBiayaHarian::where('kategori', $kategori)
            ->where('approval_status', 'Disetujui')
            ->orderBy('tanggal')
            ->get(['tanggal', 'nama_beban', 'jumlah', 'uraian']);

        $totalKategori = (float) $transaksi->sum('jumlah');

        return view('analitik-detail', compact('kategori', 'transaksi', 'totalKategori'));
    }

    public function exportCsv()
    {
        $data = FactBiayaBulanan::with(['waktu', 'kategori'])->get();

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tahun', 'Bulan', 'Kategori', 'Total Biaya', 'Jumlah Transaksi']);

            foreach ($data as $row) {
                fputcsv($out, [
                    $row->waktu->tahun,
                    $row->waktu->nama_bulan,
                    $row->kategori->nama_kategori,
                    $row->total_biaya,
                    $row->jumlah_transaksi,
                ]);
            }

            fclose($out);
        }, 'warehouse-biaya-bulanan.csv');
    }
}