<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class BusquedaController extends Controller
{
    /**
     * Handle search requests - Busca en todas las tablas y columnas según los permisos del usuario
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $resultados = [];
        $queryLower = strtolower($query);

        // Obtener usuario autenticado y sus permisos
        $user = Auth::user();
        $userPermissions = [];

        if ($user) {
            // Obtener todos los permisos del usuario (directos y a través de roles)
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

            // También verificar si es superadmin o tiene permisos específicos
            $hasGtic = in_array('gtic', $userPermissions) || $user->hasRole('super-admin') || $user->hasRole('gtic');
            $hasOga = in_array('oga', $userPermissions) || $user->hasRole('super-admin') || $user->hasRole('oga');
            $hasOgh = in_array('ogh', $userPermissions) || $user->hasRole('super-admin') || $user->hasRole('ogh');
        }

        // Definir qué tablas puede buscar según los permisos
        $permisoTablas = [
            // GTIC - Usuarios, Roles, Permisos
            'users' => ['gtic', 'user_index'],
            'roles' => ['gtic', 'role_index'],
            'permissions' => ['gtic', 'permission_index'],

            // OGA - Bienes, Inventarios, Consumibles
            'documentos' => ['oga', 'documentos_index'],
            'categorias' => ['oga', 'categorias_index'],
            'estanterias' => ['oga', 'estanterias_index'],
            'bienes' => ['oga', 'bienes_index'],
            'unidades_administrativas' => ['oga', 'unidad_adminis_index'],
            'forma_adquisiciones' => ['oga', 'forma_adquisicion_index'],
            'colores' => ['oga', 'color_index'],
            'condiciones' => ['oga', 'condicion_index'],
            'inventario_oficinas' => ['oga', 'inventarioofic_index'],
            'salida_oficinas' => ['oga', 'salidaofic_index'],
            'almacenamiento_impresoras' => ['oga', 'almacen_inv_index'],
            'distribucion_almacenamiento_impresoras' => ['oga', 'dist_almac_index'],
            'salida_almacenamiento_impresoras' => ['oga', 'salidaimpre_index'],
            'cartuchos' => ['oga', 'cartucho_index'],
            'articulos' => ['oga', 'articulo_index'],
            'proveedores' => ['oga', 'proveedor_index'],
            'unidades_medidas' => ['oga', 'unidad_medida_index'],
            'impresoras' => ['oga', 'impresora_index'],
            'areas' => ['oga', 'area_index'],
            'gerencias' => ['oga', 'gerencia_index'],
            'ubicaciones' => ['oga', 'area_index'],
            'inventario_impresoras' => ['oga', 'inventarioimpre_index'],
            'salida_impresoras' => ['oga', 'salidaimpre_index'],
            'fabricantes' => ['oga', 'fabricante_index'],
            'presentaciones' => ['oga', 'presentacion_index'],

            // OGH - Gestión Humana
            'digitalizaciones' => ['ogh', 'documentos_index'],
            'tipo_documentos' => ['ogh', 'tipo_documento_index'],
            'areas_adscritas' => ['ogh', 'area_adscrita_index'],
        ];

        // Configuración de todas las tablas del sistema
        $tablasActivas = [
            // Tablas GTIC
            [
                'tabla' => 'users',
                'columnas' => ['name', 'username', 'email', 'lastname', 'cedula', 'departamento'],
                'label' => 'Usuario',
                'icono' => '👤',
                'campo_nombre' => 'name',
                'permiso_requerido' => 'gtic'
            ],
            [
                'tabla' => 'roles',
                'columnas' => ['name'],
                'label' => 'Rol',
                'icono' => '👥',
                'permiso_requerido' => 'gtic'
            ],
            [
                'tabla' => 'permissions',
                'columnas' => ['name'],
                'label' => 'Permiso',
                'icono' => '🔑',
                'permiso_requerido' => 'gtic'
            ],

            // Tablas OGA
            [
                'tabla' => 'cartuchos',
                'columnas' => ['cartucho'],
                'label' => 'Consumible',
                'icono' => '🖨️',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'articulos',
                'columnas' => ['articulo'],
                'label' => 'Artículo',
                'icono' => '📦',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'impresoras',
                'columnas' => ['impresora'],
                'label' => 'Impresora',
                'icono' => '🖨️',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'proveedores',
                'columnas' => ['proveedor'],
                'label' => 'Proveedor',
                'icono' => '🏢',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'areas',
                'columnas' => ['area'],
                'label' => 'Área',
                'icono' => '🏢',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'gerencias',
                'columnas' => ['gerencia'],
                'label' => 'Gerencia',
                'icono' => '🏛️',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'ubicaciones',
                'columnas' => ['ubicacion'],
                'label' => 'Ubicación',
                'icono' => '📍',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'documentos',
                'columnas' => ['name', 'description', 'documento'],
                'label' => 'Documento',
                'icono' => '📄',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'bienes',
                'columnas' => ['bien', 'n_interno_bien', 'n_factura', 'descripcion', 'moneda', 'marca', 'modelo', 'serial', 'comentario'],
                'label' => 'Bien Público',
                'icono' => '📋',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'estanterias',
                'columnas' => ['nombre'],
                'label' => 'Estantería',
                'icono' => '🗄️',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'categorias',
                'columnas' => ['name'],
                'label' => 'Categoría',
                'icono' => '📁',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'colores',
                'columnas' => ['color'],
                'label' => 'Color',
                'icono' => '🎨',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'colores',
                'columnas' => ['colore'],
                'label' => 'Color',
                'icono' => '🎨',
                'campo_nombre' => 'colore',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'unidades_medidas',
                'columnas' => ['unidad_medida'],
                'label' => 'Unidad de Medida',
                'icono' => '📏',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'condiciones',
                'columnas' => ['condicion'],
                'label' => 'Condición',
                'icono' => '✅',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'forma_adquisiciones',
                'columnas' => ['forma_adquisicion'],
                'label' => 'Forma de Adquisición',
                'icono' => '🛒',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'fabricantes',
                'columnas' => ['fabricante'],
                'label' => 'Fabricante',
                'icono' => '🏭',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'presentaciones',
                'columnas' => ['presentacion'],
                'label' => 'Presentación',
                'icono' => '📦',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'inventario_impresoras',
                'columnas' => ['descripcion', 'serial'],
                'label' => 'Inv. Impresora',
                'icono' => '🖨️',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'inventario_oficinas',
                'columnas' => ['descripcion', 'serial'],
                'label' => 'Inv. Oficina',
                'icono' => '🏢',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'salida_impresoras',
                'columnas' => ['descripcion', 'motivo'],
                'label' => 'Salida Impresora',
                'icono' => '📤',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'salida_oficinas',
                'columnas' => ['descripcion', 'motivo'],
                'label' => 'Salida Oficina',
                'icono' => '📤',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'almacenamiento_impresoras',
                'columnas' => ['descripcion', 'serial'],
                'label' => 'Almacenamiento',
                'icono' => '💾',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'salida_almacenamiento_impresoras',
                'columnas' => ['descripcion', 'motivo'],
                'label' => 'Salida Alm.',
                'icono' => '📤',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'distribucion_almacenamiento_impresoras',
                'columnas' => ['descripcion'],
                'label' => 'Distribución',
                'icono' => '📦',
                'permiso_requerido' => 'oga'
            ],
            [
                'tabla' => 'unidades_administrativas',
                'columnas' => ['name'],
                'label' => 'Unidad Admin.',
                'icono' => '🏛️',
                'permiso_requerido' => 'oga'
            ],

            // Tablas de Gestión Humana (OGH)
            [
                'tabla' => 'digitalizaciones',
                'columnas' => ['nombre_apellido', 'expediente', 'salud', 'cedula'],
                'label' => 'Digitalización',
                'icono' => '📑',
                'permiso_requerido' => 'ogh'
            ],
            [
                'tabla' => 'tipo_documentos',
                'columnas' => ['tipo_documento'],
                'label' => 'Tipo de Documento',
                'icono' => '📄',
                'permiso_requerido' => 'ogh'
            ],
            [
                'tabla' => 'areas_adscritas',
                'columnas' => ['area_adscrita'],
                'label' => 'Área Adscrita',
                'icono' => '🏢',
                'permiso_requerido' => 'ogh'
            ],
        ];

        foreach ($tablasActivas as $config) {
            $tabla = $config['tabla'];
            $permisoRequerido = $config['permiso_requerido'] ?? null;

            // Verificar si el usuario tiene permiso para buscar en esta tabla
            if ($permisoRequerido) {
                // Si no está autenticado o no tiene el permiso, saltar esta tabla
                if (!$user || !$user->hasPermissionTo($permisoRequerido)) {
                    continue;
                }
            }

            $columnas = $config['columnas'];

            // Verificar si la tabla existe
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            // Determinar el campo para mostrar como nombre
            $campoNombre = $config['campo_nombre'] ?? ($columnas[0] ?? 'id');

            // Verificar si el campo nombre existe
            if (!Schema::hasColumn($tabla, $campoNombre)) {
                $campoNombre = 'id';
            }

            // Construir condiciones de búsqueda
            $whereConditions = [];
            $bindings = [];
            $selectColumns = ['id', $campoNombre . ' as nombre', "'{$config['label']}' as tipo", "'{$config['icono']}' as icono", "'{$tabla}' as tabla"];

            foreach ($columnas as $columna) {
                if (Schema::hasColumn($tabla, $columna)) {
                    $whereConditions[] = "LOWER({$columna}) LIKE ?";
                    $bindings[] = "%{$queryLower}%";
                }
            }

            if (empty($whereConditions)) {
                continue;
            }

            // Construir la consulta
            $sql = "SELECT " . implode(', ', $selectColumns) . "
                    FROM {$tabla}
                    WHERE " . implode(' OR ', $whereConditions) . "
                    LIMIT 3";

            try {
                $resultadosTabla = DB::select($sql, $bindings);

                foreach ($resultadosTabla as $resultado) {
                    // Determinar qué campo coincidió
                    $campoEncontrado = null;
                    foreach ($columnas as $columna) {
                        if (Schema::hasColumn($tabla, $columna)) {
                            $valorCampo = $resultado->$columna ?? '';
                            if (is_string($valorCampo) && stripos($valorCampo, $query) !== false) {
                                $campoEncontrado = $columna;
                                break;
                            }
                        }
                    }
                    $resultado->campo_encontrado = $campoEncontrado;
                    $resultados[] = $resultado;
                }
            } catch (\Exception $e) {
                // Silenciosamente continuar si hay error
                continue;
            }
        }

        // Limitar resultados totales
        $resultados = array_slice($resultados, 0, 30);

        return response()->json($resultados);
    }

    /**
     * Get search routes for navigation
     */
    public function getRoute($tipo, $id)
    {
        $rutas = [
            'cartuchos' => route('administracion.consumibles.index'),
            'articulos' => route('administracion.articulos.index'),
            'impresoras' => route('administracion.impresoras.index'),
            'users' => route('users.index'),
            'proveedores' => route('administracion.proveedores.index'),
            'areas' => route('administracion.areas.index'),
            'gerencias' => route('administracion.gerencias.index'),
            'ubicaciones' => route('administracion.ubicacion.index'),
            'documentos' => route('administracion.documentos.index'),
            'bienes' => route('administracion.bienes_publicos.index'),
            'eventos' => route('inicio'),
            'estanterias' => route('administracion.estanterias.index'),
            'categorias' => route('administracion.categorias.index'),
            'colores' => route('administracion.colores.index'),
            'unidades_medidas' => route('administracion.unidades.index'),
            'condiciones' => route('administracion.condiciones.index'),
            'forma_adquisiciones' => route('administracion.forma_adquisiciones.index'),
            'fabricantes' => route('administracion.fabricantes.index'),
            'digitalizaciones' => route('administracion.digitalizaciones.index'),
            'presentaciones' => route('administracion.presentaciones.index'),
            'inventario_impresoras' => route('administracion.inv-impresoras.index'),
            'inventario_oficinas' => route('administracion.inv-oficinas.index'),
            'salida_impresoras' => route('administracion.salida-impresoras.index'),
            'salida_oficinas' => route('administracion.salida-oficinas.index'),
            'almacenamiento_impresoras' => route('administracion.almacenamiento-impresoras.index'),
            'salida_almacenamiento_impresoras' => route('administracion.salida-almacenamiento.index'),
            'distribucion_almacenamiento_impresoras' => route('administracion.distribucion.index'),
            'roles' => route('roles.index'),
            'permissions' => route('permissions.index'),
            'unidades_administrativas' => route('administracion.unidades-administrativas.index'),
            // Rutas de Gestión Humana
            'tipo_documentos' => route('gestion-humana.tipo_documentos.index'),
            'areas_adscritas' => route('gestion-humana.area_adscrita.index'),
        ];

        return response()->json([
            'ruta' => $rutas[$tipo] ?? '#',
            'tipo' => $tipo
        ]);
    }
}

