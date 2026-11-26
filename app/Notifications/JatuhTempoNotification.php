<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Pengingat jatuh tempo (reminder PKS, invoice sewa, dan pengingat lainnya).
// Dikirim oleh CheckJatuhTempoReminderJob ke pimpinan/aset/pengadaan/superadmin.
//
// PENTING: file ini sebelumnya adalah STUB bawaan Laravel (constructor tanpa
// argumen, channel hanya 'mail', isi email generik berbahasa Inggris).
// Job memanggilnya dengan 4 argumen — argumen itu DIABAIKAN PHP tanpa error,
// sehingga reminder yang terkirim selama ini hanya email generik yang tidak
// jelas isinya, dan TIDAK PERNAH muncul di lonceng aplikasi.
// File ini diganti dengan implementasi asli dari portum (JatuhTempoNotification),
// ditambah kunci 'type', 'title', dan 'message' di toDatabase agar lonceng
// dan halaman notifikasi bisa menampilkannya dengan benar.
class JatuhTempoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $judul,
        public string $kategori,
        public string $tanggalJatuhTempo,
        public string $catatan = '',
    ) {}

    // Dikirim lewat channel database (muncul di lonceng) + mail (opsional,
    // aktif hanya kalau user punya email). Dijalankan di queue worker,
    // bukan di request HTTP.
    public function via($notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    // Nilai yang disimpan di kolom 'type' tabel notifications.
    public function databaseType(object $notifiable): string
    {
        return 'jatuh_tempo';
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'               => 'jatuh_tempo',
            'title'              => "Jatuh Tempo: {$this->judul}",
            'judul'              => $this->judul,
            'message'            => "{$this->judul} ({$this->kategori}) jatuh tempo {$this->tanggalJatuhTempo}. " . ($this->catatan ?: 'Segera tindak lanjuti sebelum jatuh tempo.'),
            'pesan'              => "{$this->judul} ({$this->kategori}) jatuh tempo {$this->tanggalJatuhTempo}. " . ($this->catatan ?: 'Segera tindak lanjuti sebelum jatuh tempo.'),
            'kategori'           => $this->kategori,
            'tanggal_jatuh_tempo' => $this->tanggalJatuhTempo,
            'catatan'            => $this->catatan,
            'action_url'         => route('dashboard'),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reminder jatuh tempo: {$this->judul}")
            ->line("Kategori: {$this->kategori}")
            ->line("Jatuh tempo: {$this->tanggalJatuhTempo}")
            ->line($this->catatan ?: 'Segera tindak lanjuti sebelum jatuh tempo.');
    }
}
