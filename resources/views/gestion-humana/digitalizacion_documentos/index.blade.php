@can('digitalizaciones_index')
<style>
    .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
    .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
    .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }
    .pdf-thumbnail { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .pdf-thumbnail:hover { transform: scale(1.05); box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4); }
    #pdfModal .modal-content { animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    .filter-active { background-color: #3B82F6 !important; border-color: #3B82F6 !important; }

    /* PDF Zoom Controls */
    .pdf-zoom-controls { display: flex; align-items: center; gap: 8px; }
    .pdf-zoom-btn { width: 32px; height: 32px; border-radius: 8px; border: none; background: rgba(255,255,255,0.2); color: white; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s, transform 0.2s; }
    .pdf-zoom-btn:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }
    .pdf-zoom-btn:active { transform: scale(0.95); }
    .pdf-zoom-level { color: white; font-size: 12px; min-width: 45px; text-align: center; }
    #pdfContainer { overflow: auto; max-height: calc(100vh - 200px); transition: transform 0.3s ease; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

<x-app-layout>
    @section('title', 'Digitalización de Documentos')
    @section('subtitle', 'Gestión de documentos digitalizados')

    <style>
        body { box-sizing: border-box; }
        :root{ --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B; --bg-from:#f8fafc; --bg-to:#e2e8f0; --card-bg:white; --card-border:rgba(15,23,42,0.08); --muted:#64748b; --text:#0f172a; }
        body{ font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%); color:var(--text); }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .main-content{ min-height:100vh; }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
    </style>

    <form method="GET" action="{{ route('gestion-humana.digitalizacion_documentos.index') }}" id="filterForm">
        <div class="sticky top-0 z-10 px-6 mb-6">
            <div class="p-6 mb-2 glass rounded-2xl fade-in">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" onclick="toggleAdvancedFilters()" class="px-4 py-2 text-sm text-white bg-blue-900 border border-blue-600 hover:bg-blue-800 backdrop-blur-md rounded-xl">Filtros Avanzados</button>
                        <select id="filterType" name="filterType" onchange="updateSecondFilter()" class="px-4 py-2 text-sm bg-white border rounded-xl {{ request('filterType') ? 'bg-blue-100 border-blue-500' : '' }}" style="display:none;">
                            <option value="">Seleccionar filtro</option>
                            <option value="cedula" {{ request('filterType') == 'cedula' ? 'selected' : '' }}>Cédula</option>
                            <option value="nombre_apellido" {{ request('filterType') == 'nombre_apellido' ? 'selected' : '' }}>Nombres y Apellidos</option>
                            <option value="tipo_usuario" {{ request('filterType') == 'tipo_usuario' ? 'selected' : '' }}>Tipo de Usuario</option>
                            <option value="area_adscrita_id" {{ request('filterType') == 'area_adscrita_id' ? 'selected' : '' }}>Área Adscrita</option>
                            <option value="fecha" {{ request('filterType') == 'fecha' ? 'selected' : '' }}>Fecha</option>
                        </select>
                        <div id="secondFilterContainer" class="relative z-50">
                            <select id="secondFilter" name="secondFilter" class="px-4 py-2 text-sm bg-white border rounded-xl {{ request('secondFilter') ? 'bg-blue-100 border-blue-500' : '' }}" {{ request('filterType') ? '' : 'disabled' }}>
                                <option value="">-----</option>
                                @if(request('filterType') == 'area_adscrita_id')
                                    @foreach($areas_adscritas as $area)
                                        <option value="{{ $area->id }}" {{ request('secondFilter') == $area->id ? 'selected' : '' }}>{{ $area->area_adscrita }}</option>
                                    @endforeach
                                @endif
                                @if(request('filterType') == 'tipo_usuario')
                                    <option value="Titular" {{ request('secondFilter') == 'Titular' ? 'selected' : '' }}>Titular</option>
                                    <option value="Padre" {{ request('secondFilter') == 'Padre' ? 'selected' : '' }}>Padre</option>
                                    <option value="Madre" {{ request('secondFilter') == 'Madre' ? 'selected' : '' }}>Madre</option>
                                    <option value="Hijo" {{ request('secondFilter') == 'Hijo' ? 'selected' : '' }}>Hijo</option>
                                @endif
                            </select>
                        </div>
                        <div id="dateRangeFilter" style="{{ request('filterType') == 'fecha' ? '' : 'display:none;' }}">
                            <input type="text" id="dateRangePicker" name="fecha_rango" class="px-4 py-2 text-sm text-black bg-blue-100 border rounded-xl" value="{{ request('fecha_desde') && request('fecha_hasta') ? request('fecha_desde') . ' - ' . request('fecha_hasta') : '' }}" placeholder="Seleccionar rango de fechas">
                            <input type="hidden" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}">
                            <input type="hidden" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}">
                        </div>
                        <input type="text" id="textFilter" name="search" placeholder="Buscar..." class="w-64 px-4 py-2 text-sm bg-white border rounded-xl {{ request('search') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('search') }}" style="{{ in_array(request('filterType'), ['cedula', 'nombre_apellido']) ? '' : 'display:none;' }}">
