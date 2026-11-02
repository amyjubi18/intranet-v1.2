<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="login-page">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }} - Recuperar Contraseña</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
</head>
<body class="flex items-center justify-center login-page">

<!-- Fondo de red tecnológica -->
<div class="network-bg"></div>

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

<!-- Tarjeta de recuperación -->
<div class="glass rounded-2xl p-8 fade-in bg-white">
<div class="text-center mb-6">
<h2 class="text-xl font-bold text-black mb-2">¿Olvidaste tu contraseña?</h2>
<p class="text-sm text-slate-700">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
</div>

<!-- Session Status -->
@if (session('status'))
<div class="mb-4 text-sm text-green-400 bg-green-900/20 border border-green-800 rounded-lg p-3">
{{ session('status') }}
</div>
@endif

<form method="POST" action="{{ route('password.email') }}">
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
required
autofocus
autocomplete="username"
>
@error('email')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>

<!-- Botón de enviar -->
<button type="submit" class="btn btn-primary mt-6">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
<polyline points="22,6 12,13 2,6"/>
</svg>
Enviar Enlace de Restablecimiento
</button>
</form>

<!-- Enlace volver al login -->
<div class="text-center mt-6">
<a href="/" class=" text-blue-800">
← Volver al inicio de sesión
</a>
</div>
</div>

<!-- Footer -->
<div class="text-center mt-6 text-xs text-slate-500">
<p>&copy; 2025 Corporación CODECYT, S.A. - Todos los derechos reservados</p>
</div>
</div>

<!-- Toast de notificaciones -->
<div id="toast" class="toast">
<div class="glass rounded-xl px-4 py-3 flex items-center gap-3 min-w-[300px]">
<div id="toastIcon">ℹ️</div>
<div id="toastMsg" class="text-sm flex-1"></div>
</div>
</div>

<script>
// Generar fondo de red tecnológica
function createNetworkBackground() {
const networkBg = document.querySelector('.network-bg');
const nodeCount = 15;
const lineCount = 8;

for (let i = 0; i < nodeCount; i++) {
const node = document.createElement('div');
node.className = 'network-node';
node.style.left = Math.random() * 100 + '%';
node.style.top = Math.random() * 100 + '%';
node.style.animationDelay = Math.random() * 2 + 's';
networkBg.appendChild(node);
}

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
const icon = document.querySelector('#toastIcon');
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

// Manejo del formulario
document.querySelector('form').addEventListener('submit', function(e) {
const submitBtn = this.querySelector('button[type="submit"]');
const originalText = submitBtn.innerHTML;

submitBtn.innerHTML = `
<svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
<path d="M21 12a9 9 0 11-6.219-8.56"/>
</svg>
Enviando...
`;
submitBtn.disabled = true;
});

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
createNetworkBackground();
});
</script>
</body>
</html>
