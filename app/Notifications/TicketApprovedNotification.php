<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi saat kabag menyetujui tiket dan menunjuk staf pelaksana.
// Penerima: STAF yang ditugaskan (agar tahu ada pekerjaan baru + target
// resolusinya) dan PEMOHON (agar tahu tiketnya disetujui dan dikerjakan).
// Di portum tidak ada notifikasi jenis ini (persetujuan kabag tidak
// mengirim notifikasi). Di portal, persetujuan sekaligus menugaskan staf,
// jadi staf wajib diberi tahu.
class TicketApprovedNotification extends Notification
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
        return 'ticket_approved';
    }

    public function toDatabase($notifiable): array
    {
        $batas = $this->ticket->resolution_due_at
            ? $this->ticket->resolution_due_at->translatedFormat('d M Y H:i')
            : '-';

        // Pesan dibedakan menurut penerima: staf mendapat perintah kerja
        // beserta target, pemohon mendapat kabar status.
        $message = $notifiable->id === $this->ticket->assigned_to
            ? "Tiket {$this->ticket->nomor_tiket} telah disetujui dan ditugaskan ke Anda. Target resolusi: {$this->ticket->resolution_hours} jam kerja (batas {$batas})."
            : "Tiket {$this->ticket->nomor_tiket} telah disetujui dan sedang dikerjakan oleh staf. Target selesai: {$batas}.";

        return [
            'type'            => 'ticket_approved',
            'ticket_id'       => $this->ticket->id,
            'nomor_tiket'     => $this->ticket->nomor_tiket,
            'title'           => "Tiket {$this->ticket->nomor_tiket} Disetujui",
            'judul'           => "Tiket {$this->ticket->nomor_tiket} Disetujui",
            'message'         => $message,
            'pesan'           => $message,
            'resolution_due'  => $this->ticket->resolution_due_at?->toIso8601String(),
            'action_url'      => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $batas = $this->ticket->resolution_due_at
            ? $this->ticket->resolution_due_at->translatedFormat('l, d F Y H:i')
            : '-';

        $mail = (new MailMessage)
            ->subject("Tiket {$this->ticket->nomor_tiket} Disetujui")
            ->greeting("Halo, {$notifiable->nama_lengkap}");

        if ($notifiable->id === $this->ticket->assigned_to) {
            $mail->line("Tiket **{$this->ticket->nomor_tiket}** telah disetujui dan ditugaskan kepada Anda.")
                ->line("**Target resolusi:** {$this->ticket->resolution_hours} jam kerja, batas **{$batas}**.")
                ->action('Lihat Tiket', route('tiket.show', $this->ticket));
        } else {
            $mail->line("Tiket Anda **{$this->ticket->nomor_tiket}** telah disetujui dan sedang dikerjakan oleh staf.")
                ->line("**Target selesai:** {$batas}.")
                ->action('Pantau Perkembangan Tiket', route('tiket.show', $this->ticket));
        }

        return $mail->line('Terima kasih.');
    }
}
