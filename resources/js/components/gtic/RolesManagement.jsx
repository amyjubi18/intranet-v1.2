import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function RolesManagement() {
    const [roles, setRoles] = useState([]);
    const [availablePermissions, setAvailablePermissions] = useState([]);
    const [permissions, setPermissions] = useState({
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
    const [editingRoleId, setEditingRoleId] = useState(null);

    // Formulario
    const [roleName, setRoleName] = useState('');
    const [selectedPermissions, setSelectedPermissions] = useState([]); // Array de IDs
    const [selectedPermToAdd, setSelectedPermToAdd] = useState('');
    const [permSearch, setPermSearch] = useState('');
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Cargar datos
    const fetchRoles = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page, sort, direction });
            if (search.trim()) params.append('search', search.trim());

            const res = await fetch(`/api/gtic/roles?${params.toString()}`, {
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
            const rolesPaginated = resData.roles || {};

            setRoles(rolesPaginated.data || []);
            setPagination({
                current_page: rolesPaginated.current_page || 1,
                last_page: rolesPaginated.last_page || 1,
                total: rolesPaginated.total || 0
            });

            if (resData.available_permissions) {
                setAvailablePermissions(resData.available_permissions);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching roles:', err);
            setError(err.message || 'No se pudieron cargar los roles.');
        } finally {
            setLoading(false);
        }
    }, [search, sort, direction]);

    useEffect(() => {
        fetchRoles(1);
    }, [fetchRoles]);

    const toggleSort = () => {
        setDirection(prev => prev === 'asc' ? 'desc' : 'asc');
    };

    // Abrir Modal Crear
    const openCreateModal = () => {
        setIsEditing(false);
        setEditingRoleId(null);
        setRoleName('');
        setSelectedPermissions([]);
        setSelectedPermToAdd('');
        setPermSearch('');
        setFormErrors({});
        setShowModal(true);
    };

    // Abrir Modal Editar
    const openEditModal = (role) => {
        setIsEditing(true);
        setEditingRoleId(role.id);
        setRoleName(role.name || '');
        const rolePermIds = (role.permissions || []).map(p => Number(p.id));
        setSelectedPermissions(rolePermIds);
        setSelectedPermToAdd('');
        setPermSearch('');
        setFormErrors({});
        setShowModal(true);
    };

    // Agregar permiso desde el selector
    const handleAddPermission = () => {
        if (!selectedPermToAdd) return;
        const pId = Number(selectedPermToAdd);
        if (!selectedPermissions.includes(pId)) {
            setSelectedPermissions([...selectedPermissions, pId]);
        }
        setSelectedPermToAdd('');
    };

    // Remover permiso tag
    const handleRemovePermission = (idToRemove) => {
        setSelectedPermissions(selectedPermissions.filter(id => id !== idToRemove));
    };

    // Aplicar todos los permisos (Modo Administrador)
    const handleApplyAllPermissions = () => {
        const allIds = availablePermissions.map(p => Number(p.id));
        setSelectedPermissions(allIds);
    };

    // Limpiar todos los permisos seleccionados
    const handleClearPermissions = () => {
        setSelectedPermissions([]);
    };

    // Guardar Rol (Crear / Actualizar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFormErrors({});

        if (selectedPermissions.length === 0) {
            setFormErrors({ permissions: ['Debe seleccionar al menos un permiso para el rol.'] });
            setSubmitting(false);
            return;
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = isEditing ? `/api/gtic/roles/${editingRoleId}` : '/api/gtic/roles';
            const method = isEditing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    name: roleName,
                    permissions: selectedPermissions
                })
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'Error al procesar el rol.');
            }

            setShowModal(false);
            Swal.fire({
                icon: 'success',
                title: isEditing ? '¡Rol Actualizado!' : '¡Rol Guardado!',
                text: isEditing ? 'Los cambios se guardaron exitosamente.' : 'El rol ha sido registrado con éxito.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchRoles(pagination.current_page);
        } catch (err) {
            console.error('Error saving role:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el rol.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar Rol
    const handleDeleteRole = async (role) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el rol "${role.name}" y se removerá de los usuarios asignados.`,
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
            const res = await fetch(`/api/gtic/roles/${role.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'No se pudo eliminar el rol.');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'El rol fue eliminado exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchRoles(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el rol.'
            });
        }
    };

    return (
        <div className="w-full">
            {/* Barra Superior de Filtros Original */}
            <div className="px-4 sm:px-6 pb-4">
                <div className="bg-white/80 border border-blue-500/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-sm mb-6">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            {/* Buscador */}
                            <div className="relative">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400">
                                    <circle cx="10" cy="10" r="7"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar rol o permiso..."
                                    className="w-56 sm:w-64 pl-8 pr-3 py-1.5 text-xs rounded-xl border border-blue-200 bg-white/90 text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            {/* Ordenar */}
                            <button
                                type="button"
                                onClick={toggleSort}
                                className="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-100 hover:bg-blue-200 transition-colors text-xs font-semibold text-blue-900"
                            >
                                <span>{direction === 'asc' ? '↑' : '↓'}</span>
                                <span>Nombre</span>
                            </button>

                            {search && (
                                <button
                                    type="button"
                                    onClick={() => setSearch('')}
                                    className="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-all"
                                >
                                    <span>🗑️</span>
                                    <span>Limpiar</span>
                                </button>
                            )}
                        </div>

                        {/* Botón Añadir Rol */}
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 transition-all shadow-sm active:translate-y-0.5"
                            >
                                <span>➕</span>
                                <span>Añadir Rol</span>
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Tarjetas de Roles (Diseño Original Exacto) */}
            <div className="px-4 sm:px-6 pb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando roles...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchRoles(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : roles.length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">🛡️</div>
                        <h3 className="text-base font-bold text-slate-800">No se encontraron roles</h3>
                        <p className="text-xs text-slate-500 mt-1">Prueba con otra búsqueda o añade un nuevo rol.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
                        {roles.map(role => {
                            const perms = role.permissions || [];
                            return (
                                <div
                                    key={role.id}
                                    className="bg-white/85 border border-blue-500/15 backdrop-blur-md rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/10 flex flex-col justify-between"
                                >
                                    <div>
                                        {/* ID Badge */}
                                        <div className="mb-2">
                                            <span className="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold text-white shadow-sm" style={{ background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)' }}>
                                                ID: {role.id}
                                            </span>
                                        </div>

                                        {/* Nombre del Rol */}
                                        <h3 className="text-base font-bold text-blue-900 mb-3 break-words">
                                            {role.name}
                                        </h3>

                                        {/* Permisos */}
                                        <div className="mb-3">
                                            <p className="text-[11px] font-semibold text-slate-500 flex items-center gap-1 mb-2">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                                </svg>
                                                Permisos ({perms.length}):
                                            </p>
                                            <div className="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                                                {perms.length > 0 ? (
                                                    perms.map(p => (
                                                        <span
                                                            key={p.id}
                                                            className="inline-block px-2 py-0.5 rounded-lg text-[10px] font-medium bg-blue-50 text-blue-900 border border-blue-200"
                                                        >
                                                            {p.name}
                                                        </span>
                                                    ))
                                                ) : (
                                                    <span className="text-[11px] text-slate-400 italic">Sin permisos asignados</span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Botones de Acción */}
                                    <div className="flex items-center gap-2 pt-3 mt-3 border-t border-slate-100">
                                        {permissions.can_edit && (
                                            <button
                                                type="button"
                                                onClick={() => openEditModal(role)}
                                                className="flex-1 py-1.5 px-3 rounded-xl text-xs font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors flex items-center justify-center gap-1"
                                            >
                                                <span>✏️</span>
                                                <span>Editar</span>
                                            </button>
                                        )}

                                        {permissions.can_destroy && (
                                            <button
                                                type="button"
                                                onClick={() => handleDeleteRole(role)}
                                                className="py-1.5 px-3 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors flex items-center justify-center gap-1"
                                                title="Eliminar Rol"
                                            >
                                                <span>🗑️</span>
                                                <span>Eliminar</span>
                                            </button>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 mt-8">
                        <button
                            type="button"
                            onClick={() => fetchRoles(pagination.current_page - 1)}
                            disabled={pagination.current_page <= 1}
                            className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Anterior
                        </button>
                        <span className="text-xs font-bold text-blue-900 px-3">
                            Página {pagination.current_page} de {pagination.last_page} ({pagination.total} roles)
                        </span>
                        <button
                            type="button"
                            onClick={() => fetchRoles(pagination.current_page + 1)}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>

            {/* MODAL DE REGISTRO / EDICIÓN DE ROL (DISEÑO ORIGINAL EXACTO) */}
            {showModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl max-w-2xl w-full p-6 sm:p-8 border-2 border-blue-500/20 my-8 max-h-[92vh] overflow-y-auto animate-modal-expand">
                        {/* Cabecera Flotante con Icono en Gradiente */}
                        <div className="text-center mb-6 relative">
                            <button
                                type="button"
                                onClick={() => setShowModal(false)}
                                className="modal-close-btn absolute right-0 top-0"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>

                            <div
                                className="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg"
                                style={{ background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)' }}
                            >
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    <path d="M9 12l2 2 4-4" />
                                </svg>
                            </div>
                            <h2 className="text-2xl font-extrabold text-blue-900">
                                {isEditing ? `Editar Rol: ${roleName}` : 'Configuración de Rol'}
                            </h2>
                            <p className="text-slate-500 text-xs mt-1">
                                {isEditing ? 'Modifica el nombre y permisos del rol' : 'Define el nombre y los permisos para el nuevo rol'}
                            </p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Nombre del Rol */}
                            <div>
                                <label className="block text-sm font-bold text-blue-950 mb-1">Nombre del Rol *</label>
                                <input
                                    type="text"
                                    required
                                    placeholder="Ej: Gerente_OGH"
                                    value={roleName}
                                    onChange={(e) => setRoleName(e.target.value)}
                                    className="w-full p-3 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                />
                                <p className="text-[11px] text-slate-400 mt-1">Solo letras y guiones bajos (ej. Director_OGA)</p>
                                {formErrors.name && <div className="text-red-500 text-xs mt-1">{formErrors.name[0]}</div>}
                            </div>

                            {/* Permisos del Rol */}
                            <div>
                                <div className="flex items-center justify-between mb-2">
                                    <label className="text-sm font-bold text-blue-950">Permisos del Rol *</label>
                                    <div className="flex items-center gap-2">
                                        <button
                                            type="button"
                                            onClick={handleApplyAllPermissions}
                                            className="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold text-blue-900 bg-blue-100 hover:bg-blue-200 transition-colors"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                                <path d="M9 12l2 2 4-4" />
                                            </svg>
                                            <span>Administrador (Todos)</span>
                                        </button>
                                        {selectedPermissions.length > 0 && (
                                            <button
                                                type="button"
                                                onClick={handleClearPermissions}
                                                className="text-xs text-slate-500 hover:text-red-600 transition-colors"
                                            >
                                                Limpiar
                                            </button>
                                        )}
                                    </div>
                                </div>

                                {/* Selector interactivo original */}
                                <div className="border-2 border-blue-500/20 rounded-2xl p-4 bg-blue-50/20">
                                    <div className="flex gap-2 mb-3">
                                        <select
                                            value={selectedPermToAdd}
                                            onChange={(e) => setSelectedPermToAdd(e.target.value)}
                                            className="flex-1 p-2.5 text-xs rounded-xl border border-slate-200 bg-white text-blue-950 focus:outline-none focus:border-blue-900"
                                        >
                                            <option value="">Seleccionar permiso para agregar...</option>
                                            {availablePermissions
                                                .filter(p => !selectedPermissions.includes(Number(p.id)))
                                                .map(p => (
                                                    <option key={p.id} value={p.id}>{p.name}</option>
                                                ))}
                                        </select>
                                        <button
                                            type="button"
                                            onClick={handleAddPermission}
                                            disabled={!selectedPermToAdd}
                                            className="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition-all disabled:opacity-40"
                                        >
                                            + Agregar
                                        </button>
                                    </div>

                                    {/* Contenedor de permisos agregados con tags azules originales */}
                                    <div className="flex flex-wrap gap-1.5 min-h-[90px] max-h-48 overflow-y-auto p-2 bg-white rounded-xl border border-slate-100">
                                        {selectedPermissions.length > 0 ? (
                                            selectedPermissions.map(pId => {
                                                const permObj = availablePermissions.find(p => Number(p.id) === pId);
                                                const pName = permObj ? permObj.name : `Permiso #${pId}`;
                                                return (
                                                    <div
                                                        key={pId}
                                                        className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-900 border border-blue-200"
                                                    >
                                                        <span>{pName}</span>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleRemovePermission(pId)}
                                                            className="text-blue-900 hover:text-red-600 font-bold ml-1"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                );
                                            })
                                        ) : (
                                            <p className="text-slate-400 text-xs text-center w-full py-6">
                                                No hay permisos agregados aún. Selecciona arriba o presiona Administrador.
                                            </p>
                                        )}
                                    </div>
                                    <div className="text-right mt-1.5">
                                        <span className="text-[11px] font-semibold text-blue-900">
                                            {selectedPermissions.length} permisos seleccionados
                                        </span>
                                    </div>
                                </div>
                                {formErrors.permissions && <div className="text-red-500 text-xs mt-1">{formErrors.permissions[0]}</div>}
                            </div>

                            {/* Botones de Acción Originales */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
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
                                    <span>{submitting ? 'Guardando...' : (isEditing ? 'Guardar Cambios' : 'Guardar Rol')}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
