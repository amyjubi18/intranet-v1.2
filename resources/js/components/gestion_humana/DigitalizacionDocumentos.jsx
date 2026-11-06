import React, { useState, useEffect, useCallback } from 'react';

export default function DigitalizacionDocumentos() {
    const [digitalizaciones, setDigitalizaciones] = useState([]);
    const [metrics, setMetrics] = useState({
        total: 0,
        totalDocumentos: 0
    });
    const [areasAdscritas, setAreasAdscritas] = useState([]);
    const [tiposDocumentos, setTiposDocumentos] = useState([]);
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
    const [permissions, setPermissions] = useState({ can_create: false, can_edit: false, can_destroy: false });

    // Filtros
    const [search, setSearch] = useState('');
    const [areaFilter, setAreaFilter] = useState('');
    const [tipoUsuarioFilter, setTipoUsuarioFilter] = useState('');

    const [selectedExpediente, setSelectedExpediente] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    const fetchDigitalizaciones = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (search.trim()) params.append('search_general', search.trim());
            if (areaFilter) {
                params.append('filterType', 'area_adscrita_id');
                params.append('secondFilter', areaFilter);
            } else if (tipoUsuarioFilter) {
                params.append('filterType', 'tipo_usuario');
                params.append('secondFilter', tipoUsuarioFilter);
            }

            const response = await fetch(`/api/gestion-humana/digitalizacion_documentos?${params.toString()}`, {
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
            const paginated = resData.digitalizaciones || {};

            setDigitalizaciones(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 15
            });

            if (resData.areas_adscritas) setAreasAdscritas(resData.areas_adscritas);
            if (resData.tipo_documentos) setTiposDocumentos(resData.tipo_documentos);
            if (resData.permissions) setPermissions(resData.permissions);
            setMetrics({
                total: resData.total || paginated.total || 0,
                totalDocumentos: resData.totalDocumentos || 0
            });
        } catch (err) {
            console.error('Error fetching digitalizaciones:', err);
            setError(err.message || 'No se pudieron cargar los expedientes.');
        } finally {
            setLoading(false);
        }
    }, [search, areaFilter, tipoUsuarioFilter]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchDigitalizaciones(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, areaFilter, tipoUsuarioFilter, fetchDigitalizaciones]);

    const handleClearFilters = () => {
        setSearch('');
        setAreaFilter('');
        setTipoUsuarioFilter('');
    };

    return (
        <div className="space-y-6">
            {/* Métricas */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl shrink-0">
                        📁
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Expedientes Digitalizados</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.total}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl shrink-0">
                        📄
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Documentos PDF Guardados</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{metrics.totalDocumentos}</h3>
                    </div>
                </div>

                <div className="bg-white/90 backdrop-blur rounded-2xl p-5 shadow-sm border border-slate-200 flex items-center gap-4">
                    <div className="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl shrink-0">
                        🏢
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Áreas Adscritas</p>
                        <h3 className="text-2xl font-extrabold text-slate-900 mt-0.5">{areasAdscritas.length}</h3>
                    </div>
                </div>
            </div>

            {/* Cabecera y Filtros */}
            <div className="bg-white/90 backdrop-blur rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>📑</span> Digitalización de Documentos
                        </h2>
                        <p className="text-sm text-slate-500 mt-1">
                            Gestión de expedientes digitales del personal, contratos y documentación laboral (OGH).
                        </p>
                    </div>

                    <div className="flex items-center gap-2.5 flex-wrap">
                        <button
                            type="button"
                            onClick={() => fetchDigitalizaciones(pagination.current_page)}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        {permissions.can_create && (
                            <a
                                href="/gestion-humana/digitalizacion_documentos/create"
                                className="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all"
                            >
                                <span>➕</span>
                                <span>Nueva Digitalización</span>
                            </a>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-100">
                    <div className="relative sm:col-span-2">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por cédula, nombre o área..."
                            className="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        />
                        <svg className="w-5 h-5 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div>
                        <select
                            value={areaFilter}
                            onChange={(e) => setAreaFilter(e.target.value)}
                            className="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Todas las Áreas</option>
                            {(Array.isArray(areasAdscritas) ? areasAdscritas : []).map(area => (
                                <option key={area.id} value={area.id}>{area.area_adscrita}</option>
                            ))}
                        </select>
                    </div>

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
                    <button onClick={() => fetchDigitalizaciones(pagination.current_page)} className="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg font-bold">
                        Reintentar
                    </button>
                </div>
            )}

            {/* Tabla de Expedientes */}
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-left">
                        <thead className="bg-slate-50/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th className="px-5 py-3.5 whitespace-nowrap">Cédula</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Nombres y Apellidos</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Área Adscrita</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Documentos Digitalizados</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Fecha Registro</th>
                                <th className="px-5 py-3.5 text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 text-sm">
                            {loading ? (
                                Array.from({ length: 5 }).map((_, idx) => (
                                    <tr key={idx} className="animate-pulse">
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-48"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-36"></div></td>
                                        <td className="px-5 py-4"><div className="h-6 bg-slate-200 rounded-full w-28"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4 text-right"><div className="h-4 bg-slate-200 rounded w-16 ml-auto"></div></td>
                                    </tr>
                                ))
                            ) : digitalizaciones.length === 0 ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-12 text-slate-400">
                                        <div className="text-4xl mb-2">📂</div>
                                        <p className="font-semibold text-slate-600">No se encontraron expedientes</p>
                                    </td>
                                </tr>
                            ) : (
                                digitalizaciones.map((dig) => {
                                    const area = dig.area_adscrita?.area_adscrita || 'Sin área asignada';
                                    const docsCount = dig.documentos ? dig.documentos.length : 0;

                                    return (
                                        <tr key={dig.id} className="hover:bg-slate-50/70 transition-colors">
                                            <td className="px-5 py-3.5 font-mono font-bold text-blue-900 text-xs whitespace-nowrap">
                                                V-{dig.cedula}
                                            </td>
                                            <td className="px-5 py-3.5 font-bold text-slate-800">
                                                {dig.nombre_apellido}
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-600 text-xs">
                                                <span className="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md border border-slate-200">
                                                    {area}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap text-xs">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setSelectedExpediente(dig);
                                                        setDetailModalOpen(true);
                                                    }}
                                                    className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-bold border transition-colors ${
                                                        docsCount > 0 ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' : 'bg-slate-100 text-slate-500 border-slate-200'
                                                    }`}
                                                >
                                                    <span>📎</span>
                                                    <span>{docsCount} {docsCount === 1 ? 'documento' : 'documentos'}</span>
                                                </button>
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-500 font-mono text-xs whitespace-nowrap">
                                                {dig.created_at ? new Date(dig.created_at).toLocaleDateString('es-VE') : '-'}
                                            </td>
                                            <td className="px-5 py-3.5 text-right whitespace-nowrap text-xs">
                                                <div className="flex items-center justify-end gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setSelectedExpediente(dig);
                                                            setDetailModalOpen(true);
                                                        }}
                                                        className="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                    >
                                                        Ver PDFs
                                                    </button>
                                                    {permissions.can_edit && (
                                                        <a
                                                            href={`/gestion-humana/digitalizacion_documentos/${dig.id}/edit`}
                                                            className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
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
                            <span className="font-semibold text-slate-800">{pagination.total}</span> expedientes
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1 || loading}
                                onClick={() => fetchDigitalizaciones(pagination.current_page - 1)}
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
                                onClick={() => fetchDigitalizaciones(pagination.current_page + 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Documentos Adjuntos */}
            {detailModalOpen && selectedExpediente && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-xl w-full max-h-[80vh] flex flex-col overflow-hidden border border-slate-200">
                        <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                            <div>
                                <h3 className="text-lg font-bold text-slate-900">{selectedExpediente.nombre_apellido}</h3>
                                <p className="text-xs text-slate-500 font-mono">Cédula: V-{selectedExpediente.cedula}</p>
                            </div>
                            <button onClick={() => setDetailModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div className="p-6 overflow-y-auto space-y-3">
                            <h4 className="text-xs uppercase font-bold text-slate-500 mb-2">Documentos Adjuntos:</h4>
                            {selectedExpediente.documentos && selectedExpediente.documentos.length > 0 ? (
                                <div className="space-y-2">
                                    {selectedExpediente.documentos.map((doc) => (
                                        <div key={doc.id} className="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                            <div className="flex items-center gap-2.5">
                                                <span className="text-xl">📄</span>
                                                <div>
                                                    <p className="font-bold text-slate-800">{doc.tipo_documento?.tipo_documento || 'Documento PDF'}</p>
                                                    <p className="text-[11px] text-slate-400 font-mono truncate max-w-xs">{doc.nombre_archivo || 'archivo.pdf'}</p>
                                                </div>
                                            </div>
                                            {doc.ruta_archivo && (
                                                <a
                                                    href={`/storage/${doc.ruta_archivo}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                                                >
                                                    Abrir
                                                </a>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-sm text-slate-400 text-center py-6">No hay archivos PDF digitalizados en este expediente.</p>
                            )}
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
