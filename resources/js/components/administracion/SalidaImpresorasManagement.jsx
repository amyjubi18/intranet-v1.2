import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import { downloadWithProgress } from '../../utils/downloadWithProgress';

export default function SalidaImpresorasManagement() {
    const [salidas, setSalidas] = useState([]);
    const [catalogs, setCatalogs] = useState({
        cartuchos: [],
        colores: [],
        impresoras: [],
        fabricantes: [],
        areas: [],
        gerencias: [],
        inventario_impresoras: []
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
    const [filterGerencia, setFilterGerencia] = useState('');
    const [filterArea, setFilterArea] = useState('');
    const [filterFabricante, setFilterFabricante] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');
    const [fechaIngresoDesde, setFechaIngresoDesde] = useState('');
    const [fechaIngresoHasta, setFechaIngresoHasta] = useState('');

    // Modal de Crear y Editar (Estilo Inventario Oficinas)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedSalida, setSelectedSalida] = useState(null);
    const [formData, setFormData] = useState({
        fecha_salida: new Date().toISOString().split('T')[0],
        inventario_impresora_id: '',
        cantidad: 1
    });
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Conteo de filtros avanzados activos
    const activeFiltersCount = useMemo(() => {
        let count = 0;
        if (filterCartucho) count++;
        if (filterColore) count++;
        if (filterImpresora) count++;
        if (filterGerencia) count++;
        if (filterArea) count++;
        if (filterFabricante) count++;
        if (fechaDesde || fechaHasta) count++;
        if (fechaIngresoDesde || fechaIngresoHasta) count++;
        return count;
    }, [filterCartucho, filterColore, filterImpresora, filterGerencia, filterArea, filterFabricante, fechaDesde, fechaHasta, fechaIngresoDesde, fechaIngresoHasta]);

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

    // Fetch salidas
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
            if (filterGerencia) params.append('gerencia_id', filterGerencia);
            if (filterArea) params.append('area_id', filterArea);
            if (filterFabricante) params.append('fabricante_id', filterFabricante);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
            if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
            if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);

            const res = await fetch(`/administracion/salida_impresora?${params.toString()}`, {
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
                throw new Error(`Error ${res.status}: No se pudo cargar los registros de salida.`);
            }

            const json = await res.json();
            const dataPaginated = json.data || {};
            const items = Array.isArray(dataPaginated) ? dataPaginated : (dataPaginated.data || []);

            setSalidas(items);
            if (json.metrics) setMetrics(json.metrics);
            if (json.catalogs) setCatalogs(json.catalogs);
            if (json.permissions) setPermissions(json.permissions);

            if (dataPaginated.current_page) {
                setPagination({
                    current_page: dataPaginated.current_page,
                    last_page: dataPaginated.last_page || 1,
                    total: dataPaginated.total || items.length,
                    per_page: dataPaginated.per_page || 10
                });
            }
        } catch (err) {
            console.error('Error fetching salidas:', err);
            setError(err.message || 'Error de conexión.');
        } finally {
            setLoading(false);
        }
    }, [search, filterCartucho, filterColore, filterImpresora, filterGerencia, filterArea, filterFabricante, fechaDesde, fechaHasta, fechaIngresoDesde, fechaIngresoHasta]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchSalidas(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [fetchSalidas]);

    // Limpiar todos los filtros
    const handleClearFilters = () => {
        setSearch('');
        setFilterCartucho('');
        setFilterColore('');
        setFilterImpresora('');
        setFilterGerencia('');
        setFilterArea('');
        setFilterFabricante('');
        setFechaDesde('');
        setFechaHasta('');
        setFechaIngresoDesde('');
        setFechaIngresoHasta('');
    };

    // Selección de filas
    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(salidas.map(s => s.id));
        } else {
            setSelectedIds([]);
        }
    };

    const handleToggleSelect = (id) => {
        setSelectedIds(prev => prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]);
    };

    // Handlers Modal Crear / Editar
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedSalida(null);
        setFormData({
            fecha_salida: new Date().toISOString().split('T')[0],
            inventario_impresora_id: '',
            cantidad: 1
        });
        setFormErrors({});
        setModalOpen(true);
    };

    const handleOpenEdit = (salida) => {
        setModalMode('edit');
        setSelectedSalida(salida);
        setFormData({
            fecha_salida: salida.fecha_salida ? salida.fecha_salida.split('T')[0] : new Date().toISOString().split('T')[0],
            inventario_impresora_id: salida.inventario_impresora_id || '',
            cantidad: salida.cantidad || 1
        });
        setFormErrors({});
        setModalOpen(true);
    };

    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedSalida(null);
        setFormErrors({});
    };

    // Guardar / Actualizar
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        setSubmitting(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/administracion/salida_impresora'
                : `/administracion/salida_impresora/${selectedSalida.id}`;
            const method = modalMode === 'create' ? 'POST' : 'PUT';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
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
                throw new Error(data.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Registrado!' : '¡Actualizado!',
                text: data.message || 'Operación completada con éxito.',
                timer: 2000,
                showConfirmButton: false
            });

            setModalOpen(false);
            fetchSalidas(pagination.current_page);
        } catch (err) {
            console.error('Error saving salida:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el registro.'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar individual
    const handleDelete = async (salida) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará el registro de salida y se restaurarán ${salida.cantidad} unidad(es) al inventario.`,
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
            const res = await fetch(`/administracion/salida_impresora/${salida.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'No se pudo eliminar el registro.');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: data.message || 'Registro eliminado correctamente.',
                timer: 2000,
                showConfirmButton: false
            });

            fetchSalidas(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el registro.'
            });
        }
    };

    // Eliminar Seleccionados con contraseña
    const handleDeleteSelected = async () => {
        if (selectedIds.length === 0) return;

        const { value: password } = await Swal.fire({
            title: 'Confirmar Eliminación',
            text: `Se eliminarán ${selectedIds.length} registro(s) de salida seleccionados y se restaurarán las unidades al inventario. Ingresa tu contraseña:`,
            input: 'password',
            inputPlaceholder: 'Tu contraseña',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Eliminar Seleccionados',
            cancelButtonText: 'Cancelar'
        });

        if (!password) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/administracion/salida_impresora/destroy-multiple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: selectedIds, password: password })
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'No se pudieron eliminar los registros.');

            Swal.fire({
                icon: 'success',
                title: '¡Eliminados!',
                text: data.message || 'Registros eliminados correctamente.',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(pagination.current_page);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudieron eliminar los registros.'
            });
        }
    };

    // Eliminar Todo con contraseña
    const handleDeleteAll = async () => {
        const { value: password } = await Swal.fire({
            title: '⚠️ ¿ELIMINAR TODO EL HISTORIAL?',
            text: 'Esta acción eliminará TODOS los registros de salida de consumibles y restaurará las unidades. Ingresa tu contraseña para confirmar:',
            input: 'password',
            inputPlaceholder: 'Tu contraseña de administrador',
            showCancelButton: true,
            confirmButtonColor: '#991b1b',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, Eliminar TODO',
            cancelButtonText: 'Cancelar'
        });

        if (!password) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/administracion/salida_impresora/destroy-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ password: password })
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'No se pudieron eliminar los registros.');

            Swal.fire({
                icon: 'success',
                title: '¡Vaciado!',
                text: data.message || 'Todos los registros fueron eliminados.',
                timer: 2000,
                showConfirmButton: false
            });

            setSelectedIds([]);
            fetchSalidas(1);
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudieron eliminar los registros.'
            });
        }
    };

    // Exportación
    const handleExport = (format, scope) => {
        const params = new URLSearchParams();
        if (search) params.append('busqueda', search);
        if (filterCartucho) params.append('cartucho_id', filterCartucho);
        if (filterColore) params.append('colore_id', filterColore);
        if (filterImpresora) params.append('impresora_id', filterImpresora);
        if (filterGerencia) params.append('gerencia_id', filterGerencia);
        if (filterArea) params.append('area_id', filterArea);
        if (filterFabricante) params.append('fabricante_id', filterFabricante);
        if (fechaDesde) params.append('fecha_desde', fechaDesde);
        if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        if (fechaIngresoDesde) params.append('fecha_ingreso_desde', fechaIngresoDesde);
        if (fechaIngresoHasta) params.append('fecha_ingreso_hasta', fechaIngresoHasta);

        if (scope === 'selected' && selectedIds.length > 0) {
            params.append('selected_ids', selectedIds.join(','));
        }

        const endpoint = format === 'excel'
            ? `/administracion/salida_impresora/export/excel?${params.toString()}`
            : `/administracion/salida_impresora/export/pdf?${params.toString()}`;

        downloadWithProgress(endpoint, {
            filename: `salida_consumibles_${scope === 'selected' ? 'seleccionados' : 'todos'}.${format === 'excel' ? 'xlsx' : 'pdf'}`,
            title: `Exportando a ${format.toUpperCase()}`
        });
    };

    // Item de inventario seleccionado en el formulario
    const selectedInventario = useMemo(() => {
        if (!formData.inventario_impresora_id) return null;
        return (catalogs.inventario_impresoras || []).find(
            inv => String(inv.id) === String(formData.inventario_impresora_id)
        );
    }, [formData.inventario_impresora_id, catalogs.inventario_impresoras]);

    return (
        <div className="w-full">
            {/* Tarjetas superiores de estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 animate-fadeIn">
                    {/* Últimas Fechas de Salida */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500 text-2xl">📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.ultimasFechasSalida}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Últimas Fechas de Salida</p>
                    </div>

                    {/* Total Salidas */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500 text-2xl">🗃️</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalSalidas}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Total Salidas</p>
                        <div className="mt-3 flex items-center gap-2 text-xs">
                            <span className="text-green-500 font-bold">{metrics.salidasEsteMes}</span>
                            <span className="text-blue-900 font-semibold">este mes</span>
                        </div>
                    </div>

                    {/* Salidas Bajo Stock */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-red-500/20">
                                <span className="text-red-500 text-2xl">⚠️</span>
                            </div>
                            <span className="text-xs px-2 py-1 rounded-full bg-red-500/20 text-red-600 font-bold">Alerta</span>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.salidasBajoStock}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Salidas Bajo Stock</p>
                        <div className="mt-3 flex items-center gap-2 text-xs">
                            <span className="text-red-500 font-bold">{metrics.salidasBajoStock}</span>
                            <span className="text-blue-900 font-semibold">Requieren atención</span>
                        </div>
                    </div>

                    {/* Total Items Salidos */}
                    <div className="bg-white border border-white backdrop-blur-md rounded-2xl p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-purple-500/20">
                                <span className="text-purple-500 text-2xl">📦</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalItemsSalidos}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Total Items Salidos</p>
                    </div>
                </div>
            </div>

            {/* Panel de Búsqueda y Filtros Avanzados Animados (Estilo Inventario Oficinas) */}
            <div className="px-6 mb-6 relative z-30 overflow-visible">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 shadow-sm overflow-visible">
                    <div className="flex flex-col gap-4">
                        {/* Fila Superior: Buscador Rápido + Acciones (Exportar, Eliminar, Nuevo) */}
                        <div className="flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-3.5 sm:gap-4 pb-4 border-b border-gray-100">
                            {/* Buscador general fluido */}
                            <div className="relative flex-1 min-w-[260px] max-w-2xl search-container">
                                <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    🔍
                                </span>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="🔍 Buscar por consumible, impresora, gerencia o área..."
                                    className="w-full pl-10 pr-10 py-2.5 bg-blue-50/50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition-all shadow-inner"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 font-bold cursor-pointer"
                                        title="Limpiar búsqueda"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {/* Acciones */}
                            <div className="flex flex-wrap items-center justify-start xl:justify-end gap-2.5 sm:gap-3 shrink-0">
                                {/* Select Exportar */}
                                <select
                                    value=""
                                    onChange={(e) => {
                                        if (e.target.value) {
                                            const [fmt, scp] = e.target.value.split(':');
                                            handleExport(fmt, scp);
                                        }
                                    }}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all text-black border-none bg-blue-100 hover:bg-blue-200 active:translate-y-0.5 text-sm cursor-pointer shadow-xs"
                                >
                                    <option value="" disabled>📤 Exportar...</option>
                                    <optgroup label="Exportar Todos">
                                        <option value="excel:all">📊 Excel - Todos los registros</option>
                                        <option value="pdf:all">📄 PDF - Todos los registros</option>
                                    </optgroup>
                                    <optgroup label={`Exportar Seleccionados (${selectedIds.length})`}>
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
                                    <>
                                        <button
                                            type="button"
                                            onClick={handleDeleteSelected}
                                            disabled={selectedIds.length === 0}
                                            className={`inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer ${
                                                selectedIds.length === 0 ? 'opacity-50 cursor-not-allowed' : ''
                                            }`}
                                            style={{
                                                background: 'linear-gradient(135deg, #8a1e1e 0%, #f63b3b 100%)',
                                                boxShadow: '0 4px 12px rgba(246, 59, 59, 0.38)'
                                            }}
                                        >
                                            <span>🗑️</span>
                                            <span>Eliminar Seleccionados ({selectedIds.length})</span>
                                        </button>

                                        <button
                                            type="button"
                                            onClick={handleDeleteAll}
                                            className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer"
                                            style={{
                                                background: 'linear-gradient(135deg, #5e0e0e 0%, #b41818 100%)',
                                                boxShadow: '0 4px 12px rgba(246, 59, 59, 0.3)'
                                            }}
                                        >
                                            <span>⚠️</span>
                                            <span>Eliminar Todo</span>
                                        </button>
                                    </>
                                )}

                                {/* Botón Nuevo Registro */}
                                {permissions.can_create && (
                                    <button
                                        type="button"
                                        onClick={handleOpenCreate}
                                        className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer shrink-0"
                                        style={{
                                            background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                            boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                        }}
                                    >
                                        <span>➕</span>
                                        <span>Nuevo Registro</span>
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Fila de Control: Botón Filtros Avanzados + Badges de Filtros Activos */}
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                                <button
                                    type="button"
                                    onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                        boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                    }}
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
                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors cursor-pointer"
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
                                        <span>Consumible: {(catalogs.cartuchos || []).find(c => String(c.id) === String(filterCartucho))?.cartucho || filterCartucho}</span>
                                        <button type="button" onClick={() => setFilterCartucho('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {filterColore && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Color: {(catalogs.colores || []).find(c => String(c.id) === String(filterColore))?.colore || filterColore}</span>
                                        <button type="button" onClick={() => setFilterColore('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {filterImpresora && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Impresora: {(catalogs.impresoras || []).find(i => String(i.id) === String(filterImpresora))?.impresora || filterImpresora}</span>
                                        <button type="button" onClick={() => setFilterImpresora('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {filterGerencia && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Gerencia: {(catalogs.gerencias || []).find(g => String(g.id) === String(filterGerencia))?.gerencia || filterGerencia}</span>
                                        <button type="button" onClick={() => setFilterGerencia('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {filterArea && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Área: {(catalogs.areas || []).find(a => String(a.id) === String(filterArea))?.area || filterArea}</span>
                                        <button type="button" onClick={() => setFilterArea('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {filterFabricante && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Tipo: {(catalogs.fabricantes || []).find(f => String(f.id) === String(filterFabricante))?.fabricante || filterFabricante}</span>
                                        <button type="button" onClick={() => setFilterFabricante('')} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {(fechaDesde || fechaHasta) && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Salida: {fechaDesde || '...'} a {fechaHasta || '...'}</span>
                                        <button type="button" onClick={() => { setFechaDesde(''); setFechaHasta(''); }} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                                {(fechaIngresoDesde || fechaIngresoHasta) && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Factura: {fechaIngresoDesde || '...'} a {fechaIngresoHasta || '...'}</span>
                                        <button type="button" onClick={() => { setFechaIngresoDesde(''); setFechaIngresoHasta(''); }} className="hover:text-red-600 font-bold ml-1 cursor-pointer">✕</button>
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Fila 3: Cajón colapsable animado tipo acordeón */}
                        <div
                            className={`grid transition-all duration-500 ease-in-out ${
                                showAdvancedFilters
                                    ? 'grid-rows-[1fr] opacity-100 mt-3 visible'
                                    : 'grid-rows-[0fr] opacity-0 invisible mt-0 pointer-events-none'
                            }`}
                        >
                            <div className={`${showAdvancedFilters ? 'overflow-visible' : 'overflow-hidden'} transition-all duration-300`}>
                                <div className="p-5 bg-gradient-to-br from-blue-50/70 to-slate-50/90 rounded-2xl border border-blue-100 shadow-md relative z-40 overflow-visible">
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        {/* Consumible */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                🗃️ Consumible
                                            </label>
                                            <select
                                                value={filterCartucho}
                                                onChange={(e) => setFilterCartucho(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todos los consumibles</option>
                                                {(catalogs.cartuchos || []).map(c => (
                                                    <option key={c.id} value={c.id}>{c.cartucho}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Impresora */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                🖨️ Impresora
                                            </label>
                                            <select
                                                value={filterImpresora}
                                                onChange={(e) => setFilterImpresora(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todas las impresoras</option>
                                                {(catalogs.impresoras || []).map(i => (
                                                    <option key={i.id} value={i.id}>{i.impresora}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Color */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                🎨 Tipo de Consumible (Color)
                                            </label>
                                            <select
                                                value={filterColore}
                                                onChange={(e) => setFilterColore(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todos los tipos</option>
                                                {(catalogs.colores || []).map(col => (
                                                    <option key={col.id} value={col.id}>{col.colore}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Fabricante / Tipo Impresora */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                🏭 Tipo de Impresora
                                            </label>
                                            <select
                                                value={filterFabricante}
                                                onChange={(e) => setFilterFabricante(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todos los tipos</option>
                                                {(catalogs.fabricantes || []).map(f => (
                                                    <option key={f.id} value={f.id}>{f.fabricante}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Gerencia */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                🏢 Gerencia
                                            </label>
                                            <select
                                                value={filterGerencia}
                                                onChange={(e) => setFilterGerencia(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todas las gerencias</option>
                                                {(catalogs.gerencias || []).map(g => (
                                                    <option key={g.id} value={g.id}>{g.gerencia}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Área */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                📍 Área
                                            </label>
                                            <select
                                                value={filterArea}
                                                onChange={(e) => setFilterArea(e.target.value)}
                                                className="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                            >
                                                <option value="">Todas las áreas</option>
                                                {(catalogs.areas || []).map(a => (
                                                    <option key={a.id} value={a.id}>{a.area}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Rango Fecha Salida */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                📅 Fecha Salida (Desde / Hasta)
                                            </label>
                                            <div className="grid grid-cols-2 gap-1.5">
                                                <input
                                                    type="date"
                                                    value={fechaDesde}
                                                    onChange={(e) => setFechaDesde(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                                />
                                                <input
                                                    type="date"
                                                    value={fechaHasta}
                                                    onChange={(e) => setFechaHasta(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                                />
                                            </div>
                                        </div>

                                        {/* Rango Fecha Factura */}
                                        <div>
                                            <label className="block text-xs font-bold text-blue-900 mb-1">
                                                📄 Fecha Factura (Desde / Hasta)
                                            </label>
                                            <div className="grid grid-cols-2 gap-1.5">
                                                <input
                                                    type="date"
                                                    value={fechaIngresoDesde}
                                                    onChange={(e) => setFechaIngresoDesde(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
                                                />
                                                <input
                                                    type="date"
                                                    value={fechaIngresoHasta}
                                                    onChange={(e) => setFechaIngresoHasta(e.target.value)}
                                                    className="w-full bg-white border border-slate-200 rounded-xl px-2 py-1.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600 shadow-xs"
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

            {/* Listado y Tabla de Salidas */}
            <div className="px-6 mb-8">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm overflow-hidden">
                    {loading ? (
                        <div className="p-12 text-center">
                            <div className="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                            <p className="text-sm font-semibold text-slate-700">Cargando salidas de consumibles...</p>
                        </div>
                    ) : error ? (
                        <div className="p-8 text-center text-red-600">
                            <p className="font-semibold">{error}</p>
                            <button
                                type="button"
                                onClick={() => fetchSalidas(pagination.current_page)}
                                className="mt-3 px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700"
                            >
                                Reintentar
                            </button>
                        </div>
                    ) : salidas.length === 0 ? (
                        <div className="p-12 text-center">
                            <div className="w-16 h-16 mx-auto mb-3 text-4xl">📭</div>
                            <h3 className="text-base font-bold text-slate-800">No se encontraron salidas registradas</h3>
                            <p className="text-xs text-slate-500 mt-1">Prueba ajustando los filtros o registra una nueva salida.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr className="border-b border-slate-100 bg-slate-50/75 text-slate-600 uppercase font-bold text-[10px] tracking-wider">
                                        <th className="p-3 text-center w-10">
                                            <input
                                                type="checkbox"
                                                checked={salidas.length > 0 && selectedIds.length === salidas.length}
                                                onChange={handleSelectAll}
                                                className="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                            />
                                        </th>
                                        <th className="p-3">Fecha Salida</th>
                                        <th className="p-3">Fecha Factura</th>
                                        <th className="p-3">Consumible</th>
                                        <th className="p-3">Color</th>
                                        <th className="p-3">Tipo Impresora</th>
                                        <th className="p-3">Impresora</th>
                                        <th className="p-3">Gerencia</th>
                                        <th className="p-3">Área</th>
                                        <th className="p-3 text-center">Cantidad</th>
                                        <th className="p-3 text-center">Unidades Disp.</th>
                                        <th className="p-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 text-slate-700 font-medium">
                                    {salidas.map(salida => (
                                        <tr key={salida.id} className="hover:bg-blue-50/40 transition-colors">
                                            <td className="p-3 text-center">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedIds.includes(salida.id)}
                                                    onChange={() => handleToggleSelect(salida.id)}
                                                    className="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                />
                                            </td>
                                            <td className="p-3 font-bold text-blue-900 whitespace-nowrap">
                                                {salida.fecha_salida ? salida.fecha_salida.split('T')[0] : 'N/A'}
                                            </td>
                                            <td className="p-3 text-slate-500 whitespace-nowrap">
                                                {salida.inventario_impresoras?.fecha_ingreso ? salida.inventario_impresoras.fecha_ingreso.split('T')[0] : 'N/A'}
                                            </td>
                                            <td className="p-3 font-semibold text-slate-800">
                                                {salida.cartuchos?.cartucho || 'N/A'}
                                            </td>
                                            <td className="p-3">
                                                <span className="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                                    {salida.colores?.colore || 'N/A'}
                                                </span>
                                            </td>
                                            <td className="p-3">
                                                {salida.fabricantes?.fabricante || 'N/A'}
                                            </td>
                                            <td className="p-3 font-semibold text-slate-800">
                                                {salida.impresoras?.impresora || 'N/A'}
                                            </td>
                                            <td className="p-3">
                                                {salida.gerencias?.gerencia || 'N/A'}
                                            </td>
                                            <td className="p-3 text-slate-500">
                                                {salida.areas?.area || 'N/A'}
                                            </td>
                                            <td className="p-3 text-center">
                                                <span className="inline-block px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    {salida.cantidad}
                                                </span>
                                            </td>
                                            <td className="p-3 text-center">
                                                <span className="inline-block px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    {salida.historico_unidades_disponibles ?? 'N/A'}
                                                </span>
                                            </td>
                                            <td className="p-3 text-right whitespace-nowrap">
                                                <div className="inline-flex items-center gap-1.5 justify-end">
                                                    {permissions.can_edit && (
                                                        <button
                                                            type="button"
                                                            onClick={() => handleOpenEdit(salida)}
                                                            className="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors cursor-pointer"
                                                            title="Editar salida"
                                                        >
                                                            ✏️
                                                        </button>
                                                    )}
                                                    {permissions.can_destroy && (
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDelete(salida)}
                                                            className="p-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors cursor-pointer"
                                                            title="Eliminar salida"
                                                        >
                                                            🗑️
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {/* Paginación */}
                    {pagination.last_page > 1 && (
                        <div className="p-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <span className="text-slate-500 font-semibold">
                                Mostrando página {pagination.current_page} de {pagination.last_page} ({pagination.total} salidas)
                            </span>
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => fetchSalidas(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1}
                                    className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
                                >
                                    Anterior
                                </button>
                                <button
                                    type="button"
                                    onClick={() => fetchSalidas(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= pagination.last_page}
                                    className="px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
                                >
                                    Siguiente
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal de Registro y Edición (Mismo diseño de Inventario Oficinas) */}
            {modalOpen && createPortal(
                <div
                    className="fixed inset-0 z-[99999999] flex items-center justify-center p-4 bg-slate-900/65 backdrop-blur-md animate-modal-backdrop"
                    style={{
                        position: 'fixed',
                        inset: 0,
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100%',
                        height: '100%',
                        zIndex: 99999999,
                        backgroundColor: 'rgba(15, 23, 42, 0.65)',
                        backdropFilter: 'blur(8px)',
                        WebkitBackdropFilter: 'blur(8px)'
                    }}
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
                                    {modalMode === 'create' ? 'Registrar Salida de Consumible' : 'Editar Salida de Consumible'}
                                </h3>
                                <p className="text-xs text-blue-100 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Selecciona el consumible de inventario y la cantidad a retirar'
                                        : `Actualizando registro de salida #${selectedSalida?.id || ''}`}
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

                            {/* Consumible en Inventario */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    🗃️ Seleccionar Consumible de Inventario <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={formData.inventario_impresora_id}
                                    onChange={(e) => setFormData(prev => ({ ...prev, inventario_impresora_id: e.target.value }))}
                                    className={`w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                        formErrors.inventario_impresora_id ? 'border-red-400 bg-red-50' : 'border-white/20'
                                    }`}
                                    required
                                    disabled={submitting}
                                >
                                    <option value="">-- Selecciona un consumible disponible --</option>
                                    {(catalogs.inventario_impresoras || []).map(inv => {
                                        const nombreConsumible = inv.cartuchos?.cartucho || 'Consumible';
                                        const nombreImpresora = inv.impresoras?.impresora || 'Impresora';
                                        const nombreColor = inv.colores?.colore || 'N/A';
                                        const disp = inv.unidades_disponibles;
                                        return (
                                            <option key={inv.id} value={inv.id}>
                                                {nombreConsumible} | {nombreImpresora} ({nombreColor}) — Disp: {disp}
                                            </option>
                                        );
                                    })}
                                </select>
                                {formErrors.inventario_impresora_id && (
                                    <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.inventario_impresora_id[0] || formErrors.inventario_impresora_id}</p>
                                )}
                            </div>

                            {/* Tarjeta Informativa del Consumible Seleccionado */}
                            {selectedInventario && (
                                <div className="p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200 text-xs space-y-1.5 animate-fadeIn">
                                    <div className="flex items-center justify-between">
                                        <span className="font-bold text-blue-950 text-sm">
                                            {selectedInventario.cartuchos?.cartucho || 'Consumible'}
                                        </span>
                                        <span className="px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            {selectedInventario.unidades_disponibles} unidades disponibles
                                        </span>
                                    </div>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-600 pt-1">
                                        <p><b>🖨️ Impresora:</b> {selectedInventario.impresoras?.impresora || 'N/A'}</p>
                                        <p><b>🎨 Color:</b> {selectedInventario.colores?.colore || 'N/A'}</p>
                                        <p><b>🏢 Gerencia:</b> {selectedInventario.gerencias?.gerencia || 'N/A'}</p>
                                        <p><b>📍 Área:</b> {selectedInventario.areas?.area || 'N/A'}</p>
                                    </div>
                                </div>
                            )}

                            {/* Cantidad a retirar */}
                            <div>
                                <label className="block text-xs font-bold text-blue-900 mb-1">
                                    📦 Cantidad a Retirar <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min="1"
                                    max={selectedInventario ? selectedInventario.unidades_disponibles + (modalMode === 'edit' && selectedSalida ? selectedSalida.cantidad : 0) : undefined}
                                    value={formData.cantidad}
                                    onChange={(e) => setFormData(prev => ({ ...prev, cantidad: e.target.value }))}
                                    className={`w-full px-3.5 py-2.5 text-xs font-bold rounded-xl border bg-blue-100/70 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all ${
                                        formErrors.cantidad ? 'border-red-400 bg-red-50' : 'border-white/20'
                                    }`}
                                    required
                                    disabled={submitting}
                                />
                                {formErrors.cantidad && (
                                    <p className="text-[11px] text-red-500 mt-1 font-medium">{formErrors.cantidad[0] || formErrors.cantidad}</p>
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
                                    className="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                        boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
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
                </div>,
                document.body
            )}
        </div>
    );
}
