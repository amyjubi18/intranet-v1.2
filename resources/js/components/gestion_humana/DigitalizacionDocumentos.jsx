import React, { useState, useEffect, useCallback } from 'react';
import Swal from 'sweetalert2';

export default function DigitalizacionDocumentos() {
    const [digitalizaciones, setDigitalizaciones] = useState([]);
    const [areasAdscritas, setAreasAdscritas] = useState([]);
    const [tiposDocumentos, setTiposDocumentos] = useState([]);
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [permissions, setPermissions] = useState({
        can_create: false,
        can_edit: false,
        can_destroy: false
    });

    // Filtros originales
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [filterType, setFilterType] = useState('');
    const [secondFilter, setSecondFilter] = useState('');
    const [busqueda, setBusqueda] = useState('');

    // Modal Visor de PDF interactivo
    const [pdfModalOpen, setPdfModalOpen] = useState(false);
    const [currentPdfUrl, setCurrentPdfUrl] = useState('');
    const [currentPdfTitle, setCurrentPdfTitle] = useState('');
    const [zoomLevel, setZoomLevel] = useState(100);

    const fetchDigitalizaciones = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (busqueda.trim()) {
                params.append('search_general', busqueda.trim());
            }
            if (filterType && secondFilter) {
                params.append('filterType', filterType);
                params.append('secondFilter', secondFilter);
            }

            const response = await fetch(`/api/gestion-humana/digitalizacion_documentos?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '/login?timeout=1';
                    return;
                }
                throw new Error(`Error en el servidor (${response.status})`);
            }

            const json = await response.json();
            const resData = json.data || {};
            const paginated = resData.digitalizaciones || {};

            setDigitalizaciones(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 15
            });

            if (resData.areas_adscritas) setAreasAdscritas(resData.areas_adscritas);
            if (resData.tipo_documentos) setTiposDocumentos(resData.tipo_documentos);
            if (resData.permissions) setPermissions(resData.permissions);
        } catch (err) {
            console.error('Error fetching digitalizaciones:', err);
            setError(err.message || 'No se pudieron cargar los expedientes.');
        } finally {
            setLoading(false);
        }
    }, [filterType, secondFilter, busqueda]);

    useEffect(() => {
        fetchDigitalizaciones(1);
    }, [fetchDigitalizaciones]);

    const handleClearFilters = () => {
        setFilterType('');
        setSecondFilter('');
        setBusqueda('');
    };

    const handleDelete = async (dato) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el expediente de ${dato.nombre_apellido} (C.I. ${dato.cedula})`,
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
            const res = await fetch(`/api/gestion-humana/digitalizacion_documentos/${dato.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('Error al eliminar expediente.');

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'El expediente fue eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchDigitalizaciones(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el expediente.'
            });
        }
    };

    const openPdfViewer = (ruta, title) => {
        const fullUrl = ruta.startsWith('http') ? ruta : `/documentos_ogh/${ruta}`;
        setCurrentPdfUrl(fullUrl);
        setCurrentPdfTitle(title);
        setZoomLevel(100);
        setPdfModalOpen(true);
    };

    const zoomIn = () => setZoomLevel(prev => Math.min(200, prev + 25));
    const zoomOut = () => setZoomLevel(prev => Math.max(50, prev - 25));
    const resetZoom = () => setZoomLevel(100);

    return (
        <div className="w-full">
            {/* Barra de Filtros y Controles Original */}
            <div className="px-6 mb-6">
                <div className="bg-white/80 border border-slate-200 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                className="px-4 py-2 text-sm text-white bg-blue-900 border border-blue-600 hover:bg-blue-800 rounded-xl font-semibold shadow-sm transition-all"
                            >
                                Filtros Avanzados
                            </button>

                            {showAdvancedFilters && (
                                <select
                                    value={filterType}
                                    onChange={(e) => {
                                        setFilterType(e.target.value);
                                        setSecondFilter('');
                                    }}
                                    className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl text-blue-900"
                                >
                                    <option value="">Seleccionar filtro</option>
                                    <option value="cedula">Cédula</option>
                                    <option value="nombre_apellido">Nombres y Apellidos</option>
                                    <option value="tipo_usuario">Tipo de Usuario</option>
                                    <option value="area_adscrita_id">Área Adscrita</option>
                                </select>
                            )}

                            {filterType === 'area_adscrita_id' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl text-blue-900"
                                >
                                    <option value="">Seleccionar Área</option>
                                    {(areasAdscritas || []).map(a => (
                                        <option key={a.id} value={a.id}>{a.area_adscrita}</option>
                                    ))}
                                </select>
                            )}

                            {filterType === 'tipo_usuario' && (
                                <select
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl text-blue-900"
                                >
                                    <option value="">Seleccionar Tipo</option>
                                    <option value="Empleado">Empleado</option>
                                    <option value="Obrero">Obrero</option>
                                    <option value="Contratado">Contratado</option>
                                    <option value="Jubilado">Jubilado</option>
                                </select>
                            )}

                            {['cedula', 'nombre_apellido'].includes(filterType) && (
                                <input
                                    type="text"
                                    value={secondFilter}
                                    onChange={(e) => setSecondFilter(e.target.value)}
                                    placeholder="Ingrese valor..."
                                    className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl text-blue-900"
                                />
                            )}

                            {/* Búsqueda General */}
                            <input
                                type="text"
                                value={busqueda}
                                onChange={(e) => setBusqueda(e.target.value)}
                                placeholder="Buscar cédula o nombre..."
                                className="px-4 py-2 text-sm bg-white border border-slate-200 rounded-xl text-blue-900 w-48 sm:w-64"
                            />

                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all"
                            >
                                <span>🗑️</span>
                                <span>Limpiar</span>
                            </button>
                        </div>

                        {/* Botón Subir Documento */}
                        {permissions.can_create && (
                            <a
                                href="/gestion-humana/digitalizacion_documentos/create"
                                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl shadow-sm transition-all"
                            >
                                <span>➕</span>
                                <span>Subir Documento</span>
                            </a>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Documentos en Tarjetas Glass Originales */}
            <div className="px-6 mb-8">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando documentos digitalizados...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchDigitalizaciones(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : digitalizaciones.length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">📂</div>
                        <h3 className="text-base font-bold text-slate-800">No hay documentos digitalizados</h3>
                        <p className="text-xs text-slate-500 mt-1">No se encontraron expedientes con los filtros aplicados.</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {digitalizaciones.map((dato) => {
                            const docs = Array.isArray(dato.documentos) ? dato.documentos : [];

                            return (
                                <div
                                    key={dato.id}
                                    className="bg-white/90 border border-slate-200/80 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-sm"
                                >
                                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                                        {/* Columna 1: Cédula y Nombre */}
                                        <div className="text-center lg:text-left">
                                            <p className="mb-1 text-xs font-semibold text-blue-900">👤 Cédula</p>
                                            <p className="font-mono text-lg font-bold text-slate-900">{dato.cedula}</p>
                                            <p className="mt-2 mb-1 text-xs font-semibold text-blue-900">📝 Nombre</p>
                                            <p className="max-w-xs text-sm font-semibold text-slate-800 truncate" title={dato.nombre_apellido}>
                                                {dato.nombre_apellido}
                                            </p>
                                        </div>

                                        {/* Columna 2: Tipo de Usuario y Área */}
                                        <div className="text-center lg:text-left">
                                            <p className="mb-1 text-xs font-semibold text-blue-900">🚻 Tipo de Usuario</p>
                                            <span className="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">
                                                {dato.salud || dato.tipo_usuario || 'Personal'}
                                            </span>
                                            <p className="mt-2 mb-1 text-xs font-semibold text-blue-900">📂 Área Adscrita</p>
                                            <span className="text-sm font-medium text-slate-700">
                                                {dato.area_adscrita?.area_adscrita || 'Sin área asignada'}
                                            </span>
                                        </div>

                                        {/* Columna 3: Miniaturas PDF y Opciones */}
                                        <div className="text-center lg:text-right">
                                            <p className="mb-1 text-xs font-semibold text-blue-900">
                                                📄 Documentos ({docs.length})
                                            </p>

                                            <div className="flex flex-wrap justify-center lg:justify-end gap-2 mb-3">
                                                {docs.length > 0 ? (
                                                    docs.map((doc, idx) => (
                                                        <div
                                                            key={doc.id || idx}
                                                            onClick={() => openPdfViewer(doc.ruta, `${doc.nombre_archivo || 'Documento'} - ${dato.nombre_apellido}`)}
                                                            className="relative flex flex-col items-center justify-center w-20 p-2 border-2 border-red-200 rounded-lg cursor-pointer bg-red-50 hover:border-red-400 hover:scale-105 transition-all shadow-sm"
                                                            title={doc.nombre_archivo || doc.ruta}
                                                        >
                                                            <svg className="w-6 h-6 text-red-600" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                                                            </svg>
                                                            <span className="absolute flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-blue-600 rounded-full -top-2 -right-2">
                                                                {idx + 1}
                                                            </span>
                                                            <span className="mt-1 text-[8px] font-semibold text-red-700 text-center leading-tight line-clamp-2 w-full">
                                                                {doc.tipo_documento?.tipo_documento || 'PDF'}
                                                            </span>
                                                        </div>
                                                    ))
                                                ) : (
                                                    <div className="flex items-center justify-center w-12 h-14 bg-gray-100 border-2 border-gray-200 rounded-lg">
                                                        <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                            <polyline points="14 2 14 8 20 8"/>
                                                        </svg>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Opciones */}
                                            <p className="mb-1 text-xs font-semibold text-blue-900">⚙️ Opciones</p>
                                            <div className="flex flex-wrap justify-center lg:justify-end gap-2">
                                                {permissions.can_edit && (
                                                    <a
                                                        href={`/gestion-humana/digitalizacion_documentos/${dato.id}/edit`}
                                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-semibold bg-green-600 text-white hover:bg-green-700 text-xs transition-colors"
                                                    >
                                                        <span>✏️</span>
                                                        <span>Editar</span>
                                                    </a>
                                                )}

                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(dato)}
                                                        className="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-semibold bg-red-600 text-white hover:bg-red-700 text-xs transition-colors"
                                                    >
                                                        <span>🗑️</span>
                                                        <span>Eliminar</span>
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2 mt-8">
                        <button
                            type="button"
                            onClick={() => fetchDigitalizaciones(pagination.current_page - 1)}
                            disabled={pagination.current_page <= 1}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Anterior
                        </button>
                        <span className="text-xs font-bold text-blue-900 px-3">
                            Página {pagination.current_page} de {pagination.last_page} ({pagination.total} expedientes)
                        </span>
                        <button
                            type="button"
                            onClick={() => fetchDigitalizaciones(pagination.current_page + 1)}
                            disabled={pagination.current_page >= pagination.last_page}
                            className="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                        >
                            Siguiente
                        </button>
                    </div>
                )}
            </div>

            {/* MODAL VISOR DE PDF INTERACTIVO ORIGINAL (REACT) */}
            {pdfModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                    <div className="relative w-full max-w-5xl overflow-hidden bg-white shadow-2xl rounded-2xl max-h-[92vh] flex flex-col">
                        {/* Cabecera del Visor */}
                        <div className="flex items-center justify-between px-5 py-3.5 bg-blue-900 text-white">
                            <h3 className="text-sm font-bold truncate max-w-lg">{currentPdfTitle || 'Vista Previa del Documento'}</h3>
                            <div className="flex items-center gap-3">
                                {/* Controles de Zoom */}
                                <div className="flex items-center gap-1.5 bg-white/10 px-2 py-1 rounded-xl">
                                    <button
                                        type="button"
                                        onClick={zoomOut}
                                        className="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold text-sm flex items-center justify-center"
                                        title="Disminuir"
                                    >
                                        −
                                    </button>
                                    <span className="text-xs font-semibold px-2 min-w-[45px] text-center">{zoomLevel}%</span>
                                    <button
                                        type="button"
                                        onClick={zoomIn}
                                        className="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold text-sm flex items-center justify-center"
                                        title="Ampliar"
                                    >
                                        +
                                    </button>
                                    <button
                                        type="button"
                                        onClick={resetZoom}
                                        className="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold text-xs flex items-center justify-center"
                                        title="Restablecer"
                                    >
                                        ⟳
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    onClick={() => setPdfModalOpen(false)}
                                    className="text-white hover:text-slate-300 font-bold text-lg p-1"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>

                        {/* Iframe del PDF con zoom */}
                        <div className="p-4 bg-slate-100 flex-1 overflow-auto flex items-center justify-center min-h-[60vh]">
                            <iframe
                                src={currentPdfUrl}
                                title={currentPdfTitle}
                                className="w-full bg-white shadow-md rounded-xl border border-slate-300"
                                style={{
                                    height: '75vh',
                                    transform: `scale(${zoomLevel / 100})`,
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
