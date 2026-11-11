import React, { useState, useCallback } from 'react';

// Componente: Tarjeta de Estantería Individual
function EstanteriaCard({
    estanteria,
    isSelected,
    documentCount,
    onSelect,
    colors
}) {
    const [isHovered, setIsHovered] = useState(false);

    const handleClick = () => {
        onSelect(estanteria.id);
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            onSelect(estanteria.id);
        }
    };

    return (
        <div
            className={`estanteria-block relative cursor-pointer outline-none transition-all duration-300 transform ${isSelected ? 'scale-105' : 'hover:scale-110'}`}
            style={{ '--bg-estanteria': estanteria.color || colors.primary }}
            onClick={handleClick}
            onKeyDown={handleKeyDown}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
            tabIndex={0}
            role="button"
            aria-label={`Seleccionar estantería ${estanteria.nombre}`}
            aria-pressed={isSelected}
        >
            <div
                className={`estanteria-block__item rounded-2xl p-4 transition-all duration-300 ${isSelected ? 'ring-4 ring-blue-400 shadow-2xl' : ''} ${isHovered && !isSelected ? 'shadow-xl -translate-y-2' : ''}`}
                style={{
                    backgroundColor: estanteria.color || colors.primary,
                    boxShadow: isHovered
                        ? '0 20px 40px -10px rgba(0, 0, 0, 0.3)'
                        : '0 4px 15px rgba(0, 0, 0, 0.1)'
                }}
            >
                <div className="flex justify-center mb-3">
                    {estanteria.imagen ? (
                        <img
                            src={estanteria.imagenUrl || `/storage/estanterias/${estanteria.imagen}`}
                            alt={estanteria.nombre}
                            className="object-contain w-20 h-20 rounded-lg"
                            onError={(e) => {
                                e.target.style.display = 'none';
                                e.target.nextSibling.style.display = 'flex';
                            }}
                        />
                    ) : null}
                    <div className={`w-20 h-20 bg-white/20 rounded-lg flex items-center justify-center text-5xl ${estanteria.imagen ? 'hidden' : ''}`}>
                        🗄️
                    </div>
                </div>

                <h4 className="mb-1 text-lg font-semibold text-center text-white truncate">
                    {estanteria.nombre}
                </h4>

                <div className="flex items-center justify-center gap-1 text-white/80">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span className="text-sm font-medium">
                        {documentCount} {documentCount === 1 ? 'doc' : 'docs'}
                    </span>
                </div>

                {isSelected && (
                    <div className="absolute flex items-center justify-center w-8 h-8 bg-green-500 rounded-full shadow-lg -top-2 -right-2 animate-bounce">
                        <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                )}

                {isHovered && !isSelected && (
                    <div className="absolute inset-0 flex items-center justify-center rounded-2xl bg-black/20">
                        <span className="text-lg font-semibold text-white transition-transform transform scale-110">
                            Ver documentos
                        </span>
                    </div>
                )}
            </div>
        </div>
    );
}

