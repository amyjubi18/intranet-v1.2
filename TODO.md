# Plan: Corregir sistema de notificaciones leídas

## Problema
El contador de notificaciones se mostraba constantemente aunque el usuario ya hubiera visto las notificaciones. El usuario solo quiere ver el badge cuando hay notificaciones nuevas que no ha visto.

## Solución
Crear un sistema de seguimiento de notificaciones leídas usando una tabla en la base de datos.

## Cambios realizados

### Base de datos
- [x] Crear migración `2026_01_28_154204_create_user_notifications_table.php`
- [x] Crear modelo `UserNotification.php`
- [x] Ejecutar migración

### Backend
- [x] Crear `NotificationController.php` con métodos:
  - `markAllAsRead()` - Marcar todas como leídas
  - `markAsRead()` - Marcar una específica como leída
  - `getUnreadCount()` - Obtener conteo de no leídas
- [x] Agregar rutas en `web.php`
- [x] Actualizar `NotificationComposer.php` para:
  - Generar hash único para cada notificación
  - Verificar si ya fue leída antes de mostrar
  - Guardar/actualizar registros de notificaciones

### Frontend
- [x] Actualizar JavaScript en `app.blade.php` para usar la API al marcar como leídas
- [x] El dropdown ahora solo muestra notificaciones no leídas

## Funcionamiento
1. El sistema genera un hash único para cada tipo de notificación (basado en tipo + cantidad + fecha)
2. Cuando el usuario ve una notificación por primera vez, se crea un registro en `user_notifications`
3. Si el contenido de la notificación cambia (diferente hash), se considera como nueva
4. Al hacer clic en "Marcar todo como leído" o abrir el modal, se actualiza el registro

## Archivos modificados
- `database/migrations/2026_01_28_154204_create_user_notifications_table.php`
- `app/Models/UserNotification.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Http/View/Composers/NotificationComposer.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`

