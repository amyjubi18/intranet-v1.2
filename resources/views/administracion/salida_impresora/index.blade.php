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

    <!-- Filtros y controles -->
    <form method="GET" action="{{ route('administracion.salida_impresora.index') }}" id="filterForm">
        <div class="px-6 mb-6">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Botón para mostrar filtros avanzados -->
                        <button type="button" onclick="toggleAdvancedFilters()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            Filtros Avanzados
                        </button>

                        <!-- Filtro principal - Tipo de campo (oculto por defecto) -->
                        <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('filterType') ? 'bg-blue-100 border-blue-500' : '' }}" style="display:none;">
                            <option value="">Seleccionar filtro</option>
                            <option value="fecha" {{ request('filterType') == 'fecha' ? 'selected' : '' }}>Fecha de Salida</option>
                            <option value="fecha_ingreso" {{ request('filterType') == 'fecha_ingreso' ? 'selected' : '' }}>Fecha de Ingreso</option>
                            <option value="cartucho_id" {{ request('filterType') == 'cartucho_id' ? 'selected' : '' }}>Consumible</option>
                            <option value="colore_id" {{ request('filterType') == 'colore_id' ? 'selected' : '' }}>Color</option>
                            <option value="impresora_id" {{ request('filterType') == 'impresora_id' ? 'selected' : '' }}>Impresora</option>
                            <option value="fabricante_id" {{ request('filterType') == 'fabricante_id' ? 'selected' : '' }}>Tipo consumible</option>
                            <option value="gerencia_id" {{ request('filterType') == 'gerencia_id' ? 'selected' : '' }}>Gerencia</option>
                            <option value="area_id" {{ request('filterType') == 'area_id' ? 'selected' : '' }}>Área</option>
                            <option value="busqueda" {{ request('filterType') == 'busqueda' ? 'selected' : '' }}>Búsqueda General</option>
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
                                        <option value="{{ $impresora->id }}" {{ request('secondFilter') == $impresora->id ? 'selected' : '' }}>{{ $impresora->impresora }}</option>
                                    @endforeach
                                @elseif(request('filterType') == 'gerencia_id')
                                    @foreach($gerencias as $gerencia)
                                        <option value="{{ $gerencia->id }}" {{ request('secondFilter') == $gerencia->id ? 'selected' : '' }}>{{ $gerencia->gerencia }}</option>
                                    @endforeach
                                @elseif(request('filterType') == 'area_id')
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('secondFilter') == $area->id ? 'selected' : '' }}>{{ $area->area }}</option>
                                    @endforeach
                                @elseif(request('filterType') == 'fabricante_id')
                                    @foreach($fabricantes as $fabricante)
                                        <option value="{{ $fabricante->id }}" {{ request('secondFilter') == $fabricante->id ? 'selected' : '' }}>{{ $fabricante->fabricante }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <!-- Input de fecha de salida (oculto por defecto) -->
                            <div id="dateRangeFilter" style="{{ request('filterType') == 'fecha' ? '' : 'display:none;' }}">
                                <input type="text" id="dateRangePicker" name="fecha_rango" class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ (request('fecha_desde') && request('fecha_hasta')) ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('fecha_desde') && request('fecha_hasta') ? request('fecha_desde') . ' - ' . request('fecha_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                                <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                                <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                            </div>

                            <!-- Input de fecha de ingreso (oculto por defecto) -->
                            <div id="dateIngresoRangeFilter" style="{{ request('filterType') == 'fecha_ingreso' ? '' : 'display:none;' }}">
                                <input type="text" id="dateIngresoRangePicker" name="fecha_ingreso_rango" class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ (request('fecha_ingreso_desde') && request('fecha_ingreso_hasta')) ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('fecha_ingreso_desde') && request('fecha_ingreso_hasta') ? request('fecha_ingreso_desde') . ' - ' . request('fecha_ingreso_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                                <input type="hidden" name="fecha_ingreso_desde" id="fecha_ingreso_desde" value="{{ request('fecha_ingreso_desde') }}">
                                <input type="hidden" name="fecha_ingreso_hasta" id="fecha_ingreso_hasta" value="{{ request('fecha_ingreso_hasta') }}">
                            </div>

                            <!-- Input de búsqueda general (oculto por defecto) -->
                            <input type="text" id="generalSearch" name="busqueda" placeholder="Buscar en todos los campos..." class="bg-white border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ request('busqueda') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('busqueda') }}" style="{{ request('filterType') == 'busqueda' ? '' : 'display:none;' }}">
                        </div>

                        <!-- Botón para limpiar filtros -->
                        <a href="{{ route('administracion.salida_impresora.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            🗑️ Limpiar
                        </a>
                    </div>

                    <div class="flex items-center gap-3">
                        <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                            <option value="">📤 Exportar</option>
                            <option value="{{ route('administracion.salida_impresora.export.excel', request()->all()) }}">Exportar a Excel</option>
                            <option value="{{ route('administracion.salida_impresora.export.pdf', request()->all()) }}">Exportar a PDF</option>
                        </select>
                        @can('salidaimpre_create')
                        <a href="{{ route('administracion.salida_impresora.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            <span>➕</span>
                            Nuevo Registro
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Historial de salida -->
    <div class="px-6 mb-8">
        <div class="space-y-4">
            @foreach($salida_impresoras as $item)
            <!-- Registro -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
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
