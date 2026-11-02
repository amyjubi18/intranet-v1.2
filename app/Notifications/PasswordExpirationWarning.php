<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordExpirationWarning extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected int $daysRemaining
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu contraseña está por caducar')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Tu contraseña vencerá en **' . $this->daysRemaining . ' días**.')
            ->line('Para evitar que tu cuenta sea bloqueada, te recomendamos cambiar tu contraseña.')
            ->action('Cambiar contraseña ahora', route('profile.edit'))
            ->line('')
            ->line('Si no cambias tu contraseña antes de la fecha de caducidad, deberá ser restablecida por un administrador.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $notifiable->password_expires_at?->toIso8601String(),
        ];
    }
}

