<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AccountLockout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar en rutas de login
        if (!$this->isLoginRoute($request)) {
            return $next($request);
        }

        $email = $request->input('email');

        if (!$email) {
            return $next($request);
        }

        $lockoutKey = 'login_lockout_' . md5($email);
        $attemptsKey = 'login_attempts_' . md5($email);

        // Verificar si la cuenta está bloqueada
        if (Cache::has($lockoutKey)) {
            $lockedUntil = Cache::get($lockoutKey);
            $minutesRemaining = ceil(($lockedUntil - now()->timestamp) / 60);

            return redirect()->route('login')
                ->withErrors(['email' => "Cuenta bloqueada. Intente nuevamente en {$minutesRemaining} minutos."])
                ->withInput($request->except('password'));
        }

        return $next($request);
    }

    /**
     * Handle failed login attempt.
     */
    public function handleFailedAttempt(Request $request): void
    {
        $email = $request->input('email');

        if (!$email) {
            return;
        }

        $attemptsKey = 'login_attempts_' . md5($email);
        $lockoutKey = 'login_lockout_' . md5($email);

        $maxAttempts = config('auth.max_login_attempts', 5);
        $lockoutMinutes = config('auth.lockout_duration', 30);

        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes($lockoutMinutes));

        if ($attempts >= $maxAttempts) {
            Cache::put($lockoutKey, now()->addMinutes($lockoutMinutes)->timestamp, now()->addMinutes($lockoutMinutes));
            Cache::forget($attemptsKey);
        }
    }

    /**
     * Clear lockout on successful login.
     */
    public function clearLockout(Request $request): void
    {
        $email = $request->input('email');

        if ($email) {
            Cache::forget('login_lockout_' . md5($email));
            Cache::forget('login_attempts_' . md5($email));
        }
    }

    /**
     * Check if the request is for login.
     */
    protected function isLoginRoute(Request $request): bool
    {
        $loginRoutes = [
            'login',
            'auth.login',
            'post.login',
        ];

        return in_array($request->route()?->getName(), $loginRoutes) ||
               $request->is('login') ||
               $request->is('*/login');
    }
}

