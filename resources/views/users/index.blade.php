@section('title', 'Usuarios')
@section('subtitle', 'Gestión de usuarios del sistema')
<x-app-layout>

    <x-slot name="scripts">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
        <!-- SweetAlert2 se carga desde el layout principal -->
    </x-slot>

    <style>
        :root{
            --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B;
            --bg-from:#f0f9ff; --bg-to:#dbeafe;
            --text:#1e3a8a;
            --card-bg:rgba(255,255,255,0.8); --card-border:rgba(59,130,246,0.15);
            --link-hover-bg: rgba(59,130,246,0.08);
            --muted:#64748b;
        }

        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 25%, #bfdbfe 50%, #93c5fd 75%, #60a5fa 100%);
            color:var(--text);
            min-height: 100%;
        }

        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; border-radius:.75rem; padding:.5rem .75rem; font-weight:600; transition:.2s; border:1px solid var(--card-border); cursor: pointer; font-size: 0.75rem; }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff; border-color: var(--color-primary); }
        .btn-primary:hover{ background:#1e40af; }
        .btn-secondary{ background:rgba(15,23,42,0.06); }
        .btn-secondary:hover{ background:rgba(15,23,42,0.1); }
        .btn-success{ background:var(--color-secondary); color:#fff; border-color: var(--color-secondary); }
        .btn-success:hover{ background:#16a34a; }
        .btn-danger{ background:#dc2626; color:#fff; border-color: #dc2626; }
        .btn-danger:hover{ background:#b91c1c; }

        .main-content{
            min-height:100vh;
        }

        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }

        .form-input {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--card-border);
            border-radius: 0.5rem;
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
            font-size: 0.75rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 200;
            opacity: 0;
            visibility: hidden;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .toast.show {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .toast.success {
            border-color: var(--color-secondary);
        }

        .toast.error {
            border-color: #dc2626;
        }

        /* User Card */
        .user-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(8px);
            border-radius: 1rem;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
        }

        .user-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--color-primary);
        }

        .user-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            border: 2px solid var(--color-primary);
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .info-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: var(--muted);
        }

        .info-label {
            font-size: 0.7rem;
            color: var(--muted);
            min-width: 70px;
        }

        .info-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .role-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .status-badge.active {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-badge.active::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
        }

        .status-badge.inactive {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
        }

        .status-badge.inactive::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #94a3b8;
            border-radius: 50%;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--card-border);
        }

        .card-actions .btn {
            flex: 1;
        }

        

        /* Grid */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
    </style>

    <!-- Main Content -->



        <!-- Filtros Avanzados -->
        <form method="GET" action="{{ route('users.index') }}" id="filterForm">
        <div class="px-4 sm:px-6 pb-4">
            <div class="glass rounded-xl p-4 mb-4 sm:mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Botón para mostrar filtros avanzados -->
                        <button type="button" onclick="toggleAdvancedFilters()" class="btn btn-primary text-xs">
                            🔍 Filtros
                        </button>



                        <!-- Filtro principal - Tipo de campo (oculto por defecto) -->
                        <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="form-input text-xs" style="{{ request('filterType') ? '' : 'display:none;' }}">
                            <option value="">Seleccionar filtro</option>
                            <option value="nombre" {{ request('filterType') == 'nombre' ? 'selected' : '' }}>Nombre</option>
                            <option value="cedula" {{ request('filterType') == 'cedula' ? 'selected' : '' }}>Cédula</option>
                            <option value="correo" {{ request('filterType') == 'correo' ? 'selected' : '' }}>Correo</option>
                            <option value="username" {{ request('filterType') == 'username' ? 'selected' : '' }}>Username</option>
                            <option value="departamento" {{ request('filterType') == 'departamento' ? 'selected' : '' }}>Departamento</option>
                            <option value="rol" {{ request('filterType') == 'rol' ? 'selected' : '' }}>Rol</option>
                            <option value="status" {{ request('filterType') == 'status' ? 'selected' : '' }}>Estado</option>
                            <option value="busqueda" {{ request('filterType') == 'busqueda' ? 'selected' : '' }}>Búsqueda General</option>
                        </select>

                        <!-- Filtro secundario - Dinámico según selección -->
                        <div id="secondFilterContainer" class="relative z-50">
                            <select id="secondFilter" name="secondFilter" class="form-input text-xs" {{ request('filterType') ? '' : 'disabled style="display:none;"' }}>
                                <option value="">-----</option>
                                @if(request('filterType') == 'rol')
                                    @foreach($roles ?? [] as $id => $role)
                                        <option value="{{ $id }}" {{ request('secondFilter') == $id ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                @elseif(request('filterType') == 'status')
                                    <option value="1" {{ request('secondFilter') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ request('secondFilter') == '0' ? 'selected' : '' }}>Inactivo</option>
                                @endif
                            </select>
                        </div>

                        <!-- Input de texto para filtros específicos (nombre, cedula, correo, username, departamento) -->
                        <input type="text" id="textFilter" name="textFilter" placeholder="Valor del filtro..." class="form-input text-xs" value="{{ request('textFilter') }}" style="{{ in_array(request('filterType'), ['nombre', 'cedula', 'correo', 'username', 'departamento']) ? '' : 'display:none;' }}">

                        <!-- Input de búsqueda general (oculto por defecto) -->
                        <input type="text" id="generalSearch" name="busqueda" placeholder="Buscar en todos los campos..." class="form-input text-xs" value="{{ request('busqueda') }}" style="{{ request('filterType') == 'busqueda' ? '' : 'display:none;' }}">

                        <!-- Botón para limpiar filtros -->
                        <a href="{{ route('users.index') }}" class="btn btn-secondary text-xs">
                            🗑️ Limpiar
                        </a>

                        <!-- Selector de Ordenamiento -->
                        <div class="flex items-center gap-1 ml-2 border-l pl-3 border-slate-300">
                            <span class="text-xs text-slate-500 font-medium">Ordenar:</span>
                            <button type="button" onclick="toggleSortDirection()" class="flex items-center gap-1 px-2 py-1 rounded bg-blue-100 hover:bg-blue-200 transition-colors text-xs font-medium text-blue-800">
                                <span id="sortIcon">
                                    @if(isset($direction) && $direction == 'asc')
                                        ↑
                                    @else
                                        ↓
                                    @endif
                                </span>
                                <span id="sortLabel">@if(isset($sort) && $sort == 'name') Nombre @elseif(isset($sort) && $sort == 'created_at') Fecha @elseif(isset($sort) && $sort == 'cedula') Cédula @elseif(isset($sort) && $sort == 'email') Correo @else Nombre @endif</span>
                            </button>
                        </div>

                        <!-- Campos ocultos para sort y direction -->
                        <input type="hidden" name="sort" id="sortInput" value="{{ $sort ?? 'name' }}">
                        <input type="hidden" name="direction" id="directionInput" value="{{ $direction ?? 'desc' }}">
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Búsqueda rápida con debounce -->
                        <div class="relative">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400">
                                <circle cx="10" cy="10" r="7"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                            <input type="search" name="search" id="quickSearch" placeholder="Búsqueda rápida..." value="{{ request('search') }}" class="form-input pl-8 text-xs" style="width: 200px;">
                        </div>
                    </div>
                      <!-- Botón Añadir Usuario -->
                        <a href="{{ route('users.create') }}" class="btn bg-blue-900 text-white hover:bg-blue-700 text-xs">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/>
                            </svg>
                            Añadir
                        </a>
                </div>
            </div>
        </div>
        </form>

        <!-- Users Cards -->
        <div class="px-4 sm:px-6 pb-8">
            @if($users->count() > 0)
                <div class="users-grid">
                    @foreach($users as $user)
                        <div class="user-card fade-in">
                            <!-- Header de la tarjeta -->
                            <div class="flex items-start gap-3 mb-3">
                                @if($user->profile_photo)
                                    <img src="{{ asset('archivos/avatars/' . $user->profile_photo) }}"
                                         alt="{{ $user->name }}"
                                         class="user-photo"
                                         onerror="this.onerror=null; this.parentElement.querySelector('.avatar-fallback').style.display='flex'; this.style.display='none';">
                                @else
                                    <div class="user-avatar-placeholder avatar-fallback" style="display: flex;">
                                @endif

                                @if(!$user->profile_photo)
                                    </div>
                                @else
                                    <div class="user-avatar-placeholder avatar-fallback" style="display: none;">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-sm text-blue-800 truncate">{{ $user->name }} {{ $user->lastname }}</h3>
                                    <span class="status-badge {{ $user->status ? 'active' : 'inactive' }}">
                                        {{ $user->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Información del usuario -->
                            <div class="space-y-1">
                                <!-- Cédula -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span class="info-label">Cédula:</span>
                                    <span class="info-value">{{ $user->cedula }}</span>
                                </div>

                                <!-- Username -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span class="info-label">Username:</span>
                                    <span class="info-value">@ {{ $user->username }}</span>
                                </div>

                                <!-- Fecha de nacimiento -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="info-label">Nac.:</span>
                                    <span class="info-value">{{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') }}</span>
                                </div>

                                <!-- Correo -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <polyline points="22,6 12,13 2,6"/>
                                    </svg>
                                    <span class="info-label">Correo:</span>
                                    <span class="info-value truncate">{{ $user->email }}</span>
                                </div>

                                <!-- Departamento -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                    </svg>
                                    <span class="info-label">Depto:</span>
                                    <span class="info-value">{{ $user->departamento }}</span>
                                </div>

                                <!-- Rol -->
                                <div class="info-row">
                                    <svg class="info-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    </svg>
                                    <span class="info-label">Rol:</span>
                                    @forelse($user->roles as $role)
                                        <span class="role-badge">{{ $role->name }}</span>
                                    @empty
                                        <span class="role-badge" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border-color: rgba(220, 38, 38, 0.2);">Sin rol</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="card-actions">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-success">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"/>
                                    </svg>
                                    Editar
                                </a>
<form id="delete-form-{{ $user->id }}" action="{{ route('users.delete', $user->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-delete-user" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }} {{ $user->lastname }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                <div class="px-6 mt-8">
                    <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                        {!! $users->appends(request()->query())->links() !!}
                    </div>
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="glass rounded-xl p-8 text-center">
                    <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600 mb-1">No hay usuarios registrados</h3>
                    <p class="text-xs text-slate-400 mb-3">Comienza agregando un nuevo usuario</p>
                    <a href="{{ route('users.create') }}" class="btn btn-primary inline-flex">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/>
                        </svg>
                        Añadir Usuario
                    </a>
                </div>
            @endif
        </div>

    <!-- Toast -->
    @if(session('success'))
    <div id="toast" class="toast show success">
        <div id="toastIcon">✅</div>
        <div id="toastMessage">{{ session('success') }}</div>
    </div>
    @endif

@if(session('error'))
    <div id="toast" class="toast show error">
        <div id="toastIcon">❌</div>
        <div id="toastMessage">{{ session('error') }}</div>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast');
            if (toast && toast.classList.contains('show')) {
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }

            // Inicializar filtros según la URL actual
            updateSecondFilter();
        });

        // ==========================================
        // FUNCIONES DE FILTROS - USUARIOS
        // ==========================================

        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const secondFilter = document.getElementById('secondFilter');
            const textFilter = document.getElementById('textFilter');
            const generalSearch = document.getElementById('generalSearch');

            // Ocultar todos los filtros primero
            if (secondFilterContainer) {
                secondFilterContainer.style.display = 'none';
                secondFilter.disabled = true;
            }
            if (textFilter) textFilter.style.display = 'none';
            if (generalSearch) generalSearch.style.display = 'none';

            // Mostrar según la selección
            if (filterType === 'rol' || filterType === 'status') {
                if (secondFilterContainer) {
                    secondFilterContainer.style.display = 'block';
                    secondFilter.disabled = false;
                }
            } else if (filterType === 'nombre' || filterType === 'cedula' || filterType === 'correo' || filterType === 'username' || filterType === 'departamento') {
                if (textFilter) {
                    textFilter.style.display = 'block';
                    // Cambiar el name del input según el tipo de filtro
                    textFilter.name = filterType;
                }
            } else if (filterType === 'busqueda') {
                if (generalSearch) generalSearch.style.display = 'block';
            }
        }

        // Mostrar/ocultar select de filtro principal
        function toggleAdvancedFilters() {
            const filterSelect = document.getElementById('filterType');
            if (filterSelect.style.display === 'none' || filterSelect.style.display === '') {
                filterSelect.style.display = 'inline-block';
            } else {
                filterSelect.style.display = 'none';
                filterSelect.value = '';
                updateSecondFilter();
                document.getElementById('filterForm').submit();
            }
        }

        // Auto-submit para selects de filtros
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');

            // ==========================================
            // BUSCADOR RÁPIDO CON DEBOUNCE - USUARIOS
            // ==========================================
            const quickSearch = document.getElementById('quickSearch');
            if (quickSearch && filterForm) {
                let searchTimeout;

                quickSearch.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    // Debounce: esperar 500ms antes de buscar
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }

            // Segundo filtro (rol, status)
            const secondFilter = document.getElementById('secondFilter');
            if (secondFilter) {
                secondFilter.addEventListener('change', function() {
                    filterForm.submit();
                });
            }

            // Búsqueda general
            const generalSearch = document.getElementById('generalSearch');
            if (generalSearch) {
                generalSearch.addEventListener('input', function() {
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }

// Filtros de texto individuales
            const textFilter = document.getElementById('textFilter');
            if (textFilter) {
                textFilter.addEventListener('input', function() {
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }
        });

        // ==========================================
        // ORDENAMIENTO DE COLUMNAS
        // ==========================================

        function toggleSortDirection() {
            const currentDirection = document.getElementById('directionInput').value;
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';

            // Actualizar el campo hidden
            document.getElementById('directionInput').value = newDirection;

            // Actualizar el ícono
            document.getElementById('sortIcon').innerHTML = newDirection === 'asc' ? '↑' : '↓';

            // Enviar el formulario
            document.getElementById('filterForm').submit();
        }

        // Confirmar eliminación con SweetAlert2
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-user')) {
                const button = e.target.closest('.btn-delete-user');
                const userId = button.getAttribute('data-user-id');
                const userName = button.getAttribute('data-user-name');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Deseas eliminar al usuario "${userName}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + userId).submit();
                    }
                });
            }
        });
    </script>
</x-app-layout>

