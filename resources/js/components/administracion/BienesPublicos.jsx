import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import { createPortal } from 'react-dom';
import Swal from 'sweetalert2';
import JsBarcode from 'jsbarcode';
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
    icon = '📋'
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
                    {selectedOption?.colorHex && (
                        <span
                            className="w-3 h-3 rounded-full border border-black/20 flex-shrink-0 shadow-inner"
                            style={{ backgroundColor: selectedOption.colorHex }}
                        />
                    )}
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
                            <div className="px-3 py-2 text-center text-xs text-slate-400">
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
                                        <div className="flex items-center gap-1.5 truncate">
                                            {opt.colorHex && (
                                                <span
                                                    className="w-3 h-3 rounded-full border border-black/20 flex-shrink-0 shadow-inner"
                                                    style={{ backgroundColor: opt.colorHex }}
                                                />
                                            )}
                                            <span className="truncate">{opt.label}</span>
                                        </div>
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

const PRESET_COLORS = [
    { name: 'NEGRO', hex: '#000000' },
    { name: 'BLANCO', hex: '#FFFFFF' },
    { name: 'GRIS', hex: '#6B7280' },
    { name: 'PLATA', hex: '#C0C0C0' },
    { name: 'AZUL', hex: '#1D4ED8' },
    { name: 'AZUL MARINO', hex: '#1E3A8A' },
    { name: 'ROJO', hex: '#DC2626' },
    { name: 'VERDE', hex: '#16A34A' },
    { name: 'AMARILLO', hex: '#EAB308' },
    { name: 'NARANJA', hex: '#EA580C' },
    { name: 'MARRON', hex: '#8B4513' },
    { name: 'BEIGE', hex: '#F5F5DC' },
    { name: 'DORADO', hex: '#D97706' },
    { name: 'COBRE', hex: '#B87333' }
];

