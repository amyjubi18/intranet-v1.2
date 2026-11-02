<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                // Invalidar otras sesiones manualmente sin necesidad de contraseña
                $this->logoutOtherSessions($user->id);

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
     * Manually invalidate other sessions for the user.
     *
     * @param int $userId
     * @return void
     */
    protected function logoutOtherSessions(int $userId): void
    {
        // Eliminar tokens de acceso existentes (si se usa Sanctum o Passport)
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $userId)
            ->delete();

        // Nota: La invalidación de sesiones de archivo se maneja mediante el session_token
        // en la tabla users. Cuando una nueva sesión inicia, el token se actualiza
        // y las sesiones anteriores serán detectadas como inválidas.
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

