<?php

namespace App\Http\Controllers;

use App\Models\AsAset;
use App\Models\AsPks;
use App\Models\AuditLog;
use App\Models\FactPengadaan;
use App\Models\PgReminder;
use App\Models\UmBiayaHarian;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $menunggu = UmBiayaHarian::where('approval_status', 'Diajukan')->count();
        $reminderAktif = PgReminder::where('status', 'Aktif')
            ->whereDate('tanggal_jatuh_tempo', '<=', now()->addDays(90))
            ->count();
        $pksJatuhTempo = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->count();

        $totalAset = AsAset::count();
        $totalPengadaan = (float) FactPengadaan::sum('total_nilai');
        $activities = AuditLog::latest('id')->take(5)->get();
        $lastLog = $activities->first();

        // Data chart 6 bulan terakhir per kategori
        $chartLabels = [];
        $chartBbm = [];
        $chartPerawatan = [];
        $chartRt = [];
        $totalBiaya6Bln = 0;

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $chartLabels[] = $month->translatedFormat('M');

            $bbm = (float) UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->where('kategori', 'BBM')
                ->sum('jumlah');

            $rawat = (float) UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->where('kategori', 'Perawatan')
                ->sum('jumlah');

            $rt = (float) UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->where('kategori', 'Rumah Tangga')
                ->sum('jumlah');

            $totalBulan = (float) UmBiayaHarian::whereYear('tanggal', $month->year)
                ->whereMonth('tanggal', $month->month)
                ->sum('jumlah');

            $chartBbm[] = $bbm;
            $chartPerawatan[] = $rawat;
            $chartRt[] = $rt;
            $totalBiaya6Bln += $totalBulan;
        }

        $pksNearDue = AsPks::whereIn('status', ['Aktif', 'Akan Jatuh Tempo'])
            ->whereDate('jatuh_tempo', '<=', now()->addDays(90))
            ->orderBy('jatuh_tempo', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'menunggu',
            'reminderAktif',
            'pksJatuhTempo',
            'totalAset',
            'totalPengadaan',
            'activities',
            'lastLog',
            'chartLabels',
            'chartBbm',
            'chartPerawatan',
            'chartRt',
            'totalBiaya6Bln',
            'pksNearDue'
        ));
    }
}
