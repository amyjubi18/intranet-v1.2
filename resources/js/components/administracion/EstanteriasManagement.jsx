import React, { useState, useEffect, useCallback, useRef } from 'react';
import Swal from 'sweetalert2';

export default function EstanteriasManagement() {
    const [estanterias, setEstanterias] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedEstanteria, setSelectedEstanteria] = useState(null);
    const [nombre, setNombre] = useState('');
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const fileInputRef = useRef(null);

    // Cargar estanterías desde la API
    const fetchEstanterias = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 15,
                sort: 'nombre',
                direction: 'asc'
            });
            if (search.trim()) params.append('search_general', search.trim());

            const res = await fetch(`/api/administracion/estanterias?${params.toString()}`, {
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
            const resData = json.data?.estanterias || json.data || {};

            setEstanterias(resData.data || []);
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
            console.error('Error fetching estanterias:', err);
            setError(err.message || 'No se pudieron cargar las estanterías.');
        } finally {
            setLoading(false);
        }
    }, [search]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchEstanterias(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [search, fetchEstanterias]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedEstanteria(null);
        setNombre('');
        setImageFile(null);
        setImagePreview(null);
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (estanteria) => {
        setModalMode('edit');
        setSelectedEstanteria(estanteria);
        setNombre(estanteria.nombre || '');
        setImageFile(null);
        setImagePreview(estanteria.imagen ? `/storage/estanterias/${estanteria.imagen}` : null);
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedEstanteria(null);
        setNombre('');
        setImageFile(null);
        setImagePreview(null);
        setFormErrors({});
    };

    // Manejar selección de archivo
    const handleFileChange = (e) => {
        const file = e.target.files?.[0];
        if (file) {
            setImageFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setImagePreview(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    // Formato de fecha d/m/Y
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    };

    // Guardar (Crear o Actualizar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});

        if (!nombre.trim()) {
            setFormErrors({ nombre: ['El nombre de la estantería es obligatorio'] });
            return;
        }

        setSubmitting(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/api/administracion/estanterias'
                : `/api/administracion/estanterias/${selectedEstanteria.id}`;

            const formData = new FormData();
            formData.append('nombre', nombre.trim());
            if (imageFile) {
                formData.append('imagen', imageFile);
            }
            if (modalMode === 'edit') {
                formData.append('_method', 'PUT');
            }

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
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
                text: modalMode === 'create' ? 'Estantería registrada con éxito' : 'Estantería actualizada con éxito',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });

            handleCloseModal();
            fetchEstanterias(pagination.current_page);
        } catch (err) {
            console.error('Error submitting estanteria:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar la estantería',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar estantería
    const handleDelete = async (estanteria) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la estantería "${estanteria.nombre}"? Todos los documentos y archivos asociados también podrían verse afectados.`,
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
            const res = await fetch(`/api/administracion/estanterias/${estanteria.id}`, {
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
                text: 'La estantería fue eliminada con éxito',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchEstanterias(pagination.current_page);
        } catch (err) {
            console.error('Error deleting estanteria:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar la estantería',
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
                            {/* Búsqueda general */}
                            <div className="relative">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar estanterías..."
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
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreate}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Nueva
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de Estanterías en Grid */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center bg-white/80 rounded-2xl border border-slate-200">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando estanterías...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center text-red-600 bg-red-50 rounded-2xl border border-red-200">
                        <p className="text-sm font-semibold mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchEstanterias(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : estanterias.length === 0 ? (
                    <div className="p-10 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <div className="flex items-center justify-center w-16 h-16 mb-4 bg-blue-50 rounded-full mx-auto">
                            <span className="text-3xl">🗄️</span>
                        </div>
                        <h3 className="mb-1 text-lg font-medium text-slate-800">No hay estanterías</h3>
                        <p className="text-sm text-slate-400">
                            {search ? 'No se encontraron resultados para la búsqueda' : 'No se han registrado estanterías'}
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {estanterias.map((estanteria) => (
                            <div
                                key={estanteria.id}
                                className="bg-white border border-slate-200/80 rounded-2xl p-6 hover:scale-[1.02] transition-transform shadow-xs flex flex-col justify-between"
                            >
                                <div className="flex flex-col items-center">
                                    {/* Imagen de la estantería */}
                                    <a
                                        href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                        className="flex items-center justify-center w-full h-48 mb-4 overflow-hidden bg-slate-50 border border-slate-100 rounded-xl group"
                                        title={`Ver documentos de ${estanteria.nombre}`}
                                    >
                                        {estanteria.imagen ? (
                                            <img
                                                src={`/storage/estanterias/${estanteria.imagen}`}
                                                alt={estanteria.nombre}
                                                className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-200"
                                            />
                                        ) : (
                                            <div className="text-center text-gray-400 group-hover:scale-110 transition-transform">
                                                <div className="mb-2 text-6xl">🗄️</div>
                                                <p className="text-xs font-medium">Sin imagen</p>
                                            </div>
                                        )}
                                    </a>

                                    {/* Nombre */}
                                    <div className="mb-4 text-center">
                                        <a
                                            href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                            className="text-lg font-bold text-blue-900 hover:text-blue-700 transition-colors block"
                                        >
                                            {estanteria.nombre}
                                        </a>
                                        <p className="text-xs text-gray-400 mt-0.5">
                                            Creado: {formatDate(estanteria.created_at)}
                                        </p>
                                        <span className="inline-flex items-center gap-1 mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            📁 {estanteria.documentos_count ?? 0} documentos
                                        </span>
                                    </div>
                                </div>

                                {/* Acciones */}
                                <div className="flex flex-wrap justify-center gap-2 pt-2 border-t border-slate-100">
                                    <a
                                        href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-xs shadow-sm"
                                    >
                                        <span>📂</span>
                                        Ver Documentos
                                    </a>
                                    {permissions.can_edit && (
                                        <button
                                            type="button"
                                            onClick={() => handleOpenEdit(estanteria)}
                                            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-sm"
                                        >
                                            <span>✏️</span>
                                            Editar
                                        </button>
                                    )}
                                    {permissions.can_destroy && (
                                        <button
                                            type="button"
                                            onClick={() => handleDelete(estanteria)}
                                            className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-sm hover:bg-red-600"
                                        >
                                            <span>🗑️</span>
                                            Eliminar
                                        </button>
                                    )}
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
                                onClick={() => fetchEstanterias(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-3">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} estanterías)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchEstanterias(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* MODAL CREAR / EDITAR ESTANTERÍA */}
            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 sm:p-8 border border-slate-200 my-8 transition-all animate-in fade-in zoom-in-95 duration-200">
                        {/* Cabecera del modal */}
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-xl font-bold text-slate-800">
                                    {modalMode === 'create' ? 'Nueva Estantería' : 'Editar Estantería'}
                                </h3>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    {modalMode === 'create' ? 'Registrar nueva estantería para organizar documentos' : 'Actualizar información e imagen de la estantería'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Campo: Nombre */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Nombre de la Estantería:
                                </label>
                                <input
                                    type="text"
                                    value={nombre}
                                    onChange={(e) => setNombre(e.target.value)}
                                    placeholder="Ingresa el nombre de la estantería..."
                                    className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                    required
                                    autoFocus
                                    disabled={submitting}
                                />
                                {formErrors.nombre && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.nombre[0]}</p>
                                )}
                            </div>

                            {/* Carga de Imagen (Original Upload Area) */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Imagen de la Estantería (Opcional):
                                </label>

                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 items-center">
                                    {/* Área de arrastrar o seleccionar */}
                                    <div
                                        onClick={() => fileInputRef.current?.click()}
                                        className="border-2 border-dashed border-slate-300 hover:border-blue-700 rounded-xl p-4 text-center cursor-pointer transition-all bg-slate-50/50 hover:bg-blue-50/40 min-h-[160px] flex flex-col items-center justify-center"
                                    >
                                        <div className="text-3xl mb-1">🗄️</div>
                                        <p className="text-xs font-bold text-slate-700">Seleccionar Imagen</p>
                                        <p className="text-[11px] text-slate-400 mt-0.5">JPEG, PNG, JPG, SVG (máx. 2MB)</p>
                                        <button
                                            type="button"
                                            className="mt-2.5 px-3 py-1.5 bg-blue-900 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 shadow-xs"
                                        >
                                            Examinar
                                        </button>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                                            onChange={handleFileChange}
                                            className="hidden"
                                        />
                                    </div>

                                    {/* Preview de la Imagen */}
                                    <div className="flex flex-col items-center justify-center p-3 border border-slate-200 rounded-xl bg-slate-50 min-h-[160px]">
                                        {imagePreview ? (
                                            <div className="relative group w-full flex flex-col items-center">
                                                <img
                                                    src={imagePreview}
                                                    alt="Vista previa"
                                                    className="max-h-28 object-contain rounded-lg shadow-xs"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        setImageFile(null);
                                                        setImagePreview(null);
                                                    }}
                                                    className="mt-2 px-2 py-1 text-[11px] font-semibold text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors"
                                                >
                                                    Quitar imagen
                                                </button>
                                            </div>
                                        ) : (
                                            <div className="text-center text-slate-400 py-4">
                                                <div className="text-2xl mb-1">🖼️</div>
                                                <p className="text-xs">Sin imagen seleccionada</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                {formErrors.imagen && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.imagen[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center gap-2 px-5 py-2 text-xs font-bold text-white rounded-xl shadow-md transition-all disabled:opacity-50"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%)'
                                    }}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                                <polyline points="17,21 17,13 7,13 7,21"/>
                                                <polyline points="7,3 7,8 15,8"/>
                                            </svg>
                                            <span>{modalMode === 'create' ? 'Guardar Estantería' : 'Actualizar Estantería'}</span>
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
