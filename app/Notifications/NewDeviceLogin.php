<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewDeviceLogin extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $ipAddress,
        protected string $userAgent,
        protected \Carbon\Carbon $loginTime
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
        $device = $this->parseDevice($this->userAgent);
        $browser = $this->parseBrowser($this->userAgent);

        return (new MailMessage)
            ->subject('Nuevo inicio de sesión detectado')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Se ha detectado un nuevo inicio de sesión en tu cuenta.')
            ->line('**Detalles del inicio de sesión:**')
            ->line('- **Fecha y hora:** ' . $this->loginTime->format('d/m/Y H:i:s'))
            ->line('- **Dirección IP:** ' . $this->ipAddress)
            ->line('- **Dispositivo:** ' . $device)
            ->line('- **Navegador:** ' . $browser)
            ->line('')
            ->line('Si no reconoces este inicio de sesión, te recomendamos:')
            ->line('1. Cambiar tu contraseña inmediatamente')
            ->line('2. Revisar tu actividad de inicio de sesión')
            ->line('3. Contactar al administrador del sistema')
            ->action('Cambiar contraseña', route('profile.edit'))
            ->line('')
            ->line('Si fuiste tú quien inició sesión, puedes ignorar este mensaje.');
    }

    /**
     * Parse device type from user agent.
     */
    protected function parseDevice(string $userAgent): string
    {
        if (preg_match('/(mobile|tablet|android|iphone|ipad)/i', $userAgent, $matches)) {
            return ucfirst(strtolower($matches[1]));
        }
        return 'Escritorio';
    }

    /**
     * Parse browser from user agent.
     */
    protected function parseBrowser(string $userAgent): string
    {
        $browsers = [
            'Chrome' => '/Chrome/',
            'Firefox' => '/Firefox/',
            'Safari' => '/Safari/',
            'Edge' => '/Edg/',
            'Opera' => '/OPR/',
            'IE' => '/MSIE/',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return 'Desconocido';
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'login_time' => $this->loginTime->toIso8601String(),
        ];
    }
}

