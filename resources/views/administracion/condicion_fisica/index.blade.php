@can('condicion_index')
<x-app-layout>
    @section('title', 'Condiciones Físicas')
    @section('subtitle', 'Gestión de condiciones físicas')
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

        /* Animaciones */
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }

       
    </style>

    <!-- Filtros y controles -->
    <div class="sticky top-0 z-10 px-6 mb-6">
        <div class="p-6 glass rounded-2xl fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
<!-- Buscador directo con debounce -->
                    <form method="GET" action="{{ route('administracion.condicion_fisica.index') }}" id="searchForm" class="flex items-center gap-2">
                        <input
                            type="text"
                            name="search"
                            id="condicionFisicaSearchInput"
                            value="{{ $search ?? '' }}"
                            placeholder="Buscar condición física..."
                            class="w-64 px-4 py-2 text-sm glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                        >
                        @if($search)
                        <a href="{{ route('administracion.condicion_fisica.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                           🗑️ Limpiar
                        </a>
                        @endif
                        <!-- Selector de ordenamiento -->
                        <div class="flex items-center gap-1 ml-2">
                            <span class="text-xs text-gray-500">Ordenar por:</span>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'condicion', 'direction' => $sort === 'condicion' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'condicion' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Nombre
                                @if($sort === 'condicion')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-gray-400">⇅</span>
                                @endif
                            </a>
                            {{-- <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'id' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                ID
                                @if($sort === 'id')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-gray-400">⇅</span>
                                @endif
                            </a> --}}
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3">
                    @can('condicion_create')
                    <a href="{{ route('administracion.condicion_fisica.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de condiciones físicas -->
    <div class="px-6 mb-8">
        <div class="space-y-4 fade-in">
            @forelse ($condicions as $condicion)
            <!-- Registro -->
            <div class="glass justify-between rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center justify-between grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Condición Física -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">🏋️ Condición Física</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit" >{{ $condicion->condicion }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="text-center lg:text-left">
                        <div class="flex justify-center gap-2 lg:justify-start">
                            @can('condicion_edit')
                            <a href="{{ route('administracion.condicion_fisica.edit', $condicion->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('condicion_destroy')
                            <button onclick="confirmDelete({{ $condicion->id }}, '{{ $condicion->condicion }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
                <p class="text-slate-400">No hay ninguna condición física registrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($condicions->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $condicions->links() }}
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

    <script>
        // ==========================================
        // BUSCADOR CON DEBOUNCE - CONDICIONES FÍSICAS
        // ==========================================

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('condicionFisicaSearchInput');
            const searchForm = document.getElementById('searchForm');

            if (searchInput && searchForm) {
                let searchTimeout;

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    // Debounce: esperar 300ms antes de buscar
                    searchTimeout = setTimeout(function() {
                        searchForm.submit();
                    }, 300);
                });
            }

            showToast('Módulo de condiciones físicas cargado', 'info');
        });

        // Función para confirmar eliminación
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar la condición física "${name}"? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/condicion_fisica/${id}`;

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
            showToast('Módulo de condiciones físicas cargado', 'info');
        });
    </script>
</x-app-layout>
@endcan

