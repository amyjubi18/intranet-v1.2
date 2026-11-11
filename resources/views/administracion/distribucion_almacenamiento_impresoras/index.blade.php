@can('dist_almac_index')
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
<x-app-layout>
    @section('title', 'Distribución de Almacenamiento de Impresoras')
    @section('subtitle', 'Asignación de unidades a gerencias')

    <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <!-- Tarjetas superiores de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fadeIn">
            <!-- Total Distribuciones -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                        <span class="text-blue-400" style="font-size: 24px;">📦</span>
                    </div>
                </div>

                <p class="text-sm text-blue-500 font-semibold">Total Distribuciones</p>
                <h3 class="text-2xl font-bold mb-1">{{ $totalDistribuciones }}</h3>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-400">-></span>
                    <span class="text-blue-900 font-semibold">este mes</span>
                </div>
            </div>

            <!-- Gerencia con más unidades asignadas -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                        <span class="text-green-400" style="font-size: 24px;">🏢</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-500 text-sm">Gerencia con mas consumible</span>
                </div>
                <h3 class="text-sm font-bold mb-1">{{ $gerenciaMasUnidades->gerencia ?? 'N/A' }}</h3>
                <p class="text-sm text-blue-900 font-semibold">{{ $gerenciaMasUnidades->area ?? 'N/A' }}</p>
                <h3 class="text-2xl font-bold mb-1">{{ $gerenciaMasUnidades->total_unidades ?? 0 }}</h3>

            </div>

            <!-- Impresora con más distribuciones -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                        @if($impresoraMasDistribuciones && $impresoraMasDistribuciones->imagen)
                            <img src="{{ asset('storage/' . $impresoraMasDistribuciones->imagen) }}" alt="Impresora" class="w-12 h-12 object-cover rounded-xl">
                        @else
                            <span class="text-purple-400" style="font-size: 24px;">🖨️</span>
                        @endif
                    </div>

                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-purple-400 text-sm">Impresora con mas consumible</span>
                </div>
                <h3 class="text-sm font-bold mb-1">{{ $impresoraMasDistribuciones->impresora ?? 'N/A' }}</h3>
                <h3 class="text-2xl font-bold mb-1">{{ $impresoraMasDistribuciones->total_distribuciones ?? 0 }}</h3>

            </div>

            <!-- Impresora que más gasta consumible -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-orange-500/20">
                        @if($impresoraMasGasta && $impresoraMasGasta->imagen)
                            <img src="{{ asset('storage/' . $impresoraMasGasta->imagen) }}" alt="Impresora" class="w-12 h-12 object-cover rounded-xl">
                        @else
                            <span class="text-orange-400" style="font-size: 24px;">🖨️</span>
                        @endif
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-orange-400 text-sm">Más consumible gastado</span>
                </div>
                <h3 class="text-sm font-bold mb-1">{{ $impresoraMasGasta->impresora ?? 'N/A' }}</h3>
                <h3 class="text-2xl font-bold mb-1">{{ $impresoraMasGasta->total_unidades ?? 0 }}</h3>

            </div>
        </div>
    </div>

    @php
        $activeFiltersCount = 0;
        if(request('busqueda')) $activeFiltersCount++;
        if(request('gerencia')) $activeFiltersCount++;
        if(request('area')) $activeFiltersCount++;
        if(request('cartucho')) $activeFiltersCount++;
        if(request('impresora')) $activeFiltersCount++;
        if(request('colore')) $activeFiltersCount++;
        if(request('fabricante')) $activeFiltersCount++;
        if(request('fecha_desde') || request('fecha_hasta')) $activeFiltersCount++;
    @endphp

    <!-- Panel de Búsqueda y Filtros Avanzados Animados -->
    <div class="px-6 mb-6 relative z-30 overflow-visible" x-data="{ openFilters: {{ $activeFiltersCount > 0 ? 'true' : 'false' }} }">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
            <div class="flex flex-col gap-4">
                <!-- Fila Superior: Buscador Rápido + Acciones (Exportar, Nuevo) -->
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <!-- Buscador general -->
                    <form method="GET" action="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}" class="flex-1 max-w-xl">
                        @if(request('gerencia')) <input type="hidden" name="gerencia" value="{{ request('gerencia') }}"> @endif
                        @if(request('area')) <input type="hidden" name="area" value="{{ request('area') }}"> @endif
                        @if(request('cartucho')) <input type="hidden" name="cartucho" value="{{ request('cartucho') }}"> @endif
                        @if(request('impresora')) <input type="hidden" name="impresora" value="{{ request('impresora') }}"> @endif
                        @if(request('colore')) <input type="hidden" name="colore" value="{{ request('colore') }}"> @endif
                        @if(request('fabricante')) <input type="hidden" name="fabricante" value="{{ request('fabricante') }}"> @endif
                        @if(request('fecha_desde')) <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}"> @endif
                        @if(request('fecha_hasta')) <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}"> @endif

                        <div class="relative">
                            <input
                                type="text"
                                name="busqueda"
                                value="{{ request('busqueda') }}"
                                placeholder="🔍 Buscar por gerencia, área, consumible o impresora (Enter)..."
                                class="w-full bg-blue-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                            />
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
                        </div>
                    </form>

                    <!-- Acciones -->
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <select id="exportSelectDist" onchange="handleDistExport(this)" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs">
                            <option value="">📤 Exportar</option>
                            <optgroup label="Exportar Todos">
                                <option value="excel:all">📊 Excel - Todos los registros</option>
                                <option value="pdf:all">📄 PDF - Todos los registros</option>
                            </optgroup>
                            <optgroup label="Exportar Seleccionados" id="distExportSelectedGroup">
                                <option value="excel:selected" id="distOptExcelSelected" disabled>📊 Excel - Solo seleccionados (0)</option>
                                <option value="pdf:selected" id="distOptPdfSelected" disabled>📄 PDF - Solo seleccionados (0)</option>
                            </optgroup>
                        </select>

                        @can('dist_almac_create')
                        <a href="{{ route('administracion.distribucion_almacenamiento_impresoras.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-xs">
                            <span>➕</span>
                            <span>Nueva Distribución</span>
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
                                href="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}"
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
                        @if(request('cartucho'))
                            @php $cAct = $cartuchos->where('id', request('cartucho'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Consumible: {{ $cAct->cartucho ?? request('cartucho') }}</span>
                                <button type="button" onclick="clearSpecificFilter('cartucho')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('impresora'))
                            @php $iAct = $impresoras->where('id', request('impresora'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Impresora: {{ $iAct->impresora ?? request('impresora') }}</span>
                                <button type="button" onclick="clearSpecificFilter('impresora')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('colore'))
                            @php $colAct = $colores->where('id', request('colore'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Color: {{ $colAct->colore ?? request('colore') }}</span>
                                <button type="button" onclick="clearSpecificFilter('colore')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('fabricante'))
                            @php $fAct = $tiposConsumible->where('id', request('fabricante'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Tipo: {{ $fAct->fabricante ?? request('fabricante') }}</span>
                                <button type="button" onclick="clearSpecificFilter('fabricante')" class="hover:text-red-600 font-bold ml-1">✕</button>
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
                                <span>Asignación: {{ request('fecha_desde') ?: '...' }} a {{ request('fecha_hasta') ?: '...' }}</span>
                                <button type="button" onclick="clearSpecificFilter('fecha_desde'); clearSpecificFilter('fecha_hasta');" class="hover:text-red-600 font-bold ml-1">✕</button>
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
                        <form method="GET" action="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}">
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
                                                    {{ $g->gerencia }}
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

                                    <!-- Consumible -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">📦 Consumible</label>
                                        <select name="cartucho" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los consumibles</option>
                                            @foreach($cartuchos as $c)
                                                <option value="{{ $c->id }}" {{ request('cartucho') == $c->id ? 'selected' : '' }}>
                                                    {{ $c->cartucho }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Impresora -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🖨️ Impresora</label>
                                        <select name="impresora" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las impresoras</option>
                                            @foreach($impresoras as $imp)
                                                <option value="{{ $imp->id }}" {{ request('impresora') == $imp->id ? 'selected' : '' }}>
                                                    {{ str_pad($imp->id, 4, '0', STR_PAD_LEFT) }} - {{ $imp->impresora }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Color -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🎨 Color</label>
                                        <select name="colore" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los colores</option>
                                            @foreach($colores as $col)
                                                <option value="{{ $col->id }}" {{ request('colore') == $col->id ? 'selected' : '' }}>
                                                    {{ $col->colore }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Tipo de Impresora (Fabricante) -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🏷️ Tipo de Impresora</label>
                                        <select name="fabricante" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los fabricantes</option>
                                            @foreach($tiposConsumible as $tc)
                                                <option value="{{ $tc->id }}" {{ request('fabricante') == $tc->id ? 'selected' : '' }}>
                                                    {{ $tc->fabricante }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <!-- Rango de fechas con presets -->
                                <div class="pt-3 border-t border-blue-100/70">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                                        <label class="block text-xs font-semibold text-blue-900">📅 Rango de Fechas (Asignación)</label>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" onclick="setDistDatePreset('today')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Hoy</button>
                                            <button type="button" onclick="setDistDatePreset('week')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Esta Semana</button>
                                            <button type="button" onclick="setDistDatePreset('month')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Mes</button>
                                            <button type="button" onclick="setDistDatePreset('year')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Año</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Asignación Desde</span>
                                            <input type="date" name="fecha_desde" id="dist_fecha_desde" value="{{ request('fecha_desde') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Asignación Hasta</span>
                                            <input type="date" name="fecha_hasta" id="dist_fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <a href="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors">
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

    <!-- Lista de distribuciones -->
    <div class="px-6 mb-8 relative z-10">
        <div class="space-y-6">
            @php
                $groupedItems = $distribuciones->groupBy('almacenamientoImpresora.impresora_id');
            @endphp

            @foreach($groupedItems as $printerId => $items)
                @php
                    $printer = $items->first()->almacenamientoImpresora->impresoras;
                    $hasLowStock = $items->some(function($item) use ($unidadesDisponiblesBajoStock) {
                        return $unidadesDisponiblesBajoStock->contains($item->almacenamiento_impresora_id);
                    });
                @endphp

                <!-- Printer Header -->
                <div class="bg-gradient-to-r {{ $hasLowStock ? 'from-red-900 to-red-800' : 'from-indigo-900 to-blue-800' }} text-white rounded-2xl p-4 shadow-lg relative">
                    @if($hasLowStock)
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                            ⚠️ Stock Bajo
                        </div>
                    @endif
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl bg-white/20 flex items-center justify-center">
                            @if($printer && $printer->imagen)
                                <img src="{{ asset('storage/' . $printer->imagen) }}" alt="Impresora" class="w-18 h-18 object-cover rounded-xl">
                            @else
                                <span style="font-size: 24px;">🖨️</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">{{ $printer ? $printer->impresora : 'Impresora Desconocida' }}</h3>
                            <p class="text-blue-100">{{ $items->count() }} distribución{{ $items->count() > 1 ? 'es' : '' }} asignada{{ $items->count() > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Distribuciones for this printer -->
                <div class="space-y-3 ml-8">
                    @foreach($items as $distribucion)
            <!-- Registro -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                    <div class="flex-shrink-0 pt-2">
                        <input type="checkbox" class="record-checkbox w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" value="{{ $distribucion->id }}" onchange="updateDistribucionSelection()">
                    </div>
                    <!-- Imagen de la impresora -->
                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 self-center md:self-start">
                        @if($distribucion->almacenamientoImpresora->impresoras && $distribucion->almacenamientoImpresora->impresoras->imagen)
                            <img src="{{ asset('storage/' . $distribucion->almacenamientoImpresora->impresoras->imagen) }}" alt="Impresora" class="w-20 h-20 object-cover rounded-xl">
                        @else
                            <span class="text-blue-400" style="font-size: 32px;">🖨️</span>
                        @endif
                    </div>

                    <!-- Información principal -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>

                            <p class="text-base text-blue-900 font-semibold mt-2">ITEM #</p>
                            <p class="font-mono text-sm">{{ str_pad($distribucion->almacenamientoImpresora->impresoras ? $distribucion->almacenamientoImpresora->impresoras->id : 'N/A', 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Fecha de Asignación</p>
                            <p class="font-mono text-sm">{{ $distribucion->created_at->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Consumible</p>
                            <p class="font-semibold">{{ $distribucion->almacenamientoImpresora->cartuchos->cartucho ?? 'N/A' }}</p>
                            @php
                                $colorName = strtolower($distribucion->almacenamientoImpresora->colores ? $distribucion->almacenamientoImpresora->colores->colore : '');
                                $colorClass = match($colorName) {
                                    'negro' => 'bg-black text-white',
                                    'amarillo' => 'bg-yellow-400 text-black',
                                    'azul' => 'bg-blue-500 text-white',
                                    'magenta' => 'bg-pink-500 text-white',
                                    'tambor' => 'bg-gray-500 text-white',
                                    default => 'bg-gray-300 text-black',
                                };
                            @endphp
                            <p class="text-xs px-2 py-1 rounded-full {{ $colorClass }} inline-block">{{ $distribucion->almacenamientoImpresora->colores ? $distribucion->almacenamientoImpresora->colores->colore : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Impresora</p>
                            <p class="text-sm">{{ $distribucion->almacenamientoImpresora->impresoras ? $distribucion->almacenamientoImpresora->impresoras->impresora : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Gerencia</p>
                            <p class="text-sm px-2 py-1 rounded-full bg-blue-300/20 text-gray-500 inline-block">{{ $distribucion->gerencia->gerencia }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Área</p>
                            <p class="text-sm">{{ $distribucion->gerencia->area->area ?? 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Tipo de Impresora</p>
                            <p class="text-sm">{{ $distribucion->almacenamientoImpresora->fabricantes ? $distribucion->almacenamientoImpresora->fabricantes->fabricante : 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Unidades y acciones -->
                    <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                        <!-- Unidades Asignadas -->
                        <div class="text-center">
                            <p class="text-sm text-black font-bold mb-1">Unidades Asignadas</p>
                            <div class="flex items-center justify-center gap-2">
                                QUEDAN
                                <span class="text-3xl font-bold {{ $unidadesDisponiblesBajoStock->contains($distribucion->almacenamiento_impresora_id) ? 'text-red-500' : 'text-green-400' }}">{{ $distribucion->unidades_asignadas }}</span>
                                DE
                                <span class="text-3xl font-bold {{ $unidadesDisponiblesBajoStock->contains($distribucion->almacenamiento_impresora_id) ? 'text-red-500' : 'text-green-400' }}">{{ $distribucion->unidades_asignadas_originales }}</span>
                            </div>
                            @if($unidadesDisponiblesBajoStock->contains($distribucion->almacenamiento_impresora_id))
                            <span class="text-xs text-red-500 mt-1 inline-block">⚠️ Stock bajo en almacenamiento</span>
                            @endif
                        </div>



                        <!-- Acciones -->
                        <div class="flex flex-wrap justify-center md:justify-end gap-2">
                            @can('dist_almac_edit')
                            <a href="{{ route('administracion.distribucion_almacenamiento_impresoras.edit', $distribucion->id) }}" class="inline-flex h-fit items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('dist_almac_destroy')
                            <form id="delete-form-{{ $distribucion->id }}" action="{{ route('administracion.distribucion_almacenamiento_impresoras.destroy', $distribucion->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $distribucion->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                                    <span>🗑️</span>
                                    Eliminar
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        @if($distribuciones->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! $distribuciones->links() !!}
            </div>
        </div>
        @endif
    </div>

    <script>
        // Sistema de filtros dinámicos para distribución de almacenamiento de impresoras
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
            'cartucho': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos los consumibles' },
                    @foreach($cartuchos as $cartucho)
                    { value: '{{ $cartucho->id }}', text: '{{ $cartucho->cartucho }}' },
                    @endforeach
                ]
            },
            'impresora': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas las impresoras' },
                    @foreach($impresoras as $impresora)
                    { value: '{{ $impresora->id }}', text: '{{ $impresora->impresora }}' },
                    @endforeach
                ]
            },
            'colore': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos los colores' },
                    @foreach($colores as $colore)
                    { value: '{{ $colore->id }}', text: '{{ $colore->colore }}' },
                    @endforeach
                ]
            },
            'fabricante': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos los tipos' },
                    @foreach($tiposConsumible as $fabricante)
                    { value: '{{ $fabricante->id }}', text: '{{ $fabricante->fabricante }}' },
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
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const textFilter = document.getElementById('textFilter');

            // Ocultar todos los filtros secundarios
            secondFilter.classList.add('hidden');
            searchableFilter.classList.add('hidden');
            dateRangeFilter.classList.add('hidden');
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

            } else if (filterConfig.type === 'date') {
                // Mostrar filtro de fecha
                dateRangeFilter.classList.remove('hidden');

            } else if (filterConfig.type === 'text') {
                // Mostrar input de texto
                textFilter.classList.remove('hidden');
                textFilter.addEventListener('input', applyTextFilter);
            }

            showToast(`Filtro por ${filterType} seleccionado`, 'info');
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
                        window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}';
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
                window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}?' + filterType + '=' + filterValue;
            } else {
                window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}';
            }
        }

        function updateDistribucionSelection() {
            const ck = document.querySelectorAll('.record-checkbox:checked');
            const count = ck.length;

            const optExcel = document.getElementById('distOptExcelSelected');
            const optPdf = document.getElementById('distOptPdfSelected');
            if (optExcel) {
                optExcel.disabled = count === 0;
                optExcel.textContent = `📊 Excel - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
            }
            if (optPdf) {
                optPdf.disabled = count === 0;
                optPdf.textContent = `📄 PDF - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
            }
        }

        function handleDistExport(select) {
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
                ? '{{ route("administracion.distribucion_almacenamiento_impresoras.export.excel") }}'
                : '{{ route("administracion.distribucion_almacenamiento_impresoras.export.pdf") }}';

            window.location.href = baseUrl + (currentParams.toString() ? '?' + currentParams.toString() : '');
            select.value = '';
        }

        function setDistDatePreset(preset) {
            const desdeInput = document.getElementById('dist_fecha_desde');
            const hastaInput = document.getElementById('dist_fecha_hasta');
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

        function clearFilters() {
            document.getElementById('filterType').value = '';
            updateSecondFilter();
            // Limpiar filtros aplicados y recargar la página sin parámetros
            window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}';
        }

        function clearSpecificFilter(filterType) {
            const url = new URL(window.location);
            url.searchParams.delete(filterType);

            // Si es un filtro de fecha, eliminar ambos parámetros de fecha
            if (filterType === 'created_at' || filterType === 'fecha_desde') {
                url.searchParams.delete('fecha_desde');
                url.searchParams.delete('fecha_hasta');
            }

            window.location.href = url.toString();
        }

        function clearDateFilter() {
            const url = new URL(window.location);
            url.searchParams.delete('fecha_desde');
            url.searchParams.delete('fecha_hasta');
            window.location.href = url.toString();
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

        function initializeFilters() {
            const urlParams = new URLSearchParams(window.location.search);

            // Determinar qué tipo de filtro está activo
            let activeFilterType = '';
            if (urlParams.has('gerencia')) activeFilterType = 'gerencia';
            else if (urlParams.has('area')) activeFilterType = 'area';
            else if (urlParams.has('cartucho')) activeFilterType = 'cartucho';
            else if (urlParams.has('impresora')) activeFilterType = 'impresora';
            else if (urlParams.has('colore')) activeFilterType = 'colore';
            else if (urlParams.has('fabricante')) activeFilterType = 'fabricante';
            else if (urlParams.has('fecha_desde') || urlParams.has('fecha_hasta')) activeFilterType = 'created_at';

            if (activeFilterType) {
                // Establecer el filtro principal
                document.getElementById('filterType').value = activeFilterType;
                updateSecondFilter();

                // Establecer el valor del filtro secundario después de un breve delay
                setTimeout(() => {
                    setActiveFilterValue(activeFilterType, urlParams);
                }, 100);
            }
        }

        function setActiveFilterValue(filterType, urlParams) {
            const filterValue = urlParams.get(filterType);

            if (filterType === 'created_at') {
                // Manejar filtros de fecha
                const fechaDesde = urlParams.get('fecha_desde');
                const fechaHasta = urlParams.get('fecha_hasta');
                if (fechaDesde || fechaHasta) {
                    const dateRangeText = fechaDesde && fechaHasta ?
                        `${fechaDesde} - ${fechaHasta}` :
                        fechaDesde ? `Desde: ${fechaDesde}` : `Hasta: ${fechaHasta}`;
                    document.getElementById('dateRangePicker').value = dateRangeText;
                    document.getElementById('fecha_desde').value = fechaDesde || '';
                    document.getElementById('fecha_hasta').value = fechaHasta || '';
                }

            } else if (filterValue) {
                // Manejar filtros de selección
                const secondFilter = document.getElementById('secondFilter');
                if (secondFilter && !secondFilter.disabled) {
                    secondFilter.value = filterValue;
                }
            }
        }
    </script>

    <script>
        // Initialize date range picker for created_at (sin modificar)
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
                'Últimos 3 meses': [moment().subtract(3, 'months').startOf('month'), moment()],
                'Últimos 6 meses': [moment().subtract(6, 'months').startOf('month'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
            $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
            // Aplicar filtro de fecha redirigiendo con parámetros
            const fechaDesde = picker.startDate.format('YYYY-MM-DD');
            const fechaHasta = picker.endDate.format('YYYY-MM-DD');
            window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}?fecha_desde=' + fechaDesde + '&fecha_hasta=' + fechaHasta;
        });

        $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#fecha_desde').val('');
            $('#fecha_hasta').val('');
            window.location.href = '{{ route("administracion.distribucion_almacenamiento_impresoras.index") }}';
        });

        // Set initial value if dates are present
        var fechaDesde = $('#fecha_desde').val();
        var fechaHasta = $('#fecha_hasta').val();
        if (fechaDesde && fechaHasta) {
            $('#dateRangePicker').val(fechaDesde + ' - ' + fechaHasta);
        }
    </script>




    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar!",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("delete-form-" + id).submit();
                }
            });
        }
    </script>

</x-app-layout>
@endcan
