import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import SearchableSelect from '../ui/SearchableSelect';

export default function TipoImpresorasManagement() {
    const [fabricantes, setFabricantes] = useState([]);
    const [allFabricantes, setAllFabricantes] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [fabricanteFilter, setFabricanteFilter] = useState('');
    const [sortDirection, setSortDirection] = useState('asc');
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedItem, setSelectedItem] = useState(null);
    const [formData, setFormData] = useState({
        fabricante: ''
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
    const fetchFabricantes = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: 'fabricante',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());
            if (fabricanteFilter) params.append('fabricante_id', fabricanteFilter);

            const res = await fetch(`/administracion/tipo_impresoras?${params.toString()}`, {
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
            const resData = json.data?.fabricantes || {};

            setFabricantes(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.all_fabricantes) {
                setAllFabricantes(json.data.all_fabricantes);
            }

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching tipos de impresoras:', err);
            setError(err.message || 'No se pudieron cargar los tipos de impresoras.');
        } finally {
            setLoading(false);
        }
    }, [search, fabricanteFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchFabricantes(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [search, fabricanteFilter, sortDirection, fetchFabricantes]);

    // Opciones para Select Filtrable
    const fabricanteOptions = useMemo(() => {
        return allFabricantes.map(f => ({
            id: f.id,
            label: f.fabricante,
            badge: 'Fabricante'
        }));
    }, [allFabricantes]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedItem(null);
        setFormData({
            fabricante: ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (item) => {
        setModalMode('edit');
        setSelectedItem(item);
        setFormData({
            fabricante: item.fabricante || ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedItem(null);
        setFormData({ fabricante: '' });
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
                ? '/administracion/tipo_impresoras'
                : `/administracion/tipo_impresoras/${selectedItem.id}`;

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
                    ? 'El tipo de impresora fue registrado exitosamente.'
                    : 'El tipo de impresora fue actualizado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            handleCloseModal();
            fetchFabricantes(modalMode === 'create' ? 1 : pagination.current_page);
        } catch (err) {
            console.error('Error saving tipo impresora:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el tipo de impresora.',
                confirmButtonColor: '#14368b'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar registro
    const handleDelete = async (item) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el tipo de impresora "${item.fabricante}"? Esta acción no se puede deshacer.`,
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
            const res = await fetch(`/administracion/tipo_impresoras/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: json.message || 'El tipo de impresora fue eliminado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchFabricantes(pagination.current_page);
        } catch (err) {
            console.error('Error deleting tipo impresora:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el tipo de impresora.',
                confirmButtonColor: '#14368b'
            });
        }
    };

    const clearFilters = () => {
        setSearch('');
        setFabricanteFilter('');
    };

    const hasActiveFilters = Boolean(search || fabricanteFilter);

    return (
        <div className="w-full">
            {/* Filtros y controles */}
            <div className="sticky top-0 z-30 px-6 mb-6">
                <div className="p-6 bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4 flex-1">
                            {/* Buscador de texto libre */}
                            <div className="relative w-64">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar tipo impresora..."
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

                            {/* Select Filtrable: Tipo Impresora */}
                            <div className="w-72">
                                <SearchableSelect
                                    options={fabricanteOptions}
                                    value={fabricanteFilter}
                                    onChange={setFabricanteFilter}
                                    placeholder="Todos los tipos"
                                    allLabel="Todos los tipos"
                                    icon="🖨️"
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
                                Nuevo Tipo Impresora
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Tipos de Impresoras */}
            <div className="px-6 mb-8">
                {loading && fabricantes.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm animate-pulse">
                        <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando tipos de impresoras...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-white border border-red-200 rounded-2xl shadow-sm">
                        <p className="text-red-500 font-semibold mb-3">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchFabricantes(pagination.current_page)}
                            className="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : fabricantes.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                        <p className="text-slate-500 font-medium">No se encontraron tipos de impresoras con los filtros aplicados</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {fabricantes.map((item) => (
                            <div
                                key={item.id}
                                className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                            >
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center justify-between">
                                    {/* Tipo Impresora */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">
                                            📦 Tipo Impresora <span className="text-blue-500 font-bold ml-1">({String(item.id).padStart(4, '0')})</span>
                                        </p>
                                        <p className="font-semibold text-lg text-black w-fit p-2 rounded-lg">
                                            {item.fabricante}
                                        </p>
                                    </div>

                                    {/* Acciones */}
                                    <div className="text-center lg:text-right">
                                        <div className="flex justify-center gap-2 lg:justify-end">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(item)}
                                                    className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                >
                                                    <span>✏️</span>
                                                    Editar
                                                </button>
                                            )}
                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item)}
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
                            Mostrando {fabricantes.length} de {pagination.total} tipo(s) de impresoras
                            {pagination.last_page > 1 && ` (Página ${pagination.current_page} de ${pagination.last_page})`}
                        </div>

                        {pagination.last_page > 1 && (
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => fetchFabricantes(pagination.current_page - 1)}
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
                                            onClick={() => fetchFabricantes(pageNum)}
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
                                    onClick={() => fetchFabricantes(pagination.current_page + 1)}
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
                    className="fixed inset-0 z-[99999999] bg-slate-900/65 backdrop-blur-md flex items-center justify-center p-4 animate-modal-backdrop"
                    style={{
                        position: 'fixed',
                        inset: 0,
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100%',
                        height: '100%',
                        zIndex: 99999999,
                        backgroundColor: 'rgba(15, 23, 42, 0.65)',
                        backdropFilter: 'blur(8px)',
                        WebkitBackdropFilter: 'blur(8px)',
                        overflowY: 'auto'
                    }}
                    onClick={(e) => {
                        if (e.target === e.currentTarget) handleCloseModal();
                    }}
                >
                    <div
                        className="bg-white rounded-3xl shadow-2xl border border-white/20 w-full max-w-lg overflow-hidden transform transition-all animate-modal-content my-8"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Cabecera del Modal */}
                        <div className="bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-900 px-6 py-5 text-white flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lg border border-white/10">
                                    🖨️
                                </div>
                                <div>
                                    <h3 className="text-base font-bold">
                                        {modalMode === 'create' ? 'Crear Tipo Impresora' : 'Editar Tipo Impresora'}
                                    </h3>
                                    <p className="text-xs text-blue-200">
                                        {modalMode === 'create'
                                            ? 'Registra un nuevo tipo/fabricante de impresora en el sistema'
                                            : `Modificando registro #${String(selectedItem?.id || '').padStart(4, '0')}`}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white transition-all text-xs font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="p-6 space-y-5">
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                    Tipo Impresora / Fabricante <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.fabricante}
                                    onChange={(e) => setFormData({ ...formData, fabricante: e.target.value })}
                                    placeholder="Ingrese el tipo de impresora (ej. HP, Epson, Canon, Lexmark...)"
                                    required
                                    autoFocus
                                    className={`w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 transition-all ${
                                        formErrors.fabricante ? 'border-red-400 bg-red-50' : 'border-slate-200'
                                    }`}
                                />
                                {formErrors.fabricante && (
                                    <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.fabricante[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all cursor-pointer"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer disabled:opacity-50"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                        boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                    }}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
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
