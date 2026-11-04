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
     * Redirige al usuario al módulo solicitado generando un token firmado temporal
     * y permitiendo aterrizar directamente en una ruta específica vía parámetro redirect.
     */
    public function go(Request $request, string $module)
    {
        $urls = [
            'admin' => config('modules.admin_url', 'http://172.17.50.250'),
            'gh'    => config('modules.gh_url', 'http://172.17.50.251'),
            'gtic'  => config('modules.gtic_url', 'http://172.17.50.252'),
        ];

        if (!array_key_exists($module, $urls)) {
            return redirect('/inicio');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        // Validación de permisos por módulo
        $isAuthorized = false;
        if ($module === 'admin') {
            $isAuthorized = $user->can('oga');
        } elseif ($module === 'gh') {
            $isAuthorized = $user->can('ogh');
        } elseif ($module === 'gtic') {
            $isAuthorized = $user->can('gtic');
        }

        if (!$isAuthorized) {
            return redirect('/inicio')->with('error', 'No tienes permisos asignados para acceder a ese entorno.');
        }

        $targetPath = $request->query('redirect', '/inicio');
        // Validar que la ruta sea relativa y segura (evita open-redirect hacia dominios externos)
        if (!str_starts_with($targetPath, '/') || str_starts_with($targetPath, '//')) {
            $targetPath = '/inicio';
        }

        $rawTarget = trim($urls[$module]);
        $targetUrl = preg_match('#^https?://#', $rawTarget) ? $rawTarget : 'http://' . $rawTarget;
        $targetBaseUrl = rtrim($targetUrl, '/');
        $targetHost = parse_url($targetBaseUrl, PHP_URL_HOST);
        $targetPort = parse_url($targetBaseUrl, PHP_URL_PORT) ?: 80;

        // Si ya estamos en el mismo contenedor, ir a la ruta directamente sin bucles
        if ($targetHost === $request->getHost()) {
            return redirect($targetPath);
        }

        // Health-check / Socket probe rápido al contenedor destino
        $probe = @fsockopen($targetHost, $targetPort, $errno, $errstr, 1.2);
        if (!$probe) {
            $moduleNames = [
                'admin' => 'Administración',
                'gh'    => 'Gestión Humana',
                'gtic'  => 'GTIC',
            ];
            $name = $moduleNames[$module] ?? strtoupper($module);
            return redirect('/inicio')->with('error', "El módulo de {$name} ({$targetHost}) está fuera de servicio o no responde.");
        }
        fclose($probe);

        $userId = $user->id;
        $expires = now()->addSeconds(60)->timestamp;

        // Crear firma HMAC usando el APP_KEY compartido incluyendo la ruta destino
        $payload = "user={$userId}&expires={$expires}&redirect=" . urlencode($targetPath);
        $signature = hash_hmac('sha256', $payload, config('app.key'));

        return redirect("{$targetBaseUrl}/sso/verify?{$payload}&signature={$signature}");
    }

    /**
     * Recibe el token firmado, verifica la firma, autentica al usuario automáticamente
     * y lo redirige a la ruta específica solicitada.
     */
    public function verify(Request $request)
    {
        $mainUrl = rtrim(config('modules.main_url', 'http://172.17.50.219'), '/');
        $userId = $request->query('user');
        $expires = (int) $request->query('expires');
        $signature = $request->query('signature');
        
        $targetPath = $request->query('redirect', '/inicio');
        if (!str_starts_with($targetPath, '/') || str_starts_with($targetPath, '//')) {
            $targetPath = '/inicio';
        }

        // Si ya está autenticado como este mismo usuario, ir al destino directamente
        if (Auth::check() && Auth::id() == $userId) {
            return redirect($targetPath);
        }

        // Validar expiración (máximo 60 segundos)
        if (!$userId || !$expires || !$signature || now()->timestamp > $expires) {
            $msg = 'El enlace de inicio de sesión ha expirado o es inválido.';
            return redirect("{$mainUrl}/inicio?sso_error=" . urlencode($msg))->with('error', $msg);
        }

        // Verificar la firma criptográfica con el APP_KEY compartido
        $payload = "user={$userId}&expires={$expires}&redirect=" . urlencode($targetPath);
        $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));

        if (!hash_equals($expectedSignature, $signature)) {
            // Compatibilidad en caso de firma sin parámetro redirect
            $fallbackPayload = "user={$userId}&expires={$expires}";
            $fallbackSignature = hash_hmac('sha256', $fallbackPayload, config('app.key'));
            if (!hash_equals($fallbackSignature, $signature)) {
                $msg = 'Firma de autenticación no válida entre contenedores (revisar APP_KEY).';
                return redirect("{$mainUrl}/inicio?sso_error=" . urlencode($msg))->with('error', $msg);
            }
        }

        // Autenticar al usuario en este contenedor
        $user = User::find($userId);
        if (!$user) {
            $msg = 'Usuario no encontrado en la base de datos central.';
            return redirect("{$mainUrl}/inicio?sso_error=" . urlencode($msg))->with('error', $msg);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity', time());

        if (Schema::hasColumn('users', 'session_token')) {
            $user->session_token = $request->session()->getId();
            $user->save();
        }

        return redirect($targetPath);
    }
}
