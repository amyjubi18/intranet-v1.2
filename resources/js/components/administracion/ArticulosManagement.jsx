import React, { useState, useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';

export default function ArticulosManagement() {
    const [articulos, setArticulos] = useState([]);
    const [unidadMedidas, setUnidadMedidas] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [unidadMedidaFilter, setUnidadMedidaFilter] = useState('');
    const [sortDirection, setSortDirection] = useState('asc');
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedArticulo, setSelectedArticulo] = useState(null);
    const [formData, setFormData] = useState({
        articulo: '',
        unidad_medida_id: '',
        stock_min: 0
    });
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Bloquear scroll de la página cuando el modal está abierto
    useEffect(() => {
        if (modalOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [modalOpen]);

    // Cargar datos desde la API
    const fetchArticulos = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: 'articulo',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());
            if (unidadMedidaFilter) {
                params.append('unidad_medida_id', unidadMedidaFilter);
                params.append('unidad', unidadMedidaFilter);
            }

            const res = await fetch(`/administracion/articulos?${params.toString()}`, {
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
            const resData = json.data?.articulos || {};

            setArticulos(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.unidad_medidas) {
                setUnidadMedidas(json.data.unidad_medidas);
            }

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching articulos:', err);
            setError(err.message || 'No se pudieron cargar los artículos.');
        } finally {
            setLoading(false);
        }
    }, [search, unidadMedidaFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchArticulos(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, unidadMedidaFilter, sortDirection, fetchArticulos]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedArticulo(null);
        setFormData({
            articulo: '',
            unidad_medida_id: unidadMedidas.length > 0 ? unidadMedidas[0].id : '',
            stock_min: 0
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (art) => {
        setModalMode('edit');
        setSelectedArticulo(art);
        setFormData({
            articulo: art.articulo || '',
            unidad_medida_id: art.unidad_medida_id || '',
            stock_min: art.stock_min ?? 0
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedArticulo(null);
        setFormData({ articulo: '', unidad_medida_id: '', stock_min: 0 });
        setFormErrors({});
    };

    // Enviar formulario (Crear o Editar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        setSubmitting(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/administracion/articulos'
                : `/administracion/articulos/${selectedArticulo.id}`;

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

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    setFormErrors(json.errors);
                    return;
                }
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Creado!' : '¡Actualizado!',
                text: modalMode === 'create'
                    ? 'El artículo fue registrado exitosamente.'
                    : 'El artículo fue actualizado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            handleCloseModal();
            fetchArticulos(modalMode === 'create' ? 1 : pagination.current_page);
        } catch (err) {
            console.error('Error saving articulo:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el artículo.',
                confirmButtonColor: '#14368b'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar registro
    const handleDelete = async (art) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el artículo "${art.articulo}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'rounded-2xl' }
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/articulos/${art.id}`, {
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
                title: 'Eliminado',
                text: 'El artículo fue eliminado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchArticulos(pagination.current_page);
        } catch (err) {
            console.error('Error deleting articulo:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el artículo.',
                confirmButtonColor: '#14368b'
            });
        }
    };

    const clearFilters = () => {
        setSearch('');
        setUnidadMedidaFilter('');
    };

    return (
        <div className="w-full">
            {/* Filtros y controles (Diseño Usuarios de Uso) */}
            <div className="sticky top-0 z-10 px-6 mb-6">
                <div className="p-6 bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            {/* Buscador */}
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar artículo..."
                                    className="w-64 px-4 py-2 text-sm bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {/* Filtro por Unidad de Medida */}
                            <select
                                value={unidadMedidaFilter}
                                onChange={(e) => setUnidadMedidaFilter(e.target.value)}
                                className="px-4 py-2 text-sm bg-blue-100 border border-white/20 backdrop-blur-md rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 cursor-pointer"
                            >
                                <option value="">Todas las unidades de medida</option>
                                {unidadMedidas.map(um => (
                                    <option key={um.id} value={um.id}>
                                        {um.unidad_medida}
                                    </option>
                                ))}
                            </select>

                            {(search || unidadMedidaFilter) && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm cursor-pointer"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1 ml-2">
                                <span className="text-xs text-gray-500">Ordenar por:</span>
                                <button
                                    type="button"
                                    onClick={() => setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc')}
                                    className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg font-semibold bg-blue-100 text-blue-900 hover:bg-blue-200 transition-colors cursor-pointer"
                                >
                                    <span>Artículo</span>
                                    <span>{sortDirection === 'asc' ? '⬆️' : '⬇️'}</span>
                                </button>
                            </div>
                        </div>

                        {/* Botón Nuevo */}
                        <div className="flex items-center gap-3">
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreate}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 text-sm cursor-pointer shadow-sm active:translate-y-0.5"
                                >
                                    <span>➕</span>
                                    Nuevo
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de artículos */}
            <div className="px-6 mb-8">
                {loading && articulos.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm animate-pulse">
                        <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando artículos...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-white border border-red-200 rounded-2xl shadow-sm">
                        <p className="text-red-500 font-semibold mb-3">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchArticulos(pagination.current_page)}
                            className="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : articulos.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                        <p className="text-slate-500 font-medium">No hay ningún artículo registrado</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {articulos.map((art) => (
                            <div
                                key={art.id}
                                className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                            >
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center justify-between">
                                    {/* Artículo */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">
                                            📄 Artículo <span className="text-blue-500 font-bold ml-1">({String(art.id).padStart(4, '0')})</span>
                                        </p>
                                        <p className="font-semibold text-lg text-black w-fit p-2 rounded-lg">
                                            {art.articulo}
                                        </p>
                                    </div>

                                    {/* Unidad de Medida (casada) */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-green-900 font-semibold mb-1">📏 Unidad de Medida</p>
                                        <p className="font-semibold text-lg text-black w-fit p-2 rounded-lg">
                                            {art.unidad_medida?.unidad_medida || 'Sin unidad'}
                                        </p>
                                        <p className="text-xs text-slate-500 font-semibold px-2">
                                            Stock Mínimo: <span className="text-blue-800">{art.stock_min}</span>
                                        </p>
                                    </div>

                                    {/* Acciones */}
                                    <div className="text-center lg:text-left">
                                        <div className="flex justify-center gap-2 lg:justify-start">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(art)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                >
                                                    <span>✏️</span>
                                                    Editar
                                                </button>
                                            )}
                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(art)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-xs hover:bg-red-600 cursor-pointer"
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
                                onClick={() => fetchArticulos(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-3">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchArticulos(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal Crear / Editar (Portaled a document.body) */}
            {modalOpen && typeof document !== 'undefined' && createPortal(
                <div
                    className="fixed inset-0 z-[9999999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate-fadeIn"
                    style={{
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100vw',
                        height: '100vh',
                        zIndex: 9999999,
                        overflowY: 'auto',
                        margin: 0
                    }}
                    onClick={(e) => {
                        if (e.target === e.currentTarget && !submitting) handleCloseModal();
                    }}
                >
                    <div
                        className="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 overflow-hidden my-auto animate-modal-expand"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Cabecera con degradado corporativo exacto */}
                        <div className="relative px-6 py-5 bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-800 text-white rounded-t-2xl flex items-center justify-between shadow-md">
                            <div>
                                <h3 className="text-lg font-black tracking-wide uppercase">
                                    {modalMode === 'create' ? 'Registrar Artículo' : 'Editar Artículo'}
                                </h3>
                                <p className="text-xs text-blue-200 mt-0.5 font-medium">
                                    {modalMode === 'create'
                                        ? 'Complete los datos para registrar el artículo en el sistema'
                                        : 'Modificar información del artículo seleccionado'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors text-sm font-bold cursor-pointer"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="p-6 sm:p-8 space-y-5">
                            {/* Nombre del artículo */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    📄 Nombre del Artículo <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.articulo}
                                    onChange={(e) => setFormData({ ...formData, articulo: e.target.value })}
                                    placeholder="Ej: Lápiz de grafito HB, Papel Bond..."
                                    className="w-full bg-[#f0f5ff] border border-blue-200/60 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all"
                                    required
                                    autoFocus
                                    disabled={submitting}
                                />
                                {formErrors.articulo && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.articulo[0]}</p>
                                )}
                            </div>

                            {/* Unidad de Medida */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    📏 Unidad de Medida <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={formData.unidad_medida_id}
                                    onChange={(e) => setFormData({ ...formData, unidad_medida_id: e.target.value })}
                                    className="w-full bg-[#f0f5ff] border border-blue-200/60 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all cursor-pointer"
                                    required
                                    disabled={submitting}
                                >
                                    <option value="">Seleccione una unidad de medida...</option>
                                    {unidadMedidas.map(um => (
                                        <option key={um.id} value={um.id}>
                                            {um.unidad_medida}
                                        </option>
                                    ))}
                                </select>
                                {formErrors.unidad_medida_id && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.unidad_medida_id[0]}</p>
                                )}
                            </div>

                            {/* Stock Mínimo */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    📦 Stock Mínimo <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    value={formData.stock_min}
                                    onChange={(e) => setFormData({ ...formData, stock_min: parseInt(e.target.value) || 0 })}
                                    placeholder="0"
                                    className="w-full bg-[#f0f5ff] border border-blue-200/60 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all"
                                    required
                                    disabled={submitting}
                                />
                                {formErrors.stock_min && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.stock_min[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción inferiores */}
                            <div className="flex items-center justify-between gap-4 pt-4 border-t border-slate-200 mt-6">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                                >
                                    Regresar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white transition-all duration-300 shadow-md cursor-pointer flex items-center gap-2"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                        boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                    }}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <span>💾</span>
                                            <span>{modalMode === 'create' ? 'Guardar Artículo' : 'Actualizar'}</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>,
                document.body
            )}
        </div>
    );
}
