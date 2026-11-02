@can('inventarioofic_index')
<style>
    .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
    .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
    .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }
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
            <p class="text-sm text-blue-900 font-semibold">Última Fecha de Ingreso</p>
            
        </div>

        <!-- Items de Oficina -->
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                    <span class="text-green-400" style="font-size: 24px;">📦</span>
                </div>
                <span class="text-lg px-2 py-1 rounded-full bg-green-500/20 text-green-400">{{ $totalItemsRegistrados ?? 0 }}</span>
            </div>
            <h3 class="text-2xl font-bold mb-1">{{ $totalItemsRegistrados ?? 0 }}</h3>
            <p class="text-sm text-blue-900 font-semibold">Cantidad de ingresos</p>
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
                    <button type="button" onclick="toggleAdvancedFilters()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">Filtros Avanzados</button>
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
                    <div id="secondFilterContainer" class="relative z-50">
                        <select id="secondFilter" name="secondFilter" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" {{ request('filterType') ? '' : 'disabled' }}>
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
                    <div id="dateRangeFilter" style="{{ (request('filterType') == 'fecha' || request('filterType') == 'fecha_creacion') ? '' : 'display:none;' }}">
                        <input type="text" id="dateRangePicker" name="fecha_rango" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ request('fecha_desde') && request('fecha_hasta') ? request('fecha_desde') . ' - ' . request('fecha_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                        <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                        <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                    </div>
                    <input type="text" id="textFilter" name="n_factura" placeholder="Buscar..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ request('n_factura') }}" style="display:none;">
                    <select id="facturaSelect" name="secondFilter" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" style="display:none;">
                        <option value="">Seleccionar factura</option>
                        @foreach($facturas ?? [] as $factura)
                        <option value="{{ $factura }}" {{ request('secondFilter') == $factura ? 'selected' : '' }}>{{ $factura }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="generalSearch" name="busqueda" placeholder="Buscar en todos los campos..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50" value="{{ request('busqueda') }}" style="display:none;">
                    <a href="{{ route('administracion.inventario_oficinas.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">🗑️ Limpiar</a>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                        <option value="">📤 Exportar</option>
                        <option value="{{ route('administracion.inventario_oficinas.export.excel', request()->all()) }}">Exportar a Excel</option>
                        <option value="{{ route('administracion.inventario_oficinas.export.pdf', request()->all()) }}">Exportar a PDF</option>
                    </select>
                    @can('inventarioofic_destroy')
                    <button type="button" onclick="confirmDeleteSelected()" id="deleteSelectedBtn" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-600 text-white hover:bg-red-700 active:translate-y-0.5 text-sm" disabled><span>🗑️</span> Eliminar Seleccionados</button>
                    <button type="button" onclick="confirmDeleteAll()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-800 text-white hover:bg-red-900 active:translate-y-0.5 text-sm"><span>⚠️</span> Eliminar Todo</button>
                    @endcan
                    @can('inventarioofic_create')
                    <a href="/administracion/inventario_oficinas/create" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"><span>➕</span> Nuevo Registro</a>
                    @endcan
                </div>
        </div>
        </div>
        </div>
        
</form>

<!-- Formularios ocultos para eliminación masiva -->
<form id="deleteMultipleForm" method="POST" action="{{ route('administracion.inventario_oficinas.destroyMultiple') }}" style="display:none;">
    @csrf
    <input type="hidden" name="ids" id="multipleDeleteIds">
    <input type="hidden" name="password" id="multipleDeletePassword">
</form>
<form id="deleteAllForm" method="POST" action="{{ route('administracion.inventario_oficinas.destroyAll') }}" style="display:none;">
    @csrf
    <input type="hidden" name="password" id="deleteAllPassword">
</form>

<!-- Historial de inventario -->
<div class="px-6 mb-8">
    @if(isset($mostrarTodosRegistros) && $mostrarTodosRegistros && ($inventario_oficinas ?? [])->count() > 0)
        @php
            $primerItem = $inventario_oficinas->first();
            $nombreArticulo = 'Artículo Desconocido';
            if ($primerItem) {
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

    @foreach($inventario_oficinas ?? [] as $item)
        @php
            $articuloModel = null;
            if (isset($item->articulos) && is_object($item->articulos)) {
                $articuloModel = $item->articulos;
            } elseif (isset($articulos) && $item->articulo_id) {
                $articuloModel = $articulos->find($item->articulo_id);
            }
            $hasLowStock = $articuloModel && $item->unidades_disponibles < $articuloModel->stock_min;
            $itemsToShow = isset($item->original_items) ? $item->original_items : collect([$item]);
        @endphp

        @if(!isset($mostrarTodosRegistros) || !$mostrarTodosRegistros)
        <div class="bg-gradient-to-r {{ $hasLowStock ? 'from-red-900 to-red-800' : 'from-indigo-900 to-blue-800' }} text-white rounded-2xl p-4 shadow-lg relative mb-2">
            @if($hasLowStock)
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">⚠️ Stock Bajo</div>
            @endif
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 rounded-xl bg-white/20 flex items-center justify-center">
                    <span style="font-size: 24px;">📦</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold">{{ $articuloModel ? $articuloModel->articulo : 'Artículo Desconocido' }}</h3>
                    <p class="text-blue-100 text-xs">Registros Agrupados: <span class="font-semibold">{{ $itemsToShow->count() }}</span></p>
                </div>
            </div>
        </div>
        @endif

        @foreach($itemsToShow as $singleItem)
        <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200 {{ isset($mostrarTodosRegistros) && $mostrarTodosRegistros ? '' : 'ml-8' }} mb-4">
            <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                @can('inventarioofic_destroy')
                <div class="flex-shrink-0 pt-2">
                    <input type="checkbox" class="record-checkbox w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" value="{{ $singleItem->id }}" onchange="updateDeleteSelectedBtn()">
                </div>
                @endcan
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-blue-900 font-semibold mb-1">Última Fecha de Factura</p>
                        <p class="font-semibold">{{ \Carbon\Carbon::parse($singleItem->fecha_ingreso)->format('d/m/Y') }}</p>
                        <p class="text-base text-blue-900 font-semibold mt-2">Item #</p>
                        <p class="font-mono text-sm">{{ str_pad($singleItem->articulo_id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-blue-900 font-semibold mt-2">Artículo</p>
                        <p class="text-md font-bold">{{ $articuloModel ? str_pad($articuloModel->id, 4, '0', STR_PAD_LEFT) . ' - ' . $articuloModel->articulo : 'N/A' }}</p>
                        <p class="text-xs text-blue-900 font-semibold mb-1">Unidad de Medida</p>
                        <p class="text-sm">{{ $singleItem->unidad_medidas ? $singleItem->unidad_medidas->unidad_medida : 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-900 font-semibold mt-2">Proveedor</p>
                        <p class="text-sm">{{ $singleItem->proveedores ? $singleItem->proveedores->proveedor : 'N/A' }}</p>
                    </div>
                    <div>
                        @php
                            $totalesArticulo = isset($totalesPorArticulo[$item->articulo_id]) ? $totalesPorArticulo[$item->articulo_id] : ['unidades' => $item->unidades, 'unidades_disponibles' => $item->unidades_disponibles];
                        @endphp
                        <div class="text-right">
                            <p class="text-sm text-black font-bold mb-1">Unidades Registradas</p>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold {{ $articuloModel && $singleItem->unidades_disponibles < $articuloModel->stock_min ? 'text-red-400' : 'text-green-400' }}">{{ $singleItem->unidades_disponibles }}</span>
                                <span class="text-xs text-blue-900 font-semibold">de {{ $totalesArticulo['unidades_disponibles'] }}</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                <div class="{{ $articuloModel && $totalesArticulo['unidades_disponibles'] < $articuloModel->stock_min ? 'bg-red-500' : 'bg-green-500' }} h-2 rounded-full" style="width: {{ $totalesArticulo['unidades'] > 0 ? min(100, ($totalesArticulo['unidades_disponibles'] / $totalesArticulo['unidades']) * 100) : 0 }}%">
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <p class="text-xs text-blue-900 font-semibold mb-1">Nº de Factura</p>
                            <div class="text-xs bg-gray-100 px-2 py-1 rounded">
                                <span class="text-gray-600">{{ $singleItem->n_factura }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-center md:justify-end gap-2 mt-2">
                            @can('inventarioofic_edit')
                            <a href="{{ route('administracion.inventario_oficinas.edit', $singleItem->id) }}" class="inline-flex items-center gap-2 px-3 h-8 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs"><span>✏️</span> Editar</a>
                            @endcan
                            @can('inventarioofic_destroy')
                            <form id="delete-form-{{ $singleItem->id }}" action="{{ route('administracion.inventario_oficinas.destroy', $singleItem->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete({{ $singleItem->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs"><span>🗑️</span> Eliminar</button>
                            </form>
                            @endcan
                        </div>
                    </div>
            </div>
        </div>
        </div>
        </div>
        @endforeach
    @endforeach
</div>


@if(($inventario_oficinas ?? collect())->hasPages())
<div class="px-6 mt-8">
    <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
        {!! ($inventario_oficinas ?? collect())->links() !!}
    </div>
</div>
@endif

<script>
function updateSecondFilter() {
    const ft = document.getElementById('filterType').value;
    const sc = document.getElementById('secondFilterContainer');
    const sf = document.getElementById('secondFilter');
    const dr = document.getElementById('dateRangeFilter');
    const tf = document.getElementById('textFilter');
    const gs = document.getElementById('generalSearch');
    const fs = document.getElementById('facturaSelect');
    sc.style.display = 'none'; sf.disabled = true;
    dr.style.display = 'none'; tf.style.display = 'none';
    gs.style.display = 'none'; fs.style.display = 'none'; fs.disabled = true;
    if (ft === 'fecha' || ft === 'fecha_creacion') { dr.style.display = 'block'; }
    else if (ft === 'n_factura') { tf.style.display = 'none'; fs.style.display = 'block'; fs.disabled = false; }
    else if (ft === 'busqueda') { gs.style.display = 'block'; }
    else if (ft !== '') { sc.style.display = 'block'; sf.disabled = false; populateSecondFilter(ft); }
}

function populateSecondFilter(type) {
    const sf = document.getElementById('secondFilter');
    const existing = sf.querySelectorAll('option');
    if (existing.length > 1) return;
    sf.innerHTML = '<option value="">Seleccionar...</option>';
    let opts = [];
    if (type === 'proveedore_id') opts = @json(($proveedores ?? collect())->map(fn($i) => ['id' => $i->id, 'name' => $i->proveedor]));
    else if (type === 'articulo_id') { const d = @json($articulos ?? []); opts = d.map(i => ({ id: i.id, name: String(i.id).padStart(4,'0')+' - '+i.articulo })); }
    else if (type === 'unidad_medida_id') opts = @json(($unidad_medidas ?? collect())->map(fn($i) => ['id' => $i->id, 'name' => $i->unidad_medida]));
    opts.forEach(o => { const el = document.createElement('option'); el.value = o.id; el.textContent = o.name; sf.appendChild(el); });
}

function toggleAdvancedFilters() {
    const f = document.getElementById('filterType');
    if (f.style.display === 'none' || f.style.display === '') { f.style.display = 'inline-block'; }
    else { f.style.display = 'none'; f.value = ''; updateSecondFilter(); document.getElementById('filterForm').submit(); }
}

document.addEventListener('DOMContentLoaded', function() {
    updateSecondFilter();
    const ff = document.getElementById('filterForm');
    document.getElementById('secondFilter')?.addEventListener('change', () => ff.submit());
    document.getElementById('facturaSelect')?.addEventListener('change', () => ff.submit());
    document.getElementById('generalSearch')?.addEventListener('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => ff.submit(), 500);
    });
});

function confirmDelete(id) {
    Swal.fire({ title: '¿Estás seguro?', text: "No podrás revertir esto!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Sí, eliminar!', cancelButtonText: 'Cancelar' })
    .then((r) => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
}

function updateDeleteSelectedBtn() {
    const ck = document.querySelectorAll('.record-checkbox:checked');
    const btn = document.getElementById('deleteSelectedBtn');
    if (btn) btn.disabled = ck.length === 0;
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
        title: '¿Estás seguro?', text: "Se eliminarán TODOS los registros.", icon: 'warning',
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
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
<script>
function initializeDatePicker() {
    if ($('#dateRangePicker').length === 0) return;
    if ($('#dateRangePicker').data('daterangepicker')) {
        var fd = $('#fecha_desde').val(), fh = $('#fecha_hasta').val();
        if (fd && fh) $('#dateRangePicker').val(fd + ' - ' + fh);
        return;
    }
    $('#dateRangePicker').daterangepicker({
        autoUpdateInput: false,
        locale: { cancelLabel: 'Limpiar', applyLabel: 'Aplicar', format: 'YYYY-MM-DD', separator: ' - ' },
        ranges: { 'Hoy': [moment(), moment()], 'Ayer': [moment().subtract(1,'days'), moment().subtract(1,'days')], 'Últimos 7 días': [moment().subtract(6,'days'), moment()], 'Últimos 30 días': [moment().subtract(29,'days'), moment()], 'Este mes': [moment().startOf('month'), moment().endOf('month')] }
    });
    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD')); $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD'));
        $('#filterForm').submit();
    });
    $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val(''); $('#fecha_desde').val(''); $('#fecha_hasta').val('');
        $('#filterForm').submit();
    });
}
$(document).ready(function() {
    var ft = $('#filterType').val();
    if (ft === 'fecha' || ft === 'fecha_creacion') { $('#dateRangeFilter').show(); initializeDatePicker(); }
    $('#filterType').on('change', function() {
        if ($(this).val() === 'fecha' || $(this).val() === 'fecha_creacion') { $('#dateRangeFilter').show(); initializeDatePicker(); }
    });
});
</script>
</x-app-layout>
@endcan
