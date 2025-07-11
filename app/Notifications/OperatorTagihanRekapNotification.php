<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OperatorTagihanRekapNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Rekap Tagihan Masal Selesai',
            'message' => $this->data['keterangan'] ?? 'Generate tagihan masal selesai.',
            'jumlah_siswa' => $this->data['jumlah_siswa'] ?? 0,
            'tanggal_mulai' => $this->data['tanggal_mulai'] ?? null,
            'tanggal_akhir' => $this->data['tanggal_akhir'] ?? null,
            'keterangan' => $this->data['keterangan'] ?? null,
        ];
    }
} 