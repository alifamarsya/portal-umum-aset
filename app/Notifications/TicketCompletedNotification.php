<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi saat staf menandai tiket sebagai Selesai.
// Penerima: PEMOHON, agar memeriksa hasil lalu mengonfirmasi dan menutup tiket.
// Diadaptasi dari TicketCompletedNotification portum, DENGAN PERUBAHAN PENTING:
// portum menyertakan batas konfirmasi 2x24 jam kerja (karena portum punya
// auto-close). Portal TIDAK punya auto-close dan TIDAK punya batas konfirmasi,
// jadi pesan di sini tidak menyebut tenggat apa pun — pemohon menutup tiket
// secara manual kapan pun setelah memeriksa hasilnya.
class TicketCompletedNotification extends Notification
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
        return 'ticket_completed';
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'                  => 'ticket_completed',
            'ticket_id'             => $this->ticket->id,
            'nomor_tiket'           => $this->ticket->nomor_tiket,
            'title'                 => "Pekerjaan Tiket {$this->ticket->nomor_tiket} Telah Selesai",
            'judul'                 => "Pekerjaan Tiket {$this->ticket->nomor_tiket} Telah Selesai",
            'message'               => "Staf pelaksana telah menandai tiket {$this->ticket->nomor_tiket} sebagai Selesai. Silakan periksa hasilnya, lalu konfirmasi dan tutup tiket.",
            'pesan'                 => "Staf pelaksana telah menandai tiket {$this->ticket->nomor_tiket} sebagai Selesai. Silakan periksa hasilnya, lalu konfirmasi dan tutup tiket.",
            'resolution_sla_status' => $this->ticket->resolution_sla_status,
            'action_url'            => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Pekerjaan Tiket {$this->ticket->nomor_tiket} Telah Selesai — Butuh Konfirmasi Anda")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Permintaan tiket Anda dengan nomor **{$this->ticket->nomor_tiket}** telah diselesaikan oleh staf terkait.")
            ->line("**Uraian Permintaan:** {$this->ticket->deskripsi}")
            ->line("Harap periksa hasilnya, lalu konfirmasi dan tutup tiket ini.")
            ->action('Konfirmasi & Tutup Tiket', route('tiket.show', $this->ticket))
            ->line('Terima kasih telah menggunakan layanan Divisi Umum Bank Sulteng.');
    }
}
