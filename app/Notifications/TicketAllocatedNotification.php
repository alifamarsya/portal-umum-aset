<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi saat operator mengalokasikan tiket ke sebuah bagian.
// Penerima: KABAG bagian tersebut (agar segera meninjau dan menyetujui)
// dan PEMOHON (agar tahu tiketnya sudah diterima dan diteruskan).
// Diadaptasi dari TicketAllocatedNotification portum:
// route tickets.show -> tiket.show, ticket_number -> nomor_tiket.
class TicketAllocatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $departmentName
    ) {}

    // Database (muncul di lonceng + halaman notifikasi) + mail kalau user punya email.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    // Nilai yang disimpan di kolom 'type' tabel notifications.
    public function databaseType(object $notifiable): string
    {
        return 'ticket_allocated';
    }

    public function toDatabase($notifiable): array
    {
        // Pesan dibedakan menurut penerima: kabag mendapat perintah kerja,
        // pemohon mendapat kabar status.
        $message = $notifiable->isKabag()
            ? "Tiket {$this->ticket->nomor_tiket} dialokasikan ke bagian Anda ({$this->departmentName}). Silakan tinjau dan setujui."
            : "Permohonan tiket Anda telah diterima dan diteruskan ke {$this->departmentName} untuk ditindaklanjuti.";

        return [
            'type'            => 'ticket_allocated',
            'ticket_id'       => $this->ticket->id,
            'nomor_tiket'     => $this->ticket->nomor_tiket,
            'title'           => "Tiket {$this->ticket->nomor_tiket} Sedang Ditangani",
            'judul'           => "Tiket {$this->ticket->nomor_tiket} Sedang Ditangani",
            'message'         => $message,
            'pesan'           => $message,
            'department_name' => $this->departmentName,
            'action_url'      => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Tiket {$this->ticket->nomor_tiket} Sedang Ditangani")
            ->greeting("Halo, {$notifiable->nama_lengkap}");

        if ($notifiable->isKabag()) {
            $mail->line("Tiket dengan nomor **{$this->ticket->nomor_tiket}** telah dialokasikan ke bagian Anda (**{$this->departmentName}**).")
                ->line("Silakan tinjau dan setujui tiket ini, lalu tunjuk staf pelaksana.")
                ->action('Tinjau Tiket', route('tiket.show', $this->ticket));
        } else {
            $mail->line("Permohonan tiket Anda dengan nomor **{$this->ticket->nomor_tiket}** telah diverifikasi dan diteruskan ke unit **{$this->departmentName}** untuk ditindaklanjuti.")
                ->action('Pantau Perkembangan Tiket', route('tiket.show', $this->ticket));
        }

        return $mail->line('Terima kasih.');
    }
}
