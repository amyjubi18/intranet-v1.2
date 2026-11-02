# Plan: Directorio editable con permisos Admin

## Objetivo
Implementar un directorio editable en la página de inicio donde:
- Solo usuarios con rol Admin pueden editar/eliminar
- Los datos se guardan en archivo JSON (no en BD)
- "Ver todas" abre un modal con todos los registros

---

## Pasos a implementar

### 1. Crear archivo JSON con datos del directorio
- Ubicación: `storage/app/directory.json`
- Estructura: Array de objetos {id, nombre, extension, icono}

### 2. Crear API Controller
- Archivo: `app/Http/Controllers/Api/DirectoryController.php`
- Métodos:
  - `index()` - Obtener todos los directorios
  - `store()` - Crear nuevo registro
  - `update()` - Actualizar registro
  - `destroy()` - Eliminar registro

### 3. Agregar rutas API
- En: `routes/api.php`
- Endpoints:
  - GET `/api/directory`
  - POST `/api/directory`
  - PUT `/api/directory/{id}`
  - DELETE `/api/directory/{id}`

### 4. Modificar inicio.blade.php
- Cargar datos del JSON via API
- Mostrar "Ver todas" como modal con todos los registros
- Para Admin:
  - Campos editables (input fields)
  - Botones de editar y eliminar
- Para usuarios normales:
  - Solo mostrar lectura (no editar)

---

## Archivos a crear/modificar

| Archivo | Acción |
|---------|--------|
| `storage/app/directory.json` | Crear |
| `app/Http/Controllers/Api/DirectoryController.php` | Crear |
| `routes/api.php` | Modificar |
| `resources/views/inicio.blade.php` | Modificar |

---

## Notas
- No se requiere migración de BD
- Los datos persisten en archivo JSON
- Verificación de Admin: `@if(Auth::user()->hasRole('Admin'))`

