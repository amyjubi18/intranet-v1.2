import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import { createPortal } from 'react-dom';
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

export default function SalidaAlmacenManagement() {
    const [salidas, setSalidas] = useState([]);
    const [catalogs, setCatalogs] = useState({
        cartuchos: [],
        colores: [],
        impresoras: [],
        fabricantes: [],
        gerencias: [],
        areas: [],
        almacenamientos: [],
        distribuciones: []
    });
    const [metrics, setMetrics] = useState({
        totalSalidas: 0,
        totalSalidasRegistradas: 0,
        salidasEsteMes: 0,
        totalItemsSalidos: 0,
        salidasBajoStock: 0,
        ultimasFechasSalida: 'N/A'
    });
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Selección múltiple
    const [selectedIds, setSelectedIds] = useState([]);

    // Filtros
    const [search, setSearch] = useState('');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [filterCartucho, setFilterCartucho] = useState('');
    const [filterColore, setFilterColore] = useState('');
    const [filterImpresora, setFilterImpresora] = useState('');
    const [filterFabricante, setFilterFabricante] = useState('');
    const [filterGerencia, setFilterGerencia] = useState('');
    const [filterArea, setFilterArea] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');
    const [fechaIngresoDesde, setFechaIngresoDesde] = useState('');
    const [fechaIngresoHasta, setFechaIngresoHasta] = useState('');

    // Modal Crear y Editar
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedSalida, setSelectedSalida] = useState(null);
    const [formData, setFormData] = useState({
        fecha_salida: new Date().toISOString().split('T')[0],
        almacenamiento_impresora_id: '',
        distribucion_almacenamiento_impresora_id: '',
        cantidad: 1,
        ajuste_inventario: ''
    });
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Bloquear scroll al abrir modal
    useEffect(() => {
        if (modalOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [modalOpen]);

    // Conteo de filtros activos
    const activeFiltersCount = useMemo(() => {
        let count = 0;
        if (filterCartucho) count++;
        if (filterColore) count++;
        if (filterImpresora) count++;
        if (filterFabricante) count++;
        if (filterGerencia) count++;
        if (filterArea) count++;
        if (fechaDesde || fechaHasta) count++;
        if (fechaIngresoDesde || fechaIngresoHasta) count++;
        return count;
    }, [filterCartucho, filterColore, filterImpresora, filterFabricante, filterGerencia, filterArea, fechaDesde, fechaHasta, fechaIngresoDesde, fechaIngresoHasta]);

    // Opciones dinámicas de áreas y gerencias casadas en filtros
    const filterAreasOptions = useMemo(() => {
        let list = catalogs.areas || [];
        if (filterGerencia) {
            const currentGer = (catalogs.gerencias || []).find(g => String(g.id) === String(filterGerencia));
            if (currentGer && currentGer.area_id) {
                list = list.filter(a => String(a.id) === String(currentGer.area_id));
            }
        }
        return list.map(a => ({ id: a.id, label: a.area })).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.areas, catalogs.gerencias, filterGerencia]);

    const filterGerenciasOptions = useMemo(() => {
        let list = catalogs.gerencias || [];
        if (filterArea) {
            list = list.filter(g => String(g.area_id) === String(filterArea));
        }
        return list.map(g => ({
            id: g.id,
            label: `${g.gerencia} (${g.area?.area || 'Sin Área'})`
        })).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.gerencias, filterArea]);

    // Opciones de consumibles filtrados por impresora
    const filterCartuchosOptions = useMemo(() => {
        let list = catalogs.cartuchos || [];
        if (filterImpresora) {
            list = list.filter(c => String(c.impresora_id) === String(filterImpresora));
        }
        return list.map(c => ({
            id: c.id,
            label: `${c.cartucho} (${c.colore?.colore || 'N/A'})`
        })).sort((a, b) => a.label.localeCompare(b.label));
    }, [catalogs.cartuchos, filterImpresora]);

    // Opciones de almacenamiento para el modal
    const modalAlmacenamientoOptions = useMemo(() => {
        return (catalogs.almacenamientos || []).map(item => {
            const printerName = item.impresoras?.impresora || 'Impresora';
            const cartName = item.cartuchos?.cartucho || 'Consumible';
            const colName = item.colores?.colore || 'Color';
            return {
                id: item.id,
                label: `${printerName} — ${cartName} (${colName})`,
                badge: `Disp: ${item.unidades_disponibles}`,
                badgeClass: item.unidades_disponibles > 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            };
        });
    }, [catalogs.almacenamientos]);

    // Detalle del almacenamiento seleccionado actualmente en el modal
    const selectedAlmacenItem = useMemo(() => {
        if (!formData.almacenamiento_impresora_id) return null;
        return (catalogs.almacenamientos || []).find(a => String(a.id) === String(formData.almacenamiento_impresora_id));
    }, [catalogs.almacenamientos, formData.almacenamiento_impresora_id]);

    // Opciones de distribuciones para el modal: CASADAS CON EL ALMACENAMIENTO SELECCIONADO
    const modalDistribucionesOptions = useMemo(() => {
        if (!formData.almacenamiento_impresora_id || !selectedAlmacenItem) return [];

        const impName = selectedAlmacenItem.impresoras?.impresora || '';
        const cartName = selectedAlmacenItem.cartuchos?.cartucho || '';
        const colName = selectedAlmacenItem.colores?.colore || '';

        // Filtrar distribuciones que coincidan con este almacenamiento o sus características
        const matching = (catalogs.distribuciones || []).filter(dist => {
            if (String(dist.almacenamiento_impresora_id) === String(formData.almacenamiento_impresora_id)) return true;
            const distImp = dist.almacenamiento_impresora?.impresoras?.impresora || '';
            const distCart = dist.almacenamiento_impresora?.cartuchos?.cartucho || '';
            const distCol = dist.almacenamiento_impresora?.colores?.colore || '';
            return distImp === impName && distCart === cartName && distCol === colName;
        });

        return matching.map(dist => ({
            id: dist.id,
            label: `${dist.gerencia?.gerencia || 'Gerencia'} — Área: ${dist.gerencia?.area?.area || 'N/A'}`,
            badge: `Asignadas: ${dist.unidades_asignadas}`,
            badgeClass: 'bg-blue-100 text-blue-900',
            unidades_asignadas: dist.unidades_asignadas
        }));
    }, [catalogs.distribuciones, formData.almacenamiento_impresora_id, selectedAlmacenItem]);

    // Distribución seleccionada en el modal
    const selectedDistItem = useMemo(() => {
        if (!formData.distribucion_almacenamiento_impresora_id) return null;
        return (catalogs.distribuciones || []).find(d => String(d.id) === String(formData.distribucion_almacenamiento_impresora_id));
    }, [catalogs.distribuciones, formData.distribucion_almacenamiento_impresora_id]);

    // Máximo stock permitido para la cantidad en el modal
    const maxAvailableQuantity = useMemo(() => {
        if (!selectedDistItem) return 0;
        let base = Number(selectedDistItem.unidades_asignadas) || 0;
        if (modalMode === 'edit' && selectedSalida && String(selectedSalida.distribucion_almacenamiento_impresora_id) === String(selectedDistItem.id)) {
            base += Number(selectedSalida.cantidad) || 0;
        }
        return base;
    }, [selectedDistItem, modalMode, selectedSalida]);

    // Cargar salidas desde el backend
    const fetchSalidas = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams();
            params.append('page', page);
            if (search) params.append('busqueda', search);
            if (filterCartucho) params.append('cartucho_id', filterCartucho);
            if (filterColore) params.append('colore_id', filterColore);
            if (filterImpresora) params.append('impresora_id', filterImpresora);
            if (filterFabricante) params.append('fabricante_id', filterFabricante);
            if (filterGerencia) params.append('gerencia_id', filterGerencia);
            if (filterArea) params.append('area_id', filterArea);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
            if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
            if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);

            const res = await fetch(`/administracion/salida_almacen?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                throw new Error(`Error en el servidor: ${res.status}`);
            }

            const data = await res.json();
            if (data.success) {
                setSalidas(data.data.data || []);
                setPagination({
                    current_page: data.data.current_page || 1,
                    last_page: data.data.last_page || 1,
                    total: data.data.total || 0,
                    per_page: data.data.per_page || 10
                });
                if (data.metrics) setMetrics(data.metrics);
                if (data.catalogs) setCatalogs(data.catalogs);
                if (data.permissions) setPermissions(data.permissions);
            } else {
                throw new Error(data.message || 'No se pudieron obtener los datos');
            }
        } catch (err) {
            console.error('Error fetching salidas de almacén:', err);
            setError(err.message || 'Ocurrió un error al cargar los datos.');
        } finally {
            setLoading(false);
        }
    }, [search, filterCartucho, filterColore, filterImpresora, filterFabricante, filterGerencia, filterArea, fechaDesde, fechaHasta, fechaIngresoDesde, fechaIngresoHasta]);

    useEffect(() => {
        fetchSalidas(1);
    }, [fetchSalidas]);

    // Abrir Modal de Creación
    const handleOpenCreate = () => {
        setFormData({
            fecha_salida: new Date().toISOString().split('T')[0],
            almacenamiento_impresora_id: '',
            distribucion_almacenamiento_impresora_id: '',
            cantidad: 1,
            ajuste_inventario: ''
        });
        setFormErrors({});
        setModalMode('create');
        setSelectedSalida(null);
        setModalOpen(true);
    };

    // Abrir Modal de Edición
    const handleOpenEdit = (item) => {
        setFormData({
            fecha_salida: item.fecha_salida ? item.fecha_salida.split('T')[0] : new Date().toISOString().split('T')[0],
            almacenamiento_impresora_id: item.almacenamiento_impresora_id || '',
            distribucion_almacenamiento_impresora_id: item.distribucion_almacenamiento_impresora_id || '',
            cantidad: item.cantidad || 1,
            ajuste_inventario: item.ajuste_inventario || ''
        });
        setFormErrors({});
        setModalMode('edit');
        setSelectedSalida(item);
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (!submitting) {
            setModalOpen(false);
            setFormErrors({});
        }
    };

    // Manejar envío de formulario Create / Edit
    const handleSubmit = async (e) => {
        e.preventDefault();
        const errors = {};

        if (!formData.fecha_salida) errors.fecha_salida = 'La fecha de salida es obligatoria.';
        if (!formData.almacenamiento_impresora_id) errors.almacenamiento_impresora_id = 'Debe seleccionar un consumible en almacén.';
        if (!formData.distribucion_almacenamiento_impresora_id) errors.distribucion_almacenamiento_impresora_id = 'Debe seleccionar una distribución de destino.';
        if (!formData.cantidad || Number(formData.cantidad) < 1) {
            errors.cantidad = 'La cantidad debe ser mayor o igual a 1.';
        } else if (maxAvailableQuantity > 0 && Number(formData.cantidad) > maxAvailableQuantity) {
            errors.cantidad = `La cantidad excede las unidades asignadas disponibles (${maxAvailableQuantity}).`;
        }

        if (Object.keys(errors).length > 0) {
            setFormErrors(errors);
            return;
        }

        setSubmitting(true);
        setFormErrors({});

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const isEdit = modalMode === 'edit';
            const url = isEdit
                ? `/administracion/salida_almacen/${selectedSalida.id}`
                : '/administracion/salida_almacen';
            const method = isEdit ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (res.status === 422 && data.errors) {
                    setFormErrors(data.errors);
                    return;
                }
                throw new Error(data.message || 'Error al procesar la salida de almacén');
            }

            Swal.fire({
                title: '¡Operación Exitosa!',
                text: data.message || (isEdit ? 'Salida actualizada exitosamente.' : 'Salida registrada exitosamente.'),
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            });

            setModalOpen(false);
            fetchSalidas(pagination.current_page);
        } catch (err) {
            console.error('Error submitting form:', err);
            Swal.fire({
                title: 'Error',
                text: err.message || 'Ocurrió un error inesperado.',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar un registro individual
    const handleDelete = async (id) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se eliminará este registro de salida y las unidades se restaurarán al almacén y distribución.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#2563eb',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/salida_almacen/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(data.message || 'Error al eliminar el registro');
            }

            Swal.fire({
                title: '¡Eliminado!',
                text: data.message || 'Registro eliminado correctamente.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds(prev => prev.filter(item => item !== id));
            fetchSalidas(pagination.current_page);
        } catch (err) {
            console.error('Error deleting salida:', err);
            Swal.fire({
                title: 'Error',
                text: err.message || 'Ocurrió un error al eliminar.',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            });
        }
    };

    // Eliminar seleccionados
    const handleDeleteSelected = async () => {
        if (selectedIds.length === 0) return;

        const { value: password } = await Swal.fire({
            title: '¿Eliminar seleccionados?',
            text: `Se eliminarán ${selectedIds.length} registro(s) de salida de almacén. Ingresa tu contraseña de confirmación:`,
            icon: 'warning',
            input: 'password',
            inputPlaceholder: 'Contraseña de confirmación',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#2563eb',
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
            const res = await fetch('/administracion/salida_almacen/destroy-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    password: password
                })
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(data.message || 'Error al eliminar registros');
            }

            Swal.fire({
                title: '¡Operación Exitosa!',
                text: data.message || `${selectedIds.length} registros eliminados correctamente.`,
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(pagination.current_page);
        } catch (err) {
            Swal.fire({
                title: 'Error',
                text: err.message || 'No se pudieron eliminar los registros.',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            });
        }
    };

    // Eliminar todo
    const handleDeleteAll = async () => {
        const { value: password } = await Swal.fire({
            title: '⚠️ ¿ELIMINAR TODO EL HISTORIAL?',
            text: 'Esta acción eliminará permanentemente todas las salidas registradas. Para confirmar, ingresa tu contraseña de usuario:',
            icon: 'error',
            input: 'password',
            inputPlaceholder: 'Contraseña de confirmación',
            showCancelButton: true,
            confirmButtonColor: '#991b1b',
            cancelButtonColor: '#2563eb',
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
            const res = await fetch('/administracion/salida_almacen/destroy-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password })
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(data.message || 'Error al eliminar todos los registros');
            }

            Swal.fire({
                title: '¡Vaciado Completo!',
                text: data.message || 'Todos los registros han sido eliminados.',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(1);
        } catch (err) {
            Swal.fire({
                title: 'Error',
                text: err.message || 'No se pudieron eliminar todos los registros.',
                icon: 'error',
                confirmButtonColor: '#2563eb'
            });
        }
    };

    // Exportar con descarga progresiva
    const handleExport = (e) => {
        const val = e.target.value;
        if (!val) return;

        const [format, type] = val.split(':');
        const isPdf = format === 'pdf';
        const urlBase = isPdf
            ? '/administracion/salida_almacen/export/pdf'
            : '/administracion/salida_almacen/export/excel';

        const params = new URLSearchParams();
        if (type === 'selected' && selectedIds.length > 0) {
            params.append('selected_ids', selectedIds.join(','));
        } else {
            if (search) params.append('busqueda', search);
            if (filterCartucho) params.append('cartucho_id', filterCartucho);
            if (filterColore) params.append('colore_id', filterColore);
            if (filterImpresora) params.append('impresora_id', filterImpresora);
            if (filterFabricante) params.append('fabricante_id', filterFabricante);
            if (filterGerencia) params.append('gerencia_id', filterGerencia);
            if (filterArea) params.append('area_id', filterArea);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
            if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
            if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);
        }

        const fullUrl = `${urlBase}?${params.toString()}`;
        downloadWithProgress(
            fullUrl,
            `salida_almacen_${new Date().toISOString().slice(0, 10)}.${isPdf ? 'pdf' : 'xlsx'}`
        );

        e.target.value = '';
    };

    // Selección de todos en la página actual
    const handleSelectAll = (e) => {
        if (e.target.checked) {
            const pageIds = salidas.map(s => s.id);
            setSelectedIds(prev => Array.from(new Set([...prev, ...pageIds])));
        } else {
            const pageIdsSet = new Set(salidas.map(s => s.id));
            setSelectedIds(prev => prev.filter(id => !pageIdsSet.has(id)));
        }
    };

    const isAllPageSelected = salidas.length > 0 && salidas.every(s => selectedIds.includes(s.id));

    // Limpiar todos los filtros
    const handleClearFilters = () => {
        setSearch('');
        setFilterCartucho('');
        setFilterColore('');
        setFilterImpresora('');
        setFilterFabricante('');
        setFilterGerencia('');
        setFilterArea('');
        setFechaDesde('');
        setFechaHasta('');
        setFechaIngresoDesde('');
        setFechaIngresoHasta('');
    };

    return (
        <div className="w-full space-y-6">
            {/* Tarjetas Superiores de Métricas (100% responsivas para cualquier resolución como 1920x1200) */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 animate-fadeIn">
                {/* Total Salidas */}
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                            <span className="text-blue-500" style={{ fontSize: '24px' }}>📦</span>
                        </div>
                    </div>
                    <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalSalidas}</h3>
                    <p className="text-sm text-blue-900 font-semibold">Total Unidades Retiradas</p>
                </div>

                {/* Salidas Registradas */}
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                            <span className="text-green-500" style={{ fontSize: '24px' }}>📊</span>
                        </div>
                        <span className="text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-600 font-bold">
                            Activo
                        </span>
                    </div>
                    <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalSalidasRegistradas}</h3>
                    <p className="text-sm text-blue-900 font-semibold">Salidas Registradas</p>
                    <div className="mt-3 flex items-center gap-2 text-xs">
                        <span className="text-green-600 font-bold">{metrics.salidasEsteMes}</span>
                        <span className="text-blue-900 font-semibold">este mes</span>
                    </div>
                </div>

                {/* Salidas Bajo Stock */}
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <div className="w-12 h-12 rounded-xl grid place-items-center bg-amber-500/20">
                            <span className="text-amber-500" style={{ fontSize: '24px' }}>⚠️</span>
                        </div>
                        {metrics.salidasBajoStock > 0 && (
                            <span className="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 font-bold">
                                Atención
                            </span>
                        )}
                    </div>
                    <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.salidasBajoStock}</h3>
                    <p className="text-sm text-blue-900 font-semibold">Alertas de Bajo Stock</p>
                </div>

                {/* Última Salida */}
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                    <div className="flex items-center justify-between mb-4">
                        <div className="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                            <span className="text-purple-500" style={{ fontSize: '24px' }}>🗓️</span>
                        </div>
                    </div>
                    <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.ultimasFechasSalida}</h3>
                    <p className="text-sm text-blue-900 font-semibold">Última Fecha de Salida</p>
                </div>
            </div>

            {/* Barra Principal: Búsqueda, Acciones, Exportación y Botón Nuevo */}
            <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
                <div className="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
                    {/* Buscador en Vivo */}
                    <div className="relative flex-1 min-w-[280px]">
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && fetchSalidas(1)}
                            placeholder="Buscar por consumible, impresora, gerencia, área (Enter)..."
                            className="w-full bg-blue-50/50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                        />
                        <span className="absolute left-3.5 top-2.5 sm:top-3 text-slate-400 text-sm">🔍</span>
                    </div>

                    {/* Toolbar de Acciones */}
                    <div className="flex flex-wrap items-center justify-start xl:justify-end gap-2.5 sm:gap-3 shrink-0">
                        {/* Selector de Exportación */}
                        <select
                            onChange={handleExport}
                            defaultValue=""
                            className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-xs sm:text-sm cursor-pointer shadow-xs"
                        >
                            <option value="">📤 Exportar</option>
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
                                onClick={handleDeleteSelected}
                                disabled={selectedIds.length === 0}
                                className={`inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 ${
                                    selectedIds.length === 0
                                        ? 'opacity-50 cursor-not-allowed bg-red-400'
                                        : 'cursor-pointer hover:shadow-lg'
                                }`}
                                style={{
                                    background: selectedIds.length > 0 ? 'linear-gradient(135deg, #8a1e1e 0%, #f63b3b 100%)' : undefined,
                                    boxShadow: selectedIds.length > 0 ? '0 4px 12px rgba(246, 59, 59, 0.35)' : undefined
                                }}
                            >
                                <span>🗑️</span>
                                <span>Eliminar Seleccionados {selectedIds.length > 0 && `(${selectedIds.length})`}</span>
                            </button>
                        )}

                        {/* Eliminar Todo */}
                        {permissions.can_destroy && (
                            <button
                                type="button"
                                onClick={handleDeleteAll}
                                className="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer hover:shadow-lg"
                                style={{
                                    background: 'linear-gradient(135deg, #5e0e0e 0%, #b41818 100%)',
                                    boxShadow: '0 4px 12px rgba(246, 59, 59, 0.3)'
                                }}
                            >
                                <span>⚠️</span>
                                <span>Eliminar Todo</span>
                            </button>
                        )}

                        {/* Botón Nueva Salida */}
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={handleOpenCreate}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer shrink-0 hover:shadow-lg"
                                style={{
                                    background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                    boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                }}
                            >
                                <span>➕</span>
                                <span>Nueva Salida</span>
                            </button>
                        )}
                    </div>
                </div>

                {/* Fila de Filtros Avanzados y Badges */}
                <div className="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div className="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                        <button
                            type="button"
                            onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                            className="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-sm active:translate-y-0.5 cursor-pointer"
                            style={{
                                background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
                            }}
                        >
                            <span>⚡ Filtros Avanzados</span>
                            {activeFiltersCount > 0 && (
                                <span className="w-5 h-5 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                                    {{ activeFiltersCount }}
                                </span>
                            )}
                            <span className={`text-xs transition-transform duration-300 ${showAdvancedFilters ? 'rotate-180' : ''}`}>▼</span>
                        </button>

                        {activeFiltersCount > 0 && (
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors cursor-pointer"
                            >
                                <span>✕</span>
                                <span>Limpiar Filtros ({activeFiltersCount})</span>
                            </button>
                        )}
                    </div>

                    {/* Badges de filtros activos */}
                    <div className="flex flex-wrap items-center gap-2 text-xs">
                        {filterCartucho && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Consumible</span>
                                <button type="button" onClick={() => setFilterCartucho('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                        {filterColore && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Color</span>
                                <button type="button" onClick={() => setFilterColore('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                        {filterImpresora && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Impresora</span>
                                <button type="button" onClick={() => setFilterImpresora('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                        {filterGerencia && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Gerencia</span>
                                <button type="button" onClick={() => setFilterGerencia('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                        {filterArea && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Área</span>
                                <button type="button" onClick={() => setFilterArea('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                        {(fechaDesde || fechaHasta) && (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                <span>Salida: {fechaDesde || '...'} a {fechaHasta || '...'}</span>
                                <button type="button" onClick={() => { setFechaDesde(''); setFechaHasta(''); }} className="hover:text-red-600 font-bold ml-1">✕</button>
                            </span>
                        )}
                    </div>
                </div>

                {/* Acordeón de Filtros Avanzados con Grid Auto-height */}
                <div
                    className={`grid transition-all duration-500 ease-in-out ${
                        showAdvancedFilters ? 'grid-rows-[1fr] opacity-100 mt-3 visible' : 'grid-rows-[0fr] opacity-0 invisible mt-0 pointer-events-none'
                    }`}
                >
                    <div className="overflow-visible transition-all duration-300">
                        <div className="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md space-y-4">
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                                {/* Impresora */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">🖨️ Impresora</label>
                                    <select
                                        value={filterImpresora}
                                        onChange={(e) => setFilterImpresora(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todas las impresoras</option>
                                        {(catalogs.impresoras || []).map(imp => (
                                            <option key={imp.id} value={imp.id}>
                                                {String(imp.id).padStart(4, '0')} - {imp.impresora}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Consumible (filtrado por impresora si existe) */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">🗃️ Consumible</label>
                                    <select
                                        value={filterCartucho}
                                        onChange={(e) => setFilterCartucho(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todos los consumibles</option>
                                        {filterCartuchosOptions.map(c => (
                                            <option key={c.id} value={c.id}>{c.label}</option>
                                        ))}
                                    </select>
                                </div>

                                {/* Color / Tipo Consumible */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">🎨 Tipo Consumible</label>
                                    <select
                                        value={filterColore}
                                        onChange={(e) => setFilterColore(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todos los colores</option>
                                        {(catalogs.colores || []).map(col => (
                                            <option key={col.id} value={col.id}>{col.colore}</option>
                                        ))}
                                    </select>
                                </div>

                                {/* Tipo Impresora / Fabricante */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">🏭 Tipo Impresora</label>
                                    <select
                                        value={filterFabricante}
                                        onChange={(e) => setFilterFabricante(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todos los tipos</option>
                                        {(catalogs.fabricantes || []).map(fab => (
                                            <option key={fab.id} value={fab.id}>{fab.fabricante}</option>
                                        ))}
                                    </select>
                                </div>

                                {/* Gerencia (Casada con Área) */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">🏢 Gerencia</label>
                                    <select
                                        value={filterGerencia}
                                        onChange={(e) => setFilterGerencia(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todas las gerencias</option>
                                        {filterGerenciasOptions.map(g => (
                                            <option key={g.id} value={g.id}>{g.label}</option>
                                        ))}
                                    </select>
                                </div>

                                {/* Área (Casada con Gerencia) */}
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">📍 Área</label>
                                    <select
                                        value={filterArea}
                                        onChange={(e) => setFilterArea(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    >
                                        <option value="">Todas las áreas</option>
                                        {filterAreasOptions.map(a => (
                                            <option key={a.id} value={a.id}>{a.label}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Rangos de Fecha */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-blue-200/50">
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">📅 Salida Desde</label>
                                    <input
                                        type="date"
                                        value={fechaDesde}
                                        onChange={(e) => setFechaDesde(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">📅 Salida Hasta</label>
                                    <input
                                        type="date"
                                        value={fechaHasta}
                                        onChange={(e) => setFechaHasta(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">📥 Ingreso Almacén Desde</label>
                                    <input
                                        type="date"
                                        value={fechaIngresoDesde}
                                        onChange={(e) => setFechaIngresoDesde(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-blue-900 mb-1">📥 Ingreso Almacén Hasta</label>
                                    <input
                                        type="date"
                                        value={fechaIngresoHasta}
                                        onChange={(e) => setFechaIngresoHasta(e.target.value)}
                                        className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Listado de Tarjetas de Salida de Almacén */}
            <div className="space-y-4">
                {/* Cabecera de Selección Masiva */}
                {salidas.length > 0 && (
                    <div className="flex items-center justify-between px-3 py-2 bg-slate-100/80 rounded-xl text-xs font-semibold text-slate-600">
                        <label className="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                checked={isAllPageSelected}
                                onChange={handleSelectAll}
                                className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                            />
                            <span>Seleccionar todos en esta página ({salidas.length})</span>
                        </label>
                        <span>Mostrando {salidas.length} de {pagination.total} registros</span>
                    </div>
                )}

                {loading ? (
                    <div className="p-12 text-center bg-white rounded-2xl shadow-sm border border-slate-200 animate-pulse">
                        <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando registros de salida de almacén...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-red-50 rounded-2xl border border-red-200">
                        <p className="text-sm font-bold text-red-600 mb-2">Error al cargar datos</p>
                        <p className="text-xs text-red-500 mb-4">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchSalidas(1)}
                            className="px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 transition-all cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : salidas.length === 0 ? (
                    <div className="p-12 text-center bg-white rounded-2xl shadow-sm border border-slate-200">
                        <div className="text-4xl mb-3">📦</div>
                        <h3 className="text-base font-bold text-slate-800">No hay salidas registradas</h3>
                        <p className="text-xs text-slate-500 mt-1">No se encontraron salidas con los filtros seleccionados.</p>
                        {activeFiltersCount > 0 && (
                            <button
                                type="button"
                                onClick={handleClearFilters}
                                className="mt-4 px-4 py-2 bg-blue-50 text-blue-900 rounded-xl text-xs font-bold hover:bg-blue-100 border border-blue-200 transition-all cursor-pointer"
                            >
                                Limpiar filtros
                            </button>
                        )}
                    </div>
                ) : (
                    salidas.map((item) => {
                        const isSelected = selectedIds.includes(item.id);
                        const printer = item.almacenamiento_impresora?.impresoras;
                        const cartucho = item.almacenamiento_impresora?.cartuchos;
                        const color = item.almacenamiento_impresora?.colores;
                        const fabricante = item.almacenamiento_impresora?.fabricantes;
                        const gerencia = item.distribucion_almacenamiento_impresora?.gerencia;
                        const area = gerencia?.area;

                        const original = Number(item.historico_unidades_asignadas_originales) || 0;
                        const restantes = Number(item.historico_unidades_asignadas_restantes) || 0;
                        const consumidas = original - restantes;
                        const pctRemaining = original > 0 ? (restantes / original) * 100 : 0;
                        const barColor = pctRemaining < 30 ? 'bg-red-500' : (pctRemaining < 60 ? 'bg-yellow-500' : 'bg-green-500');

                        const colorName = (color?.colore || '').toLowerCase();
                        let colorClass = 'bg-slate-200 text-slate-800';
                        if (colorName.includes('negro') || colorName.includes('black')) colorClass = 'bg-black text-white';
                        else if (colorName.includes('amarillo') || colorName.includes('yellow')) colorClass = 'bg-yellow-400 text-slate-900';
                        else if (colorName.includes('azul') || colorName.includes('cyan')) colorClass = 'bg-blue-500 text-white';
                        else if (colorName.includes('magenta') || colorName.includes('rojo')) colorClass = 'bg-pink-500 text-white';

                        return (
                            <div
                                key={item.id}
                                className={`bg-white border transition-all duration-200 rounded-2xl p-5 sm:p-6 shadow-sm hover:shadow-md ${
                                    isSelected ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20' : 'border-slate-200'
                                }`}
                            >
                                <div className="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                                    {/* Checkbox y Foto de Impresora */}
                                    <div className="flex items-center gap-3.5 shrink-0">
                                        <input
                                            type="checkbox"
                                            checked={isSelected}
                                            onChange={(e) => {
                                                if (e.target.checked) {
                                                    setSelectedIds(prev => [...prev, item.id]);
                                                } else {
                                                    setSelectedIds(prev => prev.filter(x => x !== item.id));
                                                }
                                            }}
                                            className="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
                                        />

                                        <div className="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                                            {printer?.imagen ? (
                                                <img
                                                    src={`/storage/${printer.imagen}`}
                                                    alt={printer.impresora || 'Impresora'}
                                                    className="w-full h-full object-cover"
                                                />
                                            ) : (
                                                <span className="text-3xl">🖨️</span>
                                            )}
                                        </div>
                                    </div>

                                    {/* Información Detallada */}
                                    <div className="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 text-xs">
                                        <div>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Fecha Salida</p>
                                            <p className="font-bold text-slate-800 text-sm">
                                                {item.fecha_salida ? item.fecha_salida.split('T')[0] : 'N/A'}
                                            </p>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-2 mb-0.5">Fecha Ingreso Almacén</p>
                                            <p className="font-semibold text-slate-700">
                                                {item.almacenamiento_impresora?.fecha_ingreso ? item.almacenamiento_impresora.fecha_ingreso.split('T')[0] : 'N/A'}
                                            </p>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-2 mb-0.5">Factura Almacén</p>
                                            <span className="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-bold inline-block">
                                                #{item.almacenamiento_impresora?.n_factura || 'S/N'}
                                            </span>
                                        </div>

                                        <div>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Consumible & Color</p>
                                            <p className="font-bold text-slate-800 text-sm">
                                                {cartucho?.cartucho || 'N/A'}
                                            </p>
                                            <div className="flex items-center gap-1.5 mt-1.5">
                                                <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${colorClass}`}>
                                                    {color?.colore || 'N/A'}
                                                </span>
                                                <span className="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                                    {fabricante?.fabricante || 'N/A'}
                                                </span>
                                            </div>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-2 mb-0.5">Impresora</p>
                                            <p className="font-semibold text-blue-900">
                                                {printer?.impresora || 'Sin impresora asignada'}
                                            </p>
                                        </div>

                                        <div>
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Destino (Gerencia / Área)</p>
                                            <p className="font-bold text-slate-800 text-sm">
                                                {gerencia?.gerencia || 'Gerencia General'}
                                            </p>
                                            <p className="font-medium text-slate-600 mt-0.5">
                                                📍 {area?.area || 'Sin Área asignada'}
                                            </p>
                                            {item.ajuste_inventario && (
                                                <div className="mt-2 text-[11px] text-slate-500 italic bg-amber-50/70 p-1.5 rounded-lg border border-amber-200/50">
                                                    💬 {item.ajuste_inventario}
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {/* Métrica de Stock / Unidades y Cantidad Retirada */}
                                    <div className="flex flex-row lg:flex-col items-center lg:items-end justify-between w-full lg:w-auto gap-4 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                                        <div className="text-left lg:text-right">
                                            <p className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Unidades Disponibles</p>
                                            <div className="flex items-baseline gap-1.5 justify-start lg:justify-end mt-0.5">
                                                <span className="text-xl font-black text-blue-900">{restantes}</span>
                                                <span className="text-xs text-slate-400 font-bold">de {original}</span>
                                            </div>
                                            <div className="w-28 sm:w-36 bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                                                <div className={`${barColor} h-1.5 rounded-full transition-all`} style={{ width: `${Math.min(100, Math.max(0, pctRemaining))}%` }}></div>
                                            </div>
                                        </div>

                                        <div className="text-center lg:text-right">
                                            <p className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cantidad Retirada</p>
                                            <p className="text-2xl font-black text-orange-600 mt-0.5">
                                                {item.cantidad} <span className="text-sm">📦</span>
                                            </p>
                                        </div>

                                        {/* Botones de Acción */}
                                        <div className="flex items-center gap-2">
                                            {permissions.can_edit && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEdit(item)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition-all bg-green-600 hover:bg-green-700 text-white text-xs shadow-xs active:translate-y-0.5 cursor-pointer"
                                                >
                                                    <span>✏️</span>
                                                    <span>Editar</span>
                                                </button>
                                            )}

                                            {permissions.can_destroy && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(item.id)}
                                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold transition-all bg-red-600 hover:bg-red-700 text-white text-xs shadow-xs active:translate-y-0.5 cursor-pointer"
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
                    })
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="flex items-center justify-between px-4 py-3 bg-white rounded-2xl border border-slate-200 shadow-sm text-xs">
                        <span className="text-slate-500 font-medium">
                            Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros en total)
                        </span>
                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                disabled={pagination.current_page <= 1}
                                onClick={() => fetchSalidas(pagination.current_page - 1)}
                                className={`px-3 py-1.5 rounded-xl font-bold transition-all ${
                                    pagination.current_page <= 1
                                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                        : 'bg-blue-50 text-blue-900 hover:bg-blue-100 cursor-pointer'
                                }`}
                            >
                                ◀ Anterior
                            </button>

                            <button
                                type="button"
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => fetchSalidas(pagination.current_page + 1)}
                                className={`px-3 py-1.5 rounded-xl font-bold transition-all ${
                                    pagination.current_page >= pagination.last_page
                                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                        : 'bg-blue-50 text-blue-900 hover:bg-blue-100 cursor-pointer'
                                }`}
                            >
                                Siguiente ▶
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Crear / Editar Salida de Almacén (Portal al Body, estilo corporativo) */}
            {modalOpen && createPortal(
                <div
                    className="fixed inset-0 z-[99999999] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md animate-modal-backdrop"
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
                        {/* Cabecera del Modal con degradado corporativo */}
                        <div
                            className="p-5 sm:p-6 text-white rounded-t-3xl flex items-center justify-between relative overflow-hidden"
                            style={{
                                background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
                            }}
                        >
                            <div>
                                <h3 className="text-lg sm:text-xl font-black tracking-tight">
                                    {modalMode === 'create' ? 'Registrar Salida de Almacén' : 'Editar Salida de Almacén'}
                                </h3>
                                <p className="text-xs text-blue-100 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Asigna la salida de un consumible en almacén hacia su distribución y destino'
                                        : `Actualizando registro #${selectedSalida?.id || ''}`}
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
                            {/* Fecha de Salida */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    📅 Fecha de Salida <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={formData.fecha_salida}
                                    onChange={(e) => setFormData(prev => ({ ...prev, fecha_salida: e.target.value }))}
                                    className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                        formErrors.fecha_salida ? 'border-red-400 bg-red-50' : 'border-white/20'
                                    }`}
                                    required
                                    disabled={submitting}
                                />
                                {formErrors.fecha_salida && (
                                    <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.fecha_salida[0] || formErrors.fecha_salida}</p>
                                )}
                            </div>

                            {/* Select 1: Consumible en Almacén (SearchableSelect) */}
                            <div>
                                <SearchableSelect
                                    label="🖨️ Consumible Disponible en Almacén"
                                    required
                                    icon="🗃️"
                                    options={modalAlmacenamientoOptions}
                                    value={formData.almacenamiento_impresora_id}
                                    onChange={(val) => {
                                        setFormData(prev => ({
                                            ...prev,
                                            almacenamiento_impresora_id: val,
                                            // Resetear distribución casada al cambiar almacén
                                            distribucion_almacenamiento_impresora_id: ''
                                        }));
                                    }}
                                    placeholder="Seleccione el consumible disponible en almacén..."
                                    allLabel={null}
                                    disabled={submitting}
                                    error={formErrors.almacenamiento_impresora_id?.[0] || formErrors.almacenamiento_impresora_id}
                                />

                                {/* Vista Previa del Almacenamiento Seleccionado */}
                                {selectedAlmacenItem && (
                                    <div className="mt-2 p-3 bg-blue-50/70 rounded-xl border border-blue-200/60 text-xs flex items-center justify-between">
                                        <div>
                                            <span className="font-bold text-blue-900">
                                                {selectedAlmacenItem.impresoras?.impresora || 'Impresora'}
                                            </span>
                                            <p className="text-slate-600">
                                                {selectedAlmacenItem.cartuchos?.cartucho || 'Consumible'} — Color: {selectedAlmacenItem.colores?.colore || 'N/A'} ({selectedAlmacenItem.fabricantes?.fabricante || 'N/A'})
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <span className="text-[10px] font-bold uppercase text-slate-400">Stock Almacén</span>
                                            <p className="font-black text-blue-900 text-sm">
                                                {selectedAlmacenItem.unidades_disponibles} unds
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Select 2: Distribución de Destino (CASADA con el Almacenamiento seleccionado) */}
                            <div>
                                <SearchableSelect
                                    label="🏢 Distribución Asignada (Gerencia / Área Destino)"
                                    required
                                    icon="📍"
                                    options={modalDistribucionesOptions}
                                    value={formData.distribucion_almacenamiento_impresora_id}
                                    onChange={(val) => {
                                        setFormData(prev => ({
                                            ...prev,
                                            distribucion_almacenamiento_impresora_id: val
                                        }));
                                    }}
                                    placeholder={
                                        !formData.almacenamiento_impresora_id
                                            ? 'Primero seleccione un consumible en almacén arriba...'
                                            : modalDistribucionesOptions.length === 0
                                                ? 'No hay distribuciones para este consumible'
                                                : 'Seleccione la gerencia o área que recibirá el consumible...'
                                    }
                                    allLabel={null}
                                    disabled={submitting || !formData.almacenamiento_impresora_id || modalDistribucionesOptions.length === 0}
                                    error={formErrors.distribucion_almacenamiento_impresora_id?.[0] || formErrors.distribucion_almacenamiento_impresora_id}
                                />

                                {selectedDistItem && (
                                    <div className="mt-2 p-3 bg-green-50/70 rounded-xl border border-green-200/60 text-xs flex items-center justify-between">
                                        <div>
                                            <span className="font-bold text-green-900">
                                                Destino: {selectedDistItem.gerencia?.gerencia || 'Gerencia'}
                                            </span>
                                            <p className="text-slate-600">
                                                Área: {selectedDistItem.gerencia?.area?.area || 'Sin Área'}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <span className="text-[10px] font-bold uppercase text-slate-400">Asignadas Disponibles</span>
                                            <p className="font-black text-green-900 text-sm">
                                                {maxAvailableQuantity} unds
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Fila: Cantidad a Retirar y Observación */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📦 Cantidad a Retirar <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max={maxAvailableQuantity > 0 ? maxAvailableQuantity : undefined}
                                        value={formData.cantidad}
                                        onChange={(e) => setFormData(prev => ({ ...prev, cantidad: e.target.value }))}
                                        className={`w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                            formErrors.cantidad ? 'border-red-400 bg-red-50' : 'border-white/20'
                                        }`}
                                        required
                                        disabled={submitting}
                                    />
                                    {maxAvailableQuantity > 0 && (
                                        <p className="text-[11px] text-slate-500 mt-1">Máximo permitido: {maxAvailableQuantity} unds</p>
                                    )}
                                    {formErrors.cantidad && (
                                        <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.cantidad[0] || formErrors.cantidad}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-blue-900 mb-1">
                                        📝 Ajuste / Observaciones
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.ajuste_inventario}
                                        onChange={(e) => setFormData(prev => ({ ...prev, ajuste_inventario: e.target.value }))}
                                        placeholder="Ej: Entrega mensual, reposición de tóner..."
                                        className="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-white/20 bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all"
                                        disabled={submitting}
                                    />
                                </div>
                            </div>

                            {/* Botones de Acción del Modal */}
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
                                    className="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer disabled:opacity-50"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)'
                                    }}
                                >
                                    <span>💾</span>
                                    <span>{submitting ? 'Procesando...' : (modalMode === 'create' ? 'Guardar Salida' : 'Actualizar Salida')}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>,
                document.body
            )}
        </div>
    );
}
