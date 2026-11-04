@can('usuario_de_uso_index')
<x-app-layout>
    @section('title', 'Usuarios de Uso')
    @section('subtitle', 'Gestión de usuarios de uso')
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

        .searchable-select {
            position: relative;
            z-index: 99999;
        }
        .searchable-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #ffffff !important;
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
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
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

        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }

       
    </style>

<div class="sticky top-0 z-50 px-6 mb-6" style="position: sticky; top: 0; z-index: 99999;">
        <div class="p-6 glass rounded-2xl fade-in">
            <div class="relative flex flex-wrap items-center justify-between gap-4" style="z-index: 99999;">
                <div class="relative flex flex-wrap items-center gap-4" style="z-index: 99999;">
                    <select id="filterType" onchange="updateSecondFilter()" class="relative px-4 py-2 text-sm text-white bg-blue-900 border border-blue-600 hover:bg-blue-800 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50" style="z-index: 99999;">
                        <option value="">Seleccionar filtro</option>
                        <option value="usuario">Usuario de Uso</option>
                        <option value="unidad">Unidad Administrativa</option>
                    </select>

                    <div id="secondFilterContainer" class="relative" style="z-index: 99999;">
                        <select id="secondFilter" onchange="applySelectFilter()" class="px-4 py-2 text-sm bg-blue-100 glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50" disabled>
                            <option value="">Primero selecciona un filtro</option>
                        </select>

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
                                </div>
                        </div>
                        </div>

                        <input type="text" id="textFilter" placeholder="Buscar..." class="hidden px-4 py-2 text-sm glass rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    </div>

<button onclick="clearFilters()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </button>

                    <!-- Selector de ordenamiento -->
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500">Ordenar:</span>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'usuario_de_uso', 'direction' => $sort === 'usuario_de_uso' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                           class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'usuario_de_uso' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                            Usuario
                            @if($sort === 'usuario_de_uso')
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
                </div>

                <div class="flex items-center gap-3">
                    @can('usuario_de_uso_create')
                    <a href="{{ route('administracion.usuario_de_uso.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 mb-8">
        <div class="space-y-4 fade-in">
            @forelse ($usuario_de_usos as $usuario_de_uso)
            <div class="glass justify-between rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center justify-between grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">👤 Usuario de Uso</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit" >{{ $usuario_de_uso->usuario_de_uso }}</p>
                    </div>

                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-green-900">🏢 Unidad Administrativa</p>
                        <p class="p-2 text-lg font-semibold text-black rounded-lg w-fit">{{ $usuario_de_uso->unidad_administrativa->name ?? 'Sin unidad' }}</p>
                    </div>

                    <div class="text-center lg:text-left">
                        <div class="flex justify-center gap-2 lg:justify-start">
                            @can('usuario_de_uso_edit')
                            <a href="{{ route('administracion.usuario_de_uso.edit', $usuario_de_uso->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('usuario_de_uso_destroy')
                            <button onclick="confirmDelete({{ $usuario_de_uso->id }}, '{{ $usuario_de_uso->usuario_de_uso }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
                <p class="text-slate-400">No hay ningún usuario de uso registrado</p>
            </div>
            @endforelse
        </div>

        @if($usuario_de_usos->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $usuario_de_usos->links() }}
            </div>
        </div>
        @endif
    </div>

    <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
    </div>
    </div>

    <script>
        const filterData = {
            'usuario': {
                type: 'searchable-select',
                searchPlaceholder: 'Buscar usuario de uso...',
                options: [
                    { value: '', text: 'Todos los usuarios' },
                    @foreach($todosLosUsuariosDeUso as $usuario)
                    { value: '{{ $usuario->id }}', text: '{{ addslashes($usuario->usuario_de_uso) }}' },
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

            secondFilter.classList.add('hidden');
            searchableFilter.classList.add('hidden');
            textFilter.classList.add('hidden');

            closeSearchableDropdown();

            if (!filterType) {
                secondFilter.disabled = true;
                secondFilter.innerHTML = '<option value="">Primero selecciona un filtro</option>';
                secondFilter.classList.remove('hidden');
                return;
            }

            const filterConfig = filterData[filterType];

            if (filterConfig.type === 'select') {
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
                setupSearchableFilter(filterConfig);
                searchableFilter.classList.remove('hidden');

            } else if (filterConfig.type === 'text') {
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

        let currentSearchableOptions = [];
        let selectedSearchableValue = '';

        function setupSearchableFilter(filterConfig) {
            const button = document.getElementById('searchableFilterButton');
            const text = document.getElementById('searchableFilterText');
            const input = document.getElementById('searchableFilterInput');
            const optionsContainer = document.getElementById('searchableFilterOptions');

            currentSearchableOptions = filterConfig.options;
            selectedSearchableValue = '';

            optionsContainer.innerHTML = '';

            filterConfig.options.forEach(option => {
                const optionEl = document.createElement('div');
                optionEl.classList.add('searchable-select-option');
                optionEl.textContent = option.text;
                optionEl.dataset.value = option.value;
                optionEl.addEventListener('click', () => {
                    selectedSearchableValue = option.value;
                    text.textContent = option.text;
                    closeSearchableDropdown();
                    const filterType = document.getElementById('filterType').value;
                    if (option.value) {
                        window.location.href = '{{ route("administracion.usuario_de_uso.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.usuario_de_uso.index") }}';
                    }
                });
                optionsContainer.appendChild(optionEl);
            });

            text.textContent = filterConfig.options[0].text;

            button.onclick = () => {
                toggleSearchableDropdown();
            };

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
                window.location.href = '{{ route("administracion.usuario_de_uso.index") }}?' + filterType + '=' + filterValue;
            } else {
                window.location.href = '{{ route("administracion.usuario_de_uso.index") }}';
            }
        }

        function clearFilters() {
            document.getElementById('filterType').value = '';
            updateSecondFilter();
            window.location.href = '{{ route("administracion.usuario_de_uso.index") }}';
        }

        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('searchableFilterDropdown');
            const button = document.getElementById('searchableFilterButton');
            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                closeSearchableDropdown();
            }
        });

        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar el usuario de uso "${name}"? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/usuario_de_uso/${id}`;

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

        document.addEventListener('DOMContentLoaded', () => {
            updateSecondFilter();
            showToast('Módulo de usuarios de uso cargado', 'info');
        });
    </script>
</x-app-layout>
@endcan
