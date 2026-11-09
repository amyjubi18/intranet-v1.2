@can('almacen_inv_index')
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
@section('title', 'Almacenamiento de Consumibles')
@section('subtitle', 'Historial de almacenamiento de consumibles de impresoras')

 <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <!-- Tarjetas superiores de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fadeIn">
            <!-- Últimas Fechas de Ingreso -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                        <span class="text-blue-400" style="font-size: 24px;">📅</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $ultimasFechas }}</h3>
                <p class="text-sm text-blue-900 font-semibold" >Últimas Fechas de Factura</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                </div>
            </div>

            <!-- Consumibles Impresoras -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                        <span class="text-green-400" style="font-size: 24px;">🗃️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-400">{{ $porcentajeConsumibles }}%</span>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalConsumiblesRegistrados }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Consumibles Registrados</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-400">{{ $consumiblesEsteMes }}</span>
                    <span class="text-blue-900 font-semibold">este mes</span>
                </div>
            </div>

            <!-- Stock Bajo -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                        <span class="text-red-400" style="font-size: 24px;">⚠️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Alerta ({{ $stockBajo }})</span>
                </div>
                <h3 class="text-sm text-blue-900 font-semibold mb-3">Impresoras con Stock Bajo</h3>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    @if($stockBajoItems->count() > 0)
                        @php
                            // Obtener impresoras únicas con stock bajo y sus totales
                            $impresorasStockBajo = $stockBajoItems->unique('impresora_id');
                        @endphp
                        @foreach($impresorasStockBajo as $item)
                            @php
                                $key = $item->impresora_id . '_' . $item->colore_id . '_' . $item->fecha_ingreso;
                                $totalImpresora = isset($totalesPorImpresoraColor[$key])
                                    ? $totalesPorImpresoraColor[$key]
                                    : ['unidades_disponibles' => $item->unidades_disponibles];
                            @endphp
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-red-50 border border-red-200">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 bg-red-100">
                                    @if($item->impresoras && $item->impresoras->imagen)
                                        <img src="{{ asset('storage/' . $item->impresoras->imagen) }}" alt="Impresora" class="w-12 h-12 object-cover rounded-lg">
                                    @else
                                        <span class="text-red-400" style="font-size: 16px;">🖨️</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-red-700 truncate">{{ $item->impresoras ? $item->impresoras->impresora : 'Impresora Desconocida' }}</p>
                                    <p class="text-xs text-red-600">{{ $totalImpresora['unidades_disponibles'] }} unidades restantes</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-green-600 text-sm">No hay impresoras con stock bajo</p>
                    @endif
                </div>
            </div>

            <!-- Impresoras Registradas -->
            <div class="bg-white border border-white backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                        <span class="text-purple-400" style="font-size: 24px;">🖨️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-400">Total</span>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalImpresoras }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Impresoras Registradas</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="text-green-400">↗ {{ $impresorasEsteMes }}</span>
                    <span class="text-blue-900 font-semibold">este mes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y controles -->
    <form method="GET" action="{{ route('administracion.almacenamiento_impresoras.index') }}" id="filterForm">
    <div class="px-6 mb-6">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <!-- Filtros en el lado izquierdo -->
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Botón para mostrar filtros avanzados -->
                    <button type="button" onclick="toggleAdvancedFilters()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        Filtros Avanzados
                    </button>

                        <!-- Filtro principal - Tipo de campo (oculto por defecto) -->
                    <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('filterType') ? 'bg-blue-100 border-blue-500' : '' }}" style="display:none;">
                        <option value="">Seleccionar filtro</option>
                        <option value="fecha_ingreso" {{ request('filterType') == 'fecha_ingreso' ? 'selected' : '' }}>Fecha de Factura</option>
                        <option value="cartucho_id" {{ request('filterType') == 'cartucho_id' ? 'selected' : '' }}>Consumible</option>
                        <option value="colore_id" {{ request('filterType') == 'colore_id' ? 'selected' : '' }}>Tipo de Consumible</option>
                        <option value="impresora_id" {{ request('filterType') == 'impresora_id' ? 'selected' : '' }}>Impresora</option>
                        <option value="fabricante_id" {{ request('filterType') == 'fabricante_id' ? 'selected' : '' }}>Tipo de Impresora</option>
                        <option value="proveedore_id" {{ request('filterType') == 'proveedore_id' ? 'selected' : '' }}>Proveedor</option>

                        <option value="created_at" {{ request('filterType') == 'created_at' ? 'selected' : '' }}>Fecha de Ingreso</option>

                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative z-50">
                        <select id="secondFilter" name="secondFilter" class="bg-white border border-white/20 backdrop-blur-md rounded-xl active:border-2 active:border-blue-900/50 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('secondFilter') ? 'bg-blue-100 border-blue-500' : '' }}" {{ request('filterType') ? '' : 'disabled' }}>
                            <option value="">-----</option>
                            @if(request('filterType') == 'cartucho_id')
                                @foreach($cartuchos as $cartucho)
                                    <option value="{{ $cartucho->id }}" {{ request('secondFilter') == $cartucho->id ? 'selected' : '' }}>{{ $cartucho->cartucho }}</option>
                                @endforeach
                            @elseif(request('filterType') == 'colore_id')
                                @foreach($colores as $color)
                                    <option value="{{ $color->id }}" {{ request('secondFilter') == $color->id ? 'selected' : '' }}>{{ $color->colore }}</option>
                                @endforeach
                            @elseif(request('filterType') == 'impresora_id')
                                @foreach($impresoras as $impresora)
                                    <option value="{{ $impresora->id }}" {{ request('secondFilter') == $impresora->id ? 'selected' : '' }}>{{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT) }} - {{ $impresora->impresora }}</option>
                                @endforeach
                            @elseif(request('filterType') == 'proveedore_id')
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ request('secondFilter') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->proveedor }}</option>
                                @endforeach

                            @elseif(request('filterType') == 'fabricante_id')
                                @foreach($fabricantes as $fabricante)
                                    <option value="{{ $fabricante->id }}" {{ request('secondFilter') == $fabricante->id ? 'selected' : '' }}>{{ $fabricante->fabricante }}</option>
                                @endforeach
                            @endif

                        </select>

                    <!-- Input de fecha (oculto por defecto) -->
                    <div id="dateRangeFilter" style="{{ (request('filterType') == 'fecha_ingreso' || request('filterType') == 'created_at') ? '' : 'display:none;' }}">
                        <input type="text" style="margin-top= -10px;" id="dateRangePicker" name="fecha_rango" class="bg-blue-100 text-black border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ ((request('fecha_desde') && request('fecha_hasta')) || (request('fecha_desde_created_at') && request('fecha_hasta_created_at'))) ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ (request('fecha_desde') && request('fecha_hasta')) ? request('fecha_desde') . ' - ' . request('fecha_hasta') : ((request('fecha_desde_created_at') && request('fecha_hasta_created_at')) ? request('fecha_desde_created_at') . ' - ' . request('fecha_hasta_created_at') : '') }}" placeholder="Seleccionar rango de fechas">
                        <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                        <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        <input type="hidden" name="fecha_desde_created_at" id="fecha_desde_created_at" value="{{ request('fecha_desde_created_at') }}">
                        <input type="hidden" name="fecha_hasta_created_at" id="fecha_hasta_created_at" value="{{ request('fecha_hasta_created_at') }}">
                    </div>

                    <!-- Input de texto para búsqueda (oculto por defecto) -->
                    <input type="text" id="textFilter" name="n_factura" placeholder="Buscar..." class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('n_factura') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('n_factura') }}" style="{{ request('filterType') == 'n_factura' ? '' : 'display:none;' }}">



                    <!-- Botón para limpiar filtros -->
                    <a href="{{ route('administracion.almacenamiento_impresoras.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </a>
                </div>
            </div>
                <!-- Botones de exportar y nuevo registro en el lado derecho -->
                <div class="flex items-center gap-3 ml-auto">
                    <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                        <option value="">📤 Exportar</option>
                        <option value="{{ route('administracion.almacenamiento_impresoras.export.excel', request()->all()) }}">Exportar a Excel</option>
                        <option value="{{ route('administracion.almacenamiento_impresoras.export.pdf', request()->all()) }}">Exportar a PDF</option>
                    </select>
                    @can('almacen_inv_create')
                    <a href="/administracion/almacenamiento_impresoras/create" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Nuevo
                    </a>
                    @endcan
                </div>

        </div>

        <!-- Indicador de filtros activos -->
        @if(request('filterType') || request('secondFilter') || request('n_factura') || request('fecha_desde') || request('fecha_hasta') || request('fecha_desde_created_at') || request('fecha_hasta_created_at'))
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-blue-600 font-semibold">🔍 Filtros Activos:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(request('filterType') == 'cartucho_id' && request('secondFilter'))
                    @php
                        $cartuchoActivo = $cartuchos->where('id', request('secondFilter'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Consumible: {{ $cartuchoActivo->cartucho ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('cartucho_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('filterType') == 'colore_id' && request('secondFilter'))
                    @php
                        $colorActivo = $colores->where('id', request('secondFilter'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Tipo: {{ $colorActivo->colore ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('colore_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('filterType') == 'impresora_id' && request('secondFilter'))
                    @php
                        $impresoraActiva = $impresoras->where('id', request('secondFilter'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Impresora: {{ $impresoraActiva->impresora ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('impresora_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('filterType') == 'proveedore_id' && request('secondFilter'))
                    @php
                        $proveedorActivo = $proveedores->where('id', request('secondFilter'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Proveedor: {{ $proveedorActivo->proveedor ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('proveedore_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('filterType') == 'fabricante_id' && request('secondFilter'))
                    @php
                        $fabricanteActivo = $fabricantes->where('id', request('secondFilter'))->first();
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Tipo: {{ $fabricanteActivo->fabricante ?? 'N/A' }}
                        <button onclick="clearSpecificFilter('fabricante_id')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif

                @if(request('n_factura'))
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        Factura: "{{ request('n_factura') }}"
                        <button onclick="clearSpecificFilter('n_factura')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif



                @if(request('fecha_desde') || request('fecha_hasta') || request('fecha_desde_created_at') || request('fecha_hasta_created_at'))
                    @php
                        $fechaDesde = request('fecha_desde');
                        $fechaHasta = request('fecha_hasta');
                        $fechaDesdeCreatedAt = request('fecha_desde_created_at');
                        $fechaHastaCreatedAt = request('fecha_hasta_created_at');
                        $rangoTexto = '';
                        $tipoFecha = '';

                        if($fechaDesde && $fechaHasta) {
                            $rangoTexto = "Desde {$fechaDesde} hasta {$fechaHasta}";
                            $tipoFecha = "fecha_factura";
                        } elseif($fechaDesde) {
                            $rangoTexto = "Desde {$fechaDesde}";
                            $tipoFecha = "fecha_factura";
                        } elseif($fechaHasta) {
                            $rangoTexto = "Hasta {$fechaHasta}";
                            $tipoFecha = "fecha_factura";
                        } elseif($fechaDesdeCreatedAt && $fechaHastaCreatedAt) {
                            $rangoTexto = "Ingreso desde {$fechaDesdeCreatedAt} hasta {$fechaHastaCreatedAt}";
                            $tipoFecha = "fecha_ingreso";
                        } elseif($fechaDesdeCreatedAt) {
                            $rangoTexto = "Ingreso desde {$fechaDesdeCreatedAt}";
                            $tipoFecha = "fecha_ingreso";
                        } elseif($fechaHastaCreatedAt) {
                            $rangoTexto = "Ingreso hasta {$fechaHastaCreatedAt}";
                            $tipoFecha = "fecha_ingreso";
                        }
                    @endphp
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-sm rounded-full">
                        {{ $rangoTexto }}
                        <button onclick="clearDateFilter('{{ $tipoFecha }}')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
    </form>

    <!-- Historial de inventario -->
    <div class="px-6 mb-8">
        <div class="space-y-6">
            @php
                $groupedItems = $almacenamiento_impresoras->groupBy('impresora_id');
            @endphp

            @foreach($groupedItems as $printerId => $items)
                @php
                    $printer = $items->first()->impresoras;
                    $hasLowStock = $items->some(function($item) use ($unidadesDisponiblesBajoStock) {
                        return $unidadesDisponiblesBajoStock->contains($item->id);
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
                            <p class="text-blue-100">{{ $items->count() }} consumible{{ $items->count() > 1 ? 's' : '' }} registrado{{ $items->count() > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Consumables for this printer -->
                <div class="space-y-3 ml-8">
                    @foreach($items as $item)
                    <!-- Registro -->
                    <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                        <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                            <!-- Información principal -->
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <p class="text-xs text-blue-900 font-semibold mb-1">Fecha de Factura</p>
                                    <p class="font-semibold">{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</p>
                                    <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                                    <p class="font-mono text-sm">{{ str_pad($item->impresoras->id, 4, '0', STR_PAD_LEFT) }}

                                    </p>
                                    <p class="text-xs text-blue-900 font-semibold mt-2">Fecha de Ingreso</p>
                                    <p class="font-semibold">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</p>
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
                                </div>
                                <div>
                                    
                                </div>
                                <div>
                                    <p class="text-xs text-blue-900 font-semibold mb-1">Tipo de Impresora</p>
                                    <p class="text-sm px-2 py-1 rounded-full bg-blue-300/20 text-gray-500 inline-block">{{ $item->fabricantes ? $item->fabricantes->fabricante : 'N/A' }}</p>
                                    <p class="text-xs text-blue-900 font-semibold mt-2">Proveedor</p>
                                    <p class="text-sm">{{ $item->proveedores ? $item->proveedores->proveedor : 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Stock y acciones -->
                            <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                                <!-- Stock -->
                                <div class="text-right">
                                    <p class="text-sm text-black font-bold mb-1">Unidades Disponibles</p>
                                    @php
                                        $key = $item->impresora_id . '_' . $item->colore_id . '_' . $item->fecha_ingreso;
                                        $totalesImpresora = isset($totalesPorImpresoraColor[$key])
                                            ? $totalesPorImpresoraColor[$key]
                                            : ['unidades' => $item->unidades, 'unidades_disponibles' => $item->unidades_disponibles];
                                    @endphp
                                    <div class="flex justify-center text-center items-center gap-2">
                                        <span class="text-2xl font-bold {{ $unidadesDisponiblesBajoStock->contains($item->id) ? 'text-red-500' : 'text-green-400' }}">{{ $item->unidades_disponibles }}</span>
                                        <span class="text-xs text-blue-900 font-semibold ">de {{ $item['unidades'] }}</span>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-900">
                                        <span class="font-semibold text-sm">{{ $totalesImpresora['unidades'] }}</span> registradas, en total hay
                                        <span class="font-semibold {{ $unidadesDisponiblesBajoStock->contains($item->id) ? 'text-red-400' : 'text-green-600' }}">{{ $totalesImpresora['unidades_disponibles'] }}</span>
                                    </div>
                                    {{-- <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                        <div class="{{ $unidadesDisponiblesBajoStock->contains($item->id) ? 'bg-red-500' : 'bg-green-500' }} h-2 rounded-full" style="width: {{ $totalesImpresora['unidades'] > 0 ? min(100, ($totalesImpresora['unidades_disponibles'] / $totalesImpresora['unidades']) * 100) : 0 }}%"></div>
                                    </div> --}}
                                    @if($unidadesDisponiblesBajoStock->contains($item->id))
                                    <span class="text-xs p-2 bg-red-200 text-red-700 mt-1 inline-block rounded-md">⚠️ Stock bajo</span>
                                    @endif
                                </div>

                                <!-- Factura -->
                                <div class="text-right">
                                    <p class="text-xs text-blue-900 font-semibold mb-1">Factura</p>
                                    <p class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $item->n_factura }}</p>
                                </div>

                                <!-- Acciones -->
                                <div class="flex flex-wrap justify-center md:justify-end gap-2">
                                    @can('almacen_inv_edit')
                                    <a href="{{ route('administracion.almacenamiento_impresoras.edit', $item->id) }}" class="inline-flex h-fit items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                        <span>✏️</span>
                                        Editar
                                    </a>
                                    @endcan
                                    @can('almacen_inv_destroy')
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('administracion.almacenamiento_impresoras.destroy', $item->id) }}" method="POST" class="inline">
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
            @endforeach
        </div>

        <!-- Paginación -->
        @if($almacenamiento_impresoras->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! $almacenamiento_impresoras->links() !!}
            </div>
        </div>
        @endif
    </div>

    <!-- Modal para agregar nuevo registro -->
    <div id="addModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">Nuevo Registro de Almacenamiento</h2>
                <button onclick="hideAddModal()" class="text-blue-900 font-semibold hover:text-slate-200">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <form id="addForm" action="{{ route('administracion.almacenamiento_impresoras.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Fecha de Factura</label>
                        <input type="date" name="fecha_ingreso[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Cartucho</label>
                        <select name="cartucho_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar cartucho</option>
                            @foreach($cartuchos as $cartucho)
                            <option value="{{ $cartucho->id }}">{{ $cartucho->cartucho }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo de Consumible</label>
                        <select name="colore_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar color</option>
                            @foreach($colores as $color)
                            <option value="{{ $color->id }}">{{ $color->colore }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Impresora</label>
                        <select name="impresora_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar impresora</option>
                            @foreach($impresoras as $impresora)
                            <option value="{{ $impresora->id }}">{{ $impresora->impresora }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo de Impresora</label>
                        <select name="fabricante_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar fabricante</option>
                            @foreach($fabricantes as $fabricante)
                            <option value="{{ $fabricante->id }}">{{ $fabricante->fabricante }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Proveedor</label>
                        <select name="proveedore_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar proveedor</option>
                            @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}">{{ $proveedor->proveedor }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Unidades</label>
                        <input type="number" name="unidades[]" required min="1" class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Nº Factura</label>
                        <input type="text" name="n_factura[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="hideAddModal()" class="flex-1 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border border-white/20 bg-white hover:bg-white active:translate-y-0.5">Cancelar</button>
                    <x-primary-button type="submit" class="flex-1">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Guardar Registro
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funciones para filtros
        const filterOptions = {
            item: {{ ($almacenamiento_impresoras ?? collect())->pluck('descripcion', 'id')->toJson() }},
            proveedor: {{ ($proveedores ?? collect())->pluck('proveedor', 'id')->toJson() }}        };

        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const secondFilter = document.getElementById('secondFilter');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const textFilter = document.getElementById('textFilter');
            const generalSearch = document.getElementById('generalSearch');

            // Hide all
            secondFilterContainer.style.display = 'none';
            dateRangeFilter.style.display = 'none';
            textFilter.style.display = 'none';
            generalSearch.style.display = 'none';

            // Show based on selection
            if (filterType === 'fecha_ingreso') {
                dateRangeFilter.style.display = 'block';
            } else if (filterType === 'n_factura') {
                textFilter.style.display = 'block';
                secondFilter.style.display = 'none';

            } else if (filterType !== '') {
                    secondFilterContainer.style.display = 'block';
                    // Populate options based on filterType
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
            } else if (type === 'proveedore_id') {
                options = @json(($proveedores ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->proveedor]; }));

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

        function selectFilterOption(type, id, name) {
            document.getElementById('searchableFilterText').textContent = name;
            // Aquí puedes implementar la lógica de filtrado real
            console.log(`Filtrando por ${type}: ${name} (${id})`);
        }

        function clearFilters() {
            document.getElementById('filterType').value = '';
            document.getElementById('secondFilter').value = '';
            // Reset other filters
            updateSecondFilter();
        }

        // Modal functions
        function showAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function hideAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        // Inicializar filtros
        document.addEventListener('DOMContentLoaded', () => {
            updateSecondFilter();
            // Ocultar la opción inicial si no se ha presionado el botón de filtros avanzados
            const filterType = document.getElementById('filterType');
            const secondFilter = document.getElementById('secondFilter');
            if (filterType.value === '') {
                secondFilter.innerHTML = '';
            }
        });

        // Toggle theme (optional, since maintaining current)
        function toggleTheme() {
            document.body.classList.toggle('light');
            localStorage.setItem('theme', document.body.classList.contains('light') ? 'light' : 'dark');
        }

        // Load theme
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('light'); // Wait, adjust based on default
        }

        // Logout function
        function logout() {
            // Create a form to POST to logout route
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("logout") }}';
            // Add CSRF token if needed
            const csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = csrf.getAttribute('content');
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }

        // Toggle export menu
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('hidden');
        }

        // Close export menu when clicking outside
        document.addEventListener('click', function(event) {
            const button = document.getElementById('exportMenuButton');
            const menu = document.getElementById('exportMenu');
            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        // Funciones para limpiar filtros específicos
        function clearSpecificFilter(filterType) {
            const url = new URL(window.location);

            if (filterType === 'cartucho_id' || filterType === 'colore_id' ||
                filterType === 'impresora_id' || filterType === 'proveedore_id' ||
                filterType === 'fabricante_id') {
                // Limpiar filtro tipo y segundo filtro
                url.searchParams.delete('filterType');
                url.searchParams.delete('secondFilter');
            } else if (filterType === 'n_factura' ) {
                // Limpiar filtro de texto
                url.searchParams.delete(filterType);
            }

            window.location.href = url.toString();
        }

        function clearDateFilter(tipoFecha) {
            const url = new URL(window.location);

            if (tipoFecha === 'fecha_factura') {
                // Limpiar filtros de fecha de factura
                url.searchParams.delete('fecha_desde');
                url.searchParams.delete('fecha_hasta');
            } else if (tipoFecha === 'fecha_ingreso') {
                // Limpiar filtros de fecha de ingreso
                url.searchParams.delete('fecha_desde_created_at');
                url.searchParams.delete('fecha_hasta_created_at');
            }

            window.location.href = url.toString();
        }
    </script>

    <script>
        // Enviar formulario automáticamente al cambiar filtros
        document.querySelectorAll('#filterForm select[name], #filterForm input[name]').forEach(element => {
            element.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        });

        // Mostrar/ocultar select de filtro principal
        function toggleAdvancedFilters() {
            const filterSelect = document.getElementById('filterType');
            if (filterSelect.style.display === 'none' || filterSelect.style.display === '') {
                filterSelect.style.display = 'inline-block';
            } else {
                filterSelect.style.display = 'none';
                // Limpiar filtro principal y secundarios al ocultar
                filterSelect.value = '';
                updateSecondFilter();
                // Clear all filter inputs
                document.getElementById('secondFilter').value = '';
                document.getElementById('dateRangePicker').value = '';
                document.getElementById('generalSearch').value = '';
                // Clear hidden inputs
                document.getElementById('fecha_desde').value = '';
                document.getElementById('fecha_hasta').value = '';
                document.getElementById('fecha_desde_created_at').value = '';
                document.getElementById('fecha_hasta_created_at').value = '';
                document.getElementById('filterForm').submit();
            }
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
                    'Últimos 3 meses': [moment().subtract(3, 'months').startOf('month'), moment()],
                    'Últimos 6 meses': [moment().subtract(6, 'months').startOf('month'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                const filterType = document.getElementById('filterType').value;
                if (filterType === 'created_at') {
                    $('#fecha_desde_created_at').val(picker.startDate.format('YYYY-MM-DD'));
                    $('#fecha_hasta_created_at').val(picker.endDate.format('YYYY-MM-DD'));
                    $('#fecha_desde').val('');
                    $('#fecha_hasta').val('');
                } else {
                    $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
                    $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                    $('#fecha_desde_created_at').val('');
                    $('#fecha_hasta_created_at').val('');
                }
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
            var fechaDesde = $("#fecha_desde").val();
            var fechaHasta = $("#fecha_hasta").val();
            var fechaDesdeCreatedAt = $("#fecha_desde_created_at").val();
            var fechaHastaCreatedAt = $("#fecha_hasta_created_at").val();
            if (fechaDesde && fechaHasta) {
                $("#dateRangePicker").val(fechaDesde + " - " + fechaHasta);
            } else if (fechaDesdeCreatedAt && fechaHastaCreatedAt) {
                $("#dateRangePicker").val(fechaDesdeCreatedAt + " - " + fechaHastaCreatedAt);
            }
        });
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
