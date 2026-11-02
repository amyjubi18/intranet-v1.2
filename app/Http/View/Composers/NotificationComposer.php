<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserNotification;

class NotificationComposer
{
    /**
     * Vincula datos de notificaciones a la vista
     */
    public function compose(View $view)
    {
        $notificaciones = $this->obtenerNotificaciones();
        $totalNotificaciones = $this->contarTotalNotificacionesNoLeidas();

        $view->with('notificaciones', $notificaciones);
        $view->with('totalNotificaciones', $totalNotificaciones);
    }

    /**
     * Genera un hash único para la notificación basado en su contenido
     */
    private function generateNotificationHash($type, $count)
    {
        return md5($type . '_' . $count . '_' . date('Y-m-d'));
    }

    /**
     * Cuenta el total de notificaciones NO LEÍDAS
     */
    private function contarTotalNotificacionesNoLeidas()
    {
        $total = 0;
        $user = Auth::user();

        if (!$user) {
            return 0;
        }

        // 0. Perfil incompleto (siempre visible para el propio usuario)
        $perfil = $user->perfil_incompleto;
        if ($perfil['incompleto']) {
            $hash = $this->generateNotificationHash('perfil_incompleto', $perfil['total']);
            if (!$this->isNotificationRead('perfil_incompleto', $hash)) {
                $total += 1;
            }
        }

        // 1. Stock bajo en Consumibles
        if ($user->can('almacen_inv_index') || $user->can('gtic')) {
            $countConsumibles = $this->contarStockBajoConsumibles();
            if ($countConsumibles > 0) {
                $hash = $this->generateNotificationHash('stock_consumibles', $countConsumibles);
                if (!$this->isNotificationRead('stock_consumibles', $hash)) {
                    $total += 1;
                }
            }
        }

        // 2. Stock bajo en Oficina
        if ($user->can('inventarioofic_index') || $user->can('gtic')) {
            $countOficina = $this->contarStockBajoOficina();
            if ($countOficina > 0) {
                $hash = $this->generateNotificationHash('stock_oficina', $countOficina);
                if (!$this->isNotificationRead('stock_oficina', $hash)) {
                    $total += 1;
                }
            }
        }

        return $total;
    }

    /**
     * Verifica si una notificación ya fue leída
     */
    private function isNotificationRead($type, $hash)
    {
        $user = Auth::user();
        if (!$user) return true;

        $notification = UserNotification::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->first();

        // Si no existe el registro, no está leída
        if (!$notification) {
            return false;
        }

        // Si el hash cambió, considerar como no leída (nueva notificación)
        if ($notification->hash !== $hash) {
            return false;
        }

        return $notification->isRead();
    }

    /**
     * Obtiene todas las notificaciones del sistema
     */
    private function obtenerNotificaciones()
    {
        $notificaciones = [];
        $user = Auth::user();

        // Si el usuario no está autenticado, retornar vacío
        if (!$user) {
            return $notificaciones;
        }

        // 0. Perfil incompleto
        $perfil = $user->perfil_incompleto;
        if ($perfil['incompleto']) {
            $hash = $this->generateNotificationHash('perfil_incompleto', $perfil['total']);
            $isRead = $this->isNotificationRead('perfil_incompleto', $hash);

            // Guardar/actualizar el registro de notificación
            $this->updateNotificationRecord('perfil_incompleto', $hash, $isRead);

            if (!$isRead) {
                $nombresCampos = [
                    'lastname' => 'Apellido',
                    'cedula' => 'Cédula',
                    'fecha_nacimiento' => 'Fecha de nacimiento',
                    'telefono' => 'Teléfono',
                    'direccion' => 'Dirección'
                ];

                $camposTexto = [];
                foreach ($perfil['campos'] as $campo) {
                    $camposTexto[] = $nombresCampos[$campo] ?? $campo;
                }

                $notificaciones[] = [
                    'tipo' => 'perfil_incompleto',
                    'icono' => '👤',
                    'titulo' => 'Perfil Incompleto',
                    'mensaje' => 'Tu perfil necesita ser completado',
                    'cantidad' => $perfil['total'],
                    'campos_faltantes' => $camposTexto,
                    'ruta_ver_todas' => route('profile.edit'),
                    'color' => 'blue'
                ];
            }
        }

        // 1. Stock bajo en Consumibles
        if ($user->can('almacen_inv_index') || $user->can('gtic')) {
            $countConsumibles = $this->contarStockBajoConsumibles();
            if ($countConsumibles > 0) {
                $hash = $this->generateNotificationHash('stock_consumibles', $countConsumibles);
                $isRead = $this->isNotificationRead('stock_consumibles', $hash);
                $this->updateNotificationRecord('stock_consumibles', $hash, $isRead);

                if (!$isRead) {
                    $itemsConsumibles = $this->obtenerItemsConsumibles();
                    $notificaciones[] = [
                        'tipo' => 'stock_consumibles',
                        'icono' => '🖨️',
                        'titulo' => 'Stock Bajo en Consumibles',
                        'mensaje' => "{$itemsConsumibles->count()} consumible(s) requieren atención",
                        'cantidad' => $itemsConsumibles->count(),
                        'items' => $itemsConsumibles,
                        'ruta_ver_todas' => route('administracion.almacenamiento_impresoras.index'),
                        'color' => 'orange'
                    ];
                }
            } else {
                // Si no hay notificaciones, eliminar el registro si existe
                $this->clearNotificationRecord('stock_consumibles');
            }
        }

        // 2. Stock bajo en Oficina
        if ($user->can('inventarioofic_index') || $user->can('gtic')) {
            $countOficina = $this->contarStockBajoOficina();
            if ($countOficina > 0) {
                $hash = $this->generateNotificationHash('stock_oficina', $countOficina);
                $isRead = $this->isNotificationRead('stock_oficina', $hash);
                $this->updateNotificationRecord('stock_oficina', $hash, $isRead);

                if (!$isRead) {
                    $itemsOficina = $this->obtenerItemsOficina();
                    $notificaciones[] = [
                        'tipo' => 'stock_oficina',
                        'icono' => '📦',
                        'titulo' => 'Stock Bajo en Oficina',
                        'mensaje' => "{$itemsOficina->count()} artículo(s) requieren atención",
                        'cantidad' => $itemsOficina->count(),
                        'items' => $itemsOficina,
                        'ruta_ver_todas' => route('administracion.inventario_oficinas.index'),
                        'color' => 'yellow'
                    ];
                }
            } else {
                $this->clearNotificationRecord('stock_oficina');
            }
        }

        return $notificaciones;
    }

