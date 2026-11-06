import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function UsersManagement() {
    const [users, setUsers] = useState([]);
    const [roles, setRoles] = useState([]);
    const [currentUserId, setCurrentUserId] = useState(null);
    const [permissions, setPermissions] = useState({
        can_create: false,
        can_edit: false,
        can_destroy: false,
        can_export: false
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

    // Filtros y ordenamiento
    const [search, setSearch] = useState('');
    const [filterRole, setFilterRole] = useState('');
    const [filterStatus, setFilterStatus] = useState('');
    const [sortField, setSortField] = useState('name');
    const [sortDirection, setSortDirection] = useState('desc');

    // Modales
    const [selectedUser, setSelectedUser] = useState(null);
    const [detailModalOpen, setDetailModalOpen] = useState(false);

    // CSRF Token de Laravel
    const getCsrfToken = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    };

    // Cargar usuarios desde la API REST
    const fetchUsers = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: sortField,
                direction: sortDirection
            });

            if (search.trim()) {
                params.append('search', search.trim());
            }

            if (filterRole) {
                params.append('filterType', 'rol');
                params.append('secondFilter', filterRole);
            } else if (filterStatus !== '') {
                params.append('filterType', 'status');
                params.append('secondFilter', filterStatus);
            }

            const response = await fetch(`/api/gtic/users?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error('No tienes permisos suficientes para ver el listado de usuarios.');
                }
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const json = await response.json();
            const resData = json.data || {};
            const paginated = resData.users || {};

            setUsers(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 10
            });

            if (resData.roles) setRoles(resData.roles);
            if (resData.current_user_id) setCurrentUserId(resData.current_user_id);
            if (resData.permissions) setPermissions(resData.permissions);
        } catch (err) {
            console.error('Error fetching users:', err);
            setError(err.message || 'No se pudieron cargar los usuarios.');
        } finally {
            setLoading(false);
        }
    }, [search, filterRole, filterStatus, sortField, sortDirection]);

    // Debounce de 300ms para búsqueda y filtros
    useEffect(() => {
        const timer = setTimeout(() => {
            fetchUsers(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, filterRole, filterStatus, sortField, sortDirection, fetchUsers]);

    const handleClearFilters = () => {
        setSearch('');
        setFilterRole('');
        setFilterStatus('');
        setSortField('name');
        setSortDirection('desc');
    };

    const toggleSort = (field) => {
        if (sortField === field) {
            setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
        } else {
            setSortField(field);
            setSortDirection('asc');
        }
    };

    // Eliminar usuario con SweetAlert2 y restricciones de seguridad
    const handleDeleteUser = async (user) => {
        if (!permissions.can_destroy) {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: 'No tienes permisos para eliminar usuarios.',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }

        if (user.id === currentUserId) {
            Swal.fire({
                icon: 'warning',
                title: 'Acción no permitida',
                text: 'No puedes eliminar tu propia cuenta de usuario en sesión.',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }

        const result = await Swal.fire({
            title: '¿Eliminar usuario?',
            html: `¿Estás seguro de que deseas eliminar al usuario <strong>${user.name} ${user.lastname || ''}</strong> (@${user.username})?<br/><span class="text-xs text-red-500">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#2563eb',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/api/gtic/users/${user.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const resJson = await response.json();

                if (!response.ok || !resJson.success) {
                    throw new Error(resJson.message || 'No se pudo eliminar el usuario.');
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: resJson.message || 'Usuario eliminado correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Refrescar listado actual
                fetchUsers(pagination.current_page);
            } catch (delErr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: delErr.message || 'Error al intentar eliminar el usuario.',
                    confirmButtonColor: '#1e3a8a'
                });
            }
        }
    };

    const getInitials = (name, lastname) => {
        const n = name ? name.charAt(0).toUpperCase() : '';
        const l = lastname ? lastname.charAt(0).toUpperCase() : '';
        return (n + l) || 'U';
    };

    return (
        <div className="space-y-6">
            {/* Cabecera y Barra de Filtros */}
            <div className="bg-white/80 backdrop-blur-md rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <h2 className="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span>👥</span>
                            <span>Gestión de Usuarios</span>
                        </h2>
                        <p className="text-xs text-slate-500 mt-1">
                            Administra cuentas de acceso, roles asignados y estados de los usuarios del sistema.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            onClick={() => fetchUsers(pagination.current_page)}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all"
                            title="Actualizar listado"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        {/* Botón Añadir Usuario (Restringido por permiso user_create) */}
                        {permissions.can_create && (
                            <a
                                href="/users/create"
                                className="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Añadir Usuario</span>
                            </a>
                        )}
                    </div>
                </div>

                {/* Filtros en Tiempo Real */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
                    {/* Buscador Rápido con Debounce */}
                    <div className="relative">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar nombre, cédula, correo, usuario..."
                            className="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        />
                        <svg className="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="10" cy="10" r="7" strokeWidth="2"/>
                            <path d="M21 21l-4.35-4.35" strokeWidth="2" strokeLinecap="round"/>
                        </svg>
                    </div>

                    {/* Filtro por Rol Spatie */}
                    <div>
                        <select
                            value={filterRole}
                            onChange={(e) => {
                                setFilterRole(e.target.value);
                                if (e.target.value) setFilterStatus('');
                            }}
                            className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Filtrar por Rol (Todos)...</option>
                            {roles.map((r) => (
                                <option key={r.id} value={r.id}>{r.name}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro por Estado */}
                    <div>
                        <select
                            value={filterStatus}
                            onChange={(e) => {
                                setFilterStatus(e.target.value);
                                if (e.target.value !== '') setFilterRole('');
                            }}
                            className="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="">Filtrar por Estado (Todos)...</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>

                    {/* Ordenamiento Dinámico */}
                    <div className="flex items-center gap-2">
                        <select
                            value={sortField}
                            onChange={(e) => setSortField(e.target.value)}
                            className="flex-1 px-3 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800"
                        >
                            <option value="name">Ordenar: Nombre</option>
                            <option value="cedula">Ordenar: Cédula</option>
                            <option value="email">Ordenar: Correo</option>
                            <option value="created_at">Ordenar: Fecha</option>
                        </select>
                        <button
                            type="button"
                            onClick={() => toggleSort(sortField)}
                            className="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors"
                            title={`Cambiar dirección: ${sortDirection.toUpperCase()}`}
                        >
                            {sortDirection === 'asc' ? '↑ ASC' : '↓ DESC'}
                        </button>
                        {(search || filterRole || filterStatus !== '') && (
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

            {/* Manejo de Error */}
            {error && (
                <div className="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center justify-between text-xs">
                    <div className="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{error}</span>
                    </div>
                    <button
                        onClick={() => fetchUsers(1)}
                        className="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold"
                    >
                        Reintentar
                    </button>
                </div>
            )}

            {/* Grid de Tarjetas de Usuarios */}
            {loading && users.length === 0 ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {[...Array(8)].map((_, i) => (
                        <div key={i} className="bg-white/80 rounded-2xl p-5 border border-slate-200 animate-pulse">
                            <div className="flex items-center gap-3 mb-4">
                                <div className="w-12 h-12 rounded-full bg-slate-200"></div>
                                <div className="space-y-1.5 flex-1">
                                    <div className="h-4 bg-slate-200 rounded w-3/4"></div>
                                    <div className="h-3 bg-slate-200 rounded w-1/2"></div>
                                </div>
                            </div>
                            <div className="space-y-2">
                                <div className="h-3 bg-slate-200 rounded"></div>
                                <div className="h-3 bg-slate-200 rounded w-5/6"></div>
                                <div className="h-3 bg-slate-200 rounded w-2/3"></div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : users.length === 0 ? (
                <div className="bg-white/80 backdrop-blur-md rounded-2xl p-12 text-center border border-slate-200">
                    <div className="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">
                        👤
                    </div>
                    <h3 className="text-base font-bold text-slate-800 mb-1">No se encontraron usuarios</h3>
                    <p className="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                        No hay usuarios que coincidan con los criterios de búsqueda o filtros seleccionados.
                    </p>
                    {(search || filterRole || filterStatus !== '') && (
                        <button
                            type="button"
                            onClick={handleClearFilters}
                            className="px-4 py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors"
                        >
                            Limpiar filtros
                        </button>
                    )}
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    {users.map((user) => {
                        const isSelf = user.id === currentUserId;
                        const avatarUrl = user.profile_photo ? `/archivos/avatars/${user.profile_photo}` : null;
                        const userRoles = user.roles || [];

                        return (
                            <div
                                key={user.id}
                                className={`bg-white/90 backdrop-blur-md rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 ${
                                    isSelf ? 'border-blue-400 ring-1 ring-blue-300' : 'border-slate-200'
                                }`}
                            >
                                <div>
                                    {/* Cabecera de la Tarjeta */}
                                    <div className="flex items-start gap-3 mb-4">
                                        {avatarUrl ? (
                                            <img
                                                src={avatarUrl}
                                                alt={user.name}
                                                className="w-12 h-12 rounded-full object-cover border-2 border-blue-900 shadow-sm"
                                                onError={(e) => {
                                                    e.target.style.display = 'none';
                                                    e.target.nextSibling.style.display = 'flex';
                                                }}
                                            />
                                        ) : null}
                                        <div
                                            className="w-12 h-12 rounded-full bg-gradient-to-br from-blue-900 to-blue-700 text-white font-bold text-sm flex items-center justify-center border-2 border-blue-900 shadow-sm"
                                            style={{ display: avatarUrl ? 'none' : 'flex' }}
                                        >
                                            {getInitials(user.name, user.lastname)}
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <h4 className="text-sm font-bold text-slate-800 truncate" title={`${user.name} ${user.lastname || ''}`}>
                                                {user.name} {user.lastname || ''}
                                            </h4>
                                            <p className="text-xs text-slate-500 font-mono truncate">
                                                @{user.username || 'sin_usuario'}
                                            </p>
                                            <div className="flex items-center gap-1.5 mt-1">
                                                <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border ${
                                                    user.status ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'
                                                }`}>
                                                    <span className={`w-1.5 h-1.5 rounded-full ${user.status ? 'bg-emerald-500' : 'bg-slate-400'}`}></span>
                                                    <span>{user.status ? 'Activo' : 'Inactivo'}</span>
                                                </span>
                                                {isSelf && (
                                                    <span className="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200">
                                                        Tú
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Información Detallada */}
                                    <div className="space-y-1.5 text-xs text-slate-600 border-t border-slate-100 pt-3 mb-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-400">Cédula:</span>
                                            <span className="font-mono font-semibold text-slate-700">{user.cedula || 'N/A'}</span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-400">Correo:</span>
                                            <span className="font-semibold text-slate-700 truncate max-w-[140px]" title={user.email}>
                                                {user.email || 'N/A'}
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-400">Departamento:</span>
                                            <span className="font-medium text-slate-700 truncate max-w-[140px]" title={user.departamento}>
                                                {user.departamento || 'No asignado'}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Roles Asignados */}
                                    <div className="mb-4">
                                        <div className="text-[10px] uppercase font-bold text-slate-400 mb-1">Rol / Permisos:</div>
                                        <div className="flex flex-wrap gap-1">
                                            {userRoles.length > 0 ? (
                                                userRoles.map((r) => (
                                                    <span
                                                        key={r.id}
                                                        className="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-800 border border-blue-200"
                                                    >
                                                        {r.name}
                                                    </span>
                                                ))
                                            ) : (
                                                <span className="text-[10px] text-red-500 font-semibold bg-red-50 px-2 py-0.5 rounded border border-red-200">
                                                    Sin Rol Asignado
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Botones de Acción (Respetan estrictamente los permisos del usuario) */}
                                <div className="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSelectedUser(user);
                                            setDetailModalOpen(true);
                                        }}
                                        className="px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors flex-1 text-center"
                                    >
                                        Detalles
                                    </button>

                                    {/* Editar (Restringido por user_edit) */}
                                    {permissions.can_edit && (
                                        <a
                                            href={`/users/${user.id}/edit`}
                                            className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
                                            title="Editar Usuario"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                    )}

                                    {/* Eliminar (Restringido por user_destroy y no auto-eliminación) */}
                                    {permissions.can_destroy && !isSelf && (
                                        <button
                                            type="button"
                                            onClick={() => handleDeleteUser(user)}
                                            className="p-1.5 text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                            title="Eliminar Usuario"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Paginación Dinámica */}
            {pagination.total > 0 && (
                <div className="bg-white/80 backdrop-blur-md rounded-2xl p-4 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div className="text-xs text-slate-500">
                        Mostrando <span className="font-semibold text-slate-800">{pagination.from || 0}</span> a{' '}
                        <span className="font-semibold text-slate-800">{pagination.to || 0}</span> de{' '}
                        <span className="font-semibold text-slate-800">{pagination.total}</span> usuarios registrados
                    </div>

                    <div className="flex items-center gap-1">
                        <button
                            type="button"
                            disabled={pagination.current_page <= 1 || loading}
                            onClick={() => fetchUsers(pagination.current_page - 1)}
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
                            onClick={() => fetchUsers(pagination.current_page + 1)}
                            className="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            )}

            {/* Modal de Detalle de Usuario */}
            {detailModalOpen && selectedUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
                        <div className="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-full bg-blue-900 text-white font-bold text-sm flex items-center justify-center">
                                    {getInitials(selectedUser.name, selectedUser.lastname)}
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-sm">
                                        {selectedUser.name} {selectedUser.lastname || ''}
                                    </h3>
                                    <p className="text-xs text-slate-500 font-mono">@{selectedUser.username}</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setDetailModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="p-6 overflow-y-auto space-y-4 text-xs">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <span className="text-slate-400 block mb-0.5">Cédula:</span>
                                    <span className="font-bold text-slate-800">{selectedUser.cedula || 'No registrada'}</span>
                                </div>
                                <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                    <span className="text-slate-400 block mb-0.5">Fecha Nacimiento:</span>
                                    <span className="font-bold text-slate-800">
                                        {selectedUser.fecha_nacimiento ? new Date(selectedUser.fecha_nacimiento).toLocaleDateString('es-VE') : 'No registrada'}
                                    </span>
                                </div>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-0.5">Correo Electrónico:</span>
                                <span className="font-bold text-slate-800">{selectedUser.email || 'N/A'}</span>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-0.5">Departamento / Dependencia:</span>
                                <span className="font-bold text-slate-800">{selectedUser.departamento || 'Sin asignar'}</span>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-1">Roles Spatie Asignados:</span>
                                <div className="flex flex-wrap gap-1.5 mt-1">
                                    {(selectedUser.roles || []).length > 0 ? (
                                        selectedUser.roles.map(r => (
                                            <span key={r.id} className="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-900 border border-blue-200">
                                                {r.name}
                                            </span>
                                        ))
                                    ) : (
                                        <span className="text-red-500 font-semibold">Sin roles asignados</span>
                                    )}
                                </div>
                            </div>

                            <div className="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span className="text-slate-400 block mb-0.5">Estado de la Cuenta:</span>
                                <span className={`inline-flex items-center gap-1.5 font-bold ${selectedUser.status ? 'text-emerald-700' : 'text-slate-500'}`}>
                                    <span className={`w-2 h-2 rounded-full ${selectedUser.status ? 'bg-emerald-500' : 'bg-slate-400'}`}></span>
                                    <span>{selectedUser.status ? 'Cuenta Activa' : 'Cuenta Inactiva'}</span>
                                </span>
                            </div>
                        </div>

                        <div className="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2">
                            {permissions.can_edit && (
                                <a
                                    href={`/users/${selectedUser.id}/edit`}
                                    className="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-colors"
                                >
                                    Editar Usuario
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
