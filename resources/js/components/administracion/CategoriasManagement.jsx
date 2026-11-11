import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function CategoriasManagement() {
    const [categorias, setCategorias] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [sortDirection, setSortDirection] = useState('asc');
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedCategoria, setSelectedCategoria] = useState(null);
    const [formData, setFormData] = useState({ name: '' });
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Cargar categorías desde la API
    const fetchCategorias = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 15,
                sort: 'name',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());

            const res = await fetch(`/api/administracion/categorias?${params.toString()}`, {
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
            const resData = json.data?.categorias || json.data || {};

            setCategorias(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching categorias:', err);
            setError(err.message || 'No se pudieron cargar las categorías.');
        } finally {
            setLoading(false);
        }
    }, [search, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchCategorias(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, sortDirection, fetchCategorias]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedCategoria(null);
        setFormData({ name: '' });
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (categoria) => {
        setModalMode('edit');
        setSelectedCategoria(categoria);
        setFormData({ name: categoria.name });
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedCategoria(null);
        setFormData({ name: '' });
        setFormErrors({});
    };

    // Guardar (Crear o Actualizar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});

        if (!formData.name.trim()) {
            setFormErrors({ name: ['El nombre de la categoría es obligatorio'] });
            return;
        }

        setSubmitting(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/api/administracion/categorias'
                : `/api/administracion/categorias/${selectedCategoria.id}`;

            const method = modalMode === 'create' ? 'POST' : 'PUT';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            const json = await res.json();

            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    setFormErrors(json.errors);
                    return;
                }
                throw new Error(json.message || 'Error al procesar la solicitud');
            }

            Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Creada!' : '¡Actualizada!',
                text: modalMode === 'create' ? 'Categoría registrada con éxito' : 'Categoría actualizada con éxito',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });

            handleCloseModal();
            fetchCategorias(pagination.current_page);
        } catch (err) {
            console.error('Error submitting categoria:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar la categoría',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar categoría
    const handleDelete = async (categoria) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la categoría "${categoria.name}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                confirmButton: 'rounded-xl px-4 py-2 font-semibold',
                cancelButton: 'rounded-xl px-4 py-2 font-semibold'
            }
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/api/administracion/categorias/${categoria.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminada',
                text: 'La categoría fue eliminada con éxito',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchCategorias(pagination.current_page);
        } catch (err) {
            console.error('Error deleting categoria:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar la categoría',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    return (
        <div className="w-full">
            {/* Barra de Filtros y Controles Original */}
            <div className="sticky top-0 z-10 px-6 mb-6">
                <div className="p-6 bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-2xl shadow-xs transition-all">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            {/* Buscador directo */}
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar categoría..."
                                    className="w-64 px-4 py-2 text-sm bg-white border border-slate-200 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-xs text-slate-800"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {search && (
                                <button
                                    type="button"
                                    onClick={() => setSearch('')}
                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1 ml-2">
                                <span className="text-xs text-gray-500">Ordenar:</span>
                                <button
                                    type="button"
                                    onClick={() => setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc')}
                                    className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg bg-blue-100 text-blue-800 font-semibold hover:bg-blue-200 transition-colors"
                                >
                                    Nombre {sortDirection === 'asc' ? '⬆️' : '⬇️'}
                                </button>
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreate}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Nuevo
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de Categorías */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center bg-white/80 rounded-2xl border border-slate-200">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando categorías...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center text-red-600 bg-red-50 rounded-2xl border border-red-200">
                        <p className="text-sm font-semibold mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchCategorias(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : categorias.length === 0 ? (
                    <div className="p-10 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <div className="flex items-center justify-center w-16 h-16 mb-4 bg-blue-50 rounded-full mx-auto">
                            <span className="text-3xl">📁</span>
                        </div>
                        <h3 className="mb-1 text-lg font-medium text-slate-800">No hay ninguna categoría registrada</h3>
                        <p className="text-sm text-slate-400">
                            {search ? 'No se encontraron resultados para la búsqueda' : 'Comienza creando la primera categoría'}
                        </p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {categorias.map((categoria) => (
                            <div
                                key={categoria.id}
                                className="bg-white border border-slate-200/80 rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                            >
                                <div className="grid items-center justify-between grid-cols-1 gap-6 lg:grid-cols-2">
                                    {/* Categoría */}
                                    <div className="text-center lg:text-left">
                                        <p className="mb-1 text-xs font-semibold text-blue-900">📁 Categoría</p>
                                        <p className="p-2 text-lg font-semibold text-slate-900 rounded-lg w-fit">
                                            {categoria.name}
                                        </p>
                                    </div>

                                    {/* Acciones */}
                                    <div className="text-center lg:text-right">
                                        <div className="flex justify-center gap-2 lg:justify-end">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(categoria)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-sm"
                                                >
                                                    <span>✏️</span>
                                                    Editar
                                                </button>
                                            )}
                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(categoria)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-sm hover:bg-red-600"
                                                >
                                                    <span>🗑️</span>
                                                    Eliminar
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
                    <div className="flex justify-center mt-8">
                        <div className="p-3 bg-white border border-slate-200 rounded-xl shadow-xs flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => fetchCategorias(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-3">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} categorías)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchCategorias(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* MODAL CREAR / EDITAR CATEGORÍA (Mantiene el diseño original exacto) */}
            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 sm:p-8 border border-slate-200 my-8 transition-all animate-modal-expand">
                        {/* Cabecera del modal */}
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-xl font-bold text-slate-800">
                                    {modalMode === 'create' ? 'Crear Categoría' : 'Editar Categoría'}
                                </h3>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    {modalMode === 'create' ? 'Registrar nueva categoría en el sistema' : 'Modificar nombre de la categoría'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Nombre de la Categoría:
                                </label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    placeholder="Ingresa el nombre de la categoría..."
                                    className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                    required
                                    autoFocus
                                    disabled={submitting}
                                />
                                {formErrors.name && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.name[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción (Diseño exacto de create de bienes_publicos) */}
                            <div className="flex items-center justify-between gap-4 pt-6 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer shadow-md"
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
                                    className="btn-save inline-flex items-center justify-center gap-2 px-8 py-3 text-sm font-bold text-white rounded-xl shadow-md transition-all duration-300 disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" className="mr-1" viewBox="0 0 24 24" style={{ fill: 'rgba(255, 255, 255, 1)' }}>
                                                <path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path>
                                            </svg>
                                            <span>{modalMode === 'create' ? 'Guardar Categoría' : 'Actualizar Categoría'}</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
