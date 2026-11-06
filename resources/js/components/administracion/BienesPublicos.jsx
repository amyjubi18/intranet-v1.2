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
        from: 0,
        to: 0,
        per_page: 10
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [permissions, setPermissions] = useState({
        can_create: false,
        can_edit: false,
        can_destroy: false,
        can_export: false
    });

    // Filtros originales
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [busqueda, setBusqueda] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Modales
    const [selectedBien, setSelectedBien] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);
    const [qrModalOpen, setQrModalOpen] = useState(false);

    // Cargar datos
    const fetchBienes = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'fecha') {
                    if (fechaDesde) params.append('fecha_desde', fechaDesde);
                    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
                } else if (filterType === 'busqueda') {
                    if (busqueda) params.append('busqueda', busqueda);
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
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 10
            });

            setMetrics({
                totalBienes: resData.totalBienes || paginated.total || 0,
                bienesEnUso: resData.bienesEnUso || 0,
                ultimoBien: resData.ultimoBien || null
            });

            if (resData.catalogs) {
                setCatalogs(resData.catalogs);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching bienes:', err);
            setError(err.message || 'No se pudieron cargar los datos.');
        } finally {
            setLoading(false);
        }
    }, [filterType, secondFilter, busqueda, fechaDesde, fechaHasta]);

    useEffect(() => {
        fetchBienes(1);
    }, [fetchBienes]);

    const handleClearFilters = () => {
        setFilterType('');
        setSecondFilter('');
        setBusqueda('');
        setFechaDesde('');
        setFechaHasta('');
    };

    // Función para obtener icono según descripción
    const getIconForDescription = (desc = '') => {
        const d = (desc || '').toLowerCase();
        if (d.includes('computador') || d.includes('laptop') || d.includes('pc') || d.includes('servidor')) return '💻';
        if (d.includes('impresora') || d.includes('multifuncional')) return '🖨️';
        if (d.includes('silla') || d.includes('sillón') || d.includes('butaca')) return '🪑';
        if (d.includes('escritorio') || d.includes('mesa') || d.includes('estante')) return '🛋️';
        if (d.includes('vehiculo') || d.includes('carro') || d.includes('camioneta')) return '🚗';
        if (d.includes('telefono') || d.includes('celular')) return '📞';
        if (d.includes('aire') || d.includes('split')) return '❄️';
        return '📦';
    };

    const handleDelete = async (bien) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el bien "${bien.descripcion}" (N° Interno: ${bien.n_interno_bien || 'S/N'})`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/api/administracion/bienes_publicos/${bien.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('Error al eliminar');

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'El bien fue eliminado con éxito.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchBienes(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el bien.'
            });
        }
    };

    const openQrModal = (bien) => {
        setSelectedBien(bien);
        setQrModalOpen(true);
    };

    const openDetailModal = (bien) => {
        setSelectedBien(bien);
        setDetailModalOpen(true);
    };

    return (
        <div className="w-full">
            {/* Tarjetas superiores de estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Total de Bienes */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500 text-2xl">📦</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalBienes}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Total de Bienes</p>
                    </div>

                    {/* Última Fecha de Adquisición */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500 text-2xl">📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">
                            {metrics.ultimoBien && metrics.ultimoBien.fecha_adquisicion
                                ? metrics.ultimoBien.fecha_adquisicion.split('T')[0].split('-').reverse().join('/')
                                : 'N/A'}
                        </h3>
                        <p className="text-sm text-blue-900 font-semibold">Última Fecha de Adquisición</p>
                    </div>

                    {/* Bienes en Uso */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-yellow-500/20">
                                <span className="text-yellow-500 text-2xl">✅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.bienesEnUso}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Bienes en Uso</p>
                    </div>
                </div>
            </div>

            {/* Filtros y Controles (Diseño Original Exacto) */}
            <div className="px-6 mb-6">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
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
                                    <span>Nuevo</span>
                                </a>
                            )}
                        </div>

                        {/* Fila de filtros */}
                        <div className="flex flex-wrap items-center gap-4">
                            <button
                                type="button"
                                onClick={() => Swal.fire({ title: 'Escáner QR', text: 'Activa tu cámara para leer el código QR del bien', icon: 'info' })}
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
                                    }}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
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

                            {/* Segundo filtro según tipo */}
                            {filterType === 'n_interno_bien' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar N° Interno</option>
                                    {(catalogs.n_internos || []).map((n, i) => (
                                        <option key={i} value={n}>{n}</option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'unidad_administrativa_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar Unidad</option>
                                    {(catalogs.unidad_administrativas || []).map(u => (
                                        <option key={u.id} value={u.id}>{u.name}</option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'estado_del_uso_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar Estado</option>
                                    {(catalogs.estado_del_usos || []).map(e => (
                                        <option key={e.id} value={e.id}>{e.nombre}</option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'fecha' && (
                                <div className="flex items-center gap-2">
                                    <input
                                        type="date"
                                        value={fechaDesde}
                                        onChange={(e) => setFechaDesde(e.target.value)}
                                        className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-3 py-1.5 text-xs"
                                    />
                                    <span className="text-xs text-slate-500">hasta</span>
                                    <input
                                        type="date"
                                        value={fechaHasta}
                                        onChange={(e) => setFechaHasta(e.target.value)}
                                        className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-3 py-1.5 text-xs"
                                    />
                                </div>
                            )}

                            {['serial', 'codigo_qr', 'marca', 'modelo', 'descripcion'].includes(filterType) && (
                                <input
                                    type="text"
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    placeholder="Ingresa el valor..."
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                />
                            )}

                            {/* Búsqueda General */}
                            <input
                                type="text"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Buscar en todos los campos..."
                                className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm w-48 sm:w-64"
                            />

                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                            >
                                🗑️ Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de Bienes en Tarjetas Originales */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando bienes públicos...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
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
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">🏛️</div>
                        <h3 className="text-base font-bold text-slate-800">No se encontraron bienes</h3>
                        <p className="text-xs text-slate-500 mt-1">Prueba ajustando los filtros de búsqueda.</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {bienes.map((item) => (
                            <div
                                key={item.id}
                                className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.02] transition-transform duration-200 shadow-sm"
                            >
                                <div className="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                                    {/* Icono del bien con gradiente original */}
                                    <div className="flex items-center justify-center flex-shrink-0 w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500 to-blue-900 text-white shadow-md">
                                        <span className="text-3xl">{getIconForDescription(item.descripcion)}</span>
                                    </div>

                                    {/* Información principal en columnas originales */}
                                    <div className="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 w-full">
                                        <div>
                                            <p className="text-xs text-blue-900 font-semibold mb-1">Sede</p>
                                            <p className="font-semibold text-slate-800 text-sm">{item.sede || 'Principal'}</p>
                                            <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Descripción</p>
                                            <p className="font-bold truncate text-sm text-slate-900" title={item.descripcion}>
                                                {item.descripcion || 'Sin descripción'}
                                            </p>
                                        </div>

                                        <div>
                                            <p className="text-xs text-blue-900 font-semibold mb-1">N° Interno</p>
                                            <p className="font-mono text-sm font-bold text-blue-950">{item.n_interno_bien || 'N/A'}</p>
                                            <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad Admin.</p>
                                            <p className="text-xs font-semibold text-slate-700">
                                                {item.unidad_administrativas ? item.unidad_administrativas.name : 'N/A'}
                                            </p>
                                        </div>

                                        <div>
                                            <p className="text-xs text-blue-900 font-semibold mb-1">Estado del Uso</p>
                                            <span className="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {item.estado_del_usos ? item.estado_del_usos.nombre : 'En Uso'}
                                            </span>
                                            <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Marca / Modelo</p>
                                            <p className="text-xs font-medium text-slate-700 truncate">
                                                {item.marca || 'N/A'} {item.modelo ? `- ${item.modelo}` : ''}
                                            </p>
                                        </div>

                                        <div>
                                            <p className="text-xs text-blue-900 font-semibold mb-1">Serial</p>
                                            <p className="font-mono text-xs text-slate-600 truncate">{item.serial || 'S/N'}</p>

                                            {/* Botones de acción */}
                                            <div className="flex flex-wrap gap-1.5 mt-3 justify-start sm:justify-end">
                                                <button
                                                    type="button"
                                                    onClick={() => openDetailModal(item)}
                                                    className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-100 hover:bg-blue-200 text-blue-900 transition-colors"
                                                    title="Ver Detalles"
                                                >
                                                    <span>👁️</span>
                                                    <span>Ver</span>
                                                </button>

                                                <button
                                                    type="button"
                                                    onClick={() => openQrModal(item)}
                                                    className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                                                    title="Ver Código QR"
                                                >
                                                    <span>📱</span>
                                                    <span>QR</span>
                                                </button>

                                                {permissions.can_edit && (
                                                    <a
                                                        href={`/administracion/bienes_publicos/${item.id}/edit`}
                                                        className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors"
                                                        title="Editar Bien"
                                                    >
                                                        <span>✏️</span>
                                                        <span>Editar</span>
                                                    </a>
                                                )}

                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(item)}
                                                        className="inline-flex items-center px-2 py-1.5 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors"
                                                        title="Eliminar Bien"
                                                    >
                                                        <span>🗑️</span>
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 mt-8">
                        <button
                            type="button"
                            onClick={() => fetchBienes(pagination.current_page - 1)}
                            disabled={pagination.current_page <= 1}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
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
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>

            {/* Modal de Detalle (React) */}
            {detailModalOpen && selectedBien && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 border border-slate-200 my-8">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-base font-bold text-blue-950">Ficha del Bien Público</h3>
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="space-y-2 text-xs">
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Descripción:</span>
                                <span className="font-bold text-slate-800 text-right">{selectedBien.descripcion}</span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">N° Interno:</span>
                                <span className="font-mono font-bold text-blue-900">{selectedBien.n_interno_bien || 'N/A'}</span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Sede:</span>
                                <span className="font-semibold text-slate-800">{selectedBien.sede || 'N/A'}</span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Unidad Administrativa:</span>
                                <span className="font-semibold text-slate-800">
                                    {selectedBien.unidad_administrativas?.name || 'N/A'}
                                </span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Marca / Modelo:</span>
                                <span className="font-semibold text-slate-800">
                                    {selectedBien.marca || 'N/A'} {selectedBien.modelo ? `/ ${selectedBien.modelo}` : ''}
                                </span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Serial:</span>
                                <span className="font-mono text-slate-700">{selectedBien.serial || 'N/A'}</span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Estado del Uso:</span>
                                <span className="font-semibold text-blue-900">
                                    {selectedBien.estado_del_usos?.nombre || 'En Uso'}
                                </span>
                            </div>
                            <div className="flex justify-between py-1.5 border-b border-slate-100">
                                <span className="text-slate-500 font-semibold">Fecha de Adquisición:</span>
                                <span className="font-semibold text-slate-800">
                                    {selectedBien.fecha_adquisicion ? selectedBien.fecha_adquisicion.split('T')[0] : 'N/A'}
                                </span>
                            </div>
                        </div>

                        <div className="mt-5 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal de Código QR */}
            {qrModalOpen && selectedBien && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-sm font-bold text-blue-950">Código QR del Bien</h3>
                            <button
                                type="button"
                                onClick={() => setQrModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="p-4 bg-slate-50 rounded-xl border border-slate-100 inline-block mb-3">
                            <div className="text-6xl my-4">📱</div>
                            <p className="font-mono text-xs font-bold text-blue-900">{selectedBien.codigo_qr || selectedBien.n_interno_bien || 'QR-CODE'}</p>
                        </div>

                        <p className="text-xs text-slate-500 mb-4">{selectedBien.descripcion}</p>

                        <button
                            type="button"
                            onClick={() => setQrModalOpen(false)}
                            className="w-full py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