export default function BienesPublicos() {
    const [bienes, setBienes] = useState([]);
    const [metrics, setMetrics] = useState({
        totalBienes: 0,
        bienesEnUso: 0,
        ultimoBien: null
    });
    const [catalogs, setCatalogs] = useState({
        n_internos: [],
        descripciones: [],
        unidad_administrativas: [],
        usuarios_de_uso: [],
        usuarios_administrativos: [],
        estado_del_usos: [],
        forma_adquisiciones: [],
        marcas: [],
        modelos: [],
        marcas_modelos: {},
        condiciones: [],
        colors: []
    });
    const [pagination, setPagination] = useState({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [permissions, setPermissions] = useState({
        can_create: true,
        can_edit: true,
        can_destroy: true,
        can_export: true
    });

    // Filtros combinados simultáneos
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [busqueda, setBusqueda] = useState('');
    const [filterUnidadId, setFilterUnidadId] = useState('');
    const [filterUsuarioAdminId, setFilterUsuarioAdminId] = useState('');
    const [filterUsuarioUsoId, setFilterUsuarioUsoId] = useState('');
    const [filterEstadoUsoId, setFilterEstadoUsoId] = useState('');
    const [filterCondicionId, setFilterCondicionId] = useState('');
    const [filterColorId, setFilterColorId] = useState('');
    const [filterFormaAdquisicionId, setFilterFormaAdquisicionId] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [fechaHasta, setFechaHasta] = useState('');

    // Selección múltiple para exportar seleccionados
    const [selectedIds, setSelectedIds] = useState([]);

    const toggleSelect = (id) => {
        setSelectedIds(prev => prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]);
    };

    const toggleSelectAll = () => {
        if (selectedIds.length === bienes.length && bienes.length > 0) {
            setSelectedIds([]);
        } else {
            setSelectedIds(bienes.map(b => b.id));
        }
    };

    // Modal de Código QR / Barras
    const [selectedQrBien, setSelectedQrBien] = useState(null);
    const [qrModalOpen, setQrModalOpen] = useState(false);

    // Modal de Crear y Editar Bien Público
    const [modalOpen, setModalOpen] = useState(false);
    const [modalMode, setModalMode] = useState('create'); // 'create' | 'edit'
    const [modalCodeType, setModalCodeType] = useState('qr'); // 'qr' | 'barcode'
    const [selectedBien, setSelectedBien] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [formErrors, setFormErrors] = useState({});

    // Cascada de selects para el modal (Unidad -> Usuario Administrativo -> Usuario de Uso)
    const [modalUsuariosAdministrativos, setModalUsuariosAdministrativos] = useState([]);
    const [modalUsuariosDeUso, setModalUsuariosDeUso] = useState([]);
    const [loadingUsuariosAdmin, setLoadingUsuariosAdmin] = useState(false);
    const [loadingUsuariosUso, setLoadingUsuariosUso] = useState(false);

    // Sub-Modal Rápido para Crear Nuevo Color desde el formulario de Bienes (exacto a Documentos)
    const [quickColorModalOpen, setQuickColorModalOpen] = useState(false);
    const [quickColorName, setQuickColorName] = useState('');
    const [quickColorHex, setQuickColorHex] = useState('#14368b');
    const [quickColorSubmitting, setQuickColorSubmitting] = useState(false);
    const [quickColorError, setQuickColorError] = useState(null);

    // Modal de Escaneo con Cámara (Serial, N° Interno, Código Identificador o Búsqueda)
    const [cameraModalOpen, setCameraModalOpen] = useState(false);
    const [cameraTargetField, setCameraTargetField] = useState('serial');
    const [cameraModalTitle, setCameraModalTitle] = useState('Escanear con Cámara');

    const openCameraScanner = (target = 'serial', customTitle = null) => {
        setCameraTargetField(target);
        if (customTitle) {
            setCameraModalTitle(customTitle);
        } else if (target === 'serial') {
            setCameraModalTitle('Escanear Serial del Bien');
        } else if (target === 'n_interno_bien') {
            setCameraModalTitle('Escanear N° Interno del Bien');
        } else if (target === 'qr_code') {
            setCameraModalTitle(modalCodeType === 'barcode' ? 'Escanear Código de Barras' : 'Escanear Código QR');
        } else if (target === 'search') {
            setCameraModalTitle('Escanear QR o Código de Barras');
        }
        setCameraModalOpen(true);
    };

    const handleCameraScanSuccess = (code, target) => {
        const cleanCode = (code || '').trim();
        if (!cleanCode) return;

        if (target === 'search') {
            setCameraModalOpen(false);
            const matched = (bienes || []).find(b =>
                String(b.serial || '').toLowerCase() === cleanCode.toLowerCase() ||
                String(b.n_interno_bien || '').toLowerCase() === cleanCode.toLowerCase() ||
                String(b.qr_code || '').includes(cleanCode) ||
                String(b.id) === cleanCode
            );

            if (matched) {
                Swal.fire({
                    title: '¡Bien Encontrado!',
                    html: `
                        <div class="text-left text-sm space-y-1.5 p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <p><strong>Descripción:</strong> ${matched.descripcion || 'S/D'}</p>
                            <p><strong>N° Interno:</strong> <span class="font-mono text-blue-900 font-bold">${matched.n_interno_bien || 'S/I'}</span></p>
                            <p><strong>Serial:</strong> <span class="font-mono text-slate-700">${matched.serial || 'S/S'}</span></p>
                            <p><strong>Marca / Modelo:</strong> ${matched.marca || ''} ${matched.modelo || ''}</p>
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#1e3a8a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '✏️ Editar este Bien',
                    cancelButtonText: 'Filtrar en la lista'
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleOpenEdit(matched);
                    } else {
                        setSearch(cleanCode);
                    }
                });
            } else {
                setSearch(cleanCode);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: `Filtrado por: ${cleanCode}`,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        } else {
            if (target === 'serial') {
                setFormData(prev => ({ ...prev, serial: cleanCode }));
            } else if (target === 'n_interno_bien') {
                setFormData(prev => ({ ...prev, n_interno_bien: cleanCode }));
            } else if (target === 'qr_code') {
                if (modalCodeType === 'barcode') {
                    setFormData(prev => ({ ...prev, qr_code: `barcode:${cleanCode}` }));
                } else {
                    setFormData(prev => ({ ...prev, qr_code: cleanCode }));
                }
            }

            setCameraModalOpen(false);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Código asignado: ${cleanCode}`,
                showConfirmButton: false,
                timer: 2500
            });
        }
    };

    const initialFormData = {
        sede: 'CODECYT S.A',
        unidad_administrativa_id: '',
        usuario_administrativo_id: '',
        usuario_de_uso_id: '',
        n_interno_bien: '',
        descripcion: '',
        forma_adquisicion_id: '',
        fecha_adquisicion: new Date().toISOString().split('T')[0],
        n_factura: '',
        valor_factura: '0.00',
        moneda: 'Bs.',
        estado_del_uso_id: '',
        condicion_id: '',
        marca: '',
        modelo: '',
        color_id: '',
        serial: '',
        comentario: '',
        fecha_comentario: new Date().toISOString().split('T')[0],
        qr_code: ''
    };

    const [formData, setFormData] = useState(initialFormData);

    // Listas filtradas dependientes de la unidad seleccionada
    const filteredUsuariosAdministrativos = useMemo(() => {
        if (!filterUnidadId) return catalogs.usuarios_administrativos || [];
        return (catalogs.usuarios_administrativos || []).filter(u =>
            String(u.unidad_administrativa_id) === String(filterUnidadId)
        );
    }, [catalogs.usuarios_administrativos, filterUnidadId]);

    const filteredUsuariosDeUso = useMemo(() => {
        if (!filterUnidadId) return catalogs.usuarios_de_uso || [];
        return (catalogs.usuarios_de_uso || []).filter(u =>
            String(u.unidad_administrativa_id) === String(filterUnidadId)
        );
    }, [catalogs.usuarios_de_uso, filterUnidadId]);

    // Opciones formateadas para los SearchableSelect
    const unidadesOptions = useMemo(() => {
        return (catalogs.unidad_administrativas || []).map(u => ({
            id: u.id,
            label: u.name || u.unidad_administrativa
        }));
    }, [catalogs.unidad_administrativas]);

    const usuariosAdminOptions = useMemo(() => {
        return filteredUsuariosAdministrativos.map(u => ({
            id: u.id,
            label: u.usuario_administrativo || u.name
        }));
    }, [filteredUsuariosAdministrativos]);

    const usuariosUsoOptions = useMemo(() => {
        return filteredUsuariosDeUso.map(u => ({
            id: u.id,
            label: u.usuario_de_uso || u.name
        }));
    }, [filteredUsuariosDeUso]);

    const estadosUsoOptions = useMemo(() => {
        return (catalogs.estado_del_usos || []).map(e => ({
            id: e.id,
            label: e.estado_del_uso || e.nombre
        }));
    }, [catalogs.estado_del_usos]);

    const condicionesOptions = useMemo(() => {
        return (catalogs.condiciones || []).map(c => ({
            id: c.id,
            label: c.condicion || c.nombre
        }));
    }, [catalogs.condiciones]);

    const colorsOptions = useMemo(() => {
        return (catalogs.colors || []).map(c => ({
            id: c.id,
            label: c.color || c.nombre,
            colorHex: c.codigo_hex
        }));
    }, [catalogs.colors]);

    const formasAdquisicionOptions = useMemo(() => {
        return (catalogs.forma_adquisiciones || []).map(f => ({
            id: f.id,
            label: f.forma_adquisicion || f.nombre
        }));
    }, [catalogs.forma_adquisiciones]);

    const activeFiltersCount = (filterUnidadId ? 1 : 0) +
        (filterUsuarioAdminId ? 1 : 0) +
        (filterUsuarioUsoId ? 1 : 0) +
        (filterEstadoUsoId ? 1 : 0) +
        (filterCondicionId ? 1 : 0) +
        (filterColorId ? 1 : 0) +
        (filterFormaAdquisicionId ? 1 : 0) +
        (fechaDesde || fechaHasta ? 1 : 0);

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

    // Cargar datos
    const fetchBienes = useCallback(async (page = 1) => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({ page });

            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterUnidadId) params.append('unidad_administrativa_id', filterUnidadId);
            if (filterUsuarioAdminId) params.append('usuario_administrativo_id', filterUsuarioAdminId);
            if (filterUsuarioUsoId) params.append('usuario_de_uso_id', filterUsuarioUsoId);
            if (filterEstadoUsoId) params.append('estado_del_uso_id', filterEstadoUsoId);
            if (filterCondicionId) params.append('condicion_id', filterCondicionId);
            if (filterColorId) params.append('color_id', filterColorId);
            if (filterFormaAdquisicionId) params.append('forma_adquisicion_id', filterFormaAdquisicionId);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);

            const response = await fetch(`/api/administracion/bienes_publicos?${params.toString()}`, {
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
            const paginated = resData.bienes || {};

            setBienes(paginated.data || []);
            setPagination({
                current_page: paginated.current_page || 1,
                last_page: paginated.last_page || 1,
                total: paginated.total || 0,
                per_page: paginated.per_page || 10
            });

            if (resData.metrics) {
                setMetrics(resData.metrics);
            }
            if (resData.catalogs) {
                setCatalogs(resData.catalogs);
            }
            if (resData.permissions) {
                setPermissions(resData.permissions);
            }
        } catch (err) {
            console.error('Error fetching bienes:', err);
            setError(err.message || 'No se pudieron cargar los bienes.');
        } finally {
            setLoading(false);
        }
    }, [busqueda, filterUnidadId, filterUsuarioAdminId, filterUsuarioUsoId, filterEstadoUsoId, filterCondicionId, filterColorId, filterFormaAdquisicionId, fechaDesde, fechaHasta]);

    useEffect(() => {
        const timer = setTimeout(() => {
            fetchBienes(1);
        }, 300);
        return () => clearTimeout(timer);
    }, [fetchBienes]);

    // Bloquear scroll de fondo cuando haya un modal abierto
    useEffect(() => {
        if (modalOpen || qrModalOpen || quickColorModalOpen || cameraModalOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [modalOpen, qrModalOpen, quickColorModalOpen, cameraModalOpen]);

    // Limpiar filtros
    const handleClearFilters = () => {
        setBusqueda('');
        setFilterUnidadId('');
        setFilterUsuarioAdminId('');
        setFilterUsuarioUsoId('');
        setFilterEstadoUsoId('');
        setFilterCondicionId('');
        setFilterColorId('');
        setFilterFormaAdquisicionId('');
        setFechaDesde('');
        setFechaHasta('');
        setShowAdvancedFilters(false);
    };

    // Formatear Fecha d/m/Y
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
    };

    // Formatear Fecha y Hora d/m/Y H:i
    const formatDateTime = (dateString) => {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    // Formato de Moneda Bs.
    const formatCurrency = (val) => {
        const num = parseFloat(val);
        if (isNaN(num)) return '0,00';
        return num.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Obtener Emoji representativo de la descripción (Exacto a Biene.php getIconForDescription)
    const getIconForDescription = (desc) => {
        const d = (desc || '').toLowerCase();
        if (d.includes('silla') || d.includes('sillón') || d.includes('butaca')) return '🪑';
        if (d.includes('mesa') || d.includes('escritorio') || d.includes('tabla')) return '🪵';
        if (d.includes('gabinete') || d.includes('archivador') || d.includes('armario')) return '🗄️';
        if (d.includes('estante') || d.includes('anaquel')) return '📚';
        if (d.includes('aire') || d.includes('a/a') || d.includes('acondicionado')) return '❄️';
        if (d.includes('monitor') || d.includes('pantalla')) return '🖥️';
        if (d.includes('teclado')) return '⌨️';
        if (d.includes('mouse') || d.includes('ratón')) return '🖱️';
        if (d.includes('computadora') || d.includes('pc') || d.includes('laptop') || d.includes('portátil') || d.includes('portatil') || d.includes('cpu')) return '💻';
        if (d.includes('impresora')) return '🖨️';
        if (d.includes('escáner') || d.includes('scanner') || d.includes('cámara')) return '📷';
        if (d.includes('proyector') || d.includes('videobeam')) return '📽️';
        if (d.includes('teléfono') || d.includes('telefono')) return '📞';
        if (d.includes('televisor') || d.includes('tv')) return '📺';
        if (d.includes('vehículo') || d.includes('carro') || d.includes('camioneta') || d.includes('moto')) return '🚗';
        return '📦';
    };

    // Generar imagen de Código de Barras (CODE128) usando JsBarcode
    const getBarcodeDataUrl = (text, width = 2, height = 50) => {
        if (!text || !String(text).trim()) return '';
        try {
            const canvas = document.createElement('canvas');
            JsBarcode(canvas, String(text).trim(), {
                format: 'CODE128',
                lineColor: '#0f172a',
                width: width,
                height: height,
                displayValue: true,
                fontSize: 13,
                textMargin: 4,
                margin: 8,
                background: '#ffffff'
            });
            return canvas.toDataURL('image/png');
        } catch (err) {
            console.warn('Error al generar código de barras:', err);
            return '';
        }
    };

    // Identificar si el bien tiene asociado un Código de Barras
    const isBarcodeItem = (item) => {
        const code = item?.qr_code || '';
        return code.startsWith('barcode:') || code.startsWith('barcode_img:');
    };

    // Obtener URL de imagen de Código (QR o Barras)
    const getCodeImageUrl = (item, size = 150) => {
        const code = item?.qr_code || '';

        // Si es una imagen subida de código de barras
        if (code.startsWith('barcode_img:')) {
            return code.replace('barcode_img:', '');
        }

        // Si es un código de barras generado por valor
        if (code.startsWith('barcode:')) {
            const val = code.replace('barcode:', '');
            return getBarcodeDataUrl(val, size > 250 ? 3 : 2, size > 250 ? 75 : 45) || '';
        }

        // Si es una imagen en base64 de QR
        const isBase64 = code.startsWith('data:image') || code.startsWith('/9j/');
        if (isBase64) return code;

        // Código QR tradicional
        const target = item?.n_interno_bien
            ? `${window.location.origin}/bienes/qr/${encodeURIComponent(item.n_interno_bien)}`
            : `${window.location.origin}/bienes/qr/bien-${item?.id || '0'}`;

        return `https://quickchart.io/chart?cht=qr&chs=${size}x${size}&chl=${encodeURIComponent(target)}`;
    };

    const getQrImageUrl = (item, size = 150) => getCodeImageUrl(item, size);

    // Modal para visualizar QR o Código de Barras ampliado
    const handleOpenQrModal = (item) => {
        setSelectedQrBien(item);
        setQrModalOpen(true);
    };

    // Eliminar con el SweetAlert2 original
    const handleDelete = async (item) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar el bien "${item.n_interno_bien || item.descripcion}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/administracion/bienes_publicos/${item.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                throw new Error(json.message || `Error del servidor (${res.status})`);
            }

            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: 'El bien público fue eliminado correctamente',
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl' }
            });

            fetchBienes(pagination.current_page);
        } catch (err) {
            console.error('Error deleting bien:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo eliminar el bien',
                confirmButtonColor: '#1e3a8a'
            });
        }
    };

    // Cargar Usuarios Administrativos según Unidad Administrativa
    const fetchModalUsuariosAdministrativos = async (unidadId) => {
        if (!unidadId) {
            setModalUsuariosAdministrativos([]);
            return [];
        }
        setLoadingUsuariosAdmin(true);
        try {
            const res = await fetch(`/api/bienes_publicos/usuarios-administrativos/${unidadId}`);
            if (!res.ok) throw new Error('Error al cargar usuarios administrativos');
            const data = await res.json();
            const list = Array.isArray(data) ? data : [];
            setModalUsuariosAdministrativos(list);
            return list;
        } catch (err) {
            console.error('Error fetching modal usuarios administrativos:', err);
            setModalUsuariosAdministrativos([]);
            return [];
        } finally {
            setLoadingUsuariosAdmin(false);
        }
    };

    // Cargar Usuarios de Uso según Usuario Administrativo
    const fetchModalUsuariosDeUso = async (usuarioAdminId) => {
        if (!usuarioAdminId) {
            setModalUsuariosDeUso([]);
            return [];
        }
        setLoadingUsuariosUso(true);
        try {
            const res = await fetch(`/api/bienes_publicos/usuarios-de-uso/${usuarioAdminId}`);
            if (!res.ok) throw new Error('Error al cargar usuarios de uso');
            const data = await res.json();
            const list = Array.isArray(data) ? data : [];
            setModalUsuariosDeUso(list);
            return list;
        } catch (err) {
            console.error('Error fetching modal usuarios de uso:', err);
            setModalUsuariosDeUso([]);
            return [];
        } finally {
            setLoadingUsuariosUso(false);
        }
    };

    // Handler al cambiar Unidad Administrativa
    const handleUnidadChange = async (unidadId) => {
        setFormData(prev => ({
            ...prev,
            unidad_administrativa_id: unidadId,
            usuario_administrativo_id: '',
            usuario_de_uso_id: ''
        }));
        if (formErrors.unidad_administrativa_id || formErrors.usuario_administrativo_id || formErrors.usuario_de_uso_id) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next.unidad_administrativa_id;
                delete next.usuario_administrativo_id;
                delete next.usuario_de_uso_id;
                return next;
            });
        }
        setModalUsuariosDeUso([]);
        if (unidadId) {
            await fetchModalUsuariosAdministrativos(unidadId);
        } else {
            setModalUsuariosAdministrativos([]);
        }
    };

    // Handler al cambiar Usuario Administrativo
    const handleUsuarioAdminChange = async (usuarioAdminId) => {
        setFormData(prev => ({
            ...prev,
            usuario_administrativo_id: usuarioAdminId,
            usuario_de_uso_id: ''
        }));
        if (formErrors.usuario_administrativo_id || formErrors.usuario_de_uso_id) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next.usuario_administrativo_id;
                delete next.usuario_de_uso_id;
                return next;
            });
        }
        if (usuarioAdminId) {
            await fetchModalUsuariosDeUso(usuarioAdminId);
        } else {
            setModalUsuariosDeUso([]);
        }
    };

    // Abrir Modal de Creación
    const handleOpenCreate = () => {
        setModalMode('create');
        setModalCodeType('qr');
        setSelectedBien(null);
        setFormData({
            ...initialFormData,
            fecha_adquisicion: new Date().toISOString().split('T')[0],
            fecha_comentario: new Date().toISOString().split('T')[0]
        });
        setModalUsuariosAdministrativos([]);
        setModalUsuariosDeUso([]);
        setFormErrors({});
        setModalOpen(true);
    };

    // Abrir Modal de Edición
    const handleOpenEdit = async (item) => {
        setModalMode('edit');
        const isBarcode = (item.qr_code || '').startsWith('barcode:') || (item.qr_code || '').startsWith('barcode_img:');
        setModalCodeType(isBarcode ? 'barcode' : 'qr');
        setSelectedBien(item);
        setFormData({
            sede: item.sede || 'CODECYT S.A',
            unidad_administrativa_id: item.unidad_administrativa_id || '',
            usuario_administrativo_id: item.usuario_administrativo_id || '',
            usuario_de_uso_id: item.usuario_de_uso_id || '',
            n_interno_bien: item.n_interno_bien || '',
            descripcion: item.descripcion || '',
            forma_adquisicion_id: item.forma_adquisicion_id || '',
            fecha_adquisicion: item.fecha_adquisicion ? item.fecha_adquisicion.split('T')[0] : '',
            n_factura: item.n_factura || '',
            valor_factura: item.valor_factura !== undefined && item.valor_factura !== null ? item.valor_factura : '0.00',
            moneda: item.moneda || 'Bs.',
            estado_del_uso_id: item.estado_del_uso_id || '',
            condicion_id: item.condicion_id || '',
            marca: item.marca || '',
            modelo: item.modelo || '',
            color_id: item.color_id || '',
            serial: item.serial || '',
            comentario: item.comentario || '',
            fecha_comentario: item.fecha_comentario ? item.fecha_comentario.split('T')[0] : new Date().toISOString().split('T')[0],
            qr_code: item.qr_code || ''
        });
        setFormErrors({});
        setModalOpen(true);

        // Pre-cargar listas dependientes si existen asignaciones
        if (item.unidad_administrativa_id) {
            await fetchModalUsuariosAdministrativos(item.unidad_administrativa_id);
        } else {
            setModalUsuariosAdministrativos([]);
        }

        if (item.usuario_administrativo_id) {
            await fetchModalUsuariosDeUso(item.usuario_administrativo_id);
        } else {
            setModalUsuariosDeUso([]);
        }
    };

    // Casar Marca con Modelo: sugerencias dinámicas de modelos según la marca ingresada o seleccionada
    const availableModelos = useMemo(() => {
        const rawMarca = (formData.marca || '').trim().toUpperCase();
        if (!rawMarca) {
            return catalogs.modelos || [];
        }
        const map = catalogs.marcas_modelos || {};
        if (map[rawMarca] && Array.isArray(map[rawMarca]) && map[rawMarca].length > 0) {
            return map[rawMarca];
        }
        return catalogs.modelos || [];
    }, [formData.marca, catalogs.marcas_modelos, catalogs.modelos]);

    // Abrir sub-modal para crear un nuevo color rápido
    const handleOpenQuickColorModal = () => {
        setQuickColorName('');
        setQuickColorHex('#14368b');
        setQuickColorError(null);
        setQuickColorModalOpen(true);
    };

    // Guardar nuevo color desde el sub-modal
    const handleQuickColorSubmit = async (e) => {
        e.preventDefault();
        if (!quickColorName.trim()) {
            setQuickColorError('El nombre del color es obligatorio');
            return;
        }
        setQuickColorSubmitting(true);
        setQuickColorError(null);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/administracion/colors', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    color: quickColorName.trim().toUpperCase(),
                    codigo_hex: quickColorHex || '#14368b'
                })
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok) {
                if (json.errors && json.errors.color) {
                    setQuickColorError(json.errors.color[0]);
                } else {
                    setQuickColorError(json.message || 'Error al guardar el color');
                }
                return;
            }

            const newColor = json.data;

            // Actualizar catálogo de colores en el estado
            setCatalogs(prev => ({
                ...prev,
                colors: [...(prev.colors || []), newColor]
            }));

            // Seleccionar automáticamente el nuevo color en formData
            setFormData(prev => ({
                ...prev,
                color_id: newColor.id
            }));

            Swal.fire({
                icon: 'success',
                title: '¡Color Agregado!',
                text: `El color "${newColor.color}" se ha registrado y seleccionado exitosamente.`,
                timer: 1600,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-2xl z-[80]' }
            });

            setQuickColorModalOpen(false);
            setQuickColorName('');
            setQuickColorHex('#14368b');
        } catch (err) {
            console.error('Error al crear color rápido:', err);
            setQuickColorError('Error de conexión al guardar color');
        } finally {
            setQuickColorSubmitting(false);
        }
    };

    // Cerrar Modal
    const handleCloseModal = () => {
        if (submitting) return;
        setModalOpen(false);
        setSelectedBien(null);
        setFormErrors({});
    };

    // Función de exportación con modal de progreso en tiempo real y soporte para Todos o Seleccionados
    const handleExport = (format, scope = 'all') => {
        if (!format) return;

        const isSelected = scope === 'selected';
        if (isSelected && selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Por favor, selecciona al menos un bien público mediante la casilla de verificación antes de exportar.',
                confirmButtonColor: '#1e3a8a'
            });
            return;
        }

        const isExcel = format === 'excel';
        const filename = isExcel
            ? (isSelected ? `bienes_publicos_seleccionados_${selectedIds.length}.xlsx` : 'bienes_publicos.xlsx')
            : (isSelected ? `bienes_publicos_seleccionados_${selectedIds.length}.pdf` : 'bienes_publicos.pdf');
        const url = isExcel
            ? '/administracion/bienes_publicos/export/excel'
            : '/administracion/bienes_publicos/export/pdf';

        const params = new URLSearchParams();
        if (isSelected) {
            params.append('selected_ids', selectedIds.join(','));
        } else {
            if (busqueda.trim()) params.append('busqueda', busqueda.trim());
            if (filterUnidadId) params.append('unidad_administrativa_id', filterUnidadId);
            if (filterUsuarioAdminId) params.append('usuario_administrativo_id', filterUsuarioAdminId);
            if (filterUsuarioUsoId) params.append('usuario_de_uso_id', filterUsuarioUsoId);
            if (filterEstadoUsoId) params.append('estado_del_uso_id', filterEstadoUsoId);
            if (filterCondicionId) params.append('condicion_id', filterCondicionId);
            if (filterColorId) params.append('color_id', filterColorId);
            if (filterFormaAdquisicionId) params.append('forma_adquisicion_id', filterFormaAdquisicionId);
            if (fechaDesde) params.append('fecha_desde', fechaDesde);
            if (fechaHasta) params.append('fecha_hasta', fechaHasta);
        }

        const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;
        downloadWithProgress(fullUrl, {
            filename,
            type: isExcel ? 'excel' : 'pdf',
            title: isSelected
                ? `Exportando ${selectedIds.length} Bien(es) Seleccionado(s)`
                : (isExcel ? 'Exportando Bienes Públicos a Excel' : 'Exportando Bienes Públicos a PDF')
        });
    };

    // Alternar entre QR y Código de Barras en el modal
    const handleChangeCodeType = (newType) => {
        setModalCodeType(newType);
        const val = formData.n_interno_bien.trim();

        if (newType === 'barcode') {
            if (!formData.qr_code || !formData.qr_code.startsWith('data:image')) {
                setFormData(prev => ({
                    ...prev,
                    qr_code: val ? `barcode:${val}` : ''
                }));
            }
        } else {
            if (!formData.qr_code || !formData.qr_code.startsWith('data:image')) {
                setFormData(prev => ({
                    ...prev,
                    qr_code: val ? `${window.location.origin}/bienes/qr/${encodeURIComponent(val)}` : ''
                }));
            }
        }
    };

    // Cambios en los campos del formulario
    const handleFormChange = (field, value) => {
        setFormData(prev => {
            const next = { ...prev, [field]: value };
            // Si cambia el N° Interno y el código actual es automático de barras o QR, actualizarlo
            if (field === 'n_interno_bien') {
                const trimmed = value.trim();
                if (modalCodeType === 'barcode' && prev.qr_code && prev.qr_code.startsWith('barcode:')) {
                    next.qr_code = trimmed ? `barcode:${trimmed}` : '';
                } else if (modalCodeType === 'qr' && prev.qr_code && prev.qr_code.includes('/bienes/qr/')) {
                    next.qr_code = trimmed ? `${window.location.origin}/bienes/qr/${encodeURIComponent(trimmed)}` : '';
                }
            }
            return next;
        });

        if (formErrors[field]) {
            setFormErrors(prev => {
                const next = { ...prev };
                delete next[field];
                return next;
            });
        }
    };

    // Generar QR para el modal
    const handleGenerateModalQR = () => {
        if (!formData.n_interno_bien || !formData.n_interno_bien.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Ingresa primero el N° Interno del bien para generar el código QR.',
                confirmButtonColor: '#14368b'
            });
            return;
        }
        const qrUrl = `${window.location.origin}/bienes/qr/${encodeURIComponent(formData.n_interno_bien.trim())}`;
        setFormData(prev => ({ ...prev, qr_code: qrUrl }));
        Swal.fire({
            icon: 'success',
            title: 'QR Generado',
            text: 'Código QR generado correctamente con base al N° Interno',
            timer: 1500,
            showConfirmButton: false,
            customClass: { popup: 'rounded-2xl' }
        });
    };

    // Cargar archivo de imagen QR
    const handleLoadModalQRFile = (e) => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                setFormData(prev => ({ ...prev, qr_code: event.target.result }));
                Swal.fire({
                    icon: 'success',
                    title: 'QR Cargado',
                    text: 'Imagen del código QR cargada correctamente',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-2xl' }
                });
            };
            reader.readAsDataURL(file);
        }
    };

    // Generar Código de Barras para el modal
    const handleGenerateModalBarcode = () => {
        if (!formData.n_interno_bien || !formData.n_interno_bien.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Ingresa primero el N° Interno del bien para generar el código de barras.',
                confirmButtonColor: '#14368b'
            });
            return;
        }
        const val = formData.n_interno_bien.trim();
        setFormData(prev => ({ ...prev, qr_code: `barcode:${val}` }));
        Swal.fire({
            icon: 'success',
            title: 'Código de Barras Generado',
            text: `Código de barras generado correctamente para ${val}`,
            timer: 1500,
            showConfirmButton: false,
            customClass: { popup: 'rounded-2xl' }
        });
    };

    // Cargar archivo de imagen de Código de Barras
    const handleLoadModalBarcodeFile = (e) => {
        const file = e.target.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                setFormData(prev => ({ ...prev, qr_code: `barcode_img:${event.target.result}` }));
                Swal.fire({
                    icon: 'success',
                    title: 'Código Cargado',
                    text: 'Imagen del código de barras cargada correctamente',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-2xl' }
                });
            };
            reader.readAsDataURL(file);
        }
    };

    // Guardar (Crear o Editar)
    const handleSubmit = async (e) => {
        e.preventDefault();
        setFormErrors({});
        setSubmitting(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const url = modalMode === 'create'
                ? '/administracion/bienes_publicos'
                : `/administracion/bienes_publicos/${selectedBien.id}`;

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
                title: modalMode === 'create' ? '¡Registrado!' : '¡Actualizado!',
                text: modalMode === 'create' ? 'Bien público registrado exitosamente' : 'Bien público actualizado correctamente',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'rounded-2xl shadow-xl' }
            });

            setModalOpen(false);
            fetchBienes(pagination.current_page);
        } catch (err) {
            console.error('Error saving bien:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'No se pudo guardar el bien público',
                confirmButtonColor: '#14368b'
            });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="w-full">
            {/* Tarjetas superiores de estadísticas (Diseño Original Exacto) */}
            <div className="px-6 mb-8">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadeIn">
                    {/* Total de Bienes */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-blue-500/20">
                                <span className="text-blue-500" style={{ fontSize: '24px' }}>📦</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.totalBienes ?? 0}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Total de Bienes</p>
                    </div>

                    {/* Última Fecha de Adquisición */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-green-500/20">
                                <span className="text-green-500" style={{ fontSize: '24px' }}>📅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">
                            {metrics.ultimoBien && metrics.ultimoBien.fecha_adquisicion
                                ? formatDate(metrics.ultimoBien.fecha_adquisicion)
                                : 'N/A'}
                        </h3>
                        <p className="text-sm text-blue-900 font-semibold">Última Fecha de Adquisición</p>
                    </div>

                    {/* Bienes en Uso */}
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6">
                        <div className="flex items-center justify-between mb-4">
                            <div className="w-12 h-12 rounded-xl grid place-items-center bg-yellow-500/20">
                                <span className="text-yellow-500" style={{ fontSize: '24px' }}>✅</span>
                            </div>
                        </div>
                        <h3 className="text-2xl font-bold mb-1 text-slate-800">{metrics.bienesEnUso ?? 0}</h3>
                        <p className="text-sm text-blue-900 font-semibold">Bienes en Uso</p>
                    </div>
                </div>
            </div>

            {/* Filtros y controles (Diseño Original Exacto) */}
            <div className="px-6 mb-6 relative z-30">
                <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 relative z-30 overflow-visible">
                    <div className="flex flex-col gap-4">
                        {/* Fila superior: Buscar, Exportar, Nuevo */}
                        <div className="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-gray-100">
                            {/* Campo Buscar */}
                            <div className="relative flex-1 min-w-[240px] max-w-md">
                                <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    🔍
                                </span>
                                <input
                                    type="text"
                                    value={busqueda}
                                    onChange={(e) => setBusqueda(e.target.value)}
                                    placeholder="Buscar por bien, N° interno, serial..."
                                    className={`w-full pl-10 pr-9 py-2.5 bg-blue-100/80 hover:bg-blue-100 focus:bg-white border border-blue-200 focus:border-blue-500 rounded-xl text-sm text-slate-800 outline-none transition-all shadow-xs ${
                                        busqueda ? 'bg-blue-50 border-blue-500 ring-2 ring-blue-500/20' : ''
                                    }`}
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

                            {/* Exportar y Nuevo */}
                            <div className="flex items-center gap-3">
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
                                        <option value="excel:all">📊 Excel - Todos los bienes</option>
                                        <option value="pdf:all">📄 PDF - Todos los bienes</option>
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

                                {permissions.can_create && (
                                    <button
                                        type="button"
                                        onClick={handleOpenCreate}
                                        className="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl font-semibold transition-all border-none bg-blue-900 text-white hover:bg-blue-800 active:translate-y-0.5 text-sm cursor-pointer shadow-sm"
                                    >
                                        <span>➕</span>
                                        Nuevo
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Fila de controles de filtros */}
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-3 flex-wrap">
                                <button
                                    type="button"
                                    onClick={() => openCameraScanner('search', 'Escanear QR o Código de Barras')}
                                    className="bg-blue-900 hover:bg-blue-800 text-white border border-green-600 backdrop-blur-md rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500/50 shadow-xs cursor-pointer inline-flex items-center gap-1.5 transition-all hover:scale-[1.02]"
                                    title="Abrir la cámara para escanear y buscar un bien"
                                >
                                    <span>📷</span>
                                    <span>Escanear QR / Barras</span>
                                </button>

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

                                {bienes.length > 0 && (
                                    <button
                                        type="button"
                                        onClick={toggleSelectAll}
                                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                                    >
                                        <span>{selectedIds.length === bienes.length ? '☑' : '☐'}</span>
                                        <span>{selectedIds.length === bienes.length ? 'Deseleccionar Todos' : 'Seleccionar Todo'}</span>
                                    </button>
                                )}
                            </div>

                            {/* Badges de filtros activos en horizontal */}
                            <div className="flex flex-wrap items-center gap-2 text-xs">
                                {filterUnidadId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Unidad: {unidadesOptions.find(u => String(u.id) === String(filterUnidadId))?.label || filterUnidadId}</span>
                                        <button type="button" onClick={() => setFilterUnidadId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterUsuarioAdminId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>U. Admin: {usuariosAdminOptions.find(u => String(u.id) === String(filterUsuarioAdminId))?.label || filterUsuarioAdminId}</span>
                                        <button type="button" onClick={() => setFilterUsuarioAdminId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterUsuarioUsoId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>U. Uso: {usuariosUsoOptions.find(u => String(u.id) === String(filterUsuarioUsoId))?.label || filterUsuarioUsoId}</span>
                                        <button type="button" onClick={() => setFilterUsuarioUsoId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterEstadoUsoId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Estado: {estadosUsoOptions.find(e => String(e.id) === String(filterEstadoUsoId))?.label || filterEstadoUsoId}</span>
                                        <button type="button" onClick={() => setFilterEstadoUsoId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterCondicionId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Condición: {condicionesOptions.find(c => String(c.id) === String(filterCondicionId))?.label || filterCondicionId}</span>
                                        <button type="button" onClick={() => setFilterCondicionId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterColorId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Color: {colorsOptions.find(c => String(c.id) === String(filterColorId))?.label || filterColorId}</span>
                                        <button type="button" onClick={() => setFilterColorId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {filterFormaAdquisicionId && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Forma: {formasAdquisicionOptions.find(f => String(f.id) === String(filterFormaAdquisicionId))?.label || filterFormaAdquisicionId}</span>
                                        <button type="button" onClick={() => setFilterFormaAdquisicionId('')} className="hover:text-red-600 font-bold ml-1">✕</button>
                                    </span>
                                )}
                                {(fechaDesde || fechaHasta) && (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-100 text-blue-900 font-semibold border border-blue-200">
                                        <span>Adquisición: {fechaDesde || '...'} a {fechaHasta || '...'}</span>
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
                                        {/* Unidad Administrativa */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="🏢 Unidad Administrativa"
                                                options={unidadesOptions}
                                                value={filterUnidadId}
                                                onChange={(val) => {
                                                    setFilterUnidadId(val);
                                                    setFilterUsuarioAdminId('');
                                                    setFilterUsuarioUsoId('');
                                                }}
                                                placeholder="Todas las unidades"
                                                allLabel="Todas las unidades"
                                                icon="🏢"
                                            />
                                        </div>

                                        {/* Usuario Administrativo (filtrado) */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="👔 Usuario Administrativo"
                                                options={usuariosAdminOptions}
                                                value={filterUsuarioAdminId}
                                                onChange={setFilterUsuarioAdminId}
                                                placeholder={filterUnidadId ? "Usuarios de esta unidad..." : "Todos los usuarios admin"}
                                                allLabel="Todos los usuarios admin"
                                                icon="👔"
                                            />
                                        </div>

                                        {/* Usuario de Uso (filtrado) */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="👤 Usuario de Uso"
                                                options={usuariosUsoOptions}
                                                value={filterUsuarioUsoId}
                                                onChange={setFilterUsuarioUsoId}
                                                placeholder={filterUnidadId ? "Usuarios de esta unidad..." : "Todos los usuarios de uso"}
                                                allLabel="Todos los usuarios de uso"
                                                icon="👤"
                                            />
                                        </div>

                                        {/* Estado de Uso */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="⚙️ Estado del Uso"
                                                options={estadosUsoOptions}
                                                value={filterEstadoUsoId}
                                                onChange={setFilterEstadoUsoId}
                                                placeholder="Todos los estados"
                                                allLabel="Todos los estados"
                                                icon="⚙️"
                                            />
                                        </div>

                                        {/* Condición Física */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="🔍 Condición Física"
                                                options={condicionesOptions}
                                                value={filterCondicionId}
                                                onChange={setFilterCondicionId}
                                                placeholder="Todas las condiciones"
                                                allLabel="Todas las condiciones"
                                                icon="🔍"
                                            />
                                        </div>

                                        {/* Color */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="🎨 Color del Bien"
                                                options={colorsOptions}
                                                value={filterColorId}
                                                onChange={setFilterColorId}
                                                placeholder="Todos los colores"
                                                allLabel="Todos los colores"
                                                icon="🎨"
                                            />
                                        </div>

                                        {/* Forma de Adquisición */}
                                        <div className={showAdvancedFilters ? 'relative transition-opacity duration-300 opacity-100' : 'opacity-0 pointer-events-none'}>
                                            <SearchableSelect
                                                label="📑 Forma de Adquisición"
                                                options={formasAdquisicionOptions}
                                                value={filterFormaAdquisicionId}
                                                onChange={setFilterFormaAdquisicionId}
                                                placeholder="Todas las formas"
                                                allLabel="Todas las formas"
                                                icon="📑"
                                            />
                                        </div>
                                    </div>

                                    {/* Sección de Fechas de Adquisición con Presets */}
                                    <div className={`mt-5 pt-4 border-t border-blue-100 relative z-10 transition-opacity duration-300 ${showAdvancedFilters ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}>
                                        <div className="flex flex-wrap items-center justify-between gap-3 mb-2.5">
                                            <span className="text-xs font-bold text-blue-950">📅 Filtrar por Fecha de Adquisición:</span>

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

            {/* Listado de bienes (Diseño Original Exacto) */}
            <div className="px-6 mb-8 sm:justify-center relative z-10">
                {loading ? (
                    <div className="flex flex-col items-center justify-center py-16 bg-white border border-white/20 backdrop-blur-md rounded-2xl">
                        <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3"></div>
                        <p className="text-sm font-semibold text-blue-900">Cargando bienes públicos...</p>
                    </div>
                ) : error ? (
                    <div className="p-8 text-center bg-red-50 border border-red-200 rounded-2xl">
                        <p className="text-sm font-semibold text-red-600 mb-2">{error}</p>
                        <button
                            type="button"
                            onClick={() => fetchBienes(pagination.current_page)}
                            className="px-4 py-2 text-xs font-bold text-white bg-red-600 rounded-xl hover:bg-red-700"
                        >
                            Reintentar
                        </button>
                    </div>
                ) : bienes.length === 0 ? (
                    <div className="p-12 text-center bg-white border shadow-lg border-white/20 backdrop-blur-md rounded-2xl">
                        <h3 className="mb-2 text-xl font-bold text-gray-700">No hay bienes registrados</h3>
                        <p className="mb-6 text-gray-500">Comienza agregando un nuevo bien público</p>
                        {permissions.can_create && (
                            <button
                                type="button"
                                onClick={handleOpenCreate}
                                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold bg-blue-900 text-white hover:bg-blue-800 transition-colors cursor-pointer shadow-md"
                            >
                                ➕ Registrar Nuevo Bien
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {bienes.map((item) => {
                            const qrImageUrl = getQrImageUrl(item, 150);
                            const estadoColorClass = item.estado_del_uso_id === 6
                                ? 'bg-green-100 text-green-800 border-green-200'
                                : 'bg-gray-100 text-gray-800 border-gray-200';

                            return (
                                <div
                                    key={item.id}
                                    className={`bg-white border border-white/20 backdrop-blur-md rounded-2xl p-6 hover:scale-105 transition-transform duration-200 ${selectedIds.includes(item.id) ? 'ring-2 ring-blue-500 shadow-md' : ''}`}
                                >
                                    <div className="flex flex-col md:flex-row items-start gap-4 md:gap-4">
                                        {/* Checkbox de selección */}
                                        <div className="flex-shrink-0 pt-2">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.includes(item.id)}
                                                onChange={() => toggleSelect(item.id)}
                                                className="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer"
                                                title="Seleccionar bien"
                                            />
                                        </div>

                                        {/* Icono/Imagen del bien con gradiente exacto original */}
                                        <div className="flex items-center justify-center flex-shrink-0 w-20 h-20 rounded-xl bg-gradient-to-br from-blue-500 to-blue-900 text-white shadow-md">
                                            <span className="text-3xl">{getIconForDescription(item.descripcion)}</span>
                                        </div>

                                        {/* Información principal */}
                                        <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 w-20">
                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Sede</p>
                                                <p className="font-semibold text-slate-800 text-sm">{item.sede || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Descripción</p>
                                                <div className="flex items-center gap-2 flex-wrap">
                                                    <p className="font-bold text-md text-slate-900" title={item.descripcion}>
                                                        {item.descripcion || 'Sin descripción'}
                                                    </p>
                                                    {item.colors && (
                                                        <span
                                                            className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 border border-slate-300 shadow-2xs"
                                                            title={`Color: ${item.colors.color || item.colors.nombre}${item.colors.codigo_hex ? ` (${item.colors.codigo_hex})` : ''}`}
                                                        >
                                                            <span
                                                                className="w-3.5 h-3.5 rounded-full border border-black/20 shadow-inner flex-shrink-0"
                                                                style={{ backgroundColor: item.colors.codigo_hex || '#CCCCCC' }}
                                                            />
                                                            <span className="text-slate-800">{item.colors.color || item.colors.nombre}</span>
                                                        </span>
                                                    )}
                                                </div>
                                            </div>

                                            <div></div>

                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">N° Interno</p>
                                                <p className="font-mono text-sm text-slate-800 font-medium">{item.n_interno_bien || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Unidad Admin.</p>
                                                <p className="text-sm font-semibold text-slate-800">
                                                    {item.unidad_administrativas ? (item.unidad_administrativas.name || item.unidad_administrativas.unidad_administrativa) : 'N/A'}
                                                </p>
                                                <p className="text-xs text-blue-900 font-semibold mb-1 mt-1">Usuario Uso</p>
                                                <p className="text-sm text-slate-700">
                                                    {item.usuario_de_usos ? (item.usuario_de_usos.usuario_de_uso || item.usuario_de_usos.name) : 'N/A'}
                                                </p>
                                                <p className="text-xs text-blue-900 font-semibold mb-1 mt-1">Usuario Admin</p>
                                                <p className="text-sm text-slate-700">
                                                    {item.usuario_administrativos ? (item.usuario_administrativos.usuario_administrativo || item.usuario_administrativos.name) : 'N/A'}
                                                </p>
                                            </div>

                                            <div></div>
                                        </div>

                                        {/* Información secundaria */}
                                        <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Estado</p>
                                                <span className={`inline-flex items-center p-3 rounded-md text-xs font-medium border ${estadoColorClass}`}>
                                                    {item.estado_del_usos ? (item.estado_del_usos.estado_del_uso || item.estado_del_usos.nombre) : 'N/A'}
                                                </span>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Condición</p>
                                                <p className="text-sm text-slate-800">{item.condicions ? (item.condicions.condicion || item.condicions.nombre) : 'N/A'}</p>

                                                <div>
                                                    <p className="text-xs text-blue-900 font-semibold mb-1 mt-2">
                                                        {isBarcodeItem(item) ? 'Código de Barras' : 'Código QR'}
                                                    </p>
                                                    <img
                                                        src={getCodeImageUrl(item, 150)}
                                                        alt={isBarcodeItem(item) ? 'Código de Barras' : 'Código QR'}
                                                        className={`${
                                                            isBarcodeItem(item) ? 'w-28 h-12 object-contain' : 'w-16 h-16 object-contain'
                                                        } border border-gray-300 rounded-lg cursor-pointer hover:scale-105 transition-transform bg-white p-1 shadow-xs`}
                                                        onClick={() => handleOpenQrModal(item)}
                                                        title={isBarcodeItem(item) ? 'Ver Código de Barras Ampliado' : 'Ver Código QR Ampliado'}
                                                    />
                                                    <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Fecha Adq.</p>
                                                    <p className="text-sm text-slate-800">{formatDate(item.fecha_adquisicion)}</p>
                                                    <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Valor (Bs.)</p>
                                                    <p className="text-sm font-bold text-green-600">{formatCurrency(item.valor_factura)}</p>
                                                </div>
                                            </div>

                                            <div>
                                                <p className="text-xs text-blue-900 font-semibold mb-1">Marca / Modelo</p>
                                                <p className="text-sm text-slate-800">{item.marca || 'N/A'} / {item.modelo || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Serial</p>
                                                <p className="font-mono text-sm text-slate-800">{item.serial || 'N/A'}</p>
                                                <p className="text-xs text-blue-900 font-semibold mt-2 mb-1">Color</p>
                                                <p className="flex items-center gap-2 text-sm text-slate-800">
                                                    <span
                                                        className="w-4 h-4 border border-gray-300 rounded-full inline-block"
                                                        style={{ backgroundColor: item.colors?.codigo_hex || item.color_css || item.colors?.color_css || '#cccccc' }}
                                                    ></span>
                                                    {item.colors ? (item.colors.color || item.colors.nombre) : 'N/A'}
                                                </p>
                                            </div>

                                            <div></div>
                                        </div>

                                        {/* Acciones */}
                                        <div className="flex flex-col items-center md:items-end gap-2 w-full md:w-auto">
                                            <div className="flex flex-wrap justify-center gap-2 md:justify-end">
                                                {permissions.can_edit && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleOpenEdit(item)}
                                                        className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-green-600 text-white hover:bg-green-700 active:translate-y-0.5 text-xs shadow-xs cursor-pointer"
                                                    >
                                                        <span>✏️</span>
                                                        Editar
                                                    </button>
                                                )}
                                                {permissions.can_destroy && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(item)}
                                                        className="inline-flex items-center gap-2 px-3 py-2 rounded-xl font-semibold transition-all bg-red-500 text-white active:translate-y-0.5 text-xs shadow-xs hover:bg-red-600"
                                                    >
                                                        <span>🗑️</span>
                                                        Eliminar
                                                    </button>
                                                )}
                                            </div>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {formatDateTime(item.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Paginación */}
                {pagination.last_page > 1 && (
                    <div className="px-6 mt-8">
                        <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-4 flex items-center justify-between">
                            <button
                                type="button"
                                onClick={() => fetchBienes(pagination.current_page - 1)}
                                disabled={pagination.current_page <= 1}
                                className="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Anterior
                            </button>
                            <span className="text-xs font-bold text-blue-900 px-3">
                                Página {pagination.current_page} de {pagination.last_page} ({pagination.total} registros)
                            </span>
                            <button
                                type="button"
                                onClick={() => fetchBienes(pagination.current_page + 1)}
                                disabled={pagination.current_page >= pagination.last_page}
                                className="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Modal de Código QR / Barras Ampliado */}
            {qrModalOpen && selectedQrBien && createPortal(
                <div
                    className="fixed inset-0 z-[9999999] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-modal-backdrop"
                    style={{
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100vw',
                        height: '100vh',
                        zIndex: 9999999
                    }}
                >
                    <div className={`bg-white rounded-2xl shadow-2xl ${isBarcodeItem(selectedQrBien) ? 'max-w-lg' : 'max-w-sm'} w-full p-6 text-center border border-slate-200 animate-modal-expand`}>
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 className="text-sm font-bold text-blue-950 flex items-center gap-1.5">
                                <span>{isBarcodeItem(selectedQrBien) ? '🏷️' : '📱'}</span>
                                <span>
                                    {isBarcodeItem(selectedQrBien) ? 'Código de Barras' : 'Código QR'} - Bien: {selectedQrBien.n_interno_bien || selectedQrBien.id}
                                </span>
                            </h3>
                            <button
                                type="button"
                                onClick={() => setQrModalOpen(false)}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="p-4 bg-slate-50 rounded-xl border border-slate-100 inline-block mb-3 max-w-full overflow-x-auto">
                            <img
                                src={getCodeImageUrl(selectedQrBien, 350)}
                                alt={isBarcodeItem(selectedQrBien) ? 'Código de Barras' : 'Código QR'}
                                className={`${isBarcodeItem(selectedQrBien) ? 'max-h-36 max-w-full' : 'w-56 h-56'} mx-auto rounded-lg shadow-xs bg-white p-2 object-contain`}
                            />
                        </div>

                        <p className="text-xs font-medium text-slate-600 mb-4 truncate px-2" title={selectedQrBien.descripcion}>
                            {selectedQrBien.descripcion}
                        </p>

                        <div className="flex items-center gap-2">
                            <a
                                href={getCodeImageUrl(selectedQrBien, 450)}
                                download={`codigo-${selectedQrBien.n_interno_bien || selectedQrBien.id}.png`}
                                className="flex-1 py-2.5 text-xs font-bold text-blue-900 bg-blue-100 hover:bg-blue-200 rounded-xl transition-colors shadow-xs inline-flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <span>⬇️</span> Descargar
                            </a>
                            <button
                                type="button"
                                onClick={() => setQrModalOpen(false)}
                                className="flex-1 py-2.5 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-xl transition-colors shadow-xs cursor-pointer"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>,
                document.body
            )}
            {/* Modal de Crear / Editar Bien Público */}
            {modalOpen && createPortal(
                <div
                    className="fixed inset-0 z-[9999999] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto animate-modal-backdrop"
                    style={{
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100vw',
                        height: '100vh',
                        zIndex: 9999999
                    }}
                >
                    <div className="bg-white border border-white/20 backdrop-blur-md rounded-2xl shadow-2xl max-w-4xl w-full my-8 p-6 sm:p-8 max-h-[92vh] overflow-y-auto animate-modal-expand">
                        {/* Cabecera del modal */}
                        <div className="flex items-center justify-between pb-4 mb-6 border-b border-slate-200">
                            <div>
                                <h3 className="text-2xl font-bold text-slate-800">
                                    {modalMode === 'create' ? 'Registrar Bien Público' : 'Editar Bien Público'}
                                </h3>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    {modalMode === 'create'
                                        ? 'Completa los datos para registrar un nuevo bien en el sistema'
                                        : `Actualizar información del bien (N° Interno: ${formData.n_interno_bien || selectedBien?.id})`}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={handleCloseModal}
                                disabled={submitting}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario */}
                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Datalists auxiliares para autocompletado */}
                            <datalist id="descripciones-list">
                                {(catalogs.descripciones || []).map((d, i) => (
                                    <option key={i} value={d} />
                                ))}
                            </datalist>
                            <datalist id="marcas-list">
                                {(catalogs.marcas || []).map((m, i) => (
                                    <option key={i} value={m} />
                                ))}
                            </datalist>
                            <datalist id="modelos-list">
                                {availableModelos.map((m, i) => (
                                    <option key={i} value={m} />
                                ))}
                            </datalist>

                            {/* Fila 1: Sede y Fecha de Adquisición */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Sede: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.sede}
                                        onChange={(e) => handleFormChange('sede', e.target.value)}
                                        placeholder="Ej: Sede Central"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.sede && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.sede[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Fecha de Adquisición: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={formData.fecha_adquisicion}
                                        onChange={(e) => handleFormChange('fecha_adquisicion', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.fecha_adquisicion && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.fecha_adquisicion[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 2: Unidad Administrativa y Usuario Administrativo */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Unidad Administrativa: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.unidad_administrativa_id}
                                        onChange={(e) => handleUnidadChange(e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Unidad Administrativa</option>
                                        {(catalogs.unidad_administrativas || []).map((u) => (
                                            <option key={u.id} value={u.id}>{u.name || u.unidad_administrativa}</option>
                                        ))}
                                    </select>
                                    {formErrors.unidad_administrativa_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.unidad_administrativa_id[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Usuario Administrativo: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.usuario_administrativo_id}
                                        onChange={(e) => handleUsuarioAdminChange(e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:opacity-60"
                                        required
                                        disabled={submitting || loadingUsuariosAdmin || !formData.unidad_administrativa_id}
                                    >
                                        <option value="">
                                            {loadingUsuariosAdmin
                                                ? 'Cargando usuarios administrativos...'
                                                : !formData.unidad_administrativa_id
                                                    ? 'Seleccione primero una Unidad Administrativa'
                                                    : 'Seleccione Usuario Administrativo'}
                                        </option>
                                        {modalUsuariosAdministrativos.map((ua) => (
                                            <option key={ua.id} value={ua.id}>{ua.usuario_administrativo || ua.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.usuario_administrativo_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.usuario_administrativo_id[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 3: Usuario de Uso y N° Interno del Bien */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Usuario de Uso: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.usuario_de_uso_id}
                                        onChange={(e) => handleFormChange('usuario_de_uso_id', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 disabled:opacity-60"
                                        required
                                        disabled={submitting || loadingUsuariosUso || !formData.usuario_administrativo_id}
                                    >
                                        <option value="">
                                            {loadingUsuariosUso
                                                ? 'Cargando usuarios de uso...'
                                                : !formData.usuario_administrativo_id
                                                    ? 'Seleccione primero un Usuario Administrativo'
                                                    : 'Seleccione Usuario de Uso'}
                                        </option>
                                        {modalUsuariosDeUso.map((uu) => (
                                            <option key={uu.id} value={uu.id}>{uu.usuario_de_uso || uu.name}</option>
                                        ))}
                                    </select>
                                    {formErrors.usuario_de_uso_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.usuario_de_uso_id[0]}</p>}
                                </div>
                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <label className="text-sm font-semibold text-slate-800">
                                            N° Interno del Bien: <span className="text-red-500">*</span>
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => openCameraScanner('n_interno_bien', 'Escanear N° Interno con Cámara')}
                                            className="text-[11px] font-bold text-blue-900 hover:text-blue-700 bg-blue-100 hover:bg-blue-200 border border-blue-200 px-2 py-0.5 rounded-lg flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Escanear N° Interno con la cámara"
                                        >
                                            <span>📷</span>
                                            <span>Escanear</span>
                                        </button>
                                    </div>
                                    <input
                                        type="text"
                                        value={formData.n_interno_bien}
                                        onChange={(e) => handleFormChange('n_interno_bien', e.target.value)}
                                        placeholder="Ej: BP-2024-001"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.n_interno_bien && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.n_interno_bien[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 4: Descripción y Forma de Adquisición */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Descripción: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        list="descripciones-list"
                                        value={formData.descripcion}
                                        onChange={(e) => handleFormChange('descripcion', e.target.value)}
                                        placeholder="Seleccione o escriba la descripción..."
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    <p className="mt-1 text-[11px] text-slate-400">Puedes seleccionar una existente o escribir una nueva</p>
                                    {formErrors.descripcion && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.descripcion[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Forma de Adquisición: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.forma_adquisicion_id}
                                        onChange={(e) => handleFormChange('forma_adquisicion_id', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Forma de Adquisición</option>
                                        {(catalogs.forma_adquisiciones || []).map((fa) => (
                                            <option key={fa.id} value={fa.id}>{fa.forma_adquisicion || fa.nombre}</option>
                                        ))}
                                    </select>
                                    {formErrors.forma_adquisicion_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.forma_adquisicion_id[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 5: N° Factura, Valor Factura y Moneda */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        N° Factura: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.n_factura}
                                        onChange={(e) => handleFormChange('n_factura', e.target.value)}
                                        placeholder="Ej: F-123456"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.n_factura && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.n_factura[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Valor Factura: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        value={formData.valor_factura}
                                        onChange={(e) => handleFormChange('valor_factura', e.target.value)}
                                        placeholder="0.00"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.valor_factura && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.valor_factura[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Moneda: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.moneda}
                                        onChange={(e) => handleFormChange('moneda', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="Bs.">Bolívar (Bs.)</option>
                                        <option value="USD">Dólar (USD)</option>
                                    </select>
                                    {formErrors.moneda && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.moneda[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 6: Estado del Uso y Condición Física */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Estado del Uso: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.estado_del_uso_id}
                                        onChange={(e) => handleFormChange('estado_del_uso_id', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Estado</option>
                                        {(catalogs.estado_del_usos || []).map((eu) => (
                                            <option key={eu.id} value={eu.id}>{eu.estado_del_uso || eu.nombre}</option>
                                        ))}
                                    </select>
                                    {formErrors.estado_del_uso_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.estado_del_uso_id[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Condición Física: <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={formData.condicion_id}
                                        onChange={(e) => handleFormChange('condicion_id', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Condición</option>
                                        {(catalogs.condiciones || []).map((c) => (
                                            <option key={c.id} value={c.id}>{c.condicion || c.nombre}</option>
                                        ))}
                                    </select>
                                    {formErrors.condicion_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.condicion_id[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 7: Marca y Modelo */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Marca: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        list="marcas-list"
                                        value={formData.marca}
                                        onChange={(e) => handleFormChange('marca', e.target.value)}
                                        placeholder="Ej: HP, Dell, Lenovo..."
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.marca && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.marca[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Modelo: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        list="modelos-list"
                                        value={formData.modelo}
                                        onChange={(e) => handleFormChange('modelo', e.target.value)}
                                        placeholder="Ej: ProDesk 400 G6"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formData.marca && catalogs.marcas_modelos && catalogs.marcas_modelos[(formData.marca || '').trim().toUpperCase()] ? (
                                        <p className="mt-1 text-[11px] text-blue-900 font-semibold flex items-center gap-1">
                                            <span>🎯</span>
                                            <span>Modelos registrados para {formData.marca.toUpperCase()} ({catalogs.marcas_modelos[(formData.marca || '').trim().toUpperCase()].length})</span>
                                        </p>
                                    ) : (
                                        <p className="mt-1 text-[11px] text-slate-400">Puedes seleccionar uno sugerido o escribir uno nuevo</p>
                                    )}
                                    {formErrors.modelo && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.modelo[0]}</p>}
                                </div>
                            </div>

                            {/* Fila 8: Color y Serial */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <div className="flex items-center justify-between mb-1.5">
                                        <label className="text-sm font-semibold text-slate-800">
                                            Color: <span className="text-red-500">*</span>
                                        </label>
                                        <button
                                            type="button"
                                            onClick={handleOpenQuickColorModal}
                                            className="text-[11px] font-bold text-blue-900 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2.5 py-0.5 rounded-lg flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Crear un nuevo color sin salir del formulario"
                                        >
                                            <span>➕</span>
                                            <span>Nuevo Color</span>
                                        </button>
                                    </div>

                                    {/* Selector visual de colores: círculos interactivos */}
                                    <div className="mb-2 p-2 bg-blue-50/70 border border-blue-200/80 rounded-xl">
                                        <span className="text-[11px] font-semibold text-blue-950 block mb-1.5">
                                            Paleta de colores registrados (haz clic para seleccionar):
                                        </span>
                                        <div className="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                                            {(catalogs.colors || []).map((col) => {
                                                const isSelected = String(formData.color_id) === String(col.id);
                                                const bg = col.codigo_hex || '#cccccc';
                                                const isLight = bg === '#FFFFFF' || (bg || '').toUpperCase() === '#F5F5DC';
                                                return (
                                                    <button
                                                        key={col.id}
                                                        type="button"
                                                        onClick={() => handleFormChange('color_id', col.id)}
                                                        className={`w-7 h-7 rounded-xl border-2 transition-all flex items-center justify-center cursor-pointer shadow-2xs hover:scale-110 ${
                                                            isSelected
                                                                ? 'border-blue-600 scale-110 ring-2 ring-blue-500 shadow-sm'
                                                                : 'border-slate-300 hover:border-slate-400'
                                                        }`}
                                                        style={{ backgroundColor: bg }}
                                                        title={`${col.color || col.nombre} (${bg})`}
                                                    >
                                                        {isSelected && (
                                                            <span className={`text-[10px] font-bold ${isLight ? 'text-black' : 'text-white'}`}>
                                                                ✓
                                                            </span>
                                                        )}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>

                                    {/* Select desplegable sincronizado */}
                                    <select
                                        value={formData.color_id}
                                        onChange={(e) => {
                                            if (e.target.value === '__NEW__') {
                                                handleOpenQuickColorModal();
                                            } else {
                                                handleFormChange('color_id', e.target.value);
                                            }
                                        }}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    >
                                        <option value="">Seleccione Color...</option>
                                        {(catalogs.colors || []).map((col) => (
                                            <option key={col.id} value={col.id}>
                                                {col.color || col.nombre} {col.codigo_hex ? `(${col.codigo_hex})` : ''}
                                            </option>
                                        ))}
                                        <option value="__NEW__">➕ Añadir nuevo color...</option>
                                    </select>

                                    {/* Muestra visual del color seleccionado */}
                                    {(() => {
                                        const selectedCol = (catalogs.colors || []).find(c => String(c.id) === String(formData.color_id));
                                        if (selectedCol) {
                                            return (
                                                <div className="flex items-center gap-2 mt-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                                    <span
                                                        className="w-4 h-4 rounded-full border border-slate-300 shadow-2xs flex-shrink-0"
                                                        style={{ backgroundColor: selectedCol.codigo_hex || '#cccccc' }}
                                                    />
                                                    <span className="font-semibold text-slate-700">{selectedCol.color}</span>
                                                    {selectedCol.codigo_hex && (
                                                        <span className="font-mono text-[11px] text-slate-500">({selectedCol.codigo_hex})</span>
                                                    )}
                                                </div>
                                            );
                                        }
                                        return null;
                                    })()}
                                    {formErrors.color_id && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.color_id[0]}</p>}
                                </div>
                                <div>
                                    <div className="flex items-center justify-between mb-1">
                                        <label className="text-sm font-semibold text-slate-800">
                                            Serial: <span className="text-red-500">*</span>
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => openCameraScanner('serial', 'Escanear Serial del Bien con Cámara')}
                                            className="text-[11px] font-bold text-blue-900 hover:text-blue-700 bg-blue-100 hover:bg-blue-200 border border-blue-200 px-2 py-0.5 rounded-lg flex items-center gap-1 transition-colors cursor-pointer"
                                            title="Escanear serial con la cámara"
                                        >
                                            <span>📷</span>
                                            <span>Escanear</span>
                                        </button>
                                    </div>
                                    <input
                                        type="text"
                                        value={formData.serial}
                                        onChange={(e) => handleFormChange('serial', e.target.value)}
                                        placeholder="Ej: S/N o código de fabricante"
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.serial && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.serial[0]}</p>}
                                </div>
                            </div>

                            {/* Sección de Código Identificador: QR o Código de Barras */}
                            <div className="p-4 bg-blue-50/80 border border-blue-200 rounded-xl">
                                {/* Cabecera de la sección con selector de tipo */}
                                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-blue-200/60">
                                    <div>
                                        <h4 className="text-sm font-bold text-blue-950 flex items-center gap-1.5">
                                            <span>{modalCodeType === 'barcode' ? '🏷️' : '📱'}</span>
                                            <span>Código Identificador del Bien</span>
                                        </h4>
                                        <p className="text-xs text-blue-700 mt-0.5">
                                            Selecciona si deseas asociar un <strong>Código QR</strong> o un <strong>Código de Barras</strong>.
                                        </p>
                                    </div>

                                    {/* Selector de Tipo (Pills) */}
                                    <div className="inline-flex p-1 bg-white border border-blue-200 rounded-xl shadow-xs">
                                        <button
                                            type="button"
                                            onClick={() => handleChangeCodeType('qr')}
                                            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer ${
                                                modalCodeType === 'qr'
                                                    ? 'bg-blue-900 text-white shadow-xs'
                                                    : 'text-slate-600 hover:text-blue-900 hover:bg-blue-50'
                                            }`}
                                        >
                                            <span>📱</span>
                                            <span>Código QR</span>
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => handleChangeCodeType('barcode')}
                                            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer ${
                                                modalCodeType === 'barcode'
                                                    ? 'bg-blue-900 text-white shadow-xs'
                                                    : 'text-slate-600 hover:text-blue-900 hover:bg-blue-50'
                                            }`}
                                        >
                                            <span className="font-mono">|||||||</span>
                                            <span>Código de Barras</span>
                                        </button>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                    {/* Contenedor de Vista Previa */}
                                    <div className="flex-shrink-0 mx-auto sm:mx-0">
                                        {modalCodeType === 'barcode' ? (
                                            formData.qr_code && formData.qr_code.startsWith('barcode_img:') ? (
                                                <img
                                                    src={formData.qr_code.replace('barcode_img:', '')}
                                                    alt="Código de Barras Subido"
                                                    className="w-44 h-24 border-2 border-blue-300 rounded-xl p-1 bg-white shadow-xs object-contain"
                                                />
                                            ) : formData.qr_code && formData.qr_code.startsWith('barcode:') ? (
                                                <img
                                                    src={getBarcodeDataUrl(formData.qr_code.replace('barcode:', ''), 2, 50)}
                                                    alt="Código de Barras"
                                                    className="w-44 h-24 border-2 border-blue-300 rounded-xl p-1 bg-white shadow-xs object-contain"
                                                />
                                            ) : formData.n_interno_bien.trim() ? (
                                                <img
                                                    src={getBarcodeDataUrl(formData.n_interno_bien.trim(), 2, 50)}
                                                    alt="Código de Barras Sugerido"
                                                    className="w-44 h-24 border-2 border-blue-300 rounded-xl p-1 bg-white shadow-xs object-contain opacity-75"
                                                    title="Vista previa automática basada en N° Interno"
                                                />
                                            ) : (
                                                <div className="w-44 h-24 bg-white border-2 border-dashed border-blue-300 rounded-xl flex flex-col items-center justify-center text-slate-400 p-2">
                                                    <span className="text-xl font-mono">|||||||||||||</span>
                                                    <span className="text-[10px] mt-1 text-center font-medium">Ingresa N° Interno</span>
                                                </div>
                                            )
                                        ) : (
                                            formData.qr_code && !formData.qr_code.startsWith('barcode') ? (
                                                <img
                                                    src={formData.qr_code.startsWith('data:image') || formData.qr_code.startsWith('/9j/')
                                                        ? formData.qr_code
                                                        : `https://quickchart.io/chart?cht=qr&chs=150x150&chl=${encodeURIComponent(formData.qr_code)}`}
                                                    alt="QR Code"
                                                    className="w-24 h-24 border-2 border-blue-300 rounded-xl p-1 bg-white shadow-xs object-contain"
                                                />
                                            ) : formData.n_interno_bien.trim() ? (
                                                <img
                                                    src={`https://quickchart.io/chart?cht=qr&chs=150x150&chl=${encodeURIComponent(`${window.location.origin}/bienes/qr/${formData.n_interno_bien.trim()}`)}`}
                                                    alt="QR Code Sugerido"
                                                    className="w-24 h-24 border-2 border-blue-300 rounded-xl p-1 bg-white shadow-xs object-contain opacity-75"
                                                    title="Vista previa automática basada en N° Interno"
                                                />
                                            ) : (
                                                <div className="w-24 h-24 bg-white border-2 border-dashed border-blue-300 rounded-xl flex items-center justify-center text-3xl">
                                                    📱
                                                </div>
                                            )
                                        )}
                                    </div>

                                    {/* Controles de Acción según el Tipo */}
                                    <div className="flex-1">
                                        <h4 className="text-sm font-bold text-blue-900">
                                            {modalCodeType === 'barcode' ? 'Código de Barras (CODE 128)' : 'Código QR del Bien'}
                                        </h4>
                                        <p className="text-xs text-blue-700 mt-0.5">
                                            {modalCodeType === 'barcode'
                                                ? 'Genera un código de barras estándar basado en el N° Interno o carga una imagen existente.'
                                                : 'Genera el QR automáticamente basado en el N° Interno o carga una imagen existente.'}
                                        </p>

                                        <div className="flex flex-wrap gap-2 mt-3">
                                            {/* Botón de Escanear con Cámara */}
                                            <button
                                                type="button"
                                                onClick={() => openCameraScanner('qr_code', modalCodeType === 'barcode' ? 'Escanear Código de Barras' : 'Escanear Código QR')}
                                                className="px-3 py-1.5 text-xs font-bold text-white bg-blue-900 hover:bg-blue-800 rounded-lg transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1.5 hover:scale-[1.02]"
                                                title="Abrir la cámara para escanear el código físico"
                                            >
                                                <span>📷</span>
                                                <span>Escanear con Cámara</span>
                                            </button>

                                            {modalCodeType === 'barcode' ? (
                                                <>
                                                    <button
                                                        type="button"
                                                        onClick={handleGenerateModalBarcode}
                                                        className="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1"
                                                    >
                                                        <span>🔄</span> Generar Barras Automático
                                                    </button>
                                                    <label className="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg cursor-pointer hover:bg-blue-700 transition-colors shadow-xs inline-flex items-center gap-1">
                                                        <span>📤</span> Cargar Imagen de Barras
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            className="hidden"
                                                            onChange={handleLoadModalBarcodeFile}
                                                        />
                                                    </label>
                                                </>
                                            ) : (
                                                <>
                                                    <button
                                                        type="button"
                                                        onClick={handleGenerateModalQR}
                                                        className="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-xs cursor-pointer inline-flex items-center gap-1"
                                                    >
                                                        <span>🔄</span> Generar QR Automático
                                                    </button>
                                                    <label className="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg cursor-pointer hover:bg-blue-700 transition-colors shadow-xs inline-flex items-center gap-1">
                                                        <span>📤</span> Cargar QR Existente
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            className="hidden"
                                                            onChange={handleLoadModalQRFile}
                                                        />
                                                    </label>
                                                </>
                                            )}

                                            {formData.qr_code && (
                                                <button
                                                    type="button"
                                                    onClick={() => setFormData(prev => ({ ...prev, qr_code: '' }))}
                                                    className="px-3 py-1.5 text-xs font-semibold text-red-600 hover:text-red-800 transition-colors cursor-pointer"
                                                >
                                                    Quitar Código
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Fila 9: Comentario y Fecha Comentario */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Comentario (Opcional):
                                    </label>
                                    <input
                                        type="text"
                                        value={formData.comentario}
                                        onChange={(e) => handleFormChange('comentario', e.target.value)}
                                        placeholder="Detalles u observaciones..."
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        disabled={submitting}
                                    />
                                    {formErrors.comentario && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.comentario[0]}</p>}
                                </div>
                                <div>
                                    <label className="block mb-1 text-sm font-semibold text-slate-800">
                                        Fecha Comentario: <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={formData.fecha_comentario}
                                        onChange={(e) => handleFormChange('fecha_comentario', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-blue-100 border-none rounded-xl text-sm font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                        required
                                        disabled={submitting}
                                    />
                                    {formErrors.fecha_comentario && <p className="mt-1 text-xs text-red-500 font-semibold">{formErrors.fecha_comentario[0]}</p>}
                                </div>
                            </div>

                            {/* Botones de acción inferiores (Diseño exacto de create de bienes_publicos) */}
                            <div className="flex items-center justify-between gap-4 pt-6 mt-6 border-t border-slate-200">
                                <button
                                    type="button"
                                    onClick={handleCloseModal}
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
                                            <span>{modalMode === 'create' ? 'Guardar Bien' : 'Actualizar Bien'}</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>,
                document.body
            )}

            {/* SUB-MODAL RÁPIDO PARA CREAR NUEVO COLOR (Z-INDEX SUPERIOR Z-[70]) */}
            {quickColorModalOpen && createPortal(
                <div
                    className="fixed inset-0 z-[99999999] flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto animate-modal-backdrop"
                    style={{
                        position: 'fixed',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        width: '100vw',
                        height: '100vh',
                        zIndex: 99999999
                    }}
                >
                    <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200 transition-all animate-modal-expand">
                        {/* Cabecera del sub-modal */}
                        <div className="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                            <div>
                                <h4 className="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span>🎨</span>
                                    <span>Nuevo Color</span>
                                </h4>
                                <p className="text-xs text-slate-500 mt-0.5">
                                    Crea un color y se seleccionará automáticamente en el bien
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setQuickColorModalOpen(false)}
                                disabled={quickColorSubmitting}
                                className="modal-close-btn"
                                title="Cerrar modal"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Formulario de nuevo color */}
                        <form onSubmit={handleQuickColorSubmit} className="space-y-4">
                            <div>
                                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Nombre del Color: <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={quickColorName}
                                    onChange={(e) => setQuickColorName(e.target.value.toUpperCase())}
                                    placeholder="Ej: AZUL REY, VERDE OLIVA, DORADO..."
                                    className="w-full bg-blue-100 border-none rounded-xl px-4 py-2 text-sm font-medium text-slate-900 focus:ring-2 focus:ring-blue-600 uppercase"
                                    autoFocus
                                    required
                                    disabled={quickColorSubmitting}
                                />
                            </div>

                            {/* Paleta rápida de colores */}
                            <div>
                                <span className="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Colores sugeridos:
                                </span>
                                <div className="flex flex-wrap gap-1.5">
                                    {PRESET_COLORS.map((pc) => (
                                        <button
                                            key={pc.hex}
                                            type="button"
                                            onClick={() => {
                                                setQuickColorHex(pc.hex);
                                                if (!quickColorName.trim()) {
                                                    setQuickColorName(pc.name);
                                                }
                                            }}
                                            className={`w-6 h-6 rounded-lg border-2 transition-transform hover:scale-110 flex items-center justify-center cursor-pointer shadow-2xs ${
                                                quickColorHex === pc.hex
                                                    ? 'border-blue-600 scale-110 ring-2 ring-blue-400'
                                                    : 'border-slate-300'
                                            }`}
                                            style={{ backgroundColor: pc.hex }}
                                            title={`${pc.name} (${pc.hex})`}
                                        >
                                            {quickColorHex === pc.hex && (
                                                <span className={`text-[9px] font-bold ${pc.hex === '#FFFFFF' || pc.hex === '#F5F5DC' ? 'text-black' : 'text-white'}`}>
                                                    ✓
                                                </span>
                                            )}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Selector de Tono Personalizado: <span className="text-red-500">*</span>
                                </label>
                                <div className="grid grid-cols-2 gap-2 items-center">
                                    <div className="flex items-center gap-2">
                                        <div
                                            className="relative flex items-center justify-center w-11 h-9 rounded-xl border-2 border-slate-300 shadow-2xs cursor-pointer hover:scale-105 transition-transform flex-shrink-0 overflow-hidden"
                                            style={{ backgroundColor: quickColorHex }}
                                            title="Haz clic para abrir el selector de color"
                                        >
                                            <input
                                                type="color"
                                                value={quickColorHex}
                                                onChange={(e) => setQuickColorHex(e.target.value.toUpperCase())}
                                                className="absolute inset-0 opacity-0 w-full h-full cursor-pointer"
                                                disabled={quickColorSubmitting}
                                            />
                                            <span className="text-[11px] select-none pointer-events-none drop-shadow-sm">🎨</span>
                                        </div>
                                        <input
                                            type="text"
                                            value={quickColorHex}
                                            onChange={(e) => setQuickColorHex(e.target.value.toUpperCase())}
                                            className="w-full bg-blue-100 border-none rounded-xl px-2.5 py-2 text-xs font-mono font-bold text-slate-800 uppercase focus:ring-2 focus:ring-blue-600"
                                            maxLength={7}
                                            disabled={quickColorSubmitting}
                                        />
                                    </div>
                                    <div className="flex items-center gap-2 p-2 bg-slate-50 border border-slate-200 rounded-xl">
                                        <span
                                            className="w-6 h-6 rounded-lg shadow-inner border border-slate-300 flex-shrink-0"
                                            style={{ backgroundColor: quickColorHex }}
                                        />
                                        <span className="text-xs font-bold text-slate-800 truncate">
                                            {quickColorName ? quickColorName.toUpperCase() : 'Muestra'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {quickColorError && (
                                <div className="p-2.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 font-semibold">
                                    {quickColorError}
                                </div>
                            )}

                            {/* Botones de Acción */}
                            <div className="flex items-center justify-between gap-3 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onClick={() => setQuickColorModalOpen(false)}
                                    disabled={quickColorSubmitting}
                                    className="btn-save inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-bold text-white rounded-xl shadow-xs transition-all duration-300 disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    <span>Cancelar</span>
                                </button>
                                <button
                                    type="submit"
                                    disabled={quickColorSubmitting}
                                    className="btn-save inline-flex items-center justify-center gap-1.5 px-5 py-2 text-xs font-bold text-white rounded-xl shadow-xs transition-all duration-300 disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0.5 cursor-pointer"
                                    style={{
                                        background: 'linear-gradient(135deg, #14368b 0%, #4f46e5 100%)',
                                        boxShadow: '0 4px 12px rgba(99, 102, 241, 0.3)'
                                    }}
                                >
                                    {quickColorSubmitting ? (
                                        <>
                                            <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                            <span>Guardando...</span>
                                        </>
                                    ) : (
                                        <>
                                            <span>💾</span>
                                            <span>Guardar Color</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>,
                document.body
            )}

            {/* Modal de Escaneo en Vivo con Cámara */}
            <CameraScannerModal
                isOpen={cameraModalOpen}
                onClose={() => setCameraModalOpen(false)}
                onScan={handleCameraScanSuccess}
                title={cameraModalTitle}
                subtitle="Apunta la cámara al código de barras o código QR"
                allowedFields={['serial', 'n_interno_bien', 'qr_code']}
                defaultTargetField={cameraTargetField}
            />
        </div>
    );
}
