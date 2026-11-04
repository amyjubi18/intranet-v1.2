<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    //esta funcion muestra la vista de login
    public function create(): View
    {
        return view('login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Verificar si el usuario está activo
        $user = Auth::user();
        if (!$user || $user->status != 1) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador para más información.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // Establecer la última actividad al iniciar sesión
        $request->session()->put('last_activity', time());

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'session_token')) {
                $user->session_token = null;
                $user->save();
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $currentModule = config('modules.current');
        $currentHost = $request->getHost();
        $mainHost = parse_url(preg_match('#^https?://#', config('modules.main_url', 'http://172.17.50.219')) ? config('modules.main_url', 'http://172.17.50.219') : 'http://' . config('modules.main_url', 'http://172.17.50.219'), PHP_URL_HOST);

        $isMain = empty($currentModule) || in_array($currentModule, ['main', 'portal', 'all']) || ($currentHost === $mainHost) || str_contains($currentHost, '219') || str_contains($currentHost, 'localhost');

        if (!$isMain) {
            $mainUrl = rtrim(config('modules.main_url', 'http://172.17.50.219'), '/');
            return redirect("{$mainUrl}/logout?logout=1");
        }

        return redirect()->route('login');
    }
}
