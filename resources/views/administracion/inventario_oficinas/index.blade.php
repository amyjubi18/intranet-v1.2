
@can('inventarioofic_index')
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
@section('title', 'Inventario de Oficinas')
@section('subtitle', 'Historial de inventario de oficinas')

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
                <h3 class="text-2xl font-bold mb-1">{{ $ultimasFechas ?? 'N/A' }}</h3>
                <p class="text-sm text-blue-900 font-semibold" >Última Fecha de Ingreso</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                </div>
            </div>

            <!-- Items de Oficina -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                        <span class="text-green-400" style="font-size: 24px;">📦</span>
                    </div>
                    <span class="text-lg px-2 py-1 rounded-full bg-green-500/20 text-green-400">{{ $totalItemsRegistrados ?? 0  }}</span>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalItemsRegistrados ?? 0 }}</h3>

            </div>

            <!-- Stock Bajo -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                        <span class="text-red-400" style="font-size: 24px;">⚠️</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-400">Alerta <b class="text-lg">{{ $stockBajo ?? 0 }}</b></span>
                </div>

                <p class="text-sm text-blue-900 font-semibold">Stock Bajo</p>
                <div class="mt-3 text-xs">
                    @if($articulosStockBajo && $articulosStockBajo->count() > 0)
                        @foreach($articulosStockBajo as $articulo)
                            <div class="flex rounded-md items-center gap-2 mb-1 bg-red-100">
                                <span class="text-black mb-1 font-semibold p-2">{{ $articulo->articulos->articulo ?? 'N/A' }}</span>
                                <span class="text-blue-900 font-semibold">({{ $articulo->total_disponibles }})</span>
                            </div>
                        @endforeach
                    @else
                        <span class="text-blue-900 font-semibold">Sin artículos con stock bajo</span>
                    @endif
                </div>
            </div>

            <!-- Artículo Más Registrado -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                        <span class="text-purple-400" style="font-size: 24px;">🏆</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-400">Top <b class="text-lg">{{ $articuloMasRegistros ? $articuloMasRegistros->total_registros : 0 }}</b></span>
                </div>
                <p class="text-sm text-blue-900 font-semibold">Artículo Más Registrado</p>
                <h3 class="text-md font-bold rounded-md mb-1 p-2 bg-purple-500/20">{{ $articuloMasRegistros ? $articuloMasRegistros->articulos->articulo : 'N/A' }}</h3>


            </div>
        </div>
    </div>

    <!-- Filtros y controles -->
    <form method="GET" action="{{ route('administracion.inventario_oficinas.index') }}" id="filterForm">
    <div class="px-6 mb-6">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Botón para mostrar filtros avanzados -->
                    <button type="button" onclick="toggleAdvancedFilters()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        Filtros Avanzados
                    </button>

                    <!-- Filtro principal - Tipo de campo (oculto por defecto) -->
                    <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('filterType') ? 'bg-blue-100 border-blue-500' : '' }}" style="display:none;">
                        <option value="">Seleccionar filtro</option>
                        <option value="fecha" {{ request('filterType') == 'fecha' ? 'selected' : '' }}>Fecha de Factura</option>
                        <option value="fecha_creacion" {{ request('filterType') == 'fecha_creacion' ? 'selected' : '' }}>Fecha de Ingreso</option>

                        <option value="proveedore_id" {{ request('filterType') == 'proveedore_id' ? 'selected' : '' }}>Proveedor</option>
                        <option value="articulo_id" {{ request('filterType') == 'articulo_id' ? 'selected' : '' }}>Artículo</option>
                        <option value="unidad_medida_id" {{ request('filterType') == 'unidad_medida_id' ? 'selected' : '' }}>Unidad de Medida</option>

                        <option value="n_factura" {{ request('filterType') == 'n_factura' ? 'selected' : '' }}>Factura</option>
                        <option value="busqueda" {{ request('filterType') == 'busqueda' ? 'selected' : '' }}>Búsqueda General</option>
                    </select>

                    <!-- Filtro secundario - Dinámico según selección -->
                    <div id="secondFilterContainer" class="relative z-50">
                        <select id="secondFilter" name="secondFilter" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl active:border-2 active:border-blue-900/50 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('secondFilter') ? 'bg-blue-100 border-blue-500' : '' }}" {{ request('filterType') ? '' : 'disabled' }}>
                            <option value="">-----</option>

                            @if(request('filterType') == 'proveedore_id')
                                @foreach($proveedores ?? [] as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ request('secondFilter') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->proveedor }}</option>
                                @endforeach
                            @elseif(request('filterType') == 'articulo_id')
                                @foreach($articulos ?? [] as $articulo)
                                    <option value="{{ $articulo->id }}" {{ request('secondFilter') == $articulo->id ? 'selected' : '' }}>{{ str_pad($articulo->id, 4, '0', STR_PAD_LEFT) }} - {{ $articulo->articulo }}</option>
                                @endforeach
                            @elseif(request('filterType') == 'unidad_medida_id')
                                @foreach($unidad_medidas ?? [] as $unidad_medida)
                                    <option value="{{ $unidad_medida->id }}" {{ request('secondFilter') == $unidad_medida->id ? 'selected' : '' }}>{{ $unidad_medida->unidad_medida }}</option>
                                @endforeach

                            @endif
                        </select>
                    </div>

                    <!-- Input de fecha (oculto por defecto) -->
                    <div id="dateRangeFilter" style="{{ (request('filterType') == 'fecha' || request('filterType') == 'fecha_creacion') ? '' : 'display:none;' }}">
                        <input type="text" id="dateRangePicker" name="fecha_rango" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ (request('fecha_desde') && request('fecha_hasta')) ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('fecha_desde') && request('fecha_hasta') ? request('fecha_desde') . ' - ' . request('fecha_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                        <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                        <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>

                    <!-- Input de texto para búsqueda (oculto por defecto) -->
                    <input type="text" id="textFilter" name="n_factura" placeholder="Buscar..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('n_factura') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('n_factura') }}" style="{{ request('filterType') == 'n_factura' && request('secondFilter') == '' ? '' : 'display:none;' }}">

                    <!-- Select de facturas (oculto por defecto) -->
                    <select id="facturaSelect" name="secondFilter" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('filterType') == 'n_factura' && request('secondFilter') ? 'bg-blue-100 border-blue-500' : '' }}" style="{{ request('filterType') == 'n_factura' && request('secondFilter') ? '' : 'display:none;' }}">
                        <option value="">Seleccionar factura</option>
                        @foreach($facturas ?? [] as $factura)
                            <option value="{{ $factura }}" {{ request('secondFilter') == $factura ? 'selected' : '' }}>{{ $factura }}</option>
                        @endforeach
                    </select>

                    <!-- Input de búsqueda general (oculto por defecto) -->
                    <input type="text" id="generalSearch" name="busqueda" placeholder="Buscar en todos los campos..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('busqueda') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('busqueda') }}" style="{{ request('filterType') == 'busqueda' ? '' : 'display:none;' }}">

                    <!-- Botón para limpiar filtros -->
                    <a href="{{ route('administracion.inventario_oficinas.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        🗑️ Limpiar
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Botones de exportar y nuevo registro -->
                    <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                        <option value="">📤 Exportar</option>
                        <option value="{{ route('administracion.inventario_oficinas.export.excel', request()->all()) }}">Exportar a Excel</option>
                        <option value="{{ route('administracion.inventario_oficinas.export.pdf', request()->all()) }}">Exportar a PDF</option>
                    </select>
                    @can('inventarioofic_create')
                    <a href="/administracion/inventario_oficinas/create" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                        <span>➕</span>
                        Nuevo Registro
                    </a>
                    @endcan
                </div>
            </div>
            </div>
        </div>
    </div>
    </form>

    <!-- Historial de inventario -->
    <div class="px-6 mb-8">
        <!-- Mostrar título si estamos viendo todos los registros de un artículo -->
        @if(isset($mostrarTodosRegistros) && $mostrarTodosRegistros && ($inventario_oficinas ?? [])->count() > 0)
            @php
                $primerItem = $inventario_oficinas->first();
                $nombreArticulo = 'Artículo Desconocido';
                if ($primerItem) {
                    // Intentar obtener el artículo de diferentes formas
                    if (isset($primerItem->articulos) && is_object($primerItem->articulos) && isset($primerItem->articulos->articulo)) {
                        $nombreArticulo = $primerItem->articulos->articulo;
                    } elseif (isset($articulos) && $primerItem->articulo_id) {
                        $articuloEncontrado = $articulos->find($primerItem->articulo_id);
                        if ($articuloEncontrado) {
                            $nombreArticulo = $articuloEncontrado->articulo;
                        }
                    }
                }
            @endphp
            <div class="mb-6 bg-blue-900 border border-blue-900 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📦</span>
                    <div>
                        <p class="text-xl font-bold text-white">{{ $nombreArticulo }}</p>
                        <p class="text-sm text-white">Total de registros: {{ ($inventario_oficinas ?? collect())->count() }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="space-y-6">
            @foreach($inventario_oficinas ?? [] as $item)
                @php
                    // Obtener el artículo de forma segura
                    $articuloModel = null;
                    if (isset($item->articulos) && is_object($item->articulos)) {
                        $articuloModel = $item->articulos;
                    } elseif (isset($articulos) && $item->articulo_id) {
                        $articuloModel = $articulos->find($item->articulo_id);
                    }
                    $hasLowStock = $articuloModel && $item->unidades_disponibles < $articuloModel->stock_min;

                    // Determinar si es un grupo o un registro individual
                    $isGroup = isset($item->original_items) && $item->original_items instanceof \Illuminate\Support\Collection && $item->original_items->count() > 1;
                    $itemsToShow = isset($item->original_items) ? $item->original_items : collect([$item]);
                @endphp

                <!-- Article Header (solo si NO estamos mostrando todos los registros de un artículo) -->
                @if(!isset($mostrarTodosRegistros) || !$mostrarTodosRegistros)
                <div class="bg-gradient-to-r {{ $hasLowStock ? 'from-red-900 to-red-800' : 'from-indigo-900 to-blue-800' }} text-white rounded-2xl p-4 shadow-lg relative">
                    @if($hasLowStock)
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                            ⚠️ Stock Bajo
                        </div>
                    @endif
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl bg-white/20 flex items-center justify-center">
                            <span style="font-size: 24px;">📦</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold">{{ $articuloModel ? $articuloModel->articulo : 'Artículo Desconocido' }}</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2 text-sm">
                                <div>
                                    <p class="text-blue-100 text-xs">Registros Agrupados</p>
                                    <p class="font-semibold">{{ $itemsToShow->count() }}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Registrar todos los items individuales (expandidos) -->
                @foreach($itemsToShow as $singleItem)
                <!-- Registro Individual -->
                <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200 {{ isset($mostrarTodosRegistros) && $mostrarTodosRegistros ? '' : 'ml-8' }}">
                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">

                    <!-- Información principal -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Última Fecha de Factura</p>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($singleItem->fecha_ingreso)->format('d/m/Y') }}</p>
                            <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                            <p class="font-mono text-sm">{{ str_pad($singleItem->articulo_id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-blue-900 font-semibold mt-2">Articulo</p>
                            <p class="text-md font-bold">
                                {{ $articuloModel ? str_pad($articuloModel->id, 4, '0', STR_PAD_LEFT) . ' - ' . $articuloModel->articulo : 'N/A' }}
                            </p>
                            <p class="text-xs text-blue-900 font-semibold mb-1">Unidad de Medida</p>
                            <p class="text-sm">{{ $singleItem->unidad_medidas ? $singleItem->unidad_medidas->unidad_medida : 'N/A' }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-blue-900 font-semibold mt-2">Proveedor</p>
                            <p class="text-sm">{{ $singleItem->proveedores ? $singleItem->proveedores->proveedor : 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Stock y acciones -->
                    <div class="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                        <!-- Stock -->
                        <div class="text-right">
                            <p class="text-sm text-black font-bold mb-1">Unidades Disponibles</p>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold {{ $articuloModel && $singleItem->unidades_disponibles < $articuloModel->stock_min ? 'text-red-400' : 'text-green-400' }}">{{ $singleItem->unidades_disponibles }}</span>
                                <span class="text-xs text-blue-900 font-semibold">de {{ $singleItem->unidades }}</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                <div class="{{ $articuloModel && $singleItem->unidades_disponibles < $articuloModel->stock_min ? 'bg-red-500' : 'bg-green-500' }} h-2 rounded-full" style="width: {{ $singleItem->unidades > 0 ? min(100, ($singleItem->unidades_disponibles / $singleItem->unidades) * 100) : 0 }}%"></div>
                            </div>
                            @if($articuloModel && $singleItem->unidades_disponibles < $articuloModel->stock_min)
                            <span class="text-xs text-red-500 mt-1 inline-block">⚠️ Stock bajo</span>
                            @endif
                        </div>

                        <!-- Detalles de registros -->
                        <div class="text-right">
                            <p class="text-xs text-blue-900 font-semibold mb-1">Nº de Factura</p>
                            <div class="space-y-1">

                                <div class="text-xs bg-gray-100 px-2 py-1 rounded">
                                    <span class="text-gray-600">{{ $singleItem->n_factura }}</span>
                                </div>

                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-wrap justify-center md:justify-end gap-2">
                            @can('inventarioofic_edit')
                            <a href="{{ route('administracion.inventario_oficinas.edit', $singleItem->id) }}" class="inline-flex items-center gap-2 px-3 h-8  rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs">
                                <span>✏️</span>
                                Editar
                            </a>
                            @endcan
                            @can('inventarioofic_destroy')
                            <form id="delete-form-{{ $singleItem->id }}" action="{{ route('administracion.inventario_oficinas.destroy', $singleItem->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $singleItem->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
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
            @endforeach
        </div>

        <!-- Paginación -->
        @if(($inventario_oficinas ?? collect())->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! ($inventario_oficinas ?? collect())->links() !!}
            </div>
        </div>
        @endif
    </div>

    {{-- <!-- Modal para agregar nuevo registro -->
    <div id="addModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">Nuevo Registro de Item de Oficina</h2>
                <button onclick="hideAddModal()" class="text-blue-900 font-semibold hover:text-slate-200">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <form id="addForm" action="{{ route('administracion.inventario_oficinas.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Fecha de Factura</label>
                        <input type="date" name="fecha_ingreso[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ date('Y-m-d') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Proveedor</label>
                        <select name="proveedore_id[]" required class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            <option value="">Seleccionar proveedor</option>
                            @foreach($proveedores ?? [] as $proveedor)
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
    </div> --}}

    <script>
        // ==========================================
        // FUNCIONES DE FILTROS - INVENTARIO OFICINAS
        // ==========================================

        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const secondFilter = document.getElementById('secondFilter');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const textFilter = document.getElementById('textFilter');
            const generalSearch = document.getElementById('generalSearch');
            const facturaSelect = document.getElementById('facturaSelect');

            // Ocultar todos los filtros primero
            secondFilterContainer.style.display = 'none';
            secondFilter.disabled = true;
            dateRangeFilter.style.display = 'none';
            textFilter.style.display = 'none';
            generalSearch.style.display = 'none';
            facturaSelect.style.display = 'none';
            facturaSelect.disabled = true;

            // Mostrar según la selección
            if (filterType === 'fecha' || filterType === 'fecha_creacion') {
                dateRangeFilter.style.display = 'block';
                // initializeDatePicker() se llama desde $(document).ready()
            } else if (filterType === 'n_factura') {
                textFilter.style.display = 'none';
                facturaSelect.style.display = 'block';
                facturaSelect.disabled = false;
            } else if (filterType === 'busqueda') {
                generalSearch.style.display = 'block';
            } else if (filterType !== '') {
                // Para proveedor, artículo y unidad de medida
                secondFilterContainer.style.display = 'block';
                secondFilter.disabled = false;
                populateSecondFilter(filterType);
            }
        }

        function populateSecondFilter(type) {
            const secondFilter = document.getElementById('secondFilter');

            // Si ya hay opciones cargadas desde el servidor y coinciden con el tipo, no repoblar
            const existingOptions = secondFilter.querySelectorAll('option');
            const hasExistingOptions = existingOptions.length > 1 &&
                ((type === 'proveedore_id' && existingOptions[1]?.textContent.includes('@')) ||
                 (type === 'articulo_id' && existingOptions[1]?.textContent.includes('-')) ||
                 (type === 'unidad_medida_id' && existingOptions[1]?.textContent.length > 0));

            if (hasExistingOptions) {
                return; // Las opciones ya están cargadas desde el servidor
            }

            // Si no hay opciones, poblar dinámicamente
            secondFilter.innerHTML = '<option value="">Seleccionar...</option>';

            let options = [];

            if (type === 'proveedore_id') {
                options = @json(($proveedores ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->proveedor]; }));
            } else if (type === 'articulo_id') {
                // Format articulo options with padded ID in JavaScript to avoid PHP syntax issues
                const articulosData = @json($articulos ?? []);
                options = articulosData.map(item => ({
                    id: item.id,
                    name: String(item.id).padStart(4, '0') + ' - ' + item.articulo
                }));
            } else if (type === 'unidad_medida_id') {
                options = @json(($unidad_medidas ?? collect())->map(function($item) { return ['id' => $item->id, 'name' => $item->unidad_medida]; }));
            }

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.id;
                optionElement.textContent = option.name;
                secondFilter.appendChild(optionElement);
            });
        }

        // Mostrar/ocultar select de filtro principal
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

        // Inicialización cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar filtros según la URL actual
            updateSecondFilter();

            // Auto-submit para selects de filtros
            const filterForm = document.getElementById('filterForm');

            // Segundo filtro (proveedor, artículo, unidad de medida)
            document.getElementById('secondFilter').addEventListener('change', function() {
                filterForm.submit();
            });

            // Select de facturas
            document.getElementById('facturaSelect').addEventListener('change', function() {
                filterForm.submit();
            });

            // Búsqueda general
            document.getElementById('generalSearch').addEventListener('input', function() {
                // Debounce para búsqueda
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(function() {
                    filterForm.submit();
                }, 500);
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

        function confirmDeleteGrouped(articuloId, count) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Esto eliminará ${count} registro${count > 1 ? 's' : ''} de este artículo. No podrás revertir esto!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar grupo!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-grouped-form-' + articuloId).submit();
                }
            });
        }

        function showGroupedDetails(articuloId) {
            // Aquí puedes implementar la lógica para mostrar detalles de los registros agrupados
            // Por ejemplo, abrir un modal con todos los registros de ese artículo
            console.log('Mostrar detalles del artículo:', articuloId);
        }
    </script>

    <!-- Agregar jQuery y Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

    <script>
        function initializeDatePicker() {
            // Verificar si el elemento existe
            if ($('#dateRangePicker').length === 0) {
                return;
            }

            // Verificar si ya está inicializado (usando jQuery data)
            if ($('#dateRangePicker').data('daterangepicker')) {
                // Ya inicializado, solo actualizar el valor si hay fechas
                var fechaDesde = $('#fecha_desde').val();
                var fechaHasta = $('#fecha_hasta').val();
                if (fechaDesde && fechaHasta) {
                    $('#dateRangePicker').val(fechaDesde + ' - ' + fechaHasta);
                }
                return;
            }

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
                    'Últimos 9 meses': [moment().subtract(9, 'months').startOf('month'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            // Evento cuando se aplica una selección de fecha
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD'));
                $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
                // Auto-submit the form
                $('#filterForm').submit();
            });

            // Evento cuando se cancela la selección
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
        }

        $(document).ready(function() {
            // Check filterType on page load and initialize datepicker if needed
            var filterType = $('#filterType').val();
            if (filterType === 'fecha' || filterType === 'fecha_creacion') {
                $('#dateRangeFilter').show();
                initializeDatePicker();
            }

            // Re-initialize when filterType changes to fecha or fecha_creacion
            if ($('#filterType').length > 0) {
                $('#filterType').on('change', function() {
                    var newFilterType = $(this).val();
                    if (newFilterType === 'fecha' || newFilterType === 'fecha_creacion') {
                        $('#dateRangeFilter').show();
                        initializeDatePicker();
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</x-app-layout>
@endcan




