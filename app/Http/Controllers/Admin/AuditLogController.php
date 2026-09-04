<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use App\Services\AuditHasher;
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
        // Kita urutkan dari ID terkecil (awal) untuk memverifikasi konsistensi rantai
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
                // Tandai ID ini sebagai baris yang dimanipulasi
                $tamperedIds[] = $log->id;
            }

            $expectedPrev = $log->hash;
        }

        // Tandai item paginated jika termasuk yang dimanipulasi
        $items->getCollection()->transform(function ($log) use ($tamperedIds) {
            $log->is_tampered = in_array($log->id, $tamperedIds);
            return $log;
        });

        // 3. Verifikasi Root Hash Blockchain
        $anchors = AuditLogAnchor::latest()->take(10)->get();
        $anchors->transform(function ($anchor) {
            $hashes = AuditLog::whereBetween('created_at', [$anchor->periode_awal, $anchor->periode_akhir])
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