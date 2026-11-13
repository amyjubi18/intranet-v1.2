import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import Swal from 'sweetalert2';
import { downloadWithProgress } from '../../utils/downloadWithProgress';

/**
 * Selector interactivo con autocompletado y diseño Tailwind
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
    const [permissions, setPermissions] = useState({
        can_create: false,
        can_edit: false,
        can_destroy: false,
        can_export: true
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

    // Filtros de búsqueda
    const [busqueda, setBusqueda] = useState('');
    const [filterGerencia, setFilterGerencia] = useState('');
    const [filterArea, setFilterArea] = useState('');
    const [filterArticulo, setFilterArticulo] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');
    const [fechaIngresoDesde, setFechaIngresoDesde] = useState('');
    const [fechaIngresoHasta, setFechaIngresoHasta] = useState('');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);

    // Selección múltiple para exportación y eliminación
    const [selectedIds, setSelectedIds] = useState([]);

    // Estado del modal Crear / Editar
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

    // Opciones formateadas de artículos
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

    // Opciones de Gerencias únicas para filtros y modales
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

    // Opciones de Áreas para el Filtro Principal (Casadas con la Gerencia seleccionada)
    const areasOptions = useMemo(() => {
        let list = catalogs.areas || [];
        if (filterGerencia) {
            const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(filterGerencia));
            const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';

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

    // Opciones de Áreas para el Modal Crear / Editar (Casadas con la Gerencia del formulario)
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
            if (filterArea) {
                const selectedGer = (catalogs.gerencias || []).find(g => String(g.id) === String(gId));
                const selectedGerName = selectedGer?.gerencia ? selectedGer.gerencia.trim().toUpperCase() : '';
                const marriedAreaIds = new Set(
                    (catalogs.gerencias || [])
                        .filter(g => g.area_id && (String(g.id) === String(gId) || (selectedGerName && (g.gerencia || '').trim().toUpperCase() === selectedGerName)))
                        .map(g => String(g.area_id))
                );
                if (!marriedAreaIds.has(String(filterArea))) {
                    setFilterArea('');
                }
            }
        }
    };

    // Manejador del cambio de Área en el Filtro Principal (Casamiento bidireccional)
    const handleFilterAreaChange = (aId) => {
        setFilterArea(aId);
        if (aId && !filterGerencia) {
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
        (fechaDesde || fechaHasta ? 1 : 0) +
        (fechaIngresoDesde || fechaIngresoHasta ? 1 : 0);

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
            if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
            if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);

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
    }, [busqueda, filterGerencia, filterArea, filterArticulo, fechaDesde, fechaHasta, fechaIngresoDesde, fechaIngresoHasta]);

    // Recargar al cambiar filtros con debounce para búsqueda
    useEffect(() => {
        const timer = setTimeout(() => {
            fetchSalidas(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [fetchSalidas]);

    // Presets rápidos de fechas
    const setSalidaDatePreset = (preset) => {
        const today = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const formatYMD = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

        if (preset === 'today') {
            const d = formatYMD(today);
            setFechaDesde(d);
            setFechaHasta(d);
        } else if (preset === 'week') {
            const dayOfWeek = today.getDay();
            const monday = new Date(today);
            monday.setDate(today.getDate() - ((dayOfWeek + 6) % 7));
            setFechaDesde(formatYMD(monday));
            setFechaHasta(formatYMD(today));
        } else if (preset === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            setFechaDesde(formatYMD(firstDay));
            setFechaHasta(formatYMD(today));
        } else if (preset === 'year') {
            const firstDayYear = new Date(today.getFullYear(), 0, 1);
            setFechaDesde(formatYMD(firstDayYear));
            setFechaHasta(formatYMD(today));
        }
    };

    const clearAllFilters = () => {
        setBusqueda('');
        setFilterGerencia('');
        setFilterArea('');
        setFilterArticulo('');
        setFechaDesde('');
        setFechaHasta('');
        setFechaIngresoDesde('');
        setFechaIngresoHasta('');
    };

    // Selección múltiple
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

        const marriedAreaRows = (catalogs.gerencias || []).filter(g =>
            g.area_id && (String(g.id) === String(gId) || (selectedGerName && (g.gerencia || '').trim().toUpperCase() === selectedGerName))
        );

        let newAreaId = '';
        if (marriedAreaRows.length === 1) {
            newAreaId = String(marriedAreaRows[0].area_id);
        } else if (formData.area_id) {
            const isMarried = marriedAreaRows.some(g => String(g.area_id) === String(formData.area_id));
            if (isMarried) {
                newAreaId = formData.area_id;
            }
        }

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
            Swal.fire({
                icon: 'error',
                title: 'Error al procesar',
                text: err.message || 'Ocurrió un error inesperado al guardar la salida.',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminación individual protegida con contraseña
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
            confirmButtonText: 'Sí, eliminar seleccionados',
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
                title: 'Eliminando lote seleccionado...',
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
            if (!res.ok) throw new Error(json.message || 'Error al eliminar todo.');

            Swal.fire({
                icon: 'success',
                title: 'Inventario restaurado',
                text: json.message || 'Todas las salidas fueron eliminadas.',
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

    // Exportación nativa con progreso
    const handleExport = (type, scope) => {
        const params = new URLSearchParams();
        if (busqueda.trim()) params.append('busqueda', busqueda.trim());
        if (filterGerencia) params.append('gerencia', filterGerencia);
        if (filterArea) params.append('area', filterArea);
        if (filterArticulo) params.append('articulo', filterArticulo);
        if (fechaDesde) params.append('fecha_desde', fechaDesde);
        if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
        if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);

        if (scope === 'selected') {
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Selección requerida',
                    text: 'Marque al menos una casilla para exportar los seleccionados.',
                    confirmButtonColor: '#1e3a8a'
                });
                return;
            }
            params.append('selected_ids', selectedIds.join(','));
        }

        const endpoint = type === 'excel'
            ? `/administracion/salida_oficina/export/excel?${params.toString()}`
            : `/administracion/salida_oficina/export/pdf?${params.toString()}`;

        const filename = `salida_oficina_${new Date().toISOString().split('T')[0]}.${type === 'excel' ? 'xlsx' : 'pdf'}`;

        downloadWithProgress(endpoint, filename, type === 'excel' ? 'Excel' : 'PDF');
    };

    // Agrupación de salidas por artículo para el diseño original idéntico a Blade
    const groupedSalidas = useMemo(() => {
        const groups = {};
        salidas.forEach(item => {
            const artId = item.articulo_id || item.articulos?.id || 'sin_articulo';
            if (!groups[artId]) {
                groups[artId] = {
                    articuloId: artId,
                    articulo: item.articulos || (catalogs.articulos || []).find(a => String(a.id) === String(artId)) || null,
                    items: []
                };
            }
            groups[artId].items.push(item);
        });
        return Object.values(groups);
    }, [salidas, catalogs.articulos]);

    // Helper para formatear fechas a DD/MM/YYYY
    const formatDisplayDate = (d) => {
        if (!d) return 'N/A';
        try {
            const clean = d.split('T')[0];
            const [year, month, day] = clean.split('-');
            if (year && month && day) {
                return `${day}/${month}/${year}`;
            }
            return d;
        } catch {
            return d;
        }
    };

    return (
        <div className="w-full space-y-6 animate-fadeIn pb-12">
            {/* Título Principal (Exacto como antes) */}
            <h2 className="text-4xl font-semibold leading-tight text-center text-blue-900 mb-6">
                Salida de Oficina
            </h2>

            {/* Tarjetas de estadísticas superiores */}
            <div className="px-2 sm:px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
                    {/* 1. Últimas Fechas de Salida */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500" style={{ fontSize: 24 }}>📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">
                            {metrics.ultimasFechasSalida || 'N/A'}
                        </h3>
                        <p className="text-sm text-blue-900 font-semibold">Últimas Fechas de Salida</p>
                    </div>

                    {/* 2. Total Salidas */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500" style={{ fontSize: 24 }}>🗃️</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">
                            {metrics.totalSalidas}
                        </h3>
                        <p className="text-sm text-blue-900 font-semibold">Total Salidas</p>
                        <div className="mt-3 flex items-center gap-2 text-xs">
                            <span className="text-green-500 font-bold">+{metrics.salidasEsteMes}</span>
                            <span className="text-blue-900 font-semibold">este mes</span>
                        </div>
                    </div>

                    {/* 3. Artículos con Bajo Stock */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                                <span className="text-red-500" style={{ fontSize: 24 }}>⚠️</span>
                            </div>
                            <span className="text-xs px-2.5 py-1 rounded-full bg-red-500/20 text-red-500 font-bold">
                                Alerta ({metrics.countSalidasBajoStock})
                            </span>
                        </div>
                        <h3 className="text-sm text-blue-900 font-semibold mb-2">Articulos con Bajo Stock</h3>
                        <div className="text-sm space-y-1">
                            {metrics.articulosBajoStock && metrics.articulosBajoStock.length > 0 ? (
                                metrics.articulosBajoStock.slice(0, 3).map((item, idx) => (
                                    <div key={item.id || idx} className="flex items-center gap-2 bg-red-500/20 rounded-md p-1">
                                        <div className="w-12 h-12 rounded flex items-center justify-center flex-shrink-0 bg-white/40">
                                            {item.imagen ? (
                                                <img src={`/storage/${item.imagen}`} alt={item.articulo} className="w-12 h-12 object-cover rounded" />
                                            ) : (
                                                <span className="text-blue-400" style={{ fontSize: 16 }}>📦</span>
                                            )}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <p className="text-red-600 text-sm font-bold truncate">{item.articulo || 'N/A'}</p>
                                            <p className="text-red-600 text-sm">
                                                ({item.stock_disponible ?? item.cantidad ?? 0} unidades)
                                            </p>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <p className="text-green-600 font-semibold text-xs">No hay items bajo stock</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Panel de Búsqueda y Filtros Avanzados (Diseño de Antes Exacto) */}
            <div className="px-2 sm:px-6 mb-6 relative z-30 overflow-visible">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
                    <div className="flex flex-col gap-4">
                        {/* Fila Superior: Buscador Rápido + Acciones (Exportar, Eliminar, Nuevo) */}
                        <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 pb-4 border-b border-gray-100">
                            {/* Buscador general (Un solo icono limpio) */}
                            <div className="flex-1 max-w-xl">
                                <div className="relative">
                                    <input
                                        type="text"
                                        value={busqueda}
                                        onChange={(e) => setBusqueda(e.target.value)}
                                        placeholder="Buscar por artículo, gerencia, área o responsable (Enter)..."
                                        className="w-full bg-blue-50/50 border border-slate-200 rounded-xl pl-10 pr-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                                    />
                                    <span className="absolute left-3.5 top-3 text-slate-400 text-sm">🔍</span>
                                    {busqueda && (
                                        <button
                                            type="button"
                                            onClick={() => setBusqueda('')}
                                            className="absolute right-3.5 top-2.5 text-slate-400 hover:text-slate-600 font-bold"
                                            title="Limpiar búsqueda"
                                        >
                                            ✕
                                        </button>
                                    )}
                                </div>
                            </div>

                            {/* Acciones */}
                            <div className="flex flex-wrap items-center justify-end gap-3">
                                {/* Exportar select */}
                                <select
                                    onChange={(e) => {
                                        if (!e.target.value) return;
                                        const [format, scope] = e.target.value.split(':');
                                        handleExport(format, scope);
                                        e.target.value = '';
                                    }}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs"
                                    defaultValue=""
                                >
                                    <option value="" disabled>📤 Exportar</option>
                                    <optgroup label="Exportar Todos">
                                        <option value="excel:all">📊 Excel - Todos los registros</option>
                                        <option value="pdf:all">📄 PDF - Todos los registros</option>
                                    </optgroup>
                                    <optgroup label="Exportar Seleccionados">
                                        <option value="excel:selected" disabled={selectedIds.length === 0}>
                                            📊 Excel - Solo seleccionados ({selectedIds.length})
                                        </option>
                                        <option value="pdf:selected" disabled={selectedIds.length === 0}>
                                            📄 PDF - Solo seleccionados ({selectedIds.length})
                                        </option>
                                    </optgroup>
                                </select>

                                {/* Eliminar Seleccionados */}
                                {permissions.can_destroy && (
                                    <button
                                        type="button"
                                        disabled={selectedIds.length === 0}
                                        onClick={handleDeleteMultiple}
                                        className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-600 text-white hover:bg-red-700 active:translate-y-0.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-xs cursor-pointer"
                                    >
                                        <span>🗑️</span>
                                        <span>Eliminar Seleccionados</span>
                                    </button>
                                )}

                                {/* Eliminar Todo */}
                                {permissions.can_destroy && salidas.length > 0 && (
                                    <button
                                        type="button"
                                        onClick={handleDeleteAll}
                                        className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-gray-600 text-white hover:bg-gray-700 active:translate-y-0.5 text-sm shadow-xs cursor-pointer"
                                    >
                                        <span>⚠️</span>
                                        <span>Eliminar Todo</span>
                                    </button>
                                )}

                                {/* Filtros Avanzados */}
                                <button
                                    type="button"
                                    onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                    className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 text-sm shadow-xs cursor-pointer"
                                >
                                    <span>⚡</span>
                                    <span>Filtros Avanzados</span>
                                    {activeFiltersCount > 0 && (
                                        <span className="w-5 h-5 rounded-full bg-amber-400 text-blue-950 text-xs font-black grid place-items-center">
                                            {activeFiltersCount}
                                        </span>
                                    )}
                                </button>

                                {/* Nueva Salida */}
                                {permissions.can_create && (
                                    <button
                                        type="button"
                                        onClick={openCreateModal}
                                        className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-xs cursor-pointer"
                                    >
                                        <span>➕</span>
                                        <span>Nueva Salida</span>
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Cajón de Filtros Avanzados */}
                        {showAdvancedFilters && (
                            <div className="pt-2 animate-fadeIn space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {/* Gerencia Casada */}
                                    <div>
                                        <SearchableSelect
                                            label="🏢 Gerencia"
                                            options={gerenciasOptions}
                                            value={filterGerencia}
                                            onChange={handleFilterGerenciaChange}
                                            placeholder="Todas las gerencias"
                                            icon="🏢"
                                            allLabel="Todas las gerencias"
                                        />
                                        {filterGerencia && (
                                            <p className="text-[10px] text-blue-800 font-semibold mt-1">
                                                🔗 Gerencia vinculada con sus áreas
                                            </p>
                                        )}
                                    </div>

                                    {/* Área Casada */}
                                    <div>
                                        <SearchableSelect
                                            label="📂 Área"
                                            options={areasOptions}
                                            value={filterArea}
                                            onChange={handleFilterAreaChange}
                                            placeholder={filterGerencia ? "Áreas de esta gerencia..." : "Todas las áreas"}
                                            icon="📂"
                                            allLabel="Todas las áreas"
                                        />
                                        {filterGerencia && (
                                            <p className="text-[10px] text-blue-800 font-semibold mt-1">
                                                Mostrando {areasOptions.length} área(s) disponible(s)
                                            </p>
                                        )}
                                    </div>

                                    {/* Artículo */}
                                    <div>
                                        <SearchableSelect
                                            label="📦 Artículo"
                                            options={articulosOptions}
                                            value={filterArticulo}
                                            onChange={setFilterArticulo}
                                            placeholder="Todos los artículos"
                                            icon="📦"
                                            allLabel="Todos los artículos"
                                        />
                                    </div>
                                </div>

                                {/* Rango de fechas con presets */}
                                <div className="pt-3 border-t border-blue-100/70">
                                    <div className="flex flex-wrap items-center justify-between gap-3 mb-2">
                                        <label className="block text-xs font-semibold text-blue-900">📅 Rango de Fechas (Salida)</label>
                                        <div className="flex items-center gap-1.5">
                                            <button type="button" onClick={() => setSalidaDatePreset('today')} className="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Hoy</button>
                                            <button type="button" onClick={() => setSalidaDatePreset('week')} className="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Esta Semana</button>
                                            <button type="button" onClick={() => setSalidaDatePreset('month')} className="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Mes</button>
                                            <button type="button" onClick={() => setSalidaDatePreset('year')} className="px-2.5 py-1 text-2xs font-semibold rounded-lg bg-white border border-blue-200 text-blue-900 hover:bg-blue-100 transition-colors cursor-pointer">Este Año</button>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <span className="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Desde</span>
                                            <input type="date" value={fechaDesde} onChange={(e) => setFechaDesde(e.target.value)} className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                                        </div>
                                        <div>
                                            <span className="block text-2xs font-medium text-slate-500 mb-1">Fecha Salida Hasta</span>
                                            <input type="date" value={fechaHasta} onChange={(e) => setFechaHasta(e.target.value)} className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                                        </div>
                                        <div>
                                            <span className="block text-2xs font-medium text-slate-500 mb-1">Fecha Factura Desde</span>
                                            <input type="date" value={fechaIngresoDesde} onChange={(e) => setFechaIngresoDesde(e.target.value)} className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                                        </div>
                                        <div>
                                            <span className="block text-2xs font-medium text-slate-500 mb-1">Fecha Factura Hasta</span>
                                            <input type="date" value={fechaIngresoHasta} onChange={(e) => setFechaIngresoHasta(e.target.value)} className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none" />
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center justify-end gap-3 pt-2">
                                    <button type="button" onClick={clearAllFilters} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 transition-colors cursor-pointer">
                                        Limpiar Todo
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Registros de Salida Agrupados por Artículo (Diseño de Antes Exacto) */}
            <div className="px-2 sm:px-6 mb-8 relative z-10">
                {loading ? (
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-12 text-center shadow-sm">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-600">Cargando despachos de oficina...</p>
                    </div>
                ) : error ? (
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-8 text-center text-red-600 shadow-sm">
                        <p className="text-base font-bold">Error al cargar datos</p>
                        <p className="text-xs text-slate-500 mt-1">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchSalidas(1)}
                            className="mt-3 px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold shadow-xs hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : groupedSalidas.length === 0 ? (
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-12 text-center shadow-sm">
                        <div className="w-24 h-24 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                            <span style={{ fontSize: 48 }}>📭</span>
                        </div>
                        <h3 className="text-xl font-bold text-blue-900 mb-2">No hay registros de salida</h3>
                        <p className="text-gray-500 mb-6">Comienza agregando una nueva salida de oficina</p>
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={openCreateModal}
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-900 text-white rounded-xl font-semibold hover:bg-blue-800 transition-colors cursor-pointer shadow-sm"
                            >
                                <span>➕</span>
                                <span>Nueva Salida</span>
                            </button>
                        )}
                    </div>
                ) : (
                    groupedSalidas.map((group) => {
                        const articulo = group.articulo;
                        const stockTotal = catalogs.articulos?.find(a => String(a.id) === String(group.articuloId))?.stock_disponible ?? 0;
                        const stockMinimo = articulo?.stock_min ?? 0;
                        const tieneStock = stockTotal > stockMinimo;
                        const colorClase = tieneStock ? 'from-blue-900 to-blue-800' : 'from-red-900 to-red-800';
                        const badgeColor = tieneStock ? 'bg-blue-500' : 'bg-red-500';
                        const badgeText = tieneStock ? 'Stock Saludable' : 'Stock Bajo';
                        const badgeIcon = tieneStock ? '✅' : '⚠️';

                        return (
                            <div key={group.articuloId} className="mb-6">
                                {/* Banner de Cabecera del Artículo */}
                                <div className={`bg-gradient-to-r ${colorClase} text-white rounded-2xl p-4 shadow-lg relative mb-2`}>
                                    <div className="flex items-center justify-between flex-wrap gap-4">
                                        <div className="flex items-center gap-4">
                                            <div className="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                                {articulo?.imagen ? (
                                                    <img src={`/storage/${articulo.imagen}`} alt={articulo.articulo} className="w-16 h-16 object-cover rounded-xl" />
                                                ) : (
                                                    <span style={{ fontSize: 32 }}>📦</span>
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <h3 className="text-xl font-bold">{articulo?.articulo || 'Artículo Desconocido'}</h3>
                                                <div className="flex flex-wrap gap-4 mt-1 text-sm">
                                                    <div>
                                                        <span className="text-blue-200">ITEM:</span>
                                                        <span className="font-semibold ml-1">{articulo ? String(articulo.id).padStart(4, '0') : 'N/A'}</span>
                                                    </div>
                                                    <div>
                                                        <span className="text-blue-200">Total Salidas:</span>
                                                        <span className="font-semibold ml-1">{group.items.length}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="flex flex-col items-end gap-2">
                                            <div className={`${badgeColor} text-white text-xs px-3 py-1 rounded-full font-bold flex items-center gap-1 shadow-xs`}>
                                                <span>{badgeIcon}</span>
                                                <span>{badgeText}</span>
                                            </div>
                                            <div className="text-right">
                                                <span className="text-blue-200 text-sm">Stock disponible:</span>
                                                <span className={`font-bold text-xl ml-1 ${tieneStock ? 'text-blue-200' : 'text-red-300'}`}>{stockTotal}</span>
                                                <span className="text-blue-200 text-sm"> / {stockMinimo} min</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {/* Tarjetas de registros anidadas e indentadas */}
                                <div className="ml-2 md:ml-8 space-y-4 mb-6">
                                    {group.items.map((item) => {
                                        const stockTotalReal = Number(item.historico_unidades_disponibles ?? item.inventario_oficinas?.unidades ?? 0);
                                        const stockRestante = Math.max(0, stockTotalReal - Number(item.cantidad || 0));
                                        const stockMin = Number(item.articulos?.stock_min ?? 0);
                                        const porcentajeStock = stockTotalReal > 0 ? Math.min(100, Math.round((stockRestante / stockTotalReal) * 100)) : 0;
                                        const tieneStockBajo = stockRestante < stockMin;
                                        const colorStock = tieneStockBajo ? 'text-red-500' : 'text-green-600';
                                        const colorBarra = tieneStockBajo ? 'bg-red-500' : 'bg-green-500';
                                        const isSelected = selectedIds.includes(item.id);

                                        return (
                                            <div
                                                key={item.id}
                                                className={`bg-white border ${isSelected ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-white/20'} backdrop-blur-md rounded-2xl p-6 record-card hover:scale-[1.008] transition-transform duration-200 shadow-sm`}
                                            >
                                                <div className="flex flex-col lg:flex-row items-start gap-6">
                                                    <div className="flex-shrink-0 pt-2">
                                                        <input
                                                            type="checkbox"
                                                            checked={isSelected}
                                                            onChange={() => handleSelectOne(item.id)}
                                                            className="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer"
                                                        />
                                                    </div>

                                                    <div className="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                                        <div>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Fecha de Salida</p>
                                                            <p className="font-semibold text-black">{formatDisplayDate(item.fecha_salida)}</p>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Item #</p>
                                                            <p className="font-mono text-sm">{item.articulos ? String(item.articulos.id).padStart(4, '0') : 'N/A'}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Artículo</p>
                                                            <p className="font-semibold text-black">{item.articulos ? item.articulos.articulo : 'N/A'}</p>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Unidad</p>
                                                            <p className="text-sm">{item.unidad_medidas ? item.unidad_medidas.unidad_medida : (item.articulos?.unidad_medida?.unidad_medida || 'N/A')}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Gerencia</p>
                                                            <p className="text-sm font-semibold">{item.gerencias ? item.gerencias.gerencia : 'N/A'}</p>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Área</p>
                                                            <p className="text-sm font-semibold">{item.areas ? item.areas.area : 'N/A'}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-base text-blue-900 font-semibold mt-2">Solicitante</p>
                                                            <p className="text-sm font-semibold">{item.users ? (item.users.name || item.users.username) : 'N/A'}</p>
                                                            <div className="flex items-center gap-2 mt-4">
                                                                {permissions.can_edit && (
                                                                    <button
                                                                        type="button"
                                                                        onClick={() => openEditModal(item)}
                                                                        className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-blue-900 bg-blue-100 hover:bg-blue-200 rounded-xl transition-all cursor-pointer"
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
                                                                        className="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-red-700 bg-red-100 hover:bg-red-200 rounded-xl transition-all cursor-pointer"
                                                                        title="Eliminar registro con confirmación de contraseña"
                                                                    >
                                                                        <span>🗑️</span>
                                                                        <span>Eliminar</span>
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div className="flex flex-col items-center md:items-end gap-4 w-full md:w-auto">
                                                        <div className="text-center md:text-right min-w-[140px]">
                                                            <p className="text-sm text-black font-bold mb-1">Unidades Disponibles</p>
                                                            <div className="flex items-center justify-center md:justify-end gap-2">
                                                                <span className={`text-2xl font-bold ${colorStock}`}>{stockRestante}</span>
                                                                <span className="text-xs text-blue-900 font-semibold">de {stockTotalReal}</span>
                                                            </div>
                                                            <div className="w-full bg-gray-200 rounded-full h-2 mt-2 overflow-hidden">
                                                                <div className={`${colorBarra} h-2 rounded-full transition-all`} style={{ width: `${porcentajeStock}%` }}></div>
                                                            </div>
                                                            {tieneStockBajo && (
                                                                <span className="text-xs text-red-500 mt-1 inline-block font-semibold">⚠️ Stock bajo (mín: {stockMin})</span>
                                                            )}
                                                        </div>

                                                        <div className="text-right mt-4">
                                                            <p className="text-sm text-black font-bold mb-1">
                                                                Cantidad Retirada <span style={{ fontSize: 20 }}>📦</span>
                                                            </p>
                                                            <p className="text-2xl text-center font-bold text-orange-600">{item.cantidad}</p>
                                                        </div>

                                                        {item.ajuste_inventario && (
                                                            <div className="text-right">
                                                                <p className="text-xs text-blue-900 font-semibold mb-1">Ajuste Inventario</p>
                                                                <p className="font-mono text-sm bg-gray-200 px-2 py-1 rounded inline-block">{item.ajuste_inventario}</p>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="mt-8">
                        <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
                            <span className="text-xs font-semibold text-slate-500">
                                Mostrando {pagination.from || 0} - {pagination.to || 0} de {pagination.total || 0} registros (Página {pagination.current_page} de {pagination.last_page})
                            </span>
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    disabled={pagination.current_page <= 1}
                                    onClick={() => fetchSalidas(pagination.current_page - 1)}
                                    className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-900 border border-blue-200 hover:bg-blue-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
                                >
                                    ◀ Anterior
                                </button>
                                {Array.from({ length: pagination.last_page }, (_, i) => i + 1)
                                    .filter(p => p === 1 || p === pagination.last_page || Math.abs(p - pagination.current_page) <= 2)
                                    .map((p, idx, arr) => (
                                        <React.Fragment key={p}>
                                            {idx > 0 && arr[idx - 1] !== p - 1 && (
                                                <span className="px-1 text-xs text-slate-400">...</span>
                                            )}
                                            <button
                                                type="button"
                                                onClick={() => fetchSalidas(p)}
                                                className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                                                    pagination.current_page === p
                                                        ? 'bg-blue-900 text-white shadow-sm'
                                                        : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'
                                                }`}
                                            >
                                                {p}
                                            </button>
                                        </React.Fragment>
                                    ))}
                                <button
                                    type="button"
                                    disabled={pagination.current_page >= pagination.last_page}
                                    onClick={() => fetchSalidas(pagination.current_page + 1)}
                                    className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-900 border border-blue-200 hover:bg-blue-100 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer"
                                >
                                    Siguiente ▶
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Crear / Editar Salida de Oficina (Diseño exacto de inventario_oficinas) */}
            {modalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto animate-modal-backdrop">
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-2xl max-w-3xl w-full my-8 p-6 sm:p-8 max-h-[92vh] overflow-y-auto animate-modal-expand">
                        {/* Cabecera del modal */}
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800">
                                    {modalMode === 'create' ? 'Registrar Salida de Oficina' : 'Editar Salida de Oficina'}
                                </h3>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Completa los datos para registrar un nuevo despacho de artículo en almacén'
                                        : `Actualizar información del despacho (#${String(selectedSalida?.id).padStart(4, '0')})`}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setModalOpen(false)}
                                disabled={submitting}
                                className="modal-close-btn cursor-pointer"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Fila 1: Fecha de Salida y Cantidad a Despachar */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Fecha de Salida: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        max={new Date().toISOString().split('T')[0]}
                                        value={formData.fecha_salida}
                                        onChange={(e) => {
                                            setFormData(prev => ({ ...prev, fecha_salida: e.target.value }));
                                            if (formErrors.fecha_salida) setFormErrors(prev => ({ ...prev, fecha_salida: null }));
                                        }}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.fecha_salida && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.fecha_salida}</p>}
                                </div>

                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Cantidad a Despachar: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={maxStockDisponible > 0 ? maxStockDisponible : undefined}
                                        value={formData.cantidad}
                                        onChange={(e) => {
                                            setFormData(prev => ({ ...prev, cantidad: e.target.value }));
                                            if (formErrors.cantidad) setFormErrors(prev => ({ ...prev, cantidad: null }));
                                        }}
                                        placeholder="Ej: 5"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.cantidad && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.cantidad}</p>}
                                </div>
                            </div>

                            {/* Fila 2: Artículo y Unidad de Medida */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Artículo: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.articulo_id}
                                        onChange={(e) => handleModalArticuloChange(e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione un Artículo</option>
                                        {(catalogs.articulos || []).map((art) => (
                                            <option key={art.id} value={art.id}>
                                                {String(art.id).padStart(4, '0')} - {art.articulo} ({art.stock_disponible} disponibles)
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.articulo_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.articulo_id}</p>}
                                    {currentModalArticulo && (
                                        <div className="mt-1.5 flex items-center justify-between text-2xs p-2 bg-blue-50 rounded-xl border border-blue-100">
                                            <span className="text-blue-900 font-semibold">
                                                Stock disponible: <strong className="text-blue-950 font-black">{maxStockDisponible}</strong> {currentModalArticulo.unidad_medida}
                                            </span>
                                            <span className="text-slate-500 font-medium">
                                                Mínimo: {currentModalArticulo.stock_min}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Unidad de Medida: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        disabled={true}
                                        value={currentModalArticulo?.unidad_medida || 'UNIDAD'}
                                        className="w-full px-3.5 py-2.5 bg-slate-100 border-none rounded-xl text-sm font-medium text-slate-500 cursor-not-allowed"
                                    />
                                </div>
                            </div>

                            {/* Fila 3: Gerencia Solicitante y Área de Destino (Casadas) */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Gerencia Solicitante: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.gerencia_id}
                                        onChange={(e) => handleModalGerenciaChange(e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Gerencia</option>
                                        {gerenciasOptions.map((g) => (
                                            <option key={g.id} value={g.id}>
                                                {g.label}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.gerencia_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.gerencia_id}</p>}
                                </div>

                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Área de Destino: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.area_id}
                                        onChange={(e) => handleModalAreaChange(e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">{formData.gerencia_id ? "Seleccione el Área vinculada" : "Seleccione Área"}</option>
                                        {modalAreasOptions.map((a) => (
                                            <option key={a.id} value={a.id}>
                                                {a.label}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.area_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.area_id}</p>}
                                    {formData.gerencia_id && modalAreasOptions.length > 0 && (
                                        <p className="text-[10px] text-blue-700 font-semibold mt-1 flex items-center gap-1">
                                            <span>🔗</span>
                                            <span>{modalAreasOptions.length} área(s) vinculada(s) a esta Gerencia</span>
                                        </p>
                                    )}
                                </div>
                            </div>

                            {/* Fila 4: Ajuste de Inventario / Observaciones */}
                            <div>
                                <label className="block mb-1 text-sm font-semibold text-slate-800">
                                    Ajuste de Inventario / Observaciones:
                                </label>
                                <textarea
                                    rows="3"
                                    value={formData.ajuste_inventario}
                                    onChange={(e) => setFormData(prev => ({ ...prev, ajuste_inventario: e.target.value }))}
                                    placeholder="Motivo de salida, número de oficio, o notas para el inventario..."
                                    className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 placeholder-slate-400"
                                    disabled={submitting}
                                />
                            </div>

                            {/* Botones de acción inferiores (Diseño exacto de inventario_oficinas y bienes_publicos) */}
                            <div className="flex items-center justify-between gap-4 pt-6 mt-6 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={() => setModalOpen(false)}
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-white transition-all duration-300 disabled:opacity-60 text-sm hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer shadow-md"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" className="mr-1 fill-white" viewBox="0 0 24 24">
                                        <path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path>
                                    </svg>
                                    <span>Regresar</span>
                                </button>

                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center justify-center gap-2 px-8 py-3 text-sm font-bold text-white rounded-xl shadow-md transition-all duration-300 disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    {submitting ? (
                                        <>
                                            <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" className="mr-1" viewBox="0 0 24 24" style={{ fill: 'rgba(255, 255, 255, 1)' }}>
                                                <path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path>
                                            </svg>
                                            <span>{modalMode === 'create' ? 'Guardar Registro' : 'Actualizar Registro'}</span>
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
