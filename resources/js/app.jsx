import React from 'react';
import ReactDOM from 'react-dom/client';
import DocumentViewer from './components/documents/DocumentViewer';
import EstanteriaSelector from './components/documents/EstanteriaSelector';
import NotificationCenter from './components/ui/NotificationCenter';
import ActivityLog from './components/gtic/ActivityLog';
import BienesPublicos from './components/administracion/BienesPublicos';
import InventarioOficinas from './components/administracion/InventarioOficinas';
import SalidasOficinas from './components/administracion/SalidasOficinas';
import Consumibles from './components/administracion/Consumibles';
import DigitalizacionDocumentos from './components/gestion_humana/DigitalizacionDocumentos';
import UsersManagement from './components/gtic/UsersManagement';
import RolesManagement from './components/gtic/RolesManagement';
import PermisosManagement from './components/gtic/PermisosManagement';
import DocumentosManagement from './components/administracion/DocumentosManagement';
import EstanteriasManagement from './components/administracion/EstanteriasManagement';
import CategoriasManagement from './components/administracion/CategoriasManagement';
import UnidadesAdministrativasManagement from './components/administracion/UnidadesAdministrativasManagement';
import UsuariosDeUsoManagement from './components/administracion/UsuariosDeUsoManagement';
import FormasAdquisicionManagement from './components/administracion/FormasAdquisicionManagement';
import EstadosUsoManagement from './components/administracion/EstadosUsoManagement';
import ColorsManagement from './components/administracion/ColorsManagement';
import CondicionesManagement from './components/administracion/CondicionesManagement';
import UsuariosAdministrativosManagement from './components/administracion/UsuariosAdministrativosManagement';
import { downloadWithProgress } from './utils/downloadWithProgress';

// Interceptor global para cualquier enlace o select de descarga/exportación (PDF, Excel, Imagen)
let interceptorInitialized = false;

function initGlobalDownloadInterceptor() {
    if (typeof document === 'undefined' || interceptorInitialized) return;
    interceptorInitialized = true;

    // 1. Delegar clics en enlaces y botones de descarga
    document.addEventListener('click', (e) => {
        if (e.defaultPrevented) return;

        const target = e.target.closest('a, button[data-download-url]');
        if (!target) return;

        // Si se pide ignorar explícitamente o es nueva pestaña
        if (target.hasAttribute('data-no-progress') || target.getAttribute('target') === '_blank') {
            return;
        }

        const href = target.getAttribute('href') || target.getAttribute('data-download-url');
        if (!href || href === '#' || href.startsWith('javascript:')) return;

        const downloadAttr = target.getAttribute('download');
        const hasDataProgress = target.hasAttribute('data-download-progress');
        const isExportRoute = href.includes('/export/pdf') || href.includes('/export/excel') || href.includes('/export/csv');
        const isDownloadFile = downloadAttr !== null || /\.(pdf|xlsx?|csv|png|jpe?g|webp|svg)(\?.*)?$/i.test(href);

        // Si es Data URI o Blob (como código QR o código de barras)
        if (downloadAttr !== null && (href.startsWith('data:') || href.startsWith('blob:'))) {
            e.preventDefault();
            downloadWithProgress(href, { filename: downloadAttr, type: 'image' });
            return;
        }

        // Si es ruta de exportación o archivo descargable
        if (hasDataProgress || isExportRoute || (isDownloadFile && !href.startsWith('blob:') && !href.startsWith('data:'))) {
            e.preventDefault();
            const filename = downloadAttr || target.getAttribute('data-filename') || undefined;
            downloadWithProgress(href, { filename });
        }
    });

    // 2. Delegar cambios en <select> que contengan URLs de exportación (solo elementos Blade / fuera de componentes React)
    document.addEventListener('change', (e) => {
        if (e.defaultPrevented) return;

        const select = e.target.closest('select');
        if (!select) return;

        // Si el select está dentro de un componente React, dejamos que el componente gestione su evento
        if (select.closest('[data-react-component], [id$="-root"]')) {
            return;
        }

        const val = select.value;
        if (!val) return;

        if (val.includes('/export/pdf') || val.includes('/export/excel') || val.includes('/export/csv') || /\.(pdf|xlsx?|csv)(\?.*)?$/i.test(val)) {
            e.preventDefault();
            e.stopPropagation();
            downloadWithProgress(val);
            select.value = '';
        }
    });
}

// Helper global para montar componentes React en cualquier vista Blade
function mountComponent(componentName, component) {
    const elements = document.querySelectorAll(`[data-react-component="${componentName}"]`);

    elements.forEach(element => {
        if (!element.hasAttribute('data-react-mounted')) {
            element.setAttribute('data-react-mounted', 'true');
            const props = JSON.parse(element.dataset.props || '{}');
            const root = ReactDOM.createRoot(element);
            root.render(React.createElement(component, props));
        }
    });
}

function mountById(elementId, component) {
    const el = document.getElementById(elementId);
    if (el && !el.hasAttribute('data-react-mounted')) {
        el.setAttribute('data-react-mounted', 'true');
        const root = ReactDOM.createRoot(el);
        root.render(React.createElement(component));
    }
}

