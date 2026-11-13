import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import Swal from 'sweetalert2';
import { downloadWithProgress } from '../../utils/downloadWithProgress';
import CameraScannerModal from '../ui/CameraScannerModal';

/**
 * Selector buscable interactivo con autocompletado y diseño Tailwind
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

    // Elevar el contenedor padre inmediato para que el dropdown flote por encima de los demás campos y fechas
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
                    {label}
                </label>
            )}
            <button
                type="button"
                onClick={() => setIsOpen(!isOpen)}
                className="select-spring-trigger w-full flex items-center justify-between gap-2 px-3 py-2 bg-blue-100/70 hover:bg-blue-100 text-slate-800 text-xs font-semibold rounded-xl border border-white/20 transition-all text-left shadow-2xs focus:ring-2 focus:ring-blue-600 focus:outline-none"
            >
                <div className="flex items-center gap-1.5 truncate">
                    <span className="text-xs opacity-70">{icon}</span>
                    <span className="truncate">
                        {selectedOption ? selectedOption.label : placeholder}
                    </span>
                </div>
                <div className="flex items-center gap-1 flex-shrink-0">
                    {value ? (
                        <span
                            onClick={(e) => {
                                e.stopPropagation();
                                onChange('');
                            }}
                            className="text-slate-400 hover:text-red-600 p-0.5 cursor-pointer font-bold text-xs"
                            title="Quitar filtro"
                        >
                            ✕
                        </span>
                    ) : null}
                    <span className={`text-[10px] text-slate-500 transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`}>▼</span>
                </div>
            </button>

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

                        {filtered.length === 0 ? (
                            <div className="px-3 py-2 text-center text-xs text-slate-400 font-medium">
                                Sin coincidencias
                            </div>
                        ) : (
                            filtered.map((opt, idx) => {
                                const isSelected = String(opt.id) === String(value);
                                return (
                                    <div
                                        key={opt.id}
                                        onClick={() => {
                                            onChange(opt.id);
                                            setIsOpen(false);
                                            setSearch('');
                                        }}
                                        style={{ animationDelay: `${Math.min((idx + 1) * 35, 300)}ms` }}
                                        className={`select-spring-item px-3 py-2 rounded-xl cursor-pointer transition-all truncate flex items-center justify-between ${
                                            isSelected
                                                ? 'bg-blue-900 text-white font-bold shadow-xs'
                                                : 'text-slate-700 hover:bg-blue-50 hover:text-blue-900'
                                        }`}
                                    >
                                        <span className="truncate">{opt.label}</span>
                                        {isSelected && <span className="ml-1 text-xs font-bold text-white">✓</span>}
                                    </div>
                                );
                            })
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

/**
 * Calcula el estado de stock y color según las reglas:
 * - 41% a 100%: Verde
 * - Menor o igual a 40%: Amarillo
 * - Límite de stock mínimo o sin stock (0): Rojo
 */
function getStockStatus(unidadesDisponibles, unidadesTotales, stockMin) {
    const disp = Number(unidadesDisponibles) || 0;
    const total = Number(unidadesTotales) || 0;
    const min = Number(stockMin) || 0;
    const percent = total > 0 ? Math.min(100, Math.max(0, Math.round((disp / total) * 100))) : 0;

    // Condición roja: sin stock (disp <= 0) o cuando ya esté al límite o por debajo del stock mínimo
    const isAtLimitOrEmpty = disp <= 0 || (min > 0 ? disp <= min : disp <= 5);

    if (isAtLimitOrEmpty) {
        return {
            color: 'red',
            textClass: 'text-red-600',
            bgClass: 'bg-red-500',
            borderClass: 'border-red-300',
            badgeBg: 'bg-red-50 text-red-700 border-red-200',
            label: disp <= 0 ? 'Sin Stock' : 'Stock Mínimo',
            percent
        };
    }

    // Condición amarilla: menor o igual al 40% (cuando esté bajo de 40%)
    if (percent <= 40) {
        return {
            color: 'yellow',
            textClass: 'text-amber-500',
            bgClass: 'bg-amber-400',
            borderClass: 'border-amber-300',
            badgeBg: 'bg-amber-50 text-amber-700 border-amber-200',
            label: 'Stock Bajo',
            percent
        };
    }

    // Condición verde: de 41% a 100%
    return {
        color: 'green',
        textClass: 'text-emerald-600',
        bgClass: 'bg-emerald-500',
        borderClass: 'border-emerald-300',
        badgeBg: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        label: 'Stock Óptimo',
        percent
    };
}

