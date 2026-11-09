import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function PermisosManagement() {
    const [permissions, setPermissions] = useState([]);
    const [authPermissions, setAuthPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Filtros y orden
    const [search, setSearch] = useState('');
    const [sort, setSort] = useState('name');
    const [direction, setDirection] = useState('asc');

    // Modales
    const [showModal, setShowModal] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [editingPermissionId, setEditingPermissionId] = useState(null);

    // Formulario
    const [permissionName, setPermissionName] = useState('');
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Cargar permisos
    const fetchPermissions = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page, sort, direction });
            if (search.trim()) params.append('search', search.trim());

            const res = await fetch(`/api/gtic/permisos?${params.toString()}`, {
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
            const resData = json.data || {};
            const paginated = resData.permissions || {};

            setPermissions(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0
            });

            if (resData.auth_permissions) {
                setAuthPermissions(resData.auth_permissions);
            }
        } catch (err) {
            console.error('Error fetching permissions:', err);
            setError(err.message || 'No se pudieron cargar los permisos.');
        } finally {
            setLoading(false);
        }
    }, [search, sort, direction]);

    useEffect(() => {
        fetchPermissions(1);
    }, [fetchPermissions]);

    const toggleSort = () => {
        setDirection(prev => prev === 'asc' ? 'desc' : 'asc');
    };

    // Abrir Modal Crear
    const openCreateModal = () => {
        setIsEditing(false);
        setEditingPermissionId(null);
        setPermissionName('');
        setFormErrors({});
        setShowModal(true);
    };

    // Abrir Modal Editar
    const openEditModal = (permission) => {
        setIsEditing(true);
        setEditingPermissionId(permission.id);
        setPermissionName(permission.name || '');
        setFormErrors({});
        setShowModal(true);
    };

    // Guardar Permiso (Crear / Actualizar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFormErrors({});

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = isEditing ? `/api/gtic/permisos/${editingPermissionId}` : '/api/gtic/permisos';
            const method = isEditing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name: permissionName })
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'Error al procesar el permiso.');
            }

            setShowModal(false);
            Swal.fire({
                icon: 'success',
                title: isEditing ? '¡Permiso Actualizado!' : '¡Permiso Creado!',
                text: isEditing ? 'El permiso se actualizó correctamente.' : 'El permiso fue registrado exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchPermissions(pagination.current_page);
        } catch (err) {
            console.error('Error saving permission:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el permiso.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar Permiso
    const handleDeletePermission = async (permission) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el permiso "${permission.name}" permanentemente.`,
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
            const res = await fetch(`/api/gtic/permisos/${permission.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'No se pudo eliminar el permiso.');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'El permiso fue eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchPermissions(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el permiso.'
            });
        }
    };

    return (
        <div className="w-full">
            {/* Barra Superior de Filtros y Controles Original */}
            <div className="px-6 mb-6">
                <div className="p-6 bg-white/80 border border-slate-200 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar permiso..."
                                    className="w-64 px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-slate-800"
                                />
                            </div>

                            {search && (
                                <button
                                    type="button"
                                    onClick={() => setSearch('')}
                                    className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold bg-blue-900 text-white hover:bg-blue-800 text-sm transition-all"
                                >
                                    <span>🗑️</span>
                                    <span>Limpiar</span>
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1 ml-2">
                                <span className="text-xs text-gray-500 font-medium">Ordenar por:</span>
                                <button
                                    type="button"
                                    onClick={toggleSort}
                                    className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg bg-blue-100 text-blue-800 font-semibold hover:bg-blue-200 transition-colors"
                                >
                                    <span>Nombre</span>
                                    <span>{direction === 'asc' ? '⬆️' : '⬇️'}</span>
                                </button>
                            </div>
                        </div>

                        {/* Botón Nuevo */}
                        {authPermissions.can_create && (
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold bg-blue-900 text-white hover:bg-blue-800 text-sm shadow-sm transition-all active:translate-y-0.5"
                            >
                                <span>➕</span>
                                <span>Nuevo</span>
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Permisos en Tarjetas Originales */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando permisos...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchPermissions(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : permissions.length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">🔑</div>
                        <h3 className="text-base font-bold text-slate-800">No hay ningún permiso registrado</h3>
                        <p className="text-xs text-slate-500 mt-1">Prueba con otra búsqueda o añade un nuevo permiso.</p>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {permissions.map(perm => (
                            <div
                                key={perm.id}
                                className="bg-white/90 border border-slate-200/80 backdrop-blur-md rounded-2xl p-5 hover:scale-[1.01] transition-transform shadow-sm"
                            >
                                <div className="grid items-center justify-between grid-cols-1 gap-4 lg:grid-cols-2">
                                    {/* Permiso */}
                                    <div className="text-center lg:text-left">
                                        <p className="mb-1 text-xs font-semibold text-blue-900">🔑 Permiso</p>
                                        <p className="text-base font-bold text-slate-900 font-mono bg-blue-50/60 px-3 py-1.5 rounded-xl border border-blue-100 inline-block">
                                            {perm.name}
                                        </p>
                                    </div>

                                    {/* Acciones */}
                                    <div className="text-center lg:text-right">
                                        <div className="flex justify-center lg:justify-end gap-2">
                                            {authPermissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => openEditModal(perm)}
                                                    className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-semibold bg-green-600 text-white hover:bg-green-700 text-xs transition-colors"
                                                >
                                                    <span>✏️</span>
                                                    <span>Editar</span>
                                                </button>
                                            )}

                                            {authPermissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDeletePermission(perm)}
                                                    className="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold bg-red-600 text-white hover:bg-red-700 text-xs transition-colors"
                                                >
                                                    <span>🗑️</span>
                                                    <span>Eliminar</span>
                                                </button>
                                            )}
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
                            onClick={() => fetchPermissions(pagination.current_page - 1)}
                            disabled={pagination.current_page <= 1}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Anterior
                        </button>
                        <span className="text-xs font-bold text-blue-900 px-3">
                            Página {pagination.current_page} de {pagination.last_page} ({pagination.total} permisos)
                        </span>
                        <button
                            type="button"
                            onClick={() => fetchPermissions(pagination.current_page + 1)}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>

            {/* MODAL DE CREAR / EDITAR PERMISO (DISEÑO ORIGINAL EXACTO) */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                    <div className="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl max-w-lg w-full p-6 sm:p-8 border-2 border-blue-500/20 my-8">
                        {/* Cabecera con icono original */}
                        <div className="text-center mb-6 relative">
                            <button
                                type="button"
                                onClick={() => setShowModal(false)}
                                className="absolute right-0 top-0 text-slate-400 hover:text-slate-600 font-bold text-xl p-1"
                            >
                                ✕
                            </button>

                            <div className="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3 bg-blue-500/20 shadow-sm">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-500">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <h2 className="text-2xl font-extrabold text-blue-900">
                                {isEditing ? 'Editar Permiso' : 'Crear Permiso'}
                            </h2>
                            <p className="text-slate-500 text-xs mt-1">
                                {isEditing ? 'Modifica el nombre del permiso en el sistema' : 'Registra un nuevo permiso en el sistema'}
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-blue-950 mb-2">Nombre del Permiso:</label>
                                <input
                                    type="text"
                                    required
                                    autoFocus
                                    placeholder="Ingrese el nombre del permiso (ej: usuarios_ver)..."
                                    value={permissionName}
                                    onChange={(e) => setPermissionName(e.target.value)}
                                    className="w-full p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                />
                                {formErrors.name && <div className="text-red-500 text-xs mt-1">{formErrors.name[0]}</div>}
                            </div>

                            {/* Botones de Acción Originales */}
                            <div className="flex items-center justify-between gap-4 pt-6 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={() => setShowModal(false)}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer shadow-md"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" className="mr-1 fill-white" viewBox="0 0 24 24">
                                        <path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path>
                                    </svg>
                                    <span>Regresar</span>
                                </button>

                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 14px rgba(99, 102, 241, 0.35)'
                                    }}
                                >
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="mr-1">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                        <polyline points="17,21 17,13 7,13 7,21"/>
                                        <polyline points="7,3 7,8 15,8"/>
                                    </svg>
                                    <span>{submitting ? 'Guardando...' : (isEditing ? 'Guardar Cambios' : 'Guardar')}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
