<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si hay un usuario autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario está inactivo, cerrar sesión
            if ($user->status != 1) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu cuenta ha sido desactivada. Contacta al administrador para más información.'])
                    ->onlyInput('email');
            }
        }

        return $next($request);
    }
}

