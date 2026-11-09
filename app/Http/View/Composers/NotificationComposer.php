<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Biene;
use App\Models\Impresora;

class NotificationComposer
{
    /**
     * Limpia la caché de notificaciones de un usuario
     */
    public static function clearUserCache($userId = null)
    {
        $id = $userId ?: Auth::id();
        if ($id) {
            \Illuminate\Support\Facades\Cache::forget("user_notifs_data_{$id}");
        }
    }

    /**
     * Vincula datos de notificaciones a la vista con caché inteligente de alto rendimiento
     */
    public function compose(View $view)
    {
        $user = Auth::user();
        if (!$user) {
            $view->with('notificaciones', []);
            $view->with('totalNotificaciones', 0);
            return;
        }

        $cacheKey = "user_notifs_data_{$user->id}";
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 20, function () {
            $notificaciones = $this->obtenerNotificaciones();
            $totalNoLeidas = 0;
            foreach ($notificaciones as $n) {
                if (empty($n['leida'])) {
                    $totalNoLeidas++;
                }
            }
            return [
                'notificaciones' => $notificaciones,
                'totalNotificaciones' => $totalNoLeidas,
            ];
        });

        $view->with('notificaciones', $data['notificaciones']);
        $view->with('totalNotificaciones', $data['totalNotificaciones']);
    }

    /**
     * Genera un hash único para la notificación basado en su contenido
     */
    private function generateNotificationHash($type, $count)
    {
        return md5($type . '_' . $count . '_' . date('Y-m-d'));
    }

    /**
     * Cuenta el total de notificaciones NO LEÍDAS basándose estrictamente en las notificaciones autorizadas
     */
    private function contarTotalNotificacionesNoLeidas()
    {
        $notificaciones = $this->obtenerNotificaciones();
        $total = 0;
        foreach ($notificaciones as $n) {
            if (empty($n['leida'])) {
                $total++;
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
     * Obtiene todas las notificaciones del sistema (incluyendo las ya leídas)
     */
    public function obtenerNotificaciones()
    {
        $notificaciones = [];
        $user = Auth::user();

        // Si el usuario no está autenticado, retornar vacío
        if (!$user) {
            return $notificaciones;
        }

        // Helper para verificar permisos estrictos del usuario (basado en Spatie permissions del usuario)
        $can = function($permission) use ($user) {
            $perms = (array) $permission;
            foreach ($perms as $p) {
                if ($user->can($p)) {
                    return true;
                }
            }
            return false;
        };

        // 0. Notificaciones de Laravel (incluye birthday wishes) - MOSTRAR TODAS, no solo no leídas
        $laravelNotifications = DatabaseNotification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($laravelNotifications as $notif) {
            $data = $notif->data;
            $tipo = $data['type'] ?? 'general';

            if ($tipo === 'birthday_wish') {
                $notificaciones[] = [
                    'tipo' => 'birthday_wish',
                    'icono' => '🎂',
                    'titulo' => '¡Feliz Cumpleaños!',
                    'mensaje' => $data['message'] ?? 'Alguien te desea un feliz cumpleaños',
                    'cantidad' => 1,
                    'hora' => $notif->created_at->diffForHumans(),
                    'color' => 'green',
                    'is_laravel_notification' => true,
                    'notification_id' => $notif->id,
                    'leida' => $notif->read_at ? true : false
                ];
            }
        }

        // 1. Perfil incompleto - MOSTRAR SIEMPRE si hay campos incompletos
        $perfil = $user->perfil_incompleto;
        if ($perfil['incompleto']) {
            $hash = $this->generateNotificationHash('perfil_incompleto', $perfil['total']);
            $isRead = $this->isNotificationRead('perfil_incompleto', $hash);
            $this->updateNotificationRecord('perfil_incompleto', $hash, $isRead);

            // Siempre mostrar si hay perfil incompleto
            $nombresCampos = [
                'lastname' => 'Apellido',
                'cedula' => 'Cédula',
                'fecha_nacimiento' => 'Fecha de nacimiento',
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
                'ruta_ver_todas' => \Illuminate\Support\Facades\Route::has('profile.edit') ? route('profile.edit') : '/profile',
                'color' => 'blue',
                'leida' => $isRead
            ];
        }

        // 2. Stock bajo en Consumibles - Solo si tiene permiso de consumibles
        if ($can(['almacen_inv_index', 'inv-consumibles', 'cartucho_index'])) {
            $countConsumibles = $this->contarStockBajoConsumibles();
            if ($countConsumibles > 0) {
                $hash = $this->generateNotificationHash('stock_consumibles', $countConsumibles);
                $isRead = $this->isNotificationRead('stock_consumibles', $hash);
                $this->updateNotificationRecord('stock_consumibles', $hash, $isRead);

                // Siempre mostrar si hay consumibles con stock bajo
                $itemsConsumibles = $this->obtenerItemsConsumibles();
                $notificaciones[] = [
                    'tipo' => 'stock_consumibles',
                    'icono' => '🖨️',
                    'titulo' => 'Stock Bajo en Consumibles',
                    'mensaje' => "{$itemsConsumibles->count()} consumible(s) requieren atención",
                    'cantidad' => $itemsConsumibles->count(),
                    'items' => $itemsConsumibles,
                    'tipo_item' => 'consumible',
                    'ruta_ver_todas' => $this->resolverRutaSegura('admin', 'administracion.almacenamiento_impresoras.index', '/administracion/almacenamiento_impresoras'),
                    'color' => 'orange',
                    'leida' => $isRead
                ];
            } else {
                // Si no hay notificaciones, eliminar el registro si existe
                $this->clearNotificationRecord('stock_consumibles');
            }
        }

        // 3. Stock bajo en Oficina - Solo si tiene permiso de oficina
        if ($can(['inventarioofic_index', 'inv-oficina', 'articulo_index'])) {
            $countOficina = $this->contarStockBajoOficina();
            if ($countOficina > 0) {
                $hash = $this->generateNotificationHash('stock_oficina', $countOficina);
                $isRead = $this->isNotificationRead('stock_oficina', $hash);
                $this->updateNotificationRecord('stock_oficina', $hash, $isRead);

                // Siempre mostrar si hay artículos de oficina con stock bajo
                $itemsOficina = $this->obtenerItemsOficina();
                $notificaciones[] = [
                    'tipo' => 'stock_oficina',
                    'icono' => '📦',
                    'titulo' => 'Stock Bajo en Oficina',
                    'mensaje' => "{$itemsOficina->count()} artículo(s) requieren atención",
                    'cantidad' => $itemsOficina->count(),
                    'items' => $itemsOficina,
                    'tipo_item' => 'articulo',
                    'ruta_ver_todas' => $this->resolverRutaSegura('admin', 'administracion.inventario_oficinas.index', '/administracion/inventario_oficinas'),
                    'color' => 'yellow',
                    'leida' => $isRead
                ];
            } else {
                $this->clearNotificationRecord('stock_oficina');
            }
        }

        // 4. Usuarios con datos incompletos - Solo si tiene permiso de gestión de usuarios
        if ($can(['user_index', 'user_edit'])) {
            $countUsuariosIncompletos = $this->contarUsuariosConDatosFaltantes();
            if ($countUsuariosIncompletos > 0) {
                $hash = $this->generateNotificationHash('usuarios_incompletos', $countUsuariosIncompletos);
                $isRead = $this->isNotificationRead('usuarios_incompletos', $hash);
                $this->updateNotificationRecord('usuarios_incompletos', $hash, $isRead);

                $usuariosIncompletos = $this->obtenerUsuariosConDatosFaltantes();
                $notificaciones[] = [
                    'tipo' => 'usuarios_incompletos',
                    'icono' => '👥',
                    'titulo' => 'Usuarios con Datos Incompletos',
                    'mensaje' => "{$usuariosIncompletos->count()} usuario(s) tienen perfil incompleto",
                    'cantidad' => $usuariosIncompletos->count(),
                    'items' => $usuariosIncompletos,
                    'tipo_item' => 'usuario',
                    'ruta_ver_todas' => $this->resolverRutaSegura('gtic', 'users.index', '/users'),
                    'color' => 'red',
                    'leida' => $isRead
                ];
            } else {
                $this->clearNotificationRecord('usuarios_incompletos');
            }
        }

        // 5. Bienes dañados o en mal estado - Solo si tiene permiso de bienes públicos
        if ($can(['bienes_index', 'bienes', 'bienes_edit'])) {
            $countBienesDaniados = $this->contarBienesDaniados();
            if ($countBienesDaniados > 0) {
                $hash = $this->generateNotificationHash('bienes_daniados', $countBienesDaniados);
                $isRead = $this->isNotificationRead('bienes_daniados', $hash);
                $this->updateNotificationRecord('bienes_daniados', $hash, $isRead);

                $bienesDaniados = $this->obtenerBienesDaniados();
                $notificaciones[] = [
                    'tipo' => 'bienes_daniados',
                    'icono' => '🔧',
                    'titulo' => 'Bienes Dañados',
                    'mensaje' => "{$bienesDaniados->count()} bien(es) necesitan reparación",
                    'cantidad' => $bienesDaniados->count(),
                    'items' => $bienesDaniados,
                    'tipo_item' => 'bien',
                    'ruta_ver_todas' => $this->resolverRutaSegura('admin', 'administracion.bienes_publicos.index', '/administracion/bienes_publicos'),
                    'color' => 'red',
                    'leida' => $isRead
                ];
            } else {
                $this->clearNotificationRecord('bienes_daniados');
            }
        }

        // 6. Bienes sin asignar - Solo si tiene permiso de bienes públicos
        if ($can(['bienes_index', 'bienes', 'bienes_edit'])) {
            $countBienesSinAsignar = $this->contarBienesSinAsignar();
            if ($countBienesSinAsignar > 0) {
                $hash = $this->generateNotificationHash('bienes_sin_asignar', $countBienesSinAsignar);
                $isRead = $this->isNotificationRead('bienes_sin_asignar', $hash);
                $this->updateNotificationRecord('bienes_sin_asignar', $hash, $isRead);

                $bienesSinAsignar = $this->obtenerBienesSinAsignar();
                $notificaciones[] = [
                    'tipo' => 'bienes_sin_asignar',
                    'icono' => '📋',
                    'titulo' => 'Bienes Sin Asignar',
                    'mensaje' => "{$bienesSinAsignar->count()} bien(es) no tienen usuario asignado",
                    'cantidad' => $bienesSinAsignar->count(),
                    'items' => $bienesSinAsignar,
                    'tipo_item' => 'bien',
                    'ruta_ver_todas' => $this->resolverRutaSegura('admin', 'administracion.bienes_publicos.index', '/administracion/bienes_publicos'),
                    'color' => 'blue',
                    'leida' => $isRead
                ];
            } else {
                $this->clearNotificationRecord('bienes_sin_asignar');
            }
        }

        // 7. Impresoras con problemas - Solo si tiene permiso de impresoras
        if ($can(['impresora_index', 'inventarioimpre_index', 'impresora_edit'])) {
            $countImpresorasProblema = $this->contarImpresorasConProblemas();
            if ($countImpresorasProblema > 0) {
                $hash = $this->generateNotificationHash('impresoras_problema', $countImpresorasProblema);
                $isRead = $this->isNotificationRead('impresoras_problema', $hash);
                $this->updateNotificationRecord('impresoras_problema', $hash, $isRead);

                $impresorasProblema = $this->obtenerImpresorasConProblemas();
                $notificaciones[] = [
                    'tipo' => 'impresoras_problema',
                    'icono' => '🖨️',
                    'titulo' => 'Impresoras con Problemas',
                    'mensaje' => "{$impresorasProblema->count()} impresoras necesitan mantenimiento",
                    'cantidad' => $impresorasProblema->count(),
                    'items' => $impresorasProblema,
                    'tipo_item' => 'impresora',
                    'ruta_ver_todas' => $this->resolverRutaSegura('admin', 'administracion.impresoras.index', '/administracion/impresoras'),
                    'color' => 'orange',
                    'leida' => $isRead
                ];
            } else {
                $this->clearNotificationRecord('impresoras_problema');
            }
        }

        return $notificaciones;
    }

    /**
     * Resuelve de forma segura la URL de la notificación para soportar arquitectura modular inter-contenedores con SSO.
     */
    private function resolverRutaSegura(string $targetModule, string $routeName, string $fallbackPath): string
    {
        $currentModule = config('modules.current');
        $isCurrent = match($targetModule) {
            'admin' => in_array($currentModule, ['administracion', 'admin']),
            'gh'    => in_array($currentModule, ['gestion-humana', 'gh']),
            'gtic'  => in_array($currentModule, ['gtic']),
            default => false,
        };

        if ($isCurrent && \Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName);
        }

        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            return route($routeName);
        }

        if (\Illuminate\Support\Facades\Route::has('sso.go')) {
            return route('sso.go', ['module' => $targetModule, 'redirect' => $fallbackPath]);
        }

        return $fallbackPath;
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

    /**
     * Cuenta usuarios con datos faltantes en su perfil
     */
    private function contarUsuariosConDatosFaltantes()
    {
        return User::where(function ($query) {
            $query->whereNull('lastname')
                ->orWhereNull('cedula')
                ->orWhereNull('fecha_nacimiento');
        })
        ->where('is_active', true)
        ->count();
    }

    /**
     * Obtiene usuarios con datos faltantes
     */
    private function obtenerUsuariosConDatosFaltantes()
    {
        return User::select('id', 'name', 'lastname', 'cedula', 'email')
            ->where(function ($query) {
                $query->whereNull('lastname')
                    ->orWhereNull('cedula')
                    ->orWhereNull('fecha_nacimiento');
            })
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Cuenta bienes que están dañados o en mal estado
     */
    private function contarBienesDaniados()
    {
        // Buscar bienes con estado "Dañado", "En Reparación", "Fuera de Servicio", etc.
        return Biene::whereHas('estado_del_usos', function ($query) {
            $query->whereRaw('LOWER(estado_del_uso) LIKE ?', ['%dañad%'])
                ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%reparaci%'])
                ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%fuera de servicio%'])
                ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%mal estado%']);
        })->count();
    }

    /**
     * Obtiene bienes dañados o en mal estado
     */
    private function obtenerBienesDaniados()
    {
        return Biene::with('estado_del_usos')
            ->whereHas('estado_del_usos', function ($query) {
                $query->whereRaw('LOWER(estado_del_uso) LIKE ?', ['%dañad%'])
                    ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%reparaci%'])
                    ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%fuera de servicio%'])
                    ->orWhereRaw('LOWER(estado_del_uso) LIKE ?', ['%mal estado%']);
            })
            ->select('id', 'n_interno_bien', 'descripcion', 'marca', 'modelo', 'estado_del_uso_id')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Cuenta bienes sin usuario asignado
     */
    private function contarBienesSinAsignar()
    {
        return Biene::whereNull('usuario_de_uso_id')
            ->orWhere('usuario_de_uso_id', 0)
            ->count();
    }

    /**
     * Obtiene bienes sin usuario asignado
     */
    private function obtenerBienesSinAsignar()
    {
        return Biene::select('id', 'n_interno_bien', 'descripcion', 'marca', 'modelo', 'serial')
            ->where(function ($query) {
                $query->whereNull('usuario_de_uso_id')
                    ->orWhere('usuario_de_uso_id', 0);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Cuenta impresoras con problemas (basado en inventario bajo o sin toner)
     */
    private function contarImpresorasConProblemas()
    {
        // Buscar impresoras que tienen inventario con stock muy bajo o en 0
        return DB::table('impresoras as i')
            ->leftJoin('inventario_impresoras as inv', 'i.id', '=', 'inv.impresora_id')
            ->select('i.id')
            ->groupBy('i.id')
            ->havingRaw('COALESCE(SUM(inv.unidades_disponibles), 0) <= 2')
            ->count();
    }

    /**
     * Obtiene impresoras con problemas
     */
    private function obtenerImpresorasConProblemas()
    {
        return DB::table('impresoras as i')
            ->leftJoin('inventario_impresoras as inv', 'i.id', '=', 'inv.impresora_id')
            ->select(
                'i.id',
                'i.impresora',
                DB::raw('COALESCE(SUM(inv.unidades_disponibles), 0) as stock_total')
            )
            ->groupBy('i.id', 'i.impresora')
            ->havingRaw('COALESCE(SUM(inv.unidades_disponibles), 0) <= 2')
            ->orderBy('stock_total', 'asc')
            ->limit(10)
            ->get();
    }
}

