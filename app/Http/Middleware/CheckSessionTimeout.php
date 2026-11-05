<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Manejar la caducidad de la sesión por inactividad (20 minutos)
     * y asegurar el cierre al desconectar o apagar el equipo.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $timeoutMinutes = (int) config('session.lifetime', 20);
            $timeoutSeconds = $timeoutMinutes * 60; // 20 min = 1200 segundos

            // Comprobar si ha transcurrido el tiempo límite de inactividad
            if ($lastActivity && (time() - $lastActivity) > $timeoutSeconds) {
                $user = Auth::user();
                if ($user && Schema::hasColumn('users', 'session_token')) {
                    $user->session_token = null;
                    $user->save();
                }

                Auth::guard('web')->logout();
                Session::invalidate();
                Session::regenerateToken();

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'session_timeout',
                        'message' => 'Tu sesión ha expirado por inactividad (20 minutos).'
                    ], 401);
                }

                $mainLoginUrl = rtrim(config('modules.main_url', 'http://172.17.50.219'), '/') . '/login?timeout=1';
                return redirect($mainLoginUrl)->with('message', 'Tu sesión ha expirado tras 20 minutos de inactividad.');
            }

            // Peticiones automáticas en segundo plano (como polling de notificaciones o métricas)
            // NO deben reiniciar el reloj de inactividad del usuario, para que si deja el equipo
            // desatendido con la pestaña abierta, la sesión se cierre efectivamente a los 20 minutos.
            $isBackgroundPoll = $request->is('api/notificaciones/unread-count')
                             || $request->is('api/dashboard/metrics');

            if (!$isBackgroundPoll) {
                Session::put('last_activity', time());
            }
        }

        return $next($request);
    }
}
