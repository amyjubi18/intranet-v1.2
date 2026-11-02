@can('usuario_administrativo_index')
<x-app-layout>
    @section('title', 'Usuarios Administrativos')
    @section('subtitle', 'Gestión de usuarios administrativos')
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

        /* Animaciones */
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
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
        <div class="p-6 glass rounded-2xl fade-in mb-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro principal - Tipo de campo -->
                    <select id="filterType" onchange="updateSecondFilter()" class="px-4 py-2 text-sm text-white bg-blue-900 border border-blue-600 hover:bg-blue-800 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        <option value="">Seleccionar filtro</option>
                        <option value="usuario">Usuario Administrativo</option>
                        <option value="unidad">Unidad Administrativa</option>
                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative" style="z-index: 99999;">
                        <select id="secondFilter" onchange="applySelectFilter()" class="px-4 py-2 text-sm bg-blue-100 glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50" disabled>
                            <option value="">Primero selecciona un filtro</option>
                        </select>

                        <!-- Filtro con buscador personalizado -->
                        <div id="searchableFilter" class="hidden searchable-select">
                            <button type="button" id="searchableFilterButton" class="flex items-center justify-between w-full px-4 py-2 text-sm text-left glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50">
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

                        <!-- Input de texto para búsqueda (oculto por defecto) -->
                        <input type="text" id="textFilter" placeholder="Buscar..." class="hidden px-4 py-2 text-sm glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    </div>

                    <!-- Botón para limpiar filtros -->
                    <button onclick="clearFilters()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    @can('usuario_administrativo_create')
                    <a href="{{ route('administracion.usuario_administrativo.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Registro de usuarios administrativos -->
    <div class="px-6 mb-8">
        <div class="space-y-4 fade-in">
            @forelse ($usuario_administrativos as $usuario)
            <!-- Registro -->
            <div class="glass justify-between rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center justify-between grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Usuario Administrativo -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">👤 Usuario Administrativo</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit" >{{ $usuario->usuario_administrativo }}</p>
                    </div>

                    <!-- Unidad Administrativa -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-green-900">🏢 Unidad Administrativa</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit">{{ $usuario->unidad_administrativa->name ?? 'Sin unidad' }}</p>
                    </div>

                    <!-- Acciones -->
                    <div class="text-center lg:text-left">
                        <div class="flex justify-center gap-2 lg:justify-start">
                            @can('usuario_administrativo_edit')
                            <a href="{{ route('administracion.usuario_administrativo.edit', $usuario->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('usuario_administrativo_destroy')
                            <button onclick="confirmDelete({{ $usuario->id }}, '{{ $usuario->usuario_administrativo }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
                <p class="text-slate-400">No hay ningún usuario administrativo registrado</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($usuario_administrativos->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $usuario_administrativos->links() }}
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
        // Sistema de filtros dinámicos para usuarios administrativos
        const filterData = {
            'usuario': {
                type: 'searchable-select',
                searchPlaceholder: 'Buscar usuario administrativo...',
                options: [
                    { value: '', text: 'Todos los usuarios' },
                    @foreach($todosLosUsuariosAdministrativos as $usuario)
                    { value: '{{ $usuario->id }}', text: '{{ addslashes($usuario->usuario_administrativo) }}' },
                    @endforeach
                ]
            },
            'unidad': {
                type: 'searchable-select',
                searchPlaceholder: 'Buscar unidad administrativa...',
                options: [
                    { value: '', text: 'Todas las unidades' },
                    @foreach($todasLasUnidadesAdministrativas as $unidad)
                    { value: '{{ $unidad->id }}', text: '{{ addslashes($unidad->name) }}' },
                    @endforeach
                ]
            }
        };

        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilter = document.getElementById('secondFilter');
            const searchableFilter = document.getElementById('searchableFilter');
            const textFilter = document.getElementById('textFilter');

            // Ocultar todos los filtros secundarios
            secondFilter.classList.add('hidden');
            searchableFilter.classList.add('hidden');
            textFilter.classList.add('hidden');

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
            }

            showToast(`Filtro por ${filterType} seleccionado`, 'info');
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
                        window.location.href = '{{ route("administracion.usuario_administrativo.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.usuario_administrativo.index") }}';
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
                window.location.href = '{{ route("administracion.usuario_administrativo.index") }}?' + filterType + '=' + filterValue;
            } else {
                window.location.href = '{{ route("administracion.usuario_administrativo.index") }}';
            }
        }

        function clearFilters() {
            document.getElementById('filterType').value = '';
            updateSecondFilter();
            // Limpiar filtros aplicados y recargar la página sin parámetros
            window.location.href = '{{ route("administracion.usuario_administrativo.index") }}';
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('searchableFilterDropdown');
            const button = document.getElementById('searchableFilterButton');
            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                closeSearchableDropdown();
            }
        });

        // Función para confirmar eliminación
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar el usuario administrativo "${name}"? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/usuario_administrativo/${id}`;

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

        // Inicializar filtros al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            updateSecondFilter();
            showToast('Módulo de usuarios administrativos cargado', 'info');
        });
    </script>
</x-app-layout>
@endcan

