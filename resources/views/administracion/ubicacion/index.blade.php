@can('ubicacion_index')
<x-app-layout>
    @section('title', 'Ubicaciones')
    @section('subtitle', 'Gestión de ubicaciones')

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

        /* Filtros con buscador */
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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
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

        
    </style>

    <!-- Controles -->
    <div class="px-6 mb-6 sticky top-0 z-10">
        <div class=" rounded-2xl p-6 fade-in">
            <div class="flex flex-wrap items-center justify-start gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- No filters for ubicacion -->
                </div>

                <div class="flex items-center gap-3">
                    @can('ubicacion_create')
                    <a href="{{ route('administracion.ubicacion.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">
                        <span>➕</span>
                        Nueva Ubicación
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de ubicaciones -->
    <div class="px-6 mb-8">
        <div class="space-y-4 fade-in">
            @forelse ($ubicacions as $ubicacion)
            <!-- Registro -->
            <div class="glass justify-between rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center justify-between">
                    <!-- Ubicación -->
                    <div class="text-center lg:text-left">
                        <p class="text-xs text-blue-900 font-semibold mb-1">📍 Ubicación</p>
                        <p class="font-semibold text-lg text-black w-fit p-2 rounded-lg">{{ $ubicacion->ubicacion }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="text-center lg:text-left">
                        <div class="flex justify-center lg:justify-start gap-2">
                            @can('ubicacion_edit')
                            <a href="{{ route('administracion.ubicacion.edit', $ubicacion->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('ubicacion_destroy')
                            <button onclick="confirmDelete({{ $ubicacion->id }}, '{{ $ubicacion->ubicacion }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
                <p class="text-slate-400">No hay ninguna ubicación registrada</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($ubicacions->hasPages())
        <div class="flex justify-center mt-8">
            <div class="glass rounded-xl p-4">
                {{ $ubicacions->links() }}
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

    <script>
        // Función para confirmar eliminación
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar la ubicación "${name}"? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/ubicacion/${id}`;

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
    </script>
</x-app-layout>
@endcan
