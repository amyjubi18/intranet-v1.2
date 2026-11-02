<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleSuccessfulLogin
{
    /**
     * The request instance.
     */
    protected $request;

    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user) {
            return;
        }

        // Limpiar el bloqueo de cuenta
        $email = $user->email;
        $lockoutKey = 'login_lockout_' . md5($email);
        $attemptsKey = 'login_attempts_' . md5($email);

        Cache::forget($lockoutKey);
        Cache::forget($attemptsKey);

        // Registrar información de login
        Log::info('Login exitoso', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
        ]);

        // Actualizar último login en el usuario
        $user->update([
            'last_login_ip' => $this->request->ip(),
            'last_login_at' => now(),
            'last_login_device' => $this->parseDevice($this->request->userAgent()),
        ]);
    }

    /**
     * Parse device info from user agent.
     */
    protected function parseDevice(string $userAgent): string
    {
        if (preg_match('/(mobile|tablet|android|iphone|ipad)/i', $userAgent, $matches)) {
            return ucfirst(strtolower($matches[1]));
        }

        return 'Desktop';
    }
}

