@can('bienes_index')
<x-app-layout>
    @section('title', 'Bienes Públicos')
    @section('subtitle', 'Bienes Nacionales ')
    <style>
        .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
        .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
        .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }
    </style>

    <!-- Tarjetas superiores de estadísticas -->
    <div class="px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
            <!-- Total de Bienes -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                        <span class="text-blue-500" style="font-size: 24px;">📦</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $totalBienes ?? 0 }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Total de Bienes</p>
            </div>

            <!-- Última Fecha de Adquisición -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                        <span class="text-green-500" style="font-size: 24px;">📅</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $ultimoBien ? \Carbon\Carbon::parse($ultimoBien->fecha_adquisicion)->format('d/m/Y') : 'N/A' }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Última Fecha de Adquisición</p>
            </div>

            <!-- Bienes en Uso -->
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl grid place-items-center bg-yellow-500/20">
                        <span class="text-yellow-500" style="font-size: 24px;">✅</span>
                    </div>
                </div>
                <h3 class="text-2xl font-bold mb-1">{{ $bienesEnUso ?? 0 }}</h3>
                <p class="text-sm text-blue-900 font-semibold">Bienes en Uso</p>
            </div>
        </div>
    </div>

    <!-- Filtros y controles -->
    <form method="GET" action="{{ route('administracion.bienes_publicos.index') }}" id="filterForm">
        <div class="px-6 mb-6">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="toggleAdvancedFilters()" class="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                            Filtros Avanzados
                        </button>
                        <button type="button" onclick="startQrScanner()" class="bg-green-600 hover:bg-green-700 text-white border border-green-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/50">
                            📷 Escanear QR
                        </button>
                        <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm" style="display:none;">
                            <option value="">Seleccionar filtro</option>
                            <option value="n_interno_bien" {{ request('filterType') == 'n_interno_bien' ? 'selected' : '' }}>N° Interno</option>
                            <option value="descripcion" {{ request('filterType') == 'descripcion' ? 'selected' : '' }}>Descripción</option>
                            <option value="unidad_administrativa_id" {{ request('filterType') == 'unidad_administrativa_id' ? 'selected' : '' }}>Unidad Administrativa</option>
                            <option value="usuario_de_uso_id" {{ request('filterType') == 'usuario_de_uso_id' ? 'selected' : '' }}>Usuario de Uso</option>
                            <option value="usuario_administrativo_id" {{ request('filterType') == 'usuario_administrativo_id' ? 'selected' : '' }}>Usuario Administrativo</option>
                            <option value="estado_del_uso_id" {{ request('filterType') == 'estado_del_uso_id' ? 'selected' : '' }}>Estado del Uso</option>
                            <option value="forma_adquisicion_id" {{ request('filterType') == 'forma_adquisicion_id' ? 'selected' : '' }}>Forma de Adquisición</option>
                            <option value="marca" {{ request('filterType') == 'marca' ? 'selected' : '' }}>Marca</option>
                            <option value="modelo" {{ request('filterType') == 'modelo' ? 'selected' : '' }}>Modelo</option>
                            <option value="condicion_id" {{ request('filterType') == 'condicion_id' ? 'selected' : '' }}>Condición</option>
                            <option value="color_id" {{ request('filterType') == 'color_id' ? 'selected' : '' }}>Color</option>
                            <option value="serial" {{ request('filterType') == 'serial' ? 'selected' : '' }}>Serial</option>
                            <option value="codigo_qr" {{ request('filterType') == 'codigo_qr' ? 'selected' : '' }}>Código QR</option>
                            <option value="fecha" {{ request('filterType') == 'fecha' ? 'selected' : '' }}>Fecha de Adquisición</option>
                            <option value="busqueda" {{ request('filterType') == 'busqueda' ? 'selected' : '' }}>Búsqueda General</option>
                        </select>
                        <div id="secondFilterContainer">
                            <select id="secondFilter" onchange="applySelectFilter()" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm" {{ request('filterType') ? '' : 'disabled' }}>
                                <option value="">-----</option>
                                @if(request('filterType') == 'n_interno_bien')
                                    @foreach($n_internos ?? [] as $n_interno)
                                        <option value="{{ $n_interno }}" {{ request('secondFilter') == $n_interno ? 'selected' : '' }}>{{ $n_interno }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div id="dateRangeFilter" style="{{ request('filterType') == 'fecha' ? '' : 'display:none;' }}">
                            <input type="text" id="dateRangePicker" name="fecha_rango" class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm" placeholder="Seleccionar rango">
                            <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                            <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        </div>
                        <input type="text" id="generalSearch" name="busqueda" placeholder="Buscar..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm" value="{{ request('busqueda') }}" style="{{ request('filterType') == 'busqueda' ? '' : 'display:none;' }}">
                        <input type="text" id="textFilter" name="secondFilter" placeholder="Ingresa el valor..." class="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm" value="{{ request('secondFilter') }}" style="{{ (request('filterType') == 'serial' || request('filterType') == 'codigo_qr') ? '' : 'display:none;' }}">
                        <a href="{{ route('administracion.bienes_publicos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            🗑️ Limpiar
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <select onchange="if(this.value) window.location.href=this.value" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm">
                            <option value="">📤 Exportar</option>
                            <option value="{{ route('administracion.bienes_publicos.export.excel', request()->all()) }}">Excel</option>
                            <option value="{{ route('administracion.bienes_publicos.export.pdf', request()->all()) }}">PDF</option>
                        </select>
                        @can('bienes_create')
                        <a href="/administracion/bienes_publicos/create" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm">
                            <span>➕</span>
                            Nuevo
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Listado de bienes -->
    <div class="px-6 mb-8">
        <div class="space-y-4">
            @forelse($bienes ?? [] as $item)
                <!-- Card de bien -->
                <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200">
                    <div class="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                        <!-- Icono/Imagen del bien -->
                        <div class="flex items-center justify-center flex-shrink-0 w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500 to-blue-900">
                            <span class="text-3xl">{{ $item->getIconForDescription() }}</span>
                        </div>

                        <!-- Información principal -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Sede</p>
                                <p class="font-semibold">{{ $item->sede }}</p>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Descripción</p>
                                <p class="font-bold truncate text-md">{{ $item->descripcion }}</p>
                            </div>
                            <div>

                            </div>
                            <div>
                                <p class="text-xs text-blue-900 font-semibold mb-1">N° Interno</p>
                                <p class="font-mono text-sm">{{ $item->n_interno_bien }}</p>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad Admin.</p>
                                <p class="text-sm font-semibold">{{ $item->unidad_administrativas ? $item->unidad_administrativas->name : 'N/A' }}</p>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Usuario Uso</p>
                                <p class="text-sm">{{ $item->usuario_de_usos ? $item->usuario_de_usos->usuario_de_uso : 'N/A' }}</p>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Usuario Admin</p>
                                <p class="text-sm">{{ $item->usuario_administrativos ? $item->usuario_administrativos->usuario_administrativo : 'N/A' }}</p>
                            </div>

                            <div>

                            </div>
                        </div>

                        <!-- Información secundaria -->
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Estado</p>
                                @php $estadoColorClass = $item->estado_del_uso_id == 6 ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-800 border-gray-200'; @endphp
                                <span class="inline-flex items-center p-3 rounded-md text-xs font-medium border {{ $estadoColorClass }}">{{ $item->estado_del_usos ? $item->estado_del_usos->estado_del_uso : 'N/A' }}</span>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Condición</p>
                                <p class="text-sm">{{ $item->condicions ? $item->condicions->condicion : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Marca / Modelo</p>
                                <p class="text-sm">{{ $item->marca }} / {{ $item->modelo }}</p>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Serial</p>
                                <p class="font-mono text-sm">{{ $item->serial }}</p>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Color</p>
                                <p class="flex items-center gap-2 text-sm">
                                    <span class="w-4 h-4 border border-gray-300 rounded-full" style="background-color: {{ $item->color_css ?? '#cccccc' }}"></span>
                                    {{ $item->colors ? $item->colors->color : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                
                            </div>
                            <div>
                                <p class="text-xs text-blue-900 font-semibold mb-1">Código QR</p>
                                @php
                                    // Verificar si qr_code es una imagen guardada (base64/Data URL) o una URL
                                    $qrCode = $item->qr_code ?? '';
                                    $isBase64 = !empty($qrCode) && (str_starts_with($qrCode, 'data:image') || str_starts_with($qrCode, '/9j/'));

                                    if ($isBase64) {
                                        // Usar la imagen QR guardada
                                        $qrImageUrl = $qrCode;
                                        $qrModalUrl = $qrCode; // Para el modal usar la misma imagen
                                    } elseif (!empty($item->n_interno_bien)) {
                                        // Generar QR automático desde n_interno_bien
                                        $qrImageUrl = 'https://quickchart.io/chart?cht=qr&chs=150x150&chl=' . urlencode(url('/bienes/qr/' . $item->n_interno_bien));
                                        $qrModalUrl = 'https://quickchart.io/chart?cht=qr&chs=300x300&chl=' . urlencode(url('/bienes/qr/' . $item->n_interno_bien));
                                    } else {
                                        // Fallback usando el ID
                                        $qrImageUrl = 'https://quickchart.io/chart?cht=qr&chs=150x150&chl=' . urlencode(url('/bienes/qr/bien-' . $item->id));
                                        $qrModalUrl = 'https://quickchart.io/chart?cht=qr&chs=300x300&chl=' . urlencode(url('/bienes/qr/bien-' . $item->id));
                                    }
                                @endphp
                                <img src="{{ $qrImageUrl }}" alt="QR" class="w-16 h-16 border border-gray-300 rounded-lg cursor-pointer hover:scale-110 transition-transform" onclick="openQrModal('{{ $qrModalUrl }}', '{{ $item->n_interno_bien ?? $item->id }}')">
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Fecha Adq.</p>
                                <p class="text-sm">{{ $item->fecha_adquisicion ? \Carbon\Carbon::parse($item->fecha_adquisicion)->format('d/m/Y') : 'N/A' }}</p>
                                <p class="text-xs text-blue-900 font-semibold mt-2 mb-1">Valor (Bs.)</p>
                                <p class="text-sm font-bold text-green-600">{{ number_format((float) $item->valor_factura, 2) }}</p>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-col items-center md:items-end gap-2 w-full md:w-auto">
                            <div class="flex flex-wrap justify-center gap-2 md:justify-end">
                                @can('bienes_edit')
                                <a href="{{ route('administracion.bienes_publicos.edit', $item->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white hover:bg-green-700 active:translate-y-0.5 text-xs">
                                    <span>✏️</span>
                                    Editar
                                </a>
                                @endcan
                                @can('bienes_destroy')
                                <form id="delete-form-{{ $item->id }}" action="{{ route('administracion.bienes_publicos.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete({{ $item->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs">
                                        <span>🗑️</span>
                                        Eliminar
                                    </button>
                                </form>
                                @endcan
                            </div>
                            <p class="text-xs text-gray-500">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Mensaje cuando no hay bienes -->
                <div class="p-12 text-center bg-white border shadow-lg border-white/20 backdrop-blur-md rounded-2xl">
                    <h3 class="mb-2 text-xl font-bold text-gray-700">No hay bienes registrados</h3>
                    <p class="mb-6 text-gray-500">Comienza agregando un nuevo bien público</p>
                    @can('bienes_create')
                    <a href="/administracion/bienes_publicos/create" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold bg-blue-900 text-white">➕ Registrar Nuevo Bien</a>
                    @endcan
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if(isset($bienes) && $bienes->hasPages())
        <div class="px-6 mt-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4">
                {!! $bienes->links() !!}
            </div>
        </div>
        @endif
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
        function openQrModal(qrUrl, nInterno) {
            Swal.fire({
                title: 'Código QR - Bien: ' + nInterno,
                html: '<img src="' + qrUrl + '" class="w-64 h-64 mx-auto border-2 border-gray-300 rounded-lg" alt="Código QR">',
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Descargar',
                width: '450px'
            }).then((result) => {
                if (result.isConfirmed && !qrUrl.startsWith('data:')) {
                    const link = document.createElement('a');
                    link.href = qrUrl;
                    link.download = 'QR_Bien_' + nInterno + '.png';
                    link.click();
                }
            });
        }
    </script>

    <script>
        const filterData = {
            'n_interno_bien': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($n_internos ?? [] as $n)
                        { value: '{{ $n }}', text: '{{ $n }}' },
                    @endforeach
                ]
            },
            'descripcion': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas' },
                    @foreach($descripciones ?? [] as $desc)
                        { value: '{{ $desc }}', text: '{{ $desc }}' },
                    @endforeach
                ]
            },
            'unidad_administrativa_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas' },
                    @foreach($unidad_administrativas ?? [] as $ua)
                        { value: '{{ $ua->id }}', text: '{{ $ua->name }}' },
                    @endforeach
                ]
            },
            'usuario_de_uso_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($usuarios_de_uso ?? [] as $uu)
                        { value: '{{ $uu->id }}', text: '{{ $uu->usuario_de_uso }}' },
                    @endforeach
                ]
            },
            'usuario_administrativo_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($usuarios_administrativos ?? [] as $ua)
                        { value: '{{ $ua->id }}', text: '{{ $ua->usuario_administrativo }}' },
                    @endforeach
                ]
            },
            'estado_del_uso_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($estado_del_usos ?? [] as $e)
                        { value: '{{ $e->id }}', text: '{{ $e->estado_del_uso }}' },
                    @endforeach
                ]
            },
            'forma_adquisicion_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas' },
                    @foreach($forma_adquisiciones ?? [] as $fa)
                        { value: '{{ $fa->id }}', text: '{{ $fa->forma_adquisicion }}' },
                    @endforeach
                ]
            },
            'marca': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas' },
                    @foreach($marcas ?? [] as $marca)
                        { value: '{{ $marca }}', text: '{{ $marca }}' },
                    @endforeach
                ]
            },
            'modelo': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($modelos ?? [] as $modelo)
                        { value: '{{ $modelo }}', text: '{{ $modelo }}' },
                    @endforeach
                ]
            },
            'condicion_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todas' },
                    @foreach($condiciones ?? [] as $c)
                        { value: '{{ $c->id }}', text: '{{ $c->condicion }}' },
                    @endforeach
                ]
            },
            'color_id': {
                type: 'select',
                options: [
                    { value: '', text: 'Todos' },
                    @foreach($colors ?? [] as $color)
                        { value: '{{ $color->id }}', text: '{{ $color->color }}' },
                    @endforeach
                ]
            },
            'serial': {
                type: 'text',
                placeholder: 'Ingresa el serial...'
            },
            'codigo_qr': {
                type: 'text_qr',
                placeholder: 'Ingresa el código QR...'
            },
            'busqueda': {
                type: 'text'
            }
        };

        function updateSecondFilter() {
            const type = document.getElementById('filterType').value;
            const container = document.getElementById('secondFilterContainer');
            const select = document.getElementById('secondFilter');
            const dateFilter = document.getElementById('dateRangeFilter');
            const searchInput = document.getElementById('generalSearch');
            const textFilter = document.getElementById('textFilter');

            container.style.display = 'none';
            select.disabled = true;
            dateFilter.style.display = 'none';
            searchInput.style.display = 'none';
            textFilter.style.display = 'none';

            const config = filterData[type];

            if (config) {
                if (config.type === 'select') {
                    container.style.display = 'block';
                    select.disabled = false;
                    select.innerHTML = '';
                    config.options.forEach(o => {
                        const opt = document.createElement('option');
                        opt.value = o.value;
                        opt.textContent = o.text;
                        select.appendChild(opt);
                    });
                    // Restaurar valor seleccionado si existe
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('secondFilter')) {
                        select.value = urlParams.get('secondFilter');
                    }
                } else if (config.type === 'date') {
                    dateFilter.style.display = 'block';
                } else if (config.type === 'text' || config.type === 'text_qr') {
                    textFilter.style.display = 'block';
                    textFilter.placeholder = config.placeholder || 'Buscar...';
                    textFilter.name = 'secondFilter';
                    textFilter.onchange = applyTextFilter;
                    // Restaurar valor si existe
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.get('secondFilter')) {
                        textFilter.value = urlParams.get('secondFilter');
                    }
                }
            }
        }

        function applySelectFilter() {
            const type = document.getElementById('filterType').value;
            const value = document.getElementById('secondFilter').value;
            window.location.href = '{{ route("administracion.bienes_publicos.index") }}?filterType=' + type + '&secondFilter=' + encodeURIComponent(value);
        }

        function applyTextFilter() {
            const type = document.getElementById('filterType').value;
            const value = document.getElementById('textFilter').value;
            if (value.trim()) {
                window.location.href = '{{ route("administracion.bienes_publicos.index") }}?filterType=' + type + '&secondFilter=' + encodeURIComponent(value);
            }
        }

        function toggleAdvancedFilters() {
            const el = document.getElementById('filterType');
            el.style.display = el.style.display === 'none' ? 'inline-block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const url = new URLSearchParams(window.location.search);
            if (url.get('filterType')) {
                const el = document.getElementById('filterType');
                el.style.display = 'inline-block';
                el.value = url.get('filterType');
                updateSecondFilter();
                setTimeout(() => {
                    const sel = document.getElementById('secondFilter');
                    if (url.get('secondFilter')) sel.value = url.get('secondFilter');
                }, 100);
            }
        });
    </script>

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <script>
        $(function() {
            if ($('#dateRangePicker').length) {
                $('#dateRangePicker').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Limpiar',
                        applyLabel: 'Aplicar',
                        format: 'YYYY-MM-DD'
                    }
                }).on('apply.daterangepicker', function(e, p) {
                    $(this).val(p.startDate.format('YYYY-MM-DD') + ' - ' + p.endDate.format('YYYY-MM-DD'));
                    $('#fecha_desde').val(p.startDate.format('YYYY-MM-DD'));
                    $('#fecha_hasta').val(p.endDate.format('YYYY-MM-DD'));
                    $('#filterForm').submit();
                });
            }
        });
    </script>

    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let scanner = null;
        function startQrScanner() {
            Swal.fire({
                title: 'Escanear QR',
                html: '<div id="reader" class="w-full"></div>',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                width: '400px',
                didOpen: () => {
                    scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
                    scanner.render(decodedText => {
                        scanner.clear();
                        Swal.close();
                        window.location.href = '/bienes/qr/' + encodeURIComponent(decodedText.includes('/bienes/qr/') ? decodedText.split('/bienes/qr/')[1] : decodedText);
                    });
                }
            });
        }
    </script>
</x-app-layout>
@endcan

