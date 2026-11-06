import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function BienesPublicos() {
    const [bienes, setBienes] = useState([]);
    const [metrics, setMetrics] = useState({
        totalBienes: 0,
        bienesEnUso: 0,
        ultimoBien: null
    });
    const [catalogs, setCatalogs] = useState({
        n_internos: [],
        descripciones: [],
        unidad_administrativas: [],
        usuarios_de_uso: [],
        usuarios_administrativos: [],
        estado_del_usos: [],
        forma_adquisiciones: [],
        marcas: [],
        modelos: [],
        condiciones: [],
        colors: []
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true,
        can_export: true
    });

    // Filtros originales
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [busqueda, setBusqueda] = useState('');
    const [textFilter, setTextFilter] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Modal de Código QR
    const [selectedQrBien, setSelectedQrBien] = useState(null);
    const [qrModalOpen, setQrModalOpen] = useState(false);

    // Cargar datos
    const fetchBienes = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });

            if (busqueda.trim()) {
                params.append('busqueda', busqueda.trim());
            }

            if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'fecha') {
                    if (fechaDesde) params.append('fecha_desde', fechaDesde);
                    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
                } else if (filterType === 'serial' || filterType === 'codigo_qr') {
                    if (textFilter.trim()) params.append('secondFilter', textFilter.trim());
                } else if (secondFilter) {
                    params.append('secondFilter', secondFilter);
                }
            }

            const response = await fetch(`/api/administracion/bienes_publicos?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '/login?timeout=1';
                    return;
                }
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const json = await response.json();
            const resData = json.data || {};
            const paginated = resData.bienes || {};

            setBienes(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                per_page: paginated.per_page || 10
            });

            if (resData.metrics) {
                setMetrics(resData.metrics);
            }
            if (resData.catalogs) {
                setCatalogs(resData.catalogs);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching bienes:', err);
            setError(err.message || 'No se pudieron cargar los bienes.');
        } finally {
            setLoading(false);
        }
    }, [filterType, secondFilter, busqueda, textFilter, fechaDesde, fechaHasta]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchBienes(1);
        }, 350);
        return () => clearTimeout(timer);
    }, [filterType, secondFilter, busqueda, textFilter, fechaDesde, fechaHasta, fetchBienes]);

    // Limpiar filtros
    const handleClearFilters = () => {
        setFilterType('');
        setSecondFilter('');
        setBusqueda('');
        setTextFilter('');
        setFechaDesde('');
        setFechaHasta('');
        setShowAdvancedFilters(false);
    };

    // Formatear Fecha d/m/Y
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    };

    // Formatear Fecha y Hora d/m/Y H:i
    const formatDateTime = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    // Formato de Moneda Bs.
    const formatCurrency = (val) => {
        const num = parseFloat(val);
        if (isNaN(num)) return '0,00';
        return num.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Obtener Emoji representativo de la descripción (Exacto a Biene.php getIconForDescription)
    const getIconForDescription = (desc) => {
        const d = (desc || '').toLowerCase();
        if (d.includes('silla') || d.includes('sillón') || d.includes('butaca')) return '🪑';
        if (d.includes('mesa') || d.includes('escritorio') || d.includes('tabla')) return '🪵';
        if (d.includes('gabinete') || d.includes('archivador') || d.includes('armario')) return '🗄️';
        if (d.includes('estante') || d.includes('anaquel')) return '📚';
        if (d.includes('aire') || d.includes('a/a') || d.includes('acondicionado')) return '❄️';
        if (d.includes('monitor') || d.includes('pantalla')) return '🖥️';
        if (d.includes('teclado')) return '⌨️';
        if (d.includes('mouse') || d.includes('ratón')) return '🖱️';
        if (d.includes('computadora') || d.includes('pc') || d.includes('laptop') || d.includes('portátil') || d.includes('portatil') || d.includes('cpu')) return '💻';
        if (d.includes('impresora')) return '🖨️';
        if (d.includes('escáner') || d.includes('scanner') || d.includes('cámara')) return '📷';
        if (d.includes('proyector') || d.includes('videobeam')) return '📽️';
        if (d.includes('teléfono') || d.includes('telefono')) return '📞';
        if (d.includes('televisor') || d.includes('tv')) return '📺';
        if (d.includes('vehículo') || d.includes('carro') || d.includes('camioneta') || d.includes('moto')) return '🚗';
        return '📦';
    };

    // Obtener URL de imagen de Código QR
    const getQrImageUrl = (item, size = 150) => {
        const qrCode = item.qr_code || '';
        const isBase64 = qrCode.startsWith('data:image') || qrCode.startsWith('/9j/');
        if (isBase64) return qrCode;

        const target = item.n_interno_bien
            ? `${window.location.origin}/bienes/qr/${encodeURIComponent(item.n_interno_bien)}`
            : `${window.location.origin}/bienes/qr/bien-${item.id}`;

        return `https://quickchart.io/chart?cht=qr&chs=${size}x${size}&chl=${encodeURIComponent(target)}`;
    };

    // Modal para visualizar QR ampliado
    const handleOpenQrModal = (item) => {
        setSelectedQrBien(item);
        setQrModalOpen(true);
    };

    // Eliminar con el SweetAlert2 original
    const handleDelete = async (item) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar el bien "${item.n_interno_bien || item.descripcion}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/bienes_publicos/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'El bien público fue eliminado correctamente',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchBienes(pagination.current_page);
        } catch (err) {
            console.error('Error deleting bien:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el bien',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    return (
        <div className="w-full">
            {/* Tarjetas superiores de estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
                    {/* Total de Bienes */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500" style={{ fontSize: '24px' }}>📦</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalBienes ?? 0}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Total de Bienes</p>
                    </div>

                    {/* Última Fecha de Adquisición */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500" style={{ fontSize: '24px' }}>📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">
                            {metrics.ultimoBien && metrics.ultimoBien.fecha_adquisicion
                                ? formatDate(metrics.ultimoBien.fecha_adquisicion)
                                : 'N/A'}
                        </h3>
                        <p className="text-sm text-blue-900 font-semibold">Última Fecha de Adquisición</p>
                    </div>

                    {/* Bienes en Uso */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-yellow-500/20">
                                <span className="text-yellow-500" style={{ fontSize: '24px' }}>✅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.bienesEnUso ?? 0}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Bienes en Uso</p>
                    </div>
                </div>
            </div>

            {/* Filtros y controles (Diseño Original Exacto) */}
            <div className="px-6 mb-6">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                    <div className="flex flex-col gap-4">
                        {/* Fila de acciones (Exportar, Nuevo) */}
                        <div className="flex flex-wrap items-center justify-end gap-3 pb-4 border-b border-gray-100">
                            <select
                                onChange={(e) => {
                                    if (e.target.value) window.location.href = e.target.value;
                                }}
                                className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm"
                            >
                                <option value="">📤 Exportar</option>
                                <option value="/administracion/bienes_publicos/export/excel">Excel</option>
                                <option value="/administracion/bienes_publicos/export/pdf">PDF</option>
                            </select>

                            {permissions.can_create && (
                                <a
                                    href="/administracion/bienes_publicos/create"
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    <span>➕</span>
                                    Nuevo
                                </a>
                            )}
                        </div>

                        {/* Fila de filtros */}
                        <div className="flex flex-wrap items-center gap-4">
                            <button
                                type="button"
                                onClick={() => {
                                    Swal.fire({
                                        title: 'Escanear QR',
                                        text: 'Apunta la cámara al código QR del bien público para buscarlo directamente.',
                                        icon: 'info',
                                        confirmButtonColor: '#1e3a8a'
                                    });
                                }}
                                className="bg-blue-900 hover:bg-blue-800 text-white border border-green-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/50"
                            >
                                📷 Escanear QR
                            </button>

                            <button
                                type="button"
                                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                className="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                            >
                                Filtros Avanzados
                            </button>

                            {showAdvancedFilters && (
                                <select
                                    value={filterType}
                                    onChange={(e) => {
                                        setFilterType(e.target.value);
                                        setSecondFilter('');
                                        setTextFilter('');
                                        setFechaDesde('');
                                        setFechaHasta('');
                                    }}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm text-slate-800"
                                >
                                    <option value="">Seleccionar filtro</option>
                                    <option value="n_interno_bien">N° Interno</option>
                                    <option value="descripcion">Descripción</option>
                                    <option value="unidad_administrativa_id">Unidad Administrativa</option>
                                    <option value="usuario_de_uso_id">Usuario de Uso</option>
                                    <option value="usuario_administrativo_id">Usuario Administrativo</option>
                                    <option value="estado_del_uso_id">Estado del Uso</option>
                                    <option value="forma_adquisicion_id">Forma de Adquisición</option>
                                    <option value="marca">Marca</option>
                                    <option value="modelo">Modelo</option>
                                    <option value="condicion_id">Condición</option>
                                    <option value="color_id">Color</option>
                                    <option value="serial">Serial</option>
                                    <option value="codigo_qr">Código QR</option>
                                    <option value="fecha">Fecha de Adquisición</option>
                                    <option value="busqueda">Búsqueda General</option>
                                </select>
                            )}

                            {/* Segundo filtro dinámico según tipo */}
                            {showAdvancedFilters && filterType && filterType !== 'fecha' && filterType !== 'busqueda' && filterType !== 'serial' && filterType !== 'codigo_qr' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm text-slate-800"
                                >
                                    <option value="">-----</option>
                                    {filterType === 'n_interno_bien' && (catalogs.n_internos || []).map((n, i) => (
                                        <option key={i} value={n}>{n}</option>
                                    ))}
                                    {filterType === 'descripcion' && (catalogs.descripciones || []).map((d, i) => (
                                        <option key={i} value={d}>{d}</option>
                                    ))}
                                    {filterType === 'unidad_administrativa_id' && (catalogs.unidad_administrativas || []).map(u => (
                                        <option key={u.id} value={u.id}>{u.name || u.unidad_administrativa}</option>
                                    ))}
                                    {filterType === 'usuario_de_uso_id' && (catalogs.usuarios_de_uso || []).map(u => (
                                        <option key={u.id} value={u.id}>{u.usuario_de_uso || u.name}</option>
                                    ))}
                                    {filterType === 'usuario_administrativo_id' && (catalogs.usuarios_administrativos || []).map(u => (
                                        <option key={u.id} value={u.id}>{u.usuario_administrativo || u.name}</option>
                                    ))}
                                    {filterType === 'estado_del_uso_id' && (catalogs.estado_del_usos || []).map(e => (
                                        <option key={e.id} value={e.id}>{e.estado_del_uso || e.nombre}</option>
                                    ))}
                                    {filterType === 'forma_adquisicion_id' && (catalogs.forma_adquisiciones || []).map(f => (
                                        <option key={f.id} value={f.id}>{f.forma_adquisicion || f.nombre}</option>
                                    ))}
                                    {filterType === 'marca' && (catalogs.marcas || []).map((m, i) => (
                                        <option key={i} value={m}>{m}</option>
                                    ))}
                                    {filterType === 'modelo' && (catalogs.modelos || []).map((m, i) => (
                                        <option key={i} value={m}>{m}</option>
                                    ))}
                                    {filterType === 'condicion_id' && (catalogs.condiciones || []).map(c => (
                                        <option key={c.id} value={c.id}>{c.condicion || c.nombre}</option>
                                    ))}
                                    {filterType === 'color_id' && (catalogs.colors || []).map(c => (
                                        <option key={c.id} value={c.id}>{c.color || c.nombre}</option>
                                    ))}
                                </select>
                            )}

                            {/* Rango de fechas */}
                            {showAdvancedFilters && filterType === 'fecha' && (
                                <div className="flex items-center gap-2">
                                    <input
                                        type="date"
                                        value={fechaDesde}
                                        onChange={(e) => setFechaDesde(e.target.value)}
                                        className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                    />
                                    <input
                                        type="date"
                                        value={fechaHasta}
                                        onChange={(e) => setFechaHasta(e.target.value)}
                                        className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                    />
                                </div>
                            )}

                            {/* Input de texto para Serial o Código QR */}
                            {showAdvancedFilters && (filterType === 'serial' || filterType === 'codigo_qr') && (
                                <input
                                    type="text"
                                    value={textFilter}
                                    onChange={(e) => setTextFilter(e.target.value)}
                                    placeholder="Ingresa el valor..."
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm text-slate-800"
                                />
                            )}

                            {/* Búsqueda General */}
                            <input
                                type="text"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Buscar..."
                                className={`bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm w-48 sm:w-64 text-slate-800 ${
                                    busqueda ? 'bg-blue-200 border-blue-500' : ''
                                }`}
                            />

                            {/* Botón para limpiar filtros */}
                            {(filterType || secondFilter || busqueda || textFilter || fechaDesde || fechaHasta) && (
                                <button
                                    type="button"
                                    onClick={handleClearFilters}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de bienes (Diseño Original Exacto) */}
            <div className="px-6 mb-8 sm:justify-center">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white border border-white/20 backdrop-blur-md rounded-2xl">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando bienes públicos...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchBienes(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : bienes.length === 0 ? (
                    <div className="p-12 text-center bg-white border shadow-lg border-white/20 backdrop-blur-md rounded-2xl">
                        <h3 className="mb-2 text-xl font-bold text-gray-700">No hay bienes registrados</h3>
                        <p className="mb-6 text-gray-500">Comienza agregando un nuevo bien público</p>
                        {permissions.can_create && (
                            <a
                                href="/administracion/bienes_publicos/create"
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold bg-blue-900 text-white hover:bg-blue-800 transition-colors"
                            >
                                ➕ Registrar Nuevo Bien
                            </a>
                        )}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {bienes.map((item) => {
                            const qrImageUrl = getQrImageUrl(item, 150);
                            const estadoColorClass = item.estado_del_uso_id === 6
                                ? 'bg-green-100 text-green-800 border-green-200'
                                : 'bg-gray-100 text-gray-800 border-gray-200';

                            return (
                                <div
                                    key={item.id}
                                    className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200"
                                >
                                    <div className="flex flex-col md:flex-row items-start gap-4 md:gap-4">
                                        {/* Icono/Imagen del bien con gradiente exacto original */}
                                        <div className="flex items-center justify-center flex-shrink-0 w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500 to-blue-900 text-white shadow-md">
                                            <span className="text-3xl">{getIconForDescription(item.descripcion)}</span>
                                        </div>

                                        {/* Información principal */}
                                        <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 w-20">
                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Sede</p>
                                                <p className="font-semibold text-slate-800 text-sm">{item.sede || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Descripción</p>
                                                <p className="font-bold truncate text-md text-slate-900" title={item.descripcion}>
                                                    {item.descripcion || 'Sin descripción'}
                                                </p>
                                            </div>

                                            <div></div>

                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">N° Interno</p>
                                                <p className="font-mono text-sm text-slate-800 font-medium">{item.n_interno_bien || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad Admin.</p>
                                                <p className="text-sm font-semibold text-slate-800">
                                                    {item.unidad_administrativas ? (item.unidad_administrativas.name || item.unidad_administrativas.unidad_administrativa) : 'N/A'}
                                                </p>
                                                <p className="text-xs text-blue-900 font-semibold mb-1 mt-1">Usuario Uso</p>
                                                <p className="text-sm text-slate-700">
                                                    {item.usuario_de_usos ? (item.usuario_de_usos.usuario_de_uso || item.usuario_de_usos.name) : 'N/A'}
                                                </p>
                                                <p className="text-xs text-blue-900 font-semibold mb-1 mt-1">Usuario Admin</p>
                                                <p className="text-sm text-slate-700">
                                                    {item.usuario_administrativos ? (item.usuario_administrativos.usuario_administrativo || item.usuario_administrativos.name) : 'N/A'}
                                                </p>
                                            </div>

                                            <div></div>
                                        </div>

                                        {/* Información secundaria */}
                                        <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Estado</p>
                                                <span className={`inline-flex items-center p-3 rounded-md text-xs font-medium border ${estadoColorClass}`}>
                                                    {item.estado_del_usos ? (item.estado_del_usos.estado_del_uso || item.estado_del_usos.nombre) : 'N/A'}
                                                </span>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Condición</p>
                                                <p className="text-sm text-slate-800">{item.condicions ? (item.condicions.condicion || item.condicions.nombre) : 'N/A'}</p>

                                                <div>
                                                    <p className="text-xs text-blue-900 font-semibold mb-1 mt-2">Código QR</p>
                                                    <img
                                                        src={qrImageUrl}
                                                        alt="QR"
                                                        className="w-16 h-16 border border-gray-300 rounded-lg cursor-pointer hover:scale-110 transition-transform bg-white p-1"
                                                        onClick={() => handleOpenQrModal(item)}
                                                        title="Ver Código QR Ampliado"
                                                    />
                                                    <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Fecha Adq.</p>
                                                    <p className="text-sm text-slate-800">{formatDate(item.fecha_adquisicion)}</p>
                                                    <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Valor (Bs.)</p>
                                                    <p className="text-sm font-bold text-green-600">{formatCurrency(item.valor_factura)}</p>
                                                </div>
                                            </div>

                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Marca / Modelo</p>
                                                <p className="text-sm text-slate-800">{item.marca || 'N/A'} / {item.modelo || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Serial</p>
                                                <p className="font-mono text-sm text-slate-800">{item.serial || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Color</p>
                                                <p className="flex items-center gap-2 text-sm text-slate-800">
                                                    <span
                                                        className="w-4 h-4 border border-gray-300 rounded-full inline-block"
                                                        style={{ backgroundColor: item.color_css || item.colors?.color_css || '#cccccc' }}
                                                    ></span>
                                                    {item.colors ? (item.colors.color || item.colors.nombre) : 'N/A'}
                                                </p>
                                            </div>

                                            <div></div>
                                        </div>

                                        {/* Acciones */}
                                        <div className="flex flex-col items-center md:items-end gap-2 w-full md:w-auto">
                                            <div className="flex flex-wrap justify-center gap-2 md:justify-end">
                                                {permissions.can_edit && (
                                                    <a
                                                        href={`/administracion/bienes_publicos/${item.id}/edit`}
                                                        className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs"
                                                    >
                                                        <span>✏️</span>
                                                        Editar
                                                    </a>
                                                )}
                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(item)}
                                                        className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-xs hover:bg-red-600"
                                                    >
                                                        <span>🗑️</span>
                                                        Eliminar
                                                    </button>
                                                )}
                                            </div>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {formatDateTime(item.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="px-6 mt-8">
                        <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4 flex items-center justify-between">
                            <button
                                type="button"
                                onClick={() => fetchBienes(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-3">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchBienes(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Código QR Ampliado (Replicando openQrModal original) */}
            {qrModalOpen && selectedQrBien && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-sm font-bold text-blue-950">
                                Código QR - Bien: {selectedQrBien.n_interno_bien || selectedQrBien.id}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setQrModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="p-4 bg-slate-50 rounded-xl border border-slate-100 inline-block mb-3">
                            <img
                                src={getQrImageUrl(selectedQrBien, 300)}
                                alt="QR Code"
                                className="w-56 h-56 mx-auto rounded-lg shadow-xs"
                            />
                        </div>

                        <p className="text-xs font-medium text-slate-600 mb-4 truncate px-2" title={selectedQrBien.descripcion}>
                            {selectedQrBien.descripcion}
                        </p>

                        <button
                            type="button"
                            onClick={() => setQrModalOpen(false)}
                            className="w-full py-2.5 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-colors shadow-sm"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
