<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckTicketSlaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:check-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek kepatuhan SLA dua tingkat (Response Time & Resolution Time) pada tiket aktif';

    public function __construct(private SlaService $slaService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pemantauan SLA tiket aktif (Response & Resolution)...');

        $activeTickets = Ticket::whereNotIn('status', [
            Ticket::STATUS_SELESAI,
            Ticket::STATUS_DITUTUP,
            Ticket::STATUS_DITOLAK,
        ])
        ->with(['pemohon', 'department', 'assignedStaf', 'kategori'])
        ->orderBy('created_at', 'asc')
        ->get();

        if ($activeTickets->isEmpty()) {
            $this->info('Tidak ada tiket aktif yang memerlukan pemantauan SLA.');
            return self::SUCCESS;
        }

        $breachedCount = 0;
        $warningCount  = 0;
        $normalCount   = 0;
        $rows          = [];

        foreach ($activeTickets as $ticket) {
            // Update / sync status SLA ke database
            $this->slaService->refreshTicketSla($ticket);
            $ticket->refresh();

            $isOverdue = $ticket->sla_status === 'overdue' || $ticket->isSlaBreached();
            $isWarning = $ticket->sla_status === 'warning';

            // Kondisi Response SLA
            $responseStatusText = match ($ticket->response_sla_status) {
                'met'      => '<fg=green>Tepat Waktu</>',
                'breached' => '<fg=red;options=bold>LEWAT</>',
                default    => $ticket->response_due_at && now()->gt($ticket->response_due_at)
                    ? '<fg=red;options=bold>LEWAT</>'
                    : '<fg=yellow>Menunggu</>',
            };

            // Kondisi Resolution SLA
            $resolutionStatusText = match ($ticket->resolution_sla_status) {
                'met'         => '<fg=green>Tepat Waktu</>',
                'breached'    => '<fg=red;options=bold>LEWAT</>',
                'in_progress' => $ticket->resolution_due_at && now()->gt($ticket->resolution_due_at)
                    ? '<fg=red;options=bold>LEWAT</>'
                    : '<fg=cyan>Proses</>',
                default       => '<fg=gray>Belum Mulai</>',
            };

            if ($isOverdue) {
                $breachedCount++;
                $overallText = '<fg=red;options=bold>OVERDUE</>';
                Log::warning("SLA BREACHED: Tiket {$ticket->nomor_tiket} melewati batas SLA (Response: {$ticket->response_sla_status}, Resolution: {$ticket->resolution_sla_status}).");
            } elseif ($isWarning) {
                $warningCount++;
                $overallText = '<fg=yellow;options=bold>WARNING</>';
            } else {
                $normalCount++;
                $overallText = '<fg=green>ON TRACK</>';
            }

            $rows[] = [
                $ticket->nomor_tiket,
                $ticket->status,
                $ticket->response_due_at?->format('d/m H:i') ?? '-',
                $responseStatusText,
                $ticket->resolution_due_at?->format('d/m H:i') ?? '—',
                $resolutionStatusText,
                $overallText,
            ];
        }

        $this->table(
            ['No. Tiket', 'Status Tiket', 'Batas Response', 'SLA Response', 'Batas Resolusi', 'SLA Resolusi', 'Status Overall'],
            $rows
        );

        $this->line('');
        $this->info("Pengecekan selesai. Total Tiket Aktif: {$activeTickets->count()}");
        if ($breachedCount > 0) {
            $this->error("Peringatan: {$breachedCount} tiket melampaui batas waktu SLA!");
        }
        if ($warningCount > 0) {
            $this->warn("Perhatian: {$warningCount} tiket mendekati deadline waktu.");
        }
        $this->info("Aman: {$normalCount} tiket berjalan sesuai target SLA.");

        return self::SUCCESS;
    }
}