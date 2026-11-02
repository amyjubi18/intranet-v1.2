<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleFailedLogin
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
    public function handle(Failed $event): void
    {
        $email = $this->request->input('email');

        if (!$email) {
            return;
        }

        $attemptsKey = 'login_attempts_' . md5($email);
        $lockoutKey = 'login_lockout_' . md5($email);

        $maxAttempts = config('auth.max_login_attempts', 5);
        $lockoutMinutes = config('auth.lockout_duration', 30);

        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes($lockoutMinutes));

        Log::warning('Intento de login fallido', [
            'email' => $email,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'intento' => $attempts . '/' . $maxAttempts,
        ]);

        if ($attempts >= $maxAttempts) {
            Cache::put($lockoutKey, now()->addMinutes($lockoutMinutes)->timestamp, now()->addMinutes($lockoutMinutes));
            Cache::forget($attemptsKey);

            Log::warning('Cuenta bloqueada por múltiples intentos fallidos', [
                'email' => $email,
                'ip' => $this->request->ip(),
                'bloqueada_hasta' => now()->addMinutes($lockoutMinutes),
            ]);
        }
    }
}