<input type="text" id="generalSearch" name="search_general" placeholder="Búsqueda general..." class="w-64 px-4 py-2 text-sm bg-white border rounded-xl {{ request('search_general') ? 'bg-blue-100 border-blue-500' : '' }}" value="{{ request('search_general') }}">
                        <a href="{{ route('gestion-humana.digitalizacion_documentos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm">🗑️ Limpiar</a>

                        <!-- Selector de ordenamiento -->
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-500">Ordenar:</span>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'cedula', 'direction' => $sort === 'cedula' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'cedula' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Cédula @if($sort === 'cedula'){{ $direction === 'asc' ? '⬆️' : '⬇️' }}@endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nombre_apellido', 'direction' => $sort === 'nombre_apellido' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'nombre_apellido' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Nombre @if($sort === 'nombre_apellido'){{ $direction === 'asc' ? '⬆️' : '⬇️' }}@endif
                            </a>
                           {{--  <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $sort === 'created_at' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'created_at' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                Fecha @if($sort === 'created_at'){{ $direction === 'asc' ? '⬆️' : '⬇️' }}@endif
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc']) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'id' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                                ID @if($sort === 'id'){{ $direction === 'asc' ? '⬆️' : '⬇️' }}@endif
                            </a> --}}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @can('digitalizaciones_create')
                        <a href="{{ route('gestion-humana.digitalizacion_documentos.create') }}" class="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm"><span>➕</span>Nuevo </a>
                        @endcan
                    </div>
            </div>
            @if(request('filterType') || request('secondFilter') || request('search') || request('search_general') || request('fecha_desde') || request('fecha_hasta'))
            <div class="p-4 mt-4 border border-blue-200 bg-blue-50 rounded-xl">
                <div class="flex items-center gap-2 mb-2"><span class="font-semibold text-blue-600">🔍 Filtros Activos:</span></div>
                <div class="flex flex-wrap gap-2">
                    @if(request('filterType') == 'cedula' && request('search'))<span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">Cédula: "{{ request('search') }}"<button onclick="clearSpecificFilter('cedula')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button></span>@endif
                    @if(request('filterType') == 'nombre_apellido' && request('search'))<span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">Nombres: "{{ request('search') }}"<button onclick="clearSpecificFilter('nombre_apellido')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button></span>@endif
                    @if(request('search_general'))<span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">Búsqueda: "{{ request('search_general') }}"<button onclick="clearSpecificFilter('search_general')" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button></span>@endif
                    @if(request('fecha_desde') || request('fecha_hasta'))<span class="inline-flex items-center gap-1 px-3 py-1 text-sm text-white bg-blue-600 rounded-full">{{ request('fecha_desde') ? request('fecha_desde') : '' }} {{ request('fecha_hasta') ? '- ' . request('fecha_hasta') : '' }}<button onclick="clearDateFilter()" class="ml-1 hover:bg-blue-800 rounded-full p-0.5">×</button></span>@endif
                </div>
            @endif
        </div>
    </form>

    <div class="px-6 mb-8">


        <div class="space-y-4 fade-in">
            @forelse ($digitalizaciones as $dato)
            <div class="glass rounded-2xl p-6 hover:scale-[1.01] transition-transform">
                <div class="grid items-center grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">👤 Cédula</p>
                        <p class="font-mono text-lg font-semibold">{{ $dato->cedula }}</p>
                        <p class="mb-1 text-xs font-semibold text-blue-900">📝 Nombre</p>
                        <p class="max-w-xs text-sm font-semibold truncate">{{ $dato->nombre_apellido }}</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <p class="mb-1 text-xs font-semibold text-blue-900">🚻 Tipo de Usuario</p>
                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">{{ $dato->salud }}</span>
                        <p class="mt-2 mb-1 text-xs font-semibold text-blue-900">📂 Área Adscrita</p>
                        <span class="text-sm text-gray-600">{{ $dato->area_adscrita->area_adscrita ?? 'Sin área' }}</span>
                    </div>
                    <div class="text-center lg:text-right">
                        <p class="mb-1 text-xs font-semibold text-blue-900">📄 Documentos ({{ $dato->documentos->count() }})</p>
                        <div class="flex flex-wrap justify-center gap-2 mb-3 lg:justify-end">
                            @forelse($dato->documentos as $doc)
                                <div class="relative flex flex-col items-center justify-center w-20 p-2 border-2 border-red-200 rounded-lg cursor-pointer bg-red-50 pdf-thumbnail hover:border-red-400 group" onclick="openPdfModal('{{ asset('documentos_ogh/' . $doc->ruta) }}', '{{ $doc->nombre_archivo }} - {{ $dato->nombre_apellido }}')" title="{{ 'documentos_ogh/' . $doc->ruta }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/></svg>
                                    <span class="absolute flex items-center justify-center w-5 h-5 text-xs text-white bg-blue-600 rounded-full -top-2 -right-2">{{ $loop->iteration }}</span>
                                    <span class="mt-1 text-[8px] font-medium text-red-700 text-center leading-tight line-clamp-2 w-full">
                                        {{ $doc->tipo_documento->tipo_documento ?? 'Sin tipo' }}
                                    </span>
                                </div>
                            @empty
                                <div class="flex items-center justify-center w-12 bg-gray-100 border-2 border-gray-200 rounded-lg h-14">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                </div>
                            @endforelse
                        </div>

                        <p class="mb-1 text-xs font-semibold text-blue-900">⚙️ Opciones</p>
                        <div class="flex flex-wrap justify-center gap-2 lg:justify-end">
                            @can('digitalizaciones_edit')
                            <a href="{{ route('gestion-humana.digitalizacion_documentos.edit', $dato->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white hover:bg-green-700 active:translate-y-0.5 text-xs"><span>✏️</span>Editar</a>
                            @endcan
                            @can('digitalizaciones_destroy')
                            <button type="button" onclick="event.preventDefault(); confirmDelete({{ $dato->id }}, '{{ $dato->nombre_apellido }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs"><span>🗑️</span>Eliminar</button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
                    @empty
                    <div class="p-6 text-center glass rounded-2xl">
                        <div class="flex flex-col items-center justify-center py-8">
                            <div class="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-full"><span class="text-3xl">📂</span></div>
                            <h3 class="mb-1 text-lg font-medium text-gray-900">No hay documentos digitalizados</h3>
                            <p class="mb-4 text-sm text-gray-500">No se han encontrado documentos digitalizados</p>
                            @can('digitalizaciones_create')
                            <a href="{{ route('gestion-humana.digitalizacion_documentos.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition-all bg-blue-900 border-none rounded-xl hover:bg-blue-800"><span>➕</span>Subir Documento</a>
                            @endcan
                        </div>
                    @endforelse
                    </div>
            </div>
         @if($digitalizaciones->hasPages())
        <div class="flex justify-center mt-8">
            <div class="p-4 glass rounded-xl">{{ $digitalizaciones->links() }}</div>
        @endif
        </div>
    </div>
     <div id="pdfModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-full p-4">
            <div class="relative w-full max-w-6xl overflow-hidden bg-white shadow-2xl rounded-2xl" style="max-height: 90vh;">
                <div class="flex items-center justify-between px-4 py-3 bg-blue-900">
                    <h3 class="text-lg font-semibold text-white" id="pdfModalTitle">Vista Previa del Documento</h3>
                    <div class="flex items-center gap-3">
                        <!-- Controles de zoom -->
                        <div class="pdf-zoom-controls">
                            <button type="button" onclick="zoomOut()" class="pdf-zoom-btn" title="Disminuir">−</button>
                            <span class="pdf-zoom-level" id="zoomLevel">100%</span>
                            <button type="button" onclick="zoomIn()" class="pdf-zoom-btn" title="Ampliar">+</button>
                            <button type="button" onclick="resetZoom()" class="pdf-zoom-btn" title="Restablecer" style="font-size: 14px;">⟳</button>
                        </div>
                        <button type="button" onclick="closePdfModal()" class="text-white transition-colors hover:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="pdfContainer" class="p-4 bg-gray-100">
                    <iframe id="pdfFrame" class="w-full border border-gray-300 rounded-lg" style="height: 75vh; transition: transform 0.3s ease;"></iframe>
                </div>
            </div>
        </div>
    </div>

     <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
        <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
            <div id="toastIcon">✅</div>
        <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>
