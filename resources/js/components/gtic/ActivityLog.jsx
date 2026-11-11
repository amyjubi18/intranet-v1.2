import React, { useState, useEffect, useCallback } from 'react';

export default function ActivityLog() {
    const [activities, setActivities] = useState([]);
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
    const [search, setSearch] = useState('');
    const [selectedActivity, setSelectedActivity] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    // Cargar actividades desde la API
    const fetchActivities = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 15
            });
            if (search.trim()) params.append('search', search.trim());

            const res = await fetch(`/api/gtic/activity-log?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/login?timeout=1';
                    return;
                }
                throw new Error(`Error en el servidor (${res.status})`);
            }

            const json = await res.json();
            const resData = json.data?.activities || json.data || {};

            setActivities(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                from: resData.from || 0,
                to: resData.to || 0,
                per_page: resData.per_page || 15
            });
        } catch (err) {
            console.error('Error fetching activities:', err);
            setError(err.message || 'No se pudieron cargar las actividades.');
        } finally {
            setLoading(false);
        }
    }, [search]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchActivities(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, fetchActivities]);

    // Formato de fecha exacto original: d/m/Y h:i:s A
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;

        const pad = (n) => String(n).padStart(2, '0');
        const day = pad(d.getDate());
        const month = pad(d.getMonth() + 1);
        const year = d.getFullYear();

        let hours = d.getHours();
        const minutes = pad(d.getMinutes());
        const seconds = pad(d.getSeconds());
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const strHours = pad(hours);

        return `${day}/${month}/${year} ${strHours}:${minutes}:${seconds} ${ampm}`;
    };

    // Nombre simple del modelo: class_basename
    const getModelName = (subjectType) => {
        if (!subjectType) return 'N/A';
        const parts = subjectType.split('\\');
        return parts[parts.length - 1];
    };

    // Nombre de usuario causer
    const getCauserName = (causer) => {
        if (!causer) return 'Sistema';
        return causer.name || causer.username || 'Sistema';
    };

    // Badge de acción con los colores exactos originales
    const renderActionBadge = (event) => {
        let badgeClass = 'bg-gray-100 text-gray-800';
        let label = event || 'N/A';

        if (event === 'created') {
            badgeClass = 'bg-green-100 text-green-800';
            label = 'Registro';
        } else if (event === 'updated') {
            badgeClass = 'bg-blue-100 text-blue-800';
            label = 'Actualización';
        } else if (event === 'deleted') {
            badgeClass = 'bg-red-100 text-red-800';
            label = 'Eliminación';
        } else if (event === 'exported') {
            badgeClass = 'bg-purple-100 text-purple-800';
            label = 'Exportación';
        }

        return (
            <span className={`px-2 py-1 text-xs rounded-full font-medium ${badgeClass}`}>
                {label}
            </span>
        );
    };

    // Descripción con el formato exacto original
    const renderDescription = (activity) => {
        const username = getCauserName(activity.causer);
        const modelName = getModelName(activity.subject_type);

        if (activity.event === 'created') {
            return (
                <span>
                    <b className="text-blue-900">{username}</b> hizo un registro en el modelo {modelName}
                </span>
            );
        }
        if (activity.event === 'updated') {
            return (
                <span>
                    <b className="text-blue-900">{username}</b> realizó un cambio en el modelo {modelName}
                </span>
            );
        }
        if (activity.event === 'deleted') {
            return (
                <span>
                    <b className="text-blue-900">{username}</b> hizo una eliminación en {modelName}
                </span>
            );
        }

        return (
            <span>
                {activity.description || activity.properties?.url || '/'}
            </span>
        );
    };

    return (
        <div className="bg-white rounded-lg shadow-md p-6">
            {/* Cabecera con Buscador */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h1 className="text-2xl font-bold">Registro de Actividades</h1>

                <div className="relative">
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Buscar en el registro..."
                        className="w-full sm:w-64 pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800"
                    />
                    <svg className="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {/* Tabla de Actividades (Diseño Original Exacto) */}
            <div className="overflow-x-auto">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-gray-700">Cargando registros de actividad...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center text-red-600 bg-red-50 rounded-lg">
                        <p className="text-sm font-semibold mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchActivities(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : activities.length === 0 ? (
                    <div className="p-8 text-center text-gray-500">
                        No se encontraron registros de actividades.
                    </div>
                ) : (
                    <table className="min-w-full table-auto">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left whitespace-nowrap">Fecha</th>
                                <th className="px-4 py-2 text-left whitespace-nowrap">Usuario</th>
                                <th className="px-4 py-2 text-left whitespace-nowrap">Acción</th>
                                <th className="px-4 py-2 text-left whitespace-nowrap">Modelo</th>
                                <th className="px-4 py-2 text-left">Descripción</th>
                                <th className="px-4 py-2 text-left whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200">
                            {activities.map((activity) => (
                                <tr key={activity.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        {formatDate(activity.created_at)}
                                    </td>
                                    <td className="px-4 py-2 whitespace-nowrap text-bold text-blue-900">
                                        <b className="text-blue-900">{getCauserName(activity.causer)}</b>
                                    </td>
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        {renderActionBadge(activity.event)}
                                    </td>
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        {getModelName(activity.subject_type)}
                                    </td>
                                    <td className="px-4 py-2 break-words">
                                        {renderDescription(activity)}
                                    </td>
                                    <td className="px-4 py-2 whitespace-nowrap">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setSelectedActivity(activity);
                                                setDetailModalOpen(true);
                                            }}
                                            className="text-blue-600 hover:text-blue-800"
                                        >
                                            Ver detalles
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Pie de Tabla: Paginación y Botón Exportar PDF Original */}
            <div className="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    {pagination.last_page > 1 ? (
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => fetchActivities(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3 py-1.5 rounded border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="text-xs text-gray-600 px-2">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchActivities(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3 py-1.5 rounded border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    ) : (
                        <span className="text-xs text-gray-500">
                            Total: {pagination.total} registros
                        </span>
                    )}
                </div>

                <div className="flex space-x-2">
                    <a
                        href="/gtic/activity-log/export/pdf"
                        data-download-progress
                        data-filename="registro_actividades.pdf"
                        className="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors inline-flex items-center gap-1.5 text-sm cursor-pointer"
                    >
                        📄 Exportar PDF
                    </a>
                </div>
            </div>

            {/* MODAL DETALLES DE ACTIVIDAD (DISEÑO EXACTO A show.blade.php) */}
            {detailModalOpen && selectedActivity && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white rounded-2xl shadow-xl max-w-4xl w-full p-6 border border-gray-200 my-8 max-h-[90vh] overflow-y-auto animate-modal-expand">
                        {/* Cabecera del Modal */}
                        <div className="flex justify-between items-center mb-6">
                            <h1 className="text-2xl font-bold">Detalles de Actividad</h1>
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="bg-gray-500 text-white px-4 py-2 rounded-xl hover:bg-gray-600 text-sm transition-all shadow-sm"
                            >
                                ← Volver
                            </button>
                        </div>

                        {/* Grid de 2 Columnas Original */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Información General */}
                            <div>
                                <h2 className="text-lg font-semibold mb-4">Información General</h2>
                                <div className="space-y-3">
                                    <div>
                                        <label className="font-medium text-gray-700 block">Fecha:</label>
                                        <p>{formatDate(selectedActivity.created_at)}</p>
                                    </div>
                                    <div>
                                        <label className="font-medium text-gray-700 block">Usuario:</label>
                                        <p>{getCauserName(selectedActivity.causer)}</p>
                                    </div>
                                    <div>
                                        <label className="font-medium text-gray-700 block">Acción:</label>
                                        <div className="mt-1">{renderActionBadge(selectedActivity.event)}</div>
                                    </div>
                                    <div>
                                        <label className="font-medium text-gray-700 block">Modelo:</label>
                                        <p>{getModelName(selectedActivity.subject_type)}</p>
                                    </div>
                                    <div>
                                        <label className="font-medium text-gray-700 block">ID del Registro:</label>
                                        <p>{selectedActivity.subject_id || 'N/A'}</p>
                                    </div>
                                    <div>
                                        <label className="font-medium text-gray-700 block">Ruta:</label>
                                        <p className="break-all">{selectedActivity.properties?.url || '/'}</p>
                                    </div>
                                </div>
                            </div>

                            {/* Cambios Realizados */}
                            <div>
                                <h2 className="text-lg font-semibold mb-4">Cambios Realizados</h2>
                                {selectedActivity.properties && (selectedActivity.properties.old || selectedActivity.properties.attributes) ? (
                                    <div className="space-y-4">
                                        {selectedActivity.properties.old && (
                                            <div>
                                                <h3 className="font-medium text-red-700 mb-2">Valores Anteriores:</h3>
                                                <pre className="bg-red-50 p-3 rounded text-sm overflow-x-auto border border-red-100 text-red-950 font-mono max-h-48">
                                                    {JSON.stringify(selectedActivity.properties.old, null, 2)}
                                                </pre>
                                            </div>
                                        )}

                                        {selectedActivity.properties.attributes && (
                                            <div>
                                                <h3 className="font-medium text-green-700 mb-2">Valores Nuevos:</h3>
                                                <pre className="bg-green-50 p-3 rounded text-sm overflow-x-auto border border-green-100 text-green-950 font-mono max-h-48">
                                                    {JSON.stringify(selectedActivity.properties.attributes, null, 2)}
                                                </pre>
                                            </div>
                                        )}
                                    </div>
                                ) : (
                                    <p className="text-gray-500">No hay cambios registrados para esta actividad.</p>
                                )}
                            </div>
                        </div>

                        {/* Descripción */}
                        <div className="mt-6">
                            <h2 className="text-lg font-semibold mb-4">Descripción</h2>
                            <p className="bg-gray-50 p-4 rounded text-gray-800">
                                {renderDescription(selectedActivity)}
                            </p>
                        </div>

                        {/* Propiedades Adicionales si las hay */}
                        {selectedActivity.properties && Object.keys(selectedActivity.properties).length > 0 && (
                            <div className="mt-6">
                                <h2 className="text-lg font-semibold mb-4">Propiedades Adicionales</h2>
                                <pre className="bg-gray-50 p-4 rounded text-sm overflow-x-auto border border-gray-200 font-mono max-h-48">
                                    {JSON.stringify(selectedActivity.properties, null, 2)}
                                </pre>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
