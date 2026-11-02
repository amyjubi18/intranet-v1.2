# Plan de Implementación de Selectores de Ordenamiento

## Resumen de Controladores Actualizados
Los siguientes controladores ya tienen soporte para sort y direction:
- PermissionController ✓
- CategoriaController ✓
- UnidadAdministrativaController ✓
- ColorController ✓
- FormaAdquisicionController ✓
- EstadoUsoController ✓
- CondicionController ✓
- ProveedorController ✓
- ArticuloController ✓
- UnidadMedidaController ✓
- AreaController ✓
- GerenciasController ✓
- CartuchoController ✓
- ImpresoraController ✓
- DocsController ✓
- TipoDocumentoController ✓
- AreaAdscritaController ✓
- DigitalizacioneController ✓
- RoleController ✓
- PresentacionController ✓
- FabricanteController ✓
- UbicacionController ✓
- EstanteriaController ✓
- UsuarioAdministrativoController ✓
- UsuarioDeUsoController ✓
- ColoresController ✓

## Vistas que Necesitan Selectores de Ordenamiento

### Vistas de Administracion:
1. [ ] administracion/unidad_administrativa/index.blade.php
2. [ ] administracion/colors/index.blade.php
3. [ ] administracion/forma_adquisicion/index.blade.php
4. [ ] administracion/estado_del_uso/index.blade.php
5. [ ] administracion/condicion_fisica/index.blade.php
6. [ ] administracion/proveedores/index.blade.php
7. [ ] administracion/articulos/index.blade.php
8. [ ] administracion/unidad_medidas/index.blade.php
9. [ ] administracion/areas/index.blade.php
10. [ ] administracion/gerencias/index.blade.php
11. [ ] administracion/consumibles/index.blade.php
12. [ ] administracion/impresoras/index.blade.php
13. [ ] administracion/documentos/index.blade.php
14. [ ] administracion/tipo_consumibles/index.blade.php
15. [ ] administracion/tipo_impresoras/index.blade.php
16. [ ] administracion/ubicacion/index.blade.php
17. [ ] administracion/estanterias/index.blade.php
18. [ ] administracion/usuario_administrativo/index.blade.php
19. [ ] administracion/usuario_de_uso/index.blade.php

### Vistas de GTIC:
20. [ ] gtic/roles/index.blade.php

### Vistas de Gestion Humana:
21. [ ] gestion-humana/tipo_documentos/index.blade.php
22. [ ] gestion-humana/area_adscrita/index.blade.php
23. [ ] gestion-humana/digitalizacion_documentos/index.blade.php

### Vistas de Registros Insumos:
24. [ ] registros-insumos/medicamentos/presentacion/index.blade.php

## Patrón de Selector de Ordenamiento

```blade
<!-- Selector de ordenamiento -->
<div class="flex items-center gap-1 ml-2">
    <span class="text-xs text-gray-500">Ordenar por:</span>
    <a href="{{ request()->fullUrlWithQuery(['sort' => 'CAMPO', 'direction' => $sort === 'CAMPO' && $direction === 'asc' ? 'desc' : 'asc']) }}"
       class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded {{ $sort === 'CAMPO' ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
        ETIQUETA
        @if($sort === 'CAMPO')
            @if($direction === 'asc') ⬆️ @else ⬇️ @endif
        @else
            <span class="text-gray-400">⇅</span>
        @endif
    </a>
</div>
```

## Pasos para Implementar:

1. Para cada vista, agregar el selector de ordenamiento después del campo de búsqueda
2. Asegurarse de que el controlador pase las variables `sort` y `direction`
3. Probar que al hacer clic en el selector, la tabla se reordene correctamente
