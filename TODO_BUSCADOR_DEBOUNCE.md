# TODO - Implementar Debounce en Todos los Buscadores

## Objetivo
Actualizar todos los buscadores del sistema para que se actualicen automáticamente cuando el usuario escribe, sin necesidad de hacer clic en el botón de buscar.

## Patrón de Implementación
```javascript
// Debounce de 500ms para búsqueda en tiempo real
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            // Auto-submit del formulario de filtros
            document.getElementById('filterForm').submit();
        }, 500);
    });
}
```

## Archivos a Modificar

### Módulo GTIC
- [ ] `resources/views/gtic/permisos/index.blade.php`
- [ ] `resources/views/gtic/roles/index.blade.php`
- [ ] `resources/views/gtic/activity-log/index.blade.php`

### Módulo Administración - General
- [ ] `resources/views/administracion/forma_adquisicion/index.blade.php`
- [ ] `resources/views/administracion/documentos/index.blade.php`
- [ ] `resources/views/administracion/estanterias/index.blade.php`
- [ ] `resources/views/administracion/unidad_administrativa/index.blade.php`
- [ ] `resources/views/administracion/impresoras/index.blade.php`
- [ ] `resources/views/administracion/colors/index.blade.php`
- [ ] `resources/views/administracion/salida_impresora/index.blade.php`
- [ ] `resources/views/administracion/cartuchos/index.blade.php`
- [ ] `resources/views/administracion/almacenamiento_impresoras/index.blade.php`
- [ ] `resources/views/administracion/areas/index.blade.php`
- [ ] `resources/views/administracion/condicion_fisica/index.blade.php`

## Progreso de Implementación

### ✅ Módulo GTIC - COMPLETADO
- [x] `resources/views/gtic/permisos/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/gtic/roles/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/gtic/activity-log/index.blade.php` - Sin buscador (solo paginación)

### ✅ Módulo Administración - COMPLETADO
- [x] `resources/views/administracion/forma_adquisicion/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/colors/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/condicion_fisica/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/unidad_administrativa/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/cartuchos/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/estanterias/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/documentos/index.blade.php` - ✅ Debounce implementado
- [x] `resources/views/administracion/impresoras/index.blade.php` - ✅ Ya tenía debounce (mejorado)
- [x] `resources/views/administracion/areas/index.blade.php` - ✅ Usa filtro con dropdown
- [x] `resources/views/users/index.blade.php` - Búsqueda rápida y filtros de texto
- [x] `resources/views/administracion/articulos/index.blade.php` - Búsqueda general
- [x] `resources/views/administracion/inventario_oficinas/index.blade.php` - Búsqueda general
- [x] `resources/views/layouts/app.blade.php` - Buscador global

## Notas
- El debounce de 500ms evita muchas consultas al servidor mientras el usuario escribe
- Se puede ajustar a 300ms para respuestas más rápidas o 800ms para menos carga
- Para selects, se mantiene el cambio automático al seleccionar una opción

