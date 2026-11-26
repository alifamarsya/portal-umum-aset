<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi saat pemohon mengonfirmasi dan menutup tiket.
// Penerima: STAF pelaksana dan KABAG yang menangani, agar tahu pekerjaan
// sudah dinyatakan selesai oleh pemohon dan tiket resmi ditutup.
// Di portum tidak ada notifikasi jenis ini untuk penutupan manual
// (portum hanya memberi tahu pemohon saat tiket ditutup OTOMATIS oleh sistem).
// Portal tidak punya auto-close, jadi penutupan manual oleh pemohon adalah
// satu-satunya penutupan — dan staf/kabag yang perlu diberi tahu.
class TicketClosedNotification extends Notification
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
        return 'ticket_closed';
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'ticket_closed',
            'ticket_id'   => $this->ticket->id,
            'nomor_tiket' => $this->ticket->nomor_tiket,
            'title'       => "Tiket {$this->ticket->nomor_tiket} Ditutup",
            'judul'       => "Tiket {$this->ticket->nomor_tiket} Ditutup",
            'message'     => "Tiket {$this->ticket->nomor_tiket} telah ditutup oleh pemohon setelah dikonfirmasi selesai.",
            'pesan'       => "Tiket {$this->ticket->nomor_tiket} telah ditutup oleh pemohon setelah dikonfirmasi selesai.",
            'action_url'  => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket {$this->ticket->nomor_tiket} Telah Ditutup")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Tiket **{$this->ticket->nomor_tiket}** telah ditutup oleh pemohon setelah dikonfirmasi selesai.")
            ->action('Lihat Detail Tiket', route('tiket.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