</div>
    <script>
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast'), icon = document.getElementById('toastIcon'), msgEl = document.getElementById('toastMsg');
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }
        function updateSecondFilter() {
            const filterType = document.getElementById('filterType').value;
            const secondFilterContainer = document.getElementById('secondFilterContainer');
            const dateRangeFilter = document.getElementById('dateRangeFilter');
            const textFilter = document.getElementById('textFilter');
            secondFilterContainer.style.display = 'none';
            dateRangeFilter.style.display = 'none';
            textFilter.style.display = 'none';
            if (filterType === 'fecha') { dateRangeFilter.style.display = 'block'; }
            else if (filterType === 'cedula' || filterType === 'nombre_apellido') { textFilter.style.display = 'block'; }
            else if (filterType === 'area_adscrita_id' || filterType === 'tipo_usuario') { secondFilterContainer.style.display = 'block'; }
            if (filterType !== '') { document.getElementById('filterType').style.display = 'none'; }
        }
        function clearSpecificFilter(filterType) {
            const url = new URL(window.location);
            if (filterType === 'cedula' || filterType === 'nombre_apellido') { url.searchParams.delete('filterType'); url.searchParams.delete('search'); }
            else if (filterType === 'search_general') { url.searchParams.delete('search_general'); }
            window.location.href = url.toString();
        }
        function clearDateFilter() {
            const url = new URL(window.location);
            url.searchParams.delete('filterType'); url.searchParams.delete('fecha_desde'); url.searchParams.delete('fecha_hasta');
            window.location.href = url.toString();
        }
        function toggleAdvancedFilters() {
            const filterSelect = document.getElementById('filterType');
            if (filterSelect.style.display === 'none' || filterSelect.style.display === '') { filterSelect.style.display = 'inline-block'; }
            else { filterSelect.style.display = 'none'; filterSelect.value = ''; updateSecondFilter(); document.getElementById('filterForm').submit(); }
        }
        function openPdfModal(pdfUrl, documentName) {
            const modal = document.getElementById('pdfModal'), frame = document.getElementById('pdfFrame'), title = document.getElementById('pdfModalTitle');

            // Reset zoom
            pdfZoom = 100;
            updateZoomDisplay();
            frame.style.transform = 'scale(1)';

            modal.classList.remove('hidden');
            frame.src = pdfUrl + '#toolbar=0&navpanes=0&scrollbar=1';
            title.textContent = documentName;
            document.body.style.overflow = 'hidden';
        }

        function closePdfModal() {
            const modal = document.getElementById('pdfModal'), frame = document.getElementById('pdfFrame');
            modal.classList.add('hidden'); frame.src = '';
            document.body.style.overflow = 'auto';
        }

        let pdfZoom = 100;

        function zoomIn() {
            if (pdfZoom < 200) {
                pdfZoom += 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function zoomOut() {
            if (pdfZoom > 50) {
                pdfZoom -= 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function resetZoom() {
            pdfZoom = 100;
            updateZoomDisplay();
            document.getElementById('pdfFrame').style.transform = 'scale(1)';
        }

        function updateZoomDisplay() {
            document.getElementById('zoomLevel').textContent = pdfZoom + '%';
        }

        document.getElementById('pdfModal')?.addEventListener('click', function(e) { if (e.target === this) { closePdfModal(); } });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closePdfModal(); } });
        document.querySelectorAll('#filterForm select[name], #filterForm input[name]').forEach(element => { element.addEventListener('change', function() { if (this.id !== 'dateRangePicker') { document.getElementById('filterForm').submit(); } }); });
        $(document).ready(function() {
            $('#dateRangePicker').daterangepicker({ autoUpdateInput: false, locale: { cancelLabel: 'Limpiar', applyLabel: 'Aplicar', format: 'YYYY-MM-DD', separator: ' - ' } });
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) { $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD')); $('#fecha_desde').val(picker.startDate.format('YYYY-MM-DD')); $('#fecha_hasta').val(picker.endDate.format('YYYY-MM-DD')); $('#filterForm').submit(); });
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) { $(this).val(''); $('#fecha_desde').val(''); $('#fecha_hasta').val(''); $('#filterForm').submit(); });
        });
        function confirmDelete(id, name) {
            Swal.fire({ title: '¿Estás seguro?', text: `¿Eliminar digitalización de "${name}" con ${document.querySelectorAll(`[data-digitalizacion="${id}"]`).length} documento(s)?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/gestion-humana/digitalizacion_documentos/${id}`;
                    const csrfInput = document.createElement('input'); csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);
                    const methodInput = document.createElement('input'); methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    document.body.appendChild(form); form.submit();
                }
            });
        }
    </script>
</x-app-layout>
@endcan
