<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi saat kabag menolak tiket.
// Penerima: PEMOHON, beserta alasan penolakan.
// Diadaptasi dari TicketRejectedNotification portum:
// route tickets.show -> tiket.show, ticket_number -> nomor_tiket.
class TicketRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $reason
    ) {}

    // Database (muncul di lonceng + halaman notifikasi) + mail kalau user punya email.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    // Nilai yang disimpan di kolom 'type' tabel notifications.
    public function databaseType(object $notifiable): string
    {
        return 'ticket_rejected';
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'ticket_rejected',
            'ticket_id'   => $this->ticket->id,
            'nomor_tiket' => $this->ticket->nomor_tiket,
            'title'       => "Tiket {$this->ticket->nomor_tiket} Ditolak",
            'judul'       => "Tiket {$this->ticket->nomor_tiket} Ditolak",
            'message'     => "Permohonan tiket Anda telah ditolak. Alasan: {$this->reason}",
            'pesan'       => "Permohonan tiket Anda telah ditolak. Alasan: {$this->reason}",
            'reason'      => $this->reason,
            'action_url'  => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Permohonan Tiket {$this->ticket->nomor_tiket} Ditolak")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permohonan tiket Anda dengan nomor **{$this->ticket->nomor_tiket}** belum dapat diproses / ditolak.")
            ->line("**Alasan Penolakan:** {$this->reason}")
            ->action('Lihat Detail Tiket', route('tiket.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
