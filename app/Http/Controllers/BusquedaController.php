<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusquedaController extends Controller
{
    /**
     * Handle search requests - Busca en todas las tablas y columnas
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $resultados = [];
        $queryLower = strtolower($query);

        // Tablas principales que definitely existen
        $tablasActivas = [
            [
                'tabla' => 'cartuchos',
                'columnas' => ['cartucho', 'modelo', 'descripcion'],
                'label' => 'Consumible',
                'icono' => '🖨️'
            ],
            [
                'tabla' => 'articulos',
                'columnas' => ['articulo', 'descripcion', 'codigo'],
                'label' => 'Artículo',
                'icono' => '📦'
            ],
            [
                'tabla' => 'impresoras',
                'columnas' => ['impresora', 'modelo', 'serial'],
                'label' => 'Impresora',
                'icono' => '🖨️'
            ],
            [
                'tabla' => 'users',
                'columnas' => ['name', 'username', 'email'],
                'label' => 'Usuario',
                'icono' => '👤',
                'campo_nombre' => 'name'
            ],
            [
                'tabla' => 'proveedores',
                'columnas' => ['proveedor', 'contacto', 'email'],
                'label' => 'Proveedor',
                'icono' => '🏢'
            ],
            [
                'tabla' => 'areas',
                'columnas' => ['area', 'codigo'],
                'label' => 'Área',
                'icono' => '🏢'
            ],
            [
                'tabla' => 'gerencias',
                'columnas' => ['gerencia', 'codigo'],
                'label' => 'Gerencia',
                'icono' => '🏛️'
            ],
            [
                'tabla' => 'ubicaciones',
                'columnas' => ['ubicacion', 'descripcion'],
                'label' => 'Ubicación',
                'icono' => '📍'
            ],
            [
                'tabla' => 'documentos',
                'columnas' => ['titulo', 'descripcion'],
                'label' => 'Documento',
                'icono' => '📄'
            ],
            [
                'tabla' => 'bienes',
                'columnas' => ['bien', 'codigo', 'serial'],
                'label' => 'Bien',
                'icono' => '📋'
            ],
        ];

        foreach ($tablasActivas as $config) {
            $tabla = $config['tabla'];
            $columnas = $config['columnas'];

            // Verificar si la tabla existe
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            // Construir condiciones de búsqueda
            $whereConditions = [];
            $bindings = [];

            foreach ($columnas as $columna) {
                if (Schema::hasColumn($tabla, $columna)) {
                    $whereConditions[] = "LOWER({$columna}) LIKE ?";
                    $bindings[] = "%{$queryLower}%";
                }
            }

            if (empty($whereConditions)) {
                continue;
            }

            // Determinar el campo para mostrar como nombre
            $campoNombre = $config['campo_nombre'] ?? ($columnas[0] ?? 'id');

            // Construir la consulta
            $sql = "SELECT id, {$campoNombre} as nombre, '{$config['label']}' as tipo,
                           '{$config['icono']}' as icono, '{$tabla}' as tabla
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
        $resultados = array_slice($resultados, 0, 20);

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
        ];

        return response()->json([
            'ruta' => $rutas[$tipo] ?? '#',
            'tipo' => $tipo
        ]);
    }
}

