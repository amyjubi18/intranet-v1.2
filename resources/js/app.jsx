import React from 'react';
import ReactDOM from 'react-dom/client';
import DocumentViewer from './components/documents/DocumentViewer';
import EstanteriaSelector from './components/documents/EstanteriaSelector';
import NotificationCenter from './components/ui/NotificationCenter';
import ActivityLog from './components/gtic/ActivityLog';
import BienesPublicos from './components/administracion/BienesPublicos';
import InventarioOficinas from './components/administracion/InventarioOficinas';
import Consumibles from './components/administracion/Consumibles';
import DigitalizacionDocumentos from './components/gestion_humana/DigitalizacionDocumentos';
import UsersManagement from './components/gtic/UsersManagement';
import RolesManagement from './components/gtic/RolesManagement';
import PermisosManagement from './components/gtic/PermisosManagement';
import DocumentosManagement from './components/administracion/DocumentosManagement';
import EstanteriasManagement from './components/administracion/EstanteriasManagement';
import CategoriasManagement from './components/administracion/CategoriasManagement';

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

    // OGA: Inventario de Oficina y Consumibles
    mountComponent('InventarioOficinas', InventarioOficinas);
    mountById('inventario-oficinas-root', InventarioOficinas);
    mountComponent('Consumibles', Consumibles);
    mountById('consumibles-root', Consumibles);

    // OGA: Documentos, Estanterías y Categorías
    mountComponent('DocumentosManagement', DocumentosManagement);
    mountById('documentos-management-root', DocumentosManagement);
    mountComponent('EstanteriasManagement', EstanteriaSelector);
    mountComponent('EstanteriasManagement', EstanteriasManagement);
    mountById('estanterias-management-root', EstanteriasManagement);
    mountComponent('CategoriasManagement', CategoriasManagement);
    mountById('categorias-management-root', CategoriasManagement);

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
        document.addEventListener('DOMContentLoaded', initReactRoots);
    } else {
        initReactRoots();
    }
    window.addEventListener('load', initReactRoots);
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
