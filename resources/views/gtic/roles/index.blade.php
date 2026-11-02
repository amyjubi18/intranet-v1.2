@section('title', 'Roles')
@section('subtitle', 'Gestión de roles y permisos')
<x-app-layout>

    <x-slot name="scripts">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
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

        /* Role Card */
        .role-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(8px);
            border-radius: 1rem;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
        }

        .role-id {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.65rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .role-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e3a8a;
            word-break: break-word;
        }

        .permissions-label {
            font-size: 0.7rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .permissions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            max-height: 100px;
            overflow-y: auto;
        }

        .permissions-list::-webkit-scrollbar {
            width: 4px;
        }

        .permissions-list::-webkit-scrollbar-track {
            background: rgba(59, 130, 246, 0.1);
            border-radius: 2px;
        }

        .permissions-list::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 2px;
        }

        .permission-badge {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            padding: 0.2rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.65rem;
            font-weight: 500;
            border: 1px solid rgba(59, 130, 246, 0.2);
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
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
    </style>

    <!-- Main Content -->

    <!-- Filtros Avanzados -->
    <form method="GET" action="{{ route('gtic.roles.index') }}" id="filterForm">
        <div class="px-4 sm:px-6 pb-4">
            <div class="glass rounded-xl p-4 mb-4 sm:mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
<!-- Búsqueda rápida con debounce -->
                        <div class="relative">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400">
                                <circle cx="10" cy="10" r="7"/>
                                <path d="M21 21l-4.35-4.35"/>
                            </svg>
                            <input type="search" name="search" id="rolesSearchInput" placeholder="Buscar rol..." value="{{ request('search') }}" class="form-input pl-8 text-xs" style="width: 200px;">
                        </div>

                        <!-- Botón para limpiar filtros -->
                        <a href="{{ route('gtic.roles.index') }}" class="btn btn-secondary text-xs">
                            🗑️ Limpiar
                        </a>

                        <!-- Selector de ordenamiento -->
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-slate-500">Ordenar:</span>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'name' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                                Nombre
                                @if($sort === 'name')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-slate-400">⇅</span>
                                @endif
                            </a>
                            {{-- <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'id' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                                ID
                                @if($sort === 'id')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-slate-400">⇅</span>
                                @endif
                            </a> --}}
                        </div>
                    </div>

                    <!-- Botón Añadir Rol -->
                    <a href="{{ route('gtic.roles.create') }}" class="btn bg-blue-900 text-white hover:bg-blue-700 text-xs">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/>
                        </svg>
                        Añadir
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Roles Cards -->
    <div class="px-4 sm:px-6 pb-8">
        @if($roles->count() > 0)
            <div class="roles-grid">
                @foreach($roles as $role)
                    <div class="role-card fade-in">
                        <!-- Header de la tarjeta -->
                        <div>
                            <div class="role-id">ID: {{ $role->id }}</div>
                            <div class="role-name">{{ $role->name }}</div>
                        </div>

                        <!-- Permisos -->
                        <div class="permissions-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="M9 12l2 2 4-4"/>
                            </svg>
                            Permisos ({{ $role->permissions->count() }})
                        </div>
                        <div class="permissions-list">
                            @forelse($role->permissions as $permission)
                                <span class="permission-badge">{{ $permission->name }}</span>
                            @empty
                                <span class="permission-badge" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; border-color: rgba(220, 38, 38, 0.2);">Sin permisos</span>
                            @endforelse
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-actions">
                            <a href="{{ route('gtic.roles.edit', $role->id) }}" class="btn btn-success">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"/>
                                </svg>
                                Editar
                            </a>
<form action="{{ route('gtic.roles.destroy', $role->id) }}" method="POST" class="delete-role-form" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-delete-role">
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
            @if($roles->hasPages())
            <div class="px-6 mt-8">
                <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                    {!! $roles->appends(request()->query())->links() !!}
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
                <h3 class="text-sm font-semibold text-slate-600 mb-1">No hay roles registrados</h3>
                <p class="text-xs text-slate-400 mb-3">Comienza creando un nuevo rol</p>
                <a href="{{ route('gtic.roles.create') }}" class="btn btn-primary inline-flex">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"/>
                    </svg>
                    Añadir Rol
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

            // ==========================================
            // BUSCADOR CON DEBOUNCE - ROLES
            // ==========================================
            const searchInput = document.getElementById('rolesSearchInput');
            const filterForm = document.getElementById('filterForm');

            if (searchInput && filterForm) {
                let searchTimeout;

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);

                    // Debounce: esperar 300ms antes de buscar (más rápido)
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 300);
                });
            }

            // Confirmar eliminación de rol con SweetAlert2
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-delete-role')) {
                    const button = e.target.closest('.btn-delete-role');
                    const form = button.closest('.delete-role-form');
                    const roleId = form.getAttribute('data-role-id');
                    const roleName = form.getAttribute('data-role-name');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: `¿Deseas eliminar el rol "${roleName}"? Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>
</x-app-layout>

