@can('impresora_index')
<x-app-layout>
    @section('title', 'Impresoras')
    @section('subtitle', 'Gestión de impresoras')
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
        }
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%);
            color:var(--text);
        }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .btn-secondary{ background:rgb(255, 255, 255) }
        .btn-success{ background:var(--color-secondary); color:#fff }
        .btn-warning{ background:var(--color-accent); color:#111827 }
        .btn-secondary{ background:rgba(15,23,42,0.06) }
        .main-content{
            min-height:100vh;
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
    <form method="GET" action="{{ route('administracion.impresoras.index') }}" id="filterForm">
    <div class="px-6 mb-6 sticky top-0 z-10">
        <div class="glass rounded-2xl p-6 fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro por nombre de impresora -->
                    <select name="impresora_id" onchange="document.getElementById('filterForm').submit()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        <option value="">Todas las impresoras</option>
                        @foreach($all_impresoras as $impresora)
                            <option value="{{ $impresora->id }}" {{ request('impresora_id') == $impresora->id ? 'selected' : '' }}>
                                {{ $impresora->impresora }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Input de búsqueda por nombre -->
                    <input type="text" name="busqueda" oninput="if(this.value.length >= 2 || this.value === '') document.getElementById('filterForm').submit()" placeholder="Buscar por nombre..." value="{{ request('busqueda') }}" class="glass bg-blue-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">

                    <!-- Botón para limpiar filtros -->
                    <a href="{{ route('administracion.impresoras.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </a>
                </div>

                <div class="flex items-center gap-3">

                    @can('impresora_create')
                    <a href="{{ route('administracion.impresoras.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Nuevo Registro
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    </form>

    <!-- Registro de impresoras -->
    <div class="px-6 mb-8 ">
        <div class="space-y-4 fade-in {{-- grid grid-cols-2 gap-4  --}}">
            @forelse ($impresoras as $impresora)
            <!-- Registro -->
            <div class="glass rounded-2xl p-6 hover:scale-[1.01] transition-transform ">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                    <!-- ID -->
                    <div class="text-center lg:text-left">
                        <p class="text-xs text-blue-900 font-semibold mb-1">ITEM</p>
                        <p class="text-2xl font-bold text-blue-400">{{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    <!-- Impresora -->
                    <div class="text-center lg:text-left">
                        <p class="text-xs text-blue-900 font-semibold mb-1">🖨️ Impresora</p>
                        <p class="font-semibold text-lg">{{ $impresora->impresora }}</p>
                    </div>

                    <!-- Imagen y acciones -->
                    <div class="text-center lg:text-left">
                        <p class="text-xs text-blue-900 font-semibold mb-1">📄 Imagen</p>
                        <div class="flex justify-center lg:justify-start">
                            <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center border border-white/10">
                                @if($impresora->imagen)
                                    <img src="{{ asset('storage/' . $impresora->imagen) }}" alt="Imagen de impresora" class="w-24 h-24 object-cover rounded-xl">
                                @else
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                                        <polyline points="6,9 6,2 18,2 18,9"/>
                                        <path d="M6,18H4a2,2,0,0,1-2-2V11a2,2,0,0,1,2-2H20a2,2,0,0,1,2,2v5a2,2,0,0,1-2,2H18"/>
                                        <rect x="6" y="14" width="12" height="8"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-center lg:justify-start gap-2 mt-3">
                            @can('impresora_edit')
                            <a href="{{ route('administracion.impresoras.edit', $impresora->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('impresora_destroy')
                            <button onclick="confirmDelete({{ $impresora->id }}, '{{ $impresora->impresora }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                                <span>🗑️</span>
                                Eliminar
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="glass rounded-2xl p-6 text-center">
                <p class="text-slate-400">No hay ninguna impresora registrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($impresoras->hasPages())
        <div class="flex justify-center mt-8">
            <div class="glass rounded-xl p-4">
                {{ $impresoras->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 hidden z-50">
        <div class="glass rounded-xl px-4 py-3 flex items-center gap-2">
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
                text: `¿Quieres eliminar la impresora "${name}"? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/impresoras/${id}`;

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
    </script>
</x-app-layout>
@endcan
