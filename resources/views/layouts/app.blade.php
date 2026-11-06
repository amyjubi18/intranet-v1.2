<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Título dinámico de la página -->
    <title>
        @if(View::hasSection('title'))
            @yield('title') - Intranet CODECYT
        @else
            Intranet CODECYT - Sistema de Inventario
        @endif
    </title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
<!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Fuente Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <script type="text/javascript">
    var baseURL = {!!json_encode(url('/'))!!}
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="icon" href="{{asset('img/Logo.png')}}" type="image/png">

    <!-- Scripts -->
     @yield('scripts')
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>

    <style>
        body {
            box-sizing: border-box;
        }

        :root{
            --color-primary:#1e3a8a;
            --color-secondary:#22C55E;
            --color-accent:#F59E0B;
            --bg-from:#f8fafc;
            --bg-to:#e2e8f0;
            --card-bg:rgba(255,255,255,0.9);
            --card-border:rgba(0,0,0,0.1);
            --muted:#64748b;
            --text:#1e293b;
            --link-hover-bg: rgba(0,0,0,0.05);
            --active-bg: rgba(30,58,138,0.1);
            --active-text: #1e3a8a;
        }
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%);
            color:#1e293b;
            overflow-x: hidden;
        }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px); }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border); }
        .btn:active{ transform:translateY(1px); }
        .btn-primary{ background:var(--color-primary); color:#fff; }
        .btn-secondary{ background:rgba(255,255,255,0.8); color:#1e293b; }
        .btn-success{ background:var(--color-secondary); color:#fff; }
        .btn-warning{ background:var(--color-accent); color:#111827; }
        .sidebar{
            position:fixed;
            left:0;
            top:0;
            height:100vh;
            width:280px;
            background: linear-gradient(to bottom, #1e40af, #3730a3);
            border-right:1px solid rgba(255,255,255,0.1);
            backdrop-filter:blur(12px);
            z-index:40;
            transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed{
            transform: translateX(-100%);
        }
        .sidebar-item{
            display:flex;
            align-items:center;
            gap:.75rem;
            padding:.75rem 1.25rem;
            margin:.25rem .75rem;
            border-radius:.75rem;
            transition:.2s;
            cursor:pointer;
            color:#ffffff;
            position:relative;
        }
        .sidebar-item:hover{
            background:rgba(255,255,255,0.1);
            color:#ffffff;
            transform:translateX(4px);
        }
        .sidebar-item.active{
            background:rgba(255,255,255,0.22) !important;
            color:#ffffff !important;
            font-weight:700 !important;
            border-left:4px solid #60a5fa !important;
            padding-left:0.75rem !important;
            box-shadow:0 4px 14px rgba(0,0,0,0.35) !important;
        }

        /* Animaciones para el preloader */
        @keyframes preloaderLogoPulse {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 14px rgba(96, 165, 250, 0.45));
            }
            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 0 30px rgba(96, 165, 250, 0.85));
            }
        }
        .preloader-logo-pulse {
            animation: preloaderLogoPulse 2.4s ease-in-out infinite;
        }
        .wave-parallax > use {
            animation: move-wave-forever 20s cubic-bezier(.55,.5,.45,.5) infinite;
        }
        .wave-parallax > use:nth-child(1) {
            animation-delay: -2s;
            animation-duration: 7s;
        }
        .wave-parallax > use:nth-child(2) {
            animation-delay: -3s;
            animation-duration: 10s;
        }
        .wave-parallax > use:nth-child(3) {
            animation-delay: -4s;
            animation-duration: 13s;
        }
        .wave-parallax > use:nth-child(4) {
            animation-delay: -5s;
            animation-duration: 18s;
        }
        @keyframes move-wave-forever {
            0% { transform: translate3d(-90px, 0, 0); }
            100% { transform: translate3d(85px, 0, 0); }
        }
        .main-content{
            margin-left:280px;
            min-height:100vh;
            transition: margin-left .3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content.sidebar-collapsed{
            margin-left:0 !important;
        }
        #menuToggle{
            display:none;
        }
        .main-content.sidebar-collapsed #menuToggle{
            display:inline-flex !important;
        }
        .stat-card{
            background:var(--card-bg);
            border:1px solid var(--card-border);
            backdrop-filter:blur(8px);
            border-radius:1rem;
            padding:1.5rem;
            transition:.2s;
        }
        .stat-card:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 24px rgba(2,6,23,.15);
        }
        .dropdown {
            position: relative;
        }
        .dropdown-content {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            border-radius: 0.75rem;
            padding: 0.5rem;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 50;
        }
        .dropdown.open .dropdown-content {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: 0.2s;
            cursor: pointer;
            color: var(--muted);
        }
        .dropdown-item:hover {
            background: var(--link-hover-bg);
            color: var(--text);
        }
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .searchable-select {
            position: relative;
            z-index: 99999;
        }
        .searchable-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            border-radius: 0.75rem;
            margin-top: 0.25rem;
            max-height: 300px;
            overflow-y: auto;
            z-index: 999999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .searchable-select-dropdown.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .searchable-select-search {
            padding: 0.75rem;
            border-bottom: 1px solid var(--card-border);
        }
        .searchable-select-search input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text);
            font-size: 0.875rem;
        }
        .searchable-select-search input::placeholder {
            color: var(--muted);
        }
        .searchable-select-options {
            max-height: 200px;
            overflow-y: auto;
        }
        .searchable-select-option {
            padding: 0.75rem;
            cursor: pointer;
            transition: 0.2s;
            color: var(--muted);
            border-bottom: 1px solid var(--card-border);
        }
        .searchable-select-option:last-child {
            border-bottom: none;
        }
        .searchable-select-option:hover {
            background: var(--link-hover-bg);
            color: var(--text);
        }
        .searchable-select-option.selected {
            background: var(--active-bg);
            color: var(--active-text);
        }
        .searchable-select-option.hidden {
            display: none;
        }


        /* Estilos del modal de notificaciones */
        .notification-modal {
            position: fixed;
            top: 70px;
            right: 20px;
            width: 380px;
            animation: slideDown 0.2s ease-out;
            z-index: 9999 !important;
        }

        .notification-modal.show {
            display: block;
        }

        .notification-arrow {
            position: absolute;
            top: -8px;
            right: 20px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid #3b82f6;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animación de fade out para errores */
        .fade-out {
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        /* Scroll personalizado para el modal de notificaciones */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

/* CORRECCIÓN: Dropdown del buscador - forzar z-index máximo */
        #searchResults {
            position: fixed !important;
            z-index: 9999999 !important;
            background: white !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15) !important;
        }

        /* TODO EL SISTEMA EN MAYÚSCULAS (excepto inputs, contraseñas y página de login) */
        body:not(.login-page) .uppercase-all,
        body:not(.login-page) td,
        body:not(.login-page) th,
        body:not(.login-page) .text-sm,
        body:not(.login-page) .text-xs,
        body:not(.login-page) .text-base,
        body:not(.login-page) p,
        body:not(.login-page) span,
        body:not(.login-page) a,
        body:not(.login-page) label,
        body:not(.login-page) h1,
        body:not(.login-page) h2,
        body:not(.login-page) h3,
        body:not(.login-page) h4,
        body:not(.login-page) h5,
        body:not(.login-page) h6,
        body:not(.login-page) .font-bold,
        body:not(.login-page) .font-semibold,
        body:not(.login-page) .font-medium,
        body:not(.login-page) .font-normal {
            text-transform: uppercase !important;
        }

