import React, { useState, useEffect, useCallback } from 'react';

export default function InventarioOficinas() {
    const [inventario, setInventario] = useState([]);
    const [metrics, setMetrics] = useState({
        ultimasFechas: 'N/A',
        totalItemsRegistrados: 0,
        itemsEsteMes: 0,
        porcentajeItems: 0,
        stockBajo: 0,
        articulosStockBajo: []
    });
    const [catalogs, setCatalogs] = useState({
        proveedores: [],
        articulos: [],
        unidad_medidas: [],
        facturas: []
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Filtros
    const [search, setSearch] = useState('');
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    const [selectedItem, setSelectedItem] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    const fetchInventario = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (search.trim()) {
                params.append('filterType', 'busqueda');
                params.append('busqueda', search.trim());
            } else if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'fecha') {
                    if (fechaDesde) params.append('fecha_desde', fechaDesde);
                    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
                } else if (secondFilter) {
                    params.append('secondFilter', secondFilter);
                }
            }

            const response = await fetch(`/api/administracion/inventario_oficinas?${params.toString()}`, {
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
            const paginated = resData.inventario || {};

            setInventario(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 15
            });

            if (resData.metrics) setMetrics(resData.metrics);
            if (resData.catalogs) setCatalogs(resData.catalogs);
        } catch (err) {
            console.error('Error fetching inventario:', err);
            setError(err.message || 'No se pudo cargar el inventario de oficina.');
        } finally {
            setLoading(false);
        }
    }, [search, filterType, secondFilter, fechaDesde, fechaHasta]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchInventario(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, filterType, secondFilter, fechaDesde, fechaHasta, fetchInventario]);

    const handleClearFilters = () => {
        setSearch('');
        setFilterType('');
        setSecondFilter('');
        setFechaDesde('');
        setFechaHasta('');
    };

    return (
        <div className="space-y-6">
            {/* Métricas del Inventario */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl shrink-0">
                        📋
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Lotes Registrados</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.totalItemsRegistrados}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl shrink-0">
                        📥
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ingresos Este Mes</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.itemsEsteMes} <span className="text-xs font-normal text-slate-400">unids</span></h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className={`w-12 h-12 rounded-xl flex items-center justify-center text-2xl shrink-0 ${metrics.stockBajo > 0 ? 'bg-red-100 text-red-700 animate-pulse' : 'bg-slate-100 text-slate-600'}`}>
                        ⚠️
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock Crítico</p>
                        <h3 className={`text-2xl font-extrabold mt-0.5 ${metrics.stockBajo > 0 ? 'text-red-600' : 'text-slate-800'}`}>
                            {metrics.stockBajo} <span className="text-xs font-normal text-slate-400">artículos</span>
                        </h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl shrink-0">
                        📅
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Última Entrada</p>
                        <h3 className="text-sm font-bold text-slate-900 mt-0.5">{metrics.ultimasFechas}</h3>
                    </div>
                </div>
            </div>

            {/* Cabecera y Barra de Filtros */}
            <div className="bg-white/90 backdrop-blur rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>📦</span> Inventario de Oficina
                        </h2>
                        <p className="text-sm text-slate-500 mt-1">
                            Control de existencia de insumos, materiales de oficina, papelería y entradas de almacén.
                        </p>
                    </div>

                    <div className="flex items-center gap-2.5 flex-wrap">
                        <button
                            type="button"
                            onClick={() => fetchInventario(pagination.current_page)}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        <a
                            href="/administracion/inventario_oficinas/create"
                            className="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all"
                        >
                            <span>➕</span>
                            <span>Nueva Entrada</span>
                        </a>

                        <a
                            href="/administracion/salida_oficina"
                            className="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                        >
                            <span>📤</span>
                            <span>Salidas</span>
                        </a>
                    </div>
                </div>

                {/* Filtros */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
                    <div className="relative">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por artículo, proveedor, factura..."
                            className="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        />
                        <svg className="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

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
                            <option value="articulo_id">Artículo</option>
                            <option value="proveedore_id">Proveedor</option>
                            <option value="n_factura">N° Factura</option>
                            <option value="fecha">Fecha de Ingreso</option>
                        </select>
                    </div>

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
                                {filterType === 'articulo_id' && catalogs.articulos.map(a => (
                                    <option key={a.id} value={a.id}>{a.articulo}</option>
                                ))}
                                {filterType === 'proveedore_id' && catalogs.proveedores.map(p => (
                                    <option key={p.id} value={p.id}>{p.proveedor}</option>
                                ))}
                                {filterType === 'n_factura' && catalogs.facturas.map((f, i) => (
                                    <option key={i} value={f}>{f}</option>
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

            {/* Error Banner */}
            {error && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between text-red-700">
                    <p className="text-sm font-semibold">{error}</p>
                    <button onClick={() => fetchInventario(pagination.current_page)} className="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg font-bold">
                        Reintentar
                    </button>
                </div>
            )}

            {/* Tabla de Inventario */}
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-left">
                        <thead className="bg-slate-50/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th className="px-5 py-3.5 whitespace-nowrap">Artículo</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Proveedor</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">N° Factura</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Fecha Ingreso</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Unidades Ingresadas</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Disponibles</th>
                                <th className="px-5 py-3.5 text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 text-sm">
                            {loading ? (
                                Array.from({ length: 5 }).map((_, idx) => (
                                    <tr key={idx} className="animate-pulse">
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-44"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-36"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-28"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-16"></div></td>
                                        <td className="px-5 py-4"><div className="h-6 bg-slate-200 rounded-full w-20"></div></td>
                                        <td className="px-5 py-4 text-right"><div className="h-4 bg-slate-200 rounded w-16 ml-auto"></div></td>
                                    </tr>
                                ))
                            ) : inventario.length === 0 ? (
                                <tr>
                                    <td colSpan="7" className="text-center py-12 text-slate-400">
                                        <div className="text-4xl mb-2">📁</div>
                                        <p className="font-semibold text-slate-600">No se encontraron artículos en inventario</p>
                                    </td>
                                </tr>
                            ) : (
                                inventario.map((item) => {
                                    const articuloNombre = item.articulo?.articulo || item.articulos?.articulo || 'Sin nombre';
                                    const proveedorNombre = item.proveedores?.proveedor || '-';
                                    const unidadMedida = item.unidad_medidas?.unidad_medida || 'Unid';
                                    const disponibles = parseInt(item.unidades_disponibles) || 0;
                                    const ingresadas = parseInt(item.unidades) || 0;
                                    const stockMin = item.articulo?.stock_min || item.articulos?.stock_min || 5;
                                    const esCritico = disponibles <= stockMin;

                                    return (
                                        <tr key={item.id} className="hover:bg-slate-50/70 transition-colors">
                                            <td className="px-5 py-3.5 font-bold text-slate-800">
                                                {articuloNombre}
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-600 text-xs">
                                                {proveedorNombre}
                                            </td>
                                            <td className="px-5 py-3.5 font-mono text-xs text-blue-900 font-semibold">
                                                {item.n_factura || 'S/N'}
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-500 text-xs font-mono">
                                                {item.fecha_ingreso || '-'}
                                            </td>
                                            <td className="px-5 py-3.5 font-semibold text-slate-700 text-xs">
                                                {ingresadas} {unidadMedida}
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap text-xs">
                                                <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold border ${
                                                    disponibles === 0 ? 'bg-red-100 text-red-800 border-red-200' :
                                                    esCritico ? 'bg-amber-100 text-amber-800 border-amber-200' :
                                                    'bg-emerald-100 text-emerald-800 border-emerald-200'
                                                }`}>
                                                    <span>{disponibles === 0 ? '🔴' : esCritico ? '🟡' : '🟢'}</span>
                                                    <span>{disponibles} {unidadMedida}</span>
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-right whitespace-nowrap text-xs">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setSelectedItem(item);
                                                            setDetailModalOpen(true);
                                                        }}
                                                        className="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                    >
                                                        Detalles
                                                    </button>
                                                    <a
                                                        href={`/administracion/inventario_oficinas/${item.id}/edit`}
                                                        className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
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
                            <span className="font-semibold text-slate-800">{pagination.total}</span> registros
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1 || loading}
                                onClick={() => fetchInventario(pagination.current_page - 1)}
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
                                onClick={() => fetchInventario(pagination.current_page + 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Detalle */}
            {detailModalOpen && selectedItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                        <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                            <h3 className="text-lg font-bold text-slate-900">{selectedItem.articulo?.articulo || selectedItem.articulos?.articulo}</h3>
                            <button onClick={() => setDetailModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div className="p-6 space-y-3 text-xs">
                            <div className="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl">
                                <div><span className="text-slate-400 uppercase font-semibold">Proveedor:</span> <p className="font-bold text-slate-800 mt-0.5">{selectedItem.proveedores?.proveedor || '-'}</p></div>
                                <div><span className="text-slate-400 uppercase font-semibold">N° Factura:</span> <p className="font-bold text-blue-900 mt-0.5">{selectedItem.n_factura || '-'}</p></div>
                                <div><span className="text-slate-400 uppercase font-semibold">Fecha Ingreso:</span> <p className="font-medium text-slate-800 mt-0.5">{selectedItem.fecha_ingreso || '-'}</p></div>
                                <div><span className="text-slate-400 uppercase font-semibold">Unidades Ingresadas:</span> <p className="font-bold text-slate-800 mt-0.5">{selectedItem.unidades}</p></div>
                                <div><span className="text-slate-400 uppercase font-semibold">Disponibles Actuales:</span> <p className="font-bold text-emerald-700 mt-0.5">{selectedItem.unidades_disponibles}</p></div>
                                <div><span className="text-slate-400 uppercase font-semibold">Unidad Medida:</span> <p className="font-medium text-slate-800 mt-0.5">{selectedItem.unidad_medidas?.unidad_medida || '-'}</p></div>
                            </div>
                        </div>
                        <div className="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button onClick={() => setDetailModalOpen(false)} className="px-4 py-2 bg-slate-200 rounded-xl text-xs font-semibold">Cerrar</button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
