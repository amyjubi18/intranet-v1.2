import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import Swal from 'sweetalert2';
import { downloadWithProgress } from '../../utils/downloadWithProgress';

/**
 * Selector interactivo tipo spring con autocompletado para filtros y formularios
 */
function SearchableSelect({
    label,
    options = [],
    value,
    onChange,
    placeholder = 'Seleccionar...',
    allLabel = 'Todos',
    icon = '📋',
    disabled = false,
    error = null,
    required = false
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
        <div className={`relative w-full ${isOpen ? 'z-[99]' : 'z-20'}`} ref={containerRef}>
            {label && (
                <label className="block text-xs font-semibold text-blue-900 mb-1">
                    {label} {required && <span className="text-red-500">*</span>}
                </label>
            )}
            <button
                type="button"
                disabled={disabled}
                onClick={() => !disabled && setIsOpen(!isOpen)}
                className={`select-spring-trigger w-full flex items-center justify-between gap-2 px-3 py-2 text-xs font-semibold rounded-xl border transition-all text-left shadow-2xs focus:ring-2 focus:ring-blue-600 focus:outline-none ${
                    disabled
                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-slate-200'
                        : error
                        ? 'bg-red-50 text-red-800 border-red-300'
                        : 'bg-blue-100/70 hover:bg-blue-100 text-slate-800 border-white/20'
                }`}
            >
                <div className="flex items-center gap-1.5 truncate">
                    <span className="text-xs opacity-70">{icon}</span>
                    <span className="truncate">
                        {selectedOption ? selectedOption.label : placeholder}
                    </span>
                </div>
                <div className="flex items-center gap-1 flex-shrink-0">
                    {value && !disabled && (
                        <span
                            onClick={(e) => {
                                e.stopPropagation();
                                onChange('');
                            }}
                            className="text-slate-400 hover:text-red-600 p-0.5 cursor-pointer font-bold text-xs"
                            title="Quitar selección"
                        >
                            ✕
                        </span>
                    )}
                    <span className={`text-[10px] text-slate-500 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`}>
                        ▼
                    </span>
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
                                                ? 'bg-white/20 text-white'
                                                : opt.badgeClass || 'bg-blue-100 text-blue-900'
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

export default function SalidasOficinas() {
    const [salidas, setSalidas] = useState([]);
    const [metrics, setMetrics] = useState({
        ultimasFechasSalida: 'N/A',
        totalSalidas: 0,
        salidasEsteMes: 0,
        countSalidasBajoStock: 0,
        articulosBajoStock: []
    });
    const [catalogs, setCatalogs] = useState({
        articulos: [],
        gerencias: [],
        areas: [],
        unidad_medidas: []
    });
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
        can_destroy: false,
        can_export: false
    });

    // Filtros combinados simultáneos
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [busqueda, setBusqueda] = useState('');
    const [filterGerencia, setFilterGerencia] = useState('');
    const [filterArea, setFilterArea] = useState('');
    const [filterArticulo, setFilterArticulo] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Selección múltiple para exportación y eliminación por lote
    const [selectedIds, setSelectedIds] = useState([]);

    // Estado de modales (Crear / Editar)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedSalida, setSelectedSalida] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [formData, setFormData] = useState({
        fecha_salida: new Date().toISOString().split('T')[0],
        articulo_id: '',
        unidad_medida_id: '',
        cantidad: 1,
        gerencia_id: '',
        area_id: '',
        ajuste_inventario: ''
    });
    const [formErrors, setFormErrors] = useState({});

    // Opciones formateadas
    const articulosOptions = useMemo(() => {
        return (catalogs.articulos || []).map(a => ({
            id: a.id,
            label: `${String(a.id).padStart(4, '0')} - ${a.articulo}`,
            badge: a.stock_disponible > 0 ? `${a.stock_disponible} disp.` : 'Agotado',
            badgeClass: a.stock_disponible > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700',
            stock_disponible: a.stock_disponible,
            unidad_medida_id: a.unidad_medida_id,
            unidad_medida: a.unidad_medida
        }));
    }, [catalogs.articulos]);

    // 1. Opciones de Gerencias únicas para filtros y modales
    const gerenciasOptions = useMemo(() => {
        const map = new Map();
        (catalogs.gerencias || []).forEach(g => {
            const rawName = (g.gerencia || '').trim();
            if (rawName && !map.has(rawName.toUpperCase())) {
                map.set(rawName.toUpperCase(), {
                    id: g.id,
                    label: rawName,
                    gerencia: rawName,
                    area_id: g.area_id
                });
            }
        });
        return Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.gerencias]);

    // 2. Opciones de Áreas para el Filtro Principal (Casadas con la Gerencia seleccionada)
    const areasOptions = useMemo(() => {
        let list = catalogs.areas || [];
        if (filterGerencia) {
            const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(filterGerencia));
            const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';

            // IDs de áreas casadas con esta gerencia en la tabla pivot gerencias
            const marriedAreaIds = new Set();
            (catalogs.gerencias || []).forEach(g => {
                const gName = (g.gerencia || '').trim().toUpperCase();
                if (g.area_id && (String(g.id) === String(filterGerencia) || (selectedGerName && gName === selectedGerName))) {
                    marriedAreaIds.add(String(g.area_id));
                }
            });

            list = list.filter(ar => {
                if (marriedAreaIds.has(String(ar.id))) return true;
                if (ar.gerencia_ids && Array.isArray(ar.gerencia_ids)) {
                    if (ar.gerencia_ids.some(gid => String(gid) === String(filterGerencia))) return true;
                }
                if (ar.gerencia_names && Array.isArray(ar.gerencia_names) && selectedGerName) {
                    if (ar.gerencia_names.some(gn => (gn || '').trim().toUpperCase() === selectedGerName)) return true;
                }
                return false;
            });
        }
        return list.map(ar => ({
            id: ar.id,
            label: ar.area,
        })).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.areas, catalogs.gerencias, filterGerencia]);

    // 3. Opciones de Áreas para el Modal Crear / Editar (Casadas con la Gerencia del formulario)
    const modalAreasOptions = useMemo(() => {
        let list = catalogs.areas || [];
        if (formData.gerencia_id) {
            const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(formData.gerencia_id));
            const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';

            const marriedAreaIds = new Set();
            (catalogs.gerencias || []).forEach(g => {
                const gName = (g.gerencia || '').trim().toUpperCase();
                if (g.area_id && (String(g.id) === String(formData.gerencia_id) || (selectedGerName && gName === selectedGerName))) {
                    marriedAreaIds.add(String(g.area_id));
                }
            });

            const filtered = list.filter(ar => {
                if (marriedAreaIds.has(String(ar.id))) return true;
                if (ar.gerencia_ids && Array.isArray(ar.gerencia_ids)) {
                    if (ar.gerencia_ids.some(gid => String(gid) === String(formData.gerencia_id))) return true;
                }
                if (ar.gerencia_names && Array.isArray(ar.gerencia_names) && selectedGerName) {
                    if (ar.gerencia_names.some(gn => (gn || '').trim().toUpperCase() === selectedGerName)) return true;
                }
                return false;
            });

            if (filtered.length > 0) {
                list = filtered;
            }
        }
        return list.map(ar => ({
            id: ar.id,
            label: ar.area,
        })).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.areas, catalogs.gerencias, formData.gerencia_id]);

    // Manejador del cambio de Gerencia en el Filtro Principal
    const handleFilterGerenciaChange = (gId) => {
        setFilterGerencia(gId);
        if (gId) {
            // Si hay un área seleccionada, verificar si pertenece a la nueva gerencia
            if (filterArea) {
                const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(gId));
                const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';
                const marriedAreaIds = new Set(
                    (catalogs.gerencias || [])
                        .filter(g => g.area_id && (String(g.id) === String(gId) || (selectedGerName && (g.gerencia || '').trim().toUpperCase() === selectedGerName)))
                        .map(g => String(g.area_id))
                );
                if (!marriedAreaIds.has(String(filterArea))) {
                    setFilterArea(''); // Resetear área porque no pertenece a esta gerencia
                }
            }
        }
    };

    // Manejador del cambio de Área en el Filtro Principal (Casamiento bidireccional)
    const handleFilterAreaChange = (aId) => {
        setFilterArea(aId);
        if (aId && !filterGerencia) {
            // Buscar la gerencia correspondiente a esta área
            const areaMatch = (catalogs.areas || []).find(ar => String(ar.id) === String(aId));
            const gerMatch = (catalogs.gerencias || []).find(g => String(g.area_id) === String(aId))
                || (areaMatch?.gerencia_ids && (catalogs.gerencias || []).find(g => areaMatch.gerencia_ids.includes(g.id)));

            if (gerMatch) {
                const repGer = gerenciasOptions.find(g => (g.gerencia || '').trim().toUpperCase() === (gerMatch.gerencia || '').trim().toUpperCase());
                if (repGer) {
                    setFilterGerencia(repGer.id);
                }
            }
        }
    };

    // Conteo de filtros activos
    const activeFiltersCount = (filterGerencia ? 1 : 0) +
        (filterArea ? 1 : 0) +
        (filterArticulo ? 1 : 0) +
        (fechaDesde || fechaHasta ? 1 : 0);

    // Cargar datos desde la API
    const fetchSalidas = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterGerencia) params.append('gerencia_id', filterGerencia);
            if (filterArea) params.append('area_id', filterArea);
            if (filterArticulo) params.append('articulo_id', filterArticulo);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);

            const res = await fetch(`/api/administracion/salida_oficinas?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            const json = await res.json();

            if (json.success && json.data) {
                const salData = json.data.salidas;
                setSalidas(salData.data || []);
                setPagination({
                    current_page: salData.current_page || 1,
                    last_page: salData.last_page || 1,
                    total: salData.total || 0,
                    from: salData.from || 0,
                    to: salData.to || 0,
                    per_page: salData.per_page || 15
                });
                if (json.data.metrics) setMetrics(json.data.metrics);
                if (json.data.catalogs) setCatalogs(json.data.catalogs);
                if (json.data.permissions) setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error cargando salidas:', err);
            setError(err.message || 'Error al conectar con el servidor');
        } finally {
            setLoading(false);
        }
    }, [busqueda, filterGerencia, filterArea, filterArticulo, fechaDesde, fechaHasta]);

    // Recargar al cambiar filtros con debounce para búsqueda
    useEffect(() => {
        const timer = setTimeout(() => {
            fetchSalidas(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [fetchSalidas]);

    // Presets rápidos de fechas
    const setDatePreset = (preset) => {
        const now = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        switch (preset) {
            case 'today':
                setFechaDesde(formatDate(now));
                setFechaHasta(formatDate(now));
                break;
            case 'thisWeek': {
                const day = now.getDay();
                const diffToMonday = now.getDate() - day + (day === 0 ? -6 : 1);
                const monday = new Date(now.setDate(diffToMonday));
                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                setFechaDesde(formatDate(monday));
                setFechaHasta(formatDate(sunday));
                break;
            }
            case 'thisMonth': {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                setFechaDesde(formatDate(firstDay));
                setFechaHasta(formatDate(lastDay));
                break;
            }
            case 'thisYear': {
                const firstDayYear = new Date(now.getFullYear(), 0, 1);
                const lastDayYear = new Date(now.getFullYear(), 11, 31);
                setFechaDesde(formatDate(firstDayYear));
                setFechaHasta(formatDate(lastDayYear));
                break;
            }
            case 'clear':
                setFechaDesde('');
                setFechaHasta('');
                break;
            default:
                break;
        }
    };

    const clearAllFilters = () => {
        setBusqueda('');
        setFilterGerencia('');
        setFilterArea('');
        setFilterArticulo('');
        setFechaDesde('');
        setFechaHasta('');
    };

    // Selección múltiple
    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(salidas.map(s => s.id));
        } else {
            setSelectedIds([]);
        }
    };

    const handleSelectOne = (id) => {
        setSelectedIds(prev =>
            prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
        );
    };

    // Manejo de modal Crear / Editar
    const openCreateModal = () => {
        setModalMode('create');
        setSelectedSalida(null);
        setFormData({
            fecha_salida: new Date().toISOString().split('T')[0],
            articulo_id: '',
            unidad_medida_id: '',
            cantidad: 1,
            gerencia_id: '',
            area_id: '',
            ajuste_inventario: ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    const openEditModal = (item) => {
        setModalMode('edit');
        setSelectedSalida(item);
        setFormData({
            fecha_salida: item.fecha_salida ? item.fecha_salida.split('T')[0] : '',
            articulo_id: item.articulo_id || '',
            unidad_medida_id: item.unidad_medida_id || '',
            cantidad: item.cantidad || 1,
            gerencia_id: item.gerencia_id || '',
            area_id: item.area_id || '',
            ajuste_inventario: item.ajuste_inventario || ''
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Al seleccionar artículo en el modal, autocompletar unidad de medida
    const handleModalArticuloChange = (artId) => {
        const art = (catalogs.articulos || []).find(a => String(a.id) === String(artId));
        setFormData(prev => ({
            ...prev,
            articulo_id: artId,
            unidad_medida_id: art?.unidad_medida_id || prev.unidad_medida_id
        }));
        if (formErrors.articulo_id) {
            setFormErrors(prev => ({ ...prev, articulo_id: null }));
        }
    };

    // Casamiento: Al seleccionar gerencia en el modal, filtrar y autoasignar área
    const handleModalGerenciaChange = (gId) => {
        const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(gId));
        const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';

        // Buscar todas las áreas casadas con esta gerencia
        const marriedAreaRows = (catalogs.gerencias || []).filter(g =>
            g.area_id && (String(g.id) === String(gId) || (selectedGerName && (g.gerencia || '').trim().toUpperCase() === selectedGerName))
        );

        let newAreaId = '';
        if (marriedAreaRows.length === 1) {
            // Solo tiene 1 área casada: autoasignarla de inmediato
            newAreaId = String(marriedAreaRows[0].area_id);
        } else if (formData.area_id) {
            // Si el área actual pertenece a la nueva gerencia, mantenerla
            const isMarried = marriedAreaRows.some(g => String(g.area_id) === String(formData.area_id));
            if (isMarried) {
                newAreaId = formData.area_id;
            }
        }

        // Determinar el ID exacto del registro gerencias (pareja gerencia + area)
        let exactGerId = gId;
        if (newAreaId) {
            const exactMatch = marriedAreaRows.find(g => String(g.area_id) === String(newAreaId));
            if (exactMatch) exactGerId = exactMatch.id;
        }

        setFormData(prev => ({
            ...prev,
            gerencia_id: exactGerId,
            area_id: newAreaId
        }));

        if (formErrors.gerencia_id) setFormErrors(prev => ({ ...prev, gerencia_id: null }));
        if (formErrors.area_id && newAreaId) setFormErrors(prev => ({ ...prev, area_id: null }));
    };

    // Casamiento: Al seleccionar área en el modal, autoasignar su gerencia correspondiente
    const handleModalAreaChange = (aId) => {
        let exactGerId = formData.gerencia_id;

        if (aId) {
            const currentGer = (catalogs.gerencias || []).find(g => String(g.id) === String(formData.gerencia_id));
            const currentGerName = currentGer?.gerencia ? currentGer.gerencia.trim().toUpperCase() : '';

            // Buscar la pareja exacta (gerencia actual + esta área) o la gerencia asociada a esta área
            const exactPair = (catalogs.gerencias || []).find(g =>
                String(g.area_id) === String(aId) && (!currentGerName || (g.gerencia || '').trim().toUpperCase() === currentGerName)
            ) || (catalogs.gerencias || []).find(g => String(g.area_id) === String(aId));

            if (exactPair) {
                exactGerId = exactPair.id;
            }
        }

        setFormData(prev => ({
            ...prev,
            area_id: aId,
            gerencia_id: exactGerId
        }));

        if (formErrors.area_id) setFormErrors(prev => ({ ...prev, area_id: null }));
        if (formErrors.gerencia_id && exactGerId) setFormErrors(prev => ({ ...prev, gerencia_id: null }));
    };

    // Artículo seleccionado en modal y stock disponible
    const currentModalArticulo = useMemo(() => {
        return (catalogs.articulos || []).find(a => String(a.id) === String(formData.articulo_id));
    }, [catalogs.articulos, formData.articulo_id]);

    const maxStockDisponible = useMemo(() => {
        if (!currentModalArticulo) return 0;
        let base = currentModalArticulo.stock_disponible || 0;
        if (modalMode === 'edit' && selectedSalida && String(selectedSalida.articulo_id) === String(formData.articulo_id)) {
            base += (selectedSalida.cantidad || 0);
        }
        return base;
    }, [currentModalArticulo, modalMode, selectedSalida, formData.articulo_id]);

    // Enviar formulario (Crear / Editar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});

        // Validaciones en cliente
        const errors = {};
        if (!formData.fecha_salida) errors.fecha_salida = 'La fecha de salida es obligatoria.';
        if (!formData.articulo_id) errors.articulo_id = 'Debe seleccionar un artículo.';
        if (!formData.cantidad || Number(formData.cantidad) < 1) {
            errors.cantidad = 'La cantidad debe ser mayor a 0.';
        } else if (Number(formData.cantidad) > maxStockDisponible) {
            errors.cantidad = `Stock insuficiente. El máximo disponible es ${maxStockDisponible}.`;
        }
        if (!formData.gerencia_id) errors.gerencia_id = 'La gerencia solicitante es obligatoria.';
        if (!formData.area_id) errors.area_id = 'El área de destino es obligatoria.';

        if (Object.keys(errors).length > 0) {
            setFormErrors(errors);
            return;
        }

        setSubmitting(true);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const url = modalMode === 'create'
                ? '/administracion/salida_oficina'
                : `/administracion/salida_oficina/${selectedSalida.id}`;

            const method = modalMode === 'create' ? 'POST' : 'PUT';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            const data = await res.json();

            if (!res.ok) {
                if (data.errors) {
                    setFormErrors(data.errors);
                }
                throw new Error(data.message || 'Error al procesar la solicitud.');
            }

            Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Salida Registrada!' : '¡Salida Actualizada!',
                text: data.message || 'Operación completada exitosamente.',
                timer: 2000,
                showConfirmButton: false
            });

            setModalOpen(false);
            fetchSalidas(pagination.current_page);
        } catch (err) {
            console.error('Error guardando salida:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar la salida. Verifique los datos.',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminación individual con confirmación de contraseña
    const handleDeleteSingle = async (salida) => {
        const { value: password } = await Swal.fire({
            title: '¿Eliminar salida?',
            text: `Se restaurarán ${salida.cantidad} unidades al inventario. Ingrese su contraseña para confirmar:`,
            input: 'password',
            inputPlaceholder: 'Su contraseña de usuario...',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            preConfirm: (pwd) => {
                if (!pwd) {
                    Swal.showValidationMessage('Debe ingresar su contraseña.');
                }
                return pwd;
            }
        });

        if (!password) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            Swal.fire({
                title: 'Eliminando...',
                text: 'Restaurando unidades al inventario',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch(`/administracion/salida_oficina/${salida.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al eliminar el registro.');

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: json.message || 'Registro eliminado y stock restaurado.',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds(prev => prev.filter(x => x !== salida.id));
            fetchSalidas(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar',
                text: err.message || 'No se pudo eliminar el registro.',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Eliminación múltiple con contraseña
    const handleDeleteMultiple = async () => {
        if (selectedIds.length === 0) return;

        const { value: password } = await Swal.fire({
            title: `¿Eliminar ${selectedIds.length} salidas?`,
            text: 'Se restaurarán todas las unidades de los registros seleccionados. Ingrese su contraseña:',
            input: 'password',
            inputPlaceholder: 'Su contraseña de usuario...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar lote',
            cancelButtonText: 'Cancelar',
            preConfirm: (pwd) => {
                if (!pwd) {
                    Swal.showValidationMessage('Debe ingresar su contraseña.');
                }
                return pwd;
            }
        });

        if (!password) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            Swal.fire({
                title: 'Eliminando lote...',
                text: 'Restaurando stock en inventario',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch('/administracion/salida_oficina/destroy-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: selectedIds, password })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al eliminar los registros.');

            Swal.fire({
                icon: 'success',
                title: 'Lote eliminado',
                text: json.message || 'Registros eliminados correctamente.',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(1);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el lote.',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Eliminación total con contraseña
    const handleDeleteAll = async () => {
        const { value: password } = await Swal.fire({
            title: '⚠️ ¿ELIMINAR TODAS LAS SALIDAS?',
            text: 'Esta acción eliminará todas las salidas registradas y restaurará todas las unidades al inventario. Requiere contraseña:',
            input: 'password',
            inputPlaceholder: 'Su contraseña de usuario...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar TODO',
            cancelButtonText: 'Cancelar',
            preConfirm: (pwd) => {
                if (!pwd) {
                    Swal.showValidationMessage('Debe ingresar su contraseña.');
                }
                return pwd;
            }
        });

        if (!password) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            Swal.fire({
                title: 'Eliminando todas las salidas...',
                text: 'Restaurando inventario general',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch('/administracion/salida_oficina/destroy-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password })
            });

            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Error al eliminar todos los registros.');

            Swal.fire({
                icon: 'success',
                title: 'Completado',
                text: json.message || 'Todas las salidas fueron eliminadas y restauradas.',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(1);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo completar la eliminación total.',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Exportación (Excel y PDF)
    const handleExport = (type, mode = 'all') => {
        const params = new URLSearchParams();
        if (mode === 'selected' && selectedIds.length > 0) {
            params.append('selected_ids', selectedIds.join(','));
        } else {
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterGerencia) params.append('gerencia_id', filterGerencia);
            if (filterArea) params.append('area_id', filterArea);
            if (filterArticulo) params.append('articulo_id', filterArticulo);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        }

        const endpoint = type === 'excel'
            ? `/administracion/salida_oficina/export/excel?${params.toString()}`
            : `/administracion/salida_oficina/export/pdf?${params.toString()}`;

        const filename = `salida_oficina_${new Date().toISOString().split('T')[0]}.${type === 'excel' ? 'xlsx' : 'pdf'}`;

        downloadWithProgress(endpoint, filename, type === 'excel' ? 'Excel' : 'PDF');
    };

    return (
        <div className="space-y-6 animate-fadeIn pb-12">
            {/* Header del Módulo */}
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl sm:text-3xl font-extrabold text-blue-900 tracking-tight">
                        Salida de Oficina
                    </h1>
                    <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Registro y control de despacho de materiales a gerencias y áreas
                    </p>
                </div>

                <div className="flex items-center gap-2">
                    {permissions.can_create && (
                        <button
                            type="button"
                            onClick={openCreateModal}
                            className="btn-save inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 active:translate-y-0"
                            style={{
                                background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
                            }}
                        >
                            <span className="text-base leading-none">＋</span>
                            <span>Nueva Salida</span>
                        </button>
                    )}
                </div>
            </div>

            {/* Tarjetas de Métricas Estadísticas */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
                {/* 1. Últimas Fechas de Salida */}
                <div className="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs transition-all hover:shadow-md">
                    <div className="flex items-center justify-between mb-3">
                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Última Salida
                        </span>
                        <div className="w-10 h-10 rounded-xl grid place-items-center bg-blue-50 text-blue-600 text-lg">
                            📅
                        </div>
                    </div>
                    <h3 className="text-2xl font-black text-slate-800">
                        {metrics.ultimasFechasSalida || 'N/A'}
                    </h3>
                    <p className="text-xs font-semibold text-blue-900 mt-1">
                        Fecha del despacho más reciente
                    </p>
                </div>

                {/* 2. Total Salidas */}
                <div className="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs transition-all hover:shadow-md">
                    <div className="flex items-center justify-between mb-3">
                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Total Despachos
                        </span>
                        <div className="w-10 h-10 rounded-xl grid place-items-center bg-emerald-50 text-emerald-600 text-lg">
                            🗃️
                        </div>
                    </div>
                    <div className="flex items-baseline gap-2">
                        <h3 className="text-2xl font-black text-slate-800">
                            {metrics.totalSalidas}
                        </h3>
                        <span className="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            +{metrics.salidasEsteMes} este mes
                        </span>
                    </div>
                    <p className="text-xs font-semibold text-blue-900 mt-1">
                        Registros acumulados en el sistema
                    </p>
                </div>

                {/* 3. Alerta Stock Bajo */}
                <div className="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs transition-all hover:shadow-md">
                    <div className="flex items-center justify-between mb-3">
                        <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Alerta Stock
                        </span>
                        <div className="w-10 h-10 rounded-xl grid place-items-center bg-amber-50 text-amber-600 text-lg">
                            ⚠️
                        </div>
                    </div>
                    <div className="flex items-baseline gap-2">
                        <h3 className="text-2xl font-black text-slate-800">
                            {metrics.countSalidasBajoStock}
                        </h3>
                        <span className="text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                            Artículos críticos
                        </span>
                    </div>
                    <p className="text-xs font-semibold text-slate-500 mt-1 truncate">
                        {metrics.articulosBajoStock?.length > 0
                            ? metrics.articulosBajoStock.map(a => a.articulo).join(', ')
                            : 'Inventario con stock suficiente'}
                    </p>
                </div>
            </div>

            {/* Panel Principal: Buscador, Filtros y Acciones */}
            <div className="bg-white border border-slate-200 rounded-2xl shadow-xs p-5 space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                    {/* Buscador en tiempo real (UN SOLO ICONO de lupa limpio) */}
                    <div className="relative flex-1 min-w-[260px] max-w-md">
                        <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            🔍
                        </span>
                        <input
                            type="text"
                            value={busqueda}
                            onChange={(e) => setBusqueda(e.target.value)}
                            placeholder="Buscar por artículo, gerencia, área o responsable..."
                            className="w-full pl-10 pr-10 py-2.5 bg-blue-100/70 border border-transparent rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-xs"
                        />
                        {busqueda && (
                            <button
                                type="button"
                                onClick={() => setBusqueda('')}
                                className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 font-bold"
                                title="Limpiar búsqueda"
                            >
                                ✕
                            </button>
                        )}
                    </div>

                    {/* Botones de acción derecha: Filtros, Exportar, Borrado */}
                    <div className="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                            className={`inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all border ${
                                showAdvancedFilters || activeFiltersCount > 0
                                    ? 'bg-blue-900 text-white border-blue-900 shadow-xs'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200'
                            }`}
                        >
                            <span>⚡ Filtros</span>
                            {activeFiltersCount > 0 && (
                                <span className="w-5 h-5 rounded-full bg-amber-400 text-blue-950 text-[10px] font-black grid place-items-center">
                                    {activeFiltersCount}
                                </span>
                            )}
                        </button>

                        {/* Exportar */}
                        <div className="relative inline-block text-left group">
                            <button
                                type="button"
                                className="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-blue-100/70 hover:bg-blue-100 text-blue-900 border border-white/20 shadow-xs transition-all"
                            >
                                <span>📥 Exportar</span>
                                <span className="text-[10px]">▼</span>
                            </button>
                            <div className="hidden group-hover:block absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-xl shadow-xl py-1.5 z-50 animate-fadeIn">
                                <div className="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    Exportar Todo
                                </div>
                                <button
                                    type="button"
                                    onClick={() => handleExport('excel', 'all')}
                                    className="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 flex items-center gap-2"
                                >
                                    <span>📊</span> <span>Excel (.xlsx)</span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handleExport('pdf', 'all')}
                                    className="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 flex items-center gap-2"
                                >
                                    <span>📄</span> <span>PDF (.pdf)</span>
                                </button>

                                {selectedIds.length > 0 && (
                                    <>
                                        <div className="border-t border-slate-100 my-1"></div>
                                        <div className="px-3 py-1 text-[10px] font-bold text-blue-600 uppercase tracking-wider">
                                            Seleccionados ({selectedIds.length})
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => handleExport('excel', 'selected')}
                                            className="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 flex items-center gap-2"
                                        >
                                            <span>📊</span> <span>Excel seleccionados</span>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleExport('pdf', 'selected')}
                                            className="w-full text-left px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-blue-50 flex items-center gap-2"
                                        >
                                            <span>📄</span> <span>PDF seleccionados</span>
                                        </button>
                                    </>
                                )}
                            </div>
                        </div>

                        {/* Eliminar Seleccionados */}
                        {selectedIds.length > 0 && permissions.can_destroy && (
                            <button
                                type="button"
                                onClick={handleDeleteMultiple}
                                className="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-all shadow-xs"
                                title="Eliminar registros seleccionados con contraseña"
                            >
                                <span>🗑️</span>
                                <span>Eliminar ({selectedIds.length})</span>
                            </button>
                        )}

                        {/* Eliminar Todo */}
                        {salidas.length > 0 && permissions.can_destroy && (
                            <button
                                type="button"
                                onClick={handleDeleteAll}
                                className="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-700 border border-slate-200 transition-all shadow-xs"
                                title="Eliminar todas las salidas restaurando el inventario"
                            >
                                <span>⚠️ Eliminar Todo</span>
                            </button>
                        )}
                    </div>
                </div>

                {/* Cajón de Filtros Avanzados */}
                {showAdvancedFilters && (
                    <div className="p-4 bg-gradient-to-br from-blue-50/60 to-slate-50/80 rounded-2xl border border-blue-100 space-y-4 animate-fadeIn">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            {/* Artículo */}
                            <SearchableSelect
                                label="📦 Artículo"
                                placeholder="Filtrar por artículo..."
                                options={articulosOptions}
                                value={filterArticulo}
                                onChange={setFilterArticulo}
                                icon="📦"
                            />

                            {/* Gerencia */}
                            <div>
                                <SearchableSelect
                                    label="🏢 Gerencia"
                                    placeholder="Filtrar por gerencia..."
                                    options={gerenciasOptions}
                                    value={filterGerencia}
                                    onChange={handleFilterGerenciaChange}
                                    icon="🏢"
                                />
                                {filterGerencia && (
                                    <p className="text-[10px] text-blue-700 font-semibold mt-1 flex items-center gap-1">
                                        <span>🔗</span>
                                        <span>Gerencia casada con sus áreas asignadas</span>
                                    </p>
                                )}
                            </div>

                            {/* Área */}
                            <div>
                                <SearchableSelect
                                    label="📂 Área"
                                    placeholder={filterGerencia ? "Áreas de esta gerencia..." : "Filtrar por área..."}
                                    options={areasOptions}
                                    value={filterArea}
                                    onChange={handleFilterAreaChange}
                                    icon="📂"
                                />
                                {filterGerencia && (
                                    <p className="text-[10px] text-slate-500 font-medium mt-1">
                                        Filtrando {areasOptions.length} área(s) vinculada(s)
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Rango de Fechas y Presets */}
                        <div className="flex flex-wrap items-end gap-3 pt-2 border-t border-blue-100/80">
                            <div>
                                <label className="block text-xs font-semibold text-blue-900 mb-1">
                                    📅 Desde
                                </label>
                                <input
                                    type="date"
                                    value={fechaDesde}
                                    onChange={(e) => setFechaDesde(e.target.value)}
                                    className="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-blue-900 mb-1">
                                    📅 Hasta
                                </label>
                                <input
                                    type="date"
                                    value={fechaHasta}
                                    onChange={(e) => setFechaHasta(e.target.value)}
                                    className="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:outline-none"
                                />
                            </div>

                            {/* Botones Presets Rápidos */}
                            <div className="flex flex-wrap items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => setDatePreset('today')}
                                    className="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-white hover:bg-blue-100 text-blue-900 border border-slate-200"
                                >
                                    Hoy
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setDatePreset('thisWeek')}
                                    className="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-white hover:bg-blue-100 text-blue-900 border border-slate-200"
                                >
                                    Esta Semana
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setDatePreset('thisMonth')}
                                    className="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-white hover:bg-blue-100 text-blue-900 border border-slate-200"
                                >
                                    Este Mes
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setDatePreset('thisYear')}
                                    className="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-white hover:bg-blue-100 text-blue-900 border border-slate-200"
                                >
                                    Este Año
                                </button>
                                {activeFiltersCount > 0 && (
                                    <button
                                        type="button"
                                        onClick={clearAllFilters}
                                        className="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 ml-1"
                                    >
                                        Limpiar Todo
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Filtros activos tipo pills */}
                {activeFiltersCount > 0 && (
                    <div className="flex flex-wrap items-center gap-1.5 pt-1">
                        <span className="text-[11px] font-bold text-slate-400 uppercase">Activos:</span>
                        {filterArticulo && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-900 text-xs font-bold rounded-lg">
                                <span>Artículo: {articulosOptions.find(a => String(a.id) === String(filterArticulo))?.label}</span>
                                <button type="button" onClick={() => setFilterArticulo('')} className="hover:text-red-600">✕</button>
                            </span>
                        )}
                        {filterGerencia && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-900 text-xs font-bold rounded-lg">
                                <span>Gerencia: {gerenciasOptions.find(g => String(g.id) === String(filterGerencia))?.label}</span>
                                <button type="button" onClick={() => setFilterGerencia('')} className="hover:text-red-600">✕</button>
                            </span>
                        )}
                        {filterArea && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-900 text-xs font-bold rounded-lg">
                                <span>Área: {areasOptions.find(ar => String(ar.id) === String(filterArea))?.label}</span>
                                <button type="button" onClick={() => setFilterArea('')} className="hover:text-red-600">✕</button>
                            </span>
                        )}
                        {(fechaDesde || fechaHasta) && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-900 text-xs font-bold rounded-lg">
                                <span>Rango: {fechaDesde || '...'} a {fechaHasta || '...'}</span>
                                <button type="button" onClick={() => { setFechaDesde(''); setFechaHasta(''); }} className="hover:text-red-600">✕</button>
                            </span>
                        )}
                    </div>
                )}
            </div>

            {/* Listado de Salidas */}
            <div className="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
                <div className="px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            checked={salidas.length > 0 && selectedIds.length === salidas.length}
                            onChange={handleSelectAll}
                            className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                        />
                        <span className="text-xs font-bold text-slate-600">
                            {selectedIds.length > 0 ? `${selectedIds.length} seleccionados` : 'Seleccionar página'}
                        </span>
                    </div>

                    <span className="text-xs font-semibold text-slate-500">
                        Mostrando {pagination.from || 0} - {pagination.to || 0} de {pagination.total || 0} registros
                    </span>
                </div>

                {loading ? (
                    <div className="p-12 text-center">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-600">Cargando despachos de oficina...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center text-red-600">
                        <p className="text-base font-bold">Error al cargar datos</p>
                        <p className="text-xs text-slate-500 mt-1">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchSalidas(1)}
                            className="mt-3 px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold shadow-xs hover:bg-blue-800"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : salidas.length === 0 ? (
                    <div className="p-12 text-center text-slate-400">
                        <div className="text-4xl mb-2">📭</div>
                        <p className="text-base font-bold text-slate-600">No se encontraron salidas</p>
                        <p className="text-xs mt-1">Intente ajustar los filtros de búsqueda o registre una nueva salida.</p>
                        {activeFiltersCount > 0 && (
                            <button
                                type="button"
                                onClick={clearAllFilters}
                                className="mt-3 px-4 py-2 bg-blue-100 text-blue-900 rounded-xl text-xs font-bold hover:bg-blue-200"
                            >
                                Limpiar Filtros
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="divide-y divide-slate-100">
                        {salidas.map((item) => {
                            const isSelected = selectedIds.includes(item.id);
                            return (
                                <div
                                    key={item.id}
                                    className={`p-4 sm:p-5 transition-all hover:bg-blue-50/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 ${
                                        isSelected ? 'bg-blue-50/60' : ''
                                    }`}
                                >
                                    <div className="flex items-start gap-3 flex-1 min-w-0">
                                        <input
                                            type="checkbox"
                                            checked={isSelected}
                                            onChange={() => handleSelectOne(item.id)}
                                            className="mt-1 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                                        />

                                        <div className="space-y-1.5 flex-1 min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="text-xs font-mono font-bold text-slate-400">
                                                    #{String(item.id).padStart(4, '0')}
                                                </span>
                                                <h4 className="text-sm font-extrabold text-slate-800 truncate">
                                                    {item.articulos?.articulo || 'Artículo no especificado'}
                                                </h4>
                                                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-900 border border-blue-200">
                                                    <span>x {item.cantidad}</span>
                                                    <span className="text-[10px] font-semibold opacity-75">
                                                        {item.unidad_medidas?.unidad_medida || 'UND'}
                                                    </span>
                                                </span>
                                            </div>

                                            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                                <span className="flex items-center gap-1 font-semibold text-slate-700">
                                                    <span>🏢</span>
                                                    <span>{item.gerencias?.gerencia || 'Sin Gerencia'}</span>
                                                </span>
                                                <span className="flex items-center gap-1">
                                                    <span>📂</span>
                                                    <span>{item.areas?.area || 'Sin Área'}</span>
                                                </span>
                                                <span className="flex items-center gap-1 font-mono">
                                                    <span>📅</span>
                                                    <span>{item.fecha_salida ? item.fecha_salida.split('T')[0] : 'N/A'}</span>
                                                </span>
                                                {item.users && (
                                                    <span className="flex items-center gap-1 text-slate-400">
                                                        <span>👤</span>
                                                        <span>{item.users.name || item.users.username}</span>
                                                    </span>
                                                )}
                                            </div>

                                            {item.ajuste_inventario && (
                                                <p className="text-xs text-slate-500 italic bg-slate-50 p-1.5 rounded-lg border border-slate-100 inline-block">
                                                    💬 {item.ajuste_inventario}
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Acciones */}
                                    <div className="flex items-center gap-1.5 self-end sm:self-center">
                                        {permissions.can_edit && (
                                            <button
                                                type="button"
                                                onClick={() => openEditModal(item)}
                                                className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-blue-900 bg-blue-100/70 hover:bg-blue-100 rounded-xl transition-all"
                                                title="Editar salida en modal"
                                            >
                                                <span>✏️</span>
                                                <span>Editar</span>
                                            </button>
                                        )}

                                        {permissions.can_destroy && (
                                            <button
                                                type="button"
                                                onClick={() => handleDeleteSingle(item)}
                                                className="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 rounded-xl transition-all"
                                                title="Eliminar salida y restaurar stock"
                                            >
                                                <span>🗑️</span>
                                            </button>
                                        )}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="p-4 bg-slate-50/80 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <div className="text-xs text-slate-500">
                            Página <span className="font-bold">{pagination.current_page}</span> de <span className="font-bold">{pagination.last_page}</span>
                        </div>

                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page === 1}
                                onClick={() => fetchSalidas(pagination.current_page - 1)}
                                className="px-3 py-1.5 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                Anterior
                            </button>
                            <button
                                type="button"
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => fetchSalidas(pagination.current_page + 1)}
                                className="px-3 py-1.5 rounded-lg text-xs font-bold border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Crear / Editar Salida de Oficina (Animación Mikael Ainalem con backdrop-blur) */}
            {modalOpen && (
                <div
                    className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate-fadeIn"
                    onClick={(e) => {
                        if (e.target === e.currentTarget && !submitting) {
                            setModalOpen(false);
                        }
                    }}
                >
                    <div
                        className="modal-content animate-modal-expand bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-xl max-h-[92vh] overflow-y-auto"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Cabecera del modal con degradado corporativo */}
                        <div
                            className="p-5 sm:p-6 text-white rounded-t-3xl flex items-center justify-between relative overflow-hidden"
                            style={{
                                background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
                            }}
                        >
                            <div>
                                <h3 className="text-lg sm:text-xl font-black tracking-tight">
                                    {modalMode === 'create' ? 'Registrar Salida de Oficina' : 'Editar Salida de Oficina'}
                                </h3>
                                <p className="text-xs text-blue-100 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Complete los datos para despachar material desde almacén'
                                        : `Actualizando despacho #${String(selectedSalida?.id).padStart(4, '0')}`}
                                </p>
                            </div>
                            <button
                                type="button"
                                disabled={submitting}
                                onClick={() => setModalOpen(false)}
                                className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold grid place-items-center text-sm transition-all"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="p-5 sm:p-6 space-y-4">
                            {/* Fecha de Salida */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    📅 Fecha de Salida <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    max={new Date().toISOString().split('T')[0]}
                                    value={formData.fecha_salida}
                                    onChange={(e) => setFormData(prev => ({ ...prev, fecha_salida: e.target.value }))}
                                    className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                        formErrors.fecha_salida ? 'border-red-400 bg-red-50' : 'border-white/20'
                                    }`}
                                />
                                {formErrors.fecha_salida && (
                                    <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.fecha_salida}</p>
                                )}
                            </div>

                            {/* Artículo */}
                            <div>
                                <SearchableSelect
                                    label="📦 Artículo"
                                    required={true}
                                    placeholder="Seleccione el artículo a despachar..."
                                    options={articulosOptions}
                                    value={formData.articulo_id}
                                    onChange={handleModalArticuloChange}
                                    error={formErrors.articulo_id}
                                    icon="📦"
                                    allLabel={null}
                                />
                                {currentModalArticulo && (
                                    <div className="mt-2 p-2.5 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-between text-xs">
                                        <span className="font-semibold text-slate-700">
                                            Stock Disponible en Almacén:
                                        </span>
                                        <span className={`font-black px-2 py-0.5 rounded-full ${
                                            maxStockDisponible > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700'
                                        }`}>
                                            {maxStockDisponible} {currentModalArticulo.unidad_medida || 'UNIDADES'}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Cantidad y Unidad de Medida */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        🔢 Cantidad a Despachar <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={maxStockDisponible > 0 ? maxStockDisponible : 1}
                                        value={formData.cantidad}
                                        onChange={(e) => setFormData(prev => ({ ...prev, cantidad: e.target.value }))}
                                        placeholder="Ej: 5"
                                        className={`w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.cantidad ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                    />
                                    {formErrors.cantidad && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.cantidad}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📏 Unidad de Medida
                                    </label>
                                    <input
                                        type="text"
                                        disabled={true}
                                        value={currentModalArticulo?.unidad_medida || 'UNIDAD'}
                                        className="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed"
                                    />
                                </div>
                            </div>

                            {/* Gerencia y Área (Casadas) */}
                            <div>
                                <SearchableSelect
                                    label="🏢 Gerencia Solicitante"
                                    required={true}
                                    placeholder="Seleccione gerencia..."
                                    options={gerenciasOptions}
                                    value={formData.gerencia_id}
                                    onChange={handleModalGerenciaChange}
                                    error={formErrors.gerencia_id}
                                    icon="🏢"
                                    allLabel={null}
                                />
                            </div>

                            {/* Área */}
                            <div>
                                <SearchableSelect
                                    label="📂 Área de Destino (Casada con Gerencia)"
                                    required={true}
                                    placeholder={formData.gerencia_id ? "Seleccione el área vinculada..." : "Seleccione gerencia primero o elija un área..."}
                                    options={modalAreasOptions}
                                    value={formData.area_id}
                                    onChange={handleModalAreaChange}
                                    error={formErrors.area_id}
                                    icon="📂"
                                    allLabel={null}
                                />
                                {formData.gerencia_id && modalAreasOptions.length > 0 && (
                                    <p className="text-[10px] text-blue-700 font-semibold mt-1 flex items-center gap-1">
                                        <span>🔗</span>
                                        <span>Mostrando {modalAreasOptions.length} área(s) disponible(s) para esta Gerencia</span>
                                    </p>
                                )}
                            </div>

                            {/* Ajuste de Inventario / Observación */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    💬 Ajuste de Inventario / Observaciones
                                </label>
                                <textarea
                                    rows="3"
                                    value={formData.ajuste_inventario}
                                    onChange={(e) => setFormData(prev => ({ ...prev, ajuste_inventario: e.target.value }))}
                                    placeholder="Motivo del despacho, destinatario o detalles adicionales..."
                                    className="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-white/20 bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all resize-none"
                                />
                            </div>

                            {/* Botones de Acción */}
                            <div className="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    disabled={submitting}
                                    onClick={() => setModalOpen(false)}
                                    className="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all"
                                >
                                    Regresar
                                </button>

                                <button
                                    type="submit"
                                    disabled={submitting || (formData.articulo_id && maxStockDisponible < 1)}
                                    className="btn-save inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
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
                                            <span>{modalMode === 'create' ? 'Guardar Salida' : 'Actualizar Salida'}</span>
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
