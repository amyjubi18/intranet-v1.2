# Tareas para actualizar filtro de impresoras.index

## Objetivo: Hacer que el filtro de impresoras.index sea como el de consumibles.index

### Tareas:
- [x] 1. Analizar archivos de origen (impresoras/index.blade.php y consumibles/index.blade.php)
- [x] 2. Revisar controlador de impresoras
- [x] 3. Actualizar controlador para agregar sort y direction
- [x] 4. Reemplazar CSS de filtros en impresoras/index.blade.php
- [x] 5. Reemplazar HTML del filtro en impresoras/index.blade.php
- [x] 6. Reemplazar JavaScript del filtro en impresoras/index.blade.php
- [x] 7. Probar funcionamiento

### Notas:
- El filtro de consumibles tiene: searchable-select para impresoras y gerencias, select para colores/tipos
- Para impresoras: searchable-select para nombre de impresora y gerencia, fecha
- El controlador ya tiene lógica de filtro para gerencia_id
- Se agregó soporte para filtros por fecha de creación

