<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Notifications\NewDeviceLogin as NewDeviceLoginNotification;

class SecureAuthController extends Controller
{
    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if account is locked
        $user = User::where('email', $request->email)->first();

        if ($user && $user->isLocked()) {
            $minutesRemaining = now()->diffInMinutes($user->locked_until);
            throw ValidationException::withMessages([
                'email' => "Tu cuenta está bloqueada. Intenta en {$minutesRemaining} minutos.",
            ]);
        }

        // Check rate limit
        $this->checkRateLimit($request);

        // Attempt login
        if (Auth::attempt($this->credentials($request), $request->boolean('remember'))) {
            $this->clearRateLimit($request);

            // Record successful login
            $user = Auth::user();
            $user->recordLogin($request->ip(), $request->userAgent());

            // Check for new device
            if ($user->isNewDeviceLogin($request->ip(), $request->userAgent())) {
                $user->notify(new NewDeviceLoginNotification(
                    $request->ip(),
                    $request->userAgent(),
                    now()
                ));
            }

            $request->session()->regenerate();

            return redirect()->intended(route('inicio', absolute: false));
        }

        // Record failed login
        if ($user) {
            $user->recordFailedLogin(
                $request->ip(),
                $request->userAgent(),
                'Credenciales incorrectas'
            );
        }

        $this->hitRateLimit($request);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Get the needed authorization credentials.
     */
    protected function credentials(Request $request): array
    {
        return $request->only('email', 'password');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        // Clear session token
        if (Auth::check()) {
            $user = Auth::user();
            $user->session_token = null;
            $user->save();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Check rate limit for login attempts.
     */
    protected function checkRateLimit(Request $request): void
    {
        $key = 'login_attempts_' . md5($request->email);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos. Intente en {$seconds} segundos.",
            ]);
        }
    }

    /**
     * Increment rate limit counter.
     */
    protected function hitRateLimit(Request $request): void
    {
        $key = 'login_attempts_' . md5($request->email);
        RateLimiter::hit($key, 60);
    }

    /**
     * Clear rate limit on successful login.
     */
    protected function clearRateLimit(Request $request): void
    {
        $key = 'login_attempts_' . md5($request->email);
        RateLimiter::clear($key);
    }
}