export default function InventarioOficinas() {
    const [inventario, setInventario] = useState([]);
    const [metrics, setMetrics] = useState({
        ultimasFechas: 'N/A',
        totalItemsRegistrados: 0,
        itemsEsteMes: 0,
        porcentajeItems: 0,
        stockBajo: 0,
        articulosStockBajo: [],
        articuloMasRegistros: null
    });
    const [catalogs, setCatalogs] = useState({
        proveedores: [],
        articulos: [],
        unidad_medidas: [],
        facturas: []
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
    const [filterProveedor, setFilterProveedor] = useState('');
    const [filterArticulo, setFilterArticulo] = useState('');
    const [filterUnidadMedida, setFilterUnidadMedida] = useState('');
    const [filterFactura, setFilterFactura] = useState('');
    const [tipoFecha, setTipoFecha] = useState('fecha_ingreso'); // 'fecha_ingreso' | 'fecha_creacion'
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Selección múltiple
    const [selectedIds, setSelectedIds] = useState([]);

    // Estado del modal de Crear / Editar
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedInventario, setSelectedInventario] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [formData, setFormData] = useState({
        fecha_ingreso: new Date().toISOString().split('T')[0],
        articulo_id: '',
        unidad_medida_id: '',
        proveedore_id: '',
        ubicacion_id: '',
        unidades: '',
        n_factura: ''
    });
    const [formErrors, setFormErrors] = useState({});

    // Estado del scanner de cámara
    const [cameraModalOpen, setCameraModalOpen] = useState(false);
    const [cameraModalTitle, setCameraModalTitle] = useState('Escanear con Cámara');
    const [cameraTargetField, setCameraTargetField] = useState('n_factura');

    // Opciones formateadas para los SearchableSelect
    const proveedoresOptions = useMemo(() => {
        return (catalogs.proveedores || []).map(p => ({
            id: p.id,
            label: p.proveedor
        }));
    }, [catalogs.proveedores]);

    const articulosOptions = useMemo(() => {
        return (catalogs.articulos || []).map(a => ({
            id: a.id,
            label: `${String(a.id).padStart(4, '0')} - ${a.articulo}`
        }));
    }, [catalogs.articulos]);

    const unidadMedidasOptions = useMemo(() => {
        return (catalogs.unidad_medidas || []).map(u => ({
            id: u.id,
            label: u.unidad_medida
        }));
    }, [catalogs.unidad_medidas]);

    const ubicacionesOptions = useMemo(() => {
        return (catalogs.ubicaciones || []).map(ub => ({
            id: ub.id,
            label: ub.ubicacion
        }));
    }, [catalogs.ubicaciones]);

    const facturasOptions = useMemo(() => {
        return (catalogs.facturas || []).map(f => ({
            id: f,
            label: `Factura #${f}`
        }));
    }, [catalogs.facturas]);

    // Conteo de filtros activos
    const activeFiltersCount = (filterProveedor ? 1 : 0) +
        (filterArticulo ? 1 : 0) +
        (filterUnidadMedida ? 1 : 0) +
        (filterFactura ? 1 : 0) +
        (fechaDesde || fechaHasta ? 1 : 0);

    // Cargar datos desde la API
    const fetchInventario = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterProveedor) params.append('proveedore_id', filterProveedor);
            if (filterArticulo) params.append('articulo_id', filterArticulo);
            if (filterUnidadMedida) params.append('unidad_medida_id', filterUnidadMedida);
            if (filterFactura) params.append('n_factura', filterFactura);
            if (tipoFecha) params.append('tipo_fecha', tipoFecha);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);

            const response = await fetch(`/api/administracion/inventario_oficinas?${params.toString()}`, {
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
            const paginated = resData.inventario || {};

            setInventario(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                from: paginated.from || 0,
                to: paginated.to || 0,
                per_page: paginated.per_page || 15
            });

            // Extraer las métricas calculadas en el servidor para las cajas superiores
            const met = resData.metrics || {};
            setMetrics({
                ultimasFechas: met.ultimasFechas || 'N/A',
                totalItemsRegistrados: met.totalItemsRegistrados ?? (paginated.total || 0),
                itemsEsteMes: met.itemsEsteMes ?? 0,
                porcentajeItems: met.porcentajeItems ?? 0,
                stockBajo: met.stockBajo ?? 0,
                articulosStockBajo: met.articulosStockBajo || [],
                articuloMasRegistros: met.articuloMasRegistros || null
            });

            if (resData.catalogs) {
                setCatalogs(resData.catalogs);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching inventario:', err);
            setError(err.message || 'No se pudieron cargar los datos.');
        } finally {
            setLoading(false);
        }
    }, [busqueda, filterProveedor, filterArticulo, filterUnidadMedida, filterFactura, tipoFecha, fechaDesde, fechaHasta]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchInventario(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [fetchInventario]);

    const handleClearFilters = () => {
        setBusqueda('');
        setFilterProveedor('');
        setFilterArticulo('');
        setFilterUnidadMedida('');
        setFilterFactura('');
        setFechaDesde('');
        setFechaHasta('');
    };

    // Presets rápidos de fechas
    const applyDatePreset = (preset) => {
        const today = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        const formatYMD = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

        if (preset === 'today') {
            const str = formatYMD(today);
            setFechaDesde(str);
            setFechaHasta(str);
        } else if (preset === 'week') {
            const dayOfWeek = today.getDay();
            const diff = (dayOfWeek === 0 ? -6 : 1) - dayOfWeek;
            const monday = new Date(today);
            monday.setDate(today.getDate() + diff);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            setFechaDesde(formatYMD(monday));
            setFechaHasta(formatYMD(sunday));
        } else if (preset === 'month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            setFechaDesde(formatYMD(firstDay));
            setFechaHasta(formatYMD(lastDay));
        } else if (preset === 'year') {
            const firstDay = new Date(today.getFullYear(), 0, 1);
            const lastDay = new Date(today.getFullYear(), 11, 31);
            setFechaDesde(formatYMD(firstDay));
            setFechaHasta(formatYMD(lastDay));
        } else if (preset === 'clear') {
            setFechaDesde('');
            setFechaHasta('');
        }
    };

    const toggleSelect = (id) => {
        setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
    };

    // Handlers para el modal de Crear y Editar
    const handleOpenCreate = () => {
        const defaultUbicacion = (catalogs.ubicaciones && catalogs.ubicaciones.length > 0)
            ? String(catalogs.ubicaciones[0].id)
            : '1';

        setFormData({
            fecha_ingreso: new Date().toISOString().split('T')[0],
            articulo_id: '',
            unidad_medida_id: '',
            proveedore_id: '',
            ubicacion_id: defaultUbicacion,
            unidades: '',
            n_factura: ''
        });
        setFormErrors({});
        setSelectedInventario(null);
        setModalMode('create');
        setModalOpen(true);
    };

    const handleOpenEdit = (item) => {
        setSelectedInventario(item);
        const defaultUbicacion = (catalogs.ubicaciones && catalogs.ubicaciones.length > 0)
            ? String(catalogs.ubicaciones[0].id)
            : '1';

        setFormData({
            fecha_ingreso: item.fecha_ingreso ? item.fecha_ingreso.split('T')[0] : new Date().toISOString().split('T')[0],
            articulo_id: item.articulo_id ? String(item.articulo_id) : '',
            unidad_medida_id: item.unidad_medida_id ? String(item.unidad_medida_id) : '',
            proveedore_id: item.proveedore_id ? String(item.proveedore_id) : '',
            ubicacion_id: item.ubicacion_id ? String(item.ubicacion_id) : defaultUbicacion,
            unidades: item.unidades !== undefined && item.unidades !== null ? String(item.unidades) : '',
            n_factura: item.n_factura || ''
        });
        setFormErrors({});
        setModalMode('edit');
        setModalOpen(true);
    };

    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedInventario(null);
        setFormErrors({});
    };

    const handleFormChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
        if (formErrors[field]) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next[field];
                return next;
            });
        }
    };

    const handleArticuloChange = (artId) => {
        const selectedArt = (catalogs.articulos || []).find(a => String(a.id) === String(artId));
        setFormData(prev => ({
            ...prev,
            articulo_id: artId,
            unidad_medida_id: (selectedArt && selectedArt.unidad_medida_id)
                ? String(selectedArt.unidad_medida_id)
                : prev.unidad_medida_id
        }));
        if (formErrors.articulo_id) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next.articulo_id;
                return next;
            });
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setFormErrors({});

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/administracion/inventario_oficinas'
                : `/administracion/inventario_oficinas/${selectedInventario.id}`;
            const method = modalMode === 'create' ? 'POST' : 'PUT';

            const defaultUbicacion = (catalogs.ubicaciones && catalogs.ubicaciones.length > 0)
                ? catalogs.ubicaciones[0].id
                : 1;

            const payload = {
                fecha_ingreso: formData.fecha_ingreso,
                articulo_id: formData.articulo_id,
                unidad_medida_id: formData.unidad_medida_id,
                proveedore_id: formData.proveedore_id,
                ubicacion_id: formData.ubicacion_id || defaultUbicacion,
                unidades: formData.unidades,
                n_factura: formData.n_factura
            };

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campos requeridos',
                        text: 'Por favor, revise los campos marcados en el formulario.',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }
                throw new Error(data.message || 'Error al procesar la solicitud');
            }

            await Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Registrado!' : '¡Actualizado!',
                text: data.message || (modalMode === 'create' ? 'Registro creado correctamente' : 'Registro actualizado correctamente'),
                timer: 2000,
                showConfirmButton: false
            });

            setModalOpen(false);
            fetchInventario(pagination.current_page);
        } catch (err) {
            console.error('Error submitting form:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Ocurrió un error inesperado al guardar.',
                confirmButtonColor: '#1e3a8a'
            });
        } finally {
            setSubmitting(false);
        }
    };

    const openCameraScanner = (field, title) => {
        setCameraTargetField(field);
        setCameraModalTitle(title || 'Escanear con Cámara');
        setCameraModalOpen(true);
    };

    const handleCameraScanSuccess = (decodedText, field) => {
        const target = field || cameraTargetField;
        if (target) {
            setFormData(prev => ({ ...prev, [target]: decodedText }));
            if (formErrors[target]) {
                setFormErrors(prev => {
                    const next = { ...prev };
                    delete next[target];
                    return next;
                });
            }
        }
        Swal.fire({
            icon: 'success',
            title: '¡Código detectado!',
            text: `Se leyó correctamente: ${decodedText}`,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    };

    const handleDeleteSingle = async (item) => {
        const { value: password } = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el registro de inventario con Factura #${item.n_factura || 'S/N'}. Ingresa tu contraseña para confirmar:`,
            icon: 'warning',
            input: 'password',
            inputPlaceholder: 'Ingresa tu contraseña...',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar tu contraseña';
                }
            }
        });

        if (!password) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/inventario_oficinas/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password })
            });

            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Error al eliminar el registro');
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: json.message || 'Registro eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
            fetchInventario(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el registro.'
            });
        }
    };

    const handleDeleteSelected = async () => {
        if (selectedIds.length === 0) return;

        const { value: password } = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminarán ${selectedIds.length} registro(s) seleccionados. Ingresa tu contraseña para confirmar:`,
            icon: 'warning',
            input: 'password',
            inputPlaceholder: 'Contraseña de confirmación',
            inputAttributes: { autocapitalize: 'off', autocorrect: 'off' },
            showCancelButton: true,
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            confirmButtonText: 'Sí, eliminar seleccionados',
            cancelButtonText: 'Cancelar',
            preConfirm: (val) => {
                if (!val) {
                    Swal.showValidationMessage('Debes ingresar tu contraseña');
                    return false;
                }
                return val;
            }
        });

        if (!password) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/administracion/inventario_oficinas/destroy-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    password
                })
            });

            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Error al eliminar registros seleccionados');
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminados',
                text: json.message || `Se eliminaron ${selectedIds.length} registro(s) correctamente.`,
                timer: 2000,
                showConfirmButton: false
            });
            setSelectedIds([]);
            fetchInventario(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudieron eliminar los registros.'
            });
        }
    };

    const handleDeleteAll = async () => {
        const { value: password } = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se eliminarán TODOS los registros del inventario de oficinas. Esta acción no se puede deshacer. Ingresa tu contraseña:',
            icon: 'warning',
            input: 'password',
            inputPlaceholder: 'Contraseña de confirmación',
            inputAttributes: { autocapitalize: 'off', autocorrect: 'off' },
            showCancelButton: true,
            confirmButtonColor: '#F52727',
            cancelButtonColor: '#2780F5',
            confirmButtonText: 'Sí, eliminar todo',
            cancelButtonText: 'Cancelar',
            preConfirm: (val) => {
                if (!val) {
                    Swal.showValidationMessage('Debes ingresar tu contraseña');
                    return false;
                }
                return val;
            }
        });

        if (!password) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/administracion/inventario_oficinas/destroy-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password })
            });

            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(json.message || 'Error al eliminar todos los registros');
            }

            Swal.fire({
                icon: 'success',
                title: 'Inventario Vaciado',
                text: json.message || 'Se eliminaron todos los registros correctamente.',
                timer: 2000,
                showConfirmButton: false
            });
            setSelectedIds([]);
            fetchInventario(1);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudieron eliminar todos los registros.'
            });
        }
    };

    // Función de exportación con modal de progreso en tiempo real y soporte para Todos o Seleccionados
    const handleExport = (format, scope = 'all') => {
        if (!format) return;

        const isSelected = scope === 'selected';
        if (isSelected && selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Por favor, selecciona al menos un registro mediante la casilla de verificación antes de exportar.',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }

        const isExcel = format === 'excel';
        const filename = isExcel
            ? (isSelected ? `inventario_oficinas_seleccionados_${selectedIds.length}.xlsx` : 'inventario_oficinas.xlsx')
            : (isSelected ? `inventario_oficinas_seleccionados_${selectedIds.length}.pdf` : 'inventario_oficinas.pdf');
        const url = isExcel
            ? '/administracion/inventario_oficinas/export/excel'
            : '/administracion/inventario_oficinas/export/pdf';

        const params = new URLSearchParams();
        if (isSelected) {
            params.append('selected_ids', selectedIds.join(','));
        } else {
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterProveedor) params.append('proveedore_id', filterProveedor);
            if (filterArticulo) params.append('articulo_id', filterArticulo);
            if (filterUnidadMedida) params.append('unidad_medida_id', filterUnidadMedida);
            if (filterFactura) params.append('n_factura', filterFactura);
            if (tipoFecha) params.append('tipo_fecha', tipoFecha);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        }

        const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;

        downloadWithProgress(fullUrl, {
            filename,
            type: isExcel ? 'excel' : 'pdf',
            title: isSelected
                ? `Exportando ${selectedIds.length} Registro(s) Seleccionado(s)`
                : (isExcel ? 'Exportando Inventario a Excel' : 'Exportando Inventario a PDF')
        });
    };

    // Agrupar items por artículo (como en la vista original)
    const groupedItems = inventario.reduce((acc, curr) => {
        const artId = curr.articulo_id || 'sin_articulo';
        if (!acc[artId]) {
            acc[artId] = {
                articulo: curr.articulos || { articulo: 'Artículo Desconocido' },
                items: []
            };
        }
        acc[artId].items.push(curr);
        return acc;
    }, {});

    return (
        <div className="w-full">
            {/* Tarjetas Superiores de Estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {/* Última Fecha de Ingreso */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500 text-2xl">📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.ultimasFechas}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Última Fecha de Ingreso</p>
                    </div>

                    {/* Cantidad de Ingresos */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500 text-2xl">📦</span>
                            </div>
                            <span className="text-lg px-2.5 py-1 rounded-full bg-green-500/20 text-green-600 font-bold">
                                {metrics.totalItemsRegistrados}
                            </span>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalItemsRegistrados}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Cantidad de ingresos</p>
                    </div>

                    {/* Stock Bajo */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                                <span className="text-red-500 text-2xl">⚠️</span>
                            </div>
                            <span className="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-600 font-bold">
                                Alerta <b className="text-base">{metrics.stockBajo}</b>
                            </span>
                        </div>
                        <p className="text-sm text-blue-900 font-semibold mb-2">Stock Bajo</p>
                        <div className="mt-2 text-xs max-h-24 overflow-y-auto space-y-1">
                            {metrics.articulosStockBajo && metrics.articulosStockBajo.length > 0 ? (
                                metrics.articulosStockBajo.map((art, idx) => (
                                    <div key={idx} className="flex rounded-md items-center justify-between gap-2 p-1.5 bg-red-50 text-red-900">
                                        <span className="font-semibold truncate">{art.articulos?.articulo || 'Artículo'}</span>
                                        <span className="text-blue-900 font-bold">({art.total_disponibles})</span>
                                    </div>
                                ))
                            ) : (
                                <span className="text-blue-900 font-semibold text-xs">Sin artículos con stock bajo</span>
                            )}
                        </div>
                    </div>

                    {/* Artículo Más Registrado */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                                <span className="text-purple-500 text-2xl">🏆</span>
                            </div>
                            <span className="text-xs px-2 py-1 rounded-full bg-purple-500/20 text-purple-600 font-bold">
                                Top <b className="text-base">{metrics.articuloMasRegistros?.total_registros || 0}</b>
                            </span>
                        </div>
                        <p className="text-sm text-blue-900 font-semibold">Artículo Más Registrado</p>
                        <h4 className="text-lg font-bold text-slate-800 mt-1 truncate">
                            {metrics.articuloMasRegistros?.articulos?.articulo || 'N/A'}
                        </h4>
                    </div>
                </div>
            </div>

            {/* Barra de Acciones y Filtros */}
            <div className="px-6 mb-6 relative z-30">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm relative z-30 overflow-visible">
                    <div className="flex flex-col gap-4">
                        {/* Fila superior: Buscador a la izquierda + Exportar y Acciones a la derecha */}
                        <div className="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                            {/* Buscador en tiempo real de los registros existentes */}
                            <div className="relative flex-1 min-w-[260px] max-w-md">
                                <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    🔍
                                </span>
                                <input
                                    type="text"
                                    value={busqueda}
                                    onChange={(e) => setBusqueda(e.target.value)}
                                    placeholder="Buscar por artículo, proveedor, factura..."
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

                            {/* Exportar (Todos o Seleccionados), Eliminar Seleccionados, Eliminar Todo y Nuevo Registro */}
                            <div className="flex flex-wrap items-center gap-3">
                                <select
                                    value=""
                                    onChange={(e) => {
                                        e.preventDefault();
                                        if (e.target.value) {
                                            const [fmt, scp] = e.target.value.split(':');
                                            handleExport(fmt, scp);
                                        }
                                    }}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs"
                                >
                                    <option value="" disabled>📤 Exportar...</option>
                                    <optgroup label="Exportar Todos (con filtros actuales)">
                                        <option value="excel:all">📊 Excel - Todos los registros</option>
                                        <option value="pdf:all">📄 PDF - Todos los registros</option>
                                    </optgroup>
                                    <optgroup label={`Exportar Seleccionados (${selectedIds.length})`}>
                                        <option value="excel:selected" disabled={selectedIds.length === 0}>
                                            📊 Excel - Solo seleccionados {selectedIds.length > 0 ? `(${selectedIds.length})` : '(0)'}
                                        </option>
                                        <option value="pdf:selected" disabled={selectedIds.length === 0}>
                                            📄 PDF - Solo seleccionados {selectedIds.length > 0 ? `(${selectedIds.length})` : '(0)'}
                                        </option>
                                    </optgroup>
                                </select>

                                {permissions.can_destroy && (
                                    <>
                                        <button
                                            type="button"
                                            onClick={handleDeleteSelected}
                                            disabled={selectedIds.length === 0}
                                            className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-600 text-white hover:bg-red-700 active:translate-y-0.5 text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-xs"
                                        >
                                            <span>🗑️</span>
                                            <span>Eliminar Seleccionados {selectedIds.length > 0 ? `(${selectedIds.length})` : ''}</span>
                                        </button>

                                        <button
                                            type="button"
                                            onClick={handleDeleteAll}
                                            className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-red-800 text-white hover:bg-red-900 active:translate-y-0.5 text-sm shadow-xs"
                                        >
                                            <span>⚠️</span>
                                            <span>Eliminar Todo</span>
                                        </button>
                                    </>
                                )}

                                {permissions.can_create && (
                                    <button
                                        type="button"
                                        onClick={handleOpenCreate}
                                        className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm shadow-xs cursor-pointer"
                                    >
                                        <span>➕</span>
                                        <span>Nuevo Registro</span>
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Fila de control: Botón Filtros Avanzados con badge + Botón Limpiar si hay activos */}
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                    className="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white border border-blue-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 shadow-xs transition-all duration-300"
                                >
                                    <span>⚡ Filtros Avanzados</span>
                                    {activeFiltersCount > 0 && (
                                        <span className="w-5 h-5 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                            {activeFiltersCount}
                                        </span>
                                    )}
                                    <svg
                                        className={`w-3.5 h-3.5 transition-transform duration-300 transform ${showAdvancedFilters ? 'rotate-180' : 'rotate-0'}`}
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {activeFiltersCount > 0 && (
                                    <button
                                        type="button"
                                        onClick={handleClearFilters}
                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors"
                                    >
                                        <span>✕</span>
                                        <span>Limpiar Filtros ({activeFiltersCount})</span>
                                    </button>
                                )}
                            </div>

                            {/* Badges de filtros activos en horizontal */}
                            <div className="flex flex-wrap items-center gap-2 text-xs">
                                {filterProveedor && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Proveedor: {proveedoresOptions.find(p => String(p.id) === String(filterProveedor))?.label || filterProveedor}</span>
                                        <button type="button" onClick={() => setFilterProveedor('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterArticulo && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Artículo: {articulosOptions.find(a => String(a.id) === String(filterArticulo))?.label || filterArticulo}</span>
                                        <button type="button" onClick={() => setFilterArticulo('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterUnidadMedida && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Unidad: {unidadMedidasOptions.find(u => String(u.id) === String(filterUnidadMedida))?.label || filterUnidadMedida}</span>
                                        <button type="button" onClick={() => setFilterUnidadMedida('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterFactura && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Factura: #{filterFactura}</span>
                                        <button type="button" onClick={() => setFilterFactura('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {(fechaDesde || fechaHasta) && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Fechas ({tipoFecha === 'fecha_creacion' ? 'Ingreso' : 'Factura'}): {fechaDesde || '...'} a {fechaHasta || '...'}</span>
                                        <button type="button" onClick={() => { setFechaDesde(''); setFechaHasta(''); }} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Panel desplegable animado tipo acordeón con CSS Grid auto-height */}
                        <div
                            className={`grid transition-all duration-500 ease-in-out ${
                                showAdvancedFilters
                                    ? 'grid-rows-[1fr] opacity-100 mt-3 visible'
                                    : 'grid-rows-[0fr] opacity-0 invisible mt-0 pointer-events-none'
                            }`}
                        >
                            <div className={`${showAdvancedFilters ? 'overflow-visible' : 'overflow-hidden'} transition-all duration-300`}>
                                <div className="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md relative z-40 overflow-visible">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-20 overflow-visible">
                                        {/* Proveedor */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="🏢 Proveedor"
                                                options={proveedoresOptions}
                                                value={filterProveedor}
                                                onChange={setFilterProveedor}
                                                placeholder="Todos los proveedores"
                                                allLabel="Todos los proveedores"
                                                icon="🏢"
                                            />
                                        </div>

                                        {/* Artículo */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="📦 Artículo"
                                                options={articulosOptions}
                                                value={filterArticulo}
                                                onChange={setFilterArticulo}
                                                placeholder="Todos los artículos"
                                                allLabel="Todos los artículos"
                                                icon="📦"
                                            />
                                        </div>

                                        {/* Unidad de Medida */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="📏 Unidad de Medida"
                                                options={unidadMedidasOptions}
                                                value={filterUnidadMedida}
                                                onChange={setFilterUnidadMedida}
                                                placeholder="Todas las unidades"
                                                allLabel="Todas las unidades"
                                                icon="📏"
                                            />
                                        </div>

                                        {/* Factura */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="🧾 Número de Factura"
                                                options={facturasOptions}
                                                value={filterFactura}
                                                onChange={setFilterFactura}
                                                placeholder="Todas las facturas"
                                                allLabel="Todas las facturas"
                                                icon="🧾"
                                            />
                                        </div>
                                    </div>

                                    {/* Sección de Fechas con Presets */}
                                    <div className={`mt-5 pt-4 border-t border-blue-100 relative z-10 transition-opacity duration-300 ${showAdvancedFilters ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                        <div className="flex flex-wrap items-center justify-between gap-3 mb-2.5">
                                            <div className="flex items-center gap-3">
                                                <span className="text-xs font-bold text-blue-950">📅 Filtrar por Rango de Fechas:</span>
                                                <div className="inline-flex rounded-xl p-0.5 bg-blue-100/80 text-xs">
                                                    <button
                                                        type="button"
                                                        onClick={() => setTipoFecha('fecha_ingreso')}
                                                        className={`px-2.5 py-1 rounded-lg font-semibold transition-all ${
                                                            tipoFecha === 'fecha_ingreso'
                                                                ? 'bg-blue-900 text-white shadow-xs'
                                                                : 'text-slate-600 hover:text-blue-900'
                                                        }`}
                                                    >
                                                        Fecha Factura
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setTipoFecha('fecha_creacion')}
                                                        className={`px-2.5 py-1 rounded-lg font-semibold transition-all ${
                                                            tipoFecha === 'fecha_creacion'
                                                                ? 'bg-blue-900 text-white shadow-xs'
                                                                : 'text-slate-600 hover:text-blue-900'
                                                        }`}
                                                    >
                                                        Fecha Ingreso Sistema
                                                    </button>
                                                </div>
                                            </div>

                                            {/* Presets Rápidos */}
                                            <div className="flex items-center gap-1.5 flex-wrap">
                                                <span className="text-[11px] font-semibold text-slate-500">Accesos rápidos:</span>
                                                <button
                                                    type="button"
                                                    onClick={() => applyDatePreset('today')}
                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-900 shadow-2xs"
                                                >
                                                    Hoy
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => applyDatePreset('week')}
                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-900 shadow-2xs"
                                                >
                                                    Esta Semana
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => applyDatePreset('month')}
                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-900 shadow-2xs"
                                                >
                                                    Este Mes
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => applyDatePreset('year')}
                                                    className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-900 shadow-2xs"
                                                >
                                                    Este Año
                                                </button>
                                                {(fechaDesde || fechaHasta) && (
                                                    <button
                                                        type="button"
                                                        onClick={() => applyDatePreset('clear')}
                                                        className="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100"
                                                    >
                                                        Quitar fechas
                                                    </button>
                                                )}
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-600 mb-1">Desde:</label>
                                                <input
                                                    type="date"
                                                    value={fechaDesde}
                                                    onChange={(e) => setFechaDesde(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-xs font-semibold text-slate-600 mb-1">Hasta:</label>
                                                <input
                                                    type="date"
                                                    value={fechaHasta}
                                                    onChange={(e) => setFechaHasta(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de Inventario Agrupado (Diseño Original Exacto) */}
            <div className="px-6 mb-8 relative z-10">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white/60 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando inventario de oficina...</p>
                    </div>
                ) : error ? (
                    <div className="p-6 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchInventario(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : Object.keys(groupedItems).length === 0 ? (
                    <div className="p-12 text-center bg-white/70 backdrop-blur-md rounded-2xl border border-blue-100">
                        <div className="w-16 h-16 mx-auto mb-3 text-4xl">📦</div>
                        <h3 className="text-base font-bold text-slate-800">No hay registros de inventario</h3>
                        <p className="text-xs text-slate-500 mt-1">No se encontraron artículos con los filtros aplicados.</p>
                    </div>
                ) : (
                    Object.entries(groupedItems).map(([artId, group]) => {
                        const totalDisp = group.items.reduce((acc, it) => acc + (Number(it.unidades_disponibles) || 0), 0);
                        const isLow = group.articulo?.stock_min && totalDisp < group.articulo.stock_min;

                        return (
                            <div key={artId} className="mb-6">
                                {/* Banner de Cabecera del Artículo */}
                                <div className={`bg-gradient-to-r ${isLow ? 'from-red-900 to-red-800' : 'from-indigo-900 to-blue-800'} text-white rounded-2xl p-4 shadow-lg relative mb-2`}>
                                    {isLow && (
                                        <div className="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                            ⚠️ Stock Bajo
                                        </div>
                                    )}
                                    <div className="flex items-center gap-4">
                                        <div className="w-16 h-16 rounded-xl bg-white/20 flex items-center justify-center text-3xl">
                                            📦
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-bold">{group.articulo?.articulo || 'Artículo Desconocido'}</h3>
                                            <p className="text-blue-100 text-xs">
                                                Registros Agrupados: <span className="font-semibold">{group.items.length}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {/* Filas / Tarjetas de cada registro dentro del grupo */}
                                {group.items.map((singleItem) => {
                                    // Cálculo de estado y color según requerimientos:
                                    // 41% a 100%: Verde
                                    // <= 40%: Amarillo
                                    // Límite de stock min o 0 stock: Rojo
                                    const status = getStockStatus(
                                        singleItem.unidades_disponibles,
                                        singleItem.unidades,
                                        group.articulo?.stock_min
                                    );

                                    return (
                                        <div
                                            key={singleItem.id}
                                            className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform duration-200 ml-4 sm:ml-8 mb-4 shadow-sm"
                                        >
                                            <div className="flex flex-col md:flex-row items-start gap-4 md:gap-6">
                                                {permissions.can_destroy && (
                                                    <div className="flex-shrink-0 pt-2">
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedIds.includes(singleItem.id)}
                                                            onChange={() => toggleSelect(singleItem.id)}
                                                            className="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer"
                                                        />
                                                    </div>
                                                )}

                                                <div className="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Última Fecha de Factura</p>
                                                        <p className="font-semibold text-slate-800 text-sm">
                                                            {singleItem.fecha_ingreso
                                                                ? singleItem.fecha_ingreso.split('T')[0].split('-').reverse().join('/')
                                                                : 'N/A'}
                        </p>
                                                        <p className="text-xs text-blue-900 font-semibold mt-2">Item #</p>
                                                        <p className="font-mono text-sm font-bold text-blue-950">
                                                            {String(singleItem.articulo_id || 0).padStart(4, '0')}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Artículo</p>
                                                        <p className="text-sm font-bold text-slate-900 truncate">
                                                            {group.articulo?.articulo || 'N/A'}
                                                        </p>
                                                        <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad de Medida</p>
                                                        <p className="text-xs font-medium text-slate-700">
                                                            {singleItem.unidad_medidas ? singleItem.unidad_medidas.unidad_medida : 'N/A'}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p className="text-xs text-blue-900 font-semibold mb-1">Proveedor</p>
                                                        <p className="text-xs font-semibold text-slate-800">
                                                            {singleItem.proveedores ? singleItem.proveedores.proveedor : 'N/A'}
                                                        </p>
                                                    </div>

                                                    <div>
                                                        {/* Progreso de Unidades Registradas con semáforo de colores */}
                                                        <div className="text-left md:text-right">
                                                            <div className="flex items-center justify-start md:justify-end gap-2 mb-1">
                                                                <p className="text-xs text-black font-bold">Unidades Registradas</p>
                                                                <span className={`text-[10px] font-bold px-1.5 py-0.5 rounded-md border ${status.badgeBg}`}>
                                                                    {status.percent}% ({status.label})
                                                                </span>
                                                            </div>
                                                            <div className="flex items-center gap-2 justify-start md:justify-end">
                                                                <span className={`text-2xl font-bold ${status.textClass}`}>
                                                                    {singleItem.unidades_disponibles}
                                                                </span>
                                                                <span className="text-xs text-blue-900 font-semibold">
                                                                    de {singleItem.unidades}
                                                                </span>
                                                            </div>
                                                            <div className="w-full bg-slate-200 rounded-full h-2 mt-2 overflow-hidden">
                                                                <div
                                                                    className={`${status.bgClass} h-2 rounded-full transition-all duration-300`}
                                                                    style={{ width: `${status.percent}%` }}
                                                                ></div>
                                                            </div>
                                                        </div>

                                                        <div className="text-left md:text-right mt-2">
                                                            <p className="text-xs text-blue-900 font-semibold mb-1">Nº de Factura</p>
                                                            <span className="text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded font-mono">
                                                                {singleItem.n_factura || 'S/N'}
                                                            </span>
                                                        </div>

                                                        {/* Botones de acción */}
                                                        <div className="flex flex-wrap justify-start md:justify-end gap-2 mt-3">
                                                            {permissions.can_edit && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleOpenEdit(singleItem)}
                                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-semibold bg-green-600 text-white hover:bg-green-700 text-xs transition-colors shadow-xs cursor-pointer"
                                                                >
                                                                    <span>✏️</span>
                                                                    <span>Editar</span>
                                                                </button>
                                                            )}

                                                            {permissions.can_destroy && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() => handleDeleteSingle(singleItem)}
                                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-semibold bg-red-600 text-white hover:bg-red-700 text-xs transition-colors shadow-xs"
                                                                >
                                                                    <span>🗑️</span>
                                                                    <span>Eliminar</span>
                                                                </button>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        );
                    })
                )}
            </div>

            {/* Paginación */}
            {pagination.total > pagination.per_page && (
                <div className="px-6 mb-8">
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
                        <div className="text-xs text-slate-600 font-medium">
                            Mostrando <span className="font-bold">{pagination.from || 0}</span> a <span className="font-bold">{pagination.to || 0}</span> de <span className="font-bold">{pagination.total}</span> registros
                        </div>
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1}
                                onClick={() => fetchInventario(pagination.current_page - 1)}
                                className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-100 text-slate-700 hover:bg-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-2">
                                Página {pagination.current_page} de {pagination.last_page}
                            </span>
                            <button
                                type="button"
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => fetchInventario(pagination.current_page + 1)}
                                className="px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-100 text-slate-700 hover:bg-blue-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Modal de Crear / Editar Inventario de Oficina (Diseño moderno con degradado corporativo y SearchableSelect) */}
            {modalOpen && (
                <div
                    className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate-fadeIn"
                    onClick={(e) => {
                        if (e.target === e.currentTarget && !submitting) {
                            handleCloseModal();
                        }
                    }}
                >
                    <div
                        className="modal-content animate-modal-expand bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[92vh] overflow-y-auto"
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
                                    {modalMode === 'create' ? 'Registrar Ingreso de Inventario' : 'Editar Inventario de Oficina'}
                                </h3>
                                <p className="text-xs text-blue-100 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Completa los datos para registrar un nuevo ingreso de artículo en inventario'
                                        : `Actualizando factura #${formData.n_factura || 'S/N'}`}
                                </p>
                            </div>
                            <button
                                type="button"
                                disabled={submitting}
                                onClick={handleCloseModal}
                                className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white font-bold grid place-items-center text-sm transition-all cursor-pointer"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="p-5 sm:p-6 space-y-4">
                            {/* Fila 1: Fecha de Factura y Número de Factura */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📅 Fecha de Factura / Ingreso <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={formData.fecha_ingreso}
                                        onChange={(e) => handleFormChange('fecha_ingreso', e.target.value)}
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.fecha_ingreso ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.fecha_ingreso && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.fecha_ingreso[0] || formErrors.fecha_ingreso}</p>
                                    )}
                                </div>

                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <label className="block text-xs font-bold text-blue-900">
                                            📄 Número de Factura <span className="text-red-500">*</span>
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => openCameraScanner('n_factura', 'Escanear N° de Factura con Cámara')}
                                            className="text-[10px] font-bold text-blue-900 hover:text-blue-700 bg-blue-100 hover:bg-blue-200 border border-blue-200 px-2 py-0.5 rounded-lg flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Escanear número de factura con la cámara"
                                        >
                                            <span>📷</span>
                                            <span>Escanear</span>
                                        </button>
                                    </div>
                                    <input
                                        type="text"
                                        value={formData.n_factura}
                                        onChange={(e) => handleFormChange('n_factura', e.target.value)}
                                        placeholder="Ej: F001-123456"
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono transition-all ${
                                            formErrors.n_factura ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.n_factura && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.n_factura[0] || formErrors.n_factura}</p>
                                    )}
                                </div>
                            </div>

                            {/* Fila 2: Artículo y Unidad de Medida */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📦 Artículo <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.articulo_id}
                                        onChange={(e) => handleArticuloChange(e.target.value)}
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.articulo_id ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione un Artículo</option>
                                        {(catalogs.articulos || []).map((art) => (
                                            <option key={art.id} value={art.id}>
                                                {String(art.id).padStart(4, '0')} - {art.articulo}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.articulo_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.articulo_id[0] || formErrors.articulo_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📏 Unidad de Medida <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.unidad_medida_id}
                                        onChange={(e) => handleFormChange('unidad_medida_id', e.target.value)}
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.unidad_medida_id ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Unidad de Medida</option>
                                        {(catalogs.unidad_medidas || []).map((u) => (
                                            <option key={u.id} value={u.id}>
                                                {u.unidad_medida}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.unidad_medida_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.unidad_medida_id[0] || formErrors.unidad_medida_id}</p>
                                    )}
                                </div>
                            </div>

                            {/* Fila 3: Proveedor y Ubicación */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        🏭 Proveedor <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.proveedore_id}
                                        onChange={(e) => handleFormChange('proveedore_id', e.target.value)}
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.proveedore_id ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Proveedor</option>
                                        {(catalogs.proveedores || []).map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.proveedor}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.proveedore_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.proveedore_id[0] || formErrors.proveedore_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📍 Ubicación <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.ubicacion_id}
                                        onChange={(e) => handleFormChange('ubicacion_id', e.target.value)}
                                        className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.ubicacion_id ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Ubicación</option>
                                        {(catalogs.ubicaciones || []).map((ub) => (
                                            <option key={ub.id} value={ub.id}>
                                                {ub.ubicacion}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.ubicacion_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.ubicacion_id[0] || formErrors.ubicacion_id}</p>
                                    )}
                                </div>
                            </div>

                            {/* Fila 4: Cantidad / Unidades */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    🔢 Cantidad / Unidades <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    value={formData.unidades}
                                    onChange={(e) => handleFormChange('unidades', e.target.value)}
                                    placeholder="Ej: 50"
                                    className={`w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                        formErrors.unidades ? 'border-red-400 bg-red-50' : 'border-white/20'
                                    }`}
                                    required
                                    disabled={submitting}
                                />
                                {formErrors.unidades && (
                                    <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.unidades[0] || formErrors.unidades}</p>
                                )}
                            </div>

                            {/* Botones de acción del Modal */}
                            <div className="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    disabled={submitting}
                                    onClick={handleCloseModal}
                                    className="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer"
                                >
                                    Regresar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn-save inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer"
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
                                            <span>{modalMode === 'create' ? 'Guardar Ingreso' : 'Actualizar Registro'}</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Modal de Escaneo en Vivo con Cámara */}
            <CameraScannerModal
                isOpen={cameraModalOpen}
                onClose={() => setCameraModalOpen(false)}
                onScan={handleCameraScanSuccess}
                title={cameraModalTitle}
                subtitle="Apunta la cámara al código de barras o código QR"
                allowedFields={['n_factura']}
                defaultTargetField={cameraTargetField}
            />
        </div>
    );
}
