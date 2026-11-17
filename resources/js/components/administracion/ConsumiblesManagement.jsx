import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import SearchableSelect from '../ui/SearchableSelect';

export default function ConsumiblesManagement() {
    const [cartuchos, setCartuchos] = useState([]);
    const [catalogs, setCatalogs] = useState({
        impresoras: [],
        colores: [],
        fabricantes: [],
        all_cartuchos: [],
        gerencias: []
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Filtros
    const [search, setSearch] = useState('');
    const [cartuchoFilter, setCartuchoFilter] = useState('');
    const [impresoraFilter, setImpresoraFilter] = useState('');
    const [colorFilter, setColorFilter] = useState('');
    const [fabricanteFilter, setFabricanteFilter] = useState('');
    const [gerenciaFilter, setGerenciaFilter] = useState('');
    const [sortDirection, setSortDirection] = useState('desc');

    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true
    });

    // Modal state (Create & Edit)
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [selectedItem, setSelectedItem] = useState(null);
    const [formData, setFormData] = useState({
        cartucho: '',
        impresora_id: '',
        fabricante_id: '',
        colore_id: '',
        stock_min: 0
    });
    const [formErrors, setFormErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    // Bloquear scroll de la página cuando el modal está abierto
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

    // Cargar datos desde la API
    const fetchConsumibles = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: page,
                sort: 'id',
                direction: sortDirection
            });
            if (search.trim()) params.append('search', search.trim());
            if (cartuchoFilter) params.append('consumible', cartuchoFilter);
            if (impresoraFilter) params.append('nombre-impresora', impresoraFilter);
            if (colorFilter) params.append('color', colorFilter);
            if (fabricanteFilter) params.append('tipo', fabricanteFilter);
            if (gerenciaFilter) params.append('gerencia_id', gerenciaFilter);

            const res = await fetch(`/administracion/consumibles?${params.toString()}`, {
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
            const resData = json.data?.cartuchos || {};

            setCartuchos(resData.data || []);
            setPagination({
                current_page: resData.current_page || 1,
                last_page: resData.last_page || 1,
                total: resData.total || 0,
                per_page: resData.per_page || 15
            });

            if (json.data?.catalogs) {
                setCatalogs(json.data.catalogs);
            }

            if (json.data?.permissions) {
                setPermissions(json.data.permissions);
            }
        } catch (err) {
            console.error('Error fetching consumibles:', err);
            setError(err.message || 'No se pudieron cargar los consumibles.');
        } finally {
            setLoading(false);
        }
    }, [search, cartuchoFilter, impresoraFilter, colorFilter, fabricanteFilter, gerenciaFilter, sortDirection]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchConsumibles(1);
        }, 250);
        return () => clearTimeout(timer);
    }, [search, cartuchoFilter, impresoraFilter, colorFilter, fabricanteFilter, gerenciaFilter, sortDirection, fetchConsumibles]);

    // Opciones para Selects Filtrables
    const cartuchoOptions = useMemo(() => {
        return (catalogs.all_cartuchos || []).map(c => ({
            id: c.id,
            label: c.cartucho,
            badge: c.colore?.colore || 'Consumible'
        }));
    }, [catalogs.all_cartuchos]);

    const impresoraOptions = useMemo(() => {
        return (catalogs.impresoras || []).map(imp => ({
            id: imp.id,
            label: imp.impresora,
            badge: 'Impresora'
        }));
    }, [catalogs.impresoras]);

    const colorOptions = useMemo(() => {
        return (catalogs.colores || []).map(c => ({
            id: c.id,
            label: c.colore,
            badge: 'Color'
        }));
    }, [catalogs.colores]);

    const fabricanteOptions = useMemo(() => {
        return (catalogs.fabricantes || []).map(f => ({
            id: f.id,
            label: f.fabricante,
            badge: 'Tipo Impresora'
        }));
    }, [catalogs.fabricantes]);

    const gerenciaOptions = useMemo(() => {
        return (catalogs.gerencias || []).map(g => ({
            id: g.id,
            label: g.gerencia + (g.area ? ` - ${g.area.area}` : ''),
            badge: g.area?.area || 'Gerencia'
        }));
    }, [catalogs.gerencias]);

    // Impresora seleccionada en el formulario (para previsualizar imagen)
    const selectedFormImpresora = useMemo(() => {
        if (!formData.impresora_id) return null;
        return (catalogs.impresoras || []).find(imp => String(imp.id) === String(formData.impresora_id));
    }, [formData.impresora_id, catalogs.impresoras]);

    // Abrir modal de creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setSelectedItem(null);
        setFormData({
            cartucho: '',
            impresora_id: catalogs.impresoras?.length > 0 ? catalogs.impresoras[0].id : '',
            fabricante_id: catalogs.fabricantes?.length > 0 ? catalogs.fabricantes[0].id : '',
            colore_id: catalogs.colores?.length > 0 ? catalogs.colores[0].id : '',
            stock_min: 0
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir modal de edición
    const handleOpenEdit = (item) => {
        setModalMode('edit');
        setSelectedItem(item);
        setFormData({
            cartucho: item.cartucho || '',
            impresora_id: item.impresora_id || '',
            fabricante_id: item.fabricante_id || '',
            colore_id: item.colore_id || '',
            stock_min: item.stock_min ?? 0
        });
        setFormErrors({});
        setModalOpen(true);
    };

    // Cerrar modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedItem(null);
        setFormData({
            cartucho: '',
            impresora_id: '',
            fabricante_id: '',
            colore_id: '',
            stock_min: 0
        });
        setFormErrors({});
    };

    // Enviar formulario (Crear o Editar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        setSubmitting(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/administracion/consumibles'
                : `/administracion/consumibles/${selectedItem.id}`;

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

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (res.status === 422 && json.errors) {
                    setFormErrors(json.errors);
                    return;
                }
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: modalMode === 'create' ? '¡Creado!' : '¡Actualizado!',
                text: modalMode === 'create'
                    ? 'El consumible fue registrado exitosamente.'
                    : 'El consumible fue actualizado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            handleCloseModal();
            fetchConsumibles(modalMode === 'create' ? 1 : pagination.current_page);
        } catch (err) {
            console.error('Error saving consumible:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el consumible.',
                confirmButtonColor: '#14368b'
            });
        } finally {
            setSubmitting(false);
        }
    };

    // Eliminar registro
    const handleDelete = async (item) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el consumible "${item.cartucho}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'rounded-2xl' }
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/consumibles/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: json.message || 'El consumible fue eliminado correctamente.',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchConsumibles(pagination.current_page);
        } catch (err) {
            console.error('Error deleting consumible:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el consumible.',
                confirmButtonColor: '#14368b'
            });
        }
    };

    const clearFilters = () => {
        setSearch('');
        setCartuchoFilter('');
        setImpresoraFilter('');
        setColorFilter('');
        setFabricanteFilter('');
        setGerenciaFilter('');
    };

    const hasActiveFilters = Boolean(
        search || cartuchoFilter || impresoraFilter || colorFilter || fabricanteFilter || gerenciaFilter
    );

    // Obtener color visual a partir del nombre
    const getColorDot = (colorName = '') => {
        const c = colorName.toLowerCase();
        if (c.includes('negro') || c.includes('black')) return '#000000';
        if (c.includes('cyan') || c.includes('azul') || c.includes('blue')) return '#06b6d4';
        if (c.includes('magenta') || c.includes('rojo') || c.includes('red')) return '#ec4899';
        if (c.includes('amarillo') || c.includes('yellow')) return '#eab308';
        return '#64748b';
    };

    return (
        <div className="w-full">
            {/* Filtros y controles */}
            <div className="sticky top-0 z-30 px-6 mb-6">
                <div className="p-6 bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3 flex-1">
                            {/* Buscador de texto libre */}
                            <div className="relative w-56">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Buscar consumible..."
                                    className="w-full px-4 py-2.5 text-xs font-semibold bg-blue-100/70 border border-white/20 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                />
                                {search && (
                                    <button
                                        type="button"
                                        onClick={() => setSearch('')}
                                        className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 font-bold text-xs"
                                    >
                                        ✕
                                    </button>
                                )}
                            </div>

                            {/* Select Filtrable: Consumible */}
                            <div className="w-56">
                                <SearchableSelect
                                    options={cartuchoOptions}
                                    value={cartuchoFilter}
                                    onChange={setCartuchoFilter}
                                    placeholder="Todos los consumibles"
                                    allLabel="Todos los consumibles"
                                    icon="🖨️"
                                />
                            </div>

                            {/* Select Filtrable: Impresora */}
                            <div className="w-56">
                                <SearchableSelect
                                    options={impresoraOptions}
                                    value={impresoraFilter}
                                    onChange={setImpresoraFilter}
                                    placeholder="Todas las impresoras"
                                    allLabel="Todas las impresoras"
                                    icon="📄"
                                />
                            </div>

                            {/* Select Filtrable: Color */}
                            <div className="w-52">
                                <SearchableSelect
                                    options={colorOptions}
                                    value={colorFilter}
                                    onChange={setColorFilter}
                                    placeholder="Todos los colores"
                                    allLabel="Todos los colores"
                                    icon="🎨"
                                />
                            </div>

                            {/* Select Filtrable: Fabricante */}
                            <div className="w-52">
                                <SearchableSelect
                                    options={fabricanteOptions}
                                    value={fabricanteFilter}
                                    onChange={setFabricanteFilter}
                                    placeholder="Todos los tipos"
                                    allLabel="Todos los tipos"
                                    icon="🏷️"
                                />
                            </div>

                            {/* Select Filtrable: Gerencia */}
                            <div className="w-56">
                                <SearchableSelect
                                    options={gerenciaOptions}
                                    value={gerenciaFilter}
                                    onChange={setGerenciaFilter}
                                    placeholder="Todas las gerencias"
                                    allLabel="Todas las gerencias"
                                    icon="🏢"
                                />
                            </div>

                            {hasActiveFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                >
                                    🗑️ Limpiar
                                </button>
                            )}

                            {/* Selector de ordenamiento */}
                            <div className="flex items-center gap-1 ml-auto">
                                <span className="text-xs text-gray-500 font-medium">Ordenar:</span>
                                <button
                                    type="button"
                                    onClick={() => setSortDirection(prev => prev === 'asc' ? 'desc' : 'asc')}
                                    className="px-2.5 py-1.5 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition-all cursor-pointer"
                                    title="Alternar orden"
                                >
                                    {sortDirection === 'asc' ? 'A ➜ Z' : 'Z ➜ A'}
                                </button>
                            </div>
                        </div>

                        {/* Botón Crear */}
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={handleOpenCreate}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer ml-auto"
                                style={{
                                    background: 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                                    boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)'
                                }}
                            >
                                <span>➕</span>
                                Registrar Consumible
                            </button>
                        )}
                    </div>
                </div>
            </div>

            {/* Listado de Consumibles */}
            <div className="px-6 mb-8">
                {loading && cartuchos.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm animate-pulse">
                        <div className="w-8 h-8 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                        <p className="text-sm font-semibold text-slate-700">Cargando consumibles...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-white border border-red-200 rounded-2xl shadow-sm">
                        <p className="text-red-500 font-semibold mb-3">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchConsumibles(pagination.current_page)}
                            className="px-4 py-2 bg-blue-900 text-white rounded-xl text-xs font-bold hover:bg-blue-800 cursor-pointer"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : cartuchos.length === 0 ? (
                    <div className="p-8 text-center bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-sm">
                        <p className="text-slate-500 font-medium">No se encontraron consumibles con los filtros aplicados</p>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {cartuchos.map((item) => {
                            const colorName = item.colore?.colore || 'N/A';
                            const dotColor = getColorDot(colorName);
                            const imp = item.impresora;
                            const fab = item.fabricante;

                            return (
                                <div
                                    key={item.id}
                                    className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-[1.01] transition-transform shadow-xs"
                                >
                                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                                        {/* Consumible & Color */}
                                        <div className="text-center lg:text-left">
                                            <p className="text-xs text-blue-900 font-semibold mb-1">
                                                CONSUMIBLE <span className="text-blue-500 font-bold ml-1">({String(item.id).padStart(4, '0')})</span>
                                            </p>
                                            <p className="font-semibold text-lg text-black mb-2">
                                                {item.cartucho}
                                            </p>
                                            <div className="flex flex-wrap items-center justify-center lg:justify-start gap-2">
                                                <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                                    <span
                                                        className="w-2.5 h-2.5 rounded-full"
                                                        style={{ backgroundColor: dotColor }}
                                                    ></span>
                                                    <span>{colorName}</span>
                                                </span>
                                                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-900 border border-blue-200">
                                                    Stock Mín: <span className="font-bold">{item.stock_min}</span>
                                                </span>
                                            </div>
                                        </div>

                                        {/* Impresora Compatible & Gerencias */}
                                        <div className="text-center lg:text-left">
                                            <p className="text-xs text-blue-900 font-semibold mb-1">🖨️ Impresora Compatible</p>
                                            <p className="font-semibold text-base text-slate-800 mb-1">
                                                {imp ? imp.impresora : 'Sin asignar'}
                                            </p>
                                            <p className="text-xs text-blue-900 font-semibold mb-1">
                                                Tipo Impresora: <span className="text-slate-600 font-normal">{fab ? fab.fabricante : 'N/A'}</span>
                                            </p>
                                            {imp?.gerencias && imp.gerencias.length > 0 && (
                                                <div className="flex flex-wrap justify-center lg:justify-start gap-1 mt-1">
                                                    {imp.gerencias.map(g => (
                                                        <span
                                                            key={g.id}
                                                            className="text-[10px] bg-green-50 text-green-700 px-2 py-0.5 rounded-full border border-green-200"
                                                        >
                                                            🏢 {g.gerencia} {g.area?.area ? `(${g.area.area})` : ''}
                                                        </span>
                                                    ))}
                                                </div>
                                            )}
                                        </div>

                                        {/* Imagen Impresora & Acciones */}
                                        <div className="text-center lg:text-left flex flex-col items-center lg:items-start">
                                            <p className="text-xs text-blue-900 font-semibold mb-1">📄 Imagen Impresora</p>
                                            <div className="flex justify-center lg:justify-start mb-3">
                                                <div className="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center border border-slate-200 overflow-hidden shadow-xs">
                                                    {imp?.imagen ? (
                                                        <img
                                                            src={`/storage/${imp.imagen}`}
                                                            alt={imp.impresora}
                                                            className="w-20 h-20 object-cover"
                                                            onError={(e) => {
                                                                e.target.style.display = 'none';
                                                                e.target.parentElement.innerHTML = '<span class="text-2xl">🖨️</span>';
                                                            }}
                                                        />
                                                    ) : (
                                                        <span className="text-2xl text-slate-400">🖨️</span>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="flex justify-center lg:justify-start gap-2">
                                                {permissions.can_edit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleOpenEdit(item)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 border text-white border-white/20 hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                    >
                                                        <span>✏️</span>
                                                        Editar
                                                    </button>
                                                )}
                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(item)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-xs hover:bg-red-600 cursor-pointer"
                                                    >
                                                        <span>🗑️</span>
                                                        Eliminar
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
                {pagination.total > 0 && (
                    <div className="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div className="text-xs font-semibold text-slate-600">
                            Mostrando {cartuchos.length} de {pagination.total} consumible(s)
                            {pagination.last_page > 1 && ` (Página ${pagination.current_page} de ${pagination.last_page})`}
                        </div>

                        {pagination.last_page > 1 && (
                            <div className="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onClick={() => fetchConsumibles(pagination.current_page - 1)}
                                    disabled={pagination.current_page <= 1 || loading}
                                    className={`px-3 py-2 rounded-xl text-xs font-bold transition-all ${
                                        pagination.current_page <= 1 || loading
                                            ? 'bg-slate-100 text-slate-300 cursor-not-allowed'
                                            : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200 cursor-pointer'
                                    }`}
                                >
                                    ◀ Anterior
                                </button>

                                {Array.from({ length: Math.min(5, pagination.last_page) }, (_, i) => {
                                    let pageNum;
                                    if (pagination.last_page <= 5) {
                                        pageNum = i + 1;
                                    } else if (pagination.current_page <= 3) {
                                        pageNum = i + 1;
                                    } else if (pagination.current_page >= pagination.last_page - 2) {
                                        pageNum = pagination.last_page - 4 + i;
                                    } else {
                                        pageNum = pagination.current_page - 2 + i;
                                    }

                                    return (
                                        <button
                                            key={pageNum}
                                            type="button"
                                            onClick={() => fetchConsumibles(pageNum)}
                                            disabled={loading}
                                            className={`w-8 h-8 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                                                pagination.current_page === pageNum
                                                    ? 'bg-blue-900 text-white shadow-sm'
                                                    : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200'
                                            }`}
                                        >
                                            {pageNum}
                                        </button>
                                    );
                                })}

                                <button
                                    type="button"
                                    onClick={() => fetchConsumibles(pagination.current_page + 1)}
                                    disabled={pagination.current_page >= pagination.last_page || loading}
                                    className={`px-3 py-2 rounded-xl text-xs font-bold transition-all ${
                                        pagination.current_page >= pagination.last_page || loading
                                            ? 'bg-slate-100 text-slate-300 cursor-not-allowed'
                                            : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200 cursor-pointer'
                                    }`}
                                >
                                    Siguiente ▶
                                </button>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Modal de Crear / Editar usando createPortal con backdrop-blur completo */}
            {modalOpen && createPortal(
                <div
                    className="fixed inset-0 z-[99999999] bg-slate-900/65 backdrop-blur-md flex items-center justify-center p-4 animate-modal-backdrop"
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
                        WebkitBackdropFilter: 'blur(8px)',
                        overflowY: 'auto'
                    }}
                    onClick={(e) => {
                        if (e.target === e.currentTarget) handleCloseModal();
                    }}
                >
                    <div
                        className="bg-white rounded-3xl shadow-2xl border border-white/20 w-full max-w-2xl overflow-hidden transform transition-all animate-modal-content my-8"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Cabecera del Modal */}
                        <div className="bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-900 px-6 py-5 text-white flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lg border border-white/10">
                                    🖨️
                                </div>
                                <div>
                                    <h3 className="text-base font-bold">
                                        {modalMode === 'create' ? 'Registrar Consumible' : 'Editar Consumible'}
                                    </h3>
                                    <p className="text-xs text-blue-200">
                                        {modalMode === 'create'
                                            ? 'Registra un nuevo consumible de impresora en el sistema'
                                            : `Modificando registro #${String(selectedItem?.id || '').padStart(4, '0')}`}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white transition-all text-xs font-bold"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="p-6 space-y-5 max-h-[calc(85vh-120px)] overflow-y-auto">
                            {/* Nombre del Consumible */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                    Consumible / Modelo <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={formData.cartucho}
                                    onChange={(e) => setFormData({ ...formData, cartucho: e.target.value })}
                                    placeholder="Ej: Toner HP 58A (CF258A)"
                                    required
                                    autoFocus
                                    className={`w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 transition-all ${
                                        formErrors.cartucho ? 'border-red-400 bg-red-50' : 'border-slate-200'
                                    }`}
                                />
                                {formErrors.cartucho && (
                                    <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.cartucho[0]}</p>
                                )}
                            </div>

                            {/* Impresora Compatible & Preview */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                                <div className="md:col-span-2">
                                    <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                        Impresora Compatible <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.impresora_id}
                                        onChange={(e) => setFormData({ ...formData, impresora_id: e.target.value })}
                                        required
                                        className="w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800"
                                    >
                                        <option value="">Seleccione una impresora...</option>
                                        {(catalogs.impresoras || []).map(imp => (
                                            <option key={imp.id} value={imp.id}>
                                                {imp.impresora}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.impresora_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.impresora_id[0]}</p>
                                    )}
                                </div>

                                {/* Preview de imagen de impresora */}
                                <div className="flex flex-col items-center justify-center p-2 border border-slate-200 rounded-2xl bg-slate-50 min-h-[90px]">
                                    {selectedFormImpresora?.imagen ? (
                                        <img
                                            src={`/storage/${selectedFormImpresora.imagen}`}
                                            alt={selectedFormImpresora.impresora}
                                            className="h-16 object-contain rounded-lg"
                                        />
                                    ) : (
                                        <div className="text-center text-slate-400 text-[11px]">
                                            <span className="text-xl block">🖨️</span>
                                            <span>Sin imagen</span>
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Tipo Impresora / Fabricante & Tipo Consumible / Color */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                        Tipo Impresora / Fabricante <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.fabricante_id}
                                        onChange={(e) => setFormData({ ...formData, fabricante_id: e.target.value })}
                                        required
                                        className="w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800"
                                    >
                                        <option value="">Seleccione un fabricante...</option>
                                        {(catalogs.fabricantes || []).map(f => (
                                            <option key={f.id} value={f.id}>
                                                {f.fabricante}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.fabricante_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.fabricante_id[0]}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                        Tipo Consumible / Color <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.colore_id}
                                        onChange={(e) => setFormData({ ...formData, colore_id: e.target.value })}
                                        required
                                        className="w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800"
                                    >
                                        <option value="">Seleccione un color...</option>
                                        {(catalogs.colores || []).map(c => (
                                            <option key={c.id} value={c.id}>
                                                {c.colore}
                                            </option>
                                        ))}
                                    </select>
                                    {formErrors.colore_id && (
                                        <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.colore_id[0]}</p>
                                    )}
                                </div>
                            </div>

                            {/* Stock Mínimo */}
                            <div>
                                <label className="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">
                                    Stock Mínimo Requerido <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min="0"
                                    value={formData.stock_min}
                                    onChange={(e) => setFormData({ ...formData, stock_min: Number(e.target.value) })}
                                    required
                                    className={`w-full px-4 py-2.5 text-xs font-semibold bg-blue-50/50 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 transition-all ${
                                        formErrors.stock_min ? 'border-red-400 bg-red-50' : 'border-slate-200'
                                    }`}
                                />
                                {formErrors.stock_min && (
                                    <p className="text-[11px] text-red-500 mt-1 font-semibold">{formErrors.stock_min[0]}</p>
                                )}
                            </div>

                            {/* Botones de acción */}
                            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
                                    disabled={submitting}
                                    className="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all cursor-pointer"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold transition-all text-xs uppercase tracking-wider text-white shadow-md active:translate-y-0.5 cursor-pointer disabled:opacity-50"
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
                                            <span>{modalMode === 'create' ? 'Guardar' : 'Actualizar'}</span>
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
