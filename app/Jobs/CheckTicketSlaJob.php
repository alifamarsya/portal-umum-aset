<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\SlaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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

        Log::info("CheckTicketSlaJob executed. Checked {$activeTickets->count()} active tickets, updated {$updatedCount} SLA statuses.");
    }
}