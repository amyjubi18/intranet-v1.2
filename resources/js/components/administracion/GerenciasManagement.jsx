import React, { useState, useEffect, useCallback } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';

export default function GerenciasManagement() {
    const [gerencias, setGerencias] = useState([]);
    const [areas, setAreas] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [areaFilter, setAreaFilter] = useState('');
    const [sortDirection, setSortDirection] = useState('asc');
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedGerencia, setSelectedGerencia] = useState(null);
    const [formData, setFormData] = useState({
        gerencia: '',
        area_id: ''
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
    const fetchGerencias = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: 'gerencia',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());
            if (areaFilter) params.append('area', areaFilter);

            const res = await fetch(`/administracion/gerencias?${params.toString()}`, {
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
            const resData = json.data?.gerencias || {};

            setGerencias(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.areas) {
                setAreas(json.data.areas);
            }

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching gerencias:', err);
            setError(err.message || 'No se pudieron cargar las gerencias.');
        } finally {
            setLoading(false);
        }
    }, [search, areaFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchGerencias(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, areaFilter, sortDirection, fetchGerencias]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedGerencia(null);
        setFormData({
            gerencia: '',
            area_id: areas.length > 0 ? areas[0].id : ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (ger) => {
        setModalMode('edit');
        setSelectedGerencia(ger);
        setFormData({
            gerencia: ger.gerencia || '',
            area_id: ger.area_id || ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedGerencia(null);
        setFormData({ gerencia: '', area_id: '' });
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
                ? '/administracion/gerencias'
                : `/administracion/gerencias/${selectedGerencia.id}`;

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
                title: modalMode === 'create' ? '¡Creada!' : '¡Actualizada!',
                text: modalMode === 'create'
                    ? 'La gerencia fue registrada exitosamente.'
                    : 'La gerencia fue actualizada correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            handleCloseModal();
            fetchGerencias(modalMode === 'create' ? 1 : pagination.current_page);
        } catch (err) {
            console.error('Error saving gerencia:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar la gerencia.',
                confirmButtonColor: '#14368b'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar registro
    const handleDelete = async (ger) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la gerencia "${ger.gerencia}"? Esta acción no se puede deshacer.`,
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
            const res = await fetch(`/administracion/gerencias/${ger.id}`, {
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
                text: 'La gerencia fue eliminada correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchGerencias(pagination.current_page);
        } catch (err) {
            console.error('Error deleting gerencia:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar la gerencia.',
                confirmButtonColor: '#14368b'
            });
        }
    };

    const clearFilters = () => {
        setSearch('');
        setAreaFilter('');
    };

    return (
        <div className="w-full">
            {/* Filtros y controles */}
            <div className="sticky top-0 z-10 px-6 mb-6">
                <div className="p-6 bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            {/* Filtro por Área */}
                            <select
                                value={areaFilter}
                                onChange={(e) => setAreaFilter(e.target.value)}
                                className="bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 cursor-pointer"
                            >
                                <option value="">Todas las áreas</option>
                                {areas.map(a => (
                                    <option key={a.id} value={a.id} className="text-slate-800 bg-white">
                                        {a.area}
                                    </option>
                                ))}
                            </select>

                            {/* Buscador */}
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="🔍 Buscar gerencia..."
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

                            {(search || areaFilter) && (
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
                                    <span>Gerencia</span>
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
                                    Nueva Gerencia
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de gerencias */}
            <div className="px-6 mb-8">
                {loading && gerencias.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm animate-pulse">
                        <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando gerencias...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-white border border-red-200 rounded-2xl shadow-sm">
                        <p className="text-red-500 font-semibold mb-3">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchGerencias(pagination.current_page)}
                            className="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : gerencias.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                        <p className="text-slate-500 font-medium">No hay ninguna gerencia registrada</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {gerencias.map((ger) => (
                            <div
                                key={ger.id}
                                className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                            >
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center justify-between">
                                    {/* Gerencia */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">🏢 Gerencia</p>
                                        <p className="font-semibold text-lg text-black w-fit p-2 rounded-lg">
                                            {ger.gerencia}
                                        </p>
                                    </div>

                                    {/* Área */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-green-900 font-semibold mb-1">📍 Área</p>
                                        <p className="font-semibold text-lg text-black w-fit p-2 rounded-lg">
                                            {ger.area?.area || 'Sin área'}
                                        </p>
                                    </div>

                                    {/* Acciones */}
                                    <div className="text-center lg:text-left">
                                        <div className="flex justify-center gap-2 lg:justify-start">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(ger)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                >
                                                    <span>✏️</span>
                                                    Editar
                                                </button>
                                            )}
                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(ger)}
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
                                onClick={() => fetchGerencias(pagination.current_page - 1)}
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
                                onClick={() => fetchGerencias(pagination.current_page + 1)}
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
                                    {modalMode === 'create' ? 'Registrar Gerencia' : 'Editar Gerencia'}
                                </h3>
                                <p className="text-xs text-blue-200 mt-0.5 font-medium">
                                    {modalMode === 'create'
                                        ? 'Ingrese el nombre de la gerencia y asigne su área'
                                        : 'Modificar información de la gerencia seleccionada'}
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
                            {/* Nombre de la Gerencia */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    🏢 Nombre de la Gerencia <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.gerencia}
                                    onChange={(e) => setFormData({ ...formData, gerencia: e.target.value })}
                                    placeholder="Ingrese nombre de la gerencia..."
                                    className="w-full bg-[#f0f5ff] border border-blue-200/60 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all"
                                    required
                                    autoFocus
                                    disabled={submitting}
                                />
                                {formErrors.gerencia && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.gerencia[0]}</p>
                                )}
                            </div>

                            {/* Área Asignada */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                                    📍 Área Asignada <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={formData.area_id}
                                    onChange={(e) => setFormData({ ...formData, area_id: e.target.value })}
                                    className="w-full bg-[#f0f5ff] border border-blue-200/60 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all cursor-pointer"
                                    required
                                    disabled={submitting}
                                >
                                    <option value="">Seleccione un área...</option>
                                    {areas.map(a => (
                                        <option key={a.id} value={a.id}>
                                            {a.area}
                                        </option>
                                    ))}
                                </select>
                                {formErrors.area_id && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.area_id[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción */}
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
                                            <span>{modalMode === 'create' ? 'Guardar' : 'Actualizar'}</span>
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
