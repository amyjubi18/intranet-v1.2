import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';
import { downloadWithProgress } from '../../utils/downloadWithProgress';

export default function InventarioOficinas() {
    const [inventario, setInventario] = useState([]);
    const [metrics, setMetrics] = useState({
        ultimasFechas: 'N/A',
        totalItemsRegistrados: 0,
        stockBajo: 0,
        articulosStockBajo: [],
        articuloMasRegistros: null
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

    // Selección múltiple
    const [selectedIds, setSelectedIds] = useState([]);

    // Cargar datos desde la API
    const fetchInventario = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (busqueda.trim()) {
                params.append('filterType', 'busqueda');
                params.append('busqueda', busqueda.trim());
            } else if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'fecha' || filterType === 'fecha_creacion') {
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
                if (response.status === 401) {
                    window.location.href = '/login?timeout=1';
                    return;
                }
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

            setMetrics({
                ultimasFechas: resData.ultimasFechas || 'N/A',
                totalItemsRegistrados: resData.totalItemsRegistrados || paginated.total || 0,
                stockBajo: resData.stockBajo || 0,
                articulosStockBajo: resData.articulosStockBajo || [],
                articuloMasRegistros: resData.articuloMasRegistros || null
            });

            if (resData.catalogs) {
                setCatalogs(resData.catalogs);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching inventario:', err);
            setError(err.message || 'No se pudieron cargar los datos.');
        } finally {
            setLoading(false);
        }
    }, [filterType, secondFilter, busqueda, fechaDesde, fechaHasta]);

    useEffect(() => {
        fetchInventario(1);
    }, [fetchInventario]);

    const handleClearFilters = () => {
        setFilterType('');
        setSecondFilter('');
        setBusqueda('');
        setFechaDesde('');
        setFechaHasta('');
    };

    const toggleSelect = (id) => {
        setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
    };

    const handleDeleteSingle = async (item) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el registro de inventario con Factura #${item.n_factura || 'S/N'}`,
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
            const res = await fetch(`/api/administracion/inventario_oficinas/${item.id}`, {
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
                text: 'El registro fue eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchInventario(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el registro.'
            });
        }
    };

    // Agrupar items por artículo (como en la vista original)
    const groupedItems = inventario.reduce((acc, curr) => {
        const artId = curr.articulo_id || 'sin_articulo';
        if (!acc[artId]) {
            acc[artId] = {
                articulo: curr.articulos || { articulo: 'Artículo Desconocido' },
                items: []
            };
        }
        acc[artId].items.push(curr);
        return acc;
    }, {});

    return (
        <div className="w-full">
            {/* Tarjetas Superiores de Estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Última Fecha de Ingreso */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500 text-2xl">📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.ultimasFechas}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Última Fecha de Ingreso</p>
                    </div>

                    {/* Cantidad de Ingresos */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500 text-2xl">📦</span>
                            </div>
                            <span className="text-lg px-2.5 py-1 rounded-full bg-green-500/20 text-green-600 font-bold">
                                {metrics.totalItemsRegistrados}
                            </span>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalItemsRegistrados}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Cantidad de ingresos</p>
                    </div>

                    {/* Stock Bajo */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                                <span className="text-red-500 text-2xl">⚠️</span>
                            </div>
                            <span className="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-600 font-bold">
                                Alerta <b className="text-base">{metrics.stockBajo}</b>
                            </span>
                        </div>
                        <p className="text-sm text-blue-900 font-semibold mb-2">Stock Bajo</p>
                        <div className="mt-2 text-xs max-h-20 overflow-y-auto space-y-1">
                            {metrics.articulosStockBajo && metrics.articulosStockBajo.length > 0 ? (
                                metrics.articulosStockBajo.map((art, idx) => (
                                    <div key={idx} className="flex rounded-md items-center justify-between gap-2 p-1 bg-red-50 text-red-900">
                                        <span className="font-semibold truncate">{art.articulos?.articulo || 'Artículo'}</span>
                                        <span className="text-blue-900 font-bold">({art.total_disponibles})</span>
                                    </div>
                                ))
                            ) : (
                                <span className="text-blue-900 font-semibold text-xs">Sin artículos con stock bajo</span>
                            )}
                        </div>
                    </div>

                    {/* Artículo Más Registrado */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                                <span className="text-purple-500 text-2xl">🏆</span>
                            </div>
                            <span className="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-600 font-bold">
                                Top <b className="text-base">{metrics.articuloMasRegistros?.total_registros || 0}</b>
                            </span>
                        </div>
                        <p className="text-sm text-blue-900 font-semibold">Artículo Más Registrado</p>
                        <h3 className="text-xs font-bold rounded-md mt-2 p-2 bg-purple-500/10 text-purple-950 truncate">
                            {metrics.articuloMasRegistros?.articulos?.articulo || 'N/A'}
                        </h3>
                    </div>
                </div>
            </div>

            {/* Filtros y Controles (Diseño Original Exacto) */}
            <div className="px-6 mb-6">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex flex-col gap-4">
                        {/* Fila de acciones */}
                        <div className="flex flex-wrap items-center justify-end gap-3 pb-4 border-b border-gray-100">
                            <select
                                value=""
                                onChange={(e) => {
                                    if (e.target.value) downloadWithProgress(e.target.value);
                                }}
                                className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs"
                            >
                                <option value="" disabled>📤 Exportar</option>
                                <option value="/administracion/inventario_oficinas/export/excel">📊 Exportar a Excel</option>
                                <option value="/administracion/inventario_oficinas/export/pdf">📄 Exportar a PDF</option>
                            </select>

                            {permissions.can_create && (
                                <a
                                    href="/administracion/inventario_oficinas/create"
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    <span>➕</span>
                                    <span>Nuevo Registro</span>
                                </a>
                            )}
                        </div>

                        {/* Fila de filtros */}
                        <div className="flex flex-wrap items-center gap-4">
                            <button
                                type="button"
                                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                className="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none"
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
                                    <option value="fecha">Fecha de Factura</option>
                                    <option value="fecha_creacion">Fecha de Ingreso</option>
                                    <option value="proveedore_id">Proveedor</option>
                                    <option value="articulo_id">Artículo</option>
                                    <option value="unidad_medida_id">Unidad de Medida</option>
                                    <option value="n_factura">Factura</option>
                                    <option value="busqueda">Búsqueda General</option>
                                </select>
                            )}

                            {filterType === 'proveedore_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar Proveedor</option>
                                    {(catalogs.proveedores || []).map(p => (
                                        <option key={p.id} value={p.id}>{p.proveedor}</option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'articulo_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar Artículo</option>
                                    {(catalogs.articulos || []).map(a => (
                                        <option key={a.id} value={a.id}>
                                            {String(a.id).padStart(4, '0')} - {a.articulo}
                                        </option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'unidad_medida_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl px-4 py-2 text-sm"
                                >
                                    <option value="">Seleccionar Unidad</option>
                                    {(catalogs.unidad_medidas || []).map(u => (
                                        <option key={u.id} value={u.id}>{u.unidad_medida}</option>
                                    ))}
                                </select>
                            )}

                            {(filterType === 'fecha' || filterType === 'fecha_creacion') && (
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

                            {filterType === 'n_factura' && (
                                <input
                                    type="text"
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    placeholder="N° de factura..."
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

            {/* Listado en Tarjetas Agrupadas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando inventario de oficina...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchInventario(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : Object.keys(groupedItems).length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">📦</div>
                        <h3 className="text-base font-bold text-slate-800">No hay registros de inventario</h3>
                        <p className="text-xs text-slate-500 mt-1">No se encontraron artículos con los filtros aplicados.</p>
                    </div>
                ) : (
                    Object.entries(groupedItems).map(([artId, group]) => {
                        const totalDisp = group.items.reduce((acc, it) => acc + (Number(it.unidades_disponibles) || 0), 0);
                        const isLow = group.articulo?.stock_min && totalDisp < group.articulo.stock_min;

                        return (
                            <div key={artId} className="mb-6">
                                {/* Banner de Cabecera del Artículo */}
                                <div className={`bg-gradient-to-r ${isLow ? 'from-red-900 to-red-800' : 'from-indigo-900 to-blue-800'} text-white rounded-2xl p-4 shadow-lg relative mb-2`}>
                                    {isLow && (
                                        <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                            ⚠️ Stock Bajo
                                        </div>
                                    )}
                                    <div className="flex items-center gap-4">
                                        <div className="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center text-3xl">
                                            📦
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-bold">{group.articulo?.articulo || 'Artículo'}</h3>
                                            <p className="text-blue-100 text-xs">
                                                Registros Agrupados: <span className="font-semibold">{group.items.length}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Filas / Tarjetas de cada registro dentro del grupo */}
                                {group.items.map((singleItem) => {
                                    const percent = singleItem.unidades > 0
                                        ? Math.min(100, Math.round((singleItem.unidades_disponibles / singleItem.unidades) * 100))
                                        : 0;

                                    return (
                                        <div
                                            key={singleItem.id}
                                            className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform duration-200 ml-4 sm:ml-8 mb-4 shadow-sm"
                                        >
                                            <div className="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                                                {permissions.can_destroy && (
                                                    <div className="flex-shrink-0 pt-2">
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedIds.includes(singleItem.id)}
                                                            onChange={() => toggleSelect(singleItem.id)}
                                                            className="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                                        />
                                                    </div>
                                                )}

                                                <div className="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Última Fecha de Factura</p>
                                                        <p className="font-semibold text-slate-800 text-sm">
                                                            {singleItem.fecha_ingreso
                                                                ? singleItem.fecha_ingreso.split('T')[0].split('-').reverse().join('/')
                                                                : 'N/A'}
                                                        </p>
                                                        <p className="text-xs text-blue-900 font-semibold mt-2">Item #</p>
                                                        <p className="font-mono text-sm font-bold text-blue-950">
                                                            {String(singleItem.articulo_id || 0).padStart(4, '0')}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Artículo</p>
                                                        <p className="text-sm font-bold text-slate-900 truncate">
                                                            {group.articulo?.articulo || 'N/A'}
                                                        </p>
                                                        <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad de Medida</p>
                                                        <p className="text-xs font-medium text-slate-700">
                                                            {singleItem.unidad_medidas ? singleItem.unidad_medidas.unidad_medida : 'N/A'}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Proveedor</p>
                                                        <p className="text-xs font-semibold text-slate-800">
                                                            {singleItem.proveedores ? singleItem.proveedores.proveedor : 'N/A'}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <div className="text-left md:text-right">
                                                            <p className="text-xs text-black font-bold mb-1">Unidades Registradas</p>
                                                            <div className="flex items-center gap-2 justify-start md:justify-end">
                                                                <span className={`text-2xl font-bold ${singleItem.unidades_disponibles <= 5 ? 'text-red-500' : 'text-green-600'}`}>
                                                                    {singleItem.unidades_disponibles}
                                                                </span>
                                                                <span className="text-xs text-blue-900 font-semibold">
                                                                    de {singleItem.unidades}
                                                                </span>
                                                            </div>
                                                            <div className="w-full bg-gray-200 rounded-full h-2 mt-2">
                                                                <div
                                                                    className={`${percent < 25 ? 'bg-red-500' : 'bg-green-500'} h-2 rounded-full`}
                                                                    style={{ width: `${percent}%` }}
                                                                ></div>
                                                            </div>
                                                        </div>

                                                        <div className="text-left md:text-right mt-2">
                                                            <p className="text-xs text-blue-900 font-semibold mb-1">Nº de Factura</p>
                                                            <span className="text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded font-mono">
                                                                {singleItem.n_factura || 'S/N'}
                                                            </span>
                                                        </div>

                                                        {/* Botones de acción */}
                                                        <div className="flex flex-wrap justify-start md:justify-end gap-2 mt-3">
                                                            {permissions.can_edit && (
                                                                <a
                                                                    href={`/administracion/inventario_oficinas/${singleItem.id}/edit`}
                                                                    className="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold bg-green-600 text-white hover:bg-green-700 text-xs transition-colors"
                                                                >
                                                                    <span>✏️</span>
                                                                    <span>Editar</span>
                                                                </a>
                                                            )}

                                                            {permissions.can_destroy && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleDeleteSingle(singleItem)}
                                                                    className="inline-flex items-center px-2.5 py-1.5 rounded-xl font-semibold bg-red-600 text-white hover:bg-red-700 text-xs transition-colors"
                                                                >
                                                                    <span>🗑️</span>
                                                                </button>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        );
                    })
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 mt-8">
                        <button
                            type="button"
                            onClick={() => fetchInventario(pagination.current_page - 1)}
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
                            onClick={() => fetchInventario(pagination.current_page + 1)}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
