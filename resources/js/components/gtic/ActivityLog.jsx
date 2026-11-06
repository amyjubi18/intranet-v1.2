import React, { useState, useEffect, useCallback } from 'react';

export default function ActivityLog() {
    const [activities, setActivities] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
        per_page: 20
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [eventFilter, setEventFilter] = useState('');
    const [selectedActivity, setSelectedActivity] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    // Fetch activities from API
    const fetchActivities = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 20,
            });
            if (search.trim()) params.append('search', search.trim());
            if (eventFilter) params.append('event', eventFilter);

            const response = await fetch(`/api/gtic/activity-log?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const json = await response.json();
            const paginatedData = json.data || {};

            setActivities(paginatedData.data || []);
            setPagination({
                current_page: paginatedData.current_page || 1,
                last_page: paginatedData.last_page || 1,
                total: paginatedData.total || 0,
                from: paginatedData.from || 0,
                to: paginatedData.to || 0,
                per_page: paginatedData.per_page || 20
            });
        } catch (err) {
            console.error('Error fetching activity log:', err);
            setError(err.message || 'No se pudieron cargar los registros de actividades.');
        } finally {
            setLoading(false);
        }
    }, [search, eventFilter]);

    // Initial load and filter change
    useEffect(() => {
        const timer = setTimeout(() => {
            fetchActivities(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, eventFilter, fetchActivities]);

    // Helper for event badge styling
    const getEventBadge = (event) => {
        switch (event) {
            case 'created':
                return { label: 'Registro', bg: 'bg-emerald-100 text-emerald-800 border-emerald-200' };
            case 'updated':
                return { label: 'Actualización', bg: 'bg-blue-100 text-blue-800 border-blue-200' };
            case 'deleted':
                return { label: 'Eliminación', bg: 'bg-red-100 text-red-800 border-red-200' };
            case 'exported':
                return { label: 'Exportación', bg: 'bg-purple-100 text-purple-800 border-purple-200' };
            default:
                return { label: event || 'Acción', bg: 'bg-slate-100 text-slate-800 border-slate-200' };
        }
    };

    // Helper for subject model name
    const formatModelName = (subjectType) => {
        if (!subjectType) return 'N/A';
        const parts = subjectType.split('\\');
        return parts[parts.length - 1];
    };

    // Helper for date formatting
    const formatDate = (dateString) => {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('es-VE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    };

    // Open detail modal
    const handleViewDetail = (activity) => {
        setSelectedActivity(activity);
        setDetailModalOpen(true);
    };

    return (
        <div className="space-y-6">
            {/* Header con acciones */}
            <div className="bg-white/90 backdrop-blur rounded-2xl p-6 shadow-sm border border-slate-200/80">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <span>🛡️</span> Registro de Actividades
                        </h2>
                        <p className="text-sm text-slate-500 mt-1">
                            Auditoría en tiempo real de cambios, accesos y eventos en el sistema.
                        </p>
                    </div>

                    <div className="flex items-center gap-2.5 flex-wrap">
                        <button
                            type="button"
                            onClick={() => fetchActivities(pagination.current_page)}
                            className="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                            title="Recargar datos"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        <a
                            href="/gtic/activity-log/export/pdf"
                            className="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-sm"
                        >
                            <span>📄</span>
                            <span>Exportar PDF</span>
                        </a>
                    </div>
                </div>

                {/* Barra de Filtros */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-3 mt-6 pt-5 border-t border-slate-100">
                    <div className="relative md:col-span-2">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar por usuario, descripción o modelo..."
                            className="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-slate-800"
                        />
                        <svg className="w-5 h-5 absolute left-3.5 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div>
                        <select
                            value={eventFilter}
                            onChange={(e) => setEventFilter(e.target.value)}
                            className="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all text-slate-800"
                        >
                            <option value="">Todos los eventos</option>
                            <option value="created">Registro (Created)</option>
                            <option value="updated">Actualización (Updated)</option>
                            <option value="deleted">Eliminación (Deleted)</option>
                            <option value="exported">Exportación (Exported)</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Estado de Error */}
            {error && (
                <div className="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between text-red-700">
                    <div className="flex items-center gap-3">
                        <span className="text-xl">⚠️</span>
                        <p className="text-sm font-semibold">{error}</p>
                    </div>
                    <button
                        onClick={() => fetchActivities(pagination.current_page)}
                        className="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 font-semibold transition-all"
                    >
                        Reintentar
                    </button>
                </div>
            )}

            {/* Tabla de Actividades */}
            <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-left">
                        <thead className="bg-slate-50/80 text-xs font-bold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th className="px-5 py-3.5 whitespace-nowrap">Fecha y Hora</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Usuario</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Acción</th>
                                <th className="px-5 py-3.5 whitespace-nowrap">Módulo / Modelo</th>
                                <th className="px-5 py-3.5">Descripción del Evento</th>
                                <th className="px-5 py-3.5 text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 text-sm">
                            {loading ? (
                                Array.from({ length: 5 }).map((_, idx) => (
                                    <tr key={idx} className="animate-pulse">
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-28"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-24"></div></td>
                                        <td className="px-5 py-4"><div className="h-6 bg-slate-200 rounded-full w-20"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-28"></div></td>
                                        <td className="px-5 py-4"><div className="h-4 bg-slate-200 rounded w-64"></div></td>
                                        <td className="px-5 py-4 text-right"><div className="h-4 bg-slate-200 rounded w-16 ml-auto"></div></td>
                                    </tr>
                                ))
                            ) : activities.length === 0 ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-12 text-slate-400">
                                        <div className="text-4xl mb-2">🔍</div>
                                        <p className="font-semibold text-slate-600">No se encontraron actividades</p>
                                        <p className="text-xs text-slate-400 mt-1">Intenta con otros filtros o términos de búsqueda</p>
                                    </td>
                                </tr>
                            ) : (
                                activities.map((activity) => {
                                    const badge = getEventBadge(activity.event);
                                    const username = activity.causer ? (activity.causer.username || activity.causer.name) : 'Sistema';
                                    const model = formatModelName(activity.subject_type);

                                    return (
                                        <tr key={activity.id} className="hover:bg-slate-50/70 transition-colors">
                                            <td className="px-5 py-3.5 whitespace-nowrap text-slate-500 font-mono text-xs">
                                                {formatDate(activity.created_at)}
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap font-bold text-blue-900">
                                                {username}
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${badge.bg}`}>
                                                    {badge.label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 whitespace-nowrap text-slate-700 font-medium">
                                                <span className="px-2 py-1 bg-slate-100 rounded-md text-xs font-mono">
                                                    {model}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3.5 text-slate-600 max-w-md break-words">
                                                {activity.description ? (
                                                    activity.description
                                                ) : (
                                                    <span>
                                                        <strong className="text-slate-800">{username}</strong> ejecutó una acción sobre <code className="text-blue-700 font-mono text-xs">{model}</code>
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-5 py-3.5 text-right whitespace-nowrap">
                                                <button
                                                    type="button"
                                                    onClick={() => handleViewDetail(activity)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all"
                                                >
                                                    <span>Ver detalle</span>
                                                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Barra de Paginación */}
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
                                onClick={() => fetchActivities(pagination.current_page - 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                            >
                                Anterior
                            </button>

                            <span className="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 rounded-lg">
                                {pagination.current_page} / {pagination.last_page}
                            </span>

                            <button
                                type="button"
                                disabled={pagination.current_page >= pagination.last_page || loading}
                                onClick={() => fetchActivities(pagination.current_page + 1)}
                                className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Detalle de Actividad */}
            {detailModalOpen && selectedActivity && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
                        {/* Header */}
                        <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                            <div className="flex items-center gap-3">
                                <span className="text-2xl">📋</span>
                                <div>
                                    <h3 className="text-lg font-bold text-slate-800">Detalle de Actividad #{selectedActivity.id}</h3>
                                    <p className="text-xs text-slate-500 font-mono">{formatDate(selectedActivity.created_at)}</p>
                                </div>
                            </div>
                            <button
                                onClick={() => setDetailModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Contenido */}
                        <div className="p-6 overflow-y-auto space-y-4 text-sm">
                            <div className="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div>
                                    <span className="text-xs text-slate-400 uppercase font-semibold">Usuario:</span>
                                    <p className="font-bold text-blue-900 mt-0.5">
                                        {selectedActivity.causer ? (selectedActivity.causer.username || selectedActivity.causer.name) : 'Sistema'}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-xs text-slate-400 uppercase font-semibold">Acción:</span>
                                    <div className="mt-0.5">
                                        <span className={`inline-flex px-2 py-0.5 rounded text-xs font-semibold ${getEventBadge(selectedActivity.event).bg}`}>
                                            {getEventBadge(selectedActivity.event).label}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span className="text-xs text-slate-400 uppercase font-semibold">Modelo:</span>
                                    <p className="font-mono text-slate-800 mt-0.5">{formatModelName(selectedActivity.subject_type)}</p>
                                </div>
                                <div>
                                    <span className="text-xs text-slate-400 uppercase font-semibold">ID Registro:</span>
                                    <p className="font-mono text-slate-800 mt-0.5">{selectedActivity.subject_id || '-'}</p>
                                </div>
                            </div>

                            {selectedActivity.properties && Object.keys(selectedActivity.properties).length > 0 && (
                                <div>
                                    <h4 className="text-xs uppercase font-bold text-slate-500 mb-2">Propiedades / Cambios:</h4>
                                    <pre className="p-4 bg-slate-900 text-emerald-400 rounded-xl text-xs font-mono overflow-x-auto max-h-60">
                                        {JSON.stringify(selectedActivity.properties, null, 2)}
                                    </pre>
                                </div>
                            )}
                        </div>

                        {/* Footer */}
                        <div className="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-xl text-xs transition-all"
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
