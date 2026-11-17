import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import SearchableSelect from '../ui/SearchableSelect';

export default function ImpresorasManagement() {
    const [impresoras, setImpresoras] = useState([]);
    const [allImpresoras, setAllImpresoras] = useState([]);
    const [gerencias, setGerencias] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [impresoraFilter, setImpresoraFilter] = useState('');
    const [gerenciaFilter, setGerenciaFilter] = useState('');
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
        impresora: '',
        gerencia_ids: []
    });
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [gerenciaSearch, setGerenciaSearch] = useState('');

    const fileInputRef = useRef(null);

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
    const fetchImpresoras = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: 'impresora',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());
            if (impresoraFilter) params.append('nombre-impresora', impresoraFilter);
            if (gerenciaFilter) params.append('gerencia_id', gerenciaFilter);

            const res = await fetch(`/administracion/impresoras?${params.toString()}`, {
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
            const resData = json.data?.impresoras || {};

            setImpresoras(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.all_impresoras) {
                setAllImpresoras(json.data.all_impresoras);
            }

            if (json.data?.gerencias) {
                setGerencias(json.data.gerencias);
            }

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching impresoras:', err);
            setError(err.message || 'No se pudieron cargar las impresoras.');
        } finally {
            setLoading(false);
        }
    }, [search, impresoraFilter, gerenciaFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchImpresoras(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [search, impresoraFilter, gerenciaFilter, sortDirection, fetchImpresoras]);

    // Opciones para Select Filtrable: Impresoras
    const impresoraOptions = useMemo(() => {
        return allImpresoras.map(imp => ({
            id: imp.id,
            label: imp.impresora,
            badge: 'Impresora'
        }));
    }, [allImpresoras]);

    // Opciones para Select Filtrable: Gerencias
    const gerenciaOptions = useMemo(() => {
        return gerencias.map(g => ({
            id: g.id,
            label: g.gerencia + (g.area ? ` - ${g.area.area}` : ''),
            badge: g.area?.area || 'Gerencia'
        }));
    }, [gerencias]);

    // Manejar selección de imagen
    const handleImageChange = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        if (!file.type.match(/^image\/(jpeg|png|jpg|gif|webp)$/i)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato Inválido',
                text: 'Por favor selecciona un archivo de imagen válido (PNG, JPG, JPEG, WEBP o GIF).'
            });
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo Demasiado Grande',
                text: 'La imagen no debe superar los 5MB.'
            });
            return;
        }

        setImageFile(file);
        const reader = new FileReader();
        reader.onload = (event) => {
            setImagePreview(event.target.result);
        };
        reader.readAsDataURL(file);
    };

    const handleRemoveImage = () => {
        setImageFile(null);
        setImagePreview(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    // Toggle gerencia seleccionada en el formulario
    const toggleGerencia = (gId) => {
        const numId = Number(gId);
        setFormData(prev => {
            const current = prev.gerencia_ids || [];
            if (current.includes(numId)) {
                return { ...prev, gerencia_ids: current.filter(id => id !== numId) };
            } else {
                return { ...prev, gerencia_ids: [...current, numId] };
            }
        });
    };

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedItem(null);
        setFormData({
            impresora: '',
            gerencia_ids: []
        });
        setImageFile(null);
        setImagePreview(null);
        setFormErrors({});
        setGerenciaSearch('');
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (item) => {
        setModalMode('edit');
        setSelectedItem(item);
        setFormData({
            impresora: item.impresora || '',
            gerencia_ids: (item.gerencias || []).map(g => Number(g.id))
        });
        setImageFile(null);
        setImagePreview(item.imagen ? `/storage/${item.imagen}` : null);
        setFormErrors({});
        setGerenciaSearch('');
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedItem(null);
        setFormData({ impresora: '', gerencia_ids: [] });
        setImageFile(null);
        setImagePreview(null);
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
                ? '/administracion/impresoras'
                : `/administracion/impresoras/${selectedItem.id}`;

            const data = new FormData();
            data.append('impresora', formData.impresora);

            if (modalMode === 'edit') {
                data.append('_method', 'PUT');
            }

            if (imageFile) {
                data.append('imagen', imageFile);
            }

            formData.gerencia_ids.forEach(gId => {
                data.append('gerencia_ids[]', gId);
            });

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: data
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
                    ? 'La impresora fue registrada exitosamente.'
                    : 'La impresora fue actualizada correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            handleCloseModal();
            fetchImpresoras(modalMode === 'create' ? 1 : pagination.current_page);
        } catch (err) {
            console.error('Error saving impresora:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar la impresora.',
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
            text: `¿Deseas eliminar la impresora "${item.impresora}"? Esta acción no se puede deshacer.`,
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
            const res = await fetch(`/administracion/impresoras/${item.id}`, {
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
                title: 'Eliminada',
                text: json.message || 'La impresora fue eliminada correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchImpresoras(pagination.current_page);
        } catch (err) {
            console.error('Error deleting impresora:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar la impresora.',
                confirmButtonColor: '#14368b'
            });
        }
    };

    const clearFilters = () => {
        setSearch('');
        setImpresoraFilter('');
        setGerenciaFilter('');
    };

    const hasActiveFilters = Boolean(search || impresoraFilter || gerenciaFilter);

    // Filtrar lista de gerencias en el modal
    const filteredGerenciasModal = useMemo(() => {
        if (!gerenciaSearch.trim()) return gerencias;
        const q = gerenciaSearch.toLowerCase();
        return gerencias.filter(g => {
            const gName = (g.gerencia || '').toLowerCase();
            const aName = (g.area?.area || '').toLowerCase();
            return gName.includes(q) || aName.includes(q);
        });
    }, [gerencias, gerenciaSearch]);

    return (
        <div className="w-full">
            {/* Filtros y controles */}
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
                                    placeholder="Buscar impresora..."
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

                            {/* Select Filtrable: Impresora */}
                            <div className="w-64">
                                <SearchableSelect
                                    options={impresoraOptions}
                                    value={impresoraFilter}
                                    onChange={setImpresoraFilter}
                                    placeholder="Todas las impresoras"
                                    allLabel="Todas las impresoras"
                                    icon="🖨️"
                                />
                            </div>

                            {/* Select Filtrable: Gerencia */}
                            <div className="w-64">
                                <SearchableSelect
                                    options={gerenciaOptions}
                                    value={gerenciaFilter}
                                    onChange={setGerenciaFilter}
                                    placeholder="Todas las gerencias"
                                    allLabel="Todas las gerencias"
                                    icon="🏢"
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
                                Registrar Impresora
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Impresoras */}
            <div className="px-6 mb-8">
                {loading && impresoras.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm animate-pulse">
                        <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando impresoras...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-white border border-red-200 rounded-2xl shadow-sm">
                        <p className="text-red-500 font-semibold mb-3">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchImpresoras(pagination.current_page)}
                            className="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : impresoras.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                        <p className="text-slate-500 font-medium">No se encontraron impresoras con los filtros aplicados</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {impresoras.map((item) => (
                            <div
                                key={item.id}
                                className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                            >
                                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                                    {/* ID / Item */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">ITEM</p>
                                        <p className="text-2xl font-bold text-blue-600">
                                            #{String(item.id).padStart(4, '0')}
                                        </p>
                                    </div>

                                    {/* Impresora & Gerencias */}
                                    <div className="text-center lg:text-left">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">🖨️ Impresora</p>
                                        <p className="font-semibold text-lg text-slate-900 mb-2">
                                            {item.impresora}
                                        </p>

                                        {item.gerencias && item.gerencias.length > 0 ? (
                                            <div>
                                                <p className="text-[11px] text-blue-900 font-semibold mb-1">Gerencias Asignadas:</p>
                                                <div className="flex flex-wrap justify-center lg:justify-start gap-1.5">
                                                    {item.gerencias.map(g => (
                                                        <span
                                                            key={g.id}
                                                            className="text-[11px] bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded-full font-semibold"
                                                        >
                                                            🏢 {g.gerencia} {g.area?.area ? `(${g.area.area})` : ''}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : (
                                            <p className="text-xs text-slate-400 italic">Sin gerencias asignadas</p>
                                        )}
                                    </div>

                                    {/* Imagen & Acciones */}
                                    <div className="text-center lg:text-left flex flex-col items-center lg:items-start">
                                        <p className="text-xs text-blue-900 font-semibold mb-1">📄 Imagen</p>
                                        <div className="flex justify-center lg:justify-start mb-3">
                                            <div className="w-24 h-24 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-200 overflow-hidden shadow-xs">
                                                {item.imagen ? (
                                                    <img
                                                        src={`/storage/${item.imagen}`}
                                                        alt={item.impresora}
                                                        className="w-24 h-24 object-cover"
                                                        onError={(e) => {
                                                            e.target.style.display = 'none';
                                                            e.target.parentElement.innerHTML = '<span class="text-3xl">🖨️</span>';
                                                        }}
                                                    />
                                                ) : (
                                                    <span className="text-3xl text-slate-400">🖨️</span>
                                                )}
                                            </div>
                                        </div>

                                        <div className="flex justify-center lg:justify-start gap-2">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(item)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                >
                                                    <span>✏️</span>
                                                    Editar
                                                </button>
                                            )}
                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-xs hover:bg-red-600 cursor-pointer"
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
                            Mostrando {impresoras.length} de {pagination.total} impresora(s)
                            {pagination.last_page > 1 && ` (Página ${pagination.current_page} de ${pagination.last_page})`}
                        </div>

                        {pagination.last_page > 1 && (
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => fetchImpresoras(pagination.current_page - 1)}
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
                                            onClick={() => fetchImpresoras(pageNum)}
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
                                    onClick={() => fetchImpresoras(pagination.current_page + 1)}
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
                        className="bg-white rounded-3xl shadow-2xl border border-white/20 w-full max-w-2xl overflow-hidden transform transition-all animate-modal-content my-8"
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
                                        {modalMode === 'create' ? 'Registrar Nueva Impresora' : 'Editar Impresora'}
                                    </h3>
                                    <p className="text-xs text-blue-200">
                                        {modalMode === 'create'
                                            ? 'Completa los datos del modelo, imagen y gerencias asignadas'
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
                        <form onSubmit={handleSubmit} className="p-6 space-y-6 max-h-[calc(85vh-120px)] overflow-y-auto">
                            {/* Modelo de Impresora */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                    Modelo de Impresora <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.impresora}
                                    onChange={(e) => setFormData({ ...formData, impresora: e.target.value })}
                                    placeholder="Ej: HP LaserJet Enterprise M608dn"
                                    required
                                    autoFocus
                                    className={`w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 transition-all ${
                                        formErrors.impresora ? 'border-red-400 bg-red-50' : 'border-slate-200'
                                    }`}
                                />
                                {formErrors.impresora && (
                                    <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.impresora[0]}</p>
                                )}
                            </div>

                            {/* Carga y Vista Previa de Imagen */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                    Imagen de la Impresora
                                </label>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Zona de Drop / Carga */}
                                    <div
                                        onClick={() => fileInputRef.current?.click()}
                                        className="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-5 text-center flex flex-col items-center justify-center cursor-pointer bg-slate-50 hover:bg-blue-50/40 transition-all min-h-[160px]"
                                    >
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                                            onChange={handleImageChange}
                                            className="hidden"
                                        />
                                        <div className="w-12 h-12 rounded-full bg-blue-100 text-blue-900 flex items-center justify-center text-xl mb-2">
                                            📷
                                        </div>
                                        <p className="text-xs font-bold text-slate-700">Haz clic para seleccionar imagen</p>
                                        <p className="text-[10px] text-slate-400 mt-0.5">Formatos: PNG, JPG, WEBP (Máx 5MB)</p>
                                    </div>

                                    {/* Vista Previa */}
                                    <div className="border border-slate-200 rounded-2xl p-3 flex flex-col items-center justify-center bg-slate-50/50 min-h-[160px] relative">
                                        {imagePreview ? (
                                            <div className="relative group w-full h-full flex flex-col items-center justify-center">
                                                <img
                                                    src={imagePreview}
                                                    alt="Vista previa"
                                                    className="max-h-32 object-contain rounded-xl shadow-xs"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        handleRemoveImage();
                                                    }}
                                                    className="mt-2 text-xs font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    🗑️ Quitar imagen
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="text-center text-slate-400">
                                                <span className="text-3xl block mb-1">🖼️</span>
                                                <span className="text-xs">Sin imagen seleccionada</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                {formErrors.imagen && (
                                    <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.imagen[0]}</p>
                                )}
                            </div>

                            {/* Asignación de Gerencias (Múltiple) */}
                            <div>
                                <div className="flex items-center justify-between mb-2">
                                    <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Gerencias Asignadas ({formData.gerencia_ids.length})
                                    </label>
                                    {formData.gerencia_ids.length > 0 && (
                                        <button
                                            type="button"
                                            onClick={() => setFormData({ ...formData, gerencia_ids: [] })}
                                            className="text-[11px] font-semibold text-red-600 hover:underline"
                                        >
                                            Limpiar selección
                                        </button>
                                    )}
                                </div>

                                {/* Buscador rápido de gerencias */}
                                <input
                                    type="text"
                                    value={gerenciaSearch}
                                    onChange={(e) => setGerenciaSearch(e.target.value)}
                                    placeholder="Filtrar gerencias por nombre..."
                                    className="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl mb-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800"
                                />

                                <div className="max-h-48 overflow-y-auto border border-slate-200 rounded-2xl p-3 bg-slate-50/50 space-y-1.5">
                                    {filteredGerenciasModal.length === 0 ? (
                                        <p className="text-xs text-slate-400 text-center py-4">No se encontraron gerencias</p>
                                    ) : (
                                        filteredGerenciasModal.map(g => {
                                            const isChecked = formData.gerencia_ids.includes(Number(g.id));
                                            return (
                                                <label
                                                    key={g.id}
                                                    className={`flex items-center gap-2.5 p-2 rounded-xl text-xs font-semibold cursor-pointer transition-all ${
                                                        isChecked
                                                            ? 'bg-blue-100/80 text-blue-900 border border-blue-300'
                                                            : 'hover:bg-slate-100 text-slate-700 border border-transparent'
                                                    }`}
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={isChecked}
                                                        onChange={() => toggleGerencia(g.id)}
                                                        className="w-4 h-4 text-blue-900 rounded focus:ring-blue-500 cursor-pointer"
                                                    />
                                                    <span className="flex-1 truncate">
                                                        🏢 {g.gerencia} {g.area?.area ? `— ${g.area.area}` : ''}
                                                    </span>
                                                </label>
                                            );
                                        })
                                    )}
                                </div>
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
