@can('estanterias_index')
<x-app-layout>
    @section('title', 'Estanterías')
    @section('subtitle', 'Gestión de estanterías')

    <style>
        body { box-sizing: border-box; }
        :root{ --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B; --card-bg:white; --card-border:rgba(15,23,42,0.08); --text:#0f172a; }
        body{ font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%); color:var(--text); }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
    </style>

    <!-- Filtros y controles -->
    <form method="GET" action="{{ route('administracion.estanterias.index') }}" id="filterForm">
        <div class="sticky top-0 z-10 px-6 mb-6">
            <div class="p-6 glass rounded-2xl fade-in">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Búsqueda general -->
                        <div class="relative">
                            <input type="text" id="generalSearch" name="search_general" placeholder="Buscar estanterías..."
                                class="w-64 px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('search_general') ? 'bg-blue-100 border-blue-500' : '' }}"
                                value="{{ request('search_general') }}">
                        </div>

                        <!-- Botón para limpiar filtros -->
                        <a href="{{ route('administracion.estanterias.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            🗑️ Limpiar
                        </a>
                    </div>

                    <div class="flex items-center gap-3">
                        @can('estanterias_create')
                        <a href="{{ route('administracion.estanterias.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            <span>➕</span>
                            Nueva
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Registro de estanterías -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 fade-in">
            @forelse ($estanterias as $estanteria)
            <!-- Tarjeta de estantería -->
            <div class="glass rounded-2xl p-6 hover:scale-[1.02] transition-transform">
                <div class="flex flex-col items-center">
                    <!-- Imagen de la estantería -->
                    <div class="flex items-center justify-center w-full h-48 mb-4 overflow-hidden bg-gray-100 rounded-xl">
                        @if($estanteria->imagen && file_exists(storage_path('app/public/estanterias/' . $estanteria->imagen)))
                            <img src="{{ asset('storage/estanterias/' . $estanteria->imagen) }}" alt="{{ $estanteria->nombre }}" class="object-cover w-full h-full">
                        @else
                            <div class="text-center text-gray-400">
                                <div class="mb-2 text-6xl">🗄️</div>
                                <p class="text-sm">Sin imagen</p>
                            </div>
                        @endif
                    </div>

                    <!-- Nombre -->
                    <div class="mb-4 text-center">
                        <p class="text-lg font-semibold text-blue-900">{{ $estanteria->nombre }}</p>
                        <p class="text-xs text-gray-400">Creado: {{ $estanteria->created_at->format('d/m/Y') }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="flex flex-wrap justify-center gap-2">
                        @can('estanterias_edit')
                        <a href="{{ route('administracion.estanterias.edit', $estanteria->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                            <span>✏️</span>
                            Editar
                        </a>
                        @endcan
                        @can('estanterias_destroy')
                        <button onclick="confirmDelete({{ $estanteria->id }}, '{{ $estanteria->nombre }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                            <span>🗑️</span>
                            Eliminar
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-center col-span-full glass rounded-2xl">
                <div class="flex flex-col items-center justify-center py-8">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-full">
                        <span class="text-3xl">🗄️</span>
                    </div>
                    <h3 class="mb-1 text-lg font-medium text-gray-900">No hay estanterías</h3>
                    <p class="mb-4 text-sm text-gray-500">No se han registrado estanterías</p>
                    @can('estanterias_create')
                    <a href="{{ route('administracion.estanterias.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Registrar Estantería
                    </a>
                    @endcan
                </div>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($estanterias->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $estanterias->links() }}
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
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // SweetAlert2 for delete confirmation
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar la estantería "${name}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/administracion/estanterias/${id}`;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken.getAttribute('content');
                        form.appendChild(csrfInput);
                    }

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

        // Auto-submit on search with debounce
        const generalSearch = document.getElementById('generalSearch');
        const filterForm = document.getElementById('filterForm');

        if (generalSearch && filterForm) {
            let searchTimeout;

            generalSearch.addEventListener('input', function() {
                clearTimeout(searchTimeout);

                // Debounce: esperar 500ms antes de buscar
                searchTimeout = setTimeout(function() {
                    filterForm.submit();
                }, 500);
            });
        }

        document.addEventListener('DOMContentLoaded', () => showToast('Módulo de estanterías cargado', 'info'));
    </script>
</x-app-layout>
@endcan

