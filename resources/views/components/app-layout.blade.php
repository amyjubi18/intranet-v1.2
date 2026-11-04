<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Fuentes externas deshabilitadas por CSP - usando fuentes del sistema --}}
    {{--
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    --}}

    <script type="text/javascript">
    var baseURL = {!!json_encode(url('/'))!!}
    </script>

    {{-- Fuentes externas deshabilitadas por CSP - usando fuentes del sistema --}}
    {{--
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    --}}
    <link rel="icon" href="{{asset('img/Logo.png')}}" type="image/png">

    <!-- Scripts -->
     @yield('scripts')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
       
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .main-content.sidebar-collapsed { margin-left: 0 !important; }
            #menuToggle { display: inline-flex !important; }
        }
    </style>
</head>
<body class="scroll-smooth">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        @include('layouts.navigation')
    </aside>

    <!-- Backdrop for mobile -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="closeSidebar()"></div>

    <!-- Contenido principal -->
    <main class="main-content bg-gradient-to-r from-slate-300 to-slate-500" id="mainContent">
        <!-- Header estilo tarjeta -->
        <div class="p-6">
            <div class="glass rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button id="menuToggle" type="button" class="btn btn-secondary shadow-sm hover:bg-white transition-all mr-2" onclick="toggleSidebar()" title="Mostrar menú">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <line x1="3" y1="12" x2="21" y2="12"/>
                                <line x1="3" y1="18" x2="21" y2="18"/>
                            </svg>
                        </button>
                        <div>
                            <h1 class="text-2xl font-extrabold">{{ $title ?? 'Sistema CODECYT' }}</h1>
                            <p class="text-sm text-slate-400">{{ $subtitle ?? 'Sistema de Inventario' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Buscador -->
                        <div class="relative">
                            <input type="text" placeholder="Buscar..." class="glass rounded-xl px-4 py-2 pl-10 w-64 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                        </div>

                        <!-- Campana de notificaciones -->
                        <button class="relative btn btn-secondary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>

                        <!-- Icono de usuario -->
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-blue-600 flex items-center justify-center">
                            <span class="text-white font-bold text-xs">{{ substr(Auth::user()->name, 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="p-4">
            {{ $slot }}
        </main>
    </main>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 hidden z-50">
        <div class="glass rounded-xl px-4 py-3 flex items-center gap-2">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script>
        // Estado de la aplicación
        let stockChart, distributionChart;

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

        // Dropdown
        function toggleDropdown() {
            const dropdownElement = document.querySelector('.dropdown');
            if (dropdownElement) {
                dropdownElement.classList.toggle('open');
            }
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdownElement = document.querySelector('.dropdown');
            if (dropdownElement && !e.target.closest('.dropdown')) {
                dropdownElement.classList.remove('open');
            }
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

        // Navegación
        function navigateTo(section) {
            const routes = {
                'inicio': '{{ route("inicio") }}',
                'movimientos': '{{ route("administracion.salida_oficina.index") }}', // Ejemplo, ajustar según rutas
                'alertas': '#', // Placeholder
                'proveedores': '{{ route("administracion.proveedores.index") }}',
                'reportes': '#', // Placeholder
                'inventario': '{{ route("administracion.inventario_impresoras.index") }}',
                'ajustes': '#', // Placeholder
            };
            if (routes[section]) {
                window.location.href = routes[section];
            } else {
                showToast(`Navegando a ${section}...`, 'info');
            }
        }

        function logout() {
            showToast('Cerrando sesión...', 'info');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("logout") }}';
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrf.getAttribute('content');
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }

        // Inicialización
        function init() {
            showToast('Módulo cargado correctamente', 'success');

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
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
    <script src="{{asset('js/agenda.js')}}"></script>
</body>
</html>
