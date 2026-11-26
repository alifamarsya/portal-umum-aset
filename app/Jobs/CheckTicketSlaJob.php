<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSlaWarningNotification;
use App\Services\SlaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Job ini jalan setiap 15 menit (lihat routes/console.php).
// Selain menyegarkan status SLA, job ini juga mengirim peringatan SLA:
// tiket yang mendekati batas waktu (sisa resolusi <= 4 jam kerja atau sisa
// respons <= 30 menit, sesuai SlaService::computeOverallSlaStatus).
// Pola diadaptasi dari TicketSlaService::checkAndNotifyStaffSlaWarning portum:
// peringatan dikirim SEKALI per tiket per user (deduplikasi lewat tabel
// notifications), ke staf pelaksana/operator + pimpinan.
class CheckTicketSlaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SlaService $slaService): void
    {
        $activeTickets = Ticket::whereNotIn('status', [
            Ticket::STATUS_SELESAI,
            Ticket::STATUS_DITUTUP,
            Ticket::STATUS_DITOLAK,
        ])->get();

        $updatedCount = 0;
        foreach ($activeTickets as $ticket) {
            if ($slaService->refreshTicketSla($ticket)) {
                $updatedCount++;
            }
        }

        // Kirim peringatan SLA untuk tiket yang statusnya berubah jadi 'warning'.
        $warningCount = $this->kirimPeringatanSla($activeTickets);

        Log::info("CheckTicketSlaJob executed. Checked {$activeTickets->count()} active tickets, updated {$updatedCount} SLA statuses, {$warningCount} peringatan SLA dikirim.");
    }

    /**
     * Kirim peringatan SLA sekali per tiket per user.
     * Penerima: staf pelaksana (tiket sedang dikerjakan) atau operator
     * (tiket belum direspons), ditambah pimpinan.
     */
    private function kirimPeringatanSla(iterable $tickets): int
    {
        $terkirim = 0;

        foreach ($tickets as $ticket) {
            $ticket->refresh();

            if ($ticket->sla_status !== 'warning') {
                continue;
            }

            $sisaWaktu = $ticket->slaRemainingFormatted();
            $penerima = collect();

            // Tiket belum direspons -> yang harus bergerak adalah operator.
            // Tiket sedang dikerjakan -> yang harus bergerak adalah staf pelaksana.
            if (!$ticket->responded_at) {
                $penerima = $penerima->merge($this->operatorAktif());
            } elseif ($ticket->assigned_to) {
                $staf = User::find($ticket->assigned_to);
                if ($staf) {
                    $penerima->push($staf);
                }
            }

            $penerima = $penerima->merge($this->pimpinanAktif())->unique('id');

            foreach ($penerima as $user) {
                if ($this->sudahDiperingatkan($ticket, $user)) {
                    continue;
                }

                $user->notify(new TicketSlaWarningNotification($ticket, $sisaWaktu));
                $terkirim++;
            }
        }

        return $terkirim;
    }

    /**
     * Cek apakah peringatan SLA untuk tiket ini sudah pernah dikirim ke user ini.
     * Dicari di tabel notifications berdasarkan kolom 'type' (= 'sla_warning',
     * lihat TicketSlaWarningNotification::databaseType) dan ticket_id di data JSON.
     * Pola LIKE memakai koma setelah id agar tiket 1 tidak cocok dengan tiket 12.
     */
    private function sudahDiperingatkan(Ticket $ticket, User $user): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('type', 'sla_warning')
            ->where('data', 'like', '%"ticket_id":' . $ticket->id . ',%')
            ->exists();
    }

    /**
     * Daftar operator aktif.
     */
    private function operatorAktif()
    {
        return User::whereHas('role', fn ($q) => $q->where('nama', 'operator'))
            ->where('is_active', true)
            ->get();
    }

    /**
     * Daftar pimpinan aktif (role 'pimpinan').
     */
    private function pimpinanAktif()
    {
        return User::whereHas('role', fn ($q) => $q->where('nama', 'pimpinan'))
            ->where('is_active', true)
            ->get();
    }
}
