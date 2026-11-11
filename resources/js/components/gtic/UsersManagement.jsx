import React, { useState, useEffect, useCallback, useRef } from 'react';
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

    // Filtros y ordenamiento
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

    // Visibilidad de contraseñas
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    // Foto de perfil
    const [photoFile, setPhotoFile] = useState(null);
    const [photoPreviewUrl, setPhotoPreviewUrl] = useState(null);
    const fileInputRef = useRef(null);

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

    // Formatear cédula automáticamente como en el script original
    const handleCedulaChange = (e) => {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '.' + value.substring(2);
        }
        if (value.length >= 6) {
            value = value.substring(0, 6) + '.' + value.substring(6);
        }
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        setFormData(prev => ({ ...prev, cedula: value }));
    };

    // Manejar selección de foto de perfil
    const handlePhotoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({ icon: 'warning', title: 'Archivo muy grande', text: 'El archivo excede el límite de 2MB.' });
                return;
            }
            if (!file.type.startsWith('image/')) {
                Swal.fire({ icon: 'warning', title: 'Formato inválido', text: 'Por favor seleccione una imagen (JPG, PNG, GIF).' });
                return;
            }
            setPhotoFile(file);
            setPhotoPreviewUrl(URL.createObjectURL(file));
        }
    };

    // Abrir Modal de Creación
    const openCreateModal = () => {
        setFormData(INITIAL_FORM_STATE);
        setFormErrors({});
        setPhotoFile(null);
        setPhotoPreviewUrl(null);
        setShowPassword(false);
        setShowConfirmPassword(false);
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
        setPhotoFile(null);
        setPhotoPreviewUrl(user.profile_photo ? `/archivos/avatars/${user.profile_photo}` : null);
        setShowPassword(false);
        setShowConfirmPassword(false);
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
            
            // Construir FormData para soporte de foto de perfil
            const bodyData = new FormData();
            bodyData.append('name', formData.name);
            bodyData.append('lastname', formData.lastname);
            bodyData.append('username', formData.username);
            bodyData.append('email', formData.email);
            bodyData.append('cedula', formData.cedula);
            bodyData.append('fecha_nacimiento', formData.fecha_nacimiento);
            bodyData.append('departamento', formData.departamento);
            bodyData.append('roles', formData.roles);
            bodyData.append('status', formData.status ? '1' : '0');
            bodyData.append('password', formData.password);
            bodyData.append('password_confirmation', formData.password_confirmation);
            if (photoFile) {
                bodyData.append('profile_photo', photoFile);
            }

            const res = await fetch('/api/gtic/users', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: bodyData
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
            
            // Si hay foto usamos POST con _method=PUT para soporte multipart de Laravel
            const bodyData = new FormData();
            bodyData.append('_method', 'PUT');
            bodyData.append('name', formData.name);
            bodyData.append('lastname', formData.lastname);
            bodyData.append('username', formData.username);
            bodyData.append('email', formData.email);
            bodyData.append('cedula', formData.cedula);
            bodyData.append('fecha_nacimiento', formData.fecha_nacimiento);
            bodyData.append('departamento', formData.departamento);
            bodyData.append('roles', formData.roles);
            bodyData.append('status', formData.status ? '1' : '0');
            if (formData.password) {
                bodyData.append('password', formData.password);
                bodyData.append('password_confirmation', formData.password_confirmation);
            }
            if (photoFile) {
                bodyData.append('profile_photo', photoFile);
            }

            const res = await fetch(`/api/gtic/users/${selectedUser.id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: bodyData
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

    // Eliminar Usuario
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

            {/* MODAL REGISTRO DE USUARIO (CON DISEÑO ORIGINAL Y BOTONES EXACTOS) */}
            {showCreateModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-4xl w-full p-6 sm:p-8 border border-white/40 my-8 max-h-[92vh] overflow-y-auto animate-modal-expand">
                        <div className="flex items-center justify-between pb-3 mb-6 border-b border-gray-200">
                            <div>
                                <h2 className="text-xl font-bold text-blue-900 flex items-center gap-2">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M19 8v6m3-3h-6"/>
                                    </svg>
                                    Registro de Usuario
                                </h2>
                                <p className="text-xs text-slate-500 mt-0.5">Crear nueva cuenta de usuario en el sistema</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowCreateModal(false)}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleCreateUser} className="space-y-6">
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                {/* Columna Izquierda */}
                                <div className="space-y-6">
                                    {/* Información Personal */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            Información Personal
                                        </h3>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            {/* Nombre */}
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Nombre *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    placeholder="Ingrese el nombre"
                                                    value={formData.name}
                                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.name && <div className="text-red-500 text-xs mt-1">{formErrors.name[0]}</div>}
                                            </div>

                                            {/* Apellido */}
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Apellido *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    placeholder="Ingrese el apellido"
                                                    value={formData.lastname}
                                                    onChange={(e) => setFormData({ ...formData, lastname: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.lastname && <div className="text-red-500 text-xs mt-1">{formErrors.lastname[0]}</div>}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            {/* Cédula */}
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Cédula *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    placeholder="12.345.678"
                                                    value={formData.cedula}
                                                    onChange={handleCedulaChange}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.cedula && <div className="text-red-500 text-xs mt-1">{formErrors.cedula[0]}</div>}
                                            </div>

                                            {/* Fecha de Nacimiento */}
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Fec. de Nacimiento *</label>
                                                <input
                                                    type="date"
                                                    required
                                                    value={formData.fecha_nacimiento}
                                                    onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.fecha_nacimiento && <div className="text-red-500 text-xs mt-1">{formErrors.fecha_nacimiento[0]}</div>}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Información de Cuenta */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            Información de Cuenta
                                        </h3>

                                        {/* Usuario */}
                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Usuario *</label>
                                            <input
                                                type="text"
                                                required
                                                placeholder="Nombre de usuario"
                                                value={formData.username}
                                                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            />
                                            {formErrors.username && <div className="text-red-500 text-xs mt-1">{formErrors.username[0]}</div>}
                                        </div>

                                        {/* Correo Electrónico */}
                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Correo Electrónico *</label>
                                            <input
                                                type="email"
                                                required
                                                placeholder="usuario@codecyt.com"
                                                value={formData.email}
                                                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            />
                                            {formErrors.email && <div className="text-red-500 text-xs mt-1">{formErrors.email[0]}</div>}
                                        </div>

                                        {/* Contraseñas con botón de ojo interactivo */}
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Contraseña *</label>
                                                <div className="relative">
                                                    <input
                                                        type={showPassword ? 'text' : 'password'}
                                                        required
                                                        placeholder="••••••••"
                                                        value={formData.password}
                                                        onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                                        className="w-full p-2.5 pr-10 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowPassword(!showPassword)}
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none"
                                                        title="Ver / Ocultar contraseña"
                                                    >
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                            {showPassword ? (
                                                                <>
                                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                    <circle cx="12" cy="12" r="3"/>
                                                                </>
                                                            )}
                                                        </svg>
                                                    </button>
                                                </div>
                                                {formErrors.password && <div className="text-red-500 text-xs mt-1">{formErrors.password[0]}</div>}
                                            </div>

                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Confirmar Contraseña *</label>
                                                <div className="relative">
                                                    <input
                                                        type={showConfirmPassword ? 'text' : 'password'}
                                                        required
                                                        placeholder="••••••••"
                                                        value={formData.password_confirmation}
                                                        onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                                                        className="w-full p-2.5 pr-10 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none"
                                                        title="Ver / Ocultar contraseña"
                                                    >
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                            {showConfirmPassword ? (
                                                                <>
                                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                    <circle cx="12" cy="12" r="3"/>
                                                                </>
                                                            )}
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Columna Derecha */}
                                <div className="space-y-6">
                                    {/* Foto de Perfil */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                            </svg>
                                            Foto de Perfil
                                        </h3>

                                        <div className="flex items-start gap-4">
                                            {/* Preview de Foto */}
                                            <div
                                                onClick={() => fileInputRef.current?.click()}
                                                className={`w-40 h-40 sm:w-48 sm:h-48 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all duration-200 relative overflow-hidden bg-white/90 ${
                                                    photoPreviewUrl
                                                        ? 'border-2 border-solid border-green-500'
                                                        : 'border-2 border-dashed border-slate-300 hover:border-blue-900'
                                                }`}
                                            >
                                                {photoPreviewUrl ? (
                                                    <img
                                                        src={photoPreviewUrl}
                                                        alt="Vista previa"
                                                        className="w-full h-full object-cover rounded-xl"
                                                    />
                                                ) : (
                                                    <div className="text-center text-slate-500 p-2">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="mx-auto mb-2 text-slate-400">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                            <circle cx="9" cy="9" r="2"/>
                                                            <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                                        </svg>
                                                        <p className="text-xs font-semibold">Seleccionar foto</p>
                                                    </div>
                                                )}

                                                <div className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity rounded-xl">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-white">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div className="flex-1">
                                                <button
                                                    type="button"
                                                    onClick={() => fileInputRef.current?.click()}
                                                    className="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-900 text-white rounded-xl hover:bg-blue-800 mb-2 text-xs font-bold transition-all shadow-sm"
                                                >
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                        <polyline points="7,10 12,15 17,10"/>
                                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                                    </svg>
                                                    Cargar Foto
                                                </button>
                                                <p className="text-xs text-slate-400">
                                                    Formatos: JPG, PNG, GIF<br />
                                                    Tamaño máximo: 2MB
                                                </p>
                                            </div>
                                        </div>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/*"
                                            onChange={handlePhotoChange}
                                            style={{ display: 'none' }}
                                        />
                                    </div>

                                    {/* Asignación Organizacional */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                            </svg>
                                            Asignación Organizacional
                                        </h3>

                                        {/* Rol */}
                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Rol *</label>
                                            <select
                                                required
                                                value={formData.roles}
                                                onChange={(e) => setFormData({ ...formData, roles: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            >
                                                <option value="">Seleccione el rol</option>
                                                {roles.map(r => (
                                                    <option key={r.id} value={r.name}>{r.name}</option>
                                                ))}
                                            </select>
                                            {formErrors.roles && <div className="text-red-500 text-xs mt-1">{formErrors.roles[0]}</div>}
                                        </div>

                                        {/* Departamento */}
                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Departamento *</label>
                                            <select
                                                required
                                                value={formData.departamento}
                                                onChange={(e) => setFormData({ ...formData, departamento: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            >
                                                <option value="">Seleccione el departamento</option>
                                                {departamentos.map((dep, idx) => (
                                                    <option key={idx} value={dep}>{dep}</option>
                                                ))}
                                            </select>
                                            {formErrors.departamento && <div className="text-red-500 text-xs mt-1">{formErrors.departamento[0]}</div>}
                                        </div>

                                        {/* Estado del Usuario con Switch Original */}
                                        <div>
                                            <label className="block font-semibold mb-2 text-sm text-blue-900">Estado del Usuario</label>
                                            <div
                                                className="cursor-pointer flex items-center gap-3"
                                                onClick={() => setFormData(prev => ({ ...prev, status: !prev.status }))}
                                            >
                                                <div
                                                    className={`w-[60px] h-[30px] rounded-[15px] relative transition-all duration-300 border-2 ${
                                                        formData.status ? 'bg-[#22C55E] border-green-500/20' : 'bg-[#dc2626] border-red-500/20'
                                                    }`}
                                                >
                                                    <div
                                                        className={`w-[22px] h-[22px] bg-white rounded-full absolute top-[2px] transition-transform duration-300 shadow-md ${
                                                            formData.status ? 'translate-x-[30px]' : 'translate-x-[2px]'
                                                        }`}
                                                    />
                                                </div>
                                                <span className={`font-semibold text-sm transition-colors duration-300 ${
                                                    formData.status ? 'text-[#22C55E]' : 'text-[#dc2626]'
                                                }`}>
                                                    {formData.status ? 'Activo' : 'Inactivo'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Botones de Acción Inferiores Originales */}
                            <div className="flex justify-end items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                                {/* Botón Regresar (Diseño exacto de create de bienes_publicos) */}
                                <button
                                    type="button"
                                    onClick={() => setShowCreateModal(false)}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer shadow-md"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" className="mr-1 fill-white" viewBox="0 0 24 24">
                                        <path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path>
                                    </svg>
                                    <span>Regresar</span>
                                </button>

                                {/* Botón Guardar Registro Original (.btn-save con degradado) */}
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
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
                                    <span>{submitting ? 'Guardando...' : 'Guardar Registro'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL EDITAR USUARIO (CON EL MISMO DISEÑO Y BOTONES ORIGINALES) */}
            {showEditModal && selectedUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl max-w-4xl w-full p-6 sm:p-8 border border-white/40 my-8 max-h-[92vh] overflow-y-auto animate-modal-expand">
                        <div className="flex items-center justify-between pb-3 mb-6 border-b border-gray-200">
                            <div>
                                <h2 className="text-xl font-bold text-blue-900 flex items-center gap-2">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Editar Usuario: {selectedUser.name}
                                </h2>
                                <p className="text-xs text-slate-500 mt-0.5">Modificar cuenta de usuario del sistema</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowEditModal(false)}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        <form onSubmit={handleUpdateUser} className="space-y-6">
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                {/* Columna Izquierda */}
                                <div className="space-y-6">
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            Información Personal
                                        </h3>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Nombre *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={formData.name}
                                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.name && <div className="text-red-500 text-xs mt-1">{formErrors.name[0]}</div>}
                                            </div>

                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Apellido *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={formData.lastname}
                                                    onChange={(e) => setFormData({ ...formData, lastname: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.lastname && <div className="text-red-500 text-xs mt-1">{formErrors.lastname[0]}</div>}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Cédula *</label>
                                                <input
                                                    type="text"
                                                    required
                                                    value={formData.cedula}
                                                    onChange={handleCedulaChange}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.cedula && <div className="text-red-500 text-xs mt-1">{formErrors.cedula[0]}</div>}
                                            </div>

                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Fec. de Nacimiento *</label>
                                                <input
                                                    type="date"
                                                    required
                                                    value={formData.fecha_nacimiento}
                                                    onChange={(e) => setFormData({ ...formData, fecha_nacimiento: e.target.value })}
                                                    className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                />
                                                {formErrors.fecha_nacimiento && <div className="text-red-500 text-xs mt-1">{formErrors.fecha_nacimiento[0]}</div>}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Información de Cuenta */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            Información de Cuenta
                                        </h3>

                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Usuario *</label>
                                            <input
                                                type="text"
                                                required
                                                value={formData.username}
                                                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            />
                                            {formErrors.username && <div className="text-red-500 text-xs mt-1">{formErrors.username[0]}</div>}
                                        </div>

                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Correo Electrónico *</label>
                                            <input
                                                type="email"
                                                required
                                                value={formData.email}
                                                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            />
                                            {formErrors.email && <div className="text-red-500 text-xs mt-1">{formErrors.email[0]}</div>}
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Nueva Contraseña (Opcional)</label>
                                                <div className="relative">
                                                    <input
                                                        type={showPassword ? 'text' : 'password'}
                                                        placeholder="••••••••"
                                                        value={formData.password}
                                                        onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                                        className="w-full p-2.5 pr-10 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowPassword(!showPassword)}
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none"
                                                    >
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                            {showPassword ? (
                                                                <>
                                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                    <circle cx="12" cy="12" r="3"/>
                                                                </>
                                                            )}
                                                        </svg>
                                                    </button>
                                                </div>
                                                {formErrors.password && <div className="text-red-500 text-xs mt-1">{formErrors.password[0]}</div>}
                                            </div>

                                            <div>
                                                <label className="block font-semibold mb-1 text-sm text-blue-900">Confirmar Contraseña</label>
                                                <div className="relative">
                                                    <input
                                                        type={showConfirmPassword ? 'text' : 'password'}
                                                        placeholder="••••••••"
                                                        value={formData.password_confirmation}
                                                        onChange={(e) => setFormData({ ...formData, password_confirmation: e.target.value })}
                                                        className="w-full p-2.5 pr-10 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                                    />
                                                    <button
                                                        type="button"
                                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none"
                                                    >
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                            {showConfirmPassword ? (
                                                                <>
                                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                    <circle cx="12" cy="12" r="3"/>
                                                                </>
                                                            )}
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Columna Derecha */}
                                <div className="space-y-6">
                                    {/* Foto de Perfil */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                            </svg>
                                            Foto de Perfil
                                        </h3>

                                        <div className="flex items-start gap-4">
                                            <div
                                                onClick={() => fileInputRef.current?.click()}
                                                className={`w-40 h-40 sm:w-48 sm:h-48 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all duration-200 relative overflow-hidden bg-white/90 ${
                                                    photoPreviewUrl
                                                        ? 'border-2 border-solid border-green-500'
                                                        : 'border-2 border-dashed border-slate-300 hover:border-blue-900'
                                                }`}
                                            >
                                                {photoPreviewUrl ? (
                                                    <img
                                                        src={photoPreviewUrl}
                                                        alt="Vista previa"
                                                        className="w-full h-full object-cover rounded-xl"
                                                    />
                                                ) : (
                                                    <div className="text-center text-slate-500 p-2">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="mx-auto mb-2 text-slate-400">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                            <circle cx="9" cy="9" r="2"/>
                                                            <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                                        </svg>
                                                        <p className="text-xs font-semibold">Seleccionar foto</p>
                                                    </div>
                                                )}

                                                <div className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity rounded-xl">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-white">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div className="flex-1">
                                                <button
                                                    type="button"
                                                    onClick={() => fileInputRef.current?.click()}
                                                    className="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-900 text-white rounded-xl hover:bg-blue-800 mb-2 text-xs font-bold transition-all shadow-sm"
                                                >
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                        <polyline points="7,10 12,15 17,10"/>
                                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                                    </svg>
                                                    Cargar Foto
                                                </button>
                                                <p className="text-xs text-slate-400">
                                                    Formatos: JPG, PNG, GIF<br />
                                                    Tamaño máximo: 2MB
                                                </p>
                                            </div>
                                        </div>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/*"
                                            onChange={handlePhotoChange}
                                            style={{ display: 'none' }}
                                        />
                                    </div>

                                    {/* Asignación Organizacional */}
                                    <div>
                                        <h3 className="text-base font-bold mb-4 flex items-center gap-2 text-blue-950">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="text-blue-900">
                                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                                            </svg>
                                            Asignación Organizacional
                                        </h3>

                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Rol *</label>
                                            <select
                                                required
                                                value={formData.roles}
                                                onChange={(e) => setFormData({ ...formData, roles: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            >
                                                <option value="">Seleccione el rol</option>
                                                {roles.map(r => (
                                                    <option key={r.id} value={r.name}>{r.name}</option>
                                                ))}
                                            </select>
                                            {formErrors.roles && <div className="text-red-500 text-xs mt-1">{formErrors.roles[0]}</div>}
                                        </div>

                                        <div className="mb-4">
                                            <label className="block font-semibold mb-1 text-sm text-blue-900">Departamento *</label>
                                            <select
                                                required
                                                value={formData.departamento}
                                                onChange={(e) => setFormData({ ...formData, departamento: e.target.value })}
                                                className="w-full p-2.5 border border-slate-200 rounded-xl bg-white/90 text-sm focus:outline-none focus:border-blue-900 focus:ring-2 focus:ring-blue-500/20"
                                            >
                                                <option value="">Seleccione el departamento</option>
                                                {departamentos.map((dep, idx) => (
                                                    <option key={idx} value={dep}>{dep}</option>
                                                ))}
                                            </select>
                                            {formErrors.departamento && <div className="text-red-500 text-xs mt-1">{formErrors.departamento[0]}</div>}
                                        </div>

                                        <div>
                                            <label className="block font-semibold mb-2 text-sm text-blue-900">Estado del Usuario</label>
                                            <div
                                                className="cursor-pointer flex items-center gap-3"
                                                onClick={() => setFormData(prev => ({ ...prev, status: !prev.status }))}
                                            >
                                                <div
                                                    className={`w-[60px] h-[30px] rounded-[15px] relative transition-all duration-300 border-2 ${
                                                        formData.status ? 'bg-[#22C55E] border-green-500/20' : 'bg-[#dc2626] border-red-500/20'
                                                    }`}
                                                >
                                                    <div
                                                        className={`w-[22px] h-[22px] bg-white rounded-full absolute top-[2px] transition-transform duration-300 shadow-md ${
                                                            formData.status ? 'translate-x-[30px]' : 'translate-x-[2px]'
                                                        }`}
                                                    />
                                                </div>
                                                <span className={`font-semibold text-sm transition-colors duration-300 ${
                                                    formData.status ? 'text-[#22C55E]' : 'text-[#dc2626]'
                                                }`}>
                                                    {formData.status ? 'Activo' : 'Inactivo'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Botones de Acción Inferiores Originales */}
                            <div className="flex justify-end items-center gap-4 mt-8 pt-6 border-t border-gray-200">
                                <button
                                    type="button"
                                    onClick={() => setShowEditModal(false)}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer shadow-md"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" className="mr-1 fill-white" viewBox="0 0 24 24">
                                        <path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path>
                                    </svg>
                                    <span>Regresar</span>
                                </button>

                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
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
                                    <span>{submitting ? 'Guardando...' : 'Guardar Cambios'}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL VER DETALLES (REACT) */}
            {showDetailModal && selectedUser && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 animate-modal-backdrop">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 animate-modal-expand">
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-base font-bold text-blue-950">Perfil de Usuario</h3>
                            <button
                                type="button"
                                onClick={() => setShowDetailModal(false)}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="flex flex-col items-center text-center mb-4">
                            {selectedUser.profile_photo ? (
                                <img
                                    src={`/archivos/avatars/${selectedUser.profile_photo}`}
                                    alt={selectedUser.name}
                                    className="w-24 h-24 rounded-full object-cover border-4 border-blue-900 shadow-lg mb-2"
                                    onError={(e) => {
                                        e.target.style.display = 'none';
                                        const fallback = e.target.parentElement?.querySelector('.detail-avatar-fallback');
                                        if (fallback) fallback.style.display = 'flex';
                                    }}
                                />
                            ) : null}
                            <div
                                className="detail-avatar-fallback w-24 h-24 rounded-full bg-gradient-to-br from-blue-900 to-blue-500 items-center justify-center text-white font-bold text-3xl border-4 border-blue-100 shadow-lg mb-2"
                                style={{ display: selectedUser.profile_photo ? 'none' : 'flex' }}
                            >
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