// Componente: Modal de Detalles de Estantería
function EstanteriaDetailModal({ estanteria, onClose, onViewDocuments }) {
    if (!estanteria) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-modal-backdrop" onClick={onClose}>
            <div className="w-full max-w-lg overflow-hidden bg-white shadow-2xl rounded-2xl animate-modal-expand" onClick={(e) => e.stopPropagation()}>
                <div className="p-6 text-white" style={{ backgroundColor: estanteria.color || '#1e3a8a' }}>
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex items-center justify-center w-16 h-16 text-4xl bg-white/20 rounded-xl">
                                🗄️
                            </div>
                            <div>
                                <h3 className="text-2xl font-bold">{estanteria.nombre}</h3>
                                <p className="text-white/80">Codigo: {estanteria.codigo || 'N/A'}</p>
                            </div>
                        </div>
                        <button onClick={onClose} className="flex items-center justify-center w-10 h-10 transition-colors rounded-full bg-white/20 hover:bg-white/30">
                            X
                        </button>
                    </div>
                </div>

                <div className="p-6 space-y-4">
                    {estanteria.descripcion && (
                        <div>
                            <h4 className="mb-1 text-sm font-semibold text-gray-500">Descripcion</h4>
                            <p className="text-gray-700">{estanteria.descripcion}</p>
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4">
                        <div className="p-4 bg-gray-50 rounded-xl">
                            <p className="text-sm text-gray-500">Documentos</p>
                            <p className="text-2xl font-bold text-gray-800">{estanteria.documentCount || 0}</p>
                        </div>
                        <div className="p-4 bg-gray-50 rounded-xl">
                            <p className="text-sm text-gray-500">Capacidad</p>
                            <p className="text-2xl font-bold text-gray-800">{estanteria.capacidad || 'Infinita'}</p>
                        </div>
                    </div>

                    {estanteria.ubicacion && (
                        <div className="flex items-center gap-3 p-4 bg-blue-50 rounded-xl">
                            <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <p className="text-sm text-gray-500">Ubicacion</p>
                                <p className="font-medium text-gray-800">{estanteria.ubicacion}</p>
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex gap-3 px-6 pb-6">
                    <button onClick={onClose} className="flex-1 px-4 py-3 font-semibold text-gray-600 transition-colors border-2 border-gray-200 rounded-xl hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button onClick={() => { onViewDocuments(estanteria.id); onClose(); }} className="flex-1 px-4 py-3 font-semibold text-white transition-colors bg-blue-600 rounded-xl hover:bg-blue-700">
                        Ver Documentos
                    </button>
                </div>
            </div>
        </div>
    );
}

// Componente Principal: Selector de Estanterías
export default function EstanteriaSelector({
    estanterias = [],
    selectedId = null,
    documentCounts = {},
    colors = { primary: '#1e3a8a', secondary: '#22C55E', accent: '#F59E0B', danger: '#ef4444' },
    onSelect,
    onViewAll,
    showDetailsModal = true,
    selectable = true,
    columns = 3
}) {
    const [selectedEstanteria, setSelectedEstanteria] = useState(selectedId);
    const [detailEstanteria, setDetailEstanteria] = useState(null);
    const [viewMode, setViewMode] = useState('grid');

    const handleSelect = useCallback((id) => {
        setSelectedEstanteria(id);
        if (onSelect) {
            onSelect(id);
        }
    }, [onSelect]);

    const handleViewAll = useCallback(() => {
        setSelectedEstanteria(null);
        if (onViewAll) {
            onViewAll();
        }
    }, [onViewAll]);

    const getEstanteriaColor = (index) => {
        const colorKeys = Object.keys(colors);
        return colors[colorKeys[index % colorKeys.length]];
    };

    const demoEstanterias = [
        { id: 1, nombre: 'Documentos Generales', codigo: 'DOC-001', documentCount: 45, color: '#1e3a8a' },
        { id: 2, nombre: 'Contratos', codigo: 'CTR-001', documentCount: 28, color: '#22C55E' },
        { id: 3, nombre: 'Facturas', codigo: 'FAC-001', documentCount: 67, color: '#F59E0B' },
        { id: 4, nombre: 'Legal', codigo: 'LEG-001', documentCount: 12, color: '#8b5cf6' },
        { id: 5, nombre: 'RRHH', codigo: 'RRHH-001', documentCount: 34, color: '#ef4444' },
        { id: 6, nombre: 'Inventario', codigo: 'INV-001', documentCount: 19, color: '#06b6d4' },
    ];

    const displayEstanterias = estanterias.length > 0 ? estanterias : demoEstanterias;

    return (
        <div className="estanteria-selector">
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h3 className="text-xl font-bold text-gray-800">📚 Selecciona una Estanteria</h3>
                    <p className="mt-1 text-sm text-gray-500">
                        {displayEstanterias.length} estanterias - {displayEstanterias.reduce((acc, e) => acc + (e.documentCount || 0), 0)} documentos totales
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    {selectedEstanteria && (
                        <button onClick={handleViewAll} className="flex items-center gap-2 px-4 py-2 font-medium text-gray-700 transition-colors bg-gray-100 rounded-xl hover:bg-gray-200">
                            Ver Todos
                        </button>
                    )}

                    <div className="flex items-center p-1 bg-gray-100 rounded-xl">
                        <button onClick={() => setViewMode('grid')} className={`p-2 rounded-lg transition-colors ${viewMode === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'}`} title="Vista de cuadrícula">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button onClick={() => setViewMode('list')} className={`p-2 rounded-lg transition-colors ${viewMode === 'list' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'}`} title="Vista de lista">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {displayEstanterias.length > 0 ? (
                <div className={viewMode === 'grid' ? `grid gap-4` : 'flex flex-col gap-3'} style={{ gridTemplateColumns: viewMode === 'grid' ? `repeat(${columns}, minmax(0, 1fr))` : undefined }}>
                    {displayEstanterias.map((estanteria, index) => (
                        <div key={estanteria.id}>
                            {viewMode === 'grid' ? (
                                <EstanteriaCard estanteria={{ ...estanteria, color: estanteria.color || getEstanteriaColor(index) }} isSelected={selectedEstanteria === estanteria.id} documentCount={estanteria.documentCount || documentCounts[estanteria.id] || 0} onSelect={handleSelect} colors={colors} />
                            ) : (
                                <div onClick={() => handleSelect(estanteria.id)} className={`flex items-center gap-4 p-4 rounded-xl cursor-pointer transition-all ${selectedEstanteria === estanteria.id ? 'bg-blue-50 border-2 border-blue-500' : 'bg-white border-2 border-transparent hover:border-gray-200'}`}>
                                    <div className="flex items-center justify-center w-12 h-12 text-2xl rounded-xl" style={{ backgroundColor: estanteria.color || getEstanteriaColor(index) }}>🗄️</div>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-gray-800">{estanteria.nombre}</h4>
                                        <p className="text-sm text-gray-500">{estanteria.codigo || 'Sin codigo'}</p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-2xl font-bold text-gray-800">{estanteria.documentCount || documentCounts[estanteria.id] || 0}</p>
                                        <p className="text-xs text-gray-500">documentos</p>
                                    </div>
                                    <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center ${selectedEstanteria === estanteria.id ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300'}`}>
                                        {selectedEstanteria === estanteria.id && (
                                            <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            ) : (
                <div className="py-12 text-center">
                    <div className="mb-4 text-6xl">🗄️</div>
                    <h4 className="mb-2 text-lg font-semibold text-gray-800">No hay estanterias registradas</h4>
                    <p className="mb-4 text-gray-500">Crea tu primera estanteria para comenzar</p>
                    <a href="/administracion/estanterias/create" className="inline-flex items-center gap-2 px-6 py-3 font-semibold text-white transition-colors bg-blue-600 rounded-xl hover:bg-blue-700">
                        <span>+</span>
                        Crear Estanteria
                    </a>
                </div>
            )}

            {detailEstanteria && (
                <EstanteriaDetailModal estanteria={detailEstanteria} onClose={() => setDetailEstanteria(null)} onViewDocuments={handleSelect} />
            )}

            <div className="mt-6 text-sm text-center text-gray-400">
                <span>Presiona <kbd className="px-2 py-1 bg-gray-100 rounded">Esc</kbd> para cerrar modales</span>
            </div>
        </div>
    );
}

