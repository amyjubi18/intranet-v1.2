import React, { useState, useEffect, useMemo, useRef } from 'react';

/**
 * Selector interactivo con autocompletado y diseño Tailwind institucional
 */
export default function SearchableSelect({
    label,
    options = [],
    value,
    onChange,
    placeholder = 'Seleccionar...',
    allLabel = 'Todos',
    icon = '📋',
    disabled = false,
    error = null,
    required = false,
    className = ''
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [search, setSearch] = useState('');
    const containerRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        if (containerRef.current && containerRef.current.parentElement) {
            if (isOpen) {
                containerRef.current.parentElement.style.zIndex = '99';
                containerRef.current.parentElement.style.position = 'relative';
            } else {
                containerRef.current.parentElement.style.zIndex = '';
            }
        }
    }, [isOpen]);

    const filtered = useMemo(() => {
        if (!search.trim()) return options;
        const q = search.toLowerCase();
        return options.filter(opt => {
            const labelStr = (opt.label || opt.name || opt.text || String(opt.id || '')).toLowerCase();
            return labelStr.includes(q);
        });
    }, [options, search]);

    const selectedOption = options.find(opt => String(opt.id) === String(value));

    return (
        <div className={`relative w-full ${isOpen ? 'z-[99]' : 'z-20'} ${className}`} ref={containerRef}>
            {label && (
                <label className="block text-xs font-semibold text-blue-900 mb-1">
                    {label} {required && <span className="text-red-500">*</span>}
                </label>
            )}

            <button
                type="button"
                disabled={disabled}
                onClick={() => !disabled && setIsOpen(!isOpen)}
                className={`w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-xs font-semibold rounded-xl border transition-all text-left ${
                    disabled
                        ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed'
                        : error
                            ? 'border-red-400 bg-red-50 text-slate-800'
                            : 'bg-blue-100/70 hover:bg-white text-slate-800 border-white/20 focus:ring-2 focus:ring-blue-600'
                }`}
            >
                <div className="flex items-center gap-2 truncate">
                    <span className="text-sm flex-shrink-0">{icon}</span>
                    <span className="truncate">
                        {selectedOption ? selectedOption.label : <span className="text-slate-400">{placeholder}</span>}
                    </span>
                </div>
                <div className="flex items-center gap-1 flex-shrink-0">
                    {selectedOption?.badge && (
                        <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold ${selectedOption.badgeClass || 'bg-blue-100 text-blue-900'}`}>
                            {selectedOption.badge}
                        </span>
                    )}
                    <span className={`text-slate-400 text-xs transition-transform ${isOpen ? 'rotate-180' : ''}`}>▼</span>
                </div>
            </button>

            {error && (
                <p className="text-[11px] text-red-500 mt-1 font-medium">{error}</p>
            )}

            {isOpen && (
                <div className="select-spring-menu absolute z-[100] left-0 mt-1 w-full min-w-[240px] bg-white border border-slate-200 rounded-2xl shadow-2xl p-2.5 ring-1 ring-black/5">
                    <div className="relative mb-2">
                        <input
                            type="text"
                            autoFocus
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Buscar en la lista..."
                            className="w-full pl-7 pr-3 py-1.5 text-xs bg-slate-50 rounded-xl border border-slate-200 focus:border-blue-500 focus:bg-white focus:outline-none transition-all"
                        />
                        <span className="absolute left-2.5 top-1.5 text-xs text-slate-400">🔍</span>
                    </div>

                    <div className="max-h-48 overflow-y-auto space-y-1 text-xs pr-0.5">
                        {allLabel && (
                            <div
                                onClick={() => {
                                    onChange('');
                                    setIsOpen(false);
                                    setSearch('');
                                }}
                                className={`select-spring-item px-3 py-2 rounded-xl cursor-pointer font-medium transition-all ${
                                    !value ? 'bg-blue-900 text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-blue-50'
                                }`}
                            >
                                {allLabel}
                            </div>
                        )}

                        {filtered.length === 0 ? (
                            <div className="px-3 py-2 text-center text-xs text-slate-400 font-medium">
                                Sin coincidencias
                            </div>
                        ) : (
                            filtered.map((opt, idx) => (
                                <div
                                    key={opt.id ?? idx}
                                    onClick={() => {
                                        onChange(opt.id);
                                        setIsOpen(false);
                                        setSearch('');
                                    }}
                                    className={`select-spring-item px-3 py-2 rounded-xl cursor-pointer font-medium transition-all flex items-center justify-between gap-2 ${
                                        String(value) === String(opt.id)
                                            ? 'bg-blue-900 text-white font-bold shadow-xs'
                                            : 'text-slate-700 hover:bg-blue-50'
                                    }`}
                                >
                                    <span className="truncate">{opt.label}</span>
                                    {opt.badge && (
                                        <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold flex-shrink-0 ${
                                            String(value) === String(opt.id)
                                                ? 'bg-blue-800 text-white'
                                                : opt.badgeClass || 'bg-slate-100 text-slate-600'
                                        }`}>
                                            {opt.badge}
                                        </span>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
