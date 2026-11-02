<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class PreventConcurrentSessions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.prevent_concurrent_sessions', true)) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = Session::getId();

            // Si el usuario tiene una sesión activa y es diferente
            if ($user->session_token && $user->session_token !== $currentSessionId) {
                // Invalidar la sesión anterior
                Auth::logoutOtherDevices($user->getAuthPassword(), 'session_expired');

                // Regenerar token CSRF
                $request->session()->regenerateToken();
            }

            // Actualizar session token
            $user->session_token = $currentSessionId;
            $user->save();
        }

        return $next($request);
    }

    /**
     * Handle user logout - clear session token.
     */
    public function handleUserLogout(Request $request): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->session_token = null;
            $user->save();
        }
    }
}

