import React, { useState, useEffect, useCallback } from 'react';

// Componente: Notificación Individual
function NotificationItem({ notification, onMarkAsRead, onDelete, onClick }) {
    const [isExpanded, setIsExpanded] = useState(false);

    const getIcon = (type) => {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️',
            document: '📄',
            user: '👤',
            permission: '🔑',
            system: '⚙️'
        };
        return icons[type] || icons.info;
    };

    const getTimeAgo = (date) => {
        const now = new Date();
        const diff = now - new Date(date);
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Ahora mismo';
        if (minutes < 60) return `Hace ${minutes}m`;
        if (hours < 24) return `Hace ${hours}h`;
        return `Hace ${days}d`;
    };

    return (
        <div
            className={`relative p-4 rounded-xl transition-all cursor-pointer ${
                notification.read
                    ? 'bg-white hover:bg-gray-50'
                    : 'bg-blue-50 border-l-4 border-blue-500'
            }`}
            onClick={() => {
                if (!notification.read) onMarkAsRead(notification.id);
                if (onClick) onClick(notification);
            }}
        >
            <div className="flex items-start gap-3">
                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-lg ${notification.read ? 'bg-gray-100' : 'bg-blue-100'}`}>
                    {getIcon(notification.type)}
                </div>

                <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between gap-2">
                        <h4 className={`font-semibold truncate ${notification.read ? 'text-gray-700' : 'text-gray-900'}`}>
                            {notification.title}
                        </h4>
                        <span className="flex-shrink-0 text-xs text-gray-400">
                            {getTimeAgo(notification.createdAt)}
                        </span>
                    </div>

                    <p className={`text-sm mt-1 ${notification.read ? 'text-gray-500' : 'text-gray-600'}`}>
                        {notification.message}
                    </p>

                    {notification.action && (
                        <button className="mt-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                            {notification.action}
                        </button>
                    )}
                </div>

                <div className="flex items-center gap-1">
                    {!notification.read && (
                        <div className="w-2 h-2 bg-blue-500 rounded-full" title="Sin leer" />
                    )}
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            onDelete(notification.id);
                        }}
                        className="flex items-center justify-center w-8 h-8 text-gray-400 transition-colors rounded-lg hover:bg-gray-200 hover:text-gray-600"
                        title="Eliminar"
                    >
                        🗑️
                    </button>
                </div>
            </div>
        </div>
    );
}

// Componente: Panel de Notificaciones
export default function NotificationCenter({
    endpoint = '/api/notifications',
    maxVisible = 5,
    autoRefresh = true,
    refreshInterval = 30000,
    showBadge = true
}) {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [filter, setFilter] = useState('all'); // 'all', 'unread', 'read'

    // Notificaciones de ejemplo
    const demoNotifications = [
        {
            id: 1,
            type: 'document',
            title: 'Nuevo documento agregado',
            message: 'Se ha agregado el documento "Contrato de Servicios 2024" a la estanteria de Contratos.',
            read: false,
            createdAt: new Date(Date.now() - 5 * 60000).toISOString(),
            action: 'Ver documento'
        },
        {
            id: 2,
            type: 'user',
            title: 'Usuario registrado',
            message: 'Maria Garcia se ha registrado exitosamente en el sistema.',
            read: false,
            createdAt: new Date(Date.now() - 15 * 60000).toISOString(),
            action: 'Ver perfil'
        },
        {
            id: 3,
            type: 'warning',
            title: 'Alerta de almacenamiento',
            message: 'La estanteria "Documentos Generales" esta casi llena (90% de capacidad).',
            read: true,
            createdAt: new Date(Date.now() - 2 * 3600000).toISOString(),
            action: 'Gestionar espacio'
        },
        {
            id: 4,
            type: 'success',
            title: 'Backup completado',
            message: 'El backup automatico del sistema se ha completado exitosamente.',
            read: true,
            createdAt: new Date(Date.now() - 24 * 3600000).toISOString()
        },
        {
            id: 5,
            type: 'permission',
            title: 'Permisos actualizados',
            message: 'Se han modificado los permisos del rol "Editor" para el modulo de documentos.',
            read: true,
            createdAt: new Date(Date.now() - 48 * 3600000).toISOString()
        }
    ];

    const fetchNotifications = useCallback(async () => {
        setLoading(true);
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (response.ok) {
                const data = await response.json();
                setNotifications(data.notifications || []);
                setUnreadCount(data.unreadCount || 0);
            }
        } catch (error) {
            console.warn('Usando notificaciones de demo:', error.message);
            // Usar demo si el endpoint no responde
            if (notifications.length === 0) {
                setNotifications(demoNotifications);
                setUnreadCount(demoNotifications.filter(n => !n.read).length);
            }
        } finally {
            setLoading(false);
        }
    }, [endpoint]);

    useEffect(() => {
        fetchNotifications();

        if (autoRefresh) {
            const interval = setInterval(fetchNotifications, refreshInterval);
            return () => clearInterval(interval);
        }
    }, [fetchNotifications, autoRefresh, refreshInterval]);

    const markAsRead = async (id) => {
        setNotifications(prev =>
            prev.map(n => n.id === id ? { ...n, read: true } : n)
        );
        setUnreadCount(prev => Math.max(0, prev - 1));

        try {
            await fetch(`${endpoint}/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        setNotifications(prev => prev.map(n => ({ ...n, read: true })));
        setUnreadCount(0);

        try {
            await fetch(`${endpoint}/mark-all-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    };

    const deleteNotification = async (id) => {
        setNotifications(prev => prev.filter(n => n.id !== id));
        const notification = notifications.find(n => n.id === id);
        if (notification && !notification.read) {
            setUnreadCount(prev => Math.max(0, prev - 1));
        }

        try {
            await fetch(`${endpoint}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    };

    const filteredNotifications = notifications.filter(n => {
        if (filter === 'unread') return !n.read;
        if (filter === 'read') return n.read;
        return true;
    }).slice(0, maxVisible);

    return (
        <div className="relative">
            {/* Botón de campanita */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="relative p-2 transition-colors rounded-xl hover:bg-gray-100"
                title="Notificaciones"
            >
                <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>

                {showBadge && unreadCount > 0 && (
                    <span className="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full -top-1 -right-1 animate-pulse">
                        {unreadCount > 9 ? '9+' : unreadCount}
                    </span>
                )}
            </button>

            {/* Panel de notificaciones */}
            {isOpen && (
                <>
                    {/* Backdrop */}
                    <div
                        className="fixed inset-0 z-40"
                        onClick={() => setIsOpen(false)}
                    />

                    {/* Dropdown */}
                    <div className="absolute right-0 z-50 mt-2 overflow-hidden bg-white border border-gray-100 shadow-2xl top-full w-96 rounded-2xl">
                        {/* Header */}
                        <div className="p-4 border-b border-gray-100">
                            <div className="flex items-center justify-between">
                                <h3 className="font-bold text-gray-800">🔔 Notificaciones</h3>
                                {unreadCount > 0 && (
                                    <button
                                        onClick={markAllAsRead}
                                        className="text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        Marcar todas como leidas
                                    </button>
                                )}
                            </div>

                            {/* Filtros */}
                            <div className="flex gap-2 mt-3">
                                {['all', 'unread', 'read'].map(f => (
                                    <button
                                        key={f}
                                        onClick={() => setFilter(f)}
                                        className={`px-3 py-1 rounded-lg text-sm transition-colors ${
                                            filter === f
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        }`}
                                    >
                                        {f === 'all' ? 'Todas' : f === 'unread' ? 'Sin leer' : 'Leidas'}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* Lista de notificaciones */}
                        <div className="overflow-y-auto max-h-96">
                            {loading && notifications.length === 0 ? (
                                <div className="p-8 text-center">
                                    <div className="w-8 h-8 mx-auto mb-2 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                                    <p className="text-gray-500">Cargando...</p>
                                </div>
                            ) : filteredNotifications.length > 0 ? (
                                <div className="divide-y divide-gray-100">
                                    {filteredNotifications.map(notification => (
                                        <div key={notification.id} className="p-2">
                                            <NotificationItem
                                                notification={notification}
                                                onMarkAsRead={markAsRead}
                                                onDelete={deleteNotification}
                                            />
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-8 text-center">
                                    <div className="mb-2 text-4xl">🔔</div>
                                    <p className="text-gray-500">No hay notificaciones</p>
                                </div>
                            )}
                        </div>

                        {/* Footer */}
                        <div className="p-3 border-t border-gray-100 bg-gray-50">
                            <a
                                href="/notificaciones"
                                className="block text-sm font-medium text-center text-blue-600 hover:text-blue-800"
                            >
                                Ver todas las notificaciones
                            </a>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}

