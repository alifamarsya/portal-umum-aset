<?php

namespace App\Services;

use App\Models\InternalDepartment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
    public function buatTiket(User $pemohon, array $data, ?UploadedFile $file = null): Ticket
    {
        return DB::transaction(function () use ($pemohon, $data, $file) {
            $attachmentPath = null;
            if ($file) {
                $attachmentPath = $file->store('tiket-attachments', 'public');
            }

            $jenisPengajuan = $data['jenis_pengajuan'] ?? Ticket::JENIS_PERMINTAAN;
            $prioritas      = $data['prioritas'] ?? Ticket::PRIORITAS_SEDANG;

            $now = now();
            // Response Time SLA: Maksimal 2 jam kerja (08.00-17.00)
            $responseDueAt  = $this->slaService->calculateResponseDueAt($now);

            // Nilai default awal sla_jam dan sla_due_at untuk backward compatibility
            $slaJam         = Ticket::SLA_HOURS[$prioritas] ?? 48;
            $slaDueAt       = $now->copy()->addHours($slaJam);

            // Auto-generate judul dari kategori
            $kategori   = \App\Models\TicketCategory::find($data['kategori_id']);
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
                'kategori_id'           => $data['kategori_id'],
                'pemohon_id'            => $pemohon->id,
                'status'                => Ticket::STATUS_MENUNGGU,
                'attachment'            => $attachmentPath,
            ]);

            $this->recordHistory(
                $ticket,
                $pemohon,
                'Membuat Tiket',
                null,
                Ticket::STATUS_MENUNGGU,
                "Tiket berhasil diajukan [{$jenisPengajuan}/{$prioritas}] (SLA Respon 2 Jam Kerja s/d {$responseDueAt->format('d/m/Y H:i')})."
            );

            Log::info("Tiket baru dibuat: {$ticket->nomor_tiket} ({$jenisPengajuan}/{$prioritas}) oleh {$pemohon->username}");

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

            return $ticket->fresh();
        });
    }

    // ── Internal Helper ──────────────────────────────────────────────────────

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