<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class BirthdayWishNotification extends Notification
{
    use Queueable;

    protected $birthdayUser;
    protected $senderUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $birthdayUser, User $senderUser)
    {
        $this->birthdayUser = $birthdayUser;
        $this->senderUser = $senderUser;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎂 ¡Feliz Cumpleaños! - Te han enviado felicitaciones')
            ->greeting('¡Feliz Cumpleaños, ' . $this->birthdayUser->name . '! 🎉')
            ->line($this->senderUser->name . ' ' . $this->senderUser->lastname . ' te ha enviado una felicitación de cumpleaños.')
            ->line('Desde el área de: ' . ($this->birthdayUser->departamento ?? 'Sin asignar'))
            ->line('')
            ->line('¡Que tu día esté lleno de alegría y éxitos en este nuevo año de vida!')
            ->salutation('Saludoscordiales, CODECYT');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'birthday_wish',
            'birthday_user_id' => $this->birthdayUser->id,
            'sender_user_id' => $this->senderUser->id,
            'sender_name' => $this->senderUser->name . ' ' . $this->senderUser->lastname,
            'message' => $this->senderUser->name . ' ' . $this->senderUser->lastname . ' te desea un feliz cumpleaños!',
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'birthday_wish',
            'birthday_user_id' => $this->birthdayUser->id,
            'sender_user_id' => $this->senderUser->id,
            'sender_name' => $this->senderUser->name . ' ' . $this->senderUser->lastname,
            'message' => $this->senderUser->name . ' ' . $this->senderUser->lastname . ' te desea un feliz cumpleaños!',
        ];
    }
}

