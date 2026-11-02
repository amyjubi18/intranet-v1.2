# TODO - Implementar Buscadores en Tiempo Real

## Objetivo
Agregar debounce a los buscadores de tipo_documentos y area_adscrita para búsqueda automática mientras el usuario escribe.

## Archivos a Modificar

### ✅ Módulo Gestión Humana - Tipo Documentos
- [ ] `resources/views/gestion-humana/tipo_documentos/index.blade.php`
  - Agregar `id="searchForm"` al formulario
  - Agregar `id="tipoDocumentoSearchInput"` al input
  - Remover botón "🔍 Buscar" (mantener solo botón limpiar)
  - Agregar script de debounce (300ms)

### ✅ Módulo Gestión Humana - Área Adscrita
- [ ] `resources/views/gestion-humana/area_adscrita/index.blade.php`
  - Agregar `id="searchForm"` al formulario
  - Agregar `id="areaAdscritaSearchInput"` al input
  - Remover botón "🔍 Buscar" (mantener solo botón limpiar)
  - Agregar script de debounce (300ms)

## Patrón de Implementación

```javascript
// Debounce de 300ms para búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tipoDocumentoSearchInput');
    const searchForm = document.getElementById('searchForm');

    if (searchInput && searchForm) {
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);

            // Debounce: esperar 300ms antes de buscar
            searchTimeout = setTimeout(function() {
                searchForm.submit();
            }, 300);
        });
    }
});
```

## Progreso de Implementación

### Pendiente de Iniciar
- Ninguno

### En Progreso
- Ninguno

### Completado
- [x] tipo_documentos/index.blade.php - ✅ Debounce implementado (300ms)
- [x] area_adscrita/index.blade.php - ✅ Debounce implementado (300ms)
- [x] BusquedaController.php - ✅ Agregadas tablas de tipo_documentos y areas_adscritas al buscador global
- [x] app.blade.php - ✅ Agregadas rutas de navegación para tipo_documentos y areas_adscritas
- [x] navigation.blade.php - ✅ Logo ahora es clickeable y lleva al inicio
- [x] BusquedaController.php - ✅ Implementada filtración por roles/permisos del usuario autenticado

