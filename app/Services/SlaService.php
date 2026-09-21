<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketCategory;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class SlaService
{
    // Jam kerja resmi: 08.00 s/d 17.00 WIB (Senin - Jumat)
    public const WORK_START_HOUR   = 8;
    public const WORK_START_MINUTE = 0;
    public const WORK_END_HOUR     = 17;
    public const WORK_END_MINUTE   = 0;

    // Durasi standar Response Time: Maksimal 2 Jam Kerja
    public const RESPONSE_TIME_HOURS = 2;

    /**
     * Basis jam kerja per kategori layanan tiket.
     */
    public const CATEGORY_BASE_HOURS = [
        'Pengadaan ATK & Perlengkapan Kantor' => 8,   // 1 hari kerja
        'Permintaan Kendaraan Dinas'          => 16,  // 2 hari kerja
        'Pemeliharaan & Perbaikan Aset IT'    => 24,  // 3 hari kerja
        'Permintaan & Pemindahan Aset'        => 24,  // 3 hari kerja
        'Pemeliharaan Gedung & Fasilitas'     => 32,  // 4 hari kerja
        'Penghapusan & Penilaian Aset'        => 48,  // 6 hari kerja
        'Pengadaan Barang / Jasa'             => 72,  // 8 hari kerja
    ];

    public const DEFAULT_BASE_HOURS = 24;

    /**
     * Pengali waktu berdasarkan skala / eselonisasi.
     */
    public const SKALA_MULTIPLIERS = [
        'Kecil'  => 1.0,  // Skala Kecil / Ringan / Eselon IV - Staf
        'Sedang' => 1.5,  // Skala Sedang / Unit / Eselon III - Kabag
        'Besar'  => 2.0,  // Skala Besar / Strategis / Eselon I-II / Pimpinan
    ];

    /**
     * Pengali waktu berdasarkan tingkat prioritas.
     */
    public const PRIORITAS_MULTIPLIERS = [
        Ticket::PRIORITAS_KRITIS => 0.5,
        Ticket::PRIORITAS_TINGGI => 0.75,
        Ticket::PRIORITAS_SEDANG => 1.0,
        Ticket::PRIORITAS_RENDAH => 1.25,
    ];

    // ── Perhitungan Jam Kerja (Business Hours) ──────────────────────────────

    /**
     * Menambahkan jam kerja ke waktu awal dengan memperhitungkan:
     * - Jam operasional 08.00 - 17.00 WIB
     * - Hari kerja Senin s/d Jumat (mengabaikan Sabtu dan Minggu)
     * - Tiket masuk setelah 17.00, timer dimulai pukul 08.00 hari kerja berikutnya.
     */
    public function addWorkingHours(CarbonInterface $startTime, float $hours): Carbon
    {
        $current = Carbon::parse($startTime)->copy();
        $minutesToAdd = (int) round($hours * 60);

        if ($minutesToAdd <= 0) {
            return $this->normalizeToWorkingWindow($current);
        }

        // Normalisasi waktu awal ke dalam rentang jam kerja
        $current = $this->normalizeToWorkingWindow($current);

        while ($minutesToAdd > 0) {
            $endOfDay = $current->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);
            $minutesLeftToday = $current->diffInMinutes($endOfDay, false);

            if ($minutesLeftToday <= 0) {
                $current = $this->nextWorkingDayStart($current);
                continue;
            }

            if ($minutesToAdd <= $minutesLeftToday) {
                $current->addMinutes($minutesToAdd);
                $minutesToAdd = 0;
            } else {
                $minutesToAdd -= $minutesLeftToday;
                $current = $this->nextWorkingDayStart($current);
            }
        }

        return $current;
    }

    /**
     * Menyesuaikan timestamp ke dalam jam kerja aktif.
     * - Jika akhir pekan (Sabtu/Minggu): geser ke Senin 08.00
     * - Jika sebelum 08.00: geser ke 08.00 hari yang sama
     * - Jika >= 17.00: geser ke 08.00 hari kerja berikutnya
     */
    public function normalizeToWorkingWindow(CarbonInterface $time): Carbon
    {
        $current = Carbon::parse($time)->copy();

        // Jika akhir pekan
        if ($current->isWeekend()) {
            $current->next(Carbon::MONDAY)->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
            return $current;
        }

        $startToday = $current->copy()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
        $endToday   = $current->copy()->setTime(self::WORK_END_HOUR, self::WORK_END_MINUTE, 0);

        // Sebelum jam kerja (sebelum 08.00)
        if ($current->lt($startToday)) {
            return $startToday;
        }

        // Setelah jam 17.00 -> mulai pukul 08.00 hari kerja berikutnya
        if ($current->gte($endToday)) {
            return $this->nextWorkingDayStart($current);
        }

        return $current;
    }

    /**
     * Memajukan waktu ke awal jam kerja hari kerja berikutnya (08.00).
     */
    public function nextWorkingDayStart(CarbonInterface $time): Carbon
    {
        $next = Carbon::parse($time)->copy()->addDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
        while ($next->isWeekend()) {
            $next->addDay();
        }
        return $next;
    }

    // ── Response Time SLA ───────────────────────────────────────────────────

    /**
     * Menghitung batas waktu Response Time (maksimal 2 jam kerja).
     */
    public function calculateResponseDueAt(CarbonInterface $createdAt): Carbon
    {
        return $this->addWorkingHours($createdAt, self::RESPONSE_TIME_HOURS);
    }

    // ── Resolution Time SLA ─────────────────────────────────────────────────

    /**
     * Menghitung total durasi jam resolusi berdasarkan kategori pekerjaan dan prioritas.
     */
    public function calculateResolutionHours(
        ?TicketCategory $category,
        string $prioritas = Ticket::PRIORITAS_SEDANG
    ): int {
        // 1. Basis jam kategori
        $baseHours = self::DEFAULT_BASE_HOURS;
        if ($category && isset(self::CATEGORY_BASE_HOURS[$category->nama])) {
            $baseHours = self::CATEGORY_BASE_HOURS[$category->nama];
        }

        // 2. Multiplier prioritas
        $prioritasMultiplier = self::PRIORITAS_MULTIPLIERS[$prioritas] ?? 1.0;

        $totalHours = (int) round($baseHours * $prioritasMultiplier);

        // Minimal 4 jam kerja
        return max(4, $totalHours);
    }

    /**
     * Menghitung batas waktu resolusi sejak tiket disetujui Kabag.
     */
    public function calculateResolutionDueAt(CarbonInterface $approvedAt, int $resolutionHours): Carbon
    {
        return $this->addWorkingHours($approvedAt, $resolutionHours);
    }

    // ── Evaluasi Status SLA ─────────────────────────────────────────────────

    /**
     * Mengevaluasi status Response SLA: 'pending', 'met', 'breached'.
     */
    public function evaluateResponseStatus(Ticket $ticket, ?CarbonInterface $respondedAt = null): string
    {
        $actualResponse = $respondedAt ?? $ticket->responded_at;

        if ($actualResponse) {
            if ($ticket->response_due_at && Carbon::parse($actualResponse)->gt($ticket->response_due_at)) {
                return 'breached';
            }
            return 'met';
        }

        if ($ticket->response_due_at && now()->gt($ticket->response_due_at)) {
            return 'breached';
        }

        return 'pending';
    }

    /**
     * Mengevaluasi status Resolution SLA: 'pending', 'in_progress', 'met', 'breached'.
     */
    public function evaluateResolutionStatus(Ticket $ticket, ?CarbonInterface $resolvedAt = null): string
    {
        $actualResolved = $resolvedAt ?? $ticket->resolved_at;

        if ($actualResolved) {
            if ($ticket->resolution_due_at && Carbon::parse($actualResolved)->gt($ticket->resolution_due_at)) {
                return 'breached';
            }
            return 'met';
        }

        if (!$ticket->resolution_due_at) {
            return 'pending';
        }

        if (now()->gt($ticket->resolution_due_at)) {
            return 'breached';
        }

        return 'in_progress';
    }

    /**
     * Menentukan kondisi overall SLA: 'on_track', 'warning', 'overdue', 'completed'.
     */
    public function computeOverallSlaStatus(Ticket $ticket): string
    {
        $responseStatus   = $this->evaluateResponseStatus($ticket);
        $resolutionStatus = $this->evaluateResolutionStatus($ticket);

        // Jika salah satu SLA telah breached / lewat deadline
        if ($responseStatus === 'breached' || $resolutionStatus === 'breached') {
            return 'overdue';
        }

        // Jika sudah selesai dan ditutup / diselesaikan
        if (in_array($ticket->status, [Ticket::STATUS_SELESAI, Ticket::STATUS_DITUTUP])) {
            return ($responseStatus === 'met' && ($resolutionStatus === 'met' || $resolutionStatus === 'pending'))
                ? 'completed'
                : 'overdue';
        }

        // Peringatan jika mendekati deadline resolusi (<= 4 jam) atau response (<= 30 menit)
        if (!$ticket->responded_at && $ticket->response_due_at) {
            $minsLeft = now()->diffInMinutes($ticket->response_due_at, false);
            if ($minsLeft <= 30 && $minsLeft > 0) {
                return 'warning';
            }
        }

        if ($ticket->resolution_due_at && !$ticket->resolved_at) {
            $hoursLeft = now()->diffInHours($ticket->resolution_due_at, false);
            if ($hoursLeft <= 4 && $hoursLeft > 0) {
                return 'warning';
            }
        }

        return 'on_track';
    }

    /**
     * Segarkan dan update status SLA pada record tiket jika ada perubahan.
     */
    public function refreshTicketSla(Ticket $ticket): bool
    {
        $oldResponseStatus   = $ticket->response_sla_status;
        $oldResolutionStatus = $ticket->resolution_sla_status;
        $oldOverallStatus    = $ticket->sla_status;

        $newResponseStatus   = $this->evaluateResponseStatus($ticket);
        $newResolutionStatus = $this->evaluateResolutionStatus($ticket);
        $newOverallStatus    = $this->computeOverallSlaStatus($ticket);

        $changed = false;
        $updates = [];

        if ($oldResponseStatus !== $newResponseStatus) {
            $updates['response_sla_status'] = $newResponseStatus;
            $changed = true;
        }

        if ($oldResolutionStatus !== $newResolutionStatus) {
            $updates['resolution_sla_status'] = $newResolutionStatus;
            $changed = true;
        }

        if ($oldOverallStatus !== $newOverallStatus) {
            $updates['sla_status'] = $newOverallStatus;
            $changed = true;
        }

        if ($changed) {
            $ticket->update($updates);
        }

        return $changed;
    }
}