/* EXCEPCIONES: NO mayúsculas (los selects se manejan por separado) */
        body:not(.login-page) input[type="text"],
        body:not(.login-page) input[type="email"],
        body:not(.login-page) input[type="password"],
        body:not(.login-page) input[type="number"],
        body:not(.login-page) input[type="tel"],
        body:not(.login-page) input[type="search"],
        body:not(.login-page) textarea,
        body:not(.login-page) .no-uppercase,
        body:not(.login-page) input::placeholder,
        body:not(.login-page) .placeholder-normal::placeholder {
            text-transform: none !important;
        }

        /* Los SELECTS y sus opciones SÍ en mayúsculas (excepto login) */
        body:not(.login-page) select,
        body:not(.login-page) select option {
            text-transform: uppercase !important;
        }

        /* Mantener campos de contraseña en minúsculas */
        body:not(.login-page) input[type="password"] {
            text-transform: none !important;
        }

        /* RESPONSIVE: Dropdown del buscador */
        @media (max-width: 640px) {
            #searchResults {
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
                max-width: none !important;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            #searchResults {
                left: 20px !important;
                right: 20px !important;
                width: auto !important;
                max-width: none !important;
            }
        }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .main-content.sidebar-collapsed { margin-left: 0 !important; }
            #menuToggle { display: inline-flex !important; }
        }

        /* Responsive para pantallas pequeñas (500px) */
        @media (max-width: 640px) {
            .glass .flex.items-center.justify-between {
                flex-direction: column;
                gap: 1rem;
            }
            .glass .flex.items-center.justify-between .flex.items-center.gap-4:first-child {
                width: 100%;
                justify-content: flex-start;
            }
            .glass .flex.items-center.justify-between .flex.items-center.gap-2 {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body class="scroll-smooth">

    <!-- Preloader Institucional: Fondo azul degradado con animación de olas y logo animado al centro -->
    <div id="page-preloader" class="fixed inset-0 z-[999999] flex flex-col items-center justify-center overflow-hidden transition-all duration-500 pointer-events-auto" style="background: linear-gradient(135deg, #07152b 0%, #0c234a 35%, #1e3a8a 75%, #1e40af 100%);">
        <!-- Contenedor central con logo animado -->
        <div class="relative z-20 flex flex-col items-center justify-center px-4 text-center">
            <div class="relative p-6 sm:p-8 rounded-3xl bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl flex items-center justify-center">
                <img src="{{ asset('img/Logo.png') }}" class="h-24 sm:h-32 md:h-36 w-auto object-contain preloader-logo-pulse" alt="CODECYT">
                <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-blue-400/30 to-cyan-300/30 blur-md pointer-events-none animate-pulse"></div>
            </div>

            <!-- Indicador sutil de carga de 3 puntos -->
            <div class="mt-6 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-cyan-400 animate-bounce" style="animation-delay: 0s;"></span>
                <span class="w-3 h-3 rounded-full bg-blue-300 animate-bounce" style="animation-delay: 0.15s;"></span>
                <span class="w-3 h-3 rounded-full bg-indigo-300 animate-bounce" style="animation-delay: 0.3s;"></span>
            </div>
            <p class="text-xs uppercase tracking-widest text-blue-200/80 font-medium mt-3">Cargando...</p>
        </div>

        <!-- Animación de olas suaves en la parte inferior -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none pointer-events-none z-10" style="height: 140px;">
            <svg class="preloader-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto" style="width: 100%; height: 100%;">
                <defs>
                    <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
                </defs>
                <g class="wave-parallax">
                    <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(37, 99, 235, 0.22)" />
                    <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(59, 130, 246, 0.32)" />
                    <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(96, 165, 250, 0.22)" />
                    <use xlink:href="#gentle-wave" x="48" y="7" fill="rgba(30, 58, 138, 0.6)" />
                </g>
            </svg>
        </div>
    </div>
    <script>
        (function() {
            function removePreloader() {
                const el = document.getElementById('page-preloader');
                if (!el || el.dataset.dismissed) return;
                el.dataset.dismissed = 'true';
                el.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    if (el && el.parentNode) {
                        el.style.display = 'none';
                    }
                }, 550);
            }

            if (document.readyState === 'complete') {
                setTimeout(removePreloader, 350);
            } else {
                window.addEventListener('load', function() {
                    setTimeout(removePreloader, 350);
                });
            }
            // Failsafe: nunca bloquear la pantalla más de 2 segundos
            setTimeout(removePreloader, 2000);
        })();
    </script>

    <!-- Modal de notificaciones (fuera del flujo para aparecer por encima de todo) -->
    @php
        $totalNotificaciones = $totalNotificaciones ?? 0;
    @endphp

    <!-- Modal/Popover de notificaciones -->
    <div id="notificationModal" class="notification-modal fixed z-[9999] hidden">
        <div class="bg-white rounded-xl shadow-2xl border border-gray-200 w-96 overflow-hidden">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <h3 class="font-semibold text-white">Notificaciones</h3>
                    @if(isset($notificaciones) && count($notificaciones) > 0)
                        <span class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">{{ count($notificaciones) }}</span>
                    @endif
                </div>
                <button onclick="toggleNotificationDropdown(event)" class="text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Lista de notificaciones -->
            <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
                @if(isset($notificaciones) && !empty($notificaciones))
                    @foreach($notificaciones as $notif)
                        <!-- Título de categoría -->
                        <div class="px-4 py-2 bg-gray-100 border-b border-gray-200 sticky top-0">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $notif['icono'] }}</span>
                                <span class="text-sm font-semibold text-gray-700">{{ $notif['titulo'] }}</span>
                                @if(isset($notif['cantidad']) && $notif['cantidad'] > 0)
                                    <span class="ml-auto text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">{{ $notif['cantidad'] }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Cumpleaños - Felicitación de cumpleaños -->
                        @if($notif['tipo'] == 'birthday_wish' && isset($notif['is_laravel_notification']) && $notif['is_laravel_notification'])
                            <div class="p-4 border-b border-gray-100 bg-green-50">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-xl">
                                        🎂
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ $notif['mensaje'] }}</p>
                                        @if(isset($notif['hora']))
                                            <p class="text-xs text-gray-500 mt-1">{{ $notif['hora'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        <!-- Perfil Incompleto - Muestra campos faltantes -->
                        @elseif($notif['tipo'] == 'perfil_incompleto' && isset($notif['campos_faltantes']))
                            <div class="p-4 border-b border-gray-100" style="cursor: pointer;" onclick="window.location.href='{{ route('profile.edit') }}'">
                                <p class="text-sm text-gray-600 mb-2">{{ $notif['mensaje'] }}</p>
                                <div class="space-y-1">
                                    @foreach($notif['campos_faltantes'] as $campo)
                                        <div class="flex items-center gap-2 text-xs text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>Completar: {{ $campo }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-xs text-blue-600 mt-2 hover:underline cursor-pointer">Click para completar tu perfil →</p>
                            </div>
                        @elseif(isset($notif['items']) && $notif['items']->isNotEmpty())
                            <!-- Items individuales -->
                            @foreach($notif['items'] as $item)
                                @php
                                    $tipoItem = $notif['tipo_item'] ?? 'default';
                                    $borderColor = match($notif['color'] ?? 'orange') {
                                        'red' => 'border-l-red-500',
                                        'orange' => 'border-l-orange-500',
                                        'yellow' => 'border-l-yellow-500',
                                        'blue' => 'border-l-blue-500',
                                        'purple' => 'border-l-purple-500',
                                        'indigo' => 'border-l-indigo-500',
                                        'gray' => 'border-l-gray-500',
                                        default => 'border-l-orange-500'
                                    };
                                @endphp
                                <div class="p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors border-l-4 {{ $borderColor }}"
                                     onclick="window.location.href='{{ $notif['ruta_ver_todas'] }}'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            @switch($tipoItem)
                                                @case('consumible')
                                                    <p class="text-sm font-medium text-gray-800">{{ $item->cartucho ?? 'N/A' }} - {{ $item->color ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        Stock: {{ $item->total_disponible ?? 0 }} und.
                                                        @if(isset($item->stock_min))
                                                            <span class="text-orange-600">(Mín: {{ $item->stock_min }})</span>
                                                        @endif
                                                    </p>
                                                    @break
                                                @case('articulo')
                                                    <p class="text-sm font-medium text-gray-800">{{ $item->articulo ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        Stock: {{ $item->total_disponible ?? 0 }} und.
                                                        @if(isset($item->stock_min))
                                                            <span class="text-orange-600">(Mín: {{ $item->stock_min }})</span>
                                                        @endif
                                                    </p>
                                                    @break
                                                @case('usuario')
                                                    <p class="text-sm font-medium text-gray-800">{{ $item->name ?? 'N/A' }} {{ $item->lastname ?? '' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        @if(isset($item->cedula)){{ $item->cedula }}@endif
                                                        @if(isset($item->email)) - {{ $item->email }}@endif
                                                    </p>
                                                    @break
                                                @case('bien')
                                                    <p class="text-sm font-medium text-gray-800">{{ $item->descripcion ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        @if(isset($item->n_interno_bien)){{ $item->n_interno_bien }}@endif
                                                        @if(isset($item->marca)) - {{ $item->marca }}@endif
                                                        @if(isset($item->modelo)) {{ $item->modelo }}@endif
                                                        @if(isset($item->condicion)) - <span class="text-red-600">{{ $item->condicion }}</span>@endif
                                                    </p>
                                                    @break
                                                @case('impresora')
                                                    <p class="text-sm font-medium text-gray-800">{{ $item->impresora ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        Stock: {{ $item->stock_total ?? 0 }} und.
                                                    </p>
                                                    @break
                                                @default
                                                    @if(isset($item->cartucho))
                                                        <p class="text-sm font-medium text-gray-800">{{ $item->cartucho }} - {{ $item->color }}</p>
                                                    @elseif(isset($item->articulo))
                                                        <p class="text-sm font-medium text-gray-800">{{ $item->articulo }}</p>
                                                    @else
                                                        <p class="text-sm font-medium text-gray-800">{{ json_encode($item) }}</p>
                                                    @endif
                                                    <p class="text-xs text-gray-500">
                                                        @if(isset($item->total_disponible))
                                                            Stock: {{ $item->total_disponible }} und.
                                                            @if(isset($item->stock_min))
                                                                <span class="text-orange-600">(Mín: {{ $item->stock_min }})</span>
                                                            @endif
                                                        @endif
                                                    </p>
                                            @endswitch
                                        </div>
                                        <div class="flex-shrink-0 ml-2">
                                            @if(isset($item->total_disponible))
                                                @if($item->total_disponible <= 2)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Crítico
                                                    </span>
                                                @elseif($item->total_disponible <= 5)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Bajo
                                                    </span>
                                                @endif
                                            @elseif(isset($item->stock_total))
                                                @if($item->stock_total <= 2)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Crítico
                                                    </span>
                                                @elseif($item->stock_total <= 5)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Bajo
                                                    </span>
                                                @endif
                                            @elseif($tipoItem == 'bien' && isset($item->condicion))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    {{ $item->condicion }}
                                                </span>
                                            @elseif($tipoItem == 'usuario')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    Incompleto
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-gray-500 text-sm">
                                No hay items con stock bajo en esta categoría
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">¡Todo está en orden!</p>
                        <p class="text-xs text-gray-400 mt-1">No hay notificaciones pendientes</p>
                    </div>
                @endif
            </div>

            <!-- Footer - siempre visible si hay notificaciones -->
            @if(isset($notificaciones) && count($notificaciones) > 0)
                <div class="p-3 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-center gap-4">
                        <button onclick="markNotificationsAsRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Marcar todo como leído
                        </button>
                    </div>
                </div>
            @endif
        </div>
        <!-- Flecha decorativa -->
        <div class="notification-arrow"></div>
    </div>

    <!-- Backdrop oscuro -->
    <div id="notificationBackdrop" class="fixed inset-0 bg-black/20 z-[9998] hidden" onclick="closeNotificationModal()"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        @include('layouts.navigation')
    </aside>

    <!-- Backdrop for mobile -->
    <div id="sidebarBackdrop" class=" inset-0 z-30 hidden bg-black/50 lg:hidden" onclick="closeSidebar()"></div>

    <!-- Dropdown del buscador (fuera del main-content para que aparezca sobre todo) -->
    <div id="searchResults" class="hidden fixed bg-white rounded-2xl shadow-2xl border border-slate-200/90 max-h-[460px] overflow-y-auto z-[99999] transition-all">
    </div>

    <!-- Contenido principal -->
    <main class="main-content bg-gradient-to-r from-slate-300 to-slate-500" id="mainContent">
        <!-- Header estilo tarjeta -->
        <div class="p-6">
            <div class="p-6 mb-6 glass rounded-2xl header-container">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <!-- Botón de menú hamburguesa -->
                        <button id="menuToggle" type="button" class="btn btn-secondary shadow-sm hover:bg-white transition-all mr-2" onclick="toggleSidebar()" title="Mostrar menú">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <line x1="3" y1="12" x2="21" y2="12"/>
                                <line x1="3" y1="18" x2="21" y2="18"/>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-2xl font-extrabold">@yield('title', 'Sistema CODECYT')</h1>
                            <p class="text-sm text-slate-400">@yield('subtitle', 'Sistema de Inventario')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto justify-end">
                        <!-- Buscador -->
                        <div class="relative shrink-0" style="z-index: 50;">
                            <input type="text" id="searchInput" placeholder="Buscar..."
                                class="w-32 sm:w-40 md:w-48 lg:w-64 px-2 sm:px-4 py-1.5 sm:py-2 pl-8 sm:pl-10 text-sm glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                autocomplete="off">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute transform -translate-y-1/2 left-2 sm:left-3 top-1/2 text-slate-400">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>

                            <!-- Loading spinner -->
                            <div id="searchLoading" class="hidden absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2">
                                <svg class="animate-spin h-3 w-3 sm:h-4 sm:w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Campana de notificaciones -->
                        @php
                            $totalNotificaciones = $totalNotificaciones ?? 0;
                        @endphp
                        <div class="relative shrink-0">
                            <button class="relative btn btn-secondary p-1.5 sm:p-2 text-blue-900" onclick="toggleNotificationDropdown(event)">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-900" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                                @if($totalNotificaciones > 0)
                                    <span class="notification-badge absolute -top-1 -right-1 flex items-center justify-center w-4 h-4 sm:w-5 sm:h-5 text-xs text-white bg-red-500 rounded-full animate-pulse">
                                        {{ $totalNotificaciones > 99 ? '99+' : $totalNotificaciones }}
                                    </span>
                                @endif
                            </button>
                        </div>

                        <!-- Icono de usuario -->
                        <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                            @auth
                                <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-11 lg:h-11 rounded-lg bg-gradient-to-br from-blue-500 to-blue-100 flex items-center justify-center">
                                    @if(Auth::user()->profile_photo)
                                        <img src="{{ asset('archivos/avatars/' . Auth::user()->profile_photo) }}"
                                             alt="{{ Auth::user()->name }}"
                                             class="w-8 h-8 sm:w-10 sm:h-10 lg:w-11 lg:h-11 rounded-lg object-cover">
                                    @else
                                        <span class="text-white font-bold text-xs">{{ substr(Auth::user()->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-red-700 text-sm p-1.5 sm:p-2">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                            <polyline points="16,17 21,12 16,7"/>
                                            <line x1="21" y1="12" x2="9" y2="12"/>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="btn bg-blue-600 hover:bg-blue-700 text-white border-blue-700 text-sm p-1.5 sm:p-2">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                        <polyline points="10,17 15,12 10,7"/>
                                        <line x1="15" y1="12" x2="3" y2="12"/>
                                    </svg>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-4">
            <!-- Mensajes de sesión -->
            @if(session('datos'))
                <div class="p-4 mb-4 text-green-700 bg-green-100 border border-green-400 rounded-lg">
                    {{ session('datos') }}
                </div>
            @endif

            @if(session('error') || request('sso_error'))
                <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
                    {{ session('error') ?? request('sso_error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="p-4 mb-4 text-blue-700 bg-blue-100 border border-blue-400 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('success') || session('datos') || session('status'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof showToast === 'function') {
                            showToast(@json(session('success') ?: (session('datos') ?: session('status'))), 'success');
                        }
                    });
                </script>
            @endif
            @if(session('error') || request('sso_error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const errMsg = @json(session('error') ?? request('sso_error'));
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Atención',
                                text: errMsg,
                                confirmButtonText: 'Aceptar',
                                confirmButtonColor: '#2563eb',
                                customClass: {
                                    popup: 'rounded-2xl shadow-2xl'
                                }
                            });
                        } else if (typeof showToast === 'function') {
                            showToast(errMsg, 'error');
                        }
                    });
                </script>
            @endif

            @if($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const errors = @json($errors->all());
                        let errorHtml = '<div class="text-left text-sm text-slate-700 mt-2 space-y-1.5">';
                        errors.forEach(function(msg) {
                            errorHtml += '<div class="flex items-start gap-2"><span class="text-red-500 font-bold">•</span><span>' + msg + '</span></div>';
                        });
                        errorHtml += '</div>';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Por favor corrija los siguientes errores:',
                                html: errorHtml,
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#2563eb',
                                customClass: {
                                    popup: 'rounded-2xl shadow-2xl border border-slate-200'
                                }
                            });
                        }
                    });
                </script>
                <div class="p-4 mb-4 text-red-700 bg-red-100 border border-red-400 rounded-lg">
                    <p class="font-bold">Por favor corrija los siguientes errores:</p>
                    <ul class="pl-5 mt-2 list-disc">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </main>

    <!-- Toast de Notificaciones -->
    <div id="toast" class="fixed bottom-5 right-5 z-[99999] hidden transition-all duration-300 transform translate-y-3 opacity-0 pointer-events-none">
        <div class="flex items-center gap-3 px-5 py-3.5 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 max-w-md">
            <div id="toastIcon" class="text-xl shrink-0">✅</div>
            <div id="toastMsg" class="text-sm font-semibold text-slate-700 leading-snug"></div>
            <button type="button" onclick="hideToast()" class="ml-2 text-slate-400 hover:text-slate-600 p-1 rounded-lg transition-colors focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <script>
        // Toast dinámico
        let toastTimeout = null;
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');
            if (!toast || !msgEl) return;

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            if (toastTimeout) {
                clearTimeout(toastTimeout);
            }

            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;

            toast.classList.remove('hidden');
            // Forzar reflow para que la animación CSS aplique suavemente
            void toast.offsetWidth;
            toast.classList.remove('translate-y-3', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');

            toastTimeout = setTimeout(() => {
                hideToast();
            }, 4000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            if (!toast) return;
            if (toastTimeout) clearTimeout(toastTimeout);

            toast.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            toast.classList.add('translate-y-3', 'opacity-0', 'pointer-events-none');

            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300);
        }
        window.showToast = showToast;
        window.showAppToast = showToast;
        window.hideToast = hideToast;
        window.showErrorAlert = function(msg, title = 'Error') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: msg,
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Entendido'
                });
            } else {
                showToast(msg, 'error');
            }
        };

        // URLs base para redirección limpia de sesión y SSO
        const MAIN_PORTAL_LOGIN = @json(rtrim(config('modules.main_url', 'http://172.17.50.219'), '/') . '/login?timeout=1');
        const MAIN_PORTAL_LOGOUT = @json(rtrim(config('modules.main_url', 'http://172.17.50.219'), '/') . '/logout?timeout=1');

        // Interceptor global para capturar 401 (expiración de sesión) y redirigir limpiamente sin alert de error
        (function() {
            const originalFetch = window.fetch;
            window.fetch = async function(...args) {
                try {
                    const response = await originalFetch.apply(this, args);
                    if (response && response.status === 401) {
                        if (!window.location.pathname.includes('/login')) {
                            window.location.href = MAIN_PORTAL_LOGIN;
                        }
                    }
                    return response;
                } catch (err) {
                    throw err;
                }
            };
        })();

        // ==========================================
        // SISTEMA DE NOTIFICACIONES DINÁMICO EN TIEMPO REAL (JS)
        // ==========================================
        let modalAbierto = false;
        let currentUnreadCount = {{ (int)($totalNotificaciones ?? 0) }};

        function updateNotificationBadge(count) {
            currentUnreadCount = Math.max(0, parseInt(count) || 0);
            const badge = document.querySelector('.notification-badge');
            const bellBtn = document.querySelector('button[onclick*="toggleNotificationDropdown"]');
            const headerBadge = document.querySelector('#notificationModal .rounded-full');

            if (currentUnreadCount > 0) {
                const text = currentUnreadCount > 99 ? '99+' : currentUnreadCount;
                if (badge) {
                    badge.textContent = text;
                    badge.style.display = 'flex';
                } else if (bellBtn) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge absolute -top-1 -right-1 flex items-center justify-center w-4 h-4 sm:w-5 sm:h-5 text-xs text-white bg-red-500 rounded-full animate-pulse';
                    newBadge.textContent = text;
                    bellBtn.appendChild(newBadge);
                }
                if (headerBadge) {
                    headerBadge.textContent = text;
                    headerBadge.style.display = 'inline-block';
                }
            } else {
                if (badge) badge.style.display = 'none';
                if (headerBadge) headerBadge.style.display = 'none';
            }
        }

        // Sondeo asíncrono en segundo plano (cada 25 segundos)
        function pollNotifications() {
            if (document.hidden) return; // Pausar cuando la pestaña no está visible

            fetch('/api/notificaciones/unread-count', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res && res.status === 401) {
                    window.location.href = MAIN_PORTAL_LOGIN;
                    return null;
                }
                return res && res.ok ? res.json() : null;
            })
            .then(data => {
                if (data && typeof data.count !== 'undefined') {
                    const newCount = parseInt(data.count) || 0;
                    if (newCount > currentUnreadCount) {
                        showToast(`Nueva notificación: tienes ${newCount} pendiente(s)`, 'info');
                    }
                    updateNotificationBadge(newCount);
                }
            })
            .catch(() => {});
        }

        setInterval(pollNotifications, 25000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) pollNotifications();
        });

        // ==========================================================
        // CONTROL DE INACTIVIDAD DEL USUARIO (20 MINUTOS)
        // ==========================================================
        (function() {
            const INACTIVITY_LIMIT_MS = 20 * 60 * 1000; // 20 minutos (1.200.000 ms)
            const WARNING_BEFORE_MS = 60 * 1000; // Aviso 1 minuto antes (a los 19 minutos)
            let lastUserActivity = Date.now();
            let warningModalShown = false;
            let checkInterval = null;

            function recordUserActivity() {
                lastUserActivity = Date.now();
                if (warningModalShown) {
                    warningModalShown = false;
                    const modal = document.getElementById('inactivityWarningModal');
                    if (modal) modal.classList.add('hidden');
                }
            }

            // Escuchar interacciones reales del usuario
            const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'];
            let throttleTimer = null;
            activityEvents.forEach(evt => {
                window.addEventListener(evt, () => {
                    if (!throttleTimer) {
                        recordUserActivity();
                        throttleTimer = setTimeout(() => { throttleTimer = null; }, 5000); // Máximo 1 actualización cada 5s
                    }
                }, { passive: true });
            });

            // Temporizador para verificar inactividad cada 10 segundos
            checkInterval = setInterval(() => {
                const idleTime = Date.now() - lastUserActivity;

                // Si se alcanzaron los 20 minutos de inactividad
                if (idleTime >= INACTIVITY_LIMIT_MS) {
                    clearInterval(checkInterval);
                    cerrarSesionPorInactividad();
                    return;
                }

                // Aviso 1 minuto antes (a los 19 minutos)
                if (idleTime >= (INACTIVITY_LIMIT_MS - WARNING_BEFORE_MS) && !warningModalShown) {
                    warningModalShown = true;
                    mostrarModalAvisoInactividad();
                }
            }, 10000);

            function mostrarModalAvisoInactividad() {
                let modal = document.getElementById('inactivityWarningModal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'inactivityWarningModal';
                    modal.className = 'fixed inset-0 z-[999999] flex items-center justify-center bg-black/60 backdrop-blur-sm';
                    modal.innerHTML = `
                        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center border border-amber-200">
                            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                                ⏳
                            </div>
                            <h3 class="text-lg font-bold text-slate-800">¿Sigues trabajando?</h3>
                            <p class="text-sm text-slate-600 mt-1">
                                Tu sesión se cerrará en <span id="inactivityCountdown" class="font-bold text-red-600">60</span> segundos por inactividad.
                            </p>
                            <p class="text-xs text-slate-400 mt-2">Mueve el cursor o presiona cualquier tecla para continuar.</p>
                            <button type="button" onclick="window.continuarSesion()" class="mt-4 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all shadow-md">
                                Continuar Trabajando
                            </button>
                        </div>
                    `;
                    document.body.appendChild(modal);
                } else {
                    modal.classList.remove('hidden');
                }

                let secondsLeft = 60;
                const countdownEl = document.getElementById('inactivityCountdown');
                const countdownInterval = setInterval(() => {
                    secondsLeft--;
                    if (countdownEl) countdownEl.textContent = secondsLeft;
                    if (secondsLeft <= 0 || !warningModalShown) {
                        clearInterval(countdownInterval);
                    }
                }, 1000);
            }

            window.continuarSesion = function() {
                recordUserActivity();
                // Notificar ping ligero al servidor para actualizar last_activity en backend
                fetch('/api/buscar?q=ping').catch(() => {});
            };

            function cerrarSesionPorInactividad() {
                window.location.href = MAIN_PORTAL_LOGOUT;
            }
        })();

        function toggleNotificationDropdown(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const modal = document.getElementById('notificationModal');
            const backdrop = document.getElementById('notificationBackdrop');

            modalAbierto = !modalAbierto;

            if (modalAbierto) {
                modal.classList.remove('hidden');
                backdrop.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
                backdrop.classList.add('hidden');
            }
        }

        // Marcar todas las notificaciones como leídas sin recargar la página
        function markNotificationsAsRead() {
            const btn = document.querySelector('#notificationModal button[onclick*="markNotificationsAsRead"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span class="inline-block animate-spin mr-1">↻</span> Procesando...`;
            }

            fetch('/api/notificaciones/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationBadge(0);

                    // Ocultar botón del footer
                    const footer = document.querySelector('#notificationModal .border-t');
                    if (footer) footer.style.display = 'none';

                    // Atenuar visualmente items como leídos
                    document.querySelectorAll('#notificationModal .border-l-4').forEach(item => {
                        item.classList.add('opacity-50', 'bg-gray-50');
                    });

                    showToast('Todas las notificaciones fueron marcadas como leídas', 'success');
                }
            })
            .catch(error => {
                console.error('Error al marcar notificaciones:', error);
                showToast('Error al marcar notificaciones', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'Marcar todo como leído';
                }
            });
        }

        // ==========================================
        // ACELERADOR DE NAVEGACIÓN Y CARGAS RÁPIDAS (PREFETCH & FEEDBACK JS)
        // ==========================================
        // Barra superior de progreso ultraligera
        const progressBar = document.createElement('div');
        progressBar.id = 'pageProgressBar';
        progressBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:linear-gradient(to right, #2563eb, #60a5fa);z-index:99999;transition:width 0.25s ease, opacity 0.3s ease;width:0%;pointer-events:none;opacity:0;';
        document.body.appendChild(progressBar);

        function triggerProgress() {
            progressBar.style.opacity = '1';
            progressBar.style.width = '35%';
            setTimeout(() => { progressBar.style.width = '75%'; }, 120);
            setTimeout(() => { progressBar.style.width = '95%'; }, 300);
        }

        // Precarga inteligente de enlaces en hover/touch (Instant Page Prefetch)
        const prefetchedUrls = new Set();
        function prefetchLink(url) {
            if (!url || prefetchedUrls.has(url) || url.startsWith('#') || url.includes('/logout') || url.includes('javascript:')) return;
            try {
                const parsed = new URL(url, window.location.origin);
                if (parsed.origin !== window.location.origin) return;
                prefetchedUrls.add(url);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            } catch(e) {}
        }

        document.addEventListener('mouseover', function(e) {
            const anchor = e.target.closest('a');
            if (anchor && anchor.href) {
                prefetchLink(anchor.href);
            }
        }, { passive: true });

        document.addEventListener('touchstart', function(e) {
            const anchor = e.target.closest('a');
            if (anchor && anchor.href) {
                prefetchLink(anchor.href);
            }
        }, { passive: true });

        // Activar barra de progreso en clics de navegación
        document.addEventListener('click', function(e) {
            const anchor = e.target.closest('a');
            if (anchor && anchor.href && !anchor.target && !anchor.hasAttribute('download') && !anchor.href.startsWith('#') && !anchor.href.includes('javascript:')) {
                try {
                    const url = new URL(anchor.href, window.location.origin);
                    if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
                        triggerProgress();
                    }
                } catch(e) {}
            }
        });

        // Feedback inmediato en envíos de formularios para evitar doble submit
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]:not([disabled])');
            if (submitBtn && !form.classList.contains('no-spin')) {
                triggerProgress();
                setTimeout(() => {
                    if (!form.checkValidity || form.checkValidity()) {
                        submitBtn.classList.add('opacity-75', 'pointer-events-none');
                        submitBtn.innerHTML = `<span class="inline-block animate-spin mr-1">↻</span> Guardando...`;
                    }
                }, 20);
            }
        });

        function closeNotificationModal() {
            const modal = document.getElementById('notificationModal');
            const backdrop = document.getElementById('notificationBackdrop');

            modalAbierto = false;
            modal.classList.add('hidden');
            backdrop.classList.add('hidden');
        }

        // Cerrar modal al hacer clic en backdrop
        document.getElementById('notificationBackdrop')?.addEventListener('click', closeNotificationModal);

        // Cerrar modal con la tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modalAbierto) {
                closeNotificationModal();
            }
        });

        // Sistema de tracking de errores frontend
        window.errorsFrontend = [];



        // Validar formularios antes del envío
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const invalidFields = [];
                    const requiredFields = form.querySelectorAll('[required]');

                    requiredFields.forEach(field => {
                        if (!field.value.trim() || (field.type === 'checkbox' && !field.checked)) {
                            invalidFields.push(field.name || field.id || field.placeholder || 'Campo sin etiqueta');
                            field.classList.add('border-red-500');
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });

                    if (invalidFields.length > 0) {
                        e.preventDefault();
                        showToast(`Por favor complete los campos requeridos: ${invalidFields.slice(0, 3).join(', ')}${invalidFields.length > 3 ? '...' : ''}`, 'error');

                        const firstInvalid = form.querySelector('.border-red-500');
                        if (firstInvalid) {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstInvalid.focus();
                        }
                    }
                });
            });
        });

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) {
                sidebar.classList.remove('open');
            }
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
        }

        // Toggle sidebar collapse/expand (unificado para móvil y escritorio)
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (!sidebar) return;

            if (window.innerWidth <= 1024) {
                // Móvil / tablet: drawer overlay con backdrop
                const isOpen = sidebar.classList.toggle('open');
                if (backdrop) {
                    backdrop.classList.toggle('hidden', !isOpen);
                }
            } else {
                // Escritorio: ocultar completamente el menú a la izquierda
                const isCollapsed = sidebar.classList.toggle('collapsed');
                if (mainContent) {
                    mainContent.classList.toggle('sidebar-collapsed', isCollapsed);
                }
                try {
                    localStorage.setItem('sidebar_collapsed', isCollapsed ? '1' : '0');
                } catch (e) {}
            }
        }
        window.toggleSidebarUnified = toggleSidebar;

        // ============ BUSCADOR GLOBAL EN TIEMPO REAL ============
        let searchTimeout = null;
        let searchAbortController = null;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const searchLoading = document.getElementById('searchLoading');

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                // Limpiar timeout y llamadas anteriores
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                if (searchAbortController) {
                    searchAbortController.abort();
                }

                // Requisito: Mínimo 3 letras para disparar búsqueda en tiempo real
                if (query.length < 3) {
                    searchResults.classList.add('hidden');
                    if (searchLoading) searchLoading.classList.add('hidden');
                    return;
                }

                if (searchLoading) searchLoading.classList.remove('hidden');

                // Debounce snappy de 250ms
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 250);
            });

            // Cerrar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#searchResults') && !e.target.closest('#searchInput')) {
                    searchResults.classList.add('hidden');
                }
            });

            // Manejo de teclado en el buscador
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.classList.add('hidden');
                    searchInput.blur();
                } else if (e.key === 'Enter') {
                    // Si presiona enter y hay resultados visibles, abrir el primer resultado
                    const primerResultado = searchResults.querySelector('a[href]');
                    if (primerResultado && !searchResults.classList.contains('hidden')) {
                        e.preventDefault();
                        primerResultado.click();
                    }
                }
            });

            // Posicionar al redimensionar o scroll
            window.addEventListener('resize', positionSearchDropdown);
            window.addEventListener('scroll', positionSearchDropdown, true);
        }

        function performSearch(query) {
            searchAbortController = new AbortController();
            const url = `/api/buscar?q=${encodeURIComponent(query)}`;

            fetch(url, { signal: searchAbortController.signal })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al consultar el servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (searchLoading) searchLoading.classList.add('hidden');
                    renderSearchResults(data, query);
                })
                .catch(error => {
                    if (error.name === 'AbortError') return; // Cancelada por tipeo continuo
                    console.error('Error en búsqueda global:', error);
                    if (searchLoading) searchLoading.classList.add('hidden');
                    searchResults.innerHTML = `
                        <div class="p-5 text-center text-red-500">
                            <p class="text-sm font-semibold">Error al realizar la búsqueda</p>
                            <p class="text-xs text-gray-400 mt-1">${escapeHtml(error.message)}</p>
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                    positionSearchDropdown();
                });
        }

        let activeCategoriaFiltro = 'todas';

        function filtrarCategoria(tipo, btnElement) {
            activeCategoriaFiltro = tipo;
            const container = document.getElementById('searchResults');
            if (!container) return;

            // Actualizar apariencia de los botones de pestañas
            container.querySelectorAll('.cat-pill-btn').forEach(btn => {
                if (btn.dataset.cat === tipo) {
                    btn.className = 'cat-pill-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-blue-600 text-white shadow-sm shrink-0 cursor-pointer';
                } else {
                    btn.className = 'cat-pill-btn px-3 py-1 rounded-full text-xs font-medium transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 shrink-0 cursor-pointer';
                }
            });

            // Mostrar/ocultar los bloques de resultados según la opción seleccionada
            container.querySelectorAll('.search-group-block').forEach(block => {
                if (tipo === 'todas' || block.dataset.tipo === tipo) {
                    block.classList.remove('hidden');
                } else {
                    block.classList.add('hidden');
                }
            });
        }
        window.filtrarCategoria = filtrarCategoria;

        function renderSearchResults(results, query) {
            if (!searchResults) return;

            if (!Array.isArray(results) || results.length === 0) {
                searchResults.innerHTML = `
                    <div class="p-6 text-center text-slate-500">
                        <div class="w-12 h-12 mx-auto mb-2 text-2xl flex items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            🔍
                        </div>
                        <p class="text-sm font-bold text-slate-700">Sin coincidencias para "${escapeHtml(query)}"</p>
                        <p class="text-xs text-slate-400 mt-1">No se encontraron registros en las páginas y módulos asignados a tu rol actual.</p>
                    </div>
                `;
                searchResults.classList.remove('hidden');
                positionSearchDropdown();
                return;
            }

            // Agrupar resultados por opción / categoría
            const grouped = {};
            results.forEach(item => {
                const key = item.tipo || item.tabla || 'Resultados';
                if (!grouped[key]) {
                    grouped[key] = [];
                }
                grouped[key].push(item);
            });

            const categoriasKeys = Object.keys(grouped);
            let html = '';

            // Encabezado con pestañas/pills de opciones cuando hay múltiples páginas de resultados
            if (categoriasKeys.length > 1) {
                html += `
                    <div class="p-2.5 bg-slate-50/95 border-b border-slate-200 sticky top-0 z-20 backdrop-blur-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1 mb-1.5">Opciones encontradas:</p>
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar">
                            <button type="button" onclick="filtrarCategoria('todas', this)" data-cat="todas"
                                class="cat-pill-btn px-3 py-1 rounded-full text-xs font-bold transition-all bg-blue-600 text-white shadow-sm shrink-0 cursor-pointer">
                                Todas (${results.length})
                            </button>
                `;
                categoriasKeys.forEach(cat => {
                    const catItems = grouped[cat];
                    const catIcon = catItems[0]?.icono || '📋';
                    html += `
                        <button type="button" onclick="filtrarCategoria('${escapeHtml(cat)}', this)" data-cat="${escapeHtml(cat)}"
                            class="cat-pill-btn px-3 py-1 rounded-full text-xs font-medium transition-all bg-white text-slate-600 hover:bg-slate-100 border border-slate-200 shrink-0 cursor-pointer">
                            ${catIcon} ${escapeHtml(cat)} (${catItems.length})
                        </button>
                    `;
                });
                html += `
                        </div>
                    </div>
                `;
            }

            for (const [tipo, items] of Object.entries(grouped)) {
                const primerItem = items[0] || {};
                const icono = primerItem.icono || '📋';

                html += `
                    <div class="search-group-block border-b border-slate-100 last:border-b-0" data-tipo="${escapeHtml(tipo)}">
                        <div class="px-4 py-2 bg-slate-100/90 border-b border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wide">
                                <span>${icono}</span>
                                <span>${escapeHtml(tipo)}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-500 bg-white px-2 py-0.5 rounded-full border border-slate-200 shadow-sm">
                                ${items.length}
                            </span>
                        </div>
                `;

                items.forEach(item => {
                    const nombreHighlighted = highlightMatch(item.nombre, query);
                    const subtituloHighlighted = item.subtitulo ? highlightMatch(item.subtitulo, query) : '';
                    const matchExtra = (item.campo_encontrado && item.coincidencia &&
                                        item.campo_encontrado.toLowerCase() !== 'descripcion' &&
                                        item.campo_encontrado.toLowerCase() !== 'name' &&
                                        item.campo_encontrado.toLowerCase() !== 'articulo' &&
                                        item.campo_encontrado.toLowerCase() !== 'cartucho' &&
                                        item.campo_encontrado.toLowerCase() !== 'impresora' &&
                                        item.campo_encontrado.toLowerCase() !== 'proveedor')
                        ? `<p class="text-[11px] text-blue-600 mt-1 flex items-center gap-1 font-medium">
                             <span class="text-slate-500">${escapeHtml(item.campo_encontrado)}:</span>
                             <span>${highlightMatch(item.coincidencia, query)}</span>
                           </p>`
                        : '';

                    const moduloTag = item.modulo ? `<span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 shrink-0">${escapeHtml(item.tipo)}</span>` : '';

                    html += `
                        <a href="${item.url || '#'}"
                           class="group flex items-start gap-3 px-4 py-3 hover:bg-blue-50/80 transition-colors border-b border-slate-50 last:border-b-0">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-blue-100 text-slate-600 group-hover:text-blue-700 flex items-center justify-center shrink-0 text-sm transition-colors mt-0.5 shadow-sm">
                                ${item.icono || '📋'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800 truncate group-hover:text-blue-900 transition-colors">
                                        ${nombreHighlighted}
                                    </p>
                                    ${moduloTag}
                                </div>
                                ${subtituloHighlighted ? `<p class="text-xs text-slate-500 truncate mt-0.5">${subtituloHighlighted}</p>` : ''}
                                ${matchExtra}
                                <div class="mt-1 flex items-center gap-1 text-[11px] text-blue-600 font-medium group-hover:underline">
                                    <span>Ir a la página de ${escapeHtml(item.tipo)}</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-600 group-hover:translate-x-0.5 shrink-0 mt-2 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    `;
                });

                html += '</div>';
            }

            // Footer informativo
            html += `
                <div class="p-2.5 bg-slate-50 text-center text-xs text-slate-500 font-medium border-t border-slate-100">
                    ${results.length} resultado(s) disponible(s) según tu rol
                </div>
            `;

            searchResults.innerHTML = html;
            searchResults.classList.remove('hidden');
            activeCategoriaFiltro = 'todas';
            positionSearchDropdown();
        }

        // Función para resaltar coincidencias
        function highlightMatch(text, searchTerm) {
            if (!text || !searchTerm) return escapeHtml(text || '');
            const escapedSearch = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedSearch})`, 'gi');
            return escapeHtml(text).replace(regex, '<span class="bg-yellow-200 text-slate-900 font-bold px-0.5 rounded">$1</span>');
        }

        // Helper para escapar HTML seguro
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Posicionar el dropdown debajo del input alineado a la derecha
        function positionSearchDropdown() {
            const searchInputEl = document.getElementById('searchInput');
            const searchResultsEl = document.getElementById('searchResults');

            if (searchInputEl && searchResultsEl && !searchResultsEl.classList.contains('hidden')) {
                const inputRect = searchInputEl.getBoundingClientRect();
                const dropdownWidth = Math.min(520, window.innerWidth - 32);

                searchResultsEl.style.top = (inputRect.bottom + 8) + 'px';
                searchResultsEl.style.width = dropdownWidth + 'px';

                // Alinear al borde derecho del input pero sin desbordar el viewport
                let left = inputRect.right - dropdownWidth;
                if (left < 16) left = 16;
                if (left + dropdownWidth > window.innerWidth - 16) {
                    left = window.innerWidth - dropdownWidth - 16;
                }
                searchResultsEl.style.left = left + 'px';
            }
        }

        // Helper global para mostrar/ocultar contraseñas
        window.togglePasswordVisibility = function(inputId) {
            var input = document.getElementById(inputId);
            var eyeIcon = document.getElementById('eye-icon-' + inputId);
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeIcon) {
                    eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
                }
            } else {
                input.type = 'password';
                if (eyeIcon) {
                    eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                }
            }
        };

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Restaurar estado del sidebar en escritorio desde localStorage
            if (window.innerWidth > 1024) {
                try {
                    if (localStorage.getItem('sidebar_collapsed') === '1') {
                        const sidebar = document.getElementById('sidebar');
                        const mainContent = document.getElementById('mainContent');
                        if (sidebar) sidebar.classList.add('collapsed');
                        if (mainContent) mainContent.classList.add('sidebar-collapsed');
                    }
                } catch (e) {}
            }
        });
    </script>
    <script src="{{asset('js/agenda.js')}}"></script>
    <!-- Sistema de Validación de Formularios -->
    <script src="{{ asset('js/validation.js') }}"></script>
</body>
</html>
