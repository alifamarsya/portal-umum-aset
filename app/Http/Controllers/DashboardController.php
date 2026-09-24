<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\FactBiayaBulanan;
use App\Models\FactPengadaan;
use App\Models\FactAmortisasiAset;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && !$user->canAccessModule('dashboard')) {
            if ($user->canAccessModule('tiket')) {
                return redirect()->route('tiket.index');
            }
            abort(403, 'Role Anda tidak memiliki akses ke Dashboard.');
        }

        $role = $user?->role?->nama;

        // Pilih view per role
        $viewMap = [
            'admin'           => 'admin.dashboard',
            'superadmin'      => 'admin.dashboard',
            'pimpinan'        => 'pimpinan.dashboard',
            'operator'        => 'operator.dashboard',
            'kabag_umum'      => 'kabag.dashboard',
            'kabag_aset'      => 'kabag.dashboard',
            'kabag_pengadaan' => 'kabag.dashboard',
            'staf_umum'       => 'staf.dashboard',
            'staf_aset'       => 'staf.dashboard',
            'staf_pengadaan'  => 'staf.dashboard',
            'umum_rt'         => 'staf.dashboard',
            'aset'            => 'staf.dashboard',
            'pengadaan'       => 'staf.dashboard',
            'user'            => 'user.dashboard',
        ];

        $viewName = isset($viewMap[$role]) && view()->exists($viewMap[$role])
            ? $viewMap[$role]
            : 'dashboard';

        // Data tiket per role untuk dashboard
        $data = $this->getDashboardData($user, $role);

        if ($viewName === 'dashboard') {
            $data = array_merge([
                'menunggu'       => 0,
                'pksJatuhTempo'  => 0,
                'totalAset'      => 0,
                'totalPengadaan' => 0,
                'totalBiaya6Bln' => 0,
                'pksNearDue'     => collect(),
            ], $data);
        }

        return view($viewName, $data);
    }

    private function getDashboardData($user, ?string $role): array
    {
        $base = [
            'activities' => AuditLog::latest('id')->take(5)->get(),
        ];

        return match ($role) {
            'admin', 'superadmin' => array_merge($base, $this->adminData()),
            'pimpinan' => array_merge($base, $this->pimpinanData()),
            'operator' => array_merge($base, $this->operatorData()),
            'kabag_umum', 'kabag_aset', 'kabag_pengadaan' => array_merge($base, $this->kabagData($user)),
            'staf_umum', 'staf_aset', 'staf_pengadaan', 'umum_rt', 'aset', 'pengadaan' => array_merge($base, $this->stafData($user)),
            'user' => array_merge($base, $this->userData($user)),
            default => $base,
        };
    }

    private function adminData(): array
    {
        return [
            'totalTiket'       => Ticket::count(),
            'menungguVerifikasi' => Ticket::where('status', Ticket::STATUS_MENUNGGU)->count(),
            'dalamProses'      => Ticket::where('status', Ticket::STATUS_DALAM_PROSES)->count(),
            'selesaiHariIni'   => Ticket::where('status', Ticket::STATUS_SELESAI)->whereDate('updated_at', today())->count(),
            'totalUser'        => User::where('is_active', true)->count(),
            'tiketTerbaru'     => Ticket::with(['pemohon', 'department'])->latest()->take(5)->get(),
            'statsByStatus'    => $this->statsByStatus(),
        ];
    }

    private function pimpinanData(): array
    {
        $base = [
            'totalTiket'         => Ticket::count(),
            'menungguVerifikasi' => Ticket::where('status', Ticket::STATUS_MENUNGGU)->count(),
            'dialokasikan'       => Ticket::where('status', Ticket::STATUS_DIALOKASIKAN)->count(),
            'dalamProses'        => Ticket::where('status', Ticket::STATUS_DALAM_PROSES)->count(),
            'selesai'            => Ticket::where('status', Ticket::STATUS_SELESAI)->count(),
            'ditolak'            => Ticket::where('status', Ticket::STATUS_DITOLAK)->count(),
            'ditutup'            => Ticket::where('status', Ticket::STATUS_DITUTUP)->count(),
            'tiketTerbaru'       => Ticket::with(['pemohon', 'department'])->latest()->take(10)->get(),
            'statsByStatus'      => $this->statsByStatus(),
        ];

        return array_merge($base, $this->analyticsData());
    }

    private function operatorData(): array
    {
        return [
            'menungguVerifikasi' => Ticket::where('status', Ticket::STATUS_MENUNGGU)->count(),
            'dialokasikan'     => Ticket::where('status', Ticket::STATUS_DIALOKASIKAN)->count(),
            'tiketMenunggu'    => Ticket::where('status', Ticket::STATUS_MENUNGGU)
                ->with(['pemohon', 'kategori'])
                ->latest()->take(10)->get(),
        ];
    }

    private function kabagData(User $user): array
    {
        $deptId = $user->effectiveDepartmentId();
        return [
            'dialokasikan'  => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_DIALOKASIKAN)->count(),
            'dalamProses'   => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_DALAM_PROSES)->count(),
            'selesai'       => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_SELESAI)->count(),
            'tiketMenungguKeputusan' => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_DIALOKASIKAN)
                ->with(['pemohon', 'kategori'])
                ->latest()->take(10)->get(),
        ];
    }

    private function stafData(User $user): array
    {
        $deptId = $user->effectiveDepartmentId();
        return [
            'ditugaskan'    => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_DALAM_PROSES)->count(),
            'selesai'       => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_SELESAI)->count(),
            'tiketSaya'     => Ticket::where('department_id', $deptId)
                ->where('status', Ticket::STATUS_DALAM_PROSES)
                ->with(['pemohon', 'kategori'])
                ->latest()->take(10)->get(),
        ];
    }

    private function userData(User $user): array
    {
        return [
            'totalTiket'       => Ticket::where('pemohon_id', $user->id)->count(),
            'menunggu'         => Ticket::where('pemohon_id', $user->id)->where('status', Ticket::STATUS_MENUNGGU)->count(),
            'dalamProses'      => Ticket::where('pemohon_id', $user->id)->where('status', Ticket::STATUS_DALAM_PROSES)->count(),
            'selesai'          => Ticket::where('pemohon_id', $user->id)->where('status', Ticket::STATUS_SELESAI)->count(),
            'tiketSaya'        => Ticket::where('pemohon_id', $user->id)
                ->with(['kategori', 'department'])
                ->latest()->take(5)->get(),
        ];
    }

    private function statsByStatus(): array
    {
        $stats = [];
        foreach (Ticket::allStatuses() as $status) {
            $stats[$status] = Ticket::where('status', $status)->count();
        }
        return $stats;
    }

    private function analyticsData(): array
    {
        $periode = request()->query('periode', 'bulanan'); // bulanan | kuartalan | tahunan

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
            $biayaDatasets[] = [
                'label' => $kategori,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1
            ];
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

        return compact(
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
        );
    }
}