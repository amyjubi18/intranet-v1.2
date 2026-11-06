import React, { useState, useEffect, useCallback } from 'react';

export default function BienesPublicos() {
    const [bienes, setBienes] = useState([]);
    const [metrics, setMetrics] = useState({
        totalBienes: 0,
        bienesEnUso: 0,
        valorTotal: 0,
        ultimoBien: null
    });
    const [catalogs, setCatalogs] = useState({
        estado_del_usos: [],
        condiciones: [],
        unidad_administrativas: [],
        forma_adquisiciones: [],
        colors: [],
        descripciones: [],
        marcas: [],
        modelos: [],
        n_internos: [],
        usuarios_de_uso: [],
        usuarios_administrativos: []
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

    // Filtros
    const [search, setSearch] = useState('');
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Modal de detalle y QR
    const [selectedBien, setSelectedBien] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);
    const [qrModalOpen, setQrModalOpen] = useState(false);

    // Cargar datos desde la API
    const fetchBienes = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (search.trim()) params.append('search', search.trim());
            if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'fecha') {
                    if (fechaDesde) params.append('fecha_desde', fechaDesde);
                    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
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

            if (resData.metrics) setMetrics(resData.metrics);
            if (resData.catalogs) setCatalogs(resData.catalogs);
        } catch (err) {
            console.error('Error fetching bienes:', err);
            setError(err.message || 'No se pudieron cargar los registros de bienes.');
        } finally {
            setLoading(false);
        }
    }, [search, filterType, secondFilter, fechaDesde, fechaHasta]);

    // Debounce para búsqueda y filtros
    useEffect(() => {
        const timer = setTimeout(() => {
            fetchBienes(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, filterType, secondFilter, fechaDesde, fechaHasta, fetchBienes]);

    const handleClearFilters = () => {
        setSearch('');
        setFilterType('');
        setSecondFilter('');
        setFechaDesde('');
        setFechaHasta('');
    };

    const formatCurrency = (val) => {
        const num = parseFloat(val) || 0;
        return num.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    return (
        <div className="space-y-6">
            {/* Tarjetas de Métricas */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl shrink-0">
                        📦
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Bienes</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.totalBienes}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl shrink-0">
                        ✅
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Bienes en Uso</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.bienesEnUso}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl shrink-0">
                        💰
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor en Facturas</p>
                        <h3 className="text-xl font-extrabold text-slate-900 mt-0.5">Bs. {formatCurrency(metrics.valorTotal)}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-2xl shrink-0">
                        🕒
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Último Ingreso</p>
                        <h3 className="text-sm font-bold text-slate-900 mt-0.5 truncate max-w-[150px]">
                            {metrics.ultimoBien ? (metrics.ultimoBien.fecha_adquisicion || 'Reciente') : 'N/A'}
                        </h3>
                    </div>
                </div>
            </div>

            {/* Cabecera y Barra de Filtros */}
            <div className="bg-white/90 backdrop-blur rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>🏛️</span> Bienes Públicos / Muebles
                        </h2>
                        <p className="text-sm text-slate-500 mt-1">
                            Gestión, inventario patrimonial, asignación y códigos QR de la Corporación.
                        </p>
                    </div>

                    <div className="flex items-center gap-2.5 flex-wrap">
                        <button
                            type="button"
                            onClick={() => fetchBienes(pagination.current_page)}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                            title="Recargar datos"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        <a
                            href="/administracion/bienes_publicos/create"
                            className="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all"
                        >
                            <span>➕</span>
                            <span>Nuevo Bien</span>
                        </a>

                        <a
                            href="/administracion/bienes_publicos/export/excel"
                            className="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-all"
                        >
                            <span>📊</span>
                            <span>Excel</span>
                        </a>

                        <a
                            href="/administracion/bienes_publicos/export/pdf"
                            className="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-all"
                        >
                            <span>📄</span>
                            <span>PDF</span>
                        </a>
                    </div>
                </div>

                {/* Filtros Dinámicos */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
                    {/* Búsqueda General */}
                    <div className="relative">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por serial, marca, descripción..."
                            className="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        />
                        <svg className="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {/* Selector de Criterio */}
                    <div>
                        <select
                            value={filterType}
                            onChange={(e) => {
                                setFilterType(e.target.value);
                                setSecondFilter('');
                            }}
                            className="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Filtrar por categoría...</option>
                            <option value="unidad_administrativa_id">Unidad Administrativa</option>
                            <option value="usuario_de_uso_id">Usuario de Uso</option>
                            <option value="estado_del_uso_id">Estado del Uso</option>
                            <option value="condicion_id">Condición Física</option>
                            <option value="descripcion">Descripción</option>
                            <option value="marca">Marca</option>
                            <option value="modelo">Modelo</option>
                            <option value="fecha">Rango de Fechas</option>
                        </select>
                    </div>

                    {/* Filtro Secundario Dinámico */}
                    {filterType === 'fecha' ? (
                        <div className="flex gap-2">
                            <input
                                type="date"
                                value={fechaDesde}
                                onChange={(e) => setFechaDesde(e.target.value)}
                                className="w-1/2 px-2.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700"
                            />
                            <input
                                type="date"
                                value={fechaHasta}
                                onChange={(e) => setFechaHasta(e.target.value)}
                                className="w-1/2 px-2.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-700"
                            />
                        </div>
                    ) : (
                        <div>
                            <select
                                value={secondFilter}
                                onChange={(e) => setSecondFilter(e.target.value)}
                                disabled={!filterType}
                                className="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 disabled:opacity-50"
                            >
                                <option value="">Seleccione opción...</option>
                                {filterType === 'unidad_administrativa_id' && catalogs.unidad_administrativas.map(u => (
                                    <option key={u.id} value={u.id}>{u.unidad_administrativa}</option>
                                ))}
                                {filterType === 'usuario_de_uso_id' && catalogs.usuarios_de_uso.map(u => (
                                    <option key={u.id} value={u.id}>{u.nombre_usuario || u.name}</option>
                                ))}
                                {filterType === 'estado_del_uso_id' && catalogs.estado_del_usos.map(e => (
                                    <option key={e.id} value={e.id}>{e.estado_del_uso}</option>
                                ))}
                                {filterType === 'condicion_id' && catalogs.condiciones.map(c => (
                                    <option key={c.id} value={c.id}>{c.condicion}</option>
                                ))}
                                {filterType === 'descripcion' && catalogs.descripciones.map((d, i) => (
                                    <option key={i} value={d}>{d}</option>
                                ))}
                                {filterType === 'marca' && catalogs.marcas.map((m, i) => (
                                    <option key={i} value={m}>{m}</option>
                                ))}
                                {filterType === 'modelo' && catalogs.modelos.map((mo, i) => (
                                    <option key={i} value={mo}>{mo}</option>
                                ))}
                            </select>
                        </div>
                    )}

                    <div>
                        <button
                            type="button"
                            onClick={handleClearFilters}
                            className="w-full py-2.5 px-4 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            {/* Error Message */}
            {error && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between text-red-700">
                    <p className="text-sm font-semibold">{error}</p>
                    <button onClick={() => fetchBienes(pagination.current_page)} className="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg font-bold">
                        Reintentar
                    </button>
                </div>
            )}

            {/* Tabla de Bienes */}
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-left">
                        <thead className="bg-slate-50/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th className="px-5 py-3.5 whitespace-nowrap">N° Interno / QR</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Descripción</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Marca / Modelo</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Serial</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Unidad / Asignado</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Estado / Condición</th>
                                <th className="px-5 py-3.5 text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 text-sm">
                            {loading ? (
                                Array.from({ length: 5 }).map((_, idx) => (
                                    <tr key={idx} className="animate-pulse">
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-48"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-32"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-28"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-36"></div></td>
                                        <td className="px-5 py-4"><div className="h-6 bg-slate-200 rounded-full w-20"></div></td>
                                        <td className="px-5 py-4 text-right"><div className="h-4 bg-slate-200 rounded w-16 ml-auto"></div></td>
                                    </tr>
                                ))
                            ) : bienes.length === 0 ? (
                                <tr>
                                    <td colSpan="7" className="text-center py-12 text-slate-400">
                                        <div className="text-4xl mb-2">📦</div>
                                        <p className="font-semibold text-slate-600">No se encontraron bienes muebles</p>
                                        <p className="text-xs text-slate-400 mt-1">Verifica los filtros aplicados</p>
                                    </td>
                                </tr>
                            ) : (
                                bienes.map((bien) => {
                                    const estado = bien.estado_del_usos?.estado_del_uso || 'No especificado';
                                    const condicion = bien.condicions?.condicion || 'N/A';
                                    const unidad = bien.unidad_administrativas?.unidad_administrativa || 'Sin asignar';
                                    const usuarioUso = bien.usuario_de_usos?.nombre_usuario || 'Sin asignar';

                                    return (
                                        <tr key={bien.id} className="hover:bg-slate-50/70 transition-colors">
                                            <td className="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-blue-900 text-xs">
                                                <div className="flex items-center gap-1.5">
                                                    <span className="bg-blue-50 text-blue-800 px-2.5 py-1 rounded-md border border-blue-200">
                                                        {bien.n_interno_bien || `#${bien.id}`}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-5 py-3.5 font-semibold text-slate-800">
                                                {bien.descripcion || '-'}
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-600 whitespace-nowrap text-xs">
                                                <span className="font-medium text-slate-800">{bien.marca || '-'}</span>
                                                <span className="text-slate-400"> / {bien.modelo || '-'}</span>
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-500 font-mono text-xs whitespace-nowrap">
                                                {bien.serial || 'S/N'}
                                            </td>
                                            <td className="px-5 py-3.5 text-xs text-slate-600 max-w-xs truncate">
                                                <div className="font-medium text-slate-800 truncate">{unidad}</div>
                                                <div className="text-slate-400 truncate">👤 {usuarioUso}</div>
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap text-xs">
                                                <span className={`inline-flex px-2.5 py-0.5 rounded-full font-semibold border ${
                                                    estado.toLowerCase().includes('uso') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                    estado.toLowerCase().includes('desuso') ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                                    'bg-slate-100 text-slate-700 border-slate-200'
                                                }`}>
                                                    {estado}
                                                </span>
                                                <div className="text-[11px] text-slate-400 mt-0.5 font-medium">{condicion}</div>
                                            </td>
                                            <td className="px-5 py-3.5 text-right whitespace-nowrap text-xs">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setSelectedBien(bien);
                                                            setQrModalOpen(true);
                                                        }}
                                                        className="p-1.5 text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                                                        title="Ver Código QR"
                                                    >
                                                        📱
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setSelectedBien(bien);
                                                            setDetailModalOpen(true);
                                                        }}
                                                        className="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                    >
                                                        Detalles
                                                    </button>
                                                    <a
                                                        href={`/administracion/bienes_publicos/${bien.id}/edit`}
                                                        className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
                                                        title="Editar"
                                                    >
                                                        ✏️
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Paginación */}
                {pagination.total > 0 && (
                    <div className="px-5 py-4 bg-slate-50/50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div className="text-xs text-slate-500">
                            Mostrando <span className="font-semibold text-slate-800">{pagination.from || 0}</span> a{' '}
                            <span className="font-semibold text-slate-800">{pagination.to || 0}</span> de{' '}
                            <span className="font-semibold text-slate-800">{pagination.total}</span> bienes registrados
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1 || loading}
                                onClick={() => fetchBienes(pagination.current_page - 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-lg">
                                {pagination.current_page} / {pagination.last_page}
                            </span>
                            <button
                                type="button"
                                disabled={pagination.current_page >= pagination.last_page || loading}
                                onClick={() => fetchBienes(pagination.current_page + 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Detalle */}
            {detailModalOpen && selectedBien && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
                        <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                            <div>
                                <h3 className="text-lg font-bold text-slate-900">{selectedBien.descripcion}</h3>
                                <p className="text-xs text-slate-500 font-mono">N° Interno: {selectedBien.n_interno_bien}</p>
                            </div>
                            <button onClick={() => setDetailModalOpen(false)} className="text-slate-400 hover:text-slate-600 p-1.5">✕</button>
                        </div>
                        <div className="p-6 overflow-y-auto space-y-4 text-sm">
                            <div className="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl text-xs">
                                <div><strong className="text-slate-400 uppercase">Marca:</strong> <p className="text-slate-800 font-semibold mt-0.5">{selectedBien.marca || '-'}</p></div>
                                <div><strong className="text-slate-400 uppercase">Modelo:</strong> <p className="text-slate-800 font-semibold mt-0.5">{selectedBien.modelo || '-'}</p></div>
                                <div><strong className="text-slate-400 uppercase">Serial:</strong> <p className="text-slate-800 font-mono font-semibold mt-0.5">{selectedBien.serial || '-'}</p></div>
                                <div><strong className="text-slate-400 uppercase">N° Factura:</strong> <p className="text-slate-800 font-mono font-semibold mt-0.5">{selectedBien.n_factura || '-'}</p></div>
                                <div><strong className="text-slate-400 uppercase">Fecha Adquisición:</strong> <p className="text-slate-800 mt-0.5">{selectedBien.fecha_adquisicion || '-'}</p></div>
                                <div><strong className="text-slate-400 uppercase">Valor Factura:</strong> <p className="text-slate-800 font-semibold mt-0.5">Bs. {formatCurrency(selectedBien.valor_factura)}</p></div>
                                <div className="col-span-2"><strong className="text-slate-400 uppercase">Unidad Administrativa:</strong> <p className="text-slate-800 mt-0.5">{selectedBien.unidad_administrativas?.unidad_administrativa || '-'}</p></div>
                                <div className="col-span-2"><strong className="text-slate-400 uppercase">Usuario Asignado:</strong> <p className="text-slate-800 mt-0.5">{selectedBien.usuario_de_usos?.nombre_usuario || '-'}</p></div>
                            </div>
                        </div>
                        <div className="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button onClick={() => setDetailModalOpen(false)} className="px-4 py-2 bg-slate-200 hover:bg-slate-300 rounded-xl text-xs font-semibold">Cerrar</button>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal Código QR */}
            {qrModalOpen && selectedBien && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200">
                        <h4 className="font-bold text-slate-800 mb-1">Código QR del Bien</h4>
                        <p className="text-xs text-slate-500 font-mono mb-4">{selectedBien.n_interno_bien}</p>
                        <img
                            src={`https://quickchart.io/chart?cht=qr&chs=220x220&chl=${encodeURIComponent(window.location.origin + '/bienes/qr/' + encodeURIComponent(selectedBien.n_interno_bien))}`}
                            alt="Código QR"
                            className="mx-auto rounded-xl border border-slate-200 shadow-sm"
                        />
                        <button
                            type="button"
                            onClick={() => setQrModalOpen(false)}
                            className="mt-5 w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition-all shadow-sm"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
