# TODO: Solución de gráficos en administracion.reportes

## Problema
Los gráficos no se ven con el usuario "permisos_hilda" porque hay una inconsistencia en los permisos:
- El controlador permite acceso con `inventarioimpre_index` O `inventarioofic_index`
- La vista solo verifica `inventarioimpre_index`
- El permiso `inventarioimpre_index` no existe en la base de datos

## Solución

### 1. Agregar permisos faltantes al PermissionSeeder
- [x] Agregar `inventarioimpre_index`
- [x] Agregar `inventarioimpre_create`
- [x] Agregar `inventarioimpre_edit`
- [x] Agregar `inventarioimpre_destroy`
- [x] Agregar `inventarioofic_index`
- [x] Agregar `inventarioofic_create`
- [x] Agregar `inventarioofic_edit`
- [x] Agregar `inventarioofic_destroy`

### 2. Actualizar vistas para usar la directiva correcta
- [x] `resources/views/administracion/reportes/index.blade.php` - Cambiar `@can('inventarioimpre_index')` por `@canany(['inventarioimpre_index', 'inventarioofic_index'])`
- [x] `resources/views/administracion/inventario_impresoras/index.blade.php` - Cambiar `@can('inventarioimpre_index')` por `@canany(['inventarioimpre_index', 'inventarioofic_index'])`

### 3. Ejecutar seeder para crear permisos
- [x] `php artisan db:seed --class=PermissionSeeder` (Permisos ya existían en BD)

## RESUELTO ✓
Los permisos `inventarioimpre_index` y `inventarioofic_index` están disponibles y las vistas ahora permiten acceso a usuarios con cualquiera de estos permisos.

## Próximo paso (Manual)
Asignar el permiso `inventarioofic_index` (o `inventarioimpre_index`) al rol del usuario "permisos_hilda" para que pueda ver los gráficos.

