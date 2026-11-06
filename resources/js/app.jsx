import React from 'react';
import ReactDOM from 'react-dom/client';
import DocumentViewer from './components/documents/DocumentViewer';
import EstanteriaSelector from './components/documents/EstanteriaSelector';
import NotificationCenter from './components/ui/NotificationCenter';
import ActivityLog from './components/gtic/ActivityLog';

// Helper global para montar componentes React en cualquier vista Blade
function mountComponent(componentName, component) {
    const elements = document.querySelectorAll(`[data-react-component="${componentName}"]`);

    elements.forEach(element => {
        const props = JSON.parse(element.dataset.props || '{}');
        const root = ReactDOM.createRoot(element);
        root.render(React.createElement(component, props));
    });
}

// Inicializar componentes cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Mount Activity Log (GTIC)
    mountComponent('ActivityLog', ActivityLog);
    const activityLogEl = document.getElementById('activity-log-root');
    if (activityLogEl && !activityLogEl.hasAttribute('data-react-mounted')) {
        activityLogEl.setAttribute('data-react-mounted', 'true');
        const root = ReactDOM.createRoot(activityLogEl);
        root.render(<ActivityLog />);
    }

    // Mount Document Viewer
    mountComponent('DocumentViewer', DocumentViewer);

    // Mount Estanteria Selector
    mountComponent('EstanteriaSelector', EstanteriaSelector);

    // Mount Notification Center (solo si existe el elemento)
    const notificationElement = document.getElementById('react-notification-center');
    if (notificationElement) {
        const root = ReactDOM.createRoot(notificationElement);
        root.render(<NotificationCenter />);
    }
});

// Exportar componentes para uso global
window.ReactComponents = {
    ActivityLog,
    DocumentViewer,
    EstanteriaSelector,
    NotificationCenter
};

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
    DashboardMetrics,
    DocumentViewer,
    EstanteriaSelector,
    NotificationCenter,
    useFetch,
    useDebounce
};

