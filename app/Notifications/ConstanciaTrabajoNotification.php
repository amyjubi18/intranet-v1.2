<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class ConstanciaTrabajoNotification extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Send the notification via email.
     */
    public function toMail($notifiable)
    {
        // Usar Mail::send directamente para versiones antiguas de Laravel
        Mail::send('emails.constancia-trabajo', ['user' => $this->user], function ($message) {
            $message->from(
                config('mail.from.address', 'hello@example.com'),
                config('mail.from.name', 'Example')
            );
            $message->to('tecnologia@codecyt.gob.ve');
            $message->subject('Constancia de Trabajo');
        });

        return true;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
