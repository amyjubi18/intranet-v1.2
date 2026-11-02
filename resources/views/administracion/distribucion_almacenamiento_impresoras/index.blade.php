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

    <!-- Filtros y controles -->
    <div class="px-6 mb-6">
        <div class="glass rounded-2xl p-6 fade-in">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Filtro principal - Tipo de campo -->
                    <select id="filterType" onchange="updateSecondFilter()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        <option value="">Seleccionar filtro</option>
                        <option value="gerencia" {{ request('gerencia') ? 'selected' : '' }}>Gerencia</option>
                        <option value="area" {{ request('area') ? 'selected' : '' }}>Área</option>
                        <option value="cartucho" {{ request('cartucho') ? 'selected' : '' }}>Consumible</option>
                        <option value="impresora" {{ request('impresora') ? 'selected' : '' }}>Impresora</option>
                        <option value="colore" {{ request('colore') ? 'selected' : '' }}>Tipo de Consumible</option>
                        <option value="fabricante" {{ request('fabricante') ? 'selected' : '' }}>Tipo Impresora</option>

                        <option value="created_at" {{ (request('fecha_desde') || request('fecha_hasta')) ? 'selected' : '' }}>Fecha de Asignación</option>
                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative" style="z-index: 99999;">
                        <select id="secondFilter" onchange="applySelectFilter()" class="glass bg-blue-100 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" disabled>
                            <option value="">Primero selecciona un filtro</option>
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

                        <!-- Input de texto para búsqueda (oculto por defecto) -->
                        <input type="text" id="textFilter" placeholder="Buscar..." class="glass rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 hidden">
                    </div>

                    <!-- Botón para limpiar filtros -->
                    <button onclick="clearFilters()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                        <option value="">📤 Exportar</option>
                        <option value="{{ route('administracion.distribucion_almacenamiento_impresoras.export.excel', request()->all()) }}">Exportar a Excel</option>
                        <option value="{{ route('administracion.distribucion_almacenamiento_impresoras.export.pdf', request()->all()) }}">Exportar a PDF</option>
                    </select>
                    @can('dist_almac_create')
                    <a href="{{ route('administracion.distribucion_almacenamiento_impresoras.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Nueva Distribución
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Indicador de filtros activos -->
        @if(request('gerencia') || request('area') || request('cartucho') || request('impresora') || request('colore') || request('fabricante')  || request('fecha_desde') || request('fecha_hasta'))
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
                        Color: {{ $colorActivo->colore ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('colore')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('fabricante'))
                    @php
                        $fabricanteActivo = $tiposConsumible->where('id', request('fabricante'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Tipo: {{ $fabricanteActivo->fabricante ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('fabricante')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
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
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Fecha: {{ $rangoTexto }}
                        <button onclick="clearDateFilter()" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
    </form>

    <!-- Lista de distribuciones -->
    <div class="px-6 mb-8">
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
