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
        per_page: 12
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Filtros y ordenamiento (como la vista original)
    const [search, setSearch] = useState('');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [filterType, setFilterType] = useState('');
    const [filterValue, setFilterValue] = useState('');
    const [sortField, setSortField] = useState('name');
    const [sortDirection, setSortDirection] = useState('asc');

    // Modales
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDetailModal, setShowDetailModal] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);

    // Formularios
    const [formData, setFormData] = useState(INITIAL_FORM_STATE);
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Cargar usuarios desde la API REST
    const fetchUsers = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams();
            params.append('page', page);
            if (search) params.append('search', search);
            if (filterType && filterValue) {
                if (filterType === 'rol') params.append('secondFilter', filterValue);
                else if (filterType === 'status') params.append('secondFilter', filterValue);
                else if (filterType === 'busqueda') params.append('busqueda', filterValue);
                else params.append('textFilter', filterValue);
                params.append('filterType', filterType);
            }
            if (sortField) params.append('sort', sortField);
            if (sortDirection) params.append('direction', sortDirection);

            let res = await fetch(`/api/gtic/users?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok && res.status === 404) {
                res = await fetch(`/api/users?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            }

            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/login?timeout=1';
                    return;
                }
                throw new Error(`Error ${res.status}: No se pudo cargar los usuarios.`);
            }

            const json = await res.json();
            const resData = json.data || json;
            const usersPaginated = resData.users || {};
            const usersList = Array.isArray(usersPaginated)
                ? usersPaginated
                : (Array.isArray(usersPaginated.data) ? usersPaginated.data : []);

            setUsers(usersList);

            if (resData.roles && Array.isArray(resData.roles)) {
                setRoles(resData.roles);
            }
            if (resData.departamentos && Array.isArray(resData.departamentos) && resData.departamentos.length > 0) {
                setDepartamentos(resData.departamentos);
            }
            if (resData.current_user_id) {
                setCurrentUserId(resData.current_user_id);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
            if (usersPaginated.current_page) {
                setPagination({
                    current_page: usersPaginated.current_page || 1,
                    last_page: usersPaginated.last_page || 1,
                    total: usersPaginated.total || usersList.length,
                    from: usersPaginated.from || 0,
                    to: usersPaginated.to || 0,
                    per_page: usersPaginated.per_page || 10
                });
            } else if (resData.pagination) {
                setPagination(resData.pagination);
            }
        } catch (err) {
            console.error('Error fetching users:', err);
            setError(err.message || 'Error al conectar con el servidor.');
        } finally {
            setLoading(false);
        }
    }, [search, filterType, filterValue, sortField, sortDirection]);

    useEffect(() => {
        fetchUsers(1);
    }, [fetchUsers]);

    // Limpiar todos los filtros
    const handleClearFilters = () => {
        setSearch('');
        setFilterType('');
        setFilterValue('');
        setSortField('name');
        setSortDirection('asc');
    };

    // Alternar dirección de ordenamiento
    const toggleSort = () => {
        setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
    };

    // Abrir Modal de Creación
    const openCreateModal = () => {
        setFormData(INITIAL_FORM_STATE);
        setFormErrors({});
        setShowCreateModal(true);
    };

    // Abrir Modal de Edición
    const openEditModal = (user) => {
        setSelectedUser(user);
        let userRole = '';
        if (user.roles && user.roles.length > 0) {
            userRole = user.roles[0].name || user.roles[0].id || '';
        }

        setFormData({
            name: user.name || '',
            lastname: user.lastname || '',
            username: user.username || '',
            email: user.email || '',
            cedula: user.cedula || '',
            fecha_nacimiento: user.fecha_nacimiento ? user.fecha_nacimiento.split('T')[0] : '',
            departamento: user.departamento || '',
            roles: userRole,
            status: Boolean(user.status),
            password: '',
            password_confirmation: ''
        });
        setFormErrors({});
        setShowEditModal(true);
    };

    // Abrir Modal de Detalles
    const openDetailModal = (user) => {
        setSelectedUser(user);
        setShowDetailModal(true);
    };

    // Guardar Usuario (Crear)
    const handleCreateUser = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFormErrors({});

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/api/gtic/users', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'Error al guardar el usuario.');
            }

            setShowCreateModal(false);
            Swal.fire({
                icon: 'success',
                title: '¡Usuario Registrado!',
                text: 'El usuario ha sido creado exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchUsers(pagination.current_page);
        } catch (err) {
            console.error('Error creating user:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo registrar el usuario.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Guardar Cambios (Editar)
    const handleUpdateUser = async (e) => {
        e.preventDefault();
        if (!selectedUser) return;
        setSubmitting(true);
        setFormErrors({});

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const payload = { ...formData };
            if (!payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }

            const res = await fetch(`/api/gtic/users/${selectedUser.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'Error al actualizar el usuario.');
            }

            setShowEditModal(false);
            Swal.fire({
                icon: 'success',
                title: '¡Usuario Actualizado!',
                text: 'Los datos del usuario han sido actualizados con éxito.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchUsers(pagination.current_page);
        } catch (err) {
            console.error('Error updating user:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo actualizar el usuario.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar o Cambiar Estado
    const handleDeleteUser = async (user) => {
        if (currentUserId && user.id === currentUserId) {
            Swal.fire({
                icon: 'warning',
                title: 'Acción No Permitida',
                text: 'No puedes eliminar tu propio usuario en sesión.'
            });
            return;
        }

        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará al usuario ${user.name} ${user.lastname || ''} del sistema.`,
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
            const res = await fetch(`/api/gtic/users/${user.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'No se pudo eliminar el usuario.');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: 'El usuario ha sido eliminado correctamente.',
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
        <div className="w-full">
            {/* Barra de Filtros y Controles Original */}
            <div className="px-4 sm:px-6 pb-4">
                <div className="bg-white/80 border border-blue-500/15 backdrop-blur-md rounded-2xl p-4 sm:p-5 shadow-sm mb-6">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3">
                            {/* Botón para mostrar filtros avanzados */}
                            <button
                                type="button"
                                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                className="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-blue-900 text-white hover:bg-blue-800 transition-all shadow-sm active:translate-y-0.5"
                            >
                                <span>🔍</span>
                                <span>Filtros</span>
                            </button>

                            {/* Filtro principal - Tipo de campo */}
                            {showAdvancedFilters && (
                                <>
                                    <select
                                        value={filterType}
                                        onChange={(e) => {
                                            setFilterType(e.target.value);
                                            setFilterValue('');
                                        }}
                                        className="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    >
                                        <option value="">Seleccionar filtro</option>
                                        <option value="nombre">Nombre</option>
                                        <option value="cedula">Cédula</option>
                                        <option value="correo">Correo</option>
                                        <option value="username">Username</option>
                                        <option value="departamento">Departamento</option>
                                        <option value="rol">Rol</option>
                                        <option value="status">Estado</option>
                                        <option value="busqueda">Búsqueda General</option>
                                    </select>

                                    {/* Segundo filtro según tipo */}
                                    {filterType === 'rol' && (
                                        <select
                                            value={filterValue}
                                            onChange={(e) => setFilterValue(e.target.value)}
                                            className="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                            <option value="">Todos los roles</option>
                                            {(roles || []).map(r => (
                                                <option key={r.id} value={r.id}>{r.name}</option>
                                            ))}
                                        </select>
                                    )}

                                    {filterType === 'status' && (
                                        <select
                                            value={filterValue}
                                            onChange={(e) => setFilterValue(e.target.value)}
                                            className="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                            <option value="">Todos los estados</option>
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    )}

                                    {['nombre', 'cedula', 'correo', 'username', 'departamento', 'busqueda'].includes(filterType) && (
                                        <input
                                            type="text"
                                            value={filterValue}
                                            onChange={(e) => setFilterValue(e.target.value)}
                                            placeholder="Valor del filtro..."
                                            className="bg-blue-50 border border-blue-200 text-blue-900 rounded-xl px-3 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        />
                                    )}
                                </>
                            )}

                            {/* Botón para limpiar filtros */}
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all active:translate-y-0.5"
                            >
                                <span>🗑️</span>
                                <span>Limpiar</span>
                            </button>

                            {/* Selector de Ordenamiento */}
                            <div className="flex items-center gap-1 ml-2 border-l pl-3 border-slate-300">
                                <span className="text-xs text-slate-500 font-medium">Ordenar:</span>
                                <button
                                    type="button"
                                    onClick={toggleSort}
                                    className="flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-blue-100 hover:bg-blue-200 transition-colors text-xs font-semibold text-blue-900"
                                >
                                    <span>{sortDirection === 'asc' ? '↑' : '↓'}</span>
                                    <span>{sortField === 'name' ? 'Nombre' : sortField}</span>
                                </button>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            {/* Búsqueda rápida con debounce */}
                            <div className="relative">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400">
                                    <circle cx="10" cy="10" r="7"/>
                                    <path d="M21 21l-4.35-4.35"/>
                                </svg>
                                <input
                                    type="search"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Búsqueda rápida..."
                                    className="w-48 sm:w-56 pl-8 pr-3 py-1.5 text-xs rounded-xl border border-blue-200 bg-white/90 text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            {/* Botón Añadir Usuario Modal */}
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={openCreateModal}
                                    className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 transition-all shadow-sm active:translate-y-0.5"
                                >
                                    <span>➕</span>
                                    <span>Añadir</span>
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Cuadrícula de Tarjetas de Usuarios */}
            <div className="px-4 sm:px-6 pb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando usuarios...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchUsers(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : users.length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">👥</div>
                        <h3 className="text-base font-bold text-slate-800">No se encontraron usuarios</h3>
                        <p className="text-xs text-slate-500 mt-1">Prueba ajustando los filtros o añade un nuevo usuario.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {users.map(u => (
                            <div
                                key={u.id}
                                className="bg-white/85 border border-blue-500/15 backdrop-blur-md rounded-2xl p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-900/10 flex flex-col justify-between"
                            >
                                <div>
                                    {/* Header de la tarjeta */}
                                    <div className="flex items-start gap-3 mb-3">
                                        {u.profile_photo ? (
                                            <img
                                                src={`/archivos/avatars/${u.profile_photo}`}
                                                alt={u.name}
                                                className="w-14 h-14 rounded-full object-cover border-2 border-blue-900 flex-shrink-0"
                                                onError={(e) => {
                                                    e.target.style.display = 'none';
                                                    e.target.nextElementSibling.style.display = 'flex';
                                                }}
                                            />
                                        ) : null}
                                        <div
                                            className="w-14 h-14 rounded-full bg-gradient-to-br from-blue-900 to-blue-500 flex items-center justify-center text-white font-bold text-base border-2 border-blue-900 flex-shrink-0 shadow-sm"
                                            style={{ display: u.profile_photo ? 'none' : 'flex' }}
                                        >
                                            {getInitials(u.name, u.lastname)}
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <h3 className="font-bold text-sm text-blue-900 truncate" title={`${u.name} ${u.lastname || ''}`}>
                                                {u.name} {u.lastname || ''}
                                            </h3>
                                            <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold mt-1 ${
                                                u.status ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'
                                            }`}>
                                                <span className={`w-1.5 h-1.5 rounded-full ${u.status ? 'bg-green-500' : 'bg-slate-400'}`}></span>
                                                {u.status ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </div>
                                    </div>

                                    {/* Información detallada en filas con iconos */}
                                    <div className="space-y-1.5 text-xs">
                                        {/* Cédula */}
                                        <div className="flex items-center gap-2">
                                            <svg className="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <span className="text-slate-400 text-[11px] w-14">Cédula:</span>
                                            <span className="font-semibold text-slate-800 text-[11px]">{u.cedula || 'N/A'}</span>
                                        </div>

                                        {/* Username */}
                                        <div className="flex items-center gap-2">
                                            <svg className="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <circle cx="12" cy="12" r="4"/>
                                                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>
                                            </svg>
                                            <span className="text-slate-400 text-[11px] w-14">User:</span>
                                            <span className="font-semibold text-slate-800 text-[11px] truncate">@{u.username}</span>
                                        </div>

                                        {/* Fecha de nacimiento */}
                                        <div className="flex items-center gap-2">
                                            <svg className="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                <line x1="16" y1="2" x2="16" y2="6"/>
                                                <line x1="8" y1="2" x2="8" y2="6"/>
                                                <line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                            <span className="text-slate-400 text-[11px] w-14">Nac.:</span>
                                            <span className="font-semibold text-slate-800 text-[11px]">
                                                {u.fecha_nacimiento ? u.fecha_nacimiento.split('T')[0] : 'N/A'}
                                            </span>
                                        </div>

                                        {/* Correo */}
                                        <div className="flex items-center gap-2">
                                            <svg className="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                                <polyline points="22,6 12,13 2,6"/>
                                            </svg>
                                            <span className="text-slate-400 text-[11px] w-14">Correo:</span>
                                            <span className="font-semibold text-slate-800 text-[11px] truncate" title={u.email}>{u.email}</span>
                                        </div>

                                        {/* Departamento */}
                                        <div className="flex items-center gap-2">
                                            <svg className="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                            </svg>
                                            <span className="text-slate-400 text-[11px] w-14">Depto:</span>
                                            <span className="font-semibold text-slate-800 text-[11px] truncate">{u.departamento || 'N/A'}</span>
                                        </div>

                                        {/* Roles */}
                                        <div className="pt-1 flex flex-wrap gap-1">
                                            {(u.roles || []).map((r, idx) => (
                                                <span key={idx} className="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-900 border border-blue-200">
                                                    {r.name || r}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* Botones de acción inferiores */}
                                <div className="flex items-center gap-1.5 mt-3 pt-3 border-t border-slate-100">
                                    <button
                                        type="button"
                                        onClick={() => openDetailModal(u)}
                                        className="flex-1 py-1.5 px-2 text-[11px] font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors flex items-center justify-center gap-1"
                                        title="Ver Detalles"
                                    >
                                        <span>👁️</span>
                                        <span>Ver</span>
                                    </button>

                                    {permissions.can_edit && (
                                        <button
                                            type="button"
                                            onClick={() => openEditModal(u)}
                                            className="flex-1 py-1.5 px-2 text-[11px] font-semibold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors flex items-center justify-center gap-1"
                                            title="Editar Usuario"
                                        >
                                            <span>✏️</span>
                                            <span>Editar</span>
                                        </button>
                                    )}

                                    {permissions.can_destroy && (
                                        <button
                                            type="button"
                                            onClick={() => handleDeleteUser(u)}
                                            disabled={currentUserId && u.id === currentUserId}
                                            className="py-1.5 px-2 text-[11px] font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-40 flex items-center justify-center"
                                            title="Eliminar Usuario"
                                        >
                                            <span>🗑️</span>
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 mt-6">
                        <button
                            type="button"
                            onClick={() => fetchUsers(pagination.current_page - 1)}
                            disabled={pagination.current_page <= 1}
                            className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Anterior
                        </button>
                        <span className="text-xs font-bold text-blue-900 px-3">
                            Página {pagination.current_page} de {pagination.last_page} ({pagination.total} usuarios)
                        </span>
                        <button
                            type="button"
                            onClick={() => fetchUsers(pagination.current_page + 1)}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>

            {/* MODAL REGISTRAR USUARIO (REACT) */}
            {showCreateModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-slate-200 my-8">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div className="flex items-center gap-2">
                                <span className="text-xl">➕</span>
                                <h3 className="text-lg font-bold text-blue-950">Añadir Nuevo Usuario</h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowCreateModal(false)}
                                className="text-slate-400 hover:text-slate-600 text-lg font-bold p-1"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleCreateUser} className="space-y-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Nombre *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.name}
                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.name && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.name[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Apellido</label>
                                    <input
                                        type="text"
                                        value={formData.lastname}
                                        onChange={(e) => setFormData({ ...formData, lastname: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Cédula *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.cedula}
                                        onChange={(e) => setFormData({ ...formData, cedula: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.cedula && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.cedula[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Fecha de Nacimiento *</label>
                                    <input
                                        type="date"
                                        required
                                        value={formData.fecha_nacimiento}
                                        onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.fecha_nacimiento && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.fecha_nacimiento[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Nombre de Usuario *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.username}
                                        onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.username && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.username[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico *</label>
                                    <input
                                        type="email"
                                        required
                                        value={formData.email}
                                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.email && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.email[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Departamento *</label>
                                    <select
                                        required
                                        value={formData.departamento}
                                        onChange={(e) => setFormData({ ...formData, departamento: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900 bg-white"
                                    >
                                        <option value="">Seleccione Departamento</option>
                                        {departamentos.map((dep, idx) => (
                                            <option key={idx} value={dep}>{dep}</option>
                                        ))}
                                    </select>
                                    {formErrors.departamento && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.departamento[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Rol *</label>
                                    <select
                                        required
                                        value={formData.roles}
                                        onChange={(e) => setFormData({ ...formData, roles: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900 bg-white"
                                    >
                                        <option value="">Seleccione Rol</option>
                                        {roles.map(r => (
                                            <option key={r.id} value={r.name}>{r.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.roles && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.roles[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Contraseña *</label>
                                    <input
                                        type="password"
                                        required
                                        value={formData.password}
                                        onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.password && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.password[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Confirmar Contraseña *</label>
                                    <input
                                        type="password"
                                        required
                                        value={formData.password_confirmation}
                                        onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-2">
                                <input
                                    type="checkbox"
                                    id="status_create"
                                    checked={formData.status}
                                    onChange={(e) => setFormData({ ...formData, status: e.target.checked })}
                                    className="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-900"
                                />
                                <label htmlFor="status_create" className="text-xs font-bold text-slate-700">Usuario Activo</label>
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setShowCreateModal(false)}
                                    className="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-md disabled:opacity-50"
                                >
                                    {submitting ? 'Guardando...' : 'Crear Usuario'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL EDITAR USUARIO (REACT) */}
            {showEditModal && selectedUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 border border-slate-200 my-8">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <div className="flex items-center gap-2">
                                <span className="text-xl">✏️</span>
                                <h3 className="text-lg font-bold text-blue-950">Editar Usuario: {selectedUser.name}</h3>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowEditModal(false)}
                                className="text-slate-400 hover:text-slate-600 text-lg font-bold p-1"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleUpdateUser} className="space-y-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Nombre *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.name}
                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.name && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.name[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Apellido</label>
                                    <input
                                        type="text"
                                        value={formData.lastname}
                                        onChange={(e) => setFormData({ ...formData, lastname: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Cédula *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.cedula}
                                        onChange={(e) => setFormData({ ...formData, cedula: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.cedula && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.cedula[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Fecha de Nacimiento *</label>
                                    <input
                                        type="date"
                                        required
                                        value={formData.fecha_nacimiento}
                                        onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.fecha_nacimiento && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.fecha_nacimiento[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Nombre de Usuario *</label>
                                    <input
                                        type="text"
                                        required
                                        value={formData.username}
                                        onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.username && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.username[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico *</label>
                                    <input
                                        type="email"
                                        required
                                        value={formData.email}
                                        onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.email && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.email[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Departamento *</label>
                                    <select
                                        required
                                        value={formData.departamento}
                                        onChange={(e) => setFormData({ ...formData, departamento: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900 bg-white"
                                    >
                                        <option value="">Seleccione Departamento</option>
                                        {departamentos.map((dep, idx) => (
                                            <option key={idx} value={dep}>{dep}</option>
                                        ))}
                                    </select>
                                    {formErrors.departamento && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.departamento[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Rol *</label>
                                    <select
                                        required
                                        value={formData.roles}
                                        onChange={(e) => setFormData({ ...formData, roles: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900 bg-white"
                                    >
                                        <option value="">Seleccione Rol</option>
                                        {roles.map(r => (
                                            <option key={r.id} value={r.name}>{r.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.roles && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.roles[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Nueva Contraseña (Opcional)</label>
                                    <input
                                        type="password"
                                        placeholder="Dejar en blanco para conservar"
                                        value={formData.password}
                                        onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                    {formErrors.password && <p className="text-[11px] text-red-600 mt-0.5">{formErrors.password[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1">Confirmar Contraseña</label>
                                    <input
                                        type="password"
                                        placeholder="Repetir si se cambia contraseña"
                                        value={formData.password_confirmation}
                                        onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                                        className="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-900"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-2">
                                <input
                                    type="checkbox"
                                    id="status_edit"
                                    checked={formData.status}
                                    onChange={(e) => setFormData({ ...formData, status: e.target.checked })}
                                    className="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-900"
                                />
                                <label htmlFor="status_edit" className="text-xs font-bold text-slate-700">Usuario Activo</label>
                            </div>

                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setShowEditModal(false)}
                                    className="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-md disabled:opacity-50"
                                >
                                    {submitting ? 'Guardando...' : 'Actualizar Usuario'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL VER DETALLES (REACT) */}
            {showDetailModal && selectedUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-base font-bold text-blue-950">Perfil de Usuario</h3>
                            <button
                                type="button"
                                onClick={() => setShowDetailModal(false)}
                                className="text-slate-400 hover:text-slate-600 font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="flex flex-col items-center text-center mb-4">
                            <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-900 to-blue-500 flex items-center justify-center text-white font-bold text-2xl border-4 border-blue-100 shadow-md mb-2">
                                {getInitials(selectedUser.name, selectedUser.lastname)}
                            </div>
                            <h4 className="text-base font-bold text-slate-800">{selectedUser.name} {selectedUser.lastname || ''}</h4>
                            <p className="text-xs text-slate-500">@{selectedUser.username}</p>
                            <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold mt-2 ${
                                selectedUser.status ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'
                            }`}>
                                <span className={`w-1.5 h-1.5 rounded-full ${selectedUser.status ? 'bg-green-500' : 'bg-slate-400'}`}></span>
                                {selectedUser.status ? 'Activo' : 'Inactivo'}
                            </span>
                        </div>

                        <div className="space-y-2 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div className="flex justify-between py-1 border-b border-slate-200/60">
                                <span className="text-slate-500 font-medium">Cédula:</span>
                                <span className="font-semibold text-slate-800">{selectedUser.cedula || 'N/A'}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b border-slate-200/60">
                                <span className="text-slate-500 font-medium">Correo:</span>
                                <span className="font-semibold text-slate-800">{selectedUser.email}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b border-slate-200/60">
                                <span className="text-slate-500 font-medium">Departamento:</span>
                                <span className="font-semibold text-slate-800">{selectedUser.departamento || 'N/A'}</span>
                            </div>
                            <div className="flex justify-between py-1 border-b border-slate-200/60">
                                <span className="text-slate-500 font-medium">Fecha Nacimiento:</span>
                                <span className="font-semibold text-slate-800">
                                    {selectedUser.fecha_nacimiento ? selectedUser.fecha_nacimiento.split('T')[0] : 'N/A'}
                                </span>
                            </div>
                            <div className="flex justify-between py-1">
                                <span className="text-slate-500 font-medium">Roles Asignados:</span>
                                <div className="flex flex-wrap gap-1 justify-end">
                                    {(selectedUser.roles || []).map((r, i) => (
                                        <span key={i} className="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900">
                                            {r.name || r}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <div className="mt-5 flex justify-end">
                            <button
                                type="button"
                                onClick={() => setShowDetailModal(false)}
                                className="w-full py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-all"
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
