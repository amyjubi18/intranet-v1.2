import React, { useState, useEffect, useCallback } from 'react';

export default function Consumibles() {
    const [cartuchos, setCartuchos] = useState([]);
    const [catalogs, setCatalogs] = useState({
        impresoras: [],
        colores: [],
        fabricantes: [],
        gerencias: []
    });
    const [permissions, setPermissions] = useState({
        can_create: false,
        can_edit: false,
        can_destroy: false
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
    const [impresoraFilter, setImpresoraFilter] = useState('');
    const [colorFilter, setColorFilter] = useState('');
    const [tipoFilter, setTipoFilter] = useState('');
    const [gerenciaFilter, setGerenciaFilter] = useState('');

    // Modal
    const [selectedCartucho, setSelectedCartucho] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    // Fetch Consumibles
    const fetchConsumibles = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });

            if (search.trim()) params.append('search', search.trim());
            if (impresoraFilter) params.append('nombre-impresora', impresoraFilter);
            if (colorFilter) params.append('color', colorFilter);
            if (tipoFilter) params.append('tipo', tipoFilter);
            if (gerenciaFilter) params.append('gerencia_id', gerenciaFilter);

            const response = await fetch(`/api/administracion/consumibles?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('No tienes permisos suficientes para ver consumibles.');
                }
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const json = await response.json();
            const resData = json.data || {};
            const paginated = resData.cartuchos || {};

            setCartuchos(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 15
            });

            if (resData.catalogs) setCatalogs(resData.catalogs);
            if (resData.permissions) setPermissions(resData.permissions);
        } catch (err) {
            console.error('Error fetching consumibles:', err);
            setError(err.message || 'No se pudieron cargar los consumibles.');
        } finally {
            setLoading(false);
        }
    }, [search, impresoraFilter, colorFilter, tipoFilter, gerenciaFilter]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchConsumibles(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, impresoraFilter, colorFilter, tipoFilter, gerenciaFilter, fetchConsumibles]);

    const handleClearFilters = () => {
        setSearch('');
        setImpresoraFilter('');
        setColorFilter('');
        setTipoFilter('');
        setGerenciaFilter('');
    };

    return (
        <div className="space-y-6">
            {/* Panel de Filtros y Acciones */}
            <div className="bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span>🖨️</span>
                            <span>Consumibles de Impresoras</span>
                        </h2>
                        <p className="text-xs text-slate-500 mt-1">
                            Control y catálogo de cartuchos, tóneres, impresoras y niveles mínimos requeridos.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            onClick={() => fetchConsumibles(pagination.current_page)}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        {/* Botón Nuevo Consumible (Restringido por permiso cartucho_create) */}
                        {permissions.can_create && (
                            <a
                                href="/administracion/consumibles/create"
                                className="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Nuevo Consumible</span>
                            </a>
                        )}
                    </div>
                </div>

                {/* Filtros en Tiempo Real */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mt-5">
                    {/* Búsqueda Rápida */}
                    <div className="relative">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar cartucho, modelo..."
                            className="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        />
                        <svg className="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="10" cy="10" r="7" strokeWidth="2"/>
                            <path d="M21 21l-4.35-4.35" strokeWidth="2" strokeLinecap="round"/>
                        </svg>
                    </div>

                    {/* Filtro por Impresora */}
                    <div>
                        <select
                            value={impresoraFilter}
                            onChange={(e) => setImpresoraFilter(e.target.value)}
                            className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Todas las Impresoras...</option>
                            {(catalogs.impresoras || []).map((imp) => (
                                <option key={imp.id} value={imp.id}>{imp.impresora}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro por Color */}
                    <div>
                        <select
                            value={colorFilter}
                            onChange={(e) => setColorFilter(e.target.value)}
                            className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Todos los Colores...</option>
                            {(catalogs.colores || []).map((col) => (
                                <option key={col.id} value={col.id}>{col.colore}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro por Fabricante / Tipo */}
                    <div>
                        <select
                            value={tipoFilter}
                            onChange={(e) => setTipoFilter(e.target.value)}
                            className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Todos los Fabricantes...</option>
                            {(catalogs.fabricantes || []).map((fab) => (
                                <option key={fab.id} value={fab.id}>{fab.fabricante}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro por Gerencia */}
                    <div className="flex items-center gap-2">
                        <select
                            value={gerenciaFilter}
                            onChange={(e) => setGerenciaFilter(e.target.value)}
                            className="flex-1 px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Todas las Gerencias...</option>
                            {(catalogs.gerencias || []).map((g) => (
                                <option key={g.id} value={g.id}>{g.gerencia || g.name}</option>
                            ))}
                        </select>
                        {(search || impresoraFilter || colorFilter || tipoFilter || gerenciaFilter) && (
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="px-2.5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-semibold"
                                title="Limpiar filtros"
                            >
                                ✕
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Error */}
            {error && (
                <div className="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center justify-between text-xs">
                    <div className="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{error}</span>
                    </div>
                    <button
                        onClick={() => fetchConsumibles(1)}
                        className="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold"
                    >
                        Reintentar
                    </button>
                </div>
            )}

            {/* Tabla de Consumibles */}
            <div className="bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-slate-50 border-b border-slate-200 text-slate-600 text-[11px] uppercase tracking-wider font-bold">
                                <th className="px-5 py-4">ID</th>
                                <th className="px-5 py-4">Consumible / Modelo</th>
                                <th className="px-5 py-4">Color</th>
                                <th className="px-5 py-4">Impresora Compatible</th>
                                <th className="px-5 py-4">Fabricante / Tipo</th>
                                <th className="px-5 py-4">Stock Mínimo</th>
                                <th className="px-5 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 text-slate-700 text-xs">
                            {loading && cartuchos.length === 0 ? (
                                [...Array(6)].map((_, i) => (
                                    <tr key={i} className="animate-pulse">
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-8"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-36"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-20"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-32"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-16"></div></td>
                                        <td className="px-5 py-4 text-right"><div className="h-4 bg-slate-200 rounded w-16 ml-auto"></div></td>
                                    </tr>
                                ))
                            ) : cartuchos.length === 0 ? (
                                <tr>
                                    <td colSpan="7" className="text-center py-12 text-slate-400">
                                        <div className="text-4xl mb-2">🖨️</div>
                                        <p className="font-semibold text-slate-600">No se encontraron consumibles</p>
                                        <p className="text-xs text-slate-400 mt-1">Intenta con otros términos o limpia los filtros</p>
                                    </td>
                                </tr>
                            ) : (
                                cartuchos.map((item) => {
                                    const colorName = item.colore?.colore || 'N/A';
                                    const impName = item.impresora?.impresora || 'Sin asignar';
                                    const fabName = item.fabricante?.fabricante || 'Genérico';

                                    return (
                                        <tr key={item.id} className="hover:bg-slate-50/70 transition-colors">
                                            <td className="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-blue-900 text-xs">
                                                #{item.id}
                                            </td>
                                            <td className="px-5 py-3.5 font-bold text-slate-800">
                                                {item.cartucho}
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap">
                                                <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full font-semibold border bg-slate-50 text-slate-800 border-slate-200 text-xs">
                                                    <span
                                                        className="w-2.5 h-2.5 rounded-full border border-black/10"
                                                        style={{
                                                            backgroundColor: colorName.toLowerCase().includes('negro') || colorName.toLowerCase().includes('black') ? '#000000' :
                                                                colorName.toLowerCase().includes('cyan') || colorName.toLowerCase().includes('azul') ? '#06b6d4' :
                                                                colorName.toLowerCase().includes('magenta') || colorName.toLowerCase().includes('rojo') ? '#ec4899' :
                                                                colorName.toLowerCase().includes('amarillo') || colorName.toLowerCase().includes('yellow') ? '#eab308' :
                                                                '#94a3b8'
                                                        }}
                                                    ></span>
                                                    <span>{colorName}</span>
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-700 font-medium">
                                                {impName}
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-600">
                                                {fabName}
                                            </td>
                                            <td className="px-5 py-3.5 font-mono font-semibold">
                                                <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                                    {item.stock_min ?? 0} unid.
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-right whitespace-nowrap text-xs">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setSelectedCartucho(item);
                                                            setDetailModalOpen(true);
                                                        }}
                                                        className="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                    >
                                                        Detalles
                                                    </button>
                                                    {permissions.can_edit && (
                                                        <a
                                                            href={`/administracion/consumibles/${item.id}/edit`}
                                                            className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
                                                            title="Editar"
                                                        >
                                                            ✏️
                                                        </a>
                                                    )}
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
                            <span className="font-semibold text-slate-800">{pagination.total}</span> consumibles
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1 || loading}
                                onClick={() => fetchConsumibles(pagination.current_page - 1)}
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
                                onClick={() => fetchConsumibles(pagination.current_page + 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Detalle */}
            {detailModalOpen && selectedCartucho && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-modal-backdrop">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200 animate-modal-expand">
                        <div className="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h3 className="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <span>🖨️</span>
                                <span>{selectedCartucho.cartucho}</span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="p-6 space-y-3 text-xs">
                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-0.5">Impresora Compatible:</span>
                                <span className="font-bold text-slate-800">{selectedCartucho.impresora?.impresora || 'Sin asignar'}</span>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <span className="text-slate-400 block mb-0.5">Color:</span>
                                    <span className="font-bold text-slate-800">{selectedCartucho.colore?.colore || 'N/A'}</span>
                                </div>
                                <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <span className="text-slate-400 block mb-0.5">Fabricante:</span>
                                    <span className="font-bold text-slate-800">{selectedCartucho.fabricante?.fabricante || 'Genérico'}</span>
                                </div>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-0.5">Stock Mínimo Configurado:</span>
                                <span className="font-mono font-bold text-blue-900 text-sm">{selectedCartucho.stock_min ?? 0} unidades</span>
                            </div>
                        </div>

                        <div className="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2">
                            {permissions.can_edit && (
                                <a
                                    href={`/administracion/consumibles/${selectedCartucho.id}/edit`}
                                    className="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-colors"
                                >
                                    Editar Consumible
                                </a>
                            )}
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-200 hover:bg-slate-300 rounded-xl transition-colors"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
