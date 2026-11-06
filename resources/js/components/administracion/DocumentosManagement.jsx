import React, { useState, useEffect, useCallback, useRef } from 'react';
import Swal from 'sweetalert2';

export default function DocumentosManagement() {
    // Datos principales
    const [documentos, setDocumentos] = useState([]);
    const [estanterias, setEstanterias] = useState([]);
    const [categorias, setCategorias] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
    });

    // Estados de carga y error
    const [loadingEstanterias, setLoadingEstanterias] = useState(true);
    const [loadingDocs, setLoadingDocs] = useState(false);
    const [error, setError] = useState(null);

    // Selección de Estantería (Requisito clave: NO mostrar registros hasta seleccionar una estantería)
    const initialEstanteriaId = new URLSearchParams(window.location.search).get('estanteria_id') || null;
    const [selectedEstanteriaId, setSelectedEstanteriaId] = useState(initialEstanteriaId ? parseInt(initialEstanteriaId) : null);

    // Filtros de búsqueda
    const [searchGeneral, setSearchGeneral] = useState('');
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [textFilter, setTextFilter] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [sortBy, setSortBy] = useState('created_at');
    const [sortDirection, setSortDirection] = useState('desc');

    // Permisos
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modales de Documentos
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [editModalOpen, setEditModalOpen] = useState(false);
    const [pdfViewerOpen, setPdfViewerOpen] = useState(false);
    const [viewingPdf, setViewingPdf] = useState(null);
    const [pdfZoom, setPdfZoom] = useState(100);

    // Sub-Modal Rápido para Añadir Nueva Categoría desde el formulario
    const [quickCatModalOpen, setQuickCatModalOpen] = useState(false);
    const [quickCatName, setQuickCatName] = useState('');
    const [quickCatSubmitting, setQuickCatSubmitting] = useState(false);
    const [quickCatError, setQuickCatError] = useState(null);
    const [quickCatTargetPdfIndex, setQuickCatTargetPdfIndex] = useState(null);
    const [quickCatTargetExistingId, setQuickCatTargetExistingId] = useState(null);

    // Formulario de Creación / Edición
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        fecha: new Date().toISOString().split('T')[0],
        estanteria_id: '',
        categoria_id: ''
    });
    const [selectedDocumento, setSelectedDocumento] = useState(null);
    const [existingArchivos, setExistingArchivos] = useState([]);
    const [existingCategorias, setExistingCategorias] = useState({});
    const [newPdfs, setNewPdfs] = useState([]);
    const [pdfCategorias, setPdfCategorias] = useState({});
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    const fileInputRef = useRef(null);

    // Cargar Estanterías y Categorías iniciales
    const fetchCatalogos = useCallback(async () => {
        setLoadingEstanterias(true);
        try {
            const res = await fetch('/api/administracion/documentos?per_page=1', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Error al cargar estanterías y categorías');
            const json = await res.json();
            const resData = json.data || {};
            setEstanterias(resData.estanterias || []);
            setCategorias(resData.categorias || []);
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching catalogos:', err);
        } finally {
            setLoadingEstanterias(false);
        }
    }, []);

    // Cargar Documentos solo cuando hay una estantería seleccionada
    const fetchDocumentos = useCallback(async (page = 1) => {
        if (!selectedEstanteriaId) {
            setDocumentos([]);
            setLoadingDocs(false);
            return;
        }

        setLoadingDocs(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                per_page: 10,
                sort: sortBy,
                direction: sortDirection,
                estanteria_id: selectedEstanteriaId
            });

            if (searchGeneral.trim()) params.append('search_general', searchGeneral.trim());
            if (filterType) {
                params.append('filterType', filterType);
                if (filterType === 'name' || filterType === 'description') {
                    if (textFilter.trim()) params.append('search', textFilter.trim());
                } else if (filterType === 'categoria_id' && secondFilter) {
                    params.append('secondFilter', secondFilter);
                } else if (filterType === 'fecha') {
                    if (fechaDesde) params.append('fecha_desde', fechaDesde);
                    if (fechaHasta) params.append('fecha_hasta', fechaHasta);
                }
            }

            const res = await fetch(`/api/administracion/documentos?${params.toString()}`, {
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
            const resData = json.data?.documentos || json.data || {};

            setDocumentos(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 10
            });

            if (json.data?.categorias) setCategorias(json.data.categorias);
            if (json.data?.estanterias) setEstanterias(json.data.estanterias);
        } catch (err) {
            console.error('Error fetching documentos:', err);
            setError(err.message || 'No se pudieron cargar los documentos.');
        } finally {
            setLoadingDocs(false);
        }
    }, [selectedEstanteriaId, searchGeneral, filterType, secondFilter, textFilter, fechaDesde, fechaHasta, sortBy, sortDirection]);

    useEffect(() => {
        fetchCatalogos();
    }, [fetchCatalogos]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchDocumentos(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [selectedEstanteriaId, searchGeneral, filterType, secondFilter, textFilter, fechaDesde, fechaHasta, sortBy, sortDirection, fetchDocumentos]);

    // Seleccionar estantería
    const handleSelectEstanteria = (estanteriaId) => {
        if (selectedEstanteriaId === estanteriaId) {
            return;
        }
        setSelectedEstanteriaId(estanteriaId);
        const url = new URL(window.location);
        url.searchParams.set('estanteria_id', estanteriaId);
        window.history.pushState({}, '', url);
    };

    // Deseleccionar estantería
    const handleDeselectEstanteria = () => {
        setSelectedEstanteriaId(null);
        setDocumentos([]);
        const url = new URL(window.location);
        url.searchParams.delete('estanteria_id');
        window.history.pushState({}, '', url);
    };

    // Formatear Fecha d/m/Y
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    };

    // Abrir Modal de Creación de Documento
    const handleOpenCreateModal = () => {
        setFormData({
            name: '',
            description: '',
            fecha: new Date().toISOString().split('T')[0],
            estanteria_id: selectedEstanteriaId || '',
            categoria_id: ''
        });
        setNewPdfs([]);
        setPdfCategorias({});
        setFormErrors({});
        setCreateModalOpen(true);
    };

    // Abrir Modal de Edición de Documento
    const handleOpenEditModal = (documento) => {
        setSelectedDocumento(documento);
        setFormData({
            name: documento.name || '',
            description: documento.description || '',
            fecha: documento.fecha ? documento.fecha.split('T')[0] : new Date().toISOString().split('T')[0],
            estanteria_id: documento.estanteria_id || '',
            categoria_id: documento.categoria_id || ''
        });
        setExistingArchivos(documento.archivos || []);
        const catMap = {};
        (documento.archivos || []).forEach(arch => {
            catMap[arch.id] = arch.categoria_id || '';
        });
        setExistingCategorias(catMap);
        setNewPdfs([]);
        setPdfCategorias({});
        setFormErrors({});
        setEditModalOpen(true);
    };

    // Abrir Sub-Modal para añadir Nueva Categoría
    const handleOpenQuickCategoryModal = (pdfIndex = null, existingArchivoId = null) => {
        setQuickCatTargetPdfIndex(pdfIndex);
        setQuickCatTargetExistingId(existingArchivoId);
        setQuickCatName('');
        setQuickCatError(null);
        setQuickCatModalOpen(true);
    };

    // Guardar Nueva Categoría desde el Sub-Modal
    const handleQuickCategorySubmit = async (e) => {
        e.preventDefault();
        if (!quickCatName.trim()) {
            setQuickCatError('El nombre de la categoría es obligatorio');
            return;
        }

        setQuickCatSubmitting(true);
        setQuickCatError(null);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/api/administracion/categorias/store-ajax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ name: quickCatName.trim() })
            });

            const json = await res.json();
            if (!res.ok) {
                throw new Error(json.message || 'Error al crear la categoría');
            }

            const createdCategory = json.categoria || json.data;

            // Actualizar el array local de categorías de forma ordenada
            setCategorias(prev => {
                const updated = [...prev, createdCategory];
                return updated.sort((a, b) => a.name.localeCompare(b.name));
            });

            // Asignar automáticamente la nueva categoría según dónde se originó
            if (quickCatTargetExistingId) {
                setExistingCategorias(prev => ({
                    ...prev,
                    [quickCatTargetExistingId]: createdCategory.id
                }));
            } else if (quickCatTargetPdfIndex !== null) {
                setPdfCategorias(prev => ({
                    ...prev,
                    [quickCatTargetPdfIndex]: createdCategory.id
                }));
            } else {
                setFormData(prev => ({
                    ...prev,
                    categoria_id: createdCategory.id
                }));
            }

            Swal.fire({
                icon: 'success',
                title: '¡Categoría creada!',
                text: `La categoría "${createdCategory.name}" fue registrada y seleccionada`,
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-2xl z-[80]' }
            });

            setQuickCatModalOpen(false);
            setQuickCatName('');
            setQuickCatTargetPdfIndex(null);
            setQuickCatTargetExistingId(null);
        } catch (err) {
            console.error('Error creating quick category:', err);
            setQuickCatError(err.message || 'No se pudo crear la categoría');
        } finally {
            setQuickCatSubmitting(false);
        }
    };

    // Manejar selección de archivos PDF
    const handleFilesSelect = (files) => {
        const fileList = Array.from(files);
        const validPdfs = fileList.filter(f => f.type === 'application/pdf' || f.name.toLowerCase().endsWith('.pdf'));

        if (validPdfs.length < fileList.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Formato no admitido',
                text: 'Solo se permiten archivos en formato PDF.',
                timer: 2000,
                showConfirmButton: false
            });
        }

        setNewPdfs(prev => [...prev, ...validPdfs]);
    };

    // Quitar un archivo PDF nuevo
    const handleRemoveNewPdf = (index) => {
        setNewPdfs(prev => prev.filter((_, i) => i !== index));
        const updatedCat = { ...pdfCategorias };
        delete updatedCat[index];
        setPdfCategorias(updatedCat);
    };

    // Eliminar archivo existente desde el modal de edición
    const handleDeleteExistingArchivo = async (archivoId) => {
        const result = await Swal.fire({
            title: '¿Eliminar archivo PDF?',
            text: 'Este archivo se eliminará de forma permanente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            customClass: { popup: 'rounded-2xl shadow-xl' }
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/api/administracion/documentos/archivo/${archivoId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('No se pudo eliminar el archivo.');

            setExistingArchivos(prev => prev.filter(a => a.id !== archivoId));
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'Archivo eliminado correctamente',
                timer: 1500,
                showConfirmButton: false
            });

            fetchDocumentos(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message,
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Guardar Nuevo Documento
    const handleCreateSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});

        if (!formData.name.trim() || !formData.description.trim() || !formData.fecha || !formData.estanteria_id) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor completa todos los campos obligatorios.',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }

        setSubmitting(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const payload = new FormData();
            payload.append('name', formData.name.trim());
            payload.append('description', formData.description.trim());
            payload.append('fecha', formData.fecha);
            payload.append('estanteria_id', formData.estanteria_id);
            if (formData.categoria_id) payload.append('categoria_id', formData.categoria_id);

            newPdfs.forEach((file, idx) => {
                payload.append('pdfs[]', file);
                const catId = pdfCategorias[idx] || formData.categoria_id;
                if (catId) payload.append('categoria_archivo_id[]', catId);
            });

            const res = await fetch('/api/administracion/documentos', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload
            });

            const json = await res.json();
            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    setFormErrors(json.errors);
                    return;
                }
                throw new Error(json.message || 'Error al guardar el documento');
            }

            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Documento registrado exitosamente',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            setCreateModalOpen(false);
            if (parseInt(formData.estanteria_id) !== selectedEstanteriaId) {
                setSelectedEstanteriaId(parseInt(formData.estanteria_id));
            } else {
                fetchDocumentos(1);
            }
            fetchCatalogos();
        } catch (err) {
            console.error('Error creating documento:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el documento',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Actualizar Documento
    const handleUpdateSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});

        setSubmitting(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const payload = new FormData();
            payload.append('name', formData.name.trim());
            payload.append('description', formData.description.trim());
            payload.append('fecha', formData.fecha);
            payload.append('estanteria_id', formData.estanteria_id);
            if (formData.categoria_id) payload.append('categoria_id', formData.categoria_id);

            Object.keys(existingCategorias).forEach(archId => {
                payload.append(`categoria_existente[${archId}]`, existingCategorias[archId] || '');
            });

            newPdfs.forEach((file, idx) => {
                payload.append('pdfs[]', file);
                const catId = pdfCategorias[idx] || formData.categoria_id;
                if (catId) payload.append('categoria_archivo_id[]', catId);
            });

            const res = await fetch(`/api/administracion/documentos/${selectedDocumento.id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload
            });

            const json = await res.json();
            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    setFormErrors(json.errors);
                    return;
                }
                throw new Error(json.message || 'Error al actualizar el documento');
            }

            Swal.fire({
                icon: 'success',
                title: '¡Actualizado!',
                text: 'Documento actualizado exitosamente',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            setEditModalOpen(false);
            fetchDocumentos(pagination.current_page);
            fetchCatalogos();
        } catch (err) {
            console.error('Error updating documento:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo actualizar el documento',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar Documento Completo
    const handleDeleteDocumento = async (documento) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el documento "${documento.name}" y todos sus archivos asociados?`,
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
            const res = await fetch(`/api/administracion/documentos/${documento.id}`, {
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
                text: 'El documento ha sido eliminado con éxito',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchDocumentos(pagination.current_page);
            fetchCatalogos();
        } catch (err) {
            console.error('Error deleting documento:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el documento',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Abrir Modal de Visualización de PDF
    const handleOpenPdfViewer = (archivo) => {
        setViewingPdf(archivo);
        setPdfZoom(100);
        setPdfViewerOpen(true);
    };

    return (
        <div className="w-full">
            {/* BARRA DE FILTROS Y CONTROLES (DISEÑO ORIGINAL EXACTO) */}
            <div className="sticky top-0 z-10 px-6 mb-6">
                <div className="p-6 bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-2xl shadow-xs transition-all">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-4">
                            {/* Botón para mostrar filtros avanzados */}
                            <button
                                type="button"
                                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                className={`px-4 py-2 text-sm text-white rounded-xl transition-all shadow-xs ${
                                    showAdvancedFilters ? 'bg-blue-800 ring-2 ring-blue-500/50' : 'bg-blue-900 hover:bg-blue-800'
                                }`}
                            >
                                Filtros Avanzados
                            </button>

                            {/* Filtro principal - Tipo de campo */}
                            {showAdvancedFilters && (
                                <>
                                    <select
                                        value={filterType}
                                        onChange={(e) => {
                                            setFilterType(e.target.value);
                                            setSecondFilter('');
                                            setTextFilter('');
                                            setFechaDesde('');
                                            setFechaHasta('');
                                        }}
                                        className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-slate-800"
                                    >
                                        <option value="">Seleccionar filtro...</option>
                                        <option value="name">Nº Expediente</option>
                                        <option value="description">Descripción</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="categoria_id">Categoría</option>
                                    </select>

                                    {/* Filtro secundario según selección */}
                                    {filterType === 'categoria_id' && (
                                        <select
                                            value={secondFilter}
                                            onChange={(e) => setSecondFilter(e.target.value)}
                                            className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-slate-800"
                                        >
                                            <option value="">Seleccionar categoría...</option>
                                            {categorias.map(cat => (
                                                <option key={cat.id} value={cat.id}>{cat.name}</option>
                                            ))}
                                        </select>
                                    )}

                                    {/* Rango de fechas */}
                                    {filterType === 'fecha' && (
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="date"
                                                value={fechaDesde}
                                                onChange={(e) => setFechaDesde(e.target.value)}
                                                className="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/50"
                                            />
                                            <span className="text-xs text-slate-400">a</span>
                                            <input
                                                type="date"
                                                value={fechaHasta}
                                                onChange={(e) => setFechaHasta(e.target.value)}
                                                className="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/50"
                                            />
                                        </div>
                                    )}

                                    {/* Texto para expediente o descripción */}
                                    {(filterType === 'name' || filterType === 'description') && (
                                        <input
                                            type="text"
                                            value={textFilter}
                                            onChange={(e) => setTextFilter(e.target.value)}
                                            placeholder={`Buscar por ${filterType === 'name' ? 'expediente' : 'descripción'}...`}
                                            className="w-56 px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-slate-800"
                                        />
                                    )}
                                </>
                            )}

                            {/* Búsqueda general */}
                            <div className="relative">
                                <input
                                    type="text"
                                    value={searchGeneral}
                                    onChange={(e) => setSearchGeneral(e.target.value)}
                                    placeholder="Búsqueda general..."
                                    className="w-64 px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 text-slate-800"
                                />
                                {searchGeneral && (
                                    <button
                                        type="button"
                                        onClick={() => setSearchGeneral('')}
                                        className="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {/* Botón para limpiar filtros */}
                            {(searchGeneral || filterType || textFilter || secondFilter || fechaDesde || fechaHasta) && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearchGeneral('');
                                        setFilterType('');
                                        setSecondFilter('');
                                        setTextFilter('');
                                        setFechaDesde('');
                                        setFechaHasta('');
                                        setShowAdvancedFilters(false);
                                    }}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1">
                                <span className="text-xs text-gray-500">Ordenar:</span>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSortBy('name');
                                        setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
                                    }}
                                    className={`inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg transition-colors ${
                                        sortBy === 'name' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-slate-600 hover:bg-slate-100'
                                    }`}
                                >
                                    Expediente {sortBy === 'name' ? (sortDirection === 'asc' ? '⬆️' : '⬇️') : '⇅'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSortBy('description');
                                        setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc');
                                    }}
                                    className={`inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg transition-colors ${
                                        sortBy === 'description' ? 'bg-blue-100 text-blue-800 font-bold' : 'text-slate-600 hover:bg-slate-100'
                                    }`}
                                >
                                    Descripción {sortBy === 'description' ? (sortDirection === 'asc' ? '⬆️' : '⬇️') : '⇅'}
                                </button>
                            </div>
                        </div>

                        {/* Botón Nuevo Registro (abre modal) */}
                        <div className="flex items-center gap-3">
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreateModal}
                                    className="btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Nuevo
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* SECCIÓN DE ESTANTERÍAS (DISEÑO ORIGINAL CON ANIMACIÓN ZOOM) */}
            <div className="px-6 mb-8">
                <div className="p-6 bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-2xl shadow-xs">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <h3 className="text-lg font-bold text-blue-900 flex items-center gap-2">
                            <span>📚</span>
                            <span>Selecciona una Estantería</span>
                        </h3>
                        {selectedEstanteriaId && (
                            <button
                                type="button"
                                onClick={handleDeselectEstanteria}
                                className="text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5"
                            >
                                <span>←</span>
                                <span>Deseleccionar Estantería</span>
                            </button>
                        )}
                    </div>

                    {loadingEstanterias ? (
                        <div className="flex items-center justify-center py-10">
                            <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    ) : estanterias.length === 0 ? (
                        <div className="flex flex-col items-center justify-center p-8 text-center">
                            <div className="text-5xl mb-3">🗄️</div>
                            <p className="text-sm text-slate-500">No hay estanterías registradas.</p>
                            <a
                                href="/administracion/estanterias"
                                className="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 rounded-xl hover:bg-blue-800 transition-all"
                            >
                                <span>➕</span>
                                Crear Estantería
                            </a>
                        </div>
                    ) : (
                        <div className="estanterias-container flex flex-wrap gap-4 justify-center p-4 rounded-2xl bg-white/40">
                            {estanterias.map((estanteria, index) => {
                                const isSelected = selectedEstanteriaId === estanteria.id;
                                const bgEstanteria = index % 2 === 0 ? '#1e3a8a' : '#1e40af';

                                return (
                                    <div
                                        key={estanteria.id}
                                        onClick={() => handleSelectEstanteria(estanteria.id)}
                                        className={`estanteria-block cursor-pointer transition-all ${isSelected ? 'ring-4 ring-offset-2 ring-blue-600 scale-105' : ''}`}
                                        style={{ width: '28%', minWidth: '180px' }}
                                        title={`Ver documentos de ${estanteria.nombre}`}
                                    >
                                        <div
                                            className="estanteria-block__item p-4 rounded-2xl flex flex-col items-center justify-center text-white shadow-lg transition-transform hover:scale-105"
                                            style={{ background: bgEstanteria }}
                                        >
                                            {estanteria.imagen ? (
                                                <img
                                                    src={`/storage/estanterias/${estanteria.imagen}`}
                                                    alt={estanteria.nombre}
                                                    className="w-16 h-16 object-contain mb-2 rounded-lg"
                                                />
                                            ) : (
                                                <div className="text-4xl mb-2">🗄️</div>
                                            )}
                                            <span className="estanteria-block__name font-bold text-base text-center line-clamp-1">
                                                {estanteria.nombre}
                                            </span>
                                            <span className="estanteria-block__count text-xs text-white/80 mt-1 font-medium">
                                                {estanteria.documentos_count ?? 0} docs
                                            </span>
                                            {isSelected && (
                                                <span className="mt-2 text-[10px] bg-white text-blue-900 px-2 py-0.5 rounded-full font-extrabold uppercase tracking-wide">
                                                    ✓ Seleccionada
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>

            {/* CONDICIONAL: SI NO HAY ESTANTERÍA SELECCIONADA, MOSTRAR MENSAJE */}
            {!selectedEstanteriaId ? (
                <div className="px-6 mb-8">
                    <div className="bg-white/80 backdrop-blur-md border border-slate-200/80 rounded-2xl p-10 text-center shadow-xs">
                        <div className="w-16 h-16 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                            📚
                        </div>
                        <h3 className="text-lg font-bold text-slate-800 mb-1">
                            Selecciona una estantería para visualizar sus documentos
                        </h3>
                        <p className="text-sm text-slate-500 max-w-md mx-auto">
                            Para optimizar la carga y organización del sistema, haz clic en cualquiera de las estanterías de arriba para explorar sus expedientes y archivos PDF.
                        </p>
                    </div>
                </div>
            ) : (
                /* REGISTRO DE DOCUMENTOS CUANDO HAY ESTANTERÍA SELECCIONADA */
                <div className="px-6 mb-8">
                    {/* Header de estantería activa */}
                    <div className="mb-4 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-sm font-semibold text-slate-600">Mostrando documentos de:</span>
                            <span className="text-sm font-bold text-blue-900 px-3 py-1 bg-blue-50 border border-blue-200 rounded-lg">
                                {estanterias.find(e => e.id === selectedEstanteriaId)?.nombre || `Estantería #${selectedEstanteriaId}`}
                            </span>
                        </div>
                    </div>

                    {loadingDocs ? (
                        <div className="flex flex-col items-center justify-center py-16 text-center bg-white/80 rounded-2xl border border-slate-200">
                            <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mb-3"></div>
                            <p className="text-sm font-semibold text-slate-700">Cargando documentos de la estantería...</p>
                        </div>
                    ) : error ? (
                        <div className="p-6 text-center text-red-600 bg-red-50 rounded-2xl border border-red-200">
                            <p className="text-sm font-semibold mb-2">{error}</p>
                            <button
                                type="button"
                                onClick={() => fetchDocumentos(pagination.current_page)}
                                className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-lg hover:bg-red-700"
                            >
                                Reintentar
                            </button>
                        </div>
                    ) : documentos.length === 0 ? (
                        <div className="p-10 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
                            <div className="flex items-center justify-center w-16 h-16 mb-4 bg-blue-50 rounded-full mx-auto">
                                <span className="text-3xl">📂</span>
                            </div>
                            <h3 className="mb-1 text-lg font-bold text-slate-800">No hay documentos en esta estantería</h3>
                            <p className="mb-4 text-sm text-slate-400">
                                No se han encontrado expedientes registrados en esta estantería.
                            </p>
                            {permissions.can_create && (
                                <button
                                    type="button"
                                    onClick={handleOpenCreateModal}
                                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-md"
                                >
                                    <span>➕</span>
                                    Subir Documento
                                </button>
                            )}
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {documentos.map((documento) => (
                                <div
                                    key={documento.id}
                                    className="bg-white border border-slate-200/80 rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                                >
                                    <div className="grid items-center grid-cols-1 gap-6 lg:grid-cols-4">
                                        {/* Info del documento */}
                                        <div className="text-center lg:text-left">
                                            <p className="mb-1 text-xs font-semibold text-blue-900">📄 Nº Expediente</p>
                                            <p className="text-sm font-bold text-slate-900">{documento.name}</p>
                                            <p className="mt-2 mb-1 text-xs font-semibold text-blue-900">📝 Descripción</p>
                                            <p className="text-sm font-medium text-slate-700 truncate" title={documento.description}>
                                                {documento.description}
                                            </p>
                                        </div>

                                        {/* Fecha y Categoría Principal */}
                                        <div className="text-center lg:text-left">
                                            <p className="mb-1 text-xs font-semibold text-blue-900">📅 Fecha</p>
                                            <p className="text-xs text-slate-600 font-medium">{formatDate(documento.fecha)}</p>
                                            {documento.categoria && (
                                                <div className="mt-2">
                                                    <span className="inline-block px-2 py-0.5 bg-blue-50 text-blue-800 border border-blue-200 rounded-md text-xs font-semibold">
                                                        📁 {documento.categoria.name}
                                                    </span>
                                                </div>
                                            )}
                                        </div>

                                        {/* Archivos PDF */}
                                        <div className="text-center lg:text-left">
                                            <p className="mb-1.5 text-xs font-semibold text-blue-900">
                                                📎 Archivos PDF ({documento.archivos?.length || 0})
                                            </p>
                                            {documento.archivos && documento.archivos.length > 0 ? (
                                                <div>
                                                    <div className="flex flex-wrap justify-center gap-2 lg:justify-start">
                                                        {documento.archivos.slice(0, 4).map((archivo) => (
                                                            <div
                                                                key={archivo.id}
                                                                onClick={() => handleOpenPdfViewer(archivo)}
                                                                className="relative flex items-center justify-center w-10 bg-red-100 border-2 border-red-200 rounded-lg cursor-pointer pdf-thumbnail h-11 hover:border-red-400 group shadow-xs transition-transform hover:scale-105"
                                                                title={`Ver ${archivo.nombre_archivo}`}
                                                            >
                                                                <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                                                                    <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                                                                </svg>
                                                            </div>
                                                        ))}
                                                        {documento.archivos.length > 4 && (
                                                            <span className="flex items-center justify-center w-10 h-11 bg-slate-100 border border-slate-300 rounded-lg text-xs font-bold text-slate-600">
                                                                +{documento.archivos.length - 4}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="mt-2 flex flex-wrap gap-1">
                                                        {documento.archivos.map((arch) => (
                                                            arch.categoria && (
                                                                <span key={arch.id} className="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded border border-slate-200">
                                                                    {arch.categoria.name}
                                                                </span>
                                                            )
                                                        ))}
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="flex items-center justify-center w-10 bg-slate-100 border-2 border-slate-200 rounded-lg h-11 mx-auto lg:mx-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                        <polyline points="14 2 14 8 20 8"/>
                                                    </svg>
                                                </div>
                                            )}
                                        </div>

                                        {/* Acciones */}
                                        <div className="text-center lg:text-right">
                                            <div className="flex flex-wrap justify-center gap-2 lg:justify-end">
                                                {permissions.can_edit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleOpenEditModal(documento)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white border border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-sm"
                                                    >
                                                        <span>✏️</span>
                                                        Editar
                                                    </button>
                                                )}
                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDeleteDocumento(documento)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-sm hover:bg-red-600"
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
                                    onClick={() => fetchDocumentos(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1}
                                    className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                                >
                                    Anterior
                                </button>
                                <span className="text-xs font-bold text-blue-900 px-3">
                                    Página {pagination.current_page} de {pagination.last_page} ({pagination.total} expedientes)
                                </span>
                                <button
                                    type="button"
                                    onClick={() => fetchDocumentos(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= pagination.last_page}
                                    className="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                                >
                                    Siguiente
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* MODAL CREAR DOCUMENTO (REPLICANDO create.blade.php) */}
            {createModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-6 sm:p-8 border border-slate-200 my-8 max-h-[92vh] overflow-y-auto transition-all animate-in fade-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800">Cargar Documento</h3>
                                <p className="text-xs text-slate-500 mt-0.5">Registrar nuevo documento con uno o más PDFs</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setCreateModalOpen(false)}
                                disabled={submitting}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleCreateSubmit} className="space-y-6">
                            {/* Número de Expediente y Descripción */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Número del Expediente:
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.name}
                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        placeholder="Ingresa el número de expediente..."
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    />
                                    {formErrors.name && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.name[0]}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Descripción del Archivo:
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.description}
                                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                        placeholder="Ingresa la descripción del documento..."
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    />
                                    {formErrors.description && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.description[0]}</p>}
                                </div>
                            </div>

                            {/* Fecha */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Fecha del Documento:
                                </label>
                                <input
                                    type="date"
                                    value={formData.fecha}
                                    onChange={(e) => setFormData({ ...formData, fecha: e.target.value })}
                                    className="bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                    required
                                />
                                {formErrors.fecha && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.fecha[0]}</p>}
                            </div>

                            {/* Estantería y Categoría */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Estantería 📚:
                                    </label>
                                    <select
                                        value={formData.estanteria_id}
                                        onChange={(e) => setFormData({ ...formData, estanteria_id: e.target.value })}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    >
                                        <option value="">Seleccionar estantería...</option>
                                        {estanterias.map(est => (
                                            <option key={est.id} value={est.id}>{est.nombre}</option>
                                        ))}
                                    </select>
                                    {formErrors.estanteria_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.estanteria_id[0]}</p>}
                                </div>

                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <label className="block text-sm font-semibold text-slate-700">
                                            Categoría 📁:
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => handleOpenQuickCategoryModal()}
                                            className="text-[11px] font-bold text-blue-900 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded-lg flex items-center gap-1 transition-colors"
                                        >
                                            <span>➕</span>
                                            <span>Nueva Categoría</span>
                                        </button>
                                    </div>
                                    <select
                                        value={formData.categoria_id}
                                        onChange={(e) => {
                                            if (e.target.value === '__NEW__') {
                                                handleOpenQuickCategoryModal();
                                            } else {
                                                setFormData({ ...formData, categoria_id: e.target.value });
                                            }
                                        }}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                    >
                                        <option value="">Seleccionar categoría...</option>
                                        <option value="__NEW__" className="text-blue-700 font-bold">➕ + Añadir nueva categoría...</option>
                                        {categorias.map(cat => (
                                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.categoria_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.categoria_id[0]}</p>}
                                </div>
                            </div>

                            {/* Área de Carga de Múltiples PDFs */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Documentos PDF (puede seleccionar múltiples):
                                </label>

                                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                    {/* Drop Area */}
                                    <div
                                        onClick={() => fileInputRef.current?.click()}
                                        className="border-2 border-dashed border-slate-300 hover:border-blue-700 rounded-xl p-6 text-center cursor-pointer transition-all bg-slate-50/50 hover:bg-blue-50/40 min-h-[180px] flex flex-col items-center justify-center"
                                    >
                                        <div className="text-3xl mb-1">📄</div>
                                        <p className="text-sm font-bold text-slate-800">Seleccionar PDFs</p>
                                        <p className="text-xs text-slate-400 mt-1">Arrastra y suelta PDFs aquí o haz clic para seleccionar</p>
                                        <button
                                            type="button"
                                            className="mt-3 px-4 py-1.5 bg-blue-900 text-white text-xs font-semibold rounded-lg hover:bg-blue-800 shadow-xs flex items-center gap-1.5"
                                        >
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                <polyline points="7,10 12,15 17,10"/>
                                                <line x1="12" y1="15" x2="12" y2="3"/>
                                            </svg>
                                            Seleccionar Archivos
                                        </button>
                                        <span className="text-[11px] text-slate-400 mt-2">Máximo: 10MB por archivo</span>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept=".pdf,application/pdf"
                                            multiple
                                            onChange={(e) => handleFilesSelect(e.target.files)}
                                            className="hidden"
                                        />
                                    </div>

                                    {/* Lista de PDFs seleccionados */}
                                    <div className="bg-slate-50 border border-slate-200 rounded-xl p-4 min-h-[180px] flex flex-col justify-between">
                                        {newPdfs.length === 0 ? (
                                            <div className="flex flex-col items-center justify-center h-full py-8 text-center text-slate-400">
                                                <div className="text-4xl mb-2">📄</div>
                                                <p className="text-sm font-semibold">Sin documentos</p>
                                                <p className="text-xs">Selecciona uno o más PDFs</p>
                                            </div>
                                        ) : (
                                            <div className="space-y-2 max-h-56 overflow-y-auto pr-1">
                                                {newPdfs.map((file, idx) => (
                                                    <div key={idx} className="flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-lg text-xs">
                                                        <div className="flex items-center gap-2 overflow-hidden">
                                                            <span className="text-red-600 font-bold">PDF</span>
                                                            <span className="truncate max-w-[140px] font-medium text-slate-800" title={file.name}>
                                                                {file.name}
                                                            </span>
                                                            <span className="text-[10px] text-slate-400">
                                                                ({(file.size / 1024 / 1024).toFixed(2)} MB)
                                                            </span>
                                                        </div>
                                                        <div className="flex items-center gap-1.5">
                                                            <select
                                                                value={pdfCategorias[idx] || formData.categoria_id || ''}
                                                                onChange={(e) => {
                                                                    if (e.target.value === '__NEW__') {
                                                                        handleOpenQuickCategoryModal(idx);
                                                                    } else {
                                                                        setPdfCategorias({ ...pdfCategorias, [idx]: e.target.value });
                                                                    }
                                                                }}
                                                                className="text-[11px] py-1 px-2 border border-slate-200 rounded-md bg-white max-w-[110px]"
                                                            >
                                                                <option value="">Categoría...</option>
                                                                <option value="__NEW__" className="text-blue-700 font-bold">➕ + Nueva...</option>
                                                                {categorias.map(c => (
                                                                    <option key={c.id} value={c.id}>{c.name}</option>
                                                                ))}
                                                            </select>
                                                            <button
                                                                type="button"
                                                                onClick={() => handleRemoveNewPdf(idx)}
                                                                className="text-red-500 hover:text-red-700 font-bold p-1"
                                                                title="Quitar"
                                                            >
                                                                ✕
                                                            </button>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {newPdfs.length > 0 && (
                                            <div className="flex items-center justify-between pt-2 border-t border-slate-200 mt-2 text-xs">
                                                <span className="font-semibold text-slate-700">Total: {newPdfs.length} archivo(s)</span>
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setNewPdfs([]);
                                                        setPdfCategorias({});
                                                    }}
                                                    className="text-red-600 hover:text-red-800 font-semibold"
                                                >
                                                    Limpiar todo
                                                </button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Botones de Acción */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={() => setCreateModalOpen(false)}
                                    disabled={submitting}
                                    className="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                                >
                                    Cancelar
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
                                            <span>Guardar Documento(s)</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL EDITAR DOCUMENTO (REPLICANDO edit.blade.php) */}
            {editModalOpen && selectedDocumento && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-6 sm:p-8 border border-slate-200 my-8 max-h-[92vh] overflow-y-auto transition-all animate-in fade-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800">Editar Documento</h3>
                                <p className="text-xs text-slate-500 mt-0.5">Actualizar expediente y administrar sus archivos PDF</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setEditModalOpen(false)}
                                disabled={submitting}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form onSubmit={handleUpdateSubmit} className="space-y-6">
                            {/* Número de Expediente y Descripción */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Número del Expediente:
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.name}
                                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Descripción del Archivo:
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.description}
                                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    />
                                </div>
                            </div>

                            {/* Fecha */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Fecha del Documento:
                                </label>
                                <input
                                    type="date"
                                    value={formData.fecha}
                                    onChange={(e) => setFormData({ ...formData, fecha: e.target.value })}
                                    className="bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                    required
                                />
                            </div>

                            {/* Estantería y Categoría */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Estantería 📚:
                                    </label>
                                    <select
                                        value={formData.estanteria_id}
                                        onChange={(e) => setFormData({ ...formData, estanteria_id: e.target.value })}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                        required
                                    >
                                        <option value="">Seleccionar estantería...</option>
                                        {estanterias.map(est => (
                                            <option key={est.id} value={est.id}>{est.nombre}</option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <label className="block text-sm font-semibold text-slate-700">
                                            Categoría Principal 📁:
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => handleOpenQuickCategoryModal()}
                                            className="text-[11px] font-bold text-blue-900 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded-lg flex items-center gap-1 transition-colors"
                                        >
                                            <span>➕</span>
                                            <span>Nueva Categoría</span>
                                        </button>
                                    </div>
                                    <select
                                        value={formData.categoria_id}
                                        onChange={(e) => {
                                            if (e.target.value === '__NEW__') {
                                                handleOpenQuickCategoryModal();
                                            } else {
                                                setFormData({ ...formData, categoria_id: e.target.value });
                                            }
                                        }}
                                        className="w-full bg-blue-100 border-none rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                    >
                                        <option value="">Seleccionar categoría...</option>
                                        <option value="__NEW__" className="text-blue-700 font-bold">➕ + Añadir nueva categoría...</option>
                                        {categorias.map(cat => (
                                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Archivos PDF Existentes con eliminación y asignación de categoría */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Archivos PDF Adjuntos ({existingArchivos.length}):
                                </label>
                                {existingArchivos.length === 0 ? (
                                    <p className="text-xs text-slate-400 italic">No hay archivos PDF vinculados a este expediente.</p>
                                ) : (
                                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 max-h-48 overflow-y-auto pr-1">
                                        {existingArchivos.map((arch) => (
                                            <div key={arch.id} className="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs">
                                                <div className="flex items-center gap-2 overflow-hidden">
                                                    <span className="text-red-600 font-bold text-sm">PDF</span>
                                                    <div className="overflow-hidden">
                                                        <p className="font-semibold text-slate-800 truncate" title={arch.nombre_archivo}>
                                                            {arch.nombre_archivo}
                                                        </p>
                                                        <select
                                                            value={existingCategorias[arch.id] || ''}
                                                            onChange={(e) => {
                                                                if (e.target.value === '__NEW__') {
                                                                    handleOpenQuickCategoryModal(null, arch.id);
                                                                } else {
                                                                    setExistingCategorias({ ...existingCategorias, [arch.id]: e.target.value });
                                                                }
                                                            }}
                                                            className="text-[10px] mt-1 py-0.5 px-1.5 border border-slate-200 rounded bg-white text-slate-700"
                                                        >
                                                            <option value="">Sin categoría</option>
                                                            <option value="__NEW__" className="text-blue-700 font-bold">➕ + Nueva...</option>
                                                            {categorias.map(c => (
                                                                <option key={c.id} value={c.id}>{c.name}</option>
                                                            ))}
                                                        </select>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-1.5">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleOpenPdfViewer(arch)}
                                                        className="px-2 py-1 bg-blue-100 text-blue-800 rounded font-semibold text-[11px] hover:bg-blue-200"
                                                    >
                                                        Ver
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDeleteExistingArchivo(arch.id)}
                                                        className="px-2 py-1 bg-red-100 text-red-700 rounded font-semibold text-[11px] hover:bg-red-200"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            {/* Agregar Nuevos Archivos PDF */}
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">
                                    Agregar Nuevos Documentos PDF (Opcional):
                                </label>
                                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 items-center">
                                    <div
                                        onClick={() => fileInputRef.current?.click()}
                                        className="border-2 border-dashed border-slate-300 hover:border-blue-700 rounded-xl p-4 text-center cursor-pointer transition-all bg-slate-50/50 hover:bg-blue-50/40 min-h-[120px] flex flex-col items-center justify-center"
                                    >
                                        <div className="text-2xl mb-1">📎</div>
                                        <p className="text-xs font-bold text-slate-800">Seleccionar más PDFs</p>
                                        <p className="text-[11px] text-slate-400">Clic aquí para añadir archivos</p>
                                        <input
                                            ref={fileInputRef}
                                            type="file"
                                            accept=".pdf,application/pdf"
                                            multiple
                                            onChange={(e) => handleFilesSelect(e.target.files)}
                                            className="hidden"
                                        />
                                    </div>

                                    {newPdfs.length > 0 && (
                                        <div className="space-y-1.5 max-h-32 overflow-y-auto">
                                            {newPdfs.map((f, i) => (
                                                <div key={i} className="flex items-center justify-between p-2 bg-blue-50/80 border border-blue-200 rounded-lg text-xs">
                                                    <span className="truncate max-w-[180px] font-medium text-blue-950">{f.name}</span>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleRemoveNewPdf(i)}
                                                        className="text-red-500 font-bold"
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Botones de Acción */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={() => setEditModalOpen(false)}
                                    disabled={submitting}
                                    className="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors"
                                >
                                    Cancelar
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
                                            <span>Actualizando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                                <polyline points="17,21 17,13 7,13 7,21"/>
                                                <polyline points="7,3 7,8 15,8"/>
                                            </svg>
                                            <span>Actualizar Documento</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* SUB-MODAL RÁPIDO PARA CREAR NUEVA CATEGORÍA (Z-INDEX SUPERIOR Z-[70]) */}
            {quickCatModalOpen && (
                <div className="fixed inset-0 z-[70] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto">
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 transition-all animate-in fade-in zoom-in-95 duration-200">
                        {/* Cabecera del sub-modal */}
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                            <div>
                                <h4 className="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span>📁</span>
                                    <span>Nueva Categoría</span>
                                </h4>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    Crea una categoría y se seleccionará automáticamente
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setQuickCatModalOpen(false)}
                                disabled={quickCatSubmitting}
                                className="text-slate-400 hover:text-slate-600 rounded-lg p-1"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {/* Formulario de nueva categoría */}
                        <form onSubmit={handleQuickCategorySubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nombre de la Categoría:
                                </label>
                                <input
                                    type="text"
                                    value={quickCatName}
                                    onChange={(e) => setQuickCatName(e.target.value)}
                                    placeholder="Ej. Facturas, Resoluciones, Memorándums..."
                                    className="w-full bg-blue-100 border-none rounded-xl px-4 py-2 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600"
                                    autoFocus
                                    required
                                    disabled={quickCatSubmitting}
                                />
                                {quickCatError && (
                                    <p className="mt-1 text-xs text-red-500 font-semibold">{quickCatError}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setQuickCatModalOpen(false)}
                                    disabled={quickCatSubmitting}
                                    className="px-3.5 py-1.5 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={quickCatSubmitting}
                                    className="btn-save inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold text-white rounded-lg shadow-sm transition-all disabled:opacity-50"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%)'
                                    }}
                                >
                                    {quickCatSubmitting ? (
                                        <>
                                            <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <span>➕</span>
                                            <span>Crear y Seleccionar</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* MODAL PARA VISUALIZAR PDF CON CONTROLES ZOOM (DISEÑO EXACTO) */}
            {pdfViewerOpen && viewingPdf && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 overflow-hidden">
                    <div className="relative w-full max-w-6xl overflow-hidden bg-white shadow-2xl rounded-2xl flex flex-col" style={{ maxHeight: '92vh' }}>
                        {/* Header del Modal */}
                        <div className="flex items-center justify-between px-5 py-3.5 bg-blue-900 text-white">
                            <h3 className="text-base font-bold truncate max-w-lg" title={viewingPdf.nombre_archivo}>
                                📄 {viewingPdf.nombre_archivo}
                            </h3>

                            <div className="flex items-center gap-3">
                                {/* Controles de zoom */}
                                <div className="pdf-zoom-controls flex items-center gap-1.5 bg-white/10 rounded-lg p-1">
                                    <button
                                        type="button"
                                        onClick={() => setPdfZoom(prev => Math.max(prev - 25, 50))}
                                        className="w-7 h-7 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition-all"
                                        title="Disminuir zoom"
                                    >
                                        −
                                    </button>
                                    <span className="text-xs font-mono min-w-[45px] text-center">{pdfZoom}%</span>
                                    <button
                                        type="button"
                                        onClick={() => setPdfZoom(prev => Math.min(prev + 25, 200))}
                                        className="w-7 h-7 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center transition-all"
                                        title="Ampliar zoom"
                                    >
                                        +
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setPdfZoom(100)}
                                        className="w-7 h-7 rounded bg-white/20 hover:bg-white/30 text-white font-bold flex items-center justify-center text-xs transition-all"
                                        title="Restablecer"
                                    >
                                        ⟳
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setPdfViewerOpen(false)}
                                    className="text-white hover:text-gray-300 p-1"
                                    title="Cerrar"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {/* Contenedor del PDF */}
                        <div className="p-4 bg-slate-100 flex-1 overflow-auto flex justify-center items-start min-h-[500px]">
                            <iframe
                                src={`/archivos/${viewingPdf.ruta}`}
                                className="w-full border border-slate-300 rounded-lg shadow-inner bg-white"
                                style={{
                                    height: '74vh',
                                    transform: `scale(${pdfZoom / 100})`,
                                    transformOrigin: 'top center',
                                    transition: 'transform 0.2s ease'
                                }}
                            />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
