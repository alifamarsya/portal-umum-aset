<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Peringatan SLA: tiket mendekati batas waktu (peringatan dihitung oleh
// SlaService::computeOverallSlaStatus — sisa resolusi <= 4 jam kerja atau
// sisa respons <= 30 menit). Dikirim oleh CheckTicketSlaJob (setiap 15 menit),
// SEKALI per tiket per user (deduplikasi dicek di job, bukan di sini).
// Penerima: staf pelaksana (kalau tiket sedang dikerjakan) atau operator
// (kalau tiket belum direspons), ditambah pimpinan.
// Diadaptasi dari TicketSlaWarningNotification portum: route tickets.show
// -> tiket.show, ticket_number -> nomor_tiket. Ambang "kurang dari 2 jam"
// di pesan portum disesuaikan ke teks sisa waktu yang dikirim job.
class TicketSlaWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $remainingText
    ) {}

    // Database (muncul di lonceng + halaman notifikasi) + mail kalau user punya email.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    // Nilai yang disimpan di kolom 'type' tabel notifications.
    // Dipakai job untuk deduplikasi (cek apakah peringatan untuk tiket ini
    // sudah pernah dikirim ke user yang sama).
    public function databaseType(object $notifiable): string
    {
        return 'sla_warning';
    }

    public function toDatabase($notifiable): array
    {
        $message = "Tiket {$this->ticket->nomor_tiket} mendekati batas SLA (sisa {$this->remainingText}). Segera tindaklanjuti.";

        return [
            'type'           => 'sla_warning',
            'ticket_id'      => $this->ticket->id,
            'nomor_tiket'    => $this->ticket->nomor_tiket,
            'title'          => 'Peringatan SLA – Tiket hampir melebihi batas waktu',
            'judul'          => 'Peringatan SLA – Tiket hampir melebihi batas waktu',
            'message'        => $message,
            'pesan'          => $message,
            'remaining_time' => $this->remainingText,
            'action_url'     => route('tiket.show', $this->ticket),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Peringatan SLA – Tiket {$this->ticket->nomor_tiket} Hampir Melebihi Batas Waktu")
            ->greeting("Halo, {$notifiable->nama_lengkap}")
            ->line("Tiket **{$this->ticket->nomor_tiket}** yang ditugaskan kepada Anda mendekati batas waktu SLA (sisa **{$this->remainingText}**).")
            ->line("Harap segera menindaklanjuti permohonan tersebut sebelum batas waktu SLA terlampaui.")
            ->action('Lihat Detail Tiket', route('tiket.show', $this->ticket))
            ->line('Terima kasih.');
    }
}