    /**
     * Actualiza el registro de notificación del usuario
     */
    private function updateNotificationRecord($type, $hash, $isRead)
    {
        $user = Auth::user();
        if (!$user) return;

        UserNotification::updateOrCreate(
            ['user_id' => $user->id, 'notification_type' => $type],
            [
                'hash' => $hash,
                'read_at' => $isRead ? now() : null
            ]
        );
    }

    /**
     * Elimina el registro de notificación si ya no hay notificaciones de ese tipo
     */
    private function clearNotificationRecord($type)
    {
        $user = Auth::user();
        if (!$user) return;

        UserNotification::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->delete();
    }

    /**
     * Cuenta consumibles con stock bajo
     */
    private function contarStockBajoConsumibles()
    {
        return DB::table('almacenamiento_impresoras as a')
            ->join('cartuchos as c', 'a.cartucho_id', '=', 'c.id')
            ->select('a.cartucho_id', 'a.colore_id', 'a.fabricante_id')
            ->groupBy('a.cartucho_id', 'a.colore_id', 'a.fabricante_id')
            ->havingRaw('SUM(a.unidades_disponibles) <= COALESCE(MAX(c.stock_min), 5)')
            ->havingRaw('SUM(a.unidades_disponibles) > 0')
            ->count();
    }

    /**
     * Obtiene items de consumibles con stock bajo
     */
    private function obtenerItemsConsumibles()
    {
        return DB::table('almacenamiento_impresoras as a')
            ->join('cartuchos as c', 'a.cartucho_id', '=', 'c.id')
            ->join('colores as col', 'a.colore_id', '=', 'col.id')
            ->select(
                'c.cartucho',
                'col.colore as color',
                DB::raw('SUM(a.unidades_disponibles) as total_disponible'),
                DB::raw('MAX(c.stock_min) as stock_min')
            )
            ->groupBy('c.cartucho', 'col.colore', 'a.cartucho_id', 'a.colore_id', 'a.fabricante_id')
            ->havingRaw('SUM(a.unidades_disponibles) <= COALESCE(MAX(c.stock_min), 5)')
            ->havingRaw('SUM(a.unidades_disponibles) > 0')
            ->orderBy('total_disponible', 'asc')
            ->get();
    }

    /**
     * Cuenta artículos de oficina con stock bajo
     */
    private function contarStockBajoOficina()
    {
        return DB::table('inventario_oficinas as i')
            ->join('articulos as a', 'i.articulo_id', '=', 'a.id')
            ->select('i.articulo_id', 'i.unidad_medida_id')
            ->groupBy('i.articulo_id', 'i.unidad_medida_id')
            ->havingRaw('SUM(i.unidades_disponibles) <= COALESCE(MAX(a.stock_min), 5)')
            ->havingRaw('SUM(i.unidades_disponibles) > 0')
            ->count();
    }

    /**
     * Obtiene items de oficina con stock bajo
     */
    private function obtenerItemsOficina()
    {
        return DB::table('inventario_oficinas as i')
            ->join('articulos as a', 'i.articulo_id', '=', 'a.id')
            ->select(
                'a.articulo',
                DB::raw('SUM(i.unidades_disponibles) as total_disponible'),
                DB::raw('MAX(a.stock_min) as stock_min')
            )
            ->groupBy('a.articulo', 'i.articulo_id', 'i.unidad_medida_id')
            ->havingRaw('SUM(i.unidades_disponibles) <= COALESCE(MAX(a.stock_min), 5)')
            ->havingRaw('SUM(i.unidades_disponibles) > 0')
            ->orderBy('total_disponible', 'asc')
            ->get();
    }
}

