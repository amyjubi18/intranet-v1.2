@can('documentos_index')
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

    /* Thumbnail styles */
    .pdf-thumbnail {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .pdf-thumbnail:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
    }

    /* Modal styles */
    #pdfModal .modal-content {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Filter indicator */
    .filter-active {
        background-color: #3B82F6 !important;
        border-color: #3B82F6 !important;
    }
</style>

<!-- jQuery y Date Range Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

<x-app-layout>
    @section('title', 'Documentos')
    @section('subtitle', 'Gestión de documentos')

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

        /* PDF Thumbnail */
        .pdf-thumbnail {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .pdf-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
        }

        /* Multiple files badge */
        .files-count {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        /* Animación de Estanterías - Estilo Zoom */
        .estanterias-container {
            display: flex;
            flex-wrap: wrap;
            padding: 16px;
            border-radius: 16px;
            gap: 16px;
            background: rgba(255, 255, 255, 0.5);
            align-items: center;
            justify-content: center;
            align-content: center;
            backdrop-filter: blur(10px);
        }

        .estanteria-block {
            width: 30%;
            /* height: 80px; */
            display: flex;
            justify-content: center;
           /*  place-items: center; */
            align-content: center;
            position: relative;
            cursor: pointer;
            outline: none;
            perspective: 1000px;
        }

        .estanteria-block__item {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 16px;
            background: var(--bg-estanteria, #1e3a8a);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275),
                        box-shadow 0.3s ease;
            transform-origin: center center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .estanteria-block:hover .estanteria-block__item,
        .estanteria-block:focus-visible .estanteria-block__item {
            transform: scale(1.15) translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(30, 58, 138, 0.4);
            z-index: 10;
        }

        .estanteria-block__image {
            width: 100%;
            height:100%;
            object-fit: contain;
            margin-bottom: 6px;
            transition: transform 0.3s ease;
        }

        .estanteria-block:hover .estanteria-block__image {
            transform: scale(1.1);
        }

        .estanteria-block__name {
            font-size: 20px;
            font-weight: 600;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            text-align: center;
            line-height: 1.2;
            padding: 0 4px;
            transition: transform 0.3s ease;
        }

        .estanteria-block:hover .estanteria-block__name {
            transform: scale(1.05);
        }

        .estanteria-block__count {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 4px;
            transition: transform 0.3s ease;
        }

        .estanteria-block:hover .estanteria-block__count {
            transform: scale(0.95);
        }

        .estanterias-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px;
            text-align: center;
        }

        .estanterias-empty__icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .estanterias-empty__text {
            color: #64748b;
        }

        .estanterias-empty__link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            margin-top: 16px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background: #1e3a8a;
            border-radius: 12px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .estanterias-empty__link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.4);
            background: #1e40af;
        }

        .btn-ver-todos {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background: #4b5563;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .btn-ver-todos:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(75, 85, 99, 0.4);
            background: #374151;
        }

        /* Ocultar/Mostrar documentos */
        #documentosWrapper {
            transition: opacity 0.3s ease;
        }
        #documentosWrapper.hidden {
            opacity: 0;
            pointer-events: none;
        }
        #documentosWrapper.visible {
            opacity: 1;
            pointer-events: auto;
        }

        .mensaje-seleccion {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        .mensaje-seleccion__icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .mensaje-seleccion__text {
            font-size: 16px;
        }

    /* Ocultar/Mostrar filtros */
        #filtrosWrapper {
            transition: opacity 0.3s ease;
        }
        #filtrosWrapper.hidden {
            opacity: 0;
            pointer-events: none;
            height: 0;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        #filtrosWrapper.visible {
            opacity: 1;
            pointer-events: auto;
        }

        /* PDF Zoom Controls */
        .pdf-zoom-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pdf-zoom-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.2s;
        }
        .pdf-zoom-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        .pdf-zoom-btn:active {
            transform: scale(0.95);
        }
        .pdf-zoom-level {
            color: white;
            font-size: 12px;
            min-width: 45px;
            text-align: center;
        }
        #pdfContainer {
            overflow: auto;
            max-height: calc(100vh - 200px);
            transition: transform 0.3s ease;
        }
     </style>

    <!-- Filtros y controles (siempre visibles) -->
    <form method="GET" action="{{ route('administracion.documentos.index') }}" id="filterForm">
        <div class="sticky top-0 z-10 px-6 mb-6">
            <div class="p-6 glass rounded-2xl fade-in">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Botón para mostrar filtros avanzados -->
                        <button type="button" onclick="toggleAdvancedFilters()" class="px-4 py-2 text-sm text-white bg-blue-900 border border-blue-600 hover:bg-blue-800 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            Filtros Avanzados
                        </button>

                        <!-- Filtro principal - Tipo de campo (oculto por defecto) -->
                        <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('filterType') ? 'bg-blue-100 border-blue-500' : '' }}" style="display:none;">
                            <option value="">Seleccionar filtro</option>
                            <option value="name" {{ request('filterType') == 'name' ? 'selected' : '' }}>Nº Expediente</option>
                            <option value="description" {{ request('filterType') == 'description' ? 'selected' : '' }}>Descripción</option>
                            <option value="fecha" {{ request('filterType') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                            <option value="categoria_id" {{ request('filterType') == 'categoria_id' ? 'selected' : '' }}>Categoría</option>
                        </select>

                        <!-- Filtro secundario - Dinámico según selección -->
                        <div id="secondFilterContainer" class="relative z-50">
                            <select id="secondFilter" name="secondFilter" class="px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl active:border-2 active:border-blue-900/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('secondFilter') ? 'bg-blue-100 border-blue-500' : '' }}" {{ request('filterType') ? '' : 'disabled' }}>
                                <option value="">-----</option>
                                @if(request('filterType') == 'categoria_id')
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" {{ request('secondFilter') == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Input de fecha (oculto por defecto) -->
                        <div id="dateRangeFilter" style="{{ request('filterType') == 'fecha' ? '' : 'display:none;' }}">
                            <input type="text" id="dateRangePicker" name="fecha_rango" class="px-4 py-2 text-sm text-black bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ request('fecha_desde') && request('fecha_hasta') ? request('fecha_desde') . ' - ' . request('fecha_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                            <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                            <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        </div>

                        <!-- Input de texto para búsqueda (oculto por defecto) -->
                        <input type="text" id="textFilter" name="search" placeholder="Buscar..." class="w-64 px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('search') && request('filterType') != 'fecha' && request('filterType') != 'categoria_id' ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('search') }}" style="{{ in_array(request('filterType'), ['name', 'description']) ? '' : 'display:none;' }}">

                        <!-- Búsqueda general (siempre visible) -->
                        <div class="relative">
                            <input type="text" id="generalSearch" name="search_general" placeholder="Búsqueda general..." class="w-64 px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('search_general') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('search_general') }}">
                        </div>

<!-- Script para debounce en búsqueda general -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
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
                            });
                        </script>

                        <!-- Botón para limpiar filtros -->
                        <a href="{{ route('administracion.documentos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            🗑️ Limpiar
                        </a>

                        <!-- Selector de ordenamiento -->
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-500">Ordenar:</span>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'name' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Expediente
                                @if($sort === 'name')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-gray-400">⇅</span>
                                @endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'description', 'direction' => $sort === 'description' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'description' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Descripcion
                                @if($sort === 'fecha')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-gray-400">⇅</span>
                                @endif
                            </a>
                            {{-- <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'created_at' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Creado
                                @if($sort === 'created_at')
                                    @if($direction === 'asc') ⬆️ @else ⬇️ @endif
                                @else
                                    <span class="text-gray-400">⇅</span>
                                @endif
                            </a> --}}
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @can('documentos_create')
                        <a href="{{ route('administracion.documentos.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            <span>➕</span>
                            Nuevo
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Indicador de filtros activos -->
            @if(request('filterType') || request('secondFilter') || request('search') || request('search_general') || request('fecha_desde') || request('fecha_hasta'))
            <div class="p-4 mt-4 border border-blue-200 bg-blue-50 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-semibold text-blue-600">🔍 Filtros Activos:</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(request('filterType') == 'name' && request('search'))
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">
                            Expediente: "{{ request('search') }}"
                            <button onclick="clearSpecificFilter('name')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('filterType') == 'description' && request('search'))
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">
                            Descripción: "{{ request('search') }}"
                            <button onclick="clearSpecificFilter('description')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('filterType') == 'categoria_id' && request('secondFilter'))
                        @php
                            $categoriaActiva = $categorias->where('id', request('secondFilter'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">
                            Categoría: {{ $categoriaActiva->name ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('categoria_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('search_general'))
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">
                            Búsqueda: "{{ request('search_general') }}"
                            <button onclick="clearSpecificFilter('search_general')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('fecha_desde') || request('fecha_hasta'))
                        @php
                            $fechaDesde = request('fecha_desde');
                            $fechaHasta = request('fecha_hasta');
                            $rangoTexto = '';

                            if($fechaDesde && $fechaHasta) {
                                $rangoTexto = "Desde {$fechaDesde} hasta {$fechaHasta}";
                            } elseif($fechaDesde) {
                                $rangoTexto = "Desde {$fechaDesde}";
                            } elseif($fechaHasta) {
                                $rangoTexto = "Hasta {$fechaHasta}";
                            }
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">
                            {{ $rangoTexto }}
                            <button onclick="clearDateFilter()" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </form>

    <!-- Sección de Estanterías -->
    <div class="px-6 mb-8 ">
        <div class="h-auto p-6 glass rounded-2xl fade-in">
            <h3 class="mb-4 text-lg font-semibold text-blue-900">📚 Selecciona una Estantería</h3>

            @if($estanterias->count() > 0)
            <div class="estanterias-container">
                @foreach($estanterias as $estanteria)
                <div class="estanteria-block  {{ request('estanteria_id') == $estanteria->id ? 'selected' : '' }} flex"
                     onclick="seleccionarEstateria({{ $estanteria->id }})"
                     tabindex="0"
                     role="button"
                     aria-label="Seleccionar estantería {{ $estanteria->nombre }}"
                     style="--bg-estanteria: {{ $loop->iteration % 2 == 0 ? '#1e40af' : '#1e3a8a' }}">
                    <div class="estanteria-block__item">
                        @if($estanteria->imagen && file_exists(storage_path('app/public/estanterias/' . $estanteria->imagen)))
                            <img src="{{ asset('storage/estanterias/' . $estanteria->imagen) }}"
                                 alt="{{ $estanteria->nombre }}"
                                 class="estanteria-block__image">
                        @else
                            <div style="font-size: 30px;">🗄️</div>
                        @endif
                        <span class="estanteria-block__name">{{ $estanteria->nombre }}</span>
                        <span class="estanteria-block__count">{{ $estanteria->documentos_count ?? $estanteria->documentos()->count() }} docs</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="estanterias-empty">
                <div class="estanterias-empty__icon">🗄️</div>
                <p class="estanterias-empty__text">No hay estanterías registradas.</p>
                <a href="{{ route('administracion.estanterias.create') }}" class="estanterias-empty__link">
                    <span>➕</span>
                    Crear Estantería
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Botón para ver todos los documentos -->
    <div class="px-6 mb-4" id="btnVerTodos" style="{{ request('estanteria_id') ? '' : 'display:none;' }}">
        <button onclick="verTodosLosDocumentos()" class="btn-ver-todos">
            ← Ver Todos los Documentos
        </button>
    </div>

    <!-- Mensaje de selección de estantería (visible solo cuando no hay selección, no hay filtros y no hay documentos) -->
    @php
        $hayFiltros = request('filterType') || request('secondFilter') || request('search') || request('search_general') || request('fecha_desde') || request('fecha_hasta');
    @endphp
    @if(!request('estanteria_id') && !$hayFiltros && $documentos->count() == 0)
    <div class="px-6 mb-8" id="mensajeSeleccion">
        <div class="glass rounded-2xl fade-in">
            <div class="mensaje-seleccion">
                <div class="mensaje-seleccion__icon">📚</div>
                <p class="mensaje-seleccion__text">Selecciona una estantería o registra un documento para comenzar</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Registro de documentos -->
    <div class="px-6 mb-8" id="documentosWrapper">
        <div class="space-y-4 fade-in">
            @forelse ($documentos as $documento)
            <!-- Registro -->
            <div class="glass rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Info del documento -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">📄 Documento</p>
                        <p class="text-sm font-semibold">{{ $documento->name }}</p>
                        <p class="mb-1 text-xs font-semibold text-blue-900">📝 Descripción</p>
                        <p class="text-lg font-semibold truncate">{{ $documento->description }}</p>
                    </div>

                    <!-- Categoría y Fecha -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">📅 Fecha</p>
                        <p class="mt-1 text-xs text-gray-400"> {{ \Carbon\Carbon::parse($documento->fecha)->format('d/m/Y') }}</p>
                    </div>

                    <!-- Archivos PDF -->
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">📎 Archivos PDF ({{ $documento->archivos->count() }})</p>
                        @if($documento->archivos->count() > 0)
                            <div class="flex flex-wrap justify-center gap-2 lg:justify-start">
                                @foreach($documento->archivos->take(3) as $archivo)
                                    <div class="relative flex items-center justify-center w-10 bg-red-100 border-2 border-red-200 rounded-lg cursor-pointer pdf-thumbnail h-11 hover:border-red-400 group"
                                         onclick="openPdfModal('{{ asset('archivos/' . $archivo->ruta) }}', '{{ $archivo->nombre_archivo }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                                        </svg>
                                        @if($documento->archivos->count() > 3 && $loop->last)
                                            <span class="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-white rounded-full -top-2 -right-2 files-count">
                                                +{{ $documento->archivos->count() - 3 }}
                                            </span>
                                        @endif
                                        <!-- Tooltip -->
                                        <div class="absolute z-10 px-2 py-1 mb-2 text-xs text-white transition-opacity transform -translate-x-1/2 bg-gray-900 rounded opacity-0 bottom-full left-1/2 group-hover:opacity-100 whitespace-nowrap">
                                            {{ $archivo->nombre_archivo }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($documento->archivos->count() > 0)
                                <p class="mt-2 text-xs text-gray-500">
                                    @foreach($documento->archivos as $archivo)
                                        <span class="inline-block px-1 py-0.5 bg-gray-100 rounded text-xs mb-1 mr-1">
                                            {{ $archivo->categoria->name ?? 'Sin cat' }}
                                        </span>
                                    @endforeach
                                </p>
                            @endif
                        @else
                            <div class="flex items-center justify-center w-10 bg-gray-100 border-2 border-gray-200 rounded-lg h-11">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Acciones -->
                    <div class="text-center lg:text-right">
                        <div class="flex flex-wrap justify-center gap-2 lg:justify-end">
                            @can('documentos_edit')
                            <a href="{{ route('administracion.documentos.edit', $documento->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('documentos_destroy')
                            <button onclick="confirmDelete({{ $documento->id }}, '{{ $documento->name }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
                <div class="flex flex-col items-center justify-center py-8">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-full">
                        <span class="text-3xl">📂</span>
                    </div>
                    <h3 class="mb-1 text-lg font-medium text-gray-900">No hay documentos</h3>
                    <p class="mb-4 text-sm text-gray-500">No se han encontrado documentos registrados</p>
                    @can('documentos_create')
                    <a href="{{ route('administracion.documentos.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Subir Documento
                    </a>
                    @endcan
                </div>
            </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($documentos->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">
                {{ $documentos->links() }}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal para visualizar PDF -->
    <div id="pdfModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-6xl overflow-hidden bg-white shadow-2xl rounded-2xl" style="max-height: 90vh;">
                <!-- Header del modal -->
                <div class="flex items-center justify-between px-4 py-3 bg-blue-900">
                    <h3 class="text-lg font-semibold text-white" id="pdfModalTitle">Vista Previa del Documento</h3>
                    <div class="flex items-center gap-3">
                        <!-- Controles de zoom -->
                        <div class="pdf-zoom-controls">
                            <button type="button" onclick="zoomOut()" class="pdf-zoom-btn" title="Disminuir">−</button>
                            <span class="pdf-zoom-level" id="zoomLevel">100%</span>
                            <button type="button" onclick="zoomIn()" class="pdf-zoom-btn" title="Ampliar">+</button>
                            <button type="button" onclick="resetZoom()" class="pdf-zoom-btn" title="Restablecer" style="font-size: 14px;">⟳</button>
                        </div>
                        <button type="button" onclick="closePdfModal()" class="text-white transition-colors hover:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Contenido del PDF con zoom -->
                <div id="pdfContainer" class="p-4 bg-gray-100">
                    <iframe id="pdfFrame" class="w-full border border-gray-300 rounded-lg" style="height: 75vh; transition: transform 0.3s ease;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script>
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

        // Funciones para filtros
        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const textFilter = document.getElementById('textFilter');

            // Hide all
            secondFilterContainer.style.display = 'none';
            dateRangeFilter.style.display = 'none';
            textFilter.style.display = 'none';

            // Show based on selection
            if (filterType === 'fecha') {
                dateRangeFilter.style.display = 'block';
            } else if (filterType === 'name' || filterType === 'description') {
                textFilter.style.display = 'block';
            } else if (filterType === 'categoria_id') {
                secondFilterContainer.style.display = 'block';
            }

            // Hide filterType select after selection
            if (filterType !== '') {
                document.getElementById('filterType').style.display = 'none';
            }
        }

        function clearSpecificFilter(filterType) {
            const url = new URL(window.location);

            if (filterType === 'name' || filterType === 'description') {
                url.searchParams.delete('filterType');
                url.searchParams.delete('search');
            } else if (filterType === 'categoria_id') {
                url.searchParams.delete('filterType');
                url.searchParams.delete('secondFilter');
            } else if (filterType === 'search_general') {
                url.searchParams.delete('search_general');
            }

            window.location.href = url.toString();
        }

        function clearDateFilter() {
            const url = new URL(window.location);
            url.searchParams.delete('filterType');
            url.searchParams.delete('fecha_desde');
            url.searchParams.delete('fecha_hasta');
            window.location.href = url.toString();
        }

        // Toggle filtros avanzados
        function toggleAdvancedFilters() {
            const filterSelect = document.getElementById('filterType');
            if (filterSelect.style.display === 'none' || filterSelect.style.display === '') {
                filterSelect.style.display = 'inline-block';
            } else {
                filterSelect.style.display = 'none';
                filterSelect.value = '';
                updateSecondFilter();
                document.getElementById('secondFilter').value = '';
                document.getElementById('dateRangePicker').value = '';
                document.getElementById('textFilter').value = '';
                document.getElementById('fecha_desde').value = '';
                document.getElementById('fecha_hasta').value = '';
                document.getElementById('filterForm').submit();
            }
        }

        // Seleccionar estantería
        function seleccionarEstateria(estanteriaId) {
            // Mostrar documentos
            document.getElementById('documentosWrapper').style.display = 'block';
            document.getElementById('documentosWrapper').classList.remove('hidden');
            document.getElementById('documentosWrapper').classList.add('visible');

            // Ocultar mensaje de selección
            document.getElementById('mensajeSeleccion').style.display = 'none';

            // Mostrar botón de ver todos
            document.getElementById('btnVerTodos').style.display = 'block';

            // Actualizar URL
            const url = new URL(window.location);
            url.searchParams.delete('page');
            url.searchParams.set('estanteria_id', estanteriaId);
            window.location.href = url.toString();
        }

        // Ver todos los documentos
        function verTodosLosDocumentos() {
            // Actualizar URL
            const url = new URL(window.location);
            url.searchParams.delete('page');
            url.searchParams.delete('estanteria_id');
            window.location.href = url.toString();
        }

        // Modal PDF functions
        let pdfZoom = 100;

        function openPdfModal(pdfUrl, documentName) {
            const modal = document.getElementById('pdfModal');
            const frame = document.getElementById('pdfFrame');
            const title = document.getElementById('pdfModalTitle');

            // Reset zoom
            pdfZoom = 100;
            updateZoomDisplay();
            frame.style.transform = 'scale(1)';

            modal.classList.remove('hidden');
            frame.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=1';
            title.textContent = documentName;

            document.body.style.overflow = 'hidden';
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal');
            const frame = document.getElementById('pdfFrame');

            modal.classList.add('hidden');
            frame.src = '';

            document.body.style.overflow = 'auto';
        }

        function zoomIn() {
            if (pdfZoom < 200) {
                pdfZoom += 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function zoomOut() {
            if (pdfZoom > 50) {
                pdfZoom -= 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function resetZoom() {
            pdfZoom = 100;
            updateZoomDisplay();
            document.getElementById('pdfFrame').style.transform = 'scale(1)';
        }

        function updateZoomDisplay() {
            document.getElementById('zoomLevel').textContent = pdfZoom + '%';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('pdfModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePdfModal();
            }
        });

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePdfModal();
            }
        });

        // Enviar formulario automáticamente al cambiar filtros
        document.querySelectorAll('#filterForm select[name], #filterForm input[name]').forEach(element => {
            element.addEventListener('change', function() {
                if (this.id !== 'dateRangePicker') {
                    document.getElementById('filterForm').submit();
                }
            });
        });

        // Date Range Picker initialization
        $(document).ready(function() {
            $('#dateRangePicker').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Limpiar',
                    applyLabel: 'Aplicar',
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    weekLabel: 'S',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                $('#filterForm').submit();
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                $('#filterForm').submit();
            });
        });

        // SweetAlert2 for delete confirmation
        function confirmDelete(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Quieres eliminar el documento "${name}" y todos sus archivos? Esta acción no se puede deshacer.`,
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
                    form.action = `/administracion/documentos/${id}`;

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

        // Inicialización
        function init() {
            showToast('Módulo de documentos cargado', 'info');
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan

