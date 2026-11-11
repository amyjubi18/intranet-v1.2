<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="login-page">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }} - Inicio de Sesión</title>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Fuente -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
</head>
<body class="flex items-center justify-center login-page">

<!-- Fondo de red tecnológica -->
<div class="network-bg">
<!-- Nodos y líneas generados dinámicamente -->
</div>

<!-- Contenedor principal -->
<div class="w-full max-w-md px-6 slide-up">

<!-- Logo CODECYT -->
<div class="text-center mb-8 logo-container py-4">
<div class="inline-block">

<div class="flex items-center gap-3">
<img src="{{asset('img/Logo.png')}}" class="h-20 w-auto" alt="Logo">
</div>
</div>
<h1 class="text-2xl font-bold text-white mt-4 mb-2">Corporación CODECYT, S.A.</h1>
<p class="text-sm text-slate-400">Sistema de Gestión Empresarial</p>
</div>

<!-- Tarjeta de login -->
<div class=" rounded-2xl p-8 fade-in glass" style="background-color:rgba(255, 255, 255, 0.62)">
<div class="text-center mb-6 ">
<h2 class="text-xl font-bold text-black mb-2">¡Bienvenido al Sistema!</h2>
<p class="text-sm text-slate-700">Ingresa tus credenciales para continuar</p>
</div>

<!-- Session Status -->
@if (session('status'))
<div class="mb-4 text-sm text-green-400 bg-green-900/20 border border-green-800 rounded-lg p-3">
{{ session('status') }}
</div>
@endif

<!-- Session Timeout Message -->
@if (session('message') || request('timeout'))
<div class="mb-4 text-sm text-amber-900 bg-amber-50 border border-amber-300 rounded-xl p-3.5 flex items-center gap-2.5 shadow-sm">
<span class="text-xl">⏳</span>
<span>{{ session('message') ?? 'Tu sesión ha expirado tras 20 minutos de inactividad o por cierre del navegador.' }}</span>
</div>
@endif

<!-- Error Message -->
@if (session('error'))
<div class="mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-xl p-3.5 flex items-center gap-2.5 shadow-sm">
<span class="text-xl">⚠️</span>
<span>{{ session('error') }}</span>
</div>
@endif

<form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-5">
@csrf

<!-- Campo de email -->
<div>
<label class="form-label text-black">Correo Electrónico</label>
<input
type="email"
name="email"
class="form-input bg-blue-100 border-none text-gray-700"
placeholder="usuario@codecyt.com"
value="{{ old('email') }}"
style="color: black"
required
autofocus
autocomplete="username"
>
@error('email')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>

<!-- Campo de contraseña -->
<div class="mt-4">
<label class="form-label text-black">Contraseña</label>
<div class="relative">
<input
type="password"
name="password"
id="passwordInput"
class="form-input pr-12 bg-blue-100 border-none text-gray-700"
placeholder="••••••••"
style="color: black"
required
autocomplete="current-password"
>
<button
type="button"
id="togglePassword"
class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-white transition-colors"
title="Mostrar/Ocultar contraseña"
>
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
<circle cx="12" cy="12" r="3"/>
</svg>
</button>
</div>
@error('password')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>

<!-- Aviso de Seguridad: Cierre de Sesión Automático -->
<div class="mt-4 flex items-center gap-2 text-xs text-slate-700 bg-blue-50/70 p-2.5 rounded-xl border border-blue-200/60">
<svg class="w-4 h-4 text-blue-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
</svg>
<span>La sesión se cerrará automáticamente tras <strong>20 min de inactividad</strong> o al <strong>apagar el equipo</strong>.</span>
</div>

<!-- Enlace de contraseña olvidada -->
<div class="text-right mt-4">
@if (Route::has('password.request'))
<a href="{{ route('password.request') }}" class=" text-blue-900">
¿Olvidó su contraseña?
</a>
@endif
</div>

<!-- Botón de inicio de sesión -->
<button type="submit" class="btn btn-primary mt-6">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
<polyline points="10,17 15,12 10,7"/>
<line x1="15" y1="12" x2="3" y2="12"/>
</svg>
Iniciar Sesión
</button>
</form>
</div>

<!-- Footer -->
<div class="text-center mt-6 text-xs text-slate-500">
<p>&copy; 2025 Corporación CODECYT, S.A. - Todos los derechos reservados</p>
<p class="mt-1">Versión 2.1.0</p>
</div>
</div>

<!-- Toast de notificaciones -->
<div id="toast" class="toast">

</div>

<script>
// Generar fondo de red tecnológica
function createNetworkBackground() {
const networkBg = document.querySelector('.network-bg');
const nodeCount = 15;
const lineCount = 8;

// Crear nodos
for (let i = 0; i < nodeCount; i++) {
const node = document.createElement('div');
node.className = 'network-node';
node.style.left = Math.random() * 100 + '%';
node.style.top = Math.random() * 100 + '%';
node.style.animationDelay = Math.random() * 2 + 's';
networkBg.appendChild(node);
}

// Crear líneas
for (let i = 0; i < lineCount; i++) {
const line = document.createElement('div');
line.className = 'network-line';
line.style.left = '0';
line.style.top = Math.random() * 100 + '%';
line.style.width = '100%';
line.style.animationDelay = Math.random() * 3 + 's';
networkBg.appendChild(line);
}
}

// Sistema de toast
function showToast(message, type = 'info') {
const toast = document.querySelector('#toast');
const msg = document.querySelector('#toastMsg');

const icons = {
success: '✅',
error: '❌',
warning: '⚠️',
info: 'ℹ️'
};

icon.textContent = icons[type] || icons.info;
msg.textContent = message;

toast.classList.add('show');

setTimeout(() => {
toast.classList.remove('show');
}, 4000);
}

// Toggle de contraseña
document.getElementById('togglePassword').addEventListener('click', function() {
const passwordInput = document.getElementById('passwordInput');
const isPassword = passwordInput.type === 'password';

passwordInput.type = isPassword ? 'text' : 'password';

// Cambiar icono
this.innerHTML = isPassword ?
`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
<line x1="1" y1="1" x2="23" y2="23"/>
</svg>` :
`<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
<circle cx="12" cy="12" r="3"/>
</svg>`;
});

// Manejo del formulario de login
document.getElementById('loginForm').addEventListener('submit', function(e) {
// No prevenir el envío, dejar que Laravel maneje la autenticación
// Solo agregar validación básica si es necesario
const email = this.email.value;
const password = this.password.value;

if (!email || !password) {
e.preventDefault();
showToast('Por favor, complete todos los campos', 'warning');
return;
}

// Mostrar loading en el botón
const submitBtn = this.querySelector('button[type="submit"]');
const originalText = submitBtn.innerHTML;

submitBtn.innerHTML = `
<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M21 12a9 9 0 11-6.219-8.56"/>
</svg>
Verificando...
`;
submitBtn.disabled = true;

// Laravel manejará el envío y redirección
});

// Manejo de contraseña olvidada (ya enlazado en HTML)

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    createNetworkBackground();
});

// Evitar error 419 (Página caducada): si se navega hacia atrás desde el historial o caché bfcache
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// Si la pestaña de inicio de sesión queda abierta e inactiva por más de 15 minutos, refrescar al volver para renovar el token CSRF
let pageLoadTime = Date.now();
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        const minutesInactive = (Date.now() - pageLoadTime) / (1000 * 60);
        if (minutesInactive >= 15) {
            window.location.reload();
        }
    }
});
</script>
</body>
</html>
