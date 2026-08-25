<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AuditLogAnchor;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
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
        $previousHash = '';
        // Kita urutkan dari ID terkecil (awal) untuk memverifikasi konsistensi rantai
        $allLogs = AuditLog::orderBy('id', 'asc')->get();
        $tamperedIds = [];

        foreach ($allLogs as $log) {
            // Re-calculate hash berdasarkan data di MySQL saat ini
            $expectedHash = hash('sha256', $log->user_id . $log->aksi . $log->modul . $log->entitas . $log->keterangan . $previousHash . $log->created_at);

            if ($log->hash !== $expectedHash) {
                // Tandai ID ini sebagai baris yang dimanipulasi
                $tamperedIds[] = $log->id;
            }
            $previousHash = $log->hash;
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

    public function rehash()
    {
        $previousHash = '';
        $logs = AuditLog::orderBy('id', 'asc')->get();

        foreach ($logs as $log) {
            // Hitung ulang hash yang benar berdasarkan data saat ini
            $newHash = hash('sha256', $log->user_id . $log->aksi . $log->modul . $log->entitas . $log->keterangan . $previousHash . $log->created_at);
            
            $log->update(['hash' => $newHash]);
            $previousHash = $newHash;
        }

        return back()->with('success', 'Rantai hash berhasil disinkronkan kembali!');
    }
}