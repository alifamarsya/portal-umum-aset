<?php

namespace App\Services;

use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Notifications\TicketAllocatedNotification;
use App\Notifications\TicketApprovedNotification;
use App\Notifications\TicketClosedNotification;
use App\Notifications\TicketCompletedNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketRejectedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    public function __construct(private SlaService $slaService) {}

    // ── Generate Nomor Tiket ─────────────────────────────────────────────────

    /**
     * Generate nomor tiket otomatis dengan format REQ-YYYYMMDD-XXXX.
     * Menggunakan DB transaction + lock untuk mencegah race condition.
     */
    public function generateNomorTiket(): string
    {
        return DB::transaction(function () {
            $prefix = 'REQ-' . now()->format('Ymd') . '-';

            // Ambil nomor urut hari ini dengan lock
            $last = Ticket::where('nomor_tiket', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('nomor_tiket', 'desc')
                ->value('nomor_tiket');

            $nextSeq = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
        });
    }

    // ── Aksi Tiket ───────────────────────────────────────────────────────────

    /**
     * User membuat tiket baru.
     * Mengkalkulasi response_due_at otomatis (maks 2 jam kerja 08.00-17.00).
     */
    public function buatTiket(User $pemohon, array $data, array|UploadedFile|null $files = null): Ticket
    {
        return DB::transaction(function () use ($pemohon, $data, $files) {
            $jenisPengajuan = $data['jenis_pengajuan'] ?? Ticket::JENIS_PERMINTAAN;
            $prioritas      = $data['prioritas'] ?? Ticket::PRIORITAS_SEDANG;

            $now = now();
            // Response Time SLA: Maksimal 2 jam kerja (08.00-17.00)
            $responseDueAt  = $this->slaService->calculateResponseDueAt($now);

            // Nilai default awal sla_jam dan sla_due_at untuk backward compatibility
            $slaJam         = Ticket::SLA_HOURS[$prioritas] ?? 48;
            $slaDueAt       = $now->copy()->addHours($slaJam);

            // Auto-generate judul
            $kategoriId = $data['kategori_id'] ?? null;
            $kategori   = $kategoriId ? \App\Models\TicketCategory::find($kategoriId) : null;
            $nomorTiket = $this->generateNomorTiket();
            $judulAuto  = ($kategori ? $kategori->nama : 'Layanan') . ' — ' . $jenisPengajuan;

            $ticket = Ticket::create([
                'nomor_tiket'           => $nomorTiket,
                'judul'                 => $judulAuto,
                'deskripsi'             => $data['deskripsi'],
                'jenis_pengajuan'       => $jenisPengajuan,
                'prioritas'             => $prioritas,
                'sla_jam'               => $slaJam,
                'sla_due_at'            => $slaDueAt,
                'response_due_at'       => $responseDueAt,
                'response_sla_status'   => 'pending',
                'resolution_sla_status' => 'pending',
                'sla_status'            => 'on_track',
                'kategori_id'           => $kategoriId,
                'pemohon_id'            => $pemohon->id,
                'status'                => Ticket::STATUS_MENUNGGU,
                'attachment'            => null,
            ]);

            // Simpan multi-file attachments jika ada
            if ($files) {
                $fileList = is_array($files) ? $files : [$files];
                $firstPath = null;

                foreach ($fileList as $file) {
                    if ($file instanceof UploadedFile) {
                        $storedPath = $file->store('tiket-attachments', 'public');
                        if (!$firstPath) {
                            $firstPath = $storedPath;
                        }

                        $ticket->attachments()->create([
                            'file_path' => $storedPath,
                            'file_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                        ]);
                    }
                }

                if ($firstPath) {
                    $ticket->update(['attachment' => $firstPath]);
                }
            }

            $this->recordHistory(
                $ticket,
                $pemohon,
                'Membuat Tiket',
                null,
                Ticket::STATUS_MENUNGGU,
                "Tiket berhasil diajukan [{$jenisPengajuan}/{$prioritas}] (SLA Respon 2 Jam Kerja s/d {$responseDueAt->format('d/m/Y H:i')})."
            );

            Log::info("Tiket baru dibuat: {$ticket->nomor_tiket} ({$jenisPengajuan}/{$prioritas}) oleh {$pemohon->username}");

            // Beri tahu semua operator: ada tiket baru yang menunggu verifikasi.
            $this->kirimNotifikasi(
                $this->operatorAktif(),
                new TicketCreatedNotification($ticket)
            );

            return $ticket;
        });
    }

    /**
     * Operator mengedit atau mengubah jenis pengajuan, prioritas, skala, atau biaya.
     */
    public function updateKlasifikasi(Ticket $ticket, User $operator, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $operator, $data) {
            $changes = [];
            $oldPrioritas = $ticket->prioritas ?? Ticket::PRIORITAS_SEDANG;
            $oldJenis     = $ticket->jenis_pengajuan ?? Ticket::JENIS_PERMINTAAN;
            $oldSkala     = $ticket->skala ?? Ticket::SKALA_KECIL;

            $updates = [];

            if (isset($data['jenis_pengajuan']) && $data['jenis_pengajuan'] !== $oldJenis) {
                $updates['jenis_pengajuan'] = $data['jenis_pengajuan'];
                $changes[] = "Jenis Pengajuan diubah dari '{$oldJenis}' ke '{$data['jenis_pengajuan']}'";
            }

            if (isset($data['prioritas']) && $data['prioritas'] !== $oldPrioritas) {
                $newPrioritas = $data['prioritas'];
                $newSlaJam    = Ticket::SLA_HOURS[$newPrioritas] ?? 48;
                $updates['prioritas'] = $newPrioritas;
                $updates['sla_jam']   = $newSlaJam;

                $baseTime = $ticket->created_at ? $ticket->created_at->copy() : now();
                $updates['sla_due_at'] = $baseTime->addHours($newSlaJam);
                $changes[] = "Prioritas diubah dari '{$oldPrioritas}' ke '{$newPrioritas}'";
            }

            if (!empty($updates)) {
                $ticket->update($updates);

                // Jika tiket sudah dalam proses, hitung ulang target resolution jika prioritas berubah
                if ($ticket->status === Ticket::STATUS_DALAM_PROSES && $ticket->resolution_started_at) {
                    $resolutionHours = $this->slaService->calculateResolutionHours(
                        $ticket->kategori,
                        $ticket->prioritas ?? Ticket::PRIORITAS_SEDANG
                    );
                    $resolutionDueAt = $this->slaService->calculateResolutionDueAt($ticket->resolution_started_at, $resolutionHours);
                    $ticket->update([
                        'resolution_hours'  => $resolutionHours,
                        'resolution_due_at' => $resolutionDueAt,
                        'sla_jam'           => $resolutionHours,
                        'sla_due_at'        => $resolutionDueAt,
                    ]);
                }

                $catatan = implode('; ', $changes);
                if (!empty($data['catatan'])) {
                    $catatan .= " (Catatan: " . $data['catatan'] . ")";
                }

                $this->recordHistory(
                    $ticket,
                    $operator,
                    'Penyesuaian Klasifikasi Tiket',
                    $ticket->status,
                    $ticket->status,
                    $catatan
                );

                Log::info("Tiket {$ticket->nomor_tiket} klasifikasi diubah oleh {$operator->username}: {$catatan}");
            }

            return $ticket->fresh();
        });
    }

    /**
     * Operator mengalokasikan tiket ke department.
     * Menghentikan timer Response Time dan mengevaluasi kepatuhan Response SLA.
     */
    public function alokasikan(Ticket $ticket, User $operator, int $departmentId, array $extraData = []): Ticket
    {
        return DB::transaction(function () use ($ticket, $operator, $departmentId, $extraData) {
            $statusLama = $ticket->status;

            // Jika operator sekaligus mengubah klasifikasi saat alokasi
            if (!empty($extraData['prioritas']) || !empty($extraData['jenis_pengajuan'])) {
                $this->updateKlasifikasi($ticket, $operator, $extraData);
                $ticket->refresh();
            }

            $respondedAt = now();
            $ticket->responded_at = $respondedAt;
            $responseStatus = $this->slaService->evaluateResponseStatus($ticket, $respondedAt);

            $updates = [
                'operator_id'         => $operator->id,
                'department_id'       => $departmentId,
                'status'              => Ticket::STATUS_DIALOKASIKAN,
                'responded_at'        => $respondedAt,
                'response_sla_status' => $responseStatus,
            ];

            $ticket->update($updates);
            $ticket->refresh();

            // Hitung kondisi overall SLA setelah respon
            $ticket->update([
                'sla_status' => $this->slaService->computeOverallSlaStatus($ticket),
            ]);

            $dept = InternalDepartment::find($departmentId);
            $slaResponseLabel = $responseStatus === 'met' ? 'Tepat Waktu' : 'Terlambat';
            $catatanAlokasi = "Dialokasikan ke: {$dept?->nama} [SLA Response: {$slaResponseLabel}]";
            if (!empty($extraData['catatan'])) {
                $catatanAlokasi .= " — Catatan: {$extraData['catatan']}";
            }

            $this->recordHistory(
                $ticket, $operator,
                'Mengalokasikan Tiket',
                $statusLama,
                Ticket::STATUS_DIALOKASIKAN,
                $catatanAlokasi
            );

            Log::info("Tiket {$ticket->nomor_tiket} dialokasikan ke dept #{$departmentId} oleh {$operator->username} (Response SLA: {$responseStatus})");

            // Beri tahu kabag bagian tujuan + pemohon bahwa tiket sudah dialokasikan.
            $notifAlokasi = new TicketAllocatedNotification($ticket, $dept?->nama ?? 'Unit Terkait');
            $this->kirimNotifikasi($this->kabagUntukDepartment($departmentId), $notifAlokasi);
            if ($ticket->pemohon) {
                $ticket->pemohon->notify($notifAlokasi);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Kabag menyetujui tiket dan mengassign ke staf.
     * Timer Resolution Time otomatis mulai berjalan di sini.
     */
    public function setujui(Ticket $ticket, User $kabag, int $stafId, ?string $catatan = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $kabag, $stafId, $catatan) {
            $statusLama = $ticket->status;
            $staf = User::findOrFail($stafId);
            $now  = now();

            // Hitung durasi resolution berdasarkan kategori dan prioritas
            $resolutionHours = $this->slaService->calculateResolutionHours(
                $ticket->kategori,
                $ticket->prioritas ?? Ticket::PRIORITAS_SEDANG
            );

            // Batas waktu penyelesaian dihitung dalam jam kerja (08.00 - 17.00 WIB)
            $resolutionDueAt = $this->slaService->calculateResolutionDueAt($now, $resolutionHours);

            $ticket->update([
                'kabag_id'              => $kabag->id,
                'assigned_to'           => $stafId,
                'status'                => Ticket::STATUS_DALAM_PROSES,
                'resolution_started_at' => $now,
                'resolution_hours'      => $resolutionHours,
                'resolution_due_at'     => $resolutionDueAt,
                'resolution_sla_status' => 'in_progress',
                'sla_jam'               => $resolutionHours,
                'sla_due_at'            => $resolutionDueAt,
                'sla_status'            => 'on_track',
            ]);

            $keteranganSla = "Disetujui dan ditugaskan ke {$staf->nama_lengkap}. Target Resolusi: {$resolutionHours} Jam Kerja (Batas: {$resolutionDueAt->translatedFormat('d M Y H:i')}).";
            if ($catatan) {
                $keteranganSla .= " Catatan: {$catatan}";
            }

            $this->recordHistory(
                $ticket, $kabag,
                'Menyetujui Tiket',
                $statusLama,
                Ticket::STATUS_DALAM_PROSES,
                $keteranganSla
            );

            Log::info("Tiket {$ticket->nomor_tiket} disetujui oleh {$kabag->username}, assigned ke {$staf->username}. Target: {$resolutionHours} jam kerja s/d {$resolutionDueAt}");

            // Beri tahu staf yang ditugaskan + pemohon bahwa tiket disetujui.
            $notifSetuju = new TicketApprovedNotification($ticket);
            $this->kirimNotifikasi($staf, $notifSetuju);
            if ($ticket->pemohon) {
                $ticket->pemohon->notify($notifSetuju);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Kabag menolak tiket.
     */
    public function tolak(Ticket $ticket, User $kabag, string $alasan): Ticket
    {
        return DB::transaction(function () use ($ticket, $kabag, $alasan) {
            $statusLama = $ticket->status;

            $ticket->update([
                'kabag_id'         => $kabag->id,
                'status'           => Ticket::STATUS_DITOLAK,
                'alasan_penolakan' => $alasan,
            ]);

            $this->recordHistory(
                $ticket, $kabag,
                'Menolak Tiket',
                $statusLama,
                Ticket::STATUS_DITOLAK,
                "Alasan: {$alasan}"
            );

            Log::info("Tiket {$ticket->nomor_tiket} ditolak oleh {$kabag->username}: {$alasan}");

            // Beri tahu pemohon bahwa tiketnya ditolak, beserta alasannya.
            if ($ticket->pemohon) {
                $ticket->pemohon->notify(new TicketRejectedNotification($ticket, $alasan));
            }

            return $ticket->fresh();
        });
    }

    /**
     * Staf menyelesaikan tiket.
     * Menghentikan timer Resolution Time dan mengevaluasi kepatuhan Resolution SLA.
     */
    public function selesaikan(Ticket $ticket, User $staf, ?string $catatan = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $staf, $catatan) {
            $statusLama = $ticket->status;
            $now = now();

            $ticket->resolved_at = $now;
            $resolutionStatus = $this->slaService->evaluateResolutionStatus($ticket, $now);

            $ticket->update([
                'status'                => Ticket::STATUS_SELESAI,
                'resolved_at'           => $now,
                'resolution_sla_status' => $resolutionStatus,
            ]);
            $ticket->refresh();

            // Hitung kondisi overall SLA
            $ticket->update([
                'sla_status' => $this->slaService->computeOverallSlaStatus($ticket),
            ]);

            $slaResultText = $resolutionStatus === 'met' ? 'Tepat Waktu' : 'Terlambat';
            $keterangan = "Tiket telah diselesaikan oleh staf [Resolusi: {$slaResultText}].";
            if ($catatan) {
                $keterangan .= " Catatan: {$catatan}";
            }

            $this->recordHistory(
                $ticket, $staf,
                'Menyelesaikan Tiket',
                $statusLama,
                Ticket::STATUS_SELESAI,
                $keterangan
            );

            Log::info("Tiket {$ticket->nomor_tiket} selesai dikerjakan oleh {$staf->username} (Resolution SLA: {$resolutionStatus})");

            // Beri tahu pemohon bahwa pekerjaannya selesai, agar memeriksa
            // hasil lalu mengonfirmasi dan menutup tiket.
            if ($ticket->pemohon) {
                $ticket->pemohon->notify(new TicketCompletedNotification($ticket));
            }

            return $ticket->fresh();
        });
    }

    /**
     * Pemohon mengonfirmasi dan menutup tiket.
     */
    public function tutup(Ticket $ticket, User $pemohon, ?string $catatan = null): Ticket
    {
        return DB::transaction(function () use ($ticket, $pemohon, $catatan) {
            $statusLama = $ticket->status;

            $ticket->update(['status' => Ticket::STATUS_DITUTUP]);

            $this->recordHistory(
                $ticket, $pemohon,
                'Menutup Tiket',
                $statusLama,
                Ticket::STATUS_DITUTUP,
                $catatan ?? 'Tiket ditutup oleh pemohon.'
            );

            Log::info("Tiket {$ticket->nomor_tiket} ditutup oleh {$pemohon->username}");

            // Beri tahu staf pelaksana + kabag bahwa tiket sudah ditutup pemohon.
            $notifTutup = new TicketClosedNotification($ticket);
            if ($ticket->assigned_to) {
                $stafPenutup = User::find($ticket->assigned_to);
                if ($stafPenutup) {
                    $stafPenutup->notify($notifTutup);
                }
            }
            if ($ticket->kabag_id) {
                $kabagPenutup = User::find($ticket->kabag_id);
                if ($kabagPenutup) {
                    $kabagPenutup->notify($notifTutup);
                }
            }

            return $ticket->fresh();
        });
    }

    // ── Internal Helper ──────────────────────────────────────────────────────

    /**
     * Kirim satu notifikasi ke satu user atau sekumpulan user.
     * Notifikasi dikirim di dalam DB transaction yang sama dengan perubahan
     * tiket (seperti pola portum): kalau transaksi dibatalkan, notifikasi ikut
     * batal tersimpan.
     */
    private function kirimNotifikasi(iterable|User|null $penerima, Notification $notifikasi): void
    {
        if ($penerima instanceof User) {
            $penerima = [$penerima];
        }

        if (empty($penerima)) {
            return;
        }

        foreach ($penerima as $user) {
            $user->notify($notifikasi);
        }
    }

    /**
     * Daftar operator aktif (penerima notifikasi tiket baru).
     */
    private function operatorAktif()
    {
        return User::whereHas('role', fn ($q) => $q->where('nama', 'operator'))
            ->where('is_active', true)
            ->get();
    }

    /**
     * Kabag dari sebuah department (1 = Umum & RT, 2 = Aset, 3 = Pengadaan).
     * Pemetaan role mengikuti User::effectiveDepartmentId().
     */
    private function kabagUntukDepartment(int $departmentId)
    {
        $roleNama = match ($departmentId) {
            1 => 'kabag_umum',
            2 => 'kabag_aset',
            3 => 'kabag_pengadaan',
            default => null,
        };

        if (!$roleNama) {
            return collect();
        }

        return User::whereHas('role', fn ($q) => $q->where('nama', $roleNama))
            ->where('is_active', true)
            ->get();
    }

    public function recordHistory(
        Ticket $ticket,
        User $user,
        string $aksi,
        ?string $statusLama,
        string $statusBaru,
        ?string $catatan = null
    ): TicketHistory {
        return TicketHistory::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'aksi'        => $aksi,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'catatan'     => $catatan,
        ]);
    }
}