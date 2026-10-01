<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\WeeklyBlockchainAnchorJob;
use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use App\Services\AuditHasher;
use App\Services\BlockchainAnchorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request, AuditHasher $hasher)
    {
        // 1. Ambil data Audit Log dengan Filter Gabungan (search)
        $items = AuditLog::with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('username', 'like', "%{$request->search}%")
                          ->orWhere('modul', 'like', "%{$request->search}%");
                });
            })
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // 2. Verifikasi Rantai Hash per Baris (Hash Chain Verification)
        $expectedPrev = null;
        $allLogs = AuditLog::orderBy('id', 'asc')->get();
        $tamperedIds = [];

        foreach ($allLogs as $log) {
            $isTampered = false;

            // Cek 1: Integritas sambungan rantai (chain linkage)
            if ($log->prev_hash !== $expectedPrev) {
                $isTampered = true;
            }

            // Cek 2: Integritas data pada baris tersebut (data integrity)
            $createdAtStr = is_string($log->created_at)
                ? $log->created_at
                : $log->created_at->format('Y-m-d H:i:s');

            $expectedHash = $hasher->hitungHash(
                $log->prev_hash,
                (string) $log->aksi,
                (string) $log->modul,
                (string) $log->entitas,
                $log->entitas_id,
                $log->keterangan,
                $createdAtStr
            );

            if (!$hasher->cocok($log->hash, $expectedHash)) {
                $isTampered = true;
            }

            if ($isTampered) {
                $tamperedIds[] = $log->id;
            }

            $expectedPrev = $log->hash;
        }

        // Ambil rentang periode sukses anchor untuk menandai baris audit log
        $successAnchors = AuditLogAnchor::where('status', AuditLogAnchor::STATUS_SUCCESS)->get();

        // Tandai item paginated
        $items->getCollection()->transform(function ($log) use ($tamperedIds, $successAnchors) {
            $log->is_tampered = in_array($log->id, $tamperedIds);

            // Cek apakah baris log ini tercakup dalam batch anchor sukses
            $matchedAnchor = $successAnchors->first(function ($a) use ($log) {
                $start = Carbon::parse($a->periode_awal)->startOfDay();
                $end   = Carbon::parse($a->periode_akhir)->endOfDay();
                return $log->created_at >= $start && $log->created_at <= $end;
            });

            $log->anchored = (bool) $matchedAnchor;
            $log->anchor_tx = $matchedAnchor?->tx_hash;

            return $log;
        });

        // 3. Verifikasi Root Hash Blockchain
        $anchors = AuditLogAnchor::latest('id')->take(15)->get();
        $anchors->transform(function ($anchor) {
            $start = Carbon::parse($anchor->periode_awal)->startOfDay();
            $end   = Carbon::parse($anchor->periode_akhir)->endOfDay();

            $hashes = AuditLog::whereBetween('created_at', [$start, $end])
                ->orderBy('id')
                ->pluck('hash');

            if ($hashes->isEmpty()) {
                $anchor->is_valid = true;
                return $anchor;
            }

            $currentRootHash = '0x' . hash('sha256', $hashes->implode(''));
            $anchor->is_valid = ($currentRootHash === $anchor->root_hash);

            return $anchor;
        });

        return view('admin.audit-log.index', compact('items', 'anchors', 'tamperedIds'));
    }

    public function triggerAnchor(Request $request, BlockchainAnchorService $anchorService)
    {
        if ($request->boolean('async')) {
            WeeklyBlockchainAnchorJob::dispatch();
            return back()->with('success', 'Job otomatisasi anchoring berhasil dikirim ke antrean queue!');
        }

        $anchors = $anchorService->anchorUnanchoredBatches();
        $totalSuccess = collect($anchors)->filter(fn ($a) => $a->isSuccess())->count();

        if ($totalSuccess > 0) {
            return back()->with('success', "Proses anchoring selesai! {$totalSuccess} batch pekan berhasil di-anchor ke Sepolia testnet.");
        }

        $failed = collect($anchors)->filter(fn ($a) => $a->isFailed())->first();
        if ($failed) {
            return back()->with('error', "Anchoring gagal ke Sepolia testnet: {$failed->error_message}");
        }

        return back()->with('success', 'Semua aktivitas audit log sudah ter-anchor dengan status sukses.');
    }

    public function rehash(AuditHasher $hasher)
    {
        $expectedPrev = null;
        $logs = AuditLog::orderBy('id', 'asc')->get();

        foreach ($logs as $log) {
            $createdAtStr = is_string($log->created_at)
                ? $log->created_at
                : $log->created_at->format('Y-m-d H:i:s');

            // Hitung ulang hash menggunakan AuditHasher service
            $newHash = $hasher->hitungHash(
                $expectedPrev,
                (string) $log->aksi,
                (string) $log->modul,
                (string) $log->entitas,
                $log->entitas_id,
                $log->keterangan,
                $createdAtStr
            );

            $log->update([
                'prev_hash' => $expectedPrev,
                'hash'      => $newHash,
            ]);

            $expectedPrev = $newHash;
        }

        return back()->with('success', 'Rantai hash berhasil disinkronkan kembali!');
    }
}