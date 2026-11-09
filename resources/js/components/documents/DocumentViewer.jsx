import React, { useState, useRef, useEffect } from 'react';

// Componente: Botón de Zoom
function ZoomControl({ zoom, onZoomIn, onZoomOut, onReset }) {
    return (
        <div className="flex items-center gap-2 px-4 py-2 bg-blue-900/80 backdrop-blur-sm rounded-xl">
            <button
                onClick={onZoomOut}
                className="flex items-center justify-center w-8 h-8 text-lg font-bold text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                title="Disminuir zoom"
            >
                −
            </button>
            <span className="text-white text-sm font-medium min-w-[60px] text-center">{zoom}%</span>
            <button
                onClick={onZoomIn}
                className="flex items-center justify-center w-8 h-8 text-lg font-bold text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                title="Aumentar zoom"
            >
                +
            </button>
            <button
                onClick={onReset}
                className="flex items-center justify-center w-8 h-8 text-xs text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                title="Restablecer zoom"
            >
                ⟳
            </button>
        </div>
    );
}

// Componente: Miniaturas de páginas
function PageThumbnails({ totalPages, currentPage, onPageSelect }) {
    const thumbnails = Array.from({ length: Math.min(totalPages, 10) }, (_, i) => i + 1);

    return (
        <div className="flex gap-2 pb-2 overflow-x-auto">
            {thumbnails.map((page) => (
                <button
                    key={page}
                    onClick={() => onPageSelect(page)}
                    className={`flex-shrink-0 w-12 h-16 rounded-lg border-2 transition-all ${
                        currentPage === page
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-200 hover:border-gray-300 bg-white'
                    }`}
                >
                    <div className="flex items-center justify-center h-full text-xs text-gray-600">
                        {page}
                    </div>
                </button>
            ))}
            {totalPages > 10 && (
                <div className="flex items-center justify-center flex-shrink-0 w-12 h-16 border-2 border-gray-300 border-dashed rounded-lg">
                    <span className="text-xs text-gray-500">+{totalPages - 10}</span>
                </div>
            )}
        </div>
    );
}

