<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Models\Tagihan;

class TagihanNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    private $tagihan;
    public function __construct($tagihan)
    {
        $this->tagihan = $tagihan;
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

        $tagihan = $this->tagihan->load(['siswa.wali', 'tagihan_details']);

        return [
            'tagihan_id' => $tagihan->id,
            'wali_id' => $tagihan->siswa->wali_id,
            'tagihan_id' => $tagihan->id,
            'title' => 'Tagihan Baru',
            'message' => 'Tagihan baru sebesar ' . number_format($tagihan->jumlah_tagihan, 0, ',', '.') . ' telah diterbitkan untuk siswa ' . ($tagihan->siswa->nama ?? 'Tidak Diketahui'),
            'siswa_nama' => $tagihan->siswa->nama ?? 'Tidak Diketahui',
            'jumlah_tagihan' => $tagihan->jumlah_tagihan,
            'tanggal_tagihan' => Carbon::parse($tagihan->tanggal_tagihan)->translatedFormat('d F Y'),
            'tanggal_jatuh_tempo' => Carbon::parse($tagihan->tanggal_jatuh_tempo)->translatedFormat('d F Y'),
            'status' => $tagihan->status,
            'keterangan' => $tagihan->keterangan,
        ];
    }
}