// Inicializar componentes cuando el DOM esté listo o inmediatamente si ya cargó
function initReactRoots() {
    // GTIC: Registro de Actividades, Gestión de Usuarios, Roles y Permisos
    mountComponent('ActivityLog', ActivityLog);
    mountById('activity-log-root', ActivityLog);
    mountComponent('UsersManagement', UsersManagement);
    mountById('users-management-root', UsersManagement);
    mountComponent('RolesManagement', RolesManagement);
    mountById('roles-management-root', RolesManagement);
    mountComponent('PermisosManagement', PermisosManagement);
    mountById('permisos-management-root', PermisosManagement);

    // OGA: Bienes Públicos / Muebles
    mountComponent('BienesPublicos', BienesPublicos);
    mountById('bienes-publicos-root', BienesPublicos);

    // OGA: Inventario de Oficina, Salidas y Consumibles
    mountComponent('InventarioOficinas', InventarioOficinas);
    mountById('inventario-oficinas-root', InventarioOficinas);
    mountComponent('SalidasOficinas', SalidasOficinas);
    mountById('salidas-oficinas-root', SalidasOficinas);
    mountComponent('Consumibles', Consumibles);
    mountById('consumibles-root', Consumibles);

    // OGA: Documentos, Estanterías y Categorías
    mountComponent('DocumentosManagement', DocumentosManagement);
    mountById('documentos-management-root', DocumentosManagement);
    mountComponent('EstanteriasManagement', EstanteriasManagement);
    mountById('estanterias-management-root', EstanteriasManagement);
    mountComponent('CategoriasManagement', CategoriasManagement);
    mountById('categorias-management-root', CategoriasManagement);

    // OGA: Carga Previa Bienes Públicos
    mountComponent('UnidadesAdministrativasManagement', UnidadesAdministrativasManagement);
    mountById('unidades-administrativas-root', UnidadesAdministrativasManagement);
    mountComponent('UsuariosDeUsoManagement', UsuariosDeUsoManagement);
    mountById('usuarios-de-uso-root', UsuariosDeUsoManagement);
    mountComponent('UsuariosAdministrativosManagement', UsuariosAdministrativosManagement);
    mountById('usuarios-administrativos-root', UsuariosAdministrativosManagement);
    mountComponent('FormasAdquisicionManagement', FormasAdquisicionManagement);
    mountById('formas-adquisicion-root', FormasAdquisicionManagement);
    mountComponent('EstadosUsoManagement', EstadosUsoManagement);
    mountById('estados-uso-root', EstadosUsoManagement);
    mountComponent('ColorsManagement', ColorsManagement);
    mountById('colors-root', ColorsManagement);
    mountComponent('CondicionesManagement', CondicionesManagement);
    mountById('condiciones-root', CondicionesManagement);

    // OGH: Digitalización de Documentos
    mountComponent('DigitalizacionDocumentos', DigitalizacionDocumentos);
    mountById('digitalizacion-documentos-root', DigitalizacionDocumentos);

    // Documentos & Selectores auxiliares
    mountComponent('DocumentViewer', DocumentViewer);
    mountComponent('EstanteriaSelector', EstanteriaSelector);

    // Notification Center (si existe el elemento)
    mountById('react-notification-center', NotificationCenter);
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initReactRoots();
            initGlobalDownloadInterceptor();
        });
    } else {
        initReactRoots();
        initGlobalDownloadInterceptor();
    }
    window.addEventListener('load', () => {
        initReactRoots();
        initGlobalDownloadInterceptor();
    });
}

// Exportar componentes para uso global
if (typeof window !== 'undefined') {
    window.ReactComponents = {
        ActivityLog,
        UsersManagement,
        RolesManagement,
        PermisosManagement,
        BienesPublicos,
        InventarioOficinas,
        SalidasOficinas,
        Consumibles,
        DigitalizacionDocumentos,
        DocumentosManagement,
        EstanteriasManagement,
        CategoriasManagement,
        DocumentViewer,
        EstanteriaSelector,
        NotificationCenter
    };
}

// Exportar funciones helpers
export function useFetch(url, options = {}) {
    const [data, setData] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);

    React.useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const response = await fetch(url, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        ...options.headers,
                    },
                    ...options,
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                setData(result);
                setError(null);
            } catch (e) {
                setError(e.message);
                setData(null);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [url]);

    return { data, loading, error };
}

export function useDebounce(value, delay = 300) {
    const [debouncedValue, setDebouncedValue] = React.useState(value);

    React.useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
}

export default {
    ActivityLog,
    UsersManagement,
    RolesManagement,
    PermisosManagement,
    BienesPublicos,
    InventarioOficinas,
    SalidasOficinas,
    Consumibles,
    DigitalizacionDocumentos,
    DocumentosManagement,
    EstanteriasManagement,
    CategoriasManagement,
    DocumentViewer,
    EstanteriaSelector,
    NotificationCenter,
    useFetch,
    useDebounce
};
