<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $timeout = config('session.lifetime') * 60; // Convertir minutos a segundos

            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                Session::flush();
                return redirect('/')->with('message', 'Tu sesión ha expirado por inactividad.');
            }

            // Actualizar la última actividad
            Session::put('last_activity', time());
        }

        return $next($request);
    }
}
