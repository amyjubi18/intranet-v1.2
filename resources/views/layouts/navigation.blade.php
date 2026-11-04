<!-- Sidebar Navigation -->
<div class="flex flex-col h-full bg-gradient-to-r from-blue-800 to-indigo-900">
    <!-- Header -->
    <div class="sidebar-header flex items-center justify-between p-6 border-b border-blue-700">
        <div class="sidebar-header-content flex items-center gap-3">
            <a href="{{ route('inicio') }}" class="transition-transform hover:scale-105">
                <img src="{{asset('img/Logo.png')}}" class="h-20 w-auto" alt="Logo">
            </a>

        </div>
        <div class="flex items-center gap-2">
            <button onclick="closeSidebar()" class="lg:hidden sidebar-item p-2 hover:bg-blue-700 rounded-lg transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
            <button onclick="toggleSidebar()" class="sidebar-item p-2 hover:bg-blue-700 rounded-lg transition-colors" id="sidebarToggleBtn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" id="sidebarToggleIcon">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav flex-1 overflow-y-auto py-4">
        <!-- Inicio -->
        <a href="{{ route('inicio') }}" class="sidebar-item {{ request()->routeIs('inicio') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M3 12l2 2 4-4 4 4 4-4 4 4 2-2"/>
                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7"/>
                <path d="M9 21v-6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6"/>
            </svg>
            <span>Inicio</span>
        </a>

        @php
            $currentHost = request()->getHost();
            $mainHost = parse_url(preg_match('#^https?://#', config('modules.main_url', 'http://172.17.50.219')) ? config('modules.main_url', 'http://172.17.50.219') : 'http://' . config('modules.main_url', 'http://172.17.50.219'), PHP_URL_HOST);
            $adminHost = parse_url(preg_match('#^https?://#', config('modules.admin_url', 'http://172.17.50.250')) ? config('modules.admin_url', 'http://172.17.50.250') : 'http://' . config('modules.admin_url', 'http://172.17.50.250'), PHP_URL_HOST);
            $ghHost = parse_url(preg_match('#^https?://#', config('modules.gh_url', 'http://172.17.50.251')) ? config('modules.gh_url', 'http://172.17.50.251') : 'http://' . config('modules.gh_url', 'http://172.17.50.251'), PHP_URL_HOST);
            $gticHost = parse_url(preg_match('#^https?://#', config('modules.gtic_url', 'http://172.17.50.252')) ? config('modules.gtic_url', 'http://172.17.50.252') : 'http://' . config('modules.gtic_url', 'http://172.17.50.252'), PHP_URL_HOST);

            $mod = config('modules.current');
            $isMainPortal = ($mod === 'main') || ($currentHost === $mainHost) || str_contains($currentHost, '219') || str_contains($currentHost, 'localhost');
            $isAdminActive = ($mod === 'administracion') || ($currentHost === $adminHost) || str_contains($currentHost, '250');
            $isGhActive = ($mod === 'gestion-humana') || ($currentHost === $ghHost) || str_contains($currentHost, '251');
            $isGticActive = ($mod === 'gtic') || ($currentHost === $gticHost) || str_contains($currentHost, '252');

            $user = Auth::user();
            $isAdminUser = $user && ($user->hasRole('Admin') || $user->hasRole('admin') || $user->hasRole('super-admin'));
            $canAccessAdmin = $user && $user->can('oga');
            $canAccessGh = $user && $user->can('ogh');
            $canAccessGtic = $user && $user->can('gtic');

            // Visibilidad por entorno y rol/permisos
            $showAdmin = $canAccessAdmin && ($isMainPortal || $isAdminUser || $isAdminActive);
            $showGh = $canAccessGh && ($isMainPortal || $isAdminUser || $isGhActive);
            $showGtic = $canAccessGtic && ($isMainPortal || $isAdminUser || $isGticActive);
        @endphp

        <!-- Selector de Módulos con SSO -->
        @if($canAccessAdmin || $canAccessGh || $canAccessGtic)
        <div class="px-4 py-3 my-2 mx-3 bg-blue-950/60 rounded-xl border border-blue-500/30">
            <span class="text-xs uppercase tracking-wider text-blue-300 font-semibold block mb-2">Entornos / Módulos:</span>
            <div class="flex flex-col gap-1.5 text-sm">
                @if($canAccessAdmin)
                <a href="{{ $isAdminActive ? route('inicio') : route('sso.go', 'admin') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg transition-colors {{ $isAdminActive ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-blue-200 hover:bg-blue-800/70 hover:text-white' }}">
                    <span class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 7V3h12v4M9 10h1M14 10h1M9 14h1M14 14h1M9 18h1M14 18h1"/></svg>
                        <span>Administración</span>
                    </span>
                    @if($isAdminActive)
                        <span class="w-2 h-2 rounded-full bg-emerald-400" title="Contenedor activo"></span>
                    @endif
                </a>
                @endif

                @if($canAccessGh)
                <a href="{{ $isGhActive ? route('inicio') : route('sso.go', 'gh') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg transition-colors {{ $isGhActive ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-blue-200 hover:bg-blue-800/70 hover:text-white' }}">
                    <span class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Gestión Humana</span>
                    </span>
                    @if($isGhActive)
                        <span class="w-2 h-2 rounded-full bg-emerald-400" title="Contenedor activo"></span>
                    @endif
                </a>
                @endif

                @if($canAccessGtic)
                <a href="{{ $isGticActive ? route('inicio') : route('sso.go', 'gtic') }}" class="flex items-center justify-between px-3 py-1.5 rounded-lg transition-colors {{ $isGticActive ? 'bg-blue-600 text-white font-medium shadow-sm' : 'text-blue-200 hover:bg-blue-800/70 hover:text-white' }}">
                    <span class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="9" x2="9" y2="15"/><line x1="15" y1="9" x2="15" y2="15"/></svg>
                        <span>GTIC</span>
                    </span>
                    @if($isGticActive)
                        <span class="w-2 h-2 rounded-full bg-emerald-400" title="Contenedor activo"></span>
                    @endif
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($showGtic)
        @can('gtic')
        <!-- GTIC -->
        <div class="sidebar-item" onclick="toggleSubmenu('gtic')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="9" x2="15" y2="9"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
                <line x1="9" y1="9" x2="9" y2="15"/>
                <line x1="15" y1="9" x2="15" y2="15"/>
            </svg>
            <span>GTIC</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="gtic-arrow">
                <polyline points="6,9 12,15 18,9"/>
            </svg>
        </div>
        <div class="submenu ml-4 hidden" id="gtic-submenu">
            @can('user_index')
            <a href="{{ route('users.index') }}" class="sidebar-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Usuarios</span>
            </a>
            @endcan
            @can('role_index')
            <a href="{{ route('gtic.roles.index') }}" class="sidebar-item {{ request()->routeIs('gtic.roles.index') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>Roles</span>
            </a>
            @endcan
            @can('permission_index')
            <a href="{{ route('gtic.permisos.index') }}" class="sidebar-item {{ request()->routeIs('gtic.permisos.index') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Permisos</span>
            </a>
            @endcan
            @can('activity_log_index')
            <a href="{{ route('gtic.activity-log.index') }}" class="sidebar-item {{ request()->routeIs('gtic.activity-log.index') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M9 11H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2h-4m-4 0V9a2 2 0 1 1 4 0v2m-4 0v2a2 2 0 0 0 4 0v-2"/>
                </svg>
                <span>Registro de Actividades</span>
            </a>
            @endcan
        </div>
        @endcan
        @endif

        @if($showAdmin)
        @can('oga')
        <!-- OGA -->
        <div class="sidebar-item" onclick="toggleSubmenu('oga')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Administración (OGA)</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="oga-arrow">
                <polyline points="6,9 12,15 18,9"/>
            </svg>
        </div>
        <div class="submenu ml-4" id="oga-submenu">
            @can('documentos')
            <div class="sidebar-item" onclick="toggleSubmenu('documentos')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                <span>Dig de Documentos</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="documentos-arrow">
                    <polyline points="6,9 12,15 18,9"/>
                </svg>
            </div>
            <div class="submenu ml-4 hidden" id="documentos-submenu">
                @can('documentos_index')
                <a href="{{ route('administracion.documentos.index') }}" class="sidebar-item {{ request()->routeIs('administracion.documentos.index') ? 'active' : '' }}">
                    <span>Documentos</span>
                </a>
                @endcan
                @can('categorias_index')
                <a href="{{ route('administracion.categorias.index') }}" class="sidebar-item {{ request()->routeIs('administracion.categorias.index') ? 'active' : '' }}">
                    <span>Categorías</span>
                </a>
                @endcan
                @can('estanterias_index')
                <a href="{{ route('administracion.estanterias.index') }}" class="sidebar-item {{ request()->routeIs('administracion.estanterias.index') ? 'active' : '' }}">
                    <span>Estanterías</span>
                </a>
                @endcan
            </div>
            @endcan

            @can('bienes')
            <div class="sidebar-item" onclick="toggleSubmenu('bienes')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
                <span>Bienes Muebles</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="bienes-arrow">
                    <polyline points="6,9 12,15 18,9"/>
                </svg>
            </div>
            <div class="submenu ml-4 hidden" id="bienes-submenu">
                @can('bienes_index')
                <a href="{{ route('administracion.bienes_publicos.index') }}" class="sidebar-item {{ request()->routeIs('administracion.bienes_publicos.index') ? 'active' : '' }}">
                    <span>Registros</span>
                </a>
                @endcan
                @if($isAdminUser || $user->can('unidad_adminis_index') || $user->can('usuario_de_uso_index') || $user->can('usuario_administrativo_index') || $user->can('forma_adquisicion_index') || $user->can('estado_uso_index') || $user->can('color_index') || $user->can('condicion_index'))
                <div class="sidebar-item" onclick="toggleSubmenu('carga-previa-bienes')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    <span>Carga Previa</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="carga-previa-bienes-arrow">
                        <polyline points="6,9 12,15 18,9"/>
                    </svg>
                </div>
                <div class="submenu ml-4 hidden" id="carga-previa-bienes-submenu">
                    @can('unidad_adminis_index')
                    <a href="{{ route('administracion.unidad_administrativa.index') }}" class="sidebar-item {{ request()->routeIs('administracion.unidad_administrativa.index') ? 'active' : '' }}">
                        <span>Unidad Administrativa</span>
                    </a>
                    @endcan
                    @can('usuario_de_uso_index')
                    <a href="{{ route('administracion.usuario_de_uso.index') }}" class="sidebar-item {{ request()->routeIs('administracion.usuario_de_uso.index') ? 'active' : '' }}">
                        <span>Usuarios de Uso</span>
                    </a>
                    @endcan
                    @can('usuario_administrativo_index')
                    <a href="{{ route('administracion.usuario_administrativo.index') }}" class="sidebar-item {{ request()->routeIs('administracion.usuario_administrativo.index') ? 'active' : '' }}">
                        <span>Usuarios Administrativo</span>
                    </a>
                    @endcan
                    @can('forma_adquisicion_index')
                    <a href="{{ route('administracion.forma_adquisicion.index') }}" class="sidebar-item {{ request()->routeIs('administracion.forma_adquisicion.index') ? 'active' : '' }}">
                        <span>Forma de Adquisición</span>
                    </a>
                    @endcan
                    @can('estado_uso_index')
                    <a href="{{ route('administracion.estado_del_uso.index') }}" class="sidebar-item {{ request()->routeIs('administracion.estado_del_uso.index') ? 'active' : '' }}">
                        <span>Estado del Uso</span>
                    </a>
                    @endcan
                    @can('color_index')
                    <a href="{{ route('administracion.colors.index') }}" class="sidebar-item {{ request()->routeIs('administracion.colors.index') ? 'active' : '' }}">
                        <span>Color</span>
                    </a>
                    @endcan
                    @can('condicion_index')
                    <a href="{{ route('administracion.condicion_fisica.index') }}" class="sidebar-item {{ request()->routeIs('administracion.condicion_fisica.index') ? 'active' : '' }}">
                        <span>Condición Física</span>
                    </a>
                    @endcan
                </div>
                @endif
            </div>
            @endcan

            @can('inv-oficina')
            <div class="sidebar-item" onclick="toggleSubmenu('inv-oficina')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="7.5,4.27 16.5,9.27"/>
                    <polyline points="3.29,7 12,12 20.71,7"/>
                    <line x1="12" y1="22.5" x2="12" y2="12"/>
                </svg>
                <span>Inv Oficina</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="inv-oficina-arrow">
                    <polyline points="6,9 12,15 18,9"/>
                </svg>
            </div>
            <div class="submenu ml-4 hidden" id="inv-oficina-submenu">
                @can('inventarioofic_index')
                <a href="{{ route('administracion.inventario_oficinas.index') }}" class="sidebar-item {{ request()->routeIs('administracion.inventario_oficinas.index') ? 'active' : '' }}">
                    <span>Ingresos de Oficina</span>
                </a>
                @endcan
                @can('salidaofic_index')
                <a href="{{ route('administracion.salida_oficina.index') }}" class="sidebar-item {{ request()->routeIs('administracion.salida_oficina.index') ? 'active' : '' }}">
                    <span>Salida de Oficina</span>
                </a>
                @endcan
                @if($isAdminUser || $user->can('articulo_index') || $user->can('proveedor_index') || $user->can('unidad_medida_index') || $user->can('area_index') || $user->can('gerencia_index'))
                <div class="sidebar-item" onclick="toggleSubmenu('carga-previa-oficina')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    <span>Carga Previa</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="carga-previa-oficina-arrow">
                        <polyline points="6,9 12,15 18,9"/>
                    </svg>
                </div>
                <div class="submenu ml-4 hidden" id="carga-previa-oficina-submenu">
                    @can('articulo_index')
                    <a href="{{ route('administracion.articulos.index') }}?from=oficina" class="sidebar-item {{ request()->routeIs('administracion.articulos.index') ? 'active' : '' }}">
                        <span>Artículos</span>
                    </a>
                    @endcan
                    @can('proveedor_index')
                    <a href="{{ route('administracion.proveedores.index') }}?from=oficina" class="sidebar-item {{ request()->routeIs('administracion.proveedores.index') ? 'active' : '' }}">
                        <span>Proveedores</span>
                    </a>
                    @endcan
                    @can('unidad_medida_index')
                    <a href="{{ route('administracion.unidad_medidas.index') }}?from=oficina" class="sidebar-item {{ request()->routeIs('administracion.unidad_medidas.index') ? 'active' : '' }}">
                        <span>Unidad de Medida</span>
                    </a>
                    @endcan
                    @can('area_index')
                    <a href="{{ route('administracion.areas.index') }}?from=oficina" class="sidebar-item {{ request()->routeIs('administracion.areas.index') ? 'active' : '' }}">
                        <span>Área</span>
                    </a>
                    @endcan
                    @can('gerencia_index')
                    <a href="{{ route('administracion.gerencias.index') }}?from=oficina" class="sidebar-item {{ request()->routeIs('administracion.gerencias.index') ? 'active' : '' }}">
                        <span>Gerencia</span>
                    </a>
                    @endcan
                </div>
                @endif

            </div>
            @endcan

            @can('inv-consumibles')
            <div class="sidebar-item" onclick="toggleSubmenu('inv-consumibles')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10,9 9,9 8,9"/>
                </svg>
                <span>Inv Consumibles</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="inv-consumibles-arrow">
                    <polyline points="6,9 12,15 18,9"/>
                </svg>
            </div>
            <div class="submenu ml-4 hidden" id="inv-consumibles-submenu">
                {{-- @can('inventarioimpre_index')
                <a href="{{ route('administracion.inventario_impresoras.index') }}" class="sidebar-item {{ request()->routeIs('administracion.inventario_impresoras.index') ? 'active' : '' }}">
                    <span>Ingresos de Consumibles</span>
                </a>
                @endcan --}}
                @can('almacen_inv_index')
                <a href="{{ route('administracion.almacenamiento_impresoras.index') }}" class="sidebar-item {{ request()->routeIs('administracion.almacenamiento_impresoras.index') ? 'active' : '' }}">
                    <span>Almacenamiento de Consumibles</span>
                </a>
                @endcan
                @can('dist_almac_index')
                <a href="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}" class="sidebar-item {{ request()->routeIs('administracion.distribucion_almacenamiento_impresoras.index') ? 'active' : '' }}">
                    <span>Distribución de Consumibles</span>
                </a>
                @endcan

                 @can('salidaimpre_index')
                <a href="{{ route('administracion.salida_almacen.index') }}" class="sidebar-item {{ request()->routeIs('administracion.salida_almacen.index') ? 'active' : '' }}">
                    <span>Salida de Consumibles</span>
                </a>
                @endcan
                @if($isAdminUser || $user->can('cartucho_index') || $user->can('colores_index') || $user->can('impresora_index') || $user->can('fabricante_index') || $user->can('proveedor_index') || $user->can('area_index') || $user->can('gerencia_index'))
                <div class="sidebar-item" onclick="toggleSubmenu('carga-previa')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    <span>Carga Previa</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="carga-previa-arrow">
                        <polyline points="6,9 12,15 18,9"/>
                    </svg>
                </div>
                <div class="submenu ml-4 hidden" id="carga-previa-submenu">
                    @can('cartucho_index')
                    <a href="{{ route('administracion.consumibles.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.consumibles.index') ? 'active' : '' }}">
                        <span>Consumibles</span>
                    </a>
                    @endcan
                    @can('colores_index')
                    <a href="{{ route('administracion.tipo_consumibles.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.tipo_consumibles.index') ? 'active' : '' }}">
                        <span>Tipo Consumibles</span>
                    </a>
                    @endcan
                    @can('impresora_index')
                    <a href="{{ route('administracion.impresoras.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.impresoras.index') ? 'active' : '' }}">
                        <span>Impresoras</span>
                    </a>
                    @endcan
                    @can('fabricante_index')
                    <a href="{{ route('administracion.tipo_impresoras.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.tipo_impresoras.index') ? 'active' : '' }}">
                        <span>Tipo Impresoras</span>
                    </a>
                    @endcan
                    @can('proveedor_index')
                    <a href="{{ route('administracion.proveedores.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.proveedores.index') ? 'active' : '' }}">
                        <span>Proveedores</span>
                    </a>
                    @endcan
                    @can('area_index')
                    <a href="{{ route('administracion.areas.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.areas.index') ? 'active' : '' }}">
                        <span>Área</span>
                    </a>
                    @endcan
                    @can('gerencia_index')
                    <a href="{{ route('administracion.gerencias.index') }}?from=consumibles" class="sidebar-item {{ request()->routeIs('administracion.gerencias.index') ? 'active' : '' }}">
                        <span>Gerencia</span>
                    </a>
                    @endcan
                </div>
                @endif


            </div>
            @endcan
             @can('inventarioimpre_index')
                <a href="{{ route('administracion.reportes.index') }}" class="sidebar-item {{ request()->routeIs('administracion.reportes.index') ? 'active' : '' }}">
                    <span>Reportes</span>
                </a>
                @endcan

        </div>
        @endcan
        @endif

        @if($showGh)
        @can('ogh')
        <!-- OGH -->
        <div class="sidebar-item" onclick="toggleSubmenu('ogh')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>OGH</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="ogh-arrow">
                <polyline points="6,9 12,15 18,9"/>
            </svg>
        </div>
        <div class="submenu ml-4 hidden" id="ogh-submenu">
            @can('documentos')
            <div class="sidebar-item" onclick="toggleSubmenu('ogh-documentos')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                <span>Dig de Documentos</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="ml-auto transition-transform" id="ogh-documentos-arrow">
                    <polyline points="6,9 12,15 18,9"/>
                </svg>
            </div>
            <div class="submenu ml-4 hidden" id="ogh-documentos-submenu">
                @can('documentos_index')
                <a href="{{ route('gestion-humana.digitalizacion_documentos.index') }}" class="sidebar-item {{ request()->routeIs('gestion-humana.digitalizacion_documentos.index') ? 'active' : '' }}">
                    <span>Documentos</span>
                </a>
                @endcan
                @can('area_adscrita_index')
                <a href="{{ route('gestion-humana.area_adscrita.index') }}" class="sidebar-item {{ request()->routeIs('gestion-humana.area_adscrita.index') ? 'active' : '' }}">
                    <span>Area Adscrita</span>
                </a>
                @endcan
                @can('tipo_documento_index')
                <a href="{{ route('gestion-humana.tipo_documentos.index') }}" class="sidebar-item {{ request()->routeIs('gestion-humana.tipo_documentos.index') ? 'active' : '' }}">
                    <span>Tipo de Documento</span>
                </a>
                @endcan
            </div>
            @endcan
        </div>
        @endcan
        @endif
    </div>

    <!-- Bottom Section -->
    <div class="sidebar-bottom p-4 border-t border-blue-700">
        <div class="flex items-center gap-3 mb-4">
            @if(Auth::user()->profile_photo)
                <img src="{{ asset('archivos/avatars/' . Auth::user()->profile_photo) }}"
                     alt="{{ Auth::user()->name }}"
                     class="w-10 h-10 rounded-lg object-cover">
            @else
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-100 flex items-center justify-center">
                    <span class="text-white font-bold">{{ substr(Auth::user()->name, 0, 2) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-md font-bold text-white truncate">{{ Auth::user()->username }}</p>

                <p class="text-xs text-blue-200 truncate">{{ Auth::user()->email ?? 'Usuario' }}</p>
            </div>
        </div>
        <div class="space-y-2">
            @if(!$isMainPortal)
            <a href="{{ config('modules.main_url', 'http://172.17.50.219') }}/inicio" class="sidebar-item text-xs text-blue-200 hover:text-white flex items-center gap-2 bg-blue-900/50 hover:bg-blue-800 rounded-lg p-2 transition-colors">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Volver al Portal Principal</span>
            </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="sidebar-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Perfil</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="sidebar-item w-full text-left">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para obtener todos los padres de un menú
    function getAllParents(menuId, parentMap) {
        const parents = [];
        let current = menuId;
        while (parentMap[current]) {
            current = parentMap[current];
            parents.push(current);
        }
        return parents;
    }

    // Función para alternar submenús
    function toggleSubmenu(menuId) {
        const submenus = ['gtic', 'oga', 'ogh', 'documentos', 'bienes', 'inv-oficina', 'inv-consumibles', 'carga-previa', 'carga-previa-oficina', 'carga-previa-bienes', 'ogh-documentos'];
        const parentMap = {
            'documentos': 'oga',
            'bienes': 'oga',
            'inv-oficina': 'oga',
            'carga-previa-oficina': 'inv-oficina',
            'inv-consumibles': 'oga',
            'carga-previa': 'inv-consumibles',
            'carga-previa-bienes': 'bienes',
            'ogh-documentos': 'ogh'
        };

        const currentSubmenu = document.getElementById(menuId + '-submenu');
        const currentArrow = document.getElementById(menuId + '-arrow');
        const isCurrentlyOpen = currentSubmenu && !currentSubmenu.classList.contains('hidden');
        const allParents = getAllParents(menuId, parentMap);

        // Cerrar todos los submenús excepto los padres
        submenus.forEach(id => {
            if (!allParents.includes(id)) {
                const submenu = document.getElementById(id + '-submenu');
                const arrow = document.getElementById(id + '-arrow');
                if (submenu) {
                    submenu.classList.add('hidden');
                    submenu.classList.remove('bg-blue-800');
                }
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });

        // Si el submenú actual no estaba abierto, abrirlo
        if (!isCurrentlyOpen) {
            // Abrir todos los padres primero
            allParents.forEach(parentId => {
                const parentSubmenu = document.getElementById(parentId + '-submenu');
                const parentArrow = document.getElementById(parentId + '-arrow');
                if (parentSubmenu) parentSubmenu.classList.remove('hidden');
                if (parentArrow) parentArrow.style.transform = 'rotate(180deg)';
            });

            if (currentSubmenu) {
                currentSubmenu.classList.remove('hidden');
                currentSubmenu.classList.add('bg-blue-700');
                if (currentArrow) currentArrow.style.transform = 'rotate(180deg)';
            }
        } else {
            // Si estaba abierto, quitar el color de fondo
            if (currentSubmenu) {
                currentSubmenu.classList.remove('bg-blue-700');
            }
        }
        // Si estaba abierto, queda cerrado (ya se cerró arriba, pero el padre se mantiene)
    }

    // Función para obtener el parámetro de la URL
    function getUrlParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Función para verificar y mostrar submenús activos
    function checkActiveSubmenus() {
        const parentMap = {
            'documentos': 'oga',
            'bienes': 'oga',
            'inv-oficina': 'oga',
            'inv-consumibles': 'oga',
            'ogh-documentos': 'ogh',
            'carga-previa': 'inv-consumibles',
            'carga-previa-oficina': 'inv-oficina',
            'carga-previa-bienes': 'bienes'
        };

        // Obtener el parámetro 'from' de la URL
        const fromParam = getUrlParam('from');

        // Determinar qué submenú de carga previa abrir según el parámetro 'from'
        let targetCargaPrevia = null;
        if (fromParam === 'consumibles') {
            targetCargaPrevia = 'carga-previa';
        } else if (fromParam === 'oficina') {
            targetCargaPrevia = 'carga-previa-oficina';
        } else if (fromParam === 'bienes') {
            targetCargaPrevia = 'carga-previa-bienes';
        }

        // Primero verificar submenús de nivel 2 (los más profundos)
        const level2Submenus = ['documentos', 'bienes', 'inv-oficina', 'inv-consumibles', 'carga-previa', 'carga-previa-oficina', 'carga-previa-bienes', 'ogh-documentos'];
        level2Submenus.forEach(menuId => {
            const submenu = document.getElementById(menuId + '-submenu');
            if (submenu) {
                const activeLinks = submenu.querySelectorAll('.sidebar-item.active');
                // Abrir si tiene elementos activos O si es el submenú de carga previa objetivo
                if (activeLinks.length > 0 || menuId === targetCargaPrevia) {
                    // Abrir el submenú de nivel 2
                    submenu.classList.remove('hidden');
                    submenu.classList.add('bg-blue-700');
                    const arrow = document.getElementById(menuId + '-arrow');
                    if (arrow) arrow.style.transform = 'rotate(180deg)';

                    // Abrir el padre (nivel 1)
                    const parent = parentMap[menuId];
                    if (parent) {
                        const parentSubmenu = document.getElementById(parent + '-submenu');
                        const parentArrow = document.getElementById(parent + '-arrow');
                        if (parentSubmenu) {
                            parentSubmenu.classList.remove('hidden');
                            parentSubmenu.classList.add('bg-blue-700');
                        }
                        if (parentArrow) parentArrow.style.transform = 'rotate(180deg)';
                    }
                }
            }
        });

        // Luego verificar submenús de nivel 1 que no tienen hijos con elementos activos
        const level1Submenus = ['gtic', 'oga', 'ogh'];
        level1Submenus.forEach(menuId => {
            const submenu = document.getElementById(menuId + '-submenu');
            if (submenu) {
                const activeLinks = submenu.querySelectorAll('.sidebar-item.active');
                // Solo abrir si tiene elementos activos Y no es padre de un submenú ya abierto
                if (activeLinks.length > 0) {
                    const hasOpenChild = Array.from(submenu.querySelectorAll('.submenu')).some(child => !child.classList.contains('hidden'));
                    if (!hasOpenChild) {
                        submenu.classList.remove('hidden');
                        submenu.classList.add('bg-blue-700');
                        const arrow = document.getElementById(menuId + '-arrow');
                        if (arrow) arrow.style.transform = 'rotate(180deg)';
                    }
                }
            }
        });

        // Si hay un parámetro 'from' pero no se encontró ningún enlace activo,
        // asegurar que el menú correspondiente esté abierto
        if (targetCargaPrevia && !document.querySelector('.sidebar-item.active')) {
            const targetSubmenu = document.getElementById(targetCargaPrevia + '-submenu');
            if (targetSubmenu) {
                targetSubmenu.classList.remove('hidden');
                targetSubmenu.classList.add('bg-blue-700');
                const targetArrow = document.getElementById(targetCargaPrevia + '-arrow');
                if (targetArrow) targetArrow.style.transform = 'rotate(180deg)';

                // Abrir el padre
                const parent = parentMap[targetCargaPrevia];
                if (parent) {
                    const parentSubmenu = document.getElementById(parent + '-submenu');
                    const parentArrow = document.getElementById(parent + '-arrow');
                    if (parentSubmenu) {
                        parentSubmenu.classList.remove('hidden');
                        parentSubmenu.classList.add('bg-blue-700');
                    }
                    if (parentArrow) parentArrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    }

    // Toggle sidebar collapse/expand
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleIcon = document.getElementById('sidebarToggleIcon');
        const toggleText = document.getElementById('sidebarToggleText');

        const isCollapsed = sidebar.classList.contains('collapsed');

        if (isCollapsed) {
            // Expandir sidebar
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            if (toggleIcon) toggleIcon.innerHTML = '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>';
            if (toggleText) toggleText.textContent = 'Ocultar Menú';
        } else {
            // Colapsar sidebar
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            if (toggleIcon) toggleIcon.innerHTML = '<path d="M3 12h18m-9-9l9 9-9 9"/>';
            if (toggleText) toggleText.textContent = 'Mostrar Menú';
        }
    }

    // Función para expandir todos los submenús en la página de inicio
    function expandAllSubmenusOnHome() {
        const allSubmenus = ['gtic', 'oga', 'ogh', 'documentos', 'bienes', 'inv-oficina', 'inv-consumibles', 'carga-previa', 'carga-previa-oficina', 'carga-previa-bienes', 'ogh-documentos'];
        allSubmenus.forEach(id => {
            const submenu = document.getElementById(id + '-submenu');
            if (submenu) {
                submenu.classList.remove('hidden');
                submenu.classList.add('bg-blue-700');
                const arrow = document.getElementById(id + '-arrow');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        });
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        checkActiveSubmenus();
        // Los submenús permanecen cerrados por defecto, solo se abren al hacer clic o si tienen elementos activos
    });
</script>
