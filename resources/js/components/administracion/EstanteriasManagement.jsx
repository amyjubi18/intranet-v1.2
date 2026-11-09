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
    const [imgErrors, setImgErrors] = useState({});

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedEstanteria, setSelectedEstanteria] = useState(null);
    const [nombre, setNombre] = useState('');
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);
    const [isDragOver, setIsDragOver] = useState(false);
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
            processFile(file);
        }
    };

    const processFile = (file) => {
        setImageFile(file);
        const reader = new FileReader();
        reader.onloadend = () => {
            setImagePreview(reader.result);
        };
        reader.readAsDataURL(file);
    };

    // Formato de fecha original: d/m/Y
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

    // Eliminar estantería con el SweetAlert2 original
    const handleDelete = async (estanteria) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar la estantería "${estanteria.nombre}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
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
            {/* Filtros y controles (Diseño exacto al original Blade) */}
            <div className="sticky top-0 z-10 px-6 mb-6">
                <div className="p-6 glass rounded-2xl fade-in">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            {/* Búsqueda general */}
                            <div className="relative">
                                <input
                                    type="text"
                                    id="generalSearch"
                                    name="search_general"
                                    placeholder="Buscar estanterías..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className={`w-64 px-4 py-2 text-sm bg-white border border-white/20 backdrop-blur-md rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 ${
                                        search ? 'bg-blue-100 border-blue-500' : ''
                                    }`}
                                />
                            </div>

                            {/* Botón para limpiar filtros */}
                            <button
                                type="button"
                                onClick={() => setSearch('')}
                                className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                            >
                                🗑️ Limpiar
                            </button>
                        </div>

                        <div className="flex items-center gap-3">
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreate}
                                    className="btn inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Nueva
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Registro de estanterías (Diseño exacto al original Blade) */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 text-center glass rounded-2xl">
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
                    <div className="p-6 text-center col-span-full glass rounded-2xl">
                        <div className="flex flex-col items-center justify-center py-8">
                            <div className="flex items-center justify-center w-16 h-16 mb-4 bg-blue-100 rounded-full">
                                <span className="text-3xl">🗄️</span>
                            </div>
                            <h3 className="mb-1 text-lg font-medium text-gray-900">No hay estanterías</h3>
                            <p className="mb-4 text-sm text-gray-500">No se han registrado estanterías</p>
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreate}
                                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Registrar Estantería
                                </button>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 fade-in">
                        {estanterias.map((estanteria) => (
                            <div
                                key={estanteria.id}
                                className="glass rounded-2xl p-6 hover:scale-[1.02] transition-transform"
                            >
                                <div className="flex flex-col items-center">
                                    {/* Imagen de la estantería (Clickable) */}
                                    <a
                                        href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                        className="flex items-center justify-center w-full h-48 mb-4 overflow-hidden bg-gray-100 rounded-xl group"
                                        title={`Ver documentos de ${estanteria.nombre}`}
                                    >
                                        {estanteria.imagen && !imgErrors[estanteria.id] ? (
                                            <img
                                                src={`/storage/estanterias/${estanteria.imagen}`}
                                                alt={estanteria.nombre}
                                                className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-200"
                                                onError={() => setImgErrors(prev => ({ ...prev, [estanteria.id]: true }))}
                                            />
                                        ) : (
                                            <div className="text-center text-gray-400 group-hover:scale-110 transition-transform">
                                                <div className="mb-2 text-6xl">🗄️</div>
                                                <p className="text-sm">Sin imagen</p>
                                            </div>
                                        )}
                                    </a>

                                    {/* Nombre (Clickable) */}
                                    <div className="mb-4 text-center">
                                        <a
                                            href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                            className="text-lg font-semibold text-blue-900 hover:text-blue-700 transition-colors"
                                        >
                                            {estanteria.nombre}
                                        </a>
                                        <p className="text-xs text-gray-400">Creado: {formatDate(estanteria.created_at)}</p>
                                        <span className="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            📁 {estanteria.documentos_count ?? 0} documentos
                                        </span>
                                    </div>

                                    {/* Acciones */}
                                    <div className="flex flex-wrap justify-center gap-2">
                                        <a
                                            href={`/administracion/documentos?estanteria_id=${estanteria.id}`}
                                            className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-xs shadow-sm"
                                        >
                                            <span>📂</span>
                                            Ver Documentos
                                        </a>
                                        {permissions.can_edit && (
                                            <button
                                                type="button"
                                                onClick={() => handleOpenEdit(estanteria)}
                                                className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-sm"
                                            >
                                                <span>✏️</span>
                                                Editar
                                            </button>
                                        )}
                                        {permissions.can_destroy && (
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(estanteria)}
                                                className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-sm hover:bg-red-600"
                                            >
                                                <span>🗑️</span>
                                                Eliminar
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex justify-center mt-8">
                        <div className="p-4 glass rounded-xl flex items-center gap-2">
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

            {/* MODAL CREAR / EDITAR ESTANTERÍA (REPLICANDO EXACTO create.blade.php y edit.blade.php) */}
            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
                    <div className="glass rounded-2xl p-8 fade-in max-w-2xl w-full my-8 border border-white/20 shadow-2xl transition-all">
                        {/* Cabecera del modal */}
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800">
                                    {modalMode === 'create' ? 'Nueva Estantería' : 'Editar Estantería'}
                                </h3>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    {modalMode === 'create' ? 'Registrar nueva estantería en el sistema' : 'Actualizar información de la estantería'}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Formulario exacto a create.blade.php */}
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Campo: Nombre */}
                            <div className="mb-6">
                                <label className="block font-semibold mb-2 text-slate-800 text-sm">
                                    Nombre de la Estantería:
                                </label>
                                <input
                                    type="text"
                                    value={nombre}
                                    onChange={(e) => setNombre(e.target.value)}
                                    placeholder="Ingresa el nombre de la estantería..."
                                    className="bg-blue-100 border-none rounded-xl p-3 w-full text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm"
                                    required
                                    autoFocus
                                    disabled={submitting}
                                />
                                {formErrors.nombre && (
                                    <p className="mt-1 text-sm text-red-500">{formErrors.nombre[0]}</p>
                                )}
                            </div>

                            {/* Área de carga de imagen */}
                            <div className="mb-8">
                                <label className="block font-semibold mb-2 text-slate-800 text-sm">
                                    Imagen de la Estantería (Opcional):
                                </label>

                                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                    {/* Área de carga */}
                                    <div
                                        onClick={() => fileInputRef.current?.click()}
                                        onDragOver={(e) => {
                                            e.preventDefault();
                                            setIsDragOver(true);
                                        }}
                                        onDragLeave={() => setIsDragOver(false)}
                                        onDrop={(e) => {
                                            e.preventDefault();
                                            setIsDragOver(false);
                                            const file = e.dataTransfer.files?.[0];
                                            if (file) processFile(file);
                                        }}
                                        className={`border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all bg-white min-h-[200px] flex items-center justify-center ${
                                            isDragOver ? 'border-green-500 bg-green-50/20 scale-[1.02]' : 'border-slate-300 hover:border-blue-900'
                                        }`}
                                    >
                                        <div className="space-y-2">
                                            <div className="text-4xl">🗄️</div>
                                            <div>
                                                <p className="mb-2 text-sm font-semibold text-slate-800">Seleccionar Imagen</p>
                                                <p className="mb-3 text-xs text-slate-400">Arrastra y suelta una imagen o haz clic para seleccionar</p>
                                                <button
                                                    type="button"
                                                    className="btn btn-primary bg-blue-900 text-white inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold hover:bg-blue-800 transition-colors"
                                                >
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                        <polyline points="7,10 12,15 17,10"/>
                                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                                    </svg>
                                                    Seleccionar Archivo
                                                </button>
                                            </div>
                                            <div className="text-xs text-slate-500">
                                                Formatos: JPEG, PNG, JPG, GIF, SVG<br />
                                                Máximo: 2MB
                                            </div>
                                        </div>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                                            onChange={handleFileChange}
                                            className="hidden"
                                        />
                                    </div>

                                    {/* Vista previa */}
                                    <div>
                                        <label className="block font-semibold mb-2 text-slate-800 text-xs">
                                            Vista Previa:
                                        </label>
                                        <div className="glass rounded-xl p-4 min-h-[200px] flex items-center justify-center border border-slate-200">
                                            {imagePreview ? (
                                                <img
                                                    className="preview-image max-h-[170px] rounded-xl object-contain shadow-xs"
                                                    src={imagePreview}
                                                    alt="Vista previa"
                                                />
                                            ) : (
                                                <div className="text-center text-slate-400">
                                                    <div className="mb-3 text-5xl">🗄️</div>
                                                    <p className="mb-1 text-sm font-semibold">Sin imagen</p>
                                                    <p className="text-xs">La vista previa aparecerá aquí</p>
                                                </div>
                                            )}
                                        </div>
                                        {imagePreview && (
                                            <div className="p-3 mt-3 rounded-lg glass border border-slate-200 flex items-center justify-between text-xs">
                                                <span className="text-slate-600 font-medium">
                                                    {imageFile ? imageFile.name : 'Imagen actual'}
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setImageFile(null);
                                                        setImagePreview(null);
                                                    }}
                                                    className="text-xs text-red-600 hover:text-red-800 font-bold"
                                                >
                                                    Quitar Imagen
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                {formErrors.imagen && (
                                    <p className="mt-1 text-sm text-red-500 font-semibold">{formErrors.imagen[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción */}
                            <div className="flex items-center justify-between gap-4 pt-6 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl font-semibold bg-gray-500 text-white hover:bg-gray-600 text-xs transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95z"/>
                                    </svg>
                                    Regresar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center gap-2 px-6 py-2.5 text-xs font-bold text-white rounded-xl shadow-md transition-all disabled:opacity-50"
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
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
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
