import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import SearchableSelect from '../ui/SearchableSelect';

export default function ArticulosManagement() {
    const [articulos, setArticulos] = useState([]);
    const [allArticulos, setAllArticulos] = useState([]);
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
    const [articuloFilter, setArticuloFilter] = useState('');
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
            if (articuloFilter) params.append('articulo_id', articuloFilter);
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

            if (json.data?.all_articulos) {
                setAllArticulos(json.data.all_articulos);
            }

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
    }, [search, articuloFilter, unidadMedidaFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchArticulos(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [search, articuloFilter, unidadMedidaFilter, sortDirection, fetchArticulos]);

    // Opciones para Select Filtrable: Unidad de Medida
    const unidadMedidasOptions = useMemo(() => {
        return unidadMedidas.map(um => ({
            id: um.id,
            label: um.unidad_medida,
            badge: 'Unidad'
        }));
    }, [unidadMedidas]);

    // Opciones para Select Filtrable: Artículos (Casado con Unidad de Medida)
    const articulosOptions = useMemo(() => {
        let list = allArticulos;
        if (unidadMedidaFilter) {
            list = list.filter(a => String(a.unidad_medida_id) === String(unidadMedidaFilter));
        }
        return list.map(a => ({
            id: a.id,
            label: a.articulo,
            badge: a.unidad_medida?.unidad_medida || null
        }));
    }, [allArticulos, unidadMedidaFilter]);

    // Manejador Casado: Cambio de Unidad de Medida en filtro
    const handleUnidadChange = (uId) => {
        setUnidadMedidaFilter(uId);
        // Si el artículo seleccionado no pertenece a la unidad seleccionada, limpiarlo
        if (uId && articuloFilter) {
            const art = allArticulos.find(a => String(a.id) === String(articuloFilter));
            if (art && String(art.unidad_medida_id) !== String(uId)) {
                setArticuloFilter('');
            }
        }
    };

    // Manejador Casado: Cambio de Artículo en filtro (autoselecciona su unidad)
    const handleArticuloChange = (aId) => {
        setArticuloFilter(aId);
        if (aId) {
            const art = allArticulos.find(a => String(a.id) === String(aId));
            if (art && art.unidad_medida_id) {
                setUnidadMedidaFilter(String(art.unidad_medida_id));
            }
        }
    };

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
        setArticuloFilter('');
        setUnidadMedidaFilter('');
    };

    const hasActiveFilters = Boolean(search || articuloFilter || unidadMedidaFilter);

    return (
        <div className="w-full">
            {/* Filtros y controles con Selects Filtrables Casados */}
            <div className="sticky top-0 z-30 px-6 mb-6">
                <div className="p-6 bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4 flex-1">
                            {/* Buscador de texto libre */}
                            <div className="relative w-60">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar por texto..."
                                    className="w-full px-4 py-2.5 text-xs font-semibold bg-blue-100/70 border border-white/20 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-xs"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {/* Select Filtrable Casado: Artículo */}
                            <div className="w-72">
                                <SearchableSelect
                                    options={articulosOptions}
                                    value={articuloFilter}
                                    onChange={handleArticuloChange}
                                    placeholder="Todos los artículos"
                                    allLabel="Todos los artículos"
                                    icon="📄"
                                />
                            </div>

                            {/* Select Filtrable Casado: Unidad de Medida */}
                            <div className="w-64">
                                <SearchableSelect
                                    options={unidadMedidasOptions}
                                    value={unidadMedidaFilter}
                                    onChange={handleUnidadChange}
                                    placeholder="Todas las unidades"
                                    allLabel="Todas las unidades"
                                    icon="📏"
                                />
                            </div>

                            {hasActiveFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1 ml-auto">
                                <span className="text-xs text-gray-500 font-medium">Ordenar:</span>
                                <button
                                    type="button"
                                    onClick={() => setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc')}
                                    className="px-2.5 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition-all cursor-pointer"
                                    title="Alternar orden A-Z / Z-A"
                                >
                                    {sortDirection === 'asc' ? 'A ➜ Z' : 'Z ➜ A'}
                                </button>
                            </div>
                        </div>

                        {/* Botón Crear */}
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={handleOpenCreate}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer ml-auto"
                                style={{
                                    background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                    boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                }}
                            >
                                <span>➕</span>
                                Registrar Artículo
                            </button>
                        )}
                    </div>

                    {/* Indicador de relación casada */}
                    {(articuloFilter || unidadMedidaFilter) && (
                        <div className="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2 text-[11px] text-blue-900 font-semibold">
                            <span>🔗</span>
                            <span>
                                {articuloFilter && unidadMedidaFilter
                                    ? 'Filtro sincronizado: Artículo y Unidad de Medida enlazados.'
                                    : unidadMedidaFilter
                                        ? `Mostrando ${articulosOptions.length} artículo(s) perteneciente(s) a la unidad seleccionada.`
                                        : 'Artículo seleccionado y unidad asociada.'}
                            </span>
                        </div>
                    )}
                </div>
            </div>

            {/* Listado de Artículos */}
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
                        <p className="text-slate-500 font-medium">No se encontraron artículos con los filtros aplicados</p>
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
                {pagination.total > 0 && (
                    <div className="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div className="text-xs font-semibold text-slate-600">
                            Mostrando {articulos.length} de {pagination.total} artículo(s)
                            {pagination.last_page > 1 && ` (Página ${pagination.current_page} de ${pagination.last_page})`}
                        </div>

                        {pagination.last_page > 1 && (
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => fetchArticulos(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1 || loading}
                                    className={`px-3 py-2 rounded-xl text-xs font-bold transition-all ${
                                        pagination.current_page <= 1 || loading
                                            ? 'bg-slate-100 text-slate-300 cursor-not-allowed'
                                            : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200 cursor-pointer'
                                    }`}
                                >
                                    ◀ Anterior
                                </button>

                                {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                                    let pageNum;
                                    if (pagination.last_page <= 5) {
                                        pageNum = i + 1;
                                    } else if (pagination.current_page <= 3) {
                                        pageNum = i + 1;
                                    } else if (pagination.current_page >= pagination.last_page - 2) {
                                        pageNum = pagination.last_page - 4 + i;
                                    } else {
                                        pageNum = pagination.current_page - 2 + i;
                                    }

                                    return (
                                        <button
                                            key={pageNum}
                                            type="button"
                                            onClick={() => fetchArticulos(pageNum)}
                                            disabled={loading}
                                            className={`w-8 h-8 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                                                pagination.current_page === pageNum
                                                    ? 'bg-blue-900 text-white shadow-sm'
                                                    : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200'
                                            }`}
                                        >
                                            {pageNum}
                                        </button>
                                    );
                                })}

                                <button
                                    type="button"
                                    onClick={() => fetchArticulos(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= pagination.last_page || loading}
                                    className={`px-3 py-2 rounded-xl text-xs font-bold transition-all ${
                                        pagination.current_page >= pagination.last_page || loading
                                            ? 'bg-slate-100 text-slate-300 cursor-not-allowed'
                                            : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200 cursor-pointer'
                                    }`}
                                >
                                    Siguiente ▶
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Modal de Crear / Editar usando createPortal con backdrop-blur completo */}
            {modalOpen && createPortal(
                <div
                    className="fixed inset-0 bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300"
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
                        className="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-200 my-auto animate-modal-expand overflow-visible"
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

                            {/* Unidad de Medida (SearchableSelect) */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    📏 Unidad de Medida <span className="text-red-500">*</span>
                                </label>
                                <SearchableSelect
                                    options={unidadMedidasOptions}
                                    value={formData.unidad_medida_id}
                                    onChange={(val) => setFormData({ ...formData, unidad_medida_id: val })}
                                    placeholder="Seleccione una unidad de medida..."
                                    allLabel={false}
                                    icon="📏"
                                    error={formErrors.unidad_medida_id ? formErrors.unidad_medida_id[0] : null}
                                    disabled={submitting}
                                    required
                                />
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
