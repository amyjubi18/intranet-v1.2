# TODO - Empleados Activos Card

## Plan de implementación

### 1. Agregar API endpoint en DashboardController
- [x] Agregar método `userStats()` para obtener estadísticas de usuarios
- [x] Retornar: total de usuarios registrados, activos e inactivos

### 2. Agregar ruta API
- [x] Agregar ruta `GET /api/dashboard/user-stats` en `routes/api.php`

### 3. Actualizar vista inicio.blade.php
- [x] Agregar JavaScript para obtener estadísticas de usuarios
- [x] Actualizar la tarjeta "Empleados Activos" con datos reales
- [x] Mostrar contadores: Total, Activos, Inactivos

### 4. Probar la funcionalidad
- [ ] Verificar que los datos se cargan correctamente

