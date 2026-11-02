@can('salidaimpre_index')
@php
    $unidadesDisponiblesBajoStock = $unidadesDisponiblesBajoStock ?? collect();
@endphp
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
@section('title', 'Salida de impresoras')
@section('subtitle', 'Egresos de consumibles')

    <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <!-- Tarjetas superiores de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
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
                <p class="text-sm text-blue-900 font-semibold">Total de Salidas</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-400">{{ $salidasEsteMes }} -></span>
                    <span class="text-blue-900 font-semibold"> este mes</span>
                </div>
            </div>

            <!-- Impresoras Bajo Stock -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                        <span class="text-red-400" style="font-size: 24px;">⚠️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Alerta ({{ $countSalidasBajoStock }})</span>
                </div>
                <h3 class="text-sm text-blue-900 font-semibold">Impresoras Bajo Stock</h3>
                <div class="text-sm">
                    @if($salidasBajoStock->count() > 0)
                        @foreach($salidasBajoStock as $item)
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-16 h-16 rounded flex items-center justify-center flex-shrink-0">
                                    @if($item->almacenamientoImpresora && $item->almacenamientoImpresora->impresoras && $item->almacenamientoImpresora->impresoras->imagen)
                                        <img src="{{ asset('storage/' . $item->almacenamientoImpresora->impresoras->imagen) }}" alt="Impresora" class="w-16 h-16 object-cover rounded bg-gradient-to-br from-blue-500/20 to-purple-500/20">
                                    @else
                                        <span class="text-blue-400" style="font-size: 16px;">🖨️</span>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-red-600 text-sm font-bold">{{ $item->almacenamientoImpresora->impresoras->impresora }}</p>
                                    <p class="text-red-600 text-sm">({{ $countSalidasBajoStock }} unidades)</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-green-600">No hay impresoras bajo stock</p>
                    @endif
                </div>
            </div>

            <!-- Total Items Salidos -->
            {{-- <div class="bg-white border border-white backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                        <span class="text-purple-400" style="font-size: 24px;">📦</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalItemsSalidos }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Total Items Salidos</p>
            </div> --}}
        </div>
    </div>

    <!-- Filtros y controles -->
    <div class="px-6 mb-6">
        <div class="glass rounded-2xl p-6 fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro principal - Tipo de campo -->
                    <select id="filterType" onchange="updateSecondFilter()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('gerencia') || request('area') || request('cartucho') || request('impresora') || request('colore') || request('fabricante') || request('busqueda') || request('fecha_desde') || request('fecha_hasta') || request('fecha_ingreso_desde') || request('fecha_ingreso_hasta') ? 'bg-blue-700 border-blue-400' : '' }}">
                        <option value="">Seleccionar filtro</option>
                        <option value="gerencia" {{ request('gerencia') ? 'selected' : '' }}>Gerencia</option>
                        <option value="area" {{ request('area') ? 'selected' : '' }}>Área</option>
                        <option value="cartucho" {{ request('cartucho') ? 'selected' : '' }}>Consumible</option>
                        <option value="impresora" {{ request('impresora') ? 'selected' : '' }}>Impresora</option>
                        <option value="colore" {{ request('colore') ? 'selected' : '' }}>Tipo de Consumible</option>
                        <option value="fabricante" {{ request('fabricante') ? 'selected' : '' }}>Tipo de Impresora</option>
                        <option value="busqueda" {{ request('busqueda') ? 'selected' : '' }}>Búsqueda General</option>
                        <option value="fecha" {{ (request('fecha_desde') || request('fecha_hasta')) ? 'selected' : '' }}>Fecha de Salida</option>
                        <option value="fecha_ingreso" {{ (request('fecha_ingreso_desde') || request('fecha_ingreso_hasta')) ? 'selected' : '' }}>Fecha de Ingreso</option>
                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative" style="z-index: 99999;">
                        <select id="secondFilter" onchange="applySelectFilter()" class="glass bg-blue-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('gerencia') || request('area') || request('cartucho') || request('impresora') || request('colore') || request('fabricante') ? 'bg-blue-200 border-blue-500' : '' }}" disabled>
                            <option value="">Primero selecciona un filtro</option>
                            @if(request('gerencia'))
                                @foreach($gerencias as $gerencia)
                                <option value="{{ $gerencia->id }}" {{ request('gerencia') == $gerencia->id ? 'selected' : '' }}>{{ $gerencia->gerencia }} ({{ $gerencia->area->area ?? "N/A" }})</option>
                                @endforeach
                            @endif
                            @if(request('area'))
                                @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area') == $area->id ? 'selected' : '' }}>{{ $area->area }}</option>
                                @endforeach
                            @endif
                            @if(request('cartucho'))
                                @foreach($cartuchos as $cartucho)
                                <option value="{{ $cartucho->id }}" {{ request('cartucho') == $cartucho->id ? 'selected' : '' }}>{{ $cartucho->cartucho }}</option>
                                @endforeach
                            @endif
                            @if(request('impresora'))
                                @foreach($impresoras as $impresora)
                                <option value="{{ $impresora->id }}" {{ request('impresora') == $impresora->id ? 'selected' : '' }}>{{ $impresora->impresora }}</option>
                                @endforeach
                            @endif
                            @if(request('colore'))
                                @foreach($colores as $colore)
                                <option value="{{ $colore->id }}" {{ request('colore') == $colore->id ? 'selected' : '' }}>{{ $colore->colore }}</option>
                                @endforeach
                            @endif
                            @if(request('fabricante'))
                                @foreach($fabricantes as $fabricante)
                                <option value="{{ $fabricante->id }}" {{ request('fabricante') == $fabricante->id ? 'selected' : '' }}>{{ $fabricante->fabricante }}</option>
                                @endforeach
                            @endif
                        </select>

                        <!-- Filtro con buscador personalizado -->
                        <div id="searchableFilter" class="searchable-select hidden">
                            <button type="button" id="searchableFilterButton" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 w-full text-left flex items-center justify-between">
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
                        </div>

                        <!-- Input de fecha (sin modificar) -->
                        <div id="dateRangeFilter" class="hidden">
                            <input type="text" id="dateRangePicker" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" placeholder="Seleccionar rango de fechas">
                            <input type="hidden" name="fecha_desde" id="fecha_desde">
                            <input type="hidden" name="fecha_hasta" id="fecha_hasta">
                        </div>

                        <!-- Input de fecha de ingreso -->
                        <div id="dateIngresoRangeFilter" class="hidden">
                            <input type="text" id="dateIngresoRangePicker" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" placeholder="Seleccionar rango de fechas">
                            <input type="hidden" name="fecha_ingreso_desde" id="fecha_ingreso_desde">
                            <input type="hidden" name="fecha_ingreso_hasta" id="fecha_ingreso_hasta">
                        </div>

                        <!-- Input de texto para búsqueda (oculto por defecto) -->
                        <input type="text" id="textFilter" placeholder="Buscar..." value="{{ request('busqueda') }}" class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('busqueda') ? 'bg-blue-200 border-blue-500' : '' }} hidden">
                    </div>

                    <!-- Botón para limpiar filtros -->
                    <button onclick="clearFilters()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </button>
                </div>

                    <div class="flex items-center gap-3">
                        <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                            <option value="">📤 Exportar</option>
                            <option value="{{ route('administracion.salida_almacen.export.excel', request()->all()) }}">Exportar a Excel</option>
                            <option value="{{ route('administracion.salida_almacen.export.pdf', request()->all()) }}">Exportar a PDF</option>
                        </select>
                        @can('salidaimpre_create')
                        <a href="{{ route('administracion.salida_almacen.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            <span>➕</span>
                            Nuevo Registro
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicador de filtros activos -->
        @if(request('gerencia') || request('area') || request('cartucho') || request('impresora') || request('colore') || request('fabricante') || request('busqueda') || request('fecha_desde') || request('fecha_hasta') || request('fecha_ingreso_desde') || request('fecha_ingreso_hasta'))
        <div class="px-6 mb-6">
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-blue-600 font-semibold">🔍 Filtros Activos:</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(request('gerencia'))
                        @php
                            $gerenciaActiva = $gerencias->where('id', request('gerencia'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Gerencia: {{ $gerenciaActiva->gerencia ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('gerencia')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('area'))
                        @php
                            $areaActiva = $areas->where('id', request('area'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Área: {{ $areaActiva->area ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('area')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('cartucho'))
                        @php
                            $cartuchoActivo = $cartuchos->where('id', request('cartucho'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Consumible: {{ $cartuchoActivo->cartucho ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('cartucho')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('impresora'))
                        @php
                            $impresoraActiva = $impresoras->where('id', request('impresora'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Impresora: {{ $impresoraActiva->impresora ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('impresora')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('colore'))
                        @php
                            $colorActivo = $colores->where('id', request('colore'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Tipo: {{ $colorActivo->colore ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('colore')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('fabricante'))
                        @php
                            $fabricanteActivo = $fabricantes->where('id', request('fabricante'))->first();
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Tipo: {{ $fabricanteActivo->fabricante ?? 'N/A' }}
                            <button onclick="clearSpecificFilter('fabricante')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('busqueda'))
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            Búsqueda: "{{ request('busqueda') }}"
                            <button onclick="clearSpecificFilter('busqueda')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('fecha_desde') || request('fecha_hasta'))
                        @php
                            $fechaDesde = request('fecha_desde');
                            $fechaHasta = request('fecha_hasta');
                            $rangoTexto = '';

                            if($fechaDesde && $fechaHasta) {
                                $rangoTexto = "Salida desde {$fechaDesde} hasta {$fechaHasta}";
                            } elseif($fechaDesde) {
                                $rangoTexto = "Salida desde {$fechaDesde}";
                            } elseif($fechaHasta) {
                                $rangoTexto = "Salida hasta {$fechaHasta}";
                            }
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            {{ $rangoTexto }}
                            <button onclick="clearDateFilter('fecha_salida')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif

                    @if(request('fecha_ingreso_desde') || request('fecha_ingreso_hasta'))
                        @php
                            $fechaIngresoDesde = request('fecha_ingreso_desde');
                            $fechaIngresoHasta = request('fecha_ingreso_hasta');
                            $rangoIngresoTexto = '';

                            if($fechaIngresoDesde && $fechaIngresoHasta) {
                                $rangoIngresoTexto = "Ingreso desde {$fechaIngresoDesde} hasta {$fechaIngresoHasta}";
                            } elseif($fechaIngresoDesde) {
                                $rangoIngresoTexto = "Ingreso desde {$fechaIngresoDesde}";
                            } elseif($fechaIngresoHasta) {
                                $rangoIngresoTexto = "Ingreso hasta {$fechaIngresoHasta}";
                            }
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                            {{ $rangoIngresoTexto }}
                            <button onclick="clearDateFilter('fecha_ingreso')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Historial de salida -->
    <div class="px-6 mb-8">
        <div class="space-y-6">
            @php
                $groupedItems = $salida_almacenamiento_impresoras->groupBy('almacenamientoImpresora.impresora_id');
                // Ensure $unidadesDisponiblesBajoStock is defined
                if (!isset($unidadesDisponiblesBajoStock)) {
                    $unidadesDisponiblesBajoStock = collect();
                }
            @endphp

            @foreach($groupedItems as $printerId => $items)
                @php
                    $printer = $items->first()->almacenamientoImpresora->impresoras;
                @endphp

                @php
                    if (!isset($unidadesDisponiblesBajoStock)) {
                        $unidadesDisponiblesBajoStock = collect();
                    }
                    $hasLowStock = $items->some(function($item) use ($unidadesDisponiblesBajoStock) {
                        return $unidadesDisponiblesBajoStock->contains('id', $item->almacenamiento_impresora_id);
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
                            <p class="text-blue-100">{{ $items->count() }} salida{{ $items->count() > 1 ? 's' : '' }} registrada{{ $items->count() > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Salidas for this printer -->
                <div class="space-y-3 ml-8">
                    @foreach($items as $item)
            <!-- Registro -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                    <!-- Imagen de la impresora -->
                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 self-center md:self-start">
                        @if($item->almacenamientoImpresora && $item->almacenamientoImpresora->impresoras && $item->almacenamientoImpresora->impresoras->imagen)
                            <img src="{{ asset('storage/' . $item->almacenamientoImpresora->impresoras->imagen) }}" alt="Impresora" class="w-20 h-20 object-cover rounded-xl">
                        @else
                            <span class="text-blue-400" style="font-size: 32px;">🖨️</span>
                        @endif
                    </div>

                    <!-- Información principal -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Fecha de Salida</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($item->fecha_salida)->format('d/m/Y') }}</p>

                            @if($item->almacenamientoImpresora)
                            <p class="text-xs text-blue-900 font-semibold mt-2">Fecha de Ingreso</p>
                            <p class="text-sm">{{ \Carbon\Carbon::parse($item->almacenamientoImpresora->fecha_ingreso)->format('d/m/Y') }}</p>
                            @endif
                            <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                            <p class="font-mono text-sm">{{ str_pad( $item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->id : 'N/A', 4, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Consumible</p>
                            <p class="font-semibold">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->cartuchos->cartucho : 'N/A' }}</p>
                            @php
                                $colorName = strtolower($item->almacenamientoImpresora ? $item->almacenamientoImpresora->colores->colore : '');
                                $colorClass = match($colorName) {
                                    'negro' => 'bg-black text-white',
                                    'amarillo' => 'bg-yellow-400 text-black',
                                    'azul' => 'bg-blue-500 text-white',
                                    'magenta' => 'bg-pink-500 text-white',
                                    'tambor' => 'bg-gray-500 text-white',
                                    default => 'bg-gray-300 text-black',
                                };
                            @endphp
                            <p class="text-xs px-2 py-1 rounded-full {{ $colorClass }} inline-block">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->colores->colore : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Impresora</p>
                            <p class="text-sm">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->impresoras->impresora : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Tipo Impresora</p>
                            <p class="text-sm px-2 py-1 rounded-full bg-blue-300/20 text-gray-500 inline-block">{{ $item->almacenamientoImpresora ? $item->almacenamientoImpresora->fabricantes->fabricante : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Gerencia</p>
                            <p class="text-sm">{{ $item->distribucionAlmacenamientoImpresora ? $item->distribucionAlmacenamientoImpresora->gerencia->gerencia : 'N/A' }}</p>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Área</p>
                            <p class="text-sm">{{ $item->distribucionAlmacenamientoImpresora ? $item->distribucionAlmacenamientoImpresora->gerencia->area->area : 'N/A' }}</p>
                        </div>

                    </div>

                        <!-- Stock y acciones -->
                        <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
            <!-- Unidades Asignadas -->
            <div class="text-center">
                <p class="text-sm text-black font-bold mb-1">Unidades Disponibles</p>
                <div class="flex items-center gap-2 justify-center">
                    <span class="text-2xl font-bold text-blue-600 {{ $unidadesDisponiblesBajoStock->contains('id', $item->almacenamiento_impresora_id) ? 'text-red-500' : '' }}">{{ $item->historico_unidades_asignadas_restantes ?? 0 }}</span>
                    <span class="text-xs text-blue-900 font-semibold">de {{ $item->historico_unidades_asignadas_originales ?? 0 }}</span>
                </div>
                @php
                    $original = $item->historico_unidades_asignadas_originales ?? 0;
                    $restantes = $item->historico_unidades_asignadas_restantes ?? 0;
                    $consumidas = $original - $restantes;
                    $porcentaje_remaining = $original > 0 ? (($restantes / $original) * 100) : 0;
                    $barraColor = $porcentaje_remaining < 50 ? 'bg-red-500' : ($porcentaje_remaining < 70 ? 'bg-yellow-500' : 'bg-green-500');
                @endphp
                <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                    <div class="{{ $barraColor }} h-2 rounded-full" style="width: {{ min(100, $porcentaje_remaining) }}%"></div>
                </div>
                <div class="text-xs text-gray-600 mt-1">
                    {{ $consumidas }} consumidas ({{ number_format(100 - $porcentaje_remaining, 1) }}%)
                </div>
                @if($porcentaje_remaining < 20)
                <span class="text-xs text-red-500 mt-1 inline-block">⚠️ Pocas unidades restantes</span>
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
                                <p class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $item->ajuste_inventario }}</p>
                            </div>

                            <!-- Acciones -->
                            {{-- <div class="flex flex-wrap justify-center md:justify-end gap-2">
                                @can('salidaimpre_edit')
                                <a href="{{ route('administracion.salida_almacen.edit', $item->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                    <span>✏️</span>
                                    Editar
                                </a>
                                @endcan
                                <form id="delete-form-{{ $item->id }}" action="{{ route('administracion.salida_almacen.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $item->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                                        <span>🗑️</span>
                                        Eliminar
                                    </button>
                                </form>
                            </div> --}}
                        </div>
                </div>
            </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        @if($salida_almacenamiento_impresoras->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! $salida_almacenamiento_impresoras->links() !!}
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Sistema de filtros dinámicos para salida de almacenamiento de impresoras
        const filterData = {
            'gerencia': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas las gerencias' },
                    @foreach($gerencias as $gerencia)
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
                    { value: '', text: 'Todos los tipos consumiblea' },
                    @foreach($colores as $colore)
                    { value: '{{ $colore->id }}', text: '{{ $colore->colore }}' },
                    @endforeach
                ]
            },
            'fabricante': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos los tipos de impresora' },
                    @foreach($fabricantes as $fabricante)
                    { value: '{{ $fabricante->id }}', text: '{{ $fabricante->fabricante }}' },
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

            } else if (filterConfig.type === 'date_ingreso') {
                // Mostrar filtro de fecha de ingreso
                dateIngresoRangeFilter.classList.remove('hidden');

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
                // Aplicar filtro de búsqueda redirigiendo con parámetro
                window.location.href = '{{ route("administracion.salida_almacen.index") }}?busqueda=' + encodeURIComponent(text);
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
                        window.location.href = '{{ route("administracion.salida_almacen.index") }}?' + filterType + '=' + option.value;
                    } else {
                        window.location.href = '{{ route("administracion.salida_almacen.index") }}';
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
                window.location.href = '{{ route("administracion.salida_almacen.index") }}?' + filterType + '=' + filterValue;
            } else {
                window.location.href = '{{ route("administracion.salida_almacen.index") }}';
            }
        }

        function clearFilters() {
            document.getElementById('filterType').value = '';
            updateSecondFilter();
            // Limpiar filtros aplicados y recargar la página sin parámetros
            window.location.href = '{{ route("administracion.salida_almacen.index") }}';
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

            // Determinar el tipo de filtro activo
            let activeFilterType = '';
            let activeFilterValue = '';

            if (urlParams.get('gerencia')) {
                activeFilterType = 'gerencia';
                activeFilterValue = urlParams.get('gerencia');
            } else if (urlParams.get('area')) {
                activeFilterType = 'area';
                activeFilterValue = urlParams.get('area');
            } else if (urlParams.get('cartucho')) {
                activeFilterType = 'cartucho';
                activeFilterValue = urlParams.get('cartucho');
            } else if (urlParams.get('impresora')) {
                activeFilterType = 'impresora';
                activeFilterValue = urlParams.get('impresora');
            } else if (urlParams.get('colore')) {
                activeFilterType = 'colore';
                activeFilterValue = urlParams.get('colore');
            } else if (urlParams.get('fabricante')) {
                activeFilterType = 'fabricante';
                activeFilterValue = urlParams.get('fabricante');
            } else if (urlParams.get('busqueda')) {
                activeFilterType = 'busqueda';
                activeFilterValue = urlParams.get('busqueda');
            } else if (urlParams.get('fecha_desde') || urlParams.get('fecha_hasta')) {
                activeFilterType = 'fecha';
            } else if (urlParams.get('fecha_ingreso_desde') || urlParams.get('fecha_ingreso_hasta')) {
                activeFilterType = 'fecha_ingreso';
            }

            // Si hay un filtro activo, establecerlo
            if (activeFilterType) {
                const filterTypeSelect = document.getElementById('filterType');
                filterTypeSelect.value = activeFilterType;
                updateSecondFilter();

                // Esperar un momento para que se actualice el filtro secundario
                setTimeout(() => {
                    setActiveFilterValue(activeFilterType, activeFilterValue);
                }, 100);
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
                // Para fechas de salida
                if (filterValue) {
                    const fechaDesde = document.getElementById('fecha_desde');
                    const fechaHasta = document.getElementById('fecha_hasta');
                    const dateRangePicker = document.getElementById('dateRangePicker');

                    if (fechaDesde && fechaHasta && dateRangePicker && filterValue.includes(' - ')) {
                        const [desde, hasta] = filterValue.split(' - ');
                        fechaDesde.value = desde;
                        fechaHasta.value = hasta;
                        dateRangePicker.value = filterValue;
                    }
                }
            } else if (filterType === 'fecha_ingreso') {
                // Para fechas de ingreso
                if (filterValue) {
                    const fechaIngresoDesde = document.getElementById('fecha_ingreso_desde');
                    const fechaIngresoHasta = document.getElementById('fecha_ingreso_hasta');
                    const dateIngresoRangePicker = document.getElementById('dateIngresoRangePicker');

                    if (fechaIngresoDesde && fechaIngresoHasta && dateIngresoRangePicker && filterValue.includes(' - ')) {
                        const [desde, hasta] = filterValue.split(' - ');
                        fechaIngresoDesde.value = desde;
                        fechaIngresoHasta.value = hasta;
                        dateIngresoRangePicker.value = filterValue;
                    }
                }
            } else {
                // Para filtros de selección
                const secondFilter = document.getElementById('secondFilter');
                if (secondFilter && filterValue) {
                    secondFilter.value = filterValue;
                }
            }
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
                window.location.href = '{{ route("administracion.salida_almacen.index") }}?fecha_desde=' + picker.startDate.format('YYYY-MM-DD') + '&fecha_hasta=' + picker.endDate.format('YYYY-MM-DD');
            });

            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');
                window.location.href = '{{ route("administracion.salida_almacen.index") }}';
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
                window.location.href = '{{ route("administracion.salida_almacen.index") }}?fecha_ingreso_desde=' + picker.startDate.format('YYYY-MM-DD') + '&fecha_ingreso_hasta=' + picker.endDate.format('YYYY-MM-DD');
            });

            $('#dateIngresoRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#fecha_ingreso_desde').val('');
                $('#fecha_ingreso_hasta').val('');
                window.location.href = '{{ route("administracion.salida_almacen.index") }}';
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