// Componente Principal: Visualizador de Documentos PDF
export default function DocumentViewer({
    pdfUrl = '',
    documentName = 'Documento',
    initialZoom = 100,
    maxZoom = 300,
    minZoom = 50,
    showThumbnails = true,
    showAnnotations = false,
    onClose = null
}) {
    const [zoom, setZoom] = useState(initialZoom);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isFullscreen, setIsFullscreen] = useState(false);
    const [rotation, setRotation] = useState(0);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const viewerRef = useRef(null);

    // Simular carga del PDF (en producción usar react-pdf)
    useEffect(() => {
        if (pdfUrl) {
            loadPdf();
        }
    }, [pdfUrl]);

    const loadPdf = async () => {
        setLoading(true);
        setError(null);

        try {
            // Simular tiempo de carga
            await new Promise(resolve => setTimeout(resolve, 500));

            // En producción, usar react-pdf:
            // const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
            // setTotalPages(pdf.numPages);

            // Por ahora simulamos que el PDF tiene 5 páginas
            setTotalPages(5);
        } catch (err) {
            setError('Error al cargar el documento. Por favor, inténtelo de nuevo.');
            console.error('PDF load error:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleZoomIn = () => {
        setZoom(prev => Math.min(prev + 25, maxZoom));
    };

    const handleZoomOut = () => {
        setZoom(prev => Math.max(prev - 25, minZoom));
    };

    const handleZoomReset = () => {
        setZoom(initialZoom);
    };

    const handleRotate = () => {
        setRotation(prev => (prev + 90) % 360);
    };

    const handleFullscreen = () => {
        if (!document.fullscreenElement) {
            viewerRef.current?.requestFullscreen();
            setIsFullscreen(true);
        } else {
            document.exitFullscreen();
            setIsFullscreen(false);
        }
    };

    const handleDownload = () => {
        if (typeof window !== 'undefined' && window.downloadWithProgress) {
            window.downloadWithProgress(pdfUrl, { filename: documentName, type: 'pdf' });
        } else {
            const link = document.createElement('a');
            link.href = pdfUrl;
            link.download = documentName;
            link.click();
        }
    };

    const handlePrint = () => {
        const printWindow = window.open(pdfUrl, '_blank');
        printWindow?.print();
    };

    const handleSearch = (e) => {
        const query = e.target.value;
        setSearchQuery(query);

        // Simular búsqueda (en producción usar pdf.js text layer)
        if (query.length > 2) {
            setSearchResults([1, 3]); // Páginas donde se encontró el término
        } else {
            setSearchResults([]);
        }
    };

    if (!pdfUrl) {
        return (
            <div className="p-8 text-center bg-gray-100 rounded-2xl">
                <div className="mb-4 text-6xl">📄</div>
                <p className="text-gray-500">No hay documento para visualizar</p>
            </div>
        );
    }

    return (
        <div
            ref={viewerRef}
            className={`bg-gray-900 rounded-2xl overflow-hidden ${
                isFullscreen ? 'fixed inset-0 z-50 rounded-none' : ''
            }`}
        >
            {/* Header del visualizador */}
            <div className="flex items-center justify-between px-4 py-3 bg-blue-900">
                <div className="flex items-center gap-4">
                    <h3 className="max-w-md font-semibold text-white truncate">
                        📄 {documentName}
                    </h3>
                    <span className="text-sm text-blue-200">
                        Página {currentPage} de {totalPages}
                    </span>
                </div>

                <div className="flex items-center gap-3">
                    {/* Búsqueda */}
                    <div className="relative">
                        <input
                            type="text"
                            placeholder="Buscar en documento..."
                            value={searchQuery}
                            onChange={handleSearch}
                            className="w-64 px-3 py-1.5 pl-9 text-sm bg-white/20 text-white placeholder-blue-200 rounded-lg border border-white/30 focus:outline-none focus:border-white"
                        />
                        <svg className="absolute w-4 h-4 text-blue-200 -translate-y-1/2 left-3 top-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {/* Controles de zoom */}
                    <ZoomControl
                        zoom={zoom}
                        onZoomIn={handleZoomIn}
                        onZoomOut={handleZoomOut}
                        onReset={handleZoomReset}
                    />

                    {/* Acciones */}
                    <div className="flex items-center gap-2">
                        <button
                            onClick={handleRotate}
                            className="p-2 text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                            title="Rotar"
                        >
                            🔄
                        </button>
                        <button
                            onClick={handleDownload}
                            className="p-2 text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                            title="Descargar"
                        >
                            ⬇️
                        </button>
                        <button
                            onClick={handlePrint}
                            className="p-2 text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                            title="Imprimir"
                        >
                            🖨️
                        </button>
                        <button
                            onClick={handleFullscreen}
                            className="p-2 text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                            title={isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa'}
                        >
                            {isFullscreen ? '⛶' : '⛶'}
                        </button>
                        {onClose && (
                            <button
                                onClick={onClose}
                                className="p-2 text-white transition-colors rounded-lg bg-white/20 hover:bg-white/30"
                                title="Cerrar"
                            >
                                ✕
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Contenido del visualizador */}
            <div className="flex">
                {/* Panel de miniaturas */}
                {showThumbnails && (
                    <div className="w-24 bg-gray-800 p-2 overflow-y-auto max-h-[600px]">
                        <PageThumbnails
                            totalPages={totalPages}
                            currentPage={currentPage}
                            onPageSelect={setCurrentPage}
                        />
                    </div>
                )}

                {/* Área del documento */}
                <div className="flex-1 bg-gray-700 p-8 overflow-auto max-h-[600px]">
                    {loading ? (
                        <div className="flex items-center justify-center h-64">
                            <div className="text-center">
                                <div className="w-12 h-12 mx-auto mb-4 border-b-2 border-white rounded-full animate-spin"></div>
                                <p className="text-white">Cargando documento...</p>
                            </div>
                        </div>
                    ) : error ? (
                        <div className="flex items-center justify-center h-64">
                            <div className="text-center">
                                <div className="mb-4 text-6xl">⚠️</div>
                                <p className="mb-4 text-white">{error}</p>
                                <button
                                    onClick={loadPdf}
                                    className="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                >
                                    Reintentar
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div
                            className="flex justify-center transition-transform duration-200"
                            style={{
                                transform: `scale(${zoom / 100}) rotate(${rotation}deg)`,
                                transformOrigin: 'center top'
                            }}
                        >
                            <iframe
                                src={`${pdfUrl}#toolbar=0&navpanes=0&scrollbar=1&page=${currentPage}`}
                                className="w-full max-w-3xl bg-white rounded-lg shadow-2xl"
                                style={{ height: '700px' }}
                                title={documentName}
                            />
                        </div>
                    )}
                </div>
            </div>

            {/* Barra de navegación de páginas */}
            <div className="flex items-center justify-between px-4 py-3 bg-gray-800">
                <button
                    onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                    disabled={currentPage === 1}
                    className="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    ← Anterior
                </button>

                <div className="flex items-center gap-2">
                    <input
                        type="number"
                        value={currentPage}
                        onChange={(e) => {
                            const page = parseInt(e.target.value);
                            if (page >= 1 && page <= totalPages) {
                                setCurrentPage(page);
                            }
                        }}
                        className="w-16 px-2 py-1 text-center text-white border rounded-lg bg-white/20 border-white/30"
                    />
                    <span className="text-white">de {totalPages}</span>
                </div>

                <button
                    onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                    disabled={currentPage === totalPages}
                    className="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Siguiente →
                </button>
            </div>
        </div>
    );
}

// Componente helper para integrar en Blade
export function createDocumentViewerElement(elementId, props = {}) {
    const element = document.getElementById(elementId);
    if (element && window.ReactComponents) {
        const root = window.ReactDOM.createRoot(element);
        root.render(React.createElement(DocumentViewer, props));
    }
}

