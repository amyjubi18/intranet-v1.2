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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 animate-fadeIn">
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
    <div class="px-6 mb-6 relative z-30 overflow-visible">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
            <div class="flex flex-col gap-4">
                <!-- Fila Superior: Buscador Rápido + Acciones (Exportar, Nuevo) -->
                <div class="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3.5 sm:gap-4 pb-4 border-b border-gray-100">
                    <!-- Buscador general -->
                    <form method="GET" action="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}" class="flex-1 min-w-[260px] max-w-2xl">
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
                    <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2.5 sm:gap-3 shrink-0">
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

                        @can('dist_almac_destroy')
                        <button
                            type="button"
                            id="btnDeleteSelectedDist"
                            onclick="handleDistDeleteSelected()"
                            disabled
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer opacity-50 cursor-not-allowed"
                            style="background: linear-gradient(135deg, #8a1e1e 0%, #f63b3b 100%); box-shadow: 0 4px 12px rgba(246, 59, 59, 0.377);"
                        >
                            <span>🗑️</span>
                            <span id="btnDeleteSelectedDistText">Eliminar Seleccionados</span>
                        </button>

                        <button
                            type="button"
                            onclick="handleDistDeleteAll()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer"
                            style="background: linear-gradient(135deg, #5e0e0e 0%, #b41818 100%); box-shadow: 0 4px 12px rgba(246, 59, 59, 0.3);"
                        >
                            <span>⚠️</span>
                            <span>Eliminar Todo</span>
                        </button>
                        @endcan

                        @can('dist_almac_create')
                        <button type="button" onclick="openDistCreate()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer shrink-0" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                            <span>➕</span>
                            <span>Nueva Distribución</span>
                        </button>
                        @endcan
                    </div>
                </div>

                <!-- Fila de Control: Botón Filtros Avanzados + Badges de Filtros Activos -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                        <button
                            type="button"
                            onclick="toggleDistFilters()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer"
                            style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);"
                        >
                            <span>⚡ Filtros Avanzados</span>
                            @if($activeFiltersCount > 0)
                                <span class="w-5 h-5 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                    {{ $activeFiltersCount }}
                                </span>
                            @endif
                            <svg
                                id="arrowDistFilters"
                                class="w-3.5 h-3.5 transition-transform duration-300 transform"
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
                    id="drawerDistFilters"
                    class="grid transition-all duration-500 ease-in-out grid-rows-[0fr] opacity-0 invisible mt-0 pointer-events-none"
                >
                    <div class="overflow-visible transition-all duration-300">
                        <form method="GET" action="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}">
                            @if(request('busqueda')) <input type="hidden" name="busqueda" value="{{ request('busqueda') }}"> @endif
                            <div class="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md relative z-40 overflow-visible space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                                    <!-- Gerencia -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🏢 Gerencia</label>
                                        <select name="gerencia" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                                        <select name="area" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                                        <select name="cartucho" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                                        <select name="impresora" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                                        <select name="colore" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                                        <select name="fabricante" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none cursor-pointer">
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
                        <div class="flex-shrink-0">
                            <input type="checkbox" title="Seleccionar todos de esta impresora" class="dist-printer-group-checkbox w-5 h-5 text-blue-600 bg-white/20 border-white/40 rounded focus:ring-blue-400 cursor-pointer" onchange="toggleDistPrinterGroup(this, {{ $printerId ?: 0 }})">
                        </div>
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
                        <input type="checkbox" class="record-checkbox dist-printer-group-item-{{ $printerId ?: 0 }} w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" value="{{ $distribucion->id }}" onchange="updateDistribucionSelection()">
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
                            <button
                                type="button"
                                onclick="openDistEdit({{ $distribucion->id }})"
                                class="inline-flex h-fit items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs cursor-pointer"
                            >
                                <span>✏️</span>
                                Editar
                            </button>
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
        async function confirmDelete(id) {
            const { value: password } = await Swal.fire({
                title: '¿Estás seguro?',
                text: 'Se eliminará este registro de distribución. Ingresa tu contraseña para confirmar:',
                icon: 'warning',
                input: 'password',
                inputPlaceholder: 'Ingresa tu contraseña...',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonColor: '#F52727',
                cancelButtonColor: '#2780F5',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Debes ingresar tu contraseña';
                    }
                }
            });

            if (!password) return;

            document.getElementById("delete-form-" + id).submit();
        }
    </script>

    <!-- Modal de Creación de Distribución -->
    <div
        id="modalCrearDistribucion"
        class="fixed inset-0 z-[99999999] hidden items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md animate-modal-backdrop"
        style="display: none;"
        onclick="if(event.target === this) closeDistCreate()"
    >
        <div
            class="modal-content animate-modal-expand bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl max-h-[92vh] overflow-y-auto"
            onclick="event.stopPropagation()"
        >
            <div
                class="p-5 sm:p-6 text-white rounded-t-3xl flex items-center justify-between relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);"
            >
                <div>
                    <h3 class="text-lg sm:text-xl font-black tracking-tight">Nueva Distribución de Consumible</h3>
                    <p class="text-xs text-blue-100 mt-0.5">Asigna consumibles en almacén a una gerencia y área</p>
                </div>
                <button
                    type="button"
                    onclick="closeDistCreate()"
                    class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold grid place-items-center text-sm transition-all cursor-pointer"
                    title="Cerrar modal"
                >
                    ✕
                </button>
            </div>

            <form action="{{ route('administracion.distribucion_almacenamiento_impresoras.store') }}" method="POST" class="p-5 sm:p-6 space-y-4">
                @csrf

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-blue-900">
                            🖨️ Consumible en Almacén <span class="text-red-500">*</span>
                        </label>
                        <span id="create_dist_stock_badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hidden">
                        </span>
                    </div>
                    <select
                        name="almacenamiento_impresora_id"
                        id="create_dist_almacen_id"
                        onchange="onDistAlmacenChange(this.value, 'create_dist_')"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                        required
                    >
                        <option value="">Seleccione Consumible Disponible</option>
                        @foreach ($almacenamientos as $almacen)
                            <option value="{{ $almacen->id }}" data-disponibles="{{ $almacen->unidades_disponibles }}">
                                {{ $almacen->impresoras?->impresora ?? 'Impresora' }} - {{ $almacen->cartuchos?->cartucho ?? 'Consumible' }} ({{ $almacen->colores?->colore ?? 'N/A' }}) — Disp: {{ $almacen->unidades_disponibles }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">
                            📍 Área de Destino
                        </label>
                        <select
                            id="create_dist_area_id"
                            onchange="onDistAreaChange(this.value, 'create_dist_')"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                        >
                            <option value="">Todas las áreas / Filtrar gerencias</option>
                            @foreach ($areas->sortBy('area') as $area)
                                <option value="{{ $area->id }}">{{ $area->area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">
                            🏢 Gerencia Destino <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="gerencia_id"
                            id="create_dist_gerencia_id"
                            onchange="onDistGerenciaChange(this.value, 'create_dist_')"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                            required
                        >
                            <option value="">Seleccione Gerencia</option>
                            @foreach ($gerencias->sortBy('gerencia') as $gerencia)
                                <option value="{{ $gerencia->id }}" data-area="{{ $gerencia->area_id }}">
                                    {{ $gerencia->gerencia }} ({{ $gerencia->area?->area ?? 'Sin Área' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-900 mb-1">
                        📦 Unidades a Asignar <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="unidades_asignadas"
                        id="create_dist_unidades_asignadas"
                        min="1"
                        value="1"
                        class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20"
                        required
                    />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        onclick="closeDistCreate()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer"
                        style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);"
                    >
                        <span>📤</span>
                        <span>Guardar Distribución</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Edición de Distribución -->
    <div
        id="modalEditarDistribucion"
        class="fixed inset-0 z-[99999999] hidden items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md animate-modal-backdrop"
        style="display: none;"
        onclick="if(event.target === this) closeDistEdit()"
    >
        <div
            class="modal-content animate-modal-expand bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl max-h-[92vh] overflow-y-auto"
            onclick="event.stopPropagation()"
        >
            <div
                class="p-5 sm:p-6 text-white rounded-t-3xl flex items-center justify-between relative overflow-hidden"
                style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);"
            >
                <div>
                    <h3 class="text-lg sm:text-xl font-black tracking-tight">Editar Distribución de Consumible</h3>
                    <p id="edit_dist_title_detail" class="text-xs text-blue-100 mt-0.5"></p>
                </div>
                <button
                    type="button"
                    onclick="closeDistEdit()"
                    class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold grid place-items-center text-sm transition-all cursor-pointer"
                    title="Cerrar modal"
                >
                    ✕
                </button>
            </div>

            <form id="formEditarDistribucion" action="" method="POST" class="p-5 sm:p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-blue-900">
                            🖨️ Consumible en Almacén <span class="text-red-500">*</span>
                        </label>
                        <span id="edit_dist_stock_badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hidden">
                        </span>
                    </div>
                    <select
                        name="almacenamiento_impresora_id"
                        id="edit_dist_almacen_id"
                        onchange="onDistAlmacenChange(this.value, 'edit_dist_')"
                        class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                        required
                    >
                        <option value="">Seleccione Consumible Disponible</option>
                        @foreach ($almacenamientos as $almacen)
                            <option value="{{ $almacen->id }}" data-disponibles="{{ $almacen->unidades_disponibles }}">
                                {{ $almacen->impresoras?->impresora ?? 'Impresora' }} - {{ $almacen->cartuchos?->cartucho ?? 'Consumible' }} ({{ $almacen->colores?->colore ?? 'N/A' }}) — Disp: {{ $almacen->unidades_disponibles }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">
                            📍 Área de Destino
                        </label>
                        <select
                            id="edit_dist_area_id"
                            onchange="onDistAreaChange(this.value, 'edit_dist_')"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                        >
                            <option value="">Todas las áreas / Filtrar gerencias</option>
                            @foreach ($areas->sortBy('area') as $area)
                                <option value="{{ $area->id }}">{{ $area->area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-blue-900 mb-1">
                            🏢 Gerencia Destino <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="gerencia_id"
                            id="edit_dist_gerencia_id"
                            onchange="onDistGerenciaChange(this.value, 'edit_dist_')"
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20 cursor-pointer"
                            required
                        >
                            <option value="">Seleccione Gerencia</option>
                            @foreach ($gerencias->sortBy('gerencia') as $gerencia)
                                <option value="{{ $gerencia->id }}" data-area="{{ $gerencia->area_id }}">
                                    {{ $gerencia->gerencia }} ({{ $gerencia->area?->area ?? 'Sin Área' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-blue-900 mb-1">
                        📦 Unidades Asignadas <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="unidades_asignadas"
                        id="edit_dist_unidades_asignadas"
                        min="1"
                        class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all border-white/20"
                        required
                    />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button
                        type="button"
                        onclick="closeDistEdit()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer"
                        style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);"
                    >
                        <span>💾</span>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.distGerencias = @json($gerencias ?? []);
        window.distItems = @json($distribuciones->keyBy('id'));
        window.distAreas = @json($areas ?? []);
        window.distAlmacenamientos = @json($almacenamientos ?? []);

        function onDistAlmacenChange(almacenId, prefix) {
            const item = (window.distAlmacenamientos || []).find(x => String(x.id) === String(almacenId));
            const badge = document.getElementById(prefix + 'stock_badge');
            const qtyInput = document.getElementById(prefix + 'unidades_asignadas');

            if (item && badge) {
                badge.textContent = 'Disp: ' + (item.unidades_disponibles ?? 0) + ' unds';
                badge.classList.remove('hidden');
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ' + (item.unidades_disponibles > 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
                if (qtyInput && prefix === 'create_dist_') {
                    qtyInput.max = item.unidades_disponibles;
                }
            } else if (badge) {
                badge.classList.add('hidden');
            }
        }

        function onDistAreaChange(areaId, prefix) {
            const gerSelect = document.getElementById(prefix + 'gerencia_id');
            if (!gerSelect) return;
            const currentGerVal = gerSelect.value;
            let currentStillVisible = false;

            Array.from(gerSelect.options).forEach(opt => {
                if (!opt.value) return;
                const g = (window.distGerencias || []).find(x => String(x.id) === String(opt.value));
                if (!areaId || (g && String(g.area_id) === String(areaId))) {
                    opt.hidden = false;
                    opt.disabled = false;
                    if (opt.value === currentGerVal) currentStillVisible = true;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });

            if (areaId && !currentStillVisible && gerSelect.value) {
                gerSelect.value = '';
            }
        }

        function onDistGerenciaChange(gerenciaId, prefix) {
            const areaSelect = document.getElementById(prefix + 'area_id');
            if (!areaSelect || !gerenciaId) return;
            const g = (window.distGerencias || []).find(x => String(x.id) === String(gerenciaId));
            if (g && g.area_id) {
                areaSelect.value = g.area_id;
            }
        }

        function openDistCreate() {
            const m = document.getElementById('modalCrearDistribucion');
            if (m) {
                m.classList.remove('hidden');
                m.classList.add('flex');
                m.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDistCreate() {
            const m = document.getElementById('modalCrearDistribucion');
            if (m) {
                m.classList.remove('flex');
                m.classList.add('hidden');
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        function openDistEdit(target) {
            const m = document.getElementById('modalEditarDistribucion');
            const item = typeof target === 'object' && target !== null ? target : (window.distItems ? window.distItems[target] : null);
            if (!m || !item) return;

            document.getElementById('formEditarDistribucion').action = '{{ url("administracion/distribucion_almacenamiento_impresoras") }}/' + item.id;
            const detail = document.getElementById('edit_dist_title_detail');
            if (detail) {
                detail.textContent = 'Actualizando distribución #' + item.id;
            }

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val !== undefined && val !== null ? val : '';
            };

            setVal('edit_dist_almacen_id', item.almacenamiento_impresora_id);
            setVal('edit_dist_gerencia_id', item.gerencia_id);
            setVal('edit_dist_unidades_asignadas', item.unidades_asignadas);

            // Relacionar gerencia con area
            const g = (window.distGerencias || []).find(x => String(x.id) === String(item.gerencia_id));
            if (g && g.area_id) {
                setVal('edit_dist_area_id', g.area_id);
            }

            onDistAlmacenChange(item.almacenamiento_impresora_id, 'edit_dist_');

            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDistEdit() {
            const m = document.getElementById('modalEditarDistribucion');
            if (m) {
                m.classList.remove('flex');
                m.classList.add('hidden');
                m.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        function toggleDistFilters() {
            const drawer = document.getElementById('drawerDistFilters');
            const arrow = document.getElementById('arrowDistFilters');
            if (!drawer) return;

            const isOpen = drawer.classList.contains('grid-rows-[1fr]');
            if (isOpen) {
                drawer.classList.remove('grid-rows-[1fr]', 'opacity-100', 'mt-3', 'visible');
                drawer.classList.add('grid-rows-[0fr]', 'opacity-0', 'invisible', 'mt-0', 'pointer-events-none');
                if (arrow) arrow.classList.remove('rotate-180');
            } else {
                drawer.classList.remove('grid-rows-[0fr]', 'opacity-0', 'invisible', 'mt-0', 'pointer-events-none');
                drawer.classList.add('grid-rows-[1fr]', 'opacity-100', 'mt-3', 'visible');
                if (arrow) arrow.classList.add('rotate-180');
            }
        }
    </script>
</x-app-layout>
@endcan
