@can('salidaimpre_index')
<x-app-layout title="Salida de Consumibles" subtitle="Historial de salida de consumibles de impresoras">
    <!-- Tarjetas superiores de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fadeIn">
            <!-- Últimas Fechas de Salida -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                        <span class="text-blue-400" style="font-size: 24px;">📅</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $ultimasFechasSalida }}</h3>
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

            <!-- Salidas Bajo Stock -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                        <span class="text-red-400" style="font-size: 24px;">⚠️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Alerta</span>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $salidasBajoStock }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Salidas Bajo Stock</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-red-400">{{ $salidasBajoStock }}</span>
                    <span class="text-blue-900 font-semibold">Requieren atención</span>
                </div>
            </div>

            <!-- Total Items Salidos -->
            <div class="bg-white border border-white backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                        <span class="text-purple-400" style="font-size: 24px;">📦</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalItemsSalidos }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Total Items Salidos</p>
            </div>
        </div>
    </div>

    @php
        $activeFiltersCount = 0;
        if(request('busqueda')) $activeFiltersCount++;
        if(request('cartucho_id')) $activeFiltersCount++;
        if(request('colore_id')) $activeFiltersCount++;
        if(request('impresora_id')) $activeFiltersCount++;
        if(request('gerencia_id')) $activeFiltersCount++;
        if(request('area_id')) $activeFiltersCount++;
        if(request('fabricante_id')) $activeFiltersCount++;
        if(request('fecha_desde') || request('fecha_hasta')) $activeFiltersCount++;
        if(request('fecha_ingreso_desde') || request('fecha_ingreso_hasta')) $activeFiltersCount++;
    @endphp

    <!-- Panel de Búsqueda y Filtros Avanzados Animados -->
    <div class="px-6 mb-6 relative z-30 overflow-visible" x-data="{ openFilters: {{ $activeFiltersCount > 0 ? 'true' : 'false' }} }">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
            <div class="flex flex-col gap-4">
                <!-- Fila Superior: Buscador Rápido + Acciones (Exportar, Nuevo) -->
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                    <!-- Buscador general -->
                    <form method="GET" action="{{ route('administracion.salida_impresora.index') }}" class="flex-1 max-w-xl">
                        @if(request('cartucho_id')) <input type="hidden" name="cartucho_id" value="{{ request('cartucho_id') }}"> @endif
                        @if(request('colore_id')) <input type="hidden" name="colore_id" value="{{ request('colore_id') }}"> @endif
                        @if(request('impresora_id')) <input type="hidden" name="impresora_id" value="{{ request('impresora_id') }}"> @endif
                        @if(request('gerencia_id')) <input type="hidden" name="gerencia_id" value="{{ request('gerencia_id') }}"> @endif
                        @if(request('area_id')) <input type="hidden" name="area_id" value="{{ request('area_id') }}"> @endif
                        @if(request('fabricante_id')) <input type="hidden" name="fabricante_id" value="{{ request('fabricante_id') }}"> @endif
                        @if(request('fecha_desde')) <input type="hidden" name="fecha_desde" value="{{ request('fecha_desde') }}"> @endif
                        @if(request('fecha_hasta')) <input type="hidden" name="fecha_hasta" value="{{ request('fecha_hasta') }}"> @endif
                        @if(request('fecha_ingreso_desde')) <input type="hidden" name="fecha_ingreso_desde" value="{{ request('fecha_ingreso_desde') }}"> @endif
                        @if(request('fecha_ingreso_hasta')) <input type="hidden" name="fecha_ingreso_hasta" value="{{ request('fecha_ingreso_hasta') }}"> @endif

                        <div class="relative">
                            <input
                                type="text"
                                name="busqueda"
                                value="{{ request('busqueda') }}"
                                placeholder="🔍 Buscar por consumible, impresora, gerencia o área (Enter)..."
                                class="w-full bg-blue-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                            />
                            <span class="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
                        </div>
                    </form>

                    <!-- Acciones -->
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <select id="exportSelectSalidaImp" onchange="handleSalidaImpExport(this)" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs">
                            <option value="">📤 Exportar</option>
                            <optgroup label="Exportar Todos">
                                <option value="excel:all">📊 Excel - Todos los registros</option>
                                <option value="pdf:all">📄 PDF - Todos los registros</option>
                            </optgroup>
                            <optgroup label="Exportar Seleccionados" id="salidaImpExportSelectedGroup">
                                <option value="excel:selected" id="salidaImpOptExcelSelected" disabled>📊 Excel - Solo seleccionados (0)</option>
                                <option value="pdf:selected" id="salidaImpOptPdfSelected" disabled>📄 PDF - Solo seleccionados (0)</option>
                            </optgroup>
                        </select>

                        @can('salidaimpre_create')
                        <a href="{{ route('administracion.salida_impresora.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-xs">
                            <span>➕</span>
                            <span>Nuevo Registro</span>
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
                                href="{{ route('administracion.salida_impresora.index') }}"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors"
                            >
                                <span>✕</span>
                                <span>Limpiar Filtros ({{ $activeFiltersCount }})</span>
                            </a>
                        @endif
                    </div>

                    <!-- Badges de filtros activos -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if(request('cartucho_id'))
                            @php $cAct = $cartuchos->where('id', request('cartucho_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Consumible: {{ $cAct->cartucho ?? request('cartucho_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('cartucho_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('colore_id'))
                            @php $colAct = $colores->where('id', request('colore_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Color: {{ $colAct->colore ?? request('colore_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('colore_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('impresora_id'))
                            @php $impAct = $impresoras->where('id', request('impresora_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Impresora: {{ $impAct->impresora ?? request('impresora_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('impresora_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('gerencia_id'))
                            @php $gAct = $gerencias->where('id', request('gerencia_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Gerencia: {{ $gAct->gerencia ?? request('gerencia_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('gerencia_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('area_id'))
                            @php $aAct = $areas->where('id', request('area_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Área: {{ $aAct->area ?? request('area_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('area_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('fabricante_id'))
                            @php $fabAct = $fabricantes->where('id', request('fabricante_id'))->first(); @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Tipo Consumible: {{ $fabAct->fabricante ?? request('fabricante_id') }}</span>
                                <button type="button" onclick="clearSpecificFilter('fabricante_id')" class="hover:text-red-600 font-bold ml-1">✕</button>
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
                                <span>Salida: {{ request('fecha_desde') ?: '...' }} a {{ request('fecha_hasta') ?: '...' }}</span>
                                <button type="button" onclick="clearDateFilter('fecha')" class="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        @endif
                        @if(request('fecha_ingreso_desde') || request('fecha_ingreso_hasta'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Factura: {{ request('fecha_ingreso_desde') ?: '...' }} a {{ request('fecha_ingreso_hasta') ?: '...' }}</span>
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
                        <form method="GET" action="{{ route('administracion.salida_impresora.index') }}">
                            @if(request('busqueda')) <input type="hidden" name="busqueda" value="{{ request('busqueda') }}"> @endif
                            <div class="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md relative z-40 overflow-visible space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Consumible -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">📦 Consumible</label>
                                        <select name="cartucho_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los consumibles</option>
                                            @foreach($cartuchos as $cartucho)
                                                <option value="{{ $cartucho->id }}" {{ request('cartucho_id') == $cartucho->id ? 'selected' : '' }}>
                                                    {{ $cartucho->cartucho }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Color -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🎨 Color</label>
                                        <select name="colore_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los colores</option>
                                            @foreach($colores as $color)
                                                <option value="{{ $color->id }}" {{ request('colore_id') == $color->id ? 'selected' : '' }}>
                                                    {{ $color->colore }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Impresora -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🖨️ Impresora</label>
                                        <select name="impresora_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las impresoras</option>
                                            @foreach($impresoras as $impresora)
                                                <option value="{{ $impresora->id }}" {{ request('impresora_id') == $impresora->id ? 'selected' : '' }}>
                                                    {{ $impresora->impresora }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Gerencia -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🏢 Gerencia</label>
                                        <select name="gerencia_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las gerencias</option>
                                            @foreach($gerencias->sortBy('gerencia') as $gerencia)
                                                <option value="{{ $gerencia->id }}" {{ request('gerencia_id') == $gerencia->id ? 'selected' : '' }}>
                                                    {{ $gerencia->gerencia }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Área -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">📂 Área</label>
                                        <select name="area_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todas las áreas</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                    {{ $area->area }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Tipo consumible (Fabricante) -->
                                    <div>
                                        <label class="block text-xs font-semibold text-blue-900 mb-1.5">🏷️ Tipo Consumible</label>
                                        <select name="fabricante_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                            <option value="">Todos los tipos</option>
                                            @foreach($fabricantes as $fabricante)
                                                <option value="{{ $fabricante->id }}" {{ request('fabricante_id') == $fabricante->id ? 'selected' : '' }}>
                                                    {{ $fabricante->fabricante }}
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
                                            <button type="button" onclick="setSalidaImpDatePreset('today')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Hoy</button>
                                            <button type="button" onclick="setSalidaImpDatePreset('week')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Esta Semana</button>
                                            <button type="button" onclick="setSalidaImpDatePreset('month')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Mes</button>
                                            <button type="button" onclick="setSalidaImpDatePreset('year')" class="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Año</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Desde</span>
                                            <input type="date" name="fecha_desde" id="salida_imp_fecha_desde" value="{{ request('fecha_desde') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Hasta</span>
                                            <input type="date" name="fecha_hasta" id="salida_imp_fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none">
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
                                    <a href="{{ route('administracion.salida_impresora.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors">
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

    <!-- Historial de salida -->
    <div class="px-6 mb-8 relative z-10">
        <div class="space-y-4">
            @foreach($salida_impresoras as $item)
            <!-- Registro -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                    <div class="flex-shrink-0 pt-2">
                        <input type="checkbox" class="record-checkbox w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" value="{{ $item->id }}" onchange="updateSalidaImpresoraSelection()">
                    </div>
                    <!-- Imagen de la impresora -->
                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 self-center md:self-start">
                        @if($item->impresoras && $item->impresoras->imagen)
                            <img src="{{ asset('storage/' . $item->impresoras->imagen) }}" alt="Impresora" class="w-20 h-20 object-cover rounded-xl">
                        @else
                            <span class="text-blue-400" style="font-size: 32px;">🖨️</span>
                        @endif
                    </div>

                    <!-- Información principal -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Fecha de Salida</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</p>

                            @if($item->inventario_impresoras)
                            <p class="text-xs text-blue-900 font-semibold mt-2">Fecha de Ingreso</p>
                            <p class="text-sm">{{ \Carbon\Carbon::parse($item->inventario_impresoras->fecha_ingreso)->format('d/m/Y') }}</p>
                            @endif
                            <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                            <p class="font-mono text-sm">{{ $item->id }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Consumible</p>
                            <p class="font-semibold">{{ $item->cartuchos ? $item->cartuchos->cartucho : 'N/A' }}</p>
                            @php
                                $colorName = strtolower($item->colores ? $item->colores->colore : '');
                                $colorClass = match($colorName) {
                                    'negro' => 'bg-black text-white',
                                    'amarillo' => 'bg-yellow-400 text-black',
                                    'azul' => 'bg-blue-500 text-white',
                                    'magenta' => 'bg-pink-500 text-white',
                                    'tambor' => 'bg-gray-500 text-white',
                                    default => 'bg-gray-300 text-black',
                                };
                            @endphp
                            <p class="text-xs px-2 py-1 rounded-full {{ $colorClass }} inline-block">{{ $item->colores ? $item->colores->colore : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Impresora</p>
                            <p class="text-sm">{{ $item->impresoras ? $item->impresoras->impresora : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Tipo Consumible</p>
                            <p class="text-sm px-2 py-1 rounded-full bg-blue-300/20 text-gray-500 inline-block">{{ $item->fabricantes ? $item->fabricantes->fabricante : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Gerencia</p>
                            <p class="text-sm">{{ $item->gerencias ? $item->gerencias->gerencia : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Área</p>
                            <p class="text-sm">{{ $item->areas ? $item->areas->area : 'N/A' }}</p>
                        </div>


                    </div>

                        <!-- Stock y acciones -->
                        <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                        <!-- Stock -->
                        <div class="text-center">
                            <p class="text-sm text-black font-bold mb-1">Unidades Disponibles </p>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl text-center font-bold {{ $item->inventario_impresoras && $item->inventario_impresoras->unidades_disponibles < 5 ? 'text-red-400' : 'text-green-400' }}">{{ $item->inventario_impresoras ? $item->inventario_impresoras->unidades_disponibles : 'N/A' }}</span>
                                <span class="text-xs text-blue-900 font-semibold">de {{ $item->inventario_impresoras ? $item->inventario_impresoras->unidades : 'N/A' }} </span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                <div class="{{ $item->inventario_impresoras && $item->inventario_impresoras->unidades_disponibles < 5 ? 'bg-red-500' : 'bg-green-500' }} h-2 rounded-full" style="width: {{ $item->inventario_impresoras ? min(100, ($item->inventario_impresoras->unidades_disponibles / $item->inventario_impresoras->unidades) * 100) : 0 }}%"></div>
                            </div>
                            @if($item->inventario_impresoras && $item->inventario_impresoras->unidades_disponibles < 5)
                            <span class="text-xs text-red-500 mt-1 inline-block">⚠️ Stock bajo</span>
                            @endif
                        </div>

                            <!-- Cantidad Retirada -->
                            <div class="text-right mt-4">
                                <p class="text-sm text-black font-bold mb-1">Cantidad Retirada <span style="font-size: 20px;">📦</span></p>
                                <p class="text-2xl text-center font-bold text-orange-600">{{ $item->cantidad }}</p>
                            </div>

                            <!-- Ajuste Inventario -->
                            <div class="text-right">
                                <p class="text-xs text-blue-900 font-semibold mb-1">Ajuste Inventario</p>
                                <p class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $item->ajuste_inventario  }}</p>
                            </div>

                            <!-- Acciones -->
                            <div class="flex flex-wrap justify-center md:justify-end gap-2">
                                @can('salidaimpre_edit')
                                <a href="{{ route('administracion.salida_impresora.edit', $item->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                    <span>✏️</span>
                                    Editar
                                </a>
                                @endcan
                                @can('salidaimpre_destroy')
                                <form id="delete-form-{{ $item->id }}" action="{{ route('administracion.salida_impresora.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $item->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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

        <!-- Paginación -->
        @if($salida_impresoras->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! $salida_impresoras->links() !!}
            </div>
        </div>
        @endif
    </div>



    <script>
        function updateSalidaImpresoraSelection() {
            const ck = document.querySelectorAll('.record-checkbox:checked');
            const count = ck.length;

            const optExcel = document.getElementById('salidaImpOptExcelSelected');
            const optPdf = document.getElementById('salidaImpOptPdfSelected');
            if (optExcel) {
                optExcel.disabled = count === 0;
                optExcel.textContent = `📊 Excel - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
            }
            if (optPdf) {
                optPdf.disabled = count === 0;
                optPdf.textContent = `📄 PDF - Solo seleccionados ${count > 0 ? `(${count})` : '(0)'}`;
            }
        }

        function handleSalidaImpExport(select) {
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
                ? '{{ route("administracion.salida_impresora.export.excel") }}'
                : '{{ route("administracion.salida_impresora.export.pdf") }}';

            window.location.href = baseUrl + (currentParams.toString() ? '?' + currentParams.toString() : '');
            select.value = '';
        }

        function setSalidaImpDatePreset(preset) {
            const desdeInput = document.getElementById('salida_imp_fecha_desde');
            const hastaInput = document.getElementById('salida_imp_fecha_hasta');
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

        // Funciones para filtros
        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const secondFilter = document.getElementById('secondFilter');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const dateIngresoRangeFilter = document.getElementById('dateIngresoRangeFilter');
            const generalSearch = document.getElementById('generalSearch');

            // Hide all
            secondFilterContainer.style.display = 'none';
            dateRangeFilter.style.display = 'none';
            dateIngresoRangeFilter.style.display = 'none';
            generalSearch.style.display = 'none';

            // Show based on selection
            if (filterType === 'fecha') {
                dateRangeFilter.style.display = 'block';
            } else if (filterType === 'fecha_ingreso') {
                dateIngresoRangeFilter.style.display = 'block';
            } else if (filterType === 'busqueda') {
                generalSearch.style.display = 'block';
            } else if (filterType !== '') {
                secondFilterContainer.style.display = 'block';
                populateSecondFilter(filterType);
            }

            // Hide filterType select after selection
            if (filterType !== '') {
                document.getElementById('filterType').style.display = 'none';
            }
        }

        function populateSecondFilter(type) {
            const secondFilter = document.getElementById('secondFilter');
            secondFilter.innerHTML = '<option value="">-----</option>';

            let options = [];
            if (type === 'cartucho_id') {
                options = @json(($cartuchos ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->cartucho ?? 'Cartucho ' . $item->id]; }));
            } else if (type === 'colore_id') {
                options = @json(($colores ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->colore]; }));
            } else if (type === 'impresora_id') {
                options = @json(($impresoras ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->impresora]; }));
            } else if (type === 'gerencia_id') {
                options = @json(($gerencias ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->gerencia]; }));
            } else if (type === 'area_id') {
                options = @json(($areas ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->area]; }));
            } else if (type === 'fabricante_id') {
                options = @json(($fabricantes ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->fabricante]; }));
            }

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.name;
                secondFilter.appendChild(optionElement);
            });
        }

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

        // Toggle advanced filters
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

        // Auto-submit form on filter change
        document.querySelectorAll('#filterForm select[name], #filterForm input[name]').forEach(element => {
            element.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        });

        // Inicializar filtros
        document.addEventListener('DOMContentLoaded', () => {
            updateSecondFilter();
        });

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
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                // Auto-submit the form
                $('#filterForm').submit();
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                $('#filterForm').submit();
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
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateIngresoRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_ingreso_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_ingreso_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                // Auto-submit the form
                $('#filterForm').submit();
            });

            $('#dateIngresoRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_ingreso_desde').val('');
                $('#fecha_ingreso_hasta').val('');
                $('#filterForm').submit();
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
@endcan
