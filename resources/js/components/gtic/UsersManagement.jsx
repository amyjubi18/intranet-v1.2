import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

const DEPARTAMENTOS_LIST = [
    'GTIC',
    'OGH',
    'OAC',
    'Consultoria Juridica',
    'GEDP',
    'OPP',
    'OGA',
    'Presidencia',
    'Auditoría Interna'
];

const INITIAL_FORM_STATE = {
    name: '',
    lastname: '',
    username: '',
    email: '',
    cedula: '',
    fecha_nacimiento: '',
    departamento: '',
    roles: '',
    status: true,
    password: '',
    password_confirmation: ''
};

export default function UsersManagement() {
    const [users, setUsers] = useState([]);
    const [roles, setRoles] = useState([]);
    const [departamentos, setDepartamentos] = useState(DEPARTAMENTOS_LIST);
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
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [editingUser, setEditingUser] = useState(null);

    // Estado del formulario de creación y edición
    const [formData, setFormData] = useState(INITIAL_FORM_STATE);
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

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

            if (Array.isArray(resData.roles)) setRoles(resData.roles);
            if (Array.isArray(resData.departamentos) && resData.departamentos.length > 0) {
                setDepartamentos(resData.departamentos);
            }
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

    // Apertura y control de modales
    const openCreateModal = () => {
        if (!permissions.can_create) {
            Swal.fire({
                icon: 'warning',
                title: 'Acceso Denegado',
                text: 'No tienes permisos para registrar nuevos usuarios.'
            });
            return;
        }
        setFormData(INITIAL_FORM_STATE);
        setFormErrors({});
        setCreateModalOpen(true);
    };

    const openEditModal = (user) => {
        if (!permissions.can_edit) {
            Swal.fire({
                icon: 'warning',
                title: 'Acceso Denegado',
                text: 'No tienes permisos para editar usuarios.'
            });
            return;
        }
        setEditingUser(user);
        const primaryRoleId = user.roles && user.roles.length > 0 ? user.roles[0].id : '';
        setFormData({
            name: user.name || '',
            lastname: user.lastname || '',
            username: user.username || '',
            email: user.email || '',
            cedula: user.cedula || '',
            fecha_nacimiento: user.fecha_nacimiento ? user.fecha_nacimiento.substring(0, 10) : '',
            departamento: user.departamento || '',
            roles: primaryRoleId,
            status: Boolean(user.status),
            password: '',
            password_confirmation: ''
        });
        setFormErrors({});
        setEditModalOpen(true);
    };

    const handleFormChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
        if (formErrors[name]) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next[name];
                return next;
            });
        }
    };

    // Guardar nuevo usuario (POST)
    const handleCreateSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFormErrors({});

        try {
            const response = await fetch('/api/gtic/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    setFormErrors(data.errors || {});
                    Swal.fire({
                        icon: 'error',
                        title: 'Datos Inválidos',
                        text: 'Por favor, revise los campos marcados en rojo.'
                    });
                    return;
                }
                throw new Error(data.message || `Error en el servidor (${response.status})`);
            }

            setCreateModalOpen(false);
            Swal.fire({
                icon: 'success',
                title: '¡Usuario Creado!',
                text: data.message || 'El usuario ha sido registrado exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchUsers(1);
        } catch (err) {
            console.error('Error creating user:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error al Registrar',
                text: err.message || 'No se pudo completar el registro del usuario.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Actualizar usuario existente (PUT)
    const handleEditSubmit = async (e) => {
        e.preventDefault();
        if (!editingUser) return;

        setSubmitting(true);
        setFormErrors({});

        try {
            const payload = { ...formData };
            if (!payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }

            const response = await fetch(`/api/gtic/users/${editingUser.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    setFormErrors(data.errors || {});
                    Swal.fire({
                        icon: 'error',
                        title: 'Datos Inválidos',
                        text: 'Por favor, revise los campos marcados en rojo.'
                    });
                    return;
                }
                throw new Error(data.message || `Error en el servidor (${response.status})`);
            }

            setEditModalOpen(false);
            setEditingUser(null);
            Swal.fire({
                icon: 'success',
                title: '¡Usuario Actualizado!',
                text: data.message || 'Los cambios se guardaron exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchUsers(pagination.current_page);
        } catch (err) {
            console.error('Error updating user:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error al Actualizar',
                text: err.message || 'No se pudieron guardar los cambios del usuario.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar usuario con SweetAlert2 y restricciones de seguridad
    const handleDeleteUser = async (user) => {
        if (!permissions.can_destroy) {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: 'No tienes permisos para eliminar usuarios.'
            });
            return;
        }

        if (currentUserId && user.id === currentUserId) {
            Swal.fire({
                icon: 'warning',
                title: 'Acción No Permitida',
                text: 'No puedes eliminar tu propia cuenta de usuario en sesión.'
            });
            return;
        }

        const result = await Swal.fire({
            title: '¿Confirmar eliminación?',
            text: `Se eliminará permanentemente al usuario ${user.name} ${user.lastname || ''} (@${user.username}).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`/api/gtic/users/${user.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'No se pudo eliminar el usuario.');
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: data.message || 'El usuario ha sido eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });

            fetchUsers(pagination.current_page);
        } catch (err) {
            console.error('Error deleting user:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error al Eliminar',
                text: err.message || 'Ocurrió un error al intentar eliminar el usuario.'
            });
        }
    };

    const getInitials = (name, lastname) => {
        const n = name ? name.charAt(0).toUpperCase() : '';
        const l = lastname ? lastname.charAt(0).toUpperCase() : '';
        return `${n}${l}` || 'U';
    };

    return (
        <div className="space-y-6">
            {/* Header del Módulo con Permiso Spatie can_create */}
            <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-xl bg-blue-900/10 text-blue-900 flex items-center justify-center font-bold text-xl">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 className="text-xl font-extrabold text-slate-800">Gestión de Usuarios</h1>
                            <p className="text-xs text-slate-500">Módulo GTIC - Control de accesos y roles del sistema</p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Botón Refrescar */}
                        <button
                            type="button"
                            onClick={() => fetchUsers(pagination.current_page)}
                            disabled={loading}
                            className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors disabled:opacity-50"
                        >
                            <svg className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Refrescar</span>
                        </button>

                        {/* Botón Añadir Usuario Modal (Restringido por permiso user_create) */}
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Añadir Usuario</span>
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Panel de Filtros y Búsqueda */}
            <div className="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {/* Búsqueda General */}
                    <div className="md:col-span-2 relative">
                        <label className="block text-xs font-bold text-slate-600 mb-1">Búsqueda rápida</label>
                        <div className="relative">
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Buscar por nombre, usuario, cédula o correo..."
                                className="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 transition-all"
                            />
                            <svg className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    {/* Filtro por Rol */}
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Filtrar por Rol</label>
                        <select
                            value={filterRole}
                            onChange={(e) => {
                                setFilterRole(e.target.value);
                                if (e.target.value) setFilterStatus('');
                            }}
                            className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 bg-white"
                        >
                            <option value="">Todos los roles</option>
                            {(roles || []).map(r => (
                                <option key={r.id} value={r.id}>{r.name}</option>
                            ))}
                        </select>
                    </div>

                    {/* Filtro por Estado */}
                    <div>
                        <label className="block text-xs font-bold text-slate-600 mb-1">Estado de cuenta</label>
                        <select
                            value={filterStatus}
                            onChange={(e) => {
                                setFilterStatus(e.target.value);
                                if (e.target.value) setFilterRole('');
                            }}
                            className="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 bg-white"
                        >
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                </div>

                {(search || filterRole || filterStatus) && (
                    <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span className="text-xs text-slate-500">
                            Filtros activos aplicados
                        </span>
                        <button
                            type="button"
                            onClick={handleClearFilters}
                            className="text-xs text-red-600 hover:text-red-800 font-semibold"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                )}
            </div>

            {/* Alerta de Error */}
            {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs flex items-center justify-between">
                    <span>{error}</span>
                    <button
                        type="button"
                        onClick={() => fetchUsers(pagination.current_page)}
                        className="underline font-bold ml-2"
                    >
                        Reintentar
                    </button>
                </div>
            )}

            {/* Listado de Usuarios */}
            {loading ? (
                <div className="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                    <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p className="text-slate-600 font-medium text-sm">Cargando usuarios del sistema...</p>
                </div>
            ) : users.length === 0 ? (
                <div className="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
                    <p className="text-slate-500 font-medium text-sm">No se encontraron usuarios con los criterios indicados.</p>
                </div>
            ) : (
                <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs text-slate-700">
                            <thead className="bg-slate-50 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider">
                                <tr>
                                    <th className="px-6 py-4 cursor-pointer hover:bg-slate-100" onClick={() => toggleSort('name')}>
                                        <div className="flex items-center gap-1.5">
                                            <span>Usuario / Nombre</span>
                                            {sortField === 'name' && (
                                                <span>{sortDirection === 'asc' ? '▲' : '▼'}</span>
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-6 py-4 cursor-pointer hover:bg-slate-100" onClick={() => toggleSort('cedula')}>
                                        <div className="flex items-center gap-1.5">
                                            <span>Cédula</span>
                                            {sortField === 'cedula' && (
                                                <span>{sortDirection === 'asc' ? '▲' : '▼'}</span>
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-6 py-4 cursor-pointer hover:bg-slate-100" onClick={() => toggleSort('email')}>
                                        <div className="flex items-center gap-1.5">
                                            <span>Correo Electrónico</span>
                                            {sortField === 'email' && (
                                                <span>{sortDirection === 'asc' ? '▲' : '▼'}</span>
                                            )}
                                        </div>
                                    </th>
                                    <th className="px-6 py-4">Roles Asignados</th>
                                    <th className="px-6 py-4">Departamento</th>
                                    <th className="px-6 py-4 text-center">Estado</th>
                                    <th className="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {users.map(user => (
                                    <tr key={user.id} className="hover:bg-slate-50/70 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="w-9 h-9 rounded-full bg-blue-900/10 text-blue-900 font-bold text-xs flex items-center justify-center shrink-0">
                                                    {getInitials(user.name, user.lastname)}
                                                </div>
                                                <div>
                                                    <span className="font-bold text-slate-800 block text-xs">
                                                        {user.name} {user.lastname || ''}
                                                    </span>
                                                    <span className="text-slate-400 font-mono text-[11px]">
                                                        @{user.username}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 font-mono text-slate-600">
                                            {user.cedula || 'N/A'}
                                        </td>
                                        <td className="px-6 py-4 text-slate-600">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex flex-wrap gap-1">
                                                {(user.roles || []).length > 0 ? (
                                                    user.roles.map(r => (
                                                        <span key={r.id} className="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-900 border border-blue-200">
                                                            {r.name}
                                                        </span>
                                                    ))
                                                ) : (
                                                    <span className="text-slate-400 italic text-[11px]">Sin rol</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-slate-600">
                                            {user.departamento || 'Sin asignar'}
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold ${user.status ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'}`}>
                                                <span className={`w-1.5 h-1.5 rounded-full ${user.status ? 'bg-emerald-500' : 'bg-slate-400'}`}></span>
                                                <span>{user.status ? 'Activo' : 'Inactivo'}</span>
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-center gap-1.5">
                                                {/* Ver Detalle en Modal */}
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setSelectedUser(user);
                                                        setDetailModalOpen(true);
                                                    }}
                                                    className="p-1.5 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                                    title="Ver Ficha Completa"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>

                                                {/* Editar en Modal (Restringido por permiso user_edit) */}
                                                {permissions.can_edit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => openEditModal(user)}
                                                        className="p-1.5 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors"
                                                        title="Editar Usuario"
                                                    >
                                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                )}

                                                {/* Eliminar (Restringido por user_destroy y no puede auto-eliminarse) */}
                                                {permissions.can_destroy && (!currentUserId || user.id !== currentUserId) && (
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
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Paginación */}
                    <div className="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                        <span>
                            Mostrando {pagination.from || 0} a {pagination.to || 0} de {pagination.total || 0} usuarios
                        </span>
                        <div className="flex items-center gap-2">
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
                </div>
            )}

            {/* MODAL 1: Crear Usuario */}
            {createModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
                        <div className="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 rounded-lg bg-blue-900 text-white flex items-center justify-center font-bold">
                                    +
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-sm">Registrar Nuevo Usuario</h3>
                                    <p className="text-xs text-slate-500">Completa los datos para registrar la cuenta</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setCreateModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="p-6 overflow-y-auto space-y-4 text-xs">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Nombre */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Nombre <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleFormChange}
                                        placeholder="Ej: Juan"
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.name ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.name && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.name[0]}</p>}
                                </div>

                                {/* Apellido */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Apellido <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="lastname"
                                        value={formData.lastname}
                                        onChange={handleFormChange}
                                        placeholder="Ej: Pérez"
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.lastname ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.lastname && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.lastname[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Cédula */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Cédula <span className="text-red-500">*</span> (Ej: 26.123.456)</label>
                                    <input
                                        type="text"
                                        name="cedula"
                                        value={formData.cedula}
                                        onChange={handleFormChange}
                                        placeholder="12.345.678"
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.cedula ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.cedula && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.cedula[0]}</p>}
                                </div>

                                {/* Fecha de Nacimiento */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Fecha de Nacimiento <span className="text-red-500">*</span></label>
                                    <input
                                        type="date"
                                        name="fecha_nacimiento"
                                        value={formData.fecha_nacimiento}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.fecha_nacimiento ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.fecha_nacimiento && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.fecha_nacimiento[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Username */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Nombre de Usuario <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="username"
                                        value={formData.username}
                                        onChange={handleFormChange}
                                        placeholder="juan_perez"
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.username ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.username && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.username[0]}</p>}
                                </div>

                                {/* Email */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Correo Electrónico <span className="text-red-500">*</span></label>
                                    <input
                                        type="email"
                                        name="email"
                                        value={formData.email}
                                        onChange={handleFormChange}
                                        placeholder="juan.perez@codecyt.gob.ve"
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.email ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.email && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.email[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Departamento */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Departamento / Gerencia <span className="text-red-500">*</span></label>
                                    <select
                                        name="departamento"
                                        value={formData.departamento}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.departamento ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20 bg-white`}
                                        required
                                    >
                                        <option value="">Seleccione Departamento</option>
                                        {departamentos.map(dep => (
                                            <option key={dep} value={dep}>{dep}</option>
                                        ))}
                                    </select>
                                    {formErrors.departamento && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.departamento[0]}</p>}
                                </div>

                                {/* Rol Spatie */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Rol en el Sistema <span className="text-red-500">*</span></label>
                                    <select
                                        name="roles"
                                        value={formData.roles}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.roles ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20 bg-white`}
                                        required
                                    >
                                        <option value="">Seleccione Rol</option>
                                        {roles.map(r => (
                                            <option key={r.id} value={r.id}>{r.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.roles && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.roles[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Contraseña */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Contraseña <span className="text-red-500">*</span> (mín. 8 caracteres)</label>
                                    <input
                                        type="password"
                                        name="password"
                                        value={formData.password}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.password ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.password && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.password[0]}</p>}
                                </div>

                                {/* Confirmar Contraseña */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Confirmar Contraseña <span className="text-red-500">*</span></label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        value={formData.password_confirmation}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.password_confirmation ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.password_confirmation && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.password_confirmation[0]}</p>}
                                </div>
                            </div>

                            {/* Estado activo */}
                            <div className="pt-2">
                                <label className="inline-flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="status"
                                        checked={formData.status}
                                        onChange={handleFormChange}
                                        className="rounded text-blue-900 focus:ring-blue-900 w-4 h-4"
                                    />
                                    <span className="font-semibold text-slate-700">Usuario Activo en el Sistema</span>
                                </label>
                            </div>

                            <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setCreateModalOpen(false)}
                                    className="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                                    disabled={submitting}
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all disabled:opacity-50 flex items-center gap-1.5"
                                >
                                    {submitting && (
                                        <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    )}
                                    <span>{submitting ? 'Guardando...' : 'Guardar Usuario'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL 2: Editar Usuario */}
            {editModalOpen && editingUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
                        <div className="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <div className="flex items-center gap-2">
                                <div className="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold">
                                    ✎
                                </div>
                                <div>
                                    <h3 className="font-bold text-slate-800 text-sm">
                                        Editar Usuario: {editingUser.name} {editingUser.lastname || ''}
                                    </h3>
                                    <p className="text-xs text-slate-500 font-mono">@{editingUser.username}</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={() => setEditModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleEditSubmit} className="p-6 overflow-y-auto space-y-4 text-xs">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Nombre */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Nombre <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.name ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.name && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.name[0]}</p>}
                                </div>

                                {/* Apellido */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Apellido <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="lastname"
                                        value={formData.lastname}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.lastname ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.lastname && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.lastname[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Cédula */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Cédula <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="cedula"
                                        value={formData.cedula}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.cedula ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.cedula && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.cedula[0]}</p>}
                                </div>

                                {/* Fecha de Nacimiento */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Fecha de Nacimiento</label>
                                    <input
                                        type="date"
                                        name="fecha_nacimiento"
                                        value={formData.fecha_nacimiento}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.fecha_nacimiento ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                    />
                                    {formErrors.fecha_nacimiento && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.fecha_nacimiento[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Username */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Nombre de Usuario <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="username"
                                        value={formData.username}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.username ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.username && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.username[0]}</p>}
                                </div>

                                {/* Email */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Correo Electrónico <span className="text-red-500">*</span></label>
                                    <input
                                        type="email"
                                        name="email"
                                        value={formData.email}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.email ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        required
                                    />
                                    {formErrors.email && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.email[0]}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Departamento */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Departamento / Gerencia <span className="text-red-500">*</span></label>
                                    <select
                                        name="departamento"
                                        value={formData.departamento}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.departamento ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20 bg-white`}
                                        required
                                    >
                                        <option value="">Seleccione Departamento</option>
                                        {departamentos.map(dep => (
                                            <option key={dep} value={dep}>{dep}</option>
                                        ))}
                                    </select>
                                    {formErrors.departamento && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.departamento[0]}</p>}
                                </div>

                                {/* Rol Spatie */}
                                <div>
                                    <label className="block font-bold text-slate-700 mb-1">Rol en el Sistema <span className="text-red-500">*</span></label>
                                    <select
                                        name="roles"
                                        value={formData.roles}
                                        onChange={handleFormChange}
                                        className={`w-full px-3 py-2 rounded-xl border ${formErrors.roles ? 'border-red-500 bg-red-50/50' : 'border-slate-200'} focus:outline-none focus:ring-2 focus:ring-blue-900/20 bg-white`}
                                        required
                                    >
                                        <option value="">Seleccione Rol</option>
                                        {roles.map(r => (
                                            <option key={r.id} value={r.id}>{r.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.roles && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.roles[0]}</p>}
                                </div>
                            </div>

                            {/* Contraseña Opcional */}
                            <div className="p-3 bg-amber-50/60 border border-amber-200/70 rounded-xl space-y-3">
                                <p className="text-[11px] text-amber-800 font-semibold">
                                    Nota: Si desea mantener la contraseña actual del usuario, deje los campos de contraseña en blanco.
                                </p>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block font-bold text-slate-700 mb-1">Nueva Contraseña (Opcional)</label>
                                        <input
                                            type="password"
                                            name="password"
                                            value={formData.password}
                                            onChange={handleFormChange}
                                            placeholder="••••••••"
                                            className={`w-full px-3 py-2 rounded-xl border ${formErrors.password ? 'border-red-500 bg-white' : 'border-slate-200 bg-white'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        />
                                        {formErrors.password && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.password[0]}</p>}
                                    </div>
                                    <div>
                                        <label className="block font-bold text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            value={formData.password_confirmation}
                                            onChange={handleFormChange}
                                            placeholder="••••••••"
                                            className={`w-full px-3 py-2 rounded-xl border ${formErrors.password_confirmation ? 'border-red-500 bg-white' : 'border-slate-200 bg-white'} focus:outline-none focus:ring-2 focus:ring-blue-900/20`}
                                        />
                                        {formErrors.password_confirmation && <p className="text-red-500 text-[11px] mt-1 font-semibold">{formErrors.password_confirmation[0]}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Estado activo */}
                            <div className="pt-2">
                                <label className="inline-flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="status"
                                        checked={formData.status}
                                        onChange={handleFormChange}
                                        className="rounded text-blue-900 focus:ring-blue-900 w-4 h-4"
                                    />
                                    <span className="font-semibold text-slate-700">Usuario Activo en el Sistema</span>
                                </label>
                            </div>

                            <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setEditModalOpen(false)}
                                    className="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                                    disabled={submitting}
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all disabled:opacity-50 flex items-center gap-1.5"
                                >
                                    {submitting && (
                                        <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    )}
                                    <span>{submitting ? 'Guardando...' : 'Guardar Cambios'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL 3: Detalle de Usuario */}
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
                                <button
                                    type="button"
                                    onClick={() => {
                                        const u = selectedUser;
                                        setDetailModalOpen(false);
                                        openEditModal(u);
                                    }}
                                    className="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-colors"
                                >
                                    Editar Usuario
                                </button>
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
