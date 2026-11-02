<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SSOController extends Controller
{
    /**
     * Redirige al usuario al módulo solicitado generando un token firmado temporal.
     */
    public function go(Request $request, string $module)
    {
        $urls = [
            'admin' => config('modules.admin_url', 'http://172.17.50.250'),
            'gh'    => config('modules.gh_url', 'http://172.17.50.251'),
            'gtic'  => config('modules.gtic_url', 'http://172.17.50.252'),
        ];

        if (!array_key_exists($module, $urls)) {
            abort(404, 'Módulo no válido');
        }

        $targetBaseUrl = rtrim($urls[$module], '/');
        $userId = Auth::id();
        $expires = now()->addSeconds(60)->timestamp;

        // Crear firma HMAC usando el APP_KEY compartido
        $payload = "user={$userId}&expires={$expires}";
        $signature = hash_hmac('sha256', $payload, config('app.key'));

        return redirect("{$targetBaseUrl}/sso/verify?{$payload}&signature={$signature}");
    }

    /**
     * Recibe el token firmado, verifica la firma y autentica al usuario automáticamente.
     */
    public function verify(Request $request)
    {
        $userId = $request->query('user');
        $expires = (int) $request->query('expires');
        $signature = $request->query('signature');

        // Validar expiración (máximo 60 segundos)
        if (!$userId || !$expires || !$signature || now()->timestamp > $expires) {
            return redirect()->route('login')->withErrors(['sso' => 'El enlace de inicio de sesión ha expirado.']);
        }

        // Verificar la firma criptográfica con el APP_KEY compartido
        $payload = "user={$userId}&expires={$expires}";
        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));

        if (!hash_equals($expectedSignature, $signature)) {
            return redirect()->route('login')->withErrors(['sso' => 'Firma de autenticación no válida.']);
        }

        // Autenticar al usuario en este contenedor
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['sso' => 'Usuario no encontrado.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity', time());

        if (Schema::hasColumn('users', 'session_token')) {
            $user->session_token = $request->session()->getId();
            $user->save();
        }

        return redirect()->route('inicio');
    }
}
