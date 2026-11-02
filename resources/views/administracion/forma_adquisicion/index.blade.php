@can('forma_adquisicion_index')
<x-app-layout>
    @section('title', 'Formas de Adquisición')
    @section('subtitle', 'Gestión de formas de adquisición')
<style>
    .swal2-popup .swal2-actions {
        display: flex !important;
        opacity: 1 !important;
    }
    .swal2-popup .swal2-confirm {
        opacity: 1 !important;
        visibility: visible !important;
        background-color: #F52727 !important;
        color: white !important;
    }
    .swal2-popup .swal2-cancel {
        opacity: 1 !important;
        visibility: visible !important;
        background-color: #2780F5 !important;
        color: white !important;
    }
</style>
    <style>
        body {
            box-sizing: border-box;
        }

        :root{
            --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B;
            --bg-from:#f8fafc; --bg-to:#e2e8f0;
            --card-bg:white; --card-border:rgba(15,23,42,0.08);
            --muted:#64748b; --text:#0f172a;
            --link-hover-bg: rgba(15,23,42,0.06);
            --active-bg: rgba(30,58,138,0.12);
            --active-text: #0f172a;
        }
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%);
            color:var(--text);
        }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .btn-secondary{ background:rgb(255, 255, 255) }
        .btn-success{ background:var(--color-secondary); color:#fff }
        .btn-warning{ background:var(--color-accent); color:#111827 }
        .btn-secondary{ background:rgba(15,23,42,0.06) }

        .main-content{
            min-height:100vh;
        }

        /* Tarjetas de estadísticas */
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

        /* Dropdown */
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

        /* Animaciones */
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }

        /* Gráficos */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Paginador */
        .pagination .page-link {
            background-color: #1e40af;
            color: white;
            border-color: #1e40af;
        }
        .pagination .page-link:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: white;
        }
        .pagination .page-item.active .page-link {
            background-color: #1e40af;
            border-color: #1e40af;
        }
        .pagination .page-item.disabled .page-link {
            background-color: #6b7280;
            border-color: #6b7280;
        }
    </style>

    <!-- Filtros y controles -->
    <div class="sticky top-0 z-10 px-6 mb-6">
        <div class="p-6 glass rounded-2xl fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Buscador directo -->
                    <form method="GET" action="{{ route('administracion.forma_adquisicion.index') }}" class="flex items-center gap-2">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Buscar forma de adquisición..."
                            class="w-64 px-4 py-2 text-sm glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                        >
                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                           Buscar 🔍
                        </button>
                        @if($search)
                        <a href="{{ route('administracion.forma_adquisicion.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                           Limpiar 🗑️
                        </a>
                        @endif
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    @can('forma_adquisicion_create')
                    <a href="{{ route('administracion.forma_adquisicion.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de formas de adquisición -->
    <div class="px-6 mb-8">
        <div class="space-y-4 fade-in">
            @forelse ($forma_adquisicions as $forma_adquisicion)
            <!-- Registro -->
            <div class="glass justify-between rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center justify-between grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Forma de Adquisición -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">📋 Forma de Adquisición</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit" >{{ $forma_adquisicion->forma_adquisicion }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="text-center lg:text-left">
                        <div class="flex justify-center gap-2 lg:justify-start">
                            @can('forma_adquisicion_edit')
                            <a href="{{ route('administracion.forma_adquisicion.edit', $forma_adquisicion->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('forma_adquisicion_destroy')
                            <button onclick="confirmDelete({{ $forma_adquisicion->id }}, '{{ $forma_adquisicion->forma_adquisicion }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                                <span>🗑️</span>
                                Eliminar
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-center glass rounded-2xl">
                <p class="text-slate-400">No hay ninguna forma de adquisición registrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($forma_adquisicions->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $forma_adquisicions->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para confirmar eliminación
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar la forma de adquisición "${name}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear y enviar formulario de eliminación
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/administracion/forma_adquisicion/${id}`;

                    // Agregar token CSRF
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }

                    // Agregar método DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

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

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
            showToast('Módulo de formas de adquisición cargado', 'info');
        });
    </script>
</x-app-layout>
@endcan

