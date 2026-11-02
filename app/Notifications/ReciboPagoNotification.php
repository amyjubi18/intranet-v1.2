<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ReciboPagoNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Solicitud de Recibo de Pago')
            ->view('emails.recibo-pago', [
                'user' => $this->user,
            ]);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail($notifiable): array
    {
        // Enviar a tecnologia@codecyt.gob.ve en lugar del correo del usuario
        return ['tecnologia@codecyt.gob.ve'];
    }

    public function toArray($notifiable)
    {
        return [];
    }
}

