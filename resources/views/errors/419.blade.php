<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url={{ route('login') }}">
    <title>Sesión Expirada - CODECYT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 min-h-screen flex items-center justify-center p-6 text-white">
    <div class="max-w-md w-full bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-8 text-center shadow-2xl">
        <div class="w-20 h-20 rounded-2xl bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center text-4xl mb-5 shadow-inner">
            ⏳
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Página o Sesión Caducada</h1>
        <p class="text-sm text-slate-300 mb-6">
            Por seguridad, tu sesión o formulario ha expirado tras un período de inactividad. Te estamos redirigiendo automáticamente a la pantalla de inicio de sesión...
        </p>
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></div>
            <span class="text-xs text-blue-300 font-medium">Redirigiendo en <span id="countdown">3</span> segundos</span>
        </div>
        <a
            href="{{ route('login') }}"
            class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm transition-all shadow-lg hover:shadow-blue-500/30"
        >
            <span>Iniciar Sesión Ahora</span>
            <span>➔</span>
        </a>
    </div>

    <script>
        let timeLeft = 3;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            timeLeft--;
            if (countdownEl) countdownEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        }, 1000);
    </script>
</body>
</html>
