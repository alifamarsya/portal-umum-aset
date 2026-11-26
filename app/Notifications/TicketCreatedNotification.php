<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi untuk OPERATOR saat ada tiket baru yang diajukan pemohon.
// Di portum tidak ada notifikasi jenis ini (pemohon portum hanya menerima
// notifikasi setelah tiketnya dialokasikan). Di portal, operator perlu tahu
// ada tiket baru yang menunggu verifikasi.
class TicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket
    ) {}

    // Database (muncul di lonceng + halaman notifikasi) + mail kalau user punya email.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    // Nilai yang disimpan di kolom 'type' tabel notifications.
    public function databaseType(object $notifiable): string
    {
        return 'ticket_created';
    }

    public function toDatabase($notifiable): array
    {
        $pemohon = $this->ticket->pemohon?->nama_lengkap ?? 'Pemohon';

        return [
            'type'        => 'ticket_created',
            'ticket_id'   => $this->ticket->id,
            'nomor_tiket' => $this->ticket->nomor_tiket,
            'title'       => "Tiket Baru {$this->ticket->nomor_tiket}",
            'judul'       => "Tiket Baru {$this->ticket->nomor_tiket}",
            'message'     => "Tiket baru {$this->ticket->nomor_tiket} diajukan oleh {$pemohon} ({$this->ticket->jenis_pengajuan} / {$this->ticket->prioritas}). Segera verifikasi dan alokasikan.",
            'pesan'       => "Tiket baru {$this->ticket->nomor_tiket} diajukan oleh {$pemohon} ({$this->ticket->jenis_pengajuan} / {$this->ticket->prioritas}). Segera verifikasi dan alokasikan.",
            'action_url'  => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $pemohon = $this->ticket->pemohon?->nama_lengkap ?? 'Pemohon';

        return (new MailMessage)
            ->subject("Tiket Baru {$this->ticket->nomor_tiket} Menunggu Verifikasi")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Tiket baru dengan nomor **{$this->ticket->nomor_tiket}** diajukan oleh **{$pemohon}** ({$this->ticket->jenis_pengajuan} / Prioritas {$this->ticket->prioritas}).")
            ->line("**Uraian:** {$this->ticket->deskripsi}")
            ->action('Verifikasi & Alokasikan Tiket', route('tiket.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
