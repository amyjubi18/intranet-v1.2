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
            background:rgba(255,255,255,0.2);
            color:#ffffff;
            box-shadow:0 4px 12px rgba(0,0,0,0.3);
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
    <div id="searchResults" class="hidden fixed bg-white rounded-xl shadow-2xl border border-gray-200 max-h-80 overflow-y-auto w-96 z-[99999] sm:w-20">
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

            @if($errors->any())
                <div class="p-4 mb-4 text-yellow-700 bg-yellow-100 border border-yellow-400 rounded-lg">
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

    <!-- Toast -->
    <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script>
        // Toast
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // Modal de notificaciones
        let modalAbierto = false;

        // Verificar si el usuario ya vio las notificaciones
        function hasUserViewedNotifications() {
            return localStorage.getItem('notifications_viewed') === 'true';
        }

        // Marcar como vistas las notificaciones
        function markNotificationsAsViewed() {
            localStorage.setItem('notifications_viewed', 'true');
        }

        // Ocultar badge de notificaciones si ya fueron vistas
        function hideNotificationBadgeIfViewed() {
            if (hasUserViewedNotifications()) {
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.style.display = 'none';
                }
            }
        }

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            hideNotificationBadgeIfViewed();
        });

        function toggleNotificationDropdown(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const modal = document.getElementById('notificationModal');
            const backdrop = document.getElementById('notificationBackdrop');
            const badge = document.querySelector('.notification-badge');

            modalAbierto = !modalAbierto;

            if (modalAbierto) {
                modal.classList.remove('hidden');
                backdrop.classList.remove('hidden');

                // Marcar como vistas y ocultar el badge
                markNotificationsAsViewed();
                if (badge) {
                    badge.style.display = 'none';
                }
            } else {
                modal.classList.add('hidden');
                backdrop.classList.add('hidden');
            }
        }

        // Función para marcar notificaciones como leídas
        function markNotificationsAsRead() {
            fetch('/api/notificaciones/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ocultar el badge visualmente (en la campana)
                    const badge = document.querySelector('.notification-badge');
                    if (badge) {
                        badge.style.display = 'none';
                    }

                    // Ocultar el contador en el header del modal
                    const headerBadge = document.querySelector('#notificationModal .rounded-full');
                    if (headerBadge) {
                        headerBadge.style.display = 'none';
                    }

                    // Ocultar el footer con el botón de marcar como leído
                    const footer = document.querySelector('#notificationModal .border-t');
                    if (footer) {
                        footer.style.display = 'none';
                    }

                    // Recargar la página para actualizar las notificaciones como leídas
                    window.location.reload();

                    console.log('Notificaciones marcadas como leídas');
                }
            })
            .catch(error => {
                console.error('Error al marcar notificaciones:', error);
            });
        }

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

        // ============ BUSCADOR GLOBAL ============
        let searchTimeout = null;
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        const searchLoading = document.getElementById('searchLoading');

        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();

                console.log('Buscando:', query);

                // Limpiar timeout anterior
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Ocultar resultados si la búsqueda está vacía
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    searchLoading.classList.add('hidden');
                    return;
                }

                // Mostrar loading
                searchLoading.classList.remove('hidden');

                // Debounce: esperar 300ms antes de buscar
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });

            // Cerrar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#searchResults') && !e.target.closest('#searchInput')) {
                    searchResults.classList.add('hidden');
                }
            });

            // Manejar teclado
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.classList.add('hidden');
                    searchInput.blur();
                }
            });
        }

        function performSearch(query) {
            const url = `/api/buscar?q=${encodeURIComponent(query)}`;
            console.log('URL de búsqueda:', url);

            fetch(url)
                .then(response => {
                    console.log('Status:', response.status);
                    if (!response.ok) {
                        throw new Error('Error en la búsqueda');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Resultados:', data);
                    searchLoading.classList.add('hidden');
                    renderSearchResults(data);
                })
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    searchLoading.classList.add('hidden');
                    searchResults.innerHTML = `
                        <div class="p-4 text-center text-red-500 z-[9999]">
                            <p class="text-sm">Error al buscar</p>
                            <p class="text-xs text-gray-400 mt-1">${error.message}</p>
                        </div>
                    `;
                    searchResults.classList.remove('hidden');
                    positionSearchDropdown();
                });
        }

        function renderSearchResults(results) {
            if (!searchResults) return;

            console.log('Renderizando:', results.length, 'resultados');

            if (results.length === 0) {
                const searchValue = document.getElementById('searchInput')?.value || '';
                searchResults.innerHTML = `
                    <div class="p-4 text-center text-gray-500 z-[9999]">
                        <p class="text-sm">No se encontraron resultados para "${searchValue}"</p>
                        <p class="text-xs text-gray-400 mt-1">Intenta con otras palabras clave</p>
                    </div>
                `;
                searchResults.classList.remove('hidden');
                positionSearchDropdown();
                return;
            }

            let html = '';

            // Agrupar por categoría/tipo
            const grouped = {};
            results.forEach(item => {
                const key = item.tabla || item.tipo || 'otro';
                if (!grouped[key]) {
                    grouped[key] = [];
                }
                grouped[key].push(item);
            });

            console.log('Categorías encontradas:', Object.keys(grouped));

            // Generar HTML
            for (const [key, items] of Object.entries(grouped)) {
                const primerItem = items[0];
                const icono = primerItem.icono || '📋';
                const label = primerItem.tipo || key;

                html += `
                    <div class="border-b border-gray-100 last:border-b-0">
                        <div class="px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wide flex items-center gap-2">
                            <span>${icono}</span>
                            <span>${label}</span>
                            <span class="text-gray-400">(${items.length})</span>
                        </div>
                `;

                items.forEach(item => {
                    // Destacar el término buscado en el resultado
                    const searchValue = document.getElementById('searchInput')?.value || '';
                    const nombreHighlighted = highlightMatch(item.nombre, searchValue);

                    const campoTexto = item.campo_encontrado
                        ? `<span class="font-medium text-blue-600">${item.campo_encontrado}</span>`
                        : '';

                    html += `
                        <a href="#" onclick="navegarABusqueda('${item.tabla}', event)"
                           class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-b-0">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm">
                                ${item.icono || '📋'}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">${nombreHighlighted}</p>
                                ${campoTexto ? `<p class="text-xs text-gray-500 mt-0.5">Encontrado en: ${campoTexto}</p>` : ''}
                            </div>
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    `;
                });

                html += '</div>';
            }

            // Footer con ver todos
            html += `
                <div class="p-3 border-t border-gray-200 bg-gray-50 text-center">
                    <span class="text-xs text-gray-500">${results.length} resultado(s) encontrado(s)</span>
                </div>
            `;

            searchResults.innerHTML = html;
            searchResults.classList.remove('hidden');

            // Posicionar el dropdown debajo del input
            positionSearchDropdown();
        }

        // Función para resaltar texto coincidente
        function highlightMatch(text, searchTerm) {
            if (!text || !searchTerm) return text || '';
            const escapedSearch = searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const regex = new RegExp(`(${escapedSearch})`, 'gi');
            return text.replace(regex, '<span class="bg-yellow-200 font-semibold">$1</span>');
        }

        // Función para posicionar el dropdown del buscador
        function positionSearchDropdown() {
            const searchInputEl = document.getElementById('searchInput');
            const searchResultsEl = document.getElementById('searchResults');

            if (searchInputEl && searchResultsEl) {
                const inputRect = searchInputEl.getBoundingClientRect();
                searchResultsEl.style.top = (inputRect.bottom + 8) + 'px';
                searchResultsEl.style.left = inputRect.left + 'px';
                searchResultsEl.style.width = inputRect.width + 'px';
            }
        }

        function getIconoPorTipo(tipo) {
            const iconos = {
                'cartucho': '🖨️',
                'articulo': '📦',
                'impresora': '🖨️',
                'usuario': '👤',
                'documento': '📄',
                'proveedor': '🏢',
                'area': '🏢',
                'gerencia': '🏛️'
            };
            return iconos[tipo] || '📋';
        }

        @php
            $searchUrl = function(string $targetModule, string $routeName, string $fallbackPath) {
                $currentModule = config('modules.current');
                $isCurrent = match($targetModule) {
                    'admin' => in_array($currentModule, ['administracion', 'admin']),
                    'gh'    => in_array($currentModule, ['gestion-humana', 'gh']),
                    'gtic'  => in_array($currentModule, ['gtic']),
                    default => false,
                };

                if ($isCurrent && \Illuminate\Support\Facades\Route::has($routeName)) {
                    return route($routeName);
                }

                if (\Illuminate\Support\Facades\Route::has($routeName)) {
                    return route($routeName);
                }

                if (\Illuminate\Support\Facades\Route::has('sso.go')) {
                    return route('sso.go', ['module' => $targetModule, 'redirect' => $fallbackPath]);
                }

                return $fallbackPath;
            };
        @endphp

        function navegarABusqueda(tabla, event) {
            event.preventDefault();

            // Mapear tablas a rutas
            const rutas = {
                'cartuchos': '{{ $searchUrl("admin", "administracion.consumibles.index", "/administracion/consumibles") }}',
                'articulos': '{{ $searchUrl("admin", "administracion.articulos.index", "/administracion/articulos") }}',
                'impresoras': '{{ $searchUrl("admin", "administracion.impresoras.index", "/administracion/impresoras") }}',
                'users': '{{ $searchUrl("gtic", "users.index", "/users") }}',
                'proveedores': '{{ $searchUrl("admin", "administracion.proveedores.index", "/administracion/proveedores") }}',
                'areas': '{{ $searchUrl("admin", "administracion.areas.index", "/administracion/areas") }}',
                'gerencias': '{{ $searchUrl("admin", "administracion.gerencias.index", "/administracion/gerencias") }}',
                'ubicaciones': '{{ $searchUrl("admin", "administracion.ubicacion.index", "/administracion/ubicacion") }}',
                'documentos': '{{ $searchUrl("admin", "administracion.documentos.index", "/administracion/documentos") }}',
                'bienes': '{{ $searchUrl("admin", "administracion.bienes_publicos.index", "/administracion/bienes_publicos") }}',
                // Gestión Humana
                'tipo_documentos': '{{ $searchUrl("gh", "gestion-humana.tipo_documentos.index", "/gestion-humana/tipo_documentos") }}',
                'areas_adscritas': '{{ $searchUrl("gh", "gestion-humana.area_adscrita.index", "/gestion-humana/area_adscrita") }}',
            };

            console.log('Navegando a:', tabla);

            // Usar la ruta del mapa o ir al dashboard
            const rutaFinal = rutas[tabla] || '{{ route("inicio") }}';
            window.location.href = rutaFinal;

            // Ocultar resultados
            if (searchResults) {
                searchResults.classList.add('hidden');
            }
            if (searchInput) {
                searchInput.value = '';
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
            showToast('Sistema cargado correctamente', 'success');

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
