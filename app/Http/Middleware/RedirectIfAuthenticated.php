<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        // Si la petición a login incluye indicación de logout o proviene de un contenedor secundario (.250, .251, .252)
        $referer = (string) $request->headers->get('referer', '');
        $isFromSecondary = str_contains($referer, '250') || str_contains($referer, '251') || str_contains($referer, '252');

        if ($request->has('logout') || ($request->has('action') && $request->action === 'logout') || $isFromSecondary) {
            foreach ($guards as $guard) {
                Auth::guard($guard)->logout();
            }
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $next($request);
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
