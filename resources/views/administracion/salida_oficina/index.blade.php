@can('salidaofic_index')
<title>{{ 'Salida de Oficina' }}</title>
@include('flatpickr::components.script')
@include('flatpickr::components.style')
@vite('resources/css/app.css')
{{-- <livewire:styles />
 --}}<style>
    .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
    .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
    .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }
    .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
    .animate-fadeIn { animation: animateFadeIn 0.6s ease-out forwards; }
    @keyframes animateFadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .record-card { transition: all 0.3s ease; }
    .record-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
    
    /* Filtros con buscador */
    .searchable-select { position: relative; z-index: 99999; }
    .searchable-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff !important;
        border: 1px solid rgba(15,23,42,0.15);
        backdrop-filter: blur(12px);
        border-radius: 0.75rem;
        margin-top: 0.25rem;
        max-height: 300px;
        overflow-y: auto;
        z-index: 999999 !important;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }
    .searchable-select-dropdown.open { opacity: 1; visibility: visible; transform: translateY(0); }
    .searchable-select-search { padding: 0.75rem; border-bottom: 1px solid rgba(15,23,42,0.08); }
    .searchable-select-search input { width: 100%; background: transparent; border: none; outline: none; font-size: 0.875rem; color: #0f172a; }
    .searchable-select-options { max-height: 200px; overflow-y: auto; }
    .searchable-select-option { padding: 0.75rem; cursor: pointer; transition: 0.2s; color: #64748b; }
    .searchable-select-option:hover { background: rgba(30,58,138,0.08); color: #1e3a8a; }
    .searchable-select-option.selected { background: #1e3a8a; color: white !important; }
    .searchable-select-option.hidden { display: none; }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-4xl font-semibold leading-tight text-center text-blue-900">{{ __('Salida de Oficina') }}</h2>
    </x-slot>

    <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>


    <!-- Tarjetas de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20"><span class="text-blue-400" style="font-size: 24px;">📅</span></div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $ultimasFechasSalida ?? '0' }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Últimas Fechas de Salida</p>
            </div>

           

            <!-- Total Salidas -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                        <span class="text-green-400" style="font-size: 24px;">🗃️</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalSalidas }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Total Salidas</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-400">{{ $salidasEsteMes }}</span>
                    <span class="text-blue-900 font-semibold">este mes</span>
                </div>
            </div>

            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20"><span class="text-red-400" style="font-size: 24px;">⚠️</span></div>
                    <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Alerta ({{ $countSalidasBajoStock ?? '0' }})</span>
                </div>
                <h3 class="text-sm text-blue-900 font-semibold">Articulos con Bajo Stock</h3>
                <div class="text-sm">
                    @if(isset($salidasBajoStock) && $salidasBajoStock->count() > 0)
                        @foreach($salidasBajoStock->take(3) as $item)
                        <div class="flex items-center gap-2 mb-1 bg-red-500/20 rounded-md p-1">
                            <div class="w-12 h-12 rounded flex items-center justify-center flex-shrink-0">
                                @if($item->articulos && $item->articulos->imagen)
                                    <img src="{{ asset('storage/' . $item->articulos->imagen) }}" alt="Item" class="w-12 h-12 object-cover rounded">
                                @else
                                    <span class="text-blue-400" style="font-size: 16px;">📦</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-red-600 text-sm font-bold">{{ $item->articulos->articulo ?? 'N/A' }}</p>
                                <p class="text-red-600 text-sm">({{ $item->cantidad }} unidades)</p>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-green-600">No hay items bajo stock</p>
                    @endif
                </div>
        </div>
    </div>
    </div>
    @php
        $activeFiltersCount = 0;
        if(request('busqueda')) $activeFiltersCount++;
        if(request('gerencia')) $activeFiltersCount++;
        if(request('area')) $activeFiltersCount++;
        if(request('articulo')) $activeFiltersCount++;
        if(request('fecha_desde') || request('fecha_hasta')) $activeFiltersCount++;
        if(request('fecha_ingreso_desde') || request('fecha_ingreso_hasta')) $activeFiltersCount++;
    @endphp

    <!-- Panel de Búsqueda y Filtros Avanzados Animados -->
    <div class="px-6 mb-6 relative z-30 overflow-visible" x-data="{ openFilters: {{ $activeFiltersCount > 0 ? 'true' : 'false' }} }">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
            <div class="flex flex-col gap-4">
                <!-- Fila Superior: Buscador Rápido + Acciones (Exportar, Eliminar, Nuevo) -->
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <!-- Buscador general -->
                    <form method="GET" action="{{ route('administracion.salida_oficina.index') }}" class="flex-1 max-w-xl">
                        @if(request('gerencia')) <input type="hidden" name="gerencia" value="{{ request('gerencia') }}"> @endif
                        @if(request('area')) <input type="hidden" name="area" value="{{ request('area') }}"> @endif
                        @if(request('articulo')) <input type="hidden" name="articulo" value="{{ request('articulo') }}"> @endif
                        @if(request('fecha_desde')) <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}"> @endif
                        @if(request('fecha_hasta')) <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}"> @endif
                        @if(request('fecha_ingreso_desde')) <input type="hidden" name="fecha_ingreso_desde" value="{{ request('fecha_ingreso_desde') }}"> @endif
                        @if(request('fecha_ingreso_hasta')) <input type="hidden" name="fecha_ingreso_hasta" value="{{ request('fecha_ingreso_hasta') }}"> @endif

                        <div class="relative">
                            <input
                                type="text"
                                name="busqueda"
                                value="{{ request('busqueda') }}"
                                placeholder="🔍 Buscar por artículo, gerencia, área o responsable (Enter)..."
                                class="w-full bg-blue-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                            />
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
                        </div>
                    </form>

                    <!-- Acciones -->
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <select id="exportSelectSalida" onchange="handleSalidaExport(this)" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs">
                            <option value="">📤 Exportar</option>
                            <optgroup label="Exportar Todos">
                                <option value="excel:all">📊 Excel - Todos los registros</option>
                                <option value="pdf:all">📄 PDF - Todos los registros</option>
                            </optgroup>
                            <optgroup label="Exportar Seleccionados" id="salidaExportSelectedGroup">
                                <option value="excel:selected" id="salidaOptExcelSelected" disabled>📊 Excel - Solo seleccionados (0)</option>
                                <option value="pdf:selected" id="salidaOptPdfSelected" disabled>📄 PDF - Solo seleccionados (0)</option>
                            </optgroup>
                        </select>

                        @can('salidaofic_destroy')
                        <button type="button" onclick="confirmDeleteSelected()" id="deleteSelectedBtn" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-600 text-white hover:bg-red-700 active:translate-y-0.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-xs" disabled>
                            <span>🗑️</span>
                            <span id="deleteSelectedBtnText">Eliminar Seleccionados</span>
                        </button>
                        <button type="button" onclick="confirmDeleteAll()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-800 text-white hover:bg-red-900 active:translate-y-0.5 text-sm shadow-xs">
                            <span>⚠️</span>
                            <span>Eliminar Todo</span>
                        </button>
                        @endcan

                        @can('salidaofic_create')
                        <a href="{{ route('administracion.salida_oficina.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-xs">
                            <span>➕</span>
                            <span>Nuevo</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- Fila de Control: Botón Filtros Avanzados + Badges de Filtros Activos -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="openFilters = !openFilters"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-xs transition-all duration-300 cursor-pointer"
                        >
                            <span>⚡ Filtros Avanzados</span>
                            @if($activeFiltersCount > 0)
                                <span class="w-5 h-5 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                            <svg
                                class="w-3.5 h-3.5 transition-transform duration-300 transform"
                                :class="openFilters ? 'rotate-180' : 'rotate-0'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        @if($activeFiltersCount > 0)
                            <a
                                href="{{ route('administracion.salida_oficina.index') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors"
                            >
                                <span>✕</span>
                                <span>Limpiar Filtros ({{ $activeFiltersCount }})</span>
                            </a>
                        @endif
                    </div>

                    <!-- Badges de filtros activos -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if(request('gerencia'))
                            @php $gAct = $gerencias->where('id', request('gerencia'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Gerencia: {{ $gAct->gerencia ?? request('gerencia') }}</span>
                                <button type="button" onclick="clearSpecificFilter('gerencia')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('area'))
                            @php $aAct = $areas->where('id', request('area'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Área: {{ $aAct->area ?? request('area') }}</span>
                                <button type="button" onclick="clearSpecificFilter('area')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('articulo'))
                            @php $artAct = $articulos->where('id', request('articulo'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Artículo: {{ $artAct->articulo ?? request('articulo') }}</span>
                                <button type="button" onclick="clearSpecificFilter('articulo')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('busqueda'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Búsqueda: "{{ request('busqueda') }}"</span>
                                <button type="button" onclick="clearSpecificFilter('busqueda')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('fecha_desde') || request('fecha_hasta'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Fecha Salida: {{ request('fecha_desde') ?: '...' }} a {{ request('fecha_hasta') ?: '...' }}</span>
                                <button type="button" onclick="clearDateFilter('fecha_salida')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('fecha_ingreso_desde') || request('fecha_ingreso_hasta'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Fecha Factura: {{ request('fecha_ingreso_desde') ?: '...' }} a {{ request('fecha_ingreso_hasta') ?: '...' }}</span>
                                <button type="button" onclick="clearDateFilter('fecha_ingreso')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Acordeón Animado con CSS Grid auto-height -->
                <div
                    class="grid transition-all duration-500 ease-in-out"
                    :class="openFilters ? 'grid-rows-[1fr] opacity-100 mt-3 visible' : 'grid-rows-[0fr] opacity-0 invisible mt-0 pointer-events-none'"
                >
                    <div :class="openFilters ? 'overflow-visible' : 'overflow-hidden'" class="transition-all duration-300">
                        <form method="GET" action="{{ route('administracion.salida_oficina.index') }}" id="advancedFilterForm">
                            @if(request('busqueda')) <input type="hidden" name="busqueda" value="{{ request('busqueda') }}"> @endif
                            <div class="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md relative z-40 overflow-visible space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Gerencia -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🏢 Gerencia</label>
                                        <select name="gerencia" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las gerencias</option>
                                            @foreach($gerencias->sortBy('gerencia') as $g)
                                                <option value="{{ $g->id }}" {{ request('gerencia') == $g->id ? 'selected' : '' }}>
                                                    {{ $g->gerencia }} {{ $g->area ? '('.$g->area->area.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Área -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">📂 Área</label>
                                        <select name="area" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las áreas</option>
                                            @foreach($areas->sortBy('area') as $a)
                                                <option value="{{ $a->id }}" {{ request('area') == $a->id ? 'selected' : '' }}>
                                                    {{ $a->area }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Artículo -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">📦 Artículo</label>
                                        <select name="articulo" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los artículos</option>
                                            @foreach($articulos->sortBy('articulo') as $art)
                                                <option value="{{ $art->id }}" {{ request('articulo') == $art->id ? 'selected' : '' }}>
                                                    {{ str_pad($art->id, 4, '0', STR_PAD_LEFT) }} - {{ $art->articulo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Rango de fechas con presets -->
                                <div class="pt-3 border-t border-blue-100/70">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                                        <label class="block text-xs font-semibold text-blue-900">📅 Rango de Fechas (Salida)</label>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" onclick="setSalidaDatePreset('today')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Hoy</button>
                                            <button type="button" onclick="setSalidaDatePreset('week')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Esta Semana</button>
                                            <button type="button" onclick="setSalidaDatePreset('month')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Mes</button>
                                            <button type="button" onclick="setSalidaDatePreset('year')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Año</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Desde</span>
                                            <input type="date" name="fecha_desde" id="salida_fecha_desde" value="{{ request('fecha_desde') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Hasta</span>
                                            <input type="date" name="fecha_hasta" id="salida_fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Factura Desde</span>
                                            <input type="date" name="fecha_ingreso_desde" value="{{ request('fecha_ingreso_desde') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Factura Hasta</span>
                                            <input type="date" name="fecha_ingreso_hasta" value="{{ request('fecha_ingreso_hasta') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <a href="{{ route('administracion.salida_oficina.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                                        Limpiar Todo
                                    </a>
                                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold bg-blue-900 text-white hover:bg-blue-800 shadow-sm transition-all cursor-pointer">
                                        <span>🔍</span>
                                        <span>Aplicar Filtros</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formularios ocultos para eliminación masiva -->
    <form id="deleteMultipleForm" method="POST" action="{{ route('administracion.salida_oficina.destroyMultiple') }}" style="display:none;">
        @csrf
        <input type="hidden" name="ids" id="multipleDeleteIds">
        <input type="hidden" name="password" id="multipleDeletePassword">
    </form>
    <form id="deleteAllForm" method="POST" action="{{ route('administracion.salida_oficina.destroyAll') }}" style="display:none;">
        @csrf
        <input type="hidden" name="password" id="deleteAllPassword">
    </form>

    <!-- Registros de Salida Agrupados por Artículo -->
    <div class="px-6 mb-8 relative z-10">
        @if(isset($salidasAgrupadas) && $salidasAgrupadas->count() > 0)
            @foreach($salidasAgrupadas as $articuloId => $grupoItems)
                @php
                    $primerItem = $grupoItems->first();
                    $articulo = $primerItem->articulos;
                    $stockTotal = $stockPorArticulo[$articuloId] ?? 0;
                    $stockMinimo = $articulo->stock_min ?? 0;
                    $tieneStock = $stockTotal > $stockMinimo;
                    $colorClase = $tieneStock ? 'from-blue-900 to-blue-800' : 'from-red-900 to-red-800';
                    $badgeColor = $tieneStock ? 'bg-blue-500' : 'bg-red-500';
                    $badgeText = $tieneStock ? 'Stock Saludable' : 'Stock Bajo';
                    $badgeIcon = $tieneStock ? '✅' : '⚠️';
                @endphp

                <div class="bg-gradient-to-r {{ $colorClase }} text-white rounded-2xl p-4 shadow-lg relative mb-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                @if($articulo && $articulo->imagen)
                                    <img src="{{ asset('storage/' . $articulo->imagen) }}" alt="Item" class="w-16 h-16 object-cover rounded-xl">
                                @else
                                    <span style="font-size: 32px;">📦</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold">{{ $articulo ? $articulo->articulo : 'Artículo Desconocido' }}</h3>
                                <div class="flex flex-wrap gap-4 mt-1 text-sm">
                                    <div>
                                        <span class="text-blue-200">ITEM:</span><span class="font-semibold ml-1">{{ $articulo ? str_pad($articulo->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-200">Total Salidas:</span><span class="font-semibold ml-1">{{ $grupoItems->count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                        <div class="flex flex-col items-end gap-2">
                            <div class="{{ $badgeColor }} text-white text-xs px-3 py-1 rounded-full font-bold flex items-center gap-1"><span>{{ $badgeIcon }}</span>{{ $badgeText }}
                        </div>
                            <div class="text-right">
                                <span class="text-blue-200 text-sm">Stock disponible:</span>
                                <span class="font-bold text-xl ml-1 {{ $tieneStock ? 'text-blue-200' : 'text-red-300' }}">{{ $stockTotal }}</span>
                                <span class="text-blue-200 text-sm">/ {{ $stockMinimo }} min</span>
                            </div>
                        
                    </div>
                </div>
            </div>        
                
                <div class="ml-4 md:ml-8 space-y-4 mb-6">
                    @foreach($grupoItems as $item)
                        @php
                            $stockTotalReal = $item->historico_unidades_disponibles ?? 0;
                            $stockRestante = max(0, $stockTotalReal - $item->cantidad);
                            $stockMinimo = $item->articulos->stock_min ?? 0;
                            $porcentajeStock = $stockTotalReal > 0 ? min(100, ($stockRestante / $stockTotalReal) * 100) : 0;
                            $colorStock = $stockRestante < $stockMinimo ? 'text-red-400' : 'text-green-400';
                            $colorBarra = $stockRestante < $stockMinimo ? 'bg-red-500' : 'bg-green-500';
                            $tieneStockBajo = $stockRestante < $stockMinimo;
                        @endphp

                        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 record-card hover:scale-105 transition-transform duration-200">
                            <div class="flex flex-col lg:flex-row items-start gap-6">
                                <div class="flex-shrink-0 pt-2">
                                    <input type="checkbox" class="record-checkbox w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" value="{{ $item->id }}" onchange="updateSalidaSelection()">
                                </div>

                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Fecha de Salida</p>
                                        <p class="font-semibold text-black">{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</p>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                                        <p class="font-mono text-sm">{{ $item->articulos ? str_pad($item->articulos->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Artículo</p>
                                        <p class="font-semibold text-black">{{ $item->articulos ? $item->articulos->articulo : 'N/A' }}</p>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Unidad</p>
                                        <p class="text-sm">{{ $item->unidad_medidas ? $item->unidad_medidas->unidad_medida : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Gerencia</p>
                                        <p class="text-sm font-semibold">{{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}</p>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Área</p>
                                        <p class="text-sm font-semibold">{{ $item->areas ? $item->areas->area : 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-base text-blue-900 font-semibold mt-2">Solicitante</p>
                                        <p class="text-sm font-semibold">{{ $item->users ? $item->users->username : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                                    <div class="text-center">
                                        <p class="text-sm text-black font-bold mb-1">Unidades Disponibles</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl text-center font-bold {{ $colorStock }}">{{ $stockRestante }}</span>
                                            <span class="text-xs text-blue-900 font-semibold">de {{ $stockTotalReal }}</span>
                                        </div>
                                        <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                            <div class="{{ $colorBarra }} h-2 rounded-full" style="width: {{ $porcentajeStock }}%"></div>
                                                @if($tieneStockBajo)
                                                <span class="text-xs text-red-500 mt-1 inline-block">⚠️ Stock bajo (mín: {{ $stockMinimo }})</span>
                                                @endif
                                        </div>
                                    </div>

                                    <div class="text-right mt-4">
                                        <p class="text-sm text-black font-bold mb-1">Cantidad Retirada <span style="font-size: 20px;">📦</span></p>
                                        <p class="text-2xl text-center font-bold text-orange-600">{{ $item->cantidad }}</p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-blue-900 font-semibold mb-1">Ajuste Inventario</p>
                                        <p class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $item->ajuste_inventario }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if($salida_oficinas->hasPages())
            <div class="mt-8">
                <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">{!! $salida_oficinas->links() !!}</div>
            </div>
            @endif

            @if(isset($inventarioFiltrado) && $inventarioFiltrado->count() > 0)
            <div class="mt-8">
                <div class="bg-gradient-to-r from-green-900 to-green-800 text-white rounded-2xl p-4 shadow-lg relative mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0"><span style="font-size: 32px;">📦</span></div>
                            <div>
                                <h3 class="text-xl font-bold">Inventario del Artículo</h3>
                                <div class="flex flex-wrap gap-4 mt-1 text-sm">
                                    <div><span class="text-green-200">Total registros:</span><span class="font-semibold ml-1">{{ $inventarioFiltrado->count() }}</span></div>
                                    <div><span class="text-green-200">Unidades disponibles:</span><span class="font-semibold ml-1">{{ $inventarioFiltrado->sum('unidades_disponibles') }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @else
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-12 text-center">
            <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center"><span style="font-size: 48px;">📭</span></div>
            <h3 class="text-xl font-bold text-blue-900 mb-2">No hay registros de salida</h3>
            <p class="text-gray-500 mb-6">Comienza agregando una nueva salida de oficina</p>
            @can('salidaofic_create')
            <a href="{{ route('administracion.salida_oficina.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-900 text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors"><span>➕</span>Nueva Salida</a>
            @endcan
        </div>
        @endif
    </div>
   

    <script>
    @if(session('datos'))
        Swal.fire({ icon: 'success', title: '¡Éxito!', text: '{{ session('datos') }}', confirmButtonColor: '#1e3a8a', confirmButtonText: 'Aceptar' });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: '¡Error!', text: '{{ session('error') }}', confirmButtonColor: '#dc2626', confirmButtonText: 'Aceptar' });
    @endif
    </script>

    <script>

    // Sistema de filtros dinámicos para salida de oficinaamiento de impresoras
        const filterData = {
    'gerencia': {
        type: 'select',
        options: [
            { value: '', text: 'Todas las gerencias' },
            @foreach($gerencias->sortBy('gerencia') as $gerencia)
            { value: '{{ $gerencia->id }}', text: '{{ $gerencia->gerencia }} ({{ $gerencia->area->area ?? "N/A" }})' },
            @endforeach
        ]
    },
    'area': {
        type: 'select',
        options: [
            { value: '', text: 'Todas las áreas' },
            @foreach($areas as $area)
            { value: '{{ $area->id }}', text: '{{ $area->area }}' },
            @endforeach
        ]
    },
    'articulo': {
        type: 'select',
        options: [
            { value: '', text: 'Todos los consumibles' },
            @foreach($articulos as $articulo)
            { value: '{{ $articulo->id }}', text: '{{ str_pad($articulo->id, 4, "0", STR_PAD_LEFT) }} - {{ $articulo->articulo }}' },
            @endforeach
        ]
    },
    'busqueda': {
        type: 'text'
    },
    'fecha': {
        type: 'date'
    },
    'fecha_ingreso': {
        type: 'date_ingreso'
    }
};




    function updateSecondFilter() {
    const filterType = document.getElementById('filterType').value;
    const secondFilter = document.getElementById('secondFilter');
    const searchableFilter = document.getElementById('searchableFilter');
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const dateIngresoRangeFilter = document.getElementById('dateIngresoRangeFilter');
    const textFilter = document.getElementById('textFilter');

    // Ocultar todos los filtros secundarios
    secondFilter.classList.add('hidden');
    searchableFilter.classList.add('hidden');
    dateRangeFilter.classList.add('hidden');
    dateIngresoRangeFilter.classList.add('hidden');
    textFilter.classList.add('hidden');

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

    } else if (filterConfig.type === 'date') {
        dateRangeFilter.classList.remove('hidden');

    } else if (filterConfig.type === 'date_ingreso') {
        dateIngresoRangeFilter.classList.remove('hidden');

    } else if (filterConfig.type === 'text') {
        textFilter.classList.remove('hidden');
        textFilter.onkeydown = function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyTextFilter();
            }
        };
    }
}

        function applyTextFilter() {
    const text = document.getElementById('textFilter').value;
    if (text.trim() !== '') {
        window.location.href = '{{ route("administracion.salida_oficina.index") }}?busqueda=' + encodeURIComponent(text);
    } else {
        window.location.href = '{{ route("administracion.salida_oficina.index") }}';
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
                        window.location.href = '{{ route("administracion.salida_oficina.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.salida_oficina.index") }}';
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

    function confirmDelete(id) {
        Swal.fire({ title: '¿Estás seguro?', text: "No podrás revertir esto!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar!', cancelButtonText: 'Cancelar' })
        .then((r) => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
    }

    function updateSalidaSelection() {
        const ck = document.querySelectorAll('.record-checkbox:checked');
        const count = ck.length;

        const btn = document.getElementById('deleteSelectedBtn');
        const btnText = document.getElementById('deleteSelectedBtnText');
        if (btn) btn.disabled = count === 0;
        if (btnText) btnText.textContent = `Eliminar Seleccionados ${count > 0 ? `(${count})` : ''}`;

        const optExcel = document.getElementById('salidaOptExcelSelected');
        const optPdf = document.getElementById('salidaOptPdfSelected');
        if (optExcel) {
            optExcel.disabled = count === 0;
            optExcel.textContent = `📊 Excel - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
        }
        if (optPdf) {
            optPdf.disabled = count === 0;
            optPdf.textContent = `📄 PDF - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
        }
    }

    function updateDeleteSelectedBtn() {
        updateSalidaSelection();
    }

    function handleSalidaExport(select) {
        const val = select.value;
        if (!val) return;
        const [format, scope] = val.split(':');
        const currentParams = new URLSearchParams(window.location.search);

        if (scope === 'selected') {
            const ck = Array.from(document.querySelectorAll('.record-checkbox:checked')).map(cb => cb.value);
            if (ck.length === 0) {
                Swal.fire('Atención', 'No has seleccionado ningún registro para exportar.', 'info');
                select.value = '';
                return;
            }
            currentParams.set('selected_ids', ck.join(','));
        } else {
            currentParams.delete('selected_ids');
        }

        const baseUrl = format === 'excel'
            ? '{{ route("administracion.salida_oficina.export.excel") }}'
            : '{{ route("administracion.salida_oficina.export.pdf") }}';

        window.location.href = baseUrl + (currentParams.toString() ? '?' + currentParams.toString() : '');
        select.value = '';
    }

    function setSalidaDatePreset(preset) {
        const desdeInput = document.getElementById('salida_fecha_desde');
        const hastaInput = document.getElementById('salida_fecha_hasta');
        const now = new Date();
        const formatDate = (d) => {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        };

        if (preset === 'today') {
            const todayStr = formatDate(now);
            if (desdeInput) desdeInput.value = todayStr;
            if (hastaInput) hastaInput.value = todayStr;
        } else if (preset === 'week') {
            const start = new Date(now);
            start.setDate(now.getDate() - 7);
            if (desdeInput) desdeInput.value = formatDate(start);
            if (hastaInput) hastaInput.value = formatDate(now);
        } else if (preset === 'month') {
            const start = new Date(now.getFullYear(), now.getMonth(), 1);
            if (desdeInput) desdeInput.value = formatDate(start);
            if (hastaInput) hastaInput.value = formatDate(now);
        } else if (preset === 'year') {
            const start = new Date(now.getFullYear(), 0, 1);
            if (desdeInput) desdeInput.value = formatDate(start);
            if (hastaInput) hastaInput.value = formatDate(now);
        }
    }

    function confirmDeleteSelected() {
        const ck = document.querySelectorAll('.record-checkbox:checked');
        if (ck.length === 0) { Swal.fire('Selección', 'Debe seleccionar al menos un registro.', 'info'); return; }
        Swal.fire({
            title: '¿Estás seguro?', text: `Se eliminarán ${ck.length} registro(s) seleccionados.`, icon: 'warning',
            input: 'password', inputLabel: 'Ingresa tu contraseña para confirmar:', inputPlaceholder: 'Contraseña',
            inputAttributes: { autocapitalize: 'off', autocorrect: 'off' },
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar!', cancelButtonText: 'Cancelar',
            preConfirm: (p) => { if (!p) { Swal.showValidationMessage('Debes ingresar tu contraseña'); } return p; }
        }).then((r) => {
            if (r.isConfirmed) {
                const ids = Array.from(ck).map(cb => cb.value);
                document.getElementById('multipleDeleteIds').value = JSON.stringify(ids);
                document.getElementById('multipleDeletePassword').value = r.value;
                document.getElementById('deleteMultipleForm').submit();
            }
        });
    }

    function confirmDeleteAll() {
        Swal.fire({
            title: '¿Estás seguro?', text: "Se eliminarán TODOS los registros de salida.", icon: 'warning',
            input: 'password', inputLabel: 'Ingresa tu contraseña para confirmar:', inputPlaceholder: 'Contraseña',
            inputAttributes: { autocapitalize: 'off', autocorrect: 'off' },
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar todo!', cancelButtonText: 'Cancelar',
            preConfirm: (p) => { if (!p) { Swal.showValidationMessage('Debes ingresar tu contraseña'); } return p; }
        }).then((r) => {
            if (r.isConfirmed) {
                document.getElementById('deleteAllPassword').value = r.value;
                document.getElementById('deleteAllForm').submit();
            }
        });
    }

    function applySelectFilter() {
    const filterType = document.getElementById('filterType').value;
    const filterValue = document.getElementById('secondFilter').value;
    if (filterValue) {
        window.location.href = '{{ route("administracion.salida_oficina.index") }}?' + filterType + '=' + filterValue;
    } else {
        window.location.href = '{{ route("administracion.salida_oficina.index") }}';
    }
}

        function clearFilters() {
    window.location.href = '{{ route("administracion.salida_oficina.index") }}';
}

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            const dropdown = document.getElementById('searchableFilterDropdown');
            const button = document.getElementById('searchableFilterButton');
            if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                closeSearchableDropdown();
            }
        });
        // Inicializar filtros al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            initializeFilters();
        });

        // Función para inicializar filtros con valores de URL
        function initializeFilters() {
    const urlParams = new URLSearchParams(window.location.search);

    let activeFilterType = '';
    let activeFilterValue = '';

    if (urlParams.get('gerencia')) {
        activeFilterType = 'gerencia';
        activeFilterValue = urlParams.get('gerencia');
    } else if (urlParams.get('area')) {
        activeFilterType = 'area';
        activeFilterValue = urlParams.get('area');
    } else if (urlParams.get('articulo')) {
        activeFilterType = 'articulo';
        activeFilterValue = urlParams.get('articulo');
    } else if (urlParams.get('busqueda')) {
        activeFilterType = 'busqueda';
        activeFilterValue = urlParams.get('busqueda');
    } else if (urlParams.get('fecha_desde') || urlParams.get('fecha_hasta')) {
        activeFilterType = 'fecha';
        activeFilterValue = (urlParams.get('fecha_desde') || '') + ' - ' + (urlParams.get('fecha_hasta') || '');
    } else if (urlParams.get('fecha_ingreso_desde') || urlParams.get('fecha_ingreso_hasta')) {
        activeFilterType = 'fecha_ingreso';
        activeFilterValue = (urlParams.get('fecha_ingreso_desde') || '') + ' - ' + (urlParams.get('fecha_ingreso_hasta') || '');
    }

    if (activeFilterType) {
        const filterTypeSelect = document.getElementById('filterType');
        filterTypeSelect.value = activeFilterType;
        updateSecondFilter();

        setTimeout(() => {
            setActiveFilterValue(activeFilterType, activeFilterValue);
        }, 50);
    }
}
    
        // Función para establecer el valor activo en el filtro secundario
        function setActiveFilterValue(filterType, filterValue) {
    if (filterType === 'busqueda') {
        const textFilter = document.getElementById('textFilter');
        if (textFilter && filterValue) {
            textFilter.value = filterValue;
        }
    } else if (filterType === 'fecha') {
        const dateRangePicker = document.getElementById('dateRangePicker');
        if (dateRangePicker && filterValue && filterValue !== ' - ') {
            dateRangePicker.value = filterValue;
        }
    } else if (filterType === 'fecha_ingreso') {
        const dateIngresoRangePicker = document.getElementById('dateIngresoRangePicker');
        if (dateIngresoRangePicker && filterValue && filterValue !== ' - ') {
            dateIngresoRangePicker.value = filterValue;
        }
    } else {
        const secondFilter = document.getElementById('secondFilter');
        if (secondFilter && filterValue) {
            secondFilter.value = filterValue;
        }
    }
}
document.addEventListener('DOMContentLoaded', () => {
    initializeFilters();
});

    </script>

    <script>
    window.addEventListener('showAlert', event => { alert(event.detail.message); })
    </script>

    <script>
    // Confirm delete
        function confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }

        // Funciones para limpiar filtros específicos
        function clearSpecificFilter(filterType) {
            const url = new URL(window.location);
            url.searchParams.delete(filterType);
            window.location.href = url.toString();
        }

        function clearDateFilter(tipoFecha) {
            const url = new URL(window.location);

            if (tipoFecha === 'fecha_salida') {
                // Limpiar filtros de fecha de salida
                url.searchParams.delete('fecha_desde');
                url.searchParams.delete('fecha_hasta');
            } else if (tipoFecha === 'fecha_ingreso') {
                // Limpiar filtros de fecha de ingreso
                url.searchParams.delete('fecha_ingreso_desde');
                url.searchParams.delete('fecha_ingreso_hasta');
            }

            window.location.href = url.toString();
        }
    </script>

    <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <script>
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
                    'Últimos 3 meses': [moment().subtract(3, 'months'), moment()],
                    'Últimos 6 meses': [moment().subtract(6, 'months'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                // Auto-submit the form
                window.location.href = '{{ route("administracion.salida_oficina.index") }}?fecha_desde=' + picker.startDate.format('YYYY-MM-DD') + '&fecha_hasta=' + picker.endDate.format('YYYY-MM-DD');
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                window.location.href = '{{ route("administracion.salida_oficina.index") }}';
            });

            // Set initial value if dates are present
            var fechaDesde = $('#fecha_desde').val();
            var fechaHasta = $('#fecha_hasta').val();
            if (fechaDesde && fechaHasta) {
                $('#dateRangePicker').val(fechaDesde + ' - ' + fechaHasta);
            }

            $('#dateIngresoRangePicker').daterangepicker({
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
                    'Últimos 3 meses': [moment().subtract(3, 'months'), moment()],
                    'Últimos 6 meses': [moment().subtract(6, 'months'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateIngresoRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_ingreso_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_ingreso_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                // Auto-submit the form
                window.location.href = '{{ route("administracion.salida_oficina.index") }}?fecha_ingreso_desde=' + picker.startDate.format('YYYY-MM-DD') + '&fecha_ingreso_hasta=' + picker.endDate.format('YYYY-MM-DD');
            });

            $('#dateIngresoRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_ingreso_desde').val('');
                $('#fecha_ingreso_hasta').val('');
                window.location.href = '{{ route("administracion.salida_oficina.index") }}';
            });

            // Set initial value if dates are present
            var fechaIngresoDesde = $('#fecha_ingreso_desde').val();
            var fechaIngresoHasta = $('#fecha_ingreso_hasta').val();
            if (fechaIngresoDesde && fechaIngresoHasta) {
                $('#dateIngresoRangePicker').val(fechaIngresoDesde + ' - ' + fechaIngresoHasta);
            }
            });
    </script>
</x-app-layout>

@livewireScripts
<script src="//unpkg.com/alpinejs" defer></script>
{{-- @endcan --}}