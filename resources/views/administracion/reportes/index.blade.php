@canany(['inventarioimpre_index', 'inventarioofic_index'])
<x-app-layout>

@section('title', 'Reportes de Inventario y Consumo')
@section('subtitle', 'Egresos de consumibles')

    <!-- Flatpickr CDN -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Estadísticas principales -->
    <div class="px-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total de tóner disponible -->
            <div class="glass rounded-xl p-6 stat-card fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Total Consumible Disponible</p>
                        @php
                            $totalToner = \App\Models\AlmacenamientoImpresora::sum('unidades_disponibles');
                        @endphp
                        <p class="text-2xl font-bold">{{ $totalToner }}</p>
                        <p class="text-xs text-green-500">Unidades en almacenamiento</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500"><rect x="2" y="3" width="20" height="14" rx="2" ry="2" /> <line x1="8" y1="21" x2="16" y2="21" /> <line x1="12" y1="17" x2="12" y2="21" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="glass rounded-xl p-6 stat-card fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Materiales de Oficina</p>
                        <p class="text-2xl font-bold">{{ $totalItemsDisponibles ?? 2834 }}</p>
                        <p class="text-xs text-blue-500">Unidades disponibles</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /> <polyline points="14,2 14,8 20,8" /> <line x1="16" y1="13" x2="8" y2="13" /> <line x1="16" y1="17" x2="8" y2="17" /> <polyline points="10,9 9,9 8,9" />
                        </svg>
                    </div>
                </div>
            </div><!-- Stock bajo -->
            <div class="glass rounded-xl p-6 stat-card fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Stock Bajo</p>
                        <p class="text-2xl font-bold text-red-500">{{ $stockBajo ?? 23 }}</p>
                        <p class="text-xs text-red-500">Requiere atención</p>
                    </div>
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /> <line x1="12" y1="9" x2="12" y2="13" /> <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Movimientos del mes -->
            <div class="glass rounded-xl p-6 stat-card fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-400">Movimientos</p>
                        <p class="text-2xl font-bold">{{ $activitiesThisMonth }}</p>
                        <p class="text-xs text-blue-500">Este mes</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-yellow-500"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18" /> <polyline points="17,6 23,6 23,12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    <div class="mt-6 mb-6 ">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfico de consumibles -->
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Consumibles Más Utilizados</h3>
                    <div class="flex gap-2"><button onclick="toggleChartType('consumibles')" class="btn btn-secondary btn-sm" id="consumiblesToggle" title="Alternar tipo de gráfico"> 🍩 </button> <button onclick="exportChart('consumibles')" class="btn btn-secondary btn-sm">
                        <svg width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /> <polyline points="7,10 12,15 17,10" /> <line x1="12" y1="15" x2="12" y2="3" />
                        </svg> Exportar </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="consumiblesChart"></canvas>
                </div>
            </div><!-- Gráfico de materiales -->
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Materiales Más Consumidos</h3>
                    <div class="flex gap-2"><button onclick="toggleChartType('materiales')" class="btn btn-secondary btn-sm" id="materialesToggle" title="Alternar tipo de gráfico"> 📊 </button> <button onclick="exportChart('materiales')" class="btn btn-secondary btn-sm">
                        <svg width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /> <polyline points="7,10 12,15 17,10" /> <line x1="12" y1="15" x2="12" y2="3" />
                        </svg> Exportar </button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="materialesChart"></canvas>
                </div>
            </div>
        </div>
    </div><!-- Alertas de stock bajo y acciones rápidas -->
    <div class=" mb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Alertas -->
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <svg width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /> <line x1="12" y1="9" x2="12" y2="13" /> <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg> Alertas de Stock Bajo</h3><span class="badge badge-danger text-red-900 bg-red-100 p-1 rounded-lg">{{ $stockBajo ?? 0 }} alertas</span>
                </div>
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($lowStockItems ?? [] as $item)
                    <div class="alert bg-red-100 text-red-900 p-2 text-bold rounded-lg {{ $item['color'] === 'bg-red-100' ? 'alert-danger' : 'alert-warning' }}">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /> <line x1="12" y1="8" x2="12" y2="12" /> <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div class="flex-1">
                            <p class="font-medium">{{ $item['nombre'] }}</p>
                            <p class="text-xs opacity-75">Stock: {{ $item['stock'] }} unidades ({{ $item['tipo'] === 'almacenamiento' ? 'Consumible' : 'Artículo' }})</p>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-success bg-green-100 text-green-800 rounded-lg p-2">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20,6 9,17 4,12" />
                        </svg>
                        <div class="flex-1 ">
                            <p class="font-bold">¡Excelente!</p>
                            <p class="text-xs opacity-75">Todos los productos están por encima del stock mínimo</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div><!-- Acciones rápidas -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3" /> <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                    </svg> Acciones Rápidas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('administracion.reportes.export.pdf', request()->all()) }}" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /> <polyline points="14,2 14,8 20,8" /> <line x1="16" y1="13" x2="8" y2="13" /> <line x1="16" y1="17" x2="8" y2="17" /> <polyline points="10,9 9,9 8,9" />
                        </svg> Reporte de Inventario
                    </a>
                    <a href="{{ route('gtic.activity-log.export.pdf') }}" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18" /> <polyline points="17,6 23,6 23,12" />
                        </svg> Reporte de Movimientos
                    </a>
                    <button onclick="generateConsumptionReport()" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10" /> <path d="M12 20V4" /> <path d="M6 20v-6" />
                        </svg> Análisis de Consumo
                    </button>
                    <button onclick="generateReport('suppliers')" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /> <circle cx="9" cy="7" r="4" /> <path d="M22 21v-2a4 4 0 0 0-3-3.87" /> <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg> Reporte de Proveedores
                    </button>

                    <button onclick="generateReport('lowstock')" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /> <line x1="12" y1="9" x2="12" y2="13" /> <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg> Reporte Stock Crítico
                    </button>
                    <button onclick="generateReport('audit')" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4" /> <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg> Auditoría de Inventario
                    </button>
                    <button onclick="exportPredictiveAnalysis()" class="btn btn-secondary py-3 hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83" /> <path d="M22 12A10 10 0 0 0 12 2v10z" />
                        </svg> Análisis Predictivo
                    </button>

                    <button onclick="generateReport('maintenance')" class="btn btn-secondary hover:bg-blue-900 hover:text-white w-full justify-start">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3" /> <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                        </svg> Plan de Mantenimiento
                    </button>
                    <select id="areaGerenciaSelect" class="w-full px-3 py-3 font-semibold border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
                        <option class="font-bold" value="">Reporte por Gerencia y Area</option>
                        <option value="all">Todas las Gerencias</option>
                        @foreach (\App\Models\Gerencia::with('area')->get() as $gerencia)
                            <option value="{{ $gerencia->id }}">
                                {{ $gerencia->gerencia }} ({{ $gerencia->area->area ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    <!-- Registro de Actividades Recientes -->
    <div class="mt-6 mb-6">
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v5h5" /> <path d="M3.05 13A9 9 0 1 0 6 5.3L3 8" /> <path d="M12 7v5l4 2" />
                    </svg> Registro de Actividades Recientes</h3>
                <div class="flex gap-2">
                    <a href="{{ route('gtic.activity-log.export.pdf') }}" class="btn btn-secondary btn-sm hover:bg-blue-900 hover:text-white justify-start">
                        <svg width="14" height="14" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /> <polyline points="7,10 12,15 17,10" /> <line x1="12" y1="15" x2="12" y2="3" />
                        </svg> Exportar
                    </a>
                    <a href="{{ route('gtic.activity-log.index') }}" class="btn btn-secondary btn-sm hover:bg-blue-900 hover:text-white justify-start">Ver Todo</a>
                </div>
            </div>
            <!-- Filtros para actividades -->
            <details class="mb-4">
                <summary class="cursor-pointer text-lg rounded-lg font-semibold p-2 mb-2 bg-blue-900 text-white flex items-center gap-2">
                    <svg id="filterIcon" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 14l5-5 5 5z"/>
                    </svg> Filtros de Actividades
                </summary>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Usuario</label>
                        <select id="activityUser" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos los usuarios</option>
                            @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ request('activity_user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Acción</label>
                        <select id="activityAction" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas las acciones</option>
                            <option value="created" {{ request('activity_action') == 'created' ? 'selected' : '' }}>Registro</option>
                            <option value="updated" {{ request('activity_action') == 'updated' ? 'selected' : '' }}>Actualización</option>
                            <option value="deleted" {{ request('activity_action') == 'deleted' ? 'selected' : '' }}>Eliminación</option>
                        </select>
                    </div>

                </div>
                <div class="flex gap-2 mt-4">
                    <button onclick="applyActivityFilters()" class="btn btn-primary">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /> <path d="M21 21l-4.35-4.35" />
                        </svg> Aplicar Filtros
                    </button>
                    <button onclick="clearActivityFilters()" class="btn btn-secondary">
                        <svg width="16" height="16" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18" /> <line x1="6" y1="6" x2="18" y2="18" />
                        </svg> Limpiar
                    </button>
                </div>
            </details>
            <div class="table-container overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-slate-50/50 sticky top-0">
                        <tr>
                            <th class="text-left p-3 font-medium whitespace-nowrap">Fecha</th>
                            <th class="text-left p-3 font-medium whitespace-nowrap">Usuario</th>
                            <th class="text-left p-3 font-medium whitespace-nowrap">Acción</th>
                            <th class="text-left p-3 font-medium whitespace-nowrap">Modelo</th>
                            <th class="text-left p-3 font-medium">Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities ?? [] as $activity)
                        <tr class="table-row border-b border-slate-100">
                            <td class="p-3">{{ $activity->created_at->format('d/m/Y h:i:s A') }}</td>
                            <td class="p-3 text-bold text-blue-900">
                               <b class="text-blue-900">{{ $activity->causer ? $activity->causer->username : 'Sistema' }}</b>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($activity->event == 'created') bg-green-100 text-green-800
                                    @elseif($activity->event == 'updated') bg-blue-100 text-blue-800
                                    @elseif($activity->event == 'deleted') bg-red-100 text-red-800
                                    @elseif($activity->event == 'exported') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($activity->event == 'created') Registro
                                    @elseif($activity->event == 'updated') Actualización
                                    @elseif($activity->event == 'deleted') Eliminación
                                    @elseif($activity->event == 'exported') Exportación
                                    @else {{ ucfirst($activity->event) }}
                                    @endif
                                </span>
                            </td>
                            <td class="p-3">{{ class_basename($activity->subject_type ?? 'N/A') }}</td>
                            <td class="p-3">
                                @if($activity->event == 'created')
                                   <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->username : 'Sistema' }}</b> hizo un registro en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @elseif($activity->event == 'updated')
                                    <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->username : 'Sistema' }} </b> realizó un cambio en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @elseif($activity->event == 'deleted')
                                    <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->username : 'Sistema' }}</b> hizo una eliminación en  {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @else
                                    {{ $activity->properties['url'] ?? request()->getPathInfo() }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr class="table-row border-b border-slate-100">
                            <td colspan="5" class="p-3 text-center text-slate-500">No hay actividades recientes</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Paginador -->
            <div class="mt-4 flex justify-center">
                {{ $recentActivities->links() }}
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Verificar que Chart.js esté disponible antes de ejecutar
        function waitForChart(callback, maxAttempts = 50) {
            let attempts = 0;
            const check = setInterval(function() {
                if (typeof Chart !== 'undefined') {
                    clearInterval(check);
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(check);
                    console.error('Chart.js no se pudo cargar después de ' + maxAttempts + ' intentos');
                }
                attempts++;
            }, 100);
        }

        function showLocationReport() {
            const container = document.getElementById('areaGerenciaSelectContainer');
            if(container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        function exportPredictiveAnalysis(){
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'pt', 'a4');
            pdf.setFontSize(18);
            pdf.text("Análisis Predictivo - Consumibles y Materiales", 40, 40);

            // Guardar tipos actuales para restaurar luego
            const originalConsumiblesType = consumiblesChart.config.type;
            const originalMaterialesType = materialesChart.config.type;

            // Cambiar temporalmente a gráfico de barras
            consumiblesChart.config.type = 'bar';
            consumiblesChart.update();

            materialesChart.config.type = 'bar';
            materialesChart.update();

            Promise.all([
                html2canvas(document.getElementById('consumiblesChart')),
                html2canvas(document.getElementById('materialesChart'))
            ]).then(canvases => {
                const consumiblesImg = canvases[0].toDataURL('image/png');
                const materialesImg = canvases[1].toDataURL('image/png');

                const pageWidth = pdf.internal.pageSize.getWidth();
                let y = 70;

                // Consumibles chart
                pdf.setFontSize(14);
                pdf.text('Consumibles Más Utilizados', 40, y);
                y += 10;
                pdf.addImage(consumiblesImg, 'PNG', 40, y, pageWidth - 180, 180);
                y += 190;

                // Materiales chart
                pdf.text('Materiales Más Consumidos', 40, y);
                y += 10;
                pdf.addImage(materialesImg, 'PNG', 40, y, pageWidth - 180, 180);

                pdf.save('Analisis_predictivo.pdf');
                showToast('PDF exportado correctamente', 'success');

                // Restaurar tipos originales y actualizar gráficos
                consumiblesChart.config.type = originalConsumiblesType;
                consumiblesChart.update();

                materialesChart.config.type = originalMaterialesType;
                materialesChart.update();
            }).catch(() => {
                showToast('Error al exportar el PDF', 'error');

                // Restaurar tipos aun si hay error
                consumiblesChart.config.type = originalConsumiblesType;
                consumiblesChart.update();

                materialesChart.config.type = originalMaterialesType;
                materialesChart.update();
            });
        }

        // Variables globales para los gráficos
        let consumiblesChart = null;
        let materialesChart = null;
        let currentChartType = 'pie'; // 'pie' o 'bar'
        let currentChartTypeMateriales = 'pie'; // 'pie' o 'bar'

        // Función para alternar tipo de gráfico
        function toggleChartType(chartId) {
            if (chartId === 'consumibles') {
                currentChartType = currentChartType === 'pie' ? 'bar' : 'pie';
                renderConsumiblesChart();
                // Cambiar el emoji del botón
                const button = document.getElementById('consumiblesToggle');
                button.textContent = currentChartType === 'pie' ? '🍩' : '📊';
            } else if (chartId === 'materiales') {
                currentChartTypeMateriales = currentChartTypeMateriales === 'pie' ? 'bar' : 'pie';
                renderMaterialesChart();
                // Cambiar el emoji del botón
                const button = document.getElementById('materialesToggle');
                button.textContent = currentChartTypeMateriales === 'pie' ? '📊' : '📈';
            }
        }

        // Función para renderizar el gráfico de consumibles
        function renderConsumiblesChart() {
            const ctx = document.getElementById('consumiblesChart').getContext('2d');

            // Destruir gráfico anterior si existe
            if (consumiblesChart) {
                consumiblesChart.destroy();
            }

            // Datos de PHP
            const rawData = @json($topConsumibles ?? []);
            const data = Array.isArray(rawData) ? rawData : [];

            console.log('Top Consumibles data:', data);

            if (data.length === 0) {
                // Mostrar mensaje si no hay datos
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos de consumibles disponibles', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const labels = data.map(item => item.name);
            const values = data.map(item => item.value);

            // Verificar que los datos no sean todos cero o vacío
            const totalValue = values.reduce((a, b) => a + b, 0);
            if (totalValue === 0) {
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay movimientos de consumibles registrados', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            // Configuración del gráfico
            const config = {
                type: currentChartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Salidas',
                        data: values,
                        backgroundColor: currentChartType === 'pie' ? [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ] : '#36A2EB',
                        borderColor: currentChartType === 'pie' ? [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ] : '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: currentChartType === 'pie' ? 'right' : 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' unidades';
                                }
                            }
                        }
                    },
                    scales: currentChartType === 'bar' ? {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Unidades Salidas'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Consumibles'
                            }
                        }
                    } : {}
                }
            };

            consumiblesChart = new Chart(ctx, config);
        }

        // Función para renderizar el gráfico de materiales
        function renderMaterialesChart() {
            const ctx = document.getElementById('materialesChart').getContext('2d');

            // Destruir gráfico anterior si existe
            if (materialesChart) {
                materialesChart.destroy();
            }

            // Datos de PHP
            const rawData = @json($topMateriales ?? []);
            const data = Array.isArray(rawData) ? rawData : [];

            console.log('Top Materiales data:', data);

            if (data.length === 0) {
                // Mostrar mensaje si no hay datos
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos de materiales disponibles', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const labels = data.map(item => item.name);
            const values = data.map(item => item.value);

            // Verificar que los datos no sean todos cero o vacío
            const totalValue = values.reduce((a, b) => a + b, 0);
            if (totalValue === 0) {
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay movimientos de materiales registrados', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            // Configuración del gráfico
            const config = {
                type: currentChartTypeMateriales,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Salidas',
                        data: values,
                        backgroundColor: currentChartTypeMateriales === 'pie' ? [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ] : '#4BC0C0',
                        borderColor: currentChartTypeMateriales === 'pie' ? [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                        ] : '#4BC0C0',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: currentChartTypeMateriales === 'pie' ? 'right' : 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.parsed + ' unidades';
                                }
                            }
                        }
                    },
                    scales: currentChartTypeMateriales === 'bar' ? {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Unidades Salidas'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Materiales'
                            }
                        }
                    } : {}
                }
            };

            materialesChart = new Chart(ctx, config);
        }

        // Función para exportar gráfico
        function exportChart(chartId) {
            if (chartId === 'consumibles') {
                // Datos de PHP
                const data = @json($topConsumibles ?? []);

                if (data.length === 0) {
                    alert('No hay datos disponibles para exportar');
                    return;
                }

                // Capturar el canvas del gráfico
                const canvas = document.getElementById('consumiblesChart');
                html2canvas(canvas).then(canvasImage => {
                    // Crear PDF
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF();

                    // Agregar título
                    pdf.setFontSize(18);
                    pdf.text('Consumibles Más Utilizados', 20, 20);

                    // Agregar fecha
                    const date = new Date().toLocaleDateString();
                    pdf.setFontSize(12);
                    pdf.text(`Fecha: ${date}`, 20, 30);

                    // Agregar imagen del gráfico
                    const imgData = canvasImage.toDataURL('image/png');
                    const imgWidth = 180;
                    const imgHeight = (canvasImage.height * imgWidth) / canvasImage.width;
                    pdf.addImage(imgData, 'PNG', 15, 40, imgWidth, imgHeight);

                    // Agregar datos en tabla
                    let yPosition = 50 + imgHeight;
                    pdf.setFontSize(14);
                    pdf.text('Datos:', 20, yPosition);
                    yPosition += 10;

                    pdf.setFontSize(10);
                    data.forEach(item => {
                        pdf.text(`${item.name}: ${item.value} unidades`, 20, yPosition);
                        yPosition += 8;
                    });

                    // Descargar PDF
                    pdf.save('consumibles-mas-utilizados.pdf');
                });
            } else if (chartId === 'materiales') {
                // Datos de PHP
                const data = @json($topMateriales ?? []);

                if (data.length === 0) {
                    alert('No hay datos disponibles para exportar');
                    return;
                }

                // Capturar el canvas del gráfico
                const canvas = document.getElementById('materialesChart');
                html2canvas(canvas).then(canvasImage => {
                    // Crear PDF
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF();

                    // Agregar título
                    pdf.setFontSize(18);
                    pdf.text('Materiales Más Consumidos', 20, 20);

                    // Agregar fecha
                    const date = new Date().toLocaleDateString();
                    pdf.setFontSize(12);
                    pdf.text(`Fecha: ${date}`, 20, 30);

                    // Agregar imagen del gráfico
                    const imgData = canvasImage.toDataURL('image/png');
                    const imgWidth = 180;
                    const imgHeight = (canvasImage.height * imgWidth) / canvasImage.width;
                    pdf.addImage(imgData, 'PNG', 15, 40, imgWidth, imgHeight);

                    // Agregar datos en tabla
                    let yPosition = 50 + imgHeight;
                    pdf.setFontSize(14);
                    pdf.text('Datos:', 20, yPosition);
                    yPosition += 10;

                    pdf.setFontSize(10);
                    data.forEach(item => {
                        pdf.text(`${item.name}: ${item.value} unidades`, 20, yPosition);
                        yPosition += 8;
                    });

                    // Descargar PDF
                    pdf.save('materiales-mas-consumidos.pdf');
                });
            }
        }

        // Función para aplicar filtros de actividades
        function applyActivityFilters() {
            const user = document.getElementById('activityUser').value;
            const action = document.getElementById('activityAction').value;

            // Construir URL con parámetros
            const params = new URLSearchParams(window.location.search);

            if (user) params.set('activity_user', user);
            else params.delete('activity_user');
            if (action) params.set('activity_action', action);
            else params.delete('activity_action');

            // Recargar la página con los filtros
            window.location.search = params.toString();
        }

        // Función para limpiar filtros de actividades
        function clearActivityFilters() {
            document.getElementById('activityUser').value = '';
            document.getElementById('activityAction').value = '';

            // Recargar la página sin parámetros de filtro de actividades
            const params = new URLSearchParams(window.location.search);
            params.delete('activity_user');
            params.delete('activity_action');

            window.location.search = params.toString();
        }

        // Función para generar reporte de consumo
        function generateConsumptionReport() {
            window.open('{{ route("administracion.reportes.consumption.pdf") }}', '_blank');
        }

        // Función para generar reportes
        function generateReport(type) {
            const reportNames = {
                'suppliers': 'Reporte de Proveedores',
                'history': 'Historial de Acciones',
                'lowstock': 'Reporte Stock Crítico',
                'audit': 'Auditoría de Inventario',
                'maintenance': 'Plan de Mantenimiento'
            };

            if (type === 'suppliers') {
                window.open('{{ route("administracion.reportes.suppliers.pdf") }}', '_blank');
            } else if (type === 'lowstock') {
                window.open('{{ route("administracion.reportes.lowstock.pdf") }}', '_blank');
            } else if (type === 'audit') {
                window.open('{{ route("administracion.reportes.audit.pdf") }}', '_blank');
            } else {
                alert('Función ' + reportNames[type] + ' próximamente disponible');
            }
        }

        // Inicializar gráficos cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            waitForChart(function() {
                renderConsumiblesChart();
                renderMaterialesChart();
            });

            // Inicializar Flatpickr para los filtros de fecha
            flatpickr('#activityDateRange', {
                mode: 'range',
                dateFormat: 'd/m/Y',
                locale: 'es',
                rangeSeparator: ' to '
            });

        // Cambiar el icono del filtro cuando se abre/cierra
        const details = document.querySelector('details');
        const filterIcon = document.getElementById('filterIcon');
        details.addEventListener('toggle', function() {
            if (details.open) {
                filterIcon.innerHTML = '<path d="M7 10l5 5 5-5z"/>';
            } else {
                filterIcon.innerHTML = '<path d="M7 14l5-5 5 5z"/>';
            }
        });

        // Event handler for Gerencia-Area report select
        const areaGerenciaSelect = document.getElementById('areaGerenciaSelect');
        if (areaGerenciaSelect) {
            areaGerenciaSelect.addEventListener('change', function() {
                const gerenciaId = this.value;
                let url = '{{ route("administracion.reportes.gerencia_area.pdf") }}';
                // Si es "all" o vacío, no envía parámetro para obtener todas las gerencias
                if (gerenciaId && gerenciaId !== 'all') {
                    url += '?gerencia_id=' + encodeURIComponent(gerenciaId);
                }
                window.open(url, '_blank');
                // Reset select to default option after open
                this.value = '';
            });
        }
    });
     </script>
</x-app-layout>
@endcan
