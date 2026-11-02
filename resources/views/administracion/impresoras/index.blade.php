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

    <!-- Filtros y controles -->
    <div class="px-6 mb-6 sticky top-0 z-10">
        <div class="glass rounded-2xl p-6 fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro principal - Tipo de campo -->
                    <select id="filterType" onchange="updateSecondFilter()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        <option value="">Seleccionar filtro</option>
                        <option value="nombre-impresora">Nombre de Impresora</option>
                        <option value="gerencia_id">Gerencia</option>
                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative" style="z-index: 99999;">
                        <select id="secondFilter" onchange="applySelectFilter()" class="glass bg-blue-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" disabled>
                            <option value="">Primero selecciona un filtro</option>
                        </select>

                        <!-- Filtro con buscador personalizado -->
                        <div id="searchableFilter" class="searchable-select hidden">
                            <button type="button" id="searchableFilterButton" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 w-full text-left flex items-center justify-between">
                                <span id="searchableFilterText">Primero selecciona un filtro</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400">
                                    <polyline points="6,9 12,15 18,9"/>
                                </svg>
                            </button>

                            <div id="searchableFilterDropdown" class="searchable-select-dropdown">
                                <div class="searchable-select-search">
                                    <input type="text" id="searchableFilterInput" placeholder="Buscar...">
                                </div>
                                <div id="searchableFilterOptions" class="searchable-select-options">
                                    <!-- Las opciones se generan dinámicamente -->
                                </div>
                            </div>
                        </div>

                        <!-- Input de texto para búsqueda (oculto por defecto) -->
                        <input type="text" id="textFilter" placeholder="Buscar..." class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 hidden">

                        <!-- Input de fecha (oculto por defecto) -->
                        <div id="dateFilter" class="hidden flex items-center gap-2">
                            <input type="date" id="fecha_desde" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" placeholder="Desde">
                            <input type="date" id="fecha_hasta" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" placeholder="Hasta">
                            <button onclick="applyDateFilter()" class="bg-blue-900 hover:bg-blue-800 text-white rounded-xl px-4 py-2 text-sm">
                                Filtrar
                            </button>
                        </div>
                    </div>

                    <!-- Botón para limpiar filtros -->
                    <button onclick="clearFilters()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </button>

                    <!-- Selector de ordenamiento -->
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500">Ordenar:</span>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'impresora', 'direction' => $sort === 'impresora' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                           class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'impresora' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                            Nombre
                            @if($sort === 'impresora')
                                @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                            @else
                                <span class="text-gray-400">⇅</span>
                            @endif
                        </a>

                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @can('impresora_create')
                    <a href="{{ route('administracion.impresoras.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de impresoras -->
    <div class="px-6 mb-8 ">
        <div class="space-y-4 fade-in">
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
                        @if($impresora->gerencias->count() > 0)
                        <p class="text-xs text-blue-900 font-semibold mb-1"> Gerencia</p>
                            <div class="flex flex-wrap justify-center lg:justify-start gap-1 mt-1">

                                @foreach($impresora->gerencias as $gerencia)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">🏢 {{$gerencia->gerencia}} - {{$gerencia->area->area}}</span>
                                @endforeach
                            </div>
                        @endif
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

    <script>
        // Sistema de filtros dinámicos para impresoras
        const filterData = {
            'nombre-impresora': {
                type: 'searchable-select',
                searchPlaceholder: 'Buscar nombre de impresora...',
                options: [
                    { value: '', text: 'Todas las impresoras' },
                    @foreach($all_impresoras as $impresora)
                    { value: '{{ $impresora->id }}', text: '{{ $impresora->impresora }}' },
                    @endforeach
                ]
            },
            'gerencia_id': {
                type: 'searchable-select',
                searchPlaceholder: 'Buscar gerencia...',
                options: [
                    { value: '', text: 'Todas las gerencias' },
                    @foreach($gerencias as $gerencia)
                    { value: '{{ $gerencia->id }}', text: '{{ $gerencia->gerencia }}@if($gerencia->area) - {{ $gerencia->area->area }}@endif' },
                    @endforeach
                ]
            },
            'created_at': {
                type: 'date'
            }
        };

        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilter = document.getElementById('secondFilter');
            const searchableFilter = document.getElementById('searchableFilter');
            const textFilter = document.getElementById('textFilter');
            const dateFilter = document.getElementById('dateFilter');

            // Ocultar todos los filtros secundarios
            secondFilter.classList.add('hidden');
            searchableFilter.classList.add('hidden');
            textFilter.classList.add('hidden');
            dateFilter.classList.add('hidden');

            // Cerrar dropdown si está abierto
            closeSearchableDropdown();

            if (!filterType) {
                secondFilter.disabled = true;
                secondFilter.innerHTML = '<option value="">Primero selecciona un filtro</option>';
                secondFilter.classList.remove('hidden');
                return;
            }

            const filterConfig = filterData[filterType];

            if (filterConfig.type === 'select') {
                // Mostrar select normal con opciones
                secondFilter.disabled = false;
                secondFilter.innerHTML = '';
                filterConfig.options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.value;
                    optionElement.textContent = option.text;
                    secondFilter.appendChild(optionElement);
                });
                secondFilter.classList.remove('hidden');

            } else if (filterConfig.type === 'searchable-select') {
                // Mostrar filtro con buscador personalizado
                setupSearchableFilter(filterConfig);
                searchableFilter.classList.remove('hidden');

            } else if (filterConfig.type === 'text') {
                // Mostrar input de texto
                textFilter.classList.remove('hidden');
                textFilter.addEventListener('input', applyTextFilter);
            } else if (filterConfig.type === 'date') {
                // Mostrar filtros de fecha
                dateFilter.classList.remove('hidden');
                // Restaurar valores de fecha si existen
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('fecha_desde')) {
                    document.getElementById('fecha_desde').value = urlParams.get('fecha_desde');
                }
                if (urlParams.get('fecha_hasta')) {
                    document.getElementById('fecha_hasta').value = urlParams.get('fecha_hasta');
                }
            }
        }

        function applyTextFilter() {
            const text = document.getElementById('textFilter').value;
            if (text.length > 2) {
                showToast(`Buscando: ${text}`, 'info');
            }
        }

        // Funciones para filtros con buscador
        let currentSearchableOptions = [];
        let selectedSearchableValue = '';

        function setupSearchableFilter(filterConfig) {
            const button = document.getElementById('searchableFilterButton');
            const text = document.getElementById('searchableFilterText');
            const input = document.getElementById('searchableFilterInput');
            const optionsContainer = document.getElementById('searchableFilterOptions');

            currentSearchableOptions = filterConfig.options;
            selectedSearchableValue = '';

            // Limpiar opciones previas
            optionsContainer.innerHTML = '';

            // Crear opciones
            filterConfig.options.forEach(option => {
                const optionEl = document.createElement('div');
                optionEl.classList.add('searchable-select-option');
                optionEl.textContent = option.text;
                optionEl.dataset.value = option.value;
                optionEl.addEventListener('click', () => {
                    selectedSearchableValue = option.value;
                    text.textContent = option.text;
                    closeSearchableDropdown();
                    // Aplicar filtro redirigiendo con parámetro
                    const filterType = document.getElementById('filterType').value;
                    if (option.value) {
                        window.location.href = '{{ route("administracion.impresoras.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.impresoras.index") }}';
                    }
                });
                optionsContainer.appendChild(optionEl);
            });

            // Mostrar texto inicial
            text.textContent = filterConfig.options[0].text;

            // Abrir dropdown al hacer click en el botón
            button.onclick = () => {
                toggleSearchableDropdown();
            };

            // Filtrar opciones al escribir
            input.oninput = () => {
                const filterText = input.value.toLowerCase();
                const optionEls = optionsContainer.querySelectorAll('.searchable-select-option');
                optionEls.forEach(el => {
                    if (el.textContent.toLowerCase().includes(filterText)) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
            };
        }

        function toggleSearchableDropdown() {
            const dropdown = document.getElementById('searchableFilterDropdown');
            dropdown.classList.toggle('open');
        }

        function closeSearchableDropdown() {
            const dropdown = document.getElementById('searchableFilterDropdown');
            dropdown.classList.remove('open');
        }

        function applySelectFilter() {
            const filterType = document.getElementById('filterType').value;
            const filterValue = document.getElementById('secondFilter').value;
            if (filterValue) {
                window.location.href = '{{ route("administracion.impresoras.index") }}?' + filterType + '=' + filterValue;
            } else {
                window.location.href = '{{ route("administracion.impresoras.index") }}';
            }
        }

        function applyDateFilter() {
            const fecha_desde = document.getElementById('fecha_desde').value;
            const fecha_hasta = document.getElementById('fecha_hasta').value;

            let url = '{{ route("administracion.impresoras.index") }}?';
            let params = [];

            if (fecha_desde) {
                params.push('fecha_desde=' + fecha_desde);
            }
            if (fecha_hasta) {
                params.push('fecha_hasta=' + fecha_hasta);
            }

            if (params.length > 0) {
                window.location.href = url + params.join('&');
            } else {
                window.location.href = '{{ route("administracion.impresoras.index") }}';
            }
        }

        function clearFilters() {
            document.getElementById('filterType').value = '';
            updateSecondFilter();
            // Limpiar filtros aplicados y recargar la página sin parámetros
            window.location.href = '{{ route("administracion.impresoras.index") }}';
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('searchableFilterDropdown');
            const button = document.getElementById('searchableFilterButton');
            if (dropdown && button) {
                if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                    closeSearchableDropdown();
                }
            }
        });

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
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/administracion/impresoras/${id}`;

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

        // Inicializar filtros al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            updateSecondFilter();

            // Restaurar el valor del filtro principal si hay parámetros en la URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('nombre-impresora')) {
                document.getElementById('filterType').value = 'nombre-impresora';
                updateSecondFilter();
            } else if (urlParams.has('gerencia_id')) {
                document.getElementById('filterType').value = 'gerencia_id';
                updateSecondFilter();
            } else if (urlParams.has('fecha_desde') || urlParams.has('fecha_hasta')) {
                document.getElementById('filterType').value = 'created_at';
                updateSecondFilter();
            }
        });
    </script>
</x-app-layout>
@endcan
