<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Pembayaran extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $pembayaran;
    public function __construct($pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Load relationships to avoid N+1 queries
        $pembayaran = $this->pembayaran->load(['tagihan.siswa.wali', 'tagihan_detail']);
        
        return [
            'tagihan_id' => $pembayaran->tagihan_id,
            'wali_id' => $pembayaran->wali_id,
            'pembayaran_id' => $pembayaran->id,
            'title' => 'Pembayaran Tagihan Baru',
            'message' => 'Wali murid ' . ($pembayaran->tagihan->siswa->wali->name ?? 'Tidak Diketahui') . ' telah melakukan pembayaran tagihan sebesar ' . number_format($pembayaran->jumlah_dibayar, 0, ',', '.') . ' untuk siswa ' . ($pembayaran->tagihan->siswa->nama ?? 'Tidak Diketahui'),
            'siswa_nama' => $pembayaran->tagihan->siswa->nama ?? 'Tidak Diketahui',
            'jumlah_dibayar' => $pembayaran->jumlah_dibayar,
            'metode_pembayaran' => $pembayaran->metode_pembayaran,
            'tanggal_bayar' => $pembayaran->tanggal_bayar,
        ];
    }
}
