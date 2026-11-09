<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BusquedaController extends Controller
{
    /**
     * Búsqueda global en tiempo real a través de los módulos y contenedores
     * filtrada estrictamente por los roles y permisos del usuario autenticado.
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        // Requisito: mínimo 3 caracteres para iniciar la búsqueda en tiempo real
        if (mb_strlen($query) < 3) {
            return response()->json([]);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $queryLower = mb_strtolower($query);

        // Identificar si es Administrador (acceso total a todas las entidades)
        $isAdmin = $user->id === 1 || $user->hasAnyRole(['Admin', 'admin', 'super-admin']);

        // Mapeo rápido de permisos y roles del usuario para evaluación eficiente
        $userPermissions = $user->getAllPermissions()->pluck('name')->flip()->toArray();
        $userRoles = $user->roles->pluck('name')->flip()->toArray();

        // Registro de entidades buscables del ecosistema (OGA, OGH, GTIC)
        $entidades = [
            // ==========================================
            // GTIC - Tecnología, Usuarios y Auditoría
            // ==========================================
            [
                'tabla' => 'users',
                'label' => 'Usuario',
                'icono' => '👤',
                'modulo' => 'gtic',
                'route_name' => 'users.index',
                'path' => '/users',
                'campo_nombre' => 'name',
                'columnas' => ['name', 'username', 'email', 'lastname', 'cedula', 'departamento'],
                'permisos' => ['user_index', 'gtic'],
                'roles' => ['GTIC'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->username)) $parts[] = "@{$row->username}";
                    if (!empty($row->cedula)) $parts[] = "CI: {$row->cedula}";
                    if (!empty($row->departamento)) $parts[] = $row->departamento;
                    return implode(' · ', $parts);
                }
            ],
            [
                'tabla' => 'roles',
                'label' => 'Rol',
                'icono' => '👥',
                'modulo' => 'gtic',
                'route_name' => 'gtic.roles.index',
                'path' => '/gtic/roles',
                'campo_nombre' => 'name',
                'columnas' => ['name'],
                'permisos' => ['role_index', 'gtic'],
                'roles' => ['GTIC'],
                'subtitle' => function ($row) {
                    return 'Rol de acceso al sistema';
                }
            ],
            [
                'tabla' => 'permissions',
                'label' => 'Permiso',
                'icono' => '🔑',
                'modulo' => 'gtic',
                'route_name' => 'gtic.permisos.index',
                'path' => '/gtic/permisos',
                'campo_nombre' => 'name',
                'columnas' => ['name'],
                'permisos' => ['permission_index', 'gtic'],
                'roles' => ['GTIC'],
                'subtitle' => function ($row) {
                    return 'Permiso granular del sistema';
                }
            ],
            [
                'tabla' => 'activity_log',
                'label' => 'Registro de Actividad',
                'icono' => '📜',
                'modulo' => 'gtic',
                'route_name' => 'gtic.activity-log.index',
                'path' => '/gtic/activity-log',
                'campo_nombre' => 'description',
                'columnas' => ['description', 'event', 'log_name'],
                'permisos' => ['activity_log_index', 'gtic'],
                'roles' => ['GTIC'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->event)) $parts[] = "Evento: {$row->event}";
                    if (!empty($row->log_name)) $parts[] = "Módulo: {$row->log_name}";
                    return implode(' · ', $parts);
                }
            ],

            // ==========================================
            // GESTIÓN HUMANA (OGH)
            // ==========================================
            [
                'tabla' => 'digitalizaciones',
                'label' => 'Expediente OGH',
                'icono' => '📑',
                'modulo' => 'gh',
                'route_name' => 'gestion-humana.digitalizacion_documentos.index',
                'path' => '/gestion-humana/digitalizacion_documentos',
                'campo_nombre' => 'nombre_apellido',
                'columnas' => ['nombre_apellido', 'expediente', 'cedula', 'salud'],
                'permisos' => ['digitalizaciones_index', 'ogh'],
                'roles' => ['analistas_ogh', 'coordinacion_ogh', 'gerente_ogh'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->expediente)) $parts[] = "Exp: {$row->expediente}";
                    if (!empty($row->cedula)) $parts[] = "CI: {$row->cedula}";
                    return implode(' · ', $parts);
                }
            ],
            [
                'tabla' => 'tipo_documentos',
                'label' => 'Tipo Documento OGH',
                'icono' => '📄',
                'modulo' => 'gh',
                'route_name' => 'gestion-humana.tipo_documentos.index',
                'path' => '/gestion-humana/tipo_documentos',
                'campo_nombre' => 'tipo_documento',
                'columnas' => ['tipo_documento'],
                'permisos' => ['tipo_documento_index', 'ogh'],
                'roles' => ['analistas_ogh', 'coordinacion_ogh', 'gerente_ogh'],
                'subtitle' => function ($row) {
                    return 'Clasificación de expediente OGH';
                }
            ],
            [
                'tabla' => 'area_adscritas',
                'label' => 'Área Adscrita OGH',
                'icono' => '🏢',
                'modulo' => 'gh',
                'route_name' => 'gestion-humana.area_adscrita.index',
                'path' => '/gestion-humana/area_adscrita',
                'campo_nombre' => 'area_adscrita',
                'columnas' => ['area_adscrita'],
                'permisos' => ['area_adscrita_index', 'ogh'],
                'roles' => ['analistas_ogh', 'coordinacion_ogh', 'gerente_ogh'],
                'subtitle' => function ($row) {
                    return 'Área de adscripción institucional';
                }
            ],

            // ==========================================
            // ADMINISTRACIÓN (OGA) - Bienes Públicos
            // ==========================================
            [
                'tabla' => 'bienes',
                'label' => 'Bien Público',
                'icono' => '📋',
                'modulo' => 'admin',
                'route_name' => 'administracion.bienes_publicos.index',
                'path' => '/administracion/bienes_publicos',
                'campo_nombre' => 'descripcion',
                'columnas' => ['descripcion', 'n_interno_bien', 'serial', 'marca', 'modelo', 'n_factura', 'comentario', 'sede'],
                'permisos' => ['bienes_index', 'bienes'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->n_interno_bien) && $row->n_interno_bien !== 'S/I') $parts[] = "Nº Bien: {$row->n_interno_bien}";
                    if (!empty($row->marca) && $row->marca !== 'S/I') $parts[] = $row->marca;
                    if (!empty($row->modelo) && $row->modelo !== 'S/I') $parts[] = $row->modelo;
                    if (!empty($row->serial) && $row->serial !== 'S/I') $parts[] = "Serial: {$row->serial}";
                    return implode(' · ', $parts);
                }
            ],

            // ==========================================
            // ADMINISTRACIÓN (OGA) - Consumibles & Artículos
            // ==========================================
            [
                'tabla' => 'cartuchos',
                'label' => 'Consumible / Tóner',
                'icono' => '🖨️',
                'modulo' => 'admin',
                'route_name' => 'administracion.consumibles.index',
                'path' => '/administracion/consumibles',
                'campo_nombre' => 'cartucho',
                'columnas' => ['cartucho'],
                'permisos' => ['cartucho_index', 'inv-consumibles'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return !empty($row->stock_min) ? "Stock mínimo: {$row->stock_min}" : 'Tóner / Tinta';
                }
            ],
            [
                'tabla' => 'articulos',
                'label' => 'Artículo de Oficina',
                'icono' => '📦',
                'modulo' => 'admin',
                'route_name' => 'administracion.articulos.index',
                'path' => '/administracion/articulos',
                'campo_nombre' => 'articulo',
                'columnas' => ['articulo'],
                'permisos' => ['articulo_index', 'inv-oficina'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return !empty($row->stock_min) ? "Stock mínimo: {$row->stock_min}" : 'Material de oficina';
                }
            ],
            [
                'tabla' => 'impresoras',
                'label' => 'Modelo de Impresora',
                'icono' => '🖨️',
                'modulo' => 'admin',
                'route_name' => 'administracion.impresoras.index',
                'path' => '/administracion/impresoras',
                'campo_nombre' => 'impresora',
                'columnas' => ['impresora'],
                'permisos' => ['impresora_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Catálogo de impresoras';
                }
            ],

            // ==========================================
            // ADMINISTRACIÓN (OGA) - Archivo y Documentos
            // ==========================================
            [
                'tabla' => 'documentos',
                'label' => 'Documento OGA',
                'icono' => '📄',
                'modulo' => 'admin',
                'route_name' => 'administracion.documentos.index',
                'path' => '/administracion/documentos',
                'campo_nombre' => 'name',
                'columnas' => ['name', 'description'],
                'permisos' => ['documentos_index', 'documentos'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return !empty($row->description) ? Str::limit($row->description, 60) : 'Archivo documental';
                }
            ],
            [
                'tabla' => 'estanterias',
                'label' => 'Estantería',
                'icono' => '🗄️',
                'modulo' => 'admin',
                'route_name' => 'administracion.estanterias.index',
                'path' => '/administracion/estanterias',
                'campo_nombre' => 'nombre',
                'columnas' => ['nombre'],
                'permisos' => ['estanterias_index', 'documentos_index', 'documentos'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Estante de archivo físico/digital';
                }
            ],
            [
                'tabla' => 'categorias',
                'label' => 'Categoría Documental',
                'icono' => '📁',
                'modulo' => 'admin',
                'route_name' => 'administracion.categorias.index',
                'path' => '/administracion/categorias',
                'campo_nombre' => 'name',
                'columnas' => ['name'],
                'permisos' => ['categorias_index', 'documentos_index', 'documentos'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Categoría de documentos OGA';
                }
            ],

            // ==========================================
            // ADMINISTRACIÓN (OGA) - Inventarios y Salidas
            // ==========================================
            [
                'tabla' => 'inventario_oficinas',
                'label' => 'Inventario Oficina',
                'icono' => '🏢',
                'modulo' => 'admin',
                'route_name' => 'administracion.inventario_oficinas.index',
                'path' => '/administracion/inventario_oficinas',
                'campo_nombre' => 'n_factura',
                'columnas' => ['n_factura'],
                'permisos' => ['inventarioofic_index', 'inv-oficina'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->n_factura)) $parts[] = "Factura: {$row->n_factura}";
                    if (isset($row->unidades_disponibles)) $parts[] = "Disponibles: {$row->unidades_disponibles}";
                    return implode(' · ', $parts);
                }
            ],
            [
                'tabla' => 'salida_oficinas',
                'label' => 'Salida de Oficina',
                'icono' => '📤',
                'modulo' => 'admin',
                'route_name' => 'administracion.salida_oficina.index',
                'path' => '/administracion/salida_oficina',
                'campo_nombre' => 'id',
                'columnas' => ['id'],
                'permisos' => ['salidaofic_index', 'inv-oficina'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return "Salida de material #{$row->id}" . (isset($row->cantidad) ? " · Cant: {$row->cantidad}" : '');
                }
            ],
            [
                'tabla' => 'inventario_impresoras',
                'label' => 'Inventario Consumible',
                'icono' => '🖨️',
                'modulo' => 'admin',
                'route_name' => 'administracion.inventario_impresoras.index',
                'path' => '/administracion/inventario_impresoras',
                'campo_nombre' => 'n_factura',
                'columnas' => ['n_factura'],
                'permisos' => ['inventarioimpre_index', 'inv-consumibles'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    $parts = [];
                    if (!empty($row->n_factura)) $parts[] = "Factura: {$row->n_factura}";
                    if (isset($row->unidades_disponibles)) $parts[] = "Disponibles: {$row->unidades_disponibles}";
                    return implode(' · ', $parts);
                }
            ],
            [
                'tabla' => 'salida_impresoras',
                'label' => 'Salida Consumible',
                'icono' => '📤',
                'modulo' => 'admin',
                'route_name' => 'administracion.salida_impresora.index',
                'path' => '/administracion/salida_impresora',
                'campo_nombre' => 'tipo_consumible',
                'columnas' => ['tipo_consumible'],
                'permisos' => ['salidaimpre_index', 'inv-consumibles'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return "Entrega de consumible" . (isset($row->cantidad) ? " · Cant: {$row->cantidad}" : '');
                }
            ],
            [
                'tabla' => 'almacenamiento_impresoras',
                'label' => 'Almacén Consumibles',
                'icono' => '💾',
                'modulo' => 'admin',
                'route_name' => 'administracion.almacenamiento_impresoras.index',
                'path' => '/administracion/almacenamiento_impresoras',
                'campo_nombre' => 'n_factura',
                'columnas' => ['n_factura'],
                'permisos' => ['almacen_inv_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return !empty($row->n_factura) ? "Factura: {$row->n_factura}" : 'Almacén de consumibles';
                }
            ],
            [
                'tabla' => 'distribucion_almacenamiento_impresoras',
                'label' => 'Distribución Almacén',
                'icono' => '📦',
                'modulo' => 'admin',
                'route_name' => 'administracion.distribucion_almacenamiento_impresoras.index',
                'path' => '/administracion/distribucion_almacenamiento_impresoras',
                'campo_nombre' => 'id',
                'columnas' => ['id'],
                'permisos' => ['dist_almac_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return "Distribución a gerencia #{$row->id}";
                }
            ],
            [
                'tabla' => 'salida_almacenamiento_impresoras',
                'label' => 'Salida de Almacén',
                'icono' => '📤',
                'modulo' => 'admin',
                'route_name' => 'administracion.salida_almacen.index',
                'path' => '/administracion/salida_almacen',
                'campo_nombre' => 'id',
                'columnas' => ['id'],
                'permisos' => ['salidaimpre_index', 'almacen_inv_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return "Salida de almacén #{$row->id}";
                }
            ],

            // ==========================================
            // ADMINISTRACIÓN (OGA) - Parámetros y Maestros
            // ==========================================
            [
                'tabla' => 'proveedores',
                'label' => 'Proveedor',
                'icono' => '🏢',
                'modulo' => 'admin',
                'route_name' => 'administracion.proveedores.index',
                'path' => '/administracion/proveedores',
                'campo_nombre' => 'proveedor',
                'columnas' => ['proveedor'],
                'permisos' => ['proveedor_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return 'Proveedor registrado';
                }
            ],
            [
                'tabla' => 'areas',
                'label' => 'Área',
                'icono' => '🏢',
                'modulo' => 'admin',
                'route_name' => 'administracion.areas.index',
                'path' => '/administracion/areas',
                'campo_nombre' => 'area',
                'columnas' => ['area'],
                'permisos' => ['area_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return 'Área administrativa';
                }
            ],
            [
                'tabla' => 'gerencias',
                'label' => 'Gerencia',
                'icono' => '🏛️',
                'modulo' => 'admin',
                'route_name' => 'administracion.gerencias.index',
                'path' => '/administracion/gerencias',
                'campo_nombre' => 'gerencia',
                'columnas' => ['gerencia'],
                'permisos' => ['gerencia_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return 'Gerencia institucional';
                }
            ],
            [
                'tabla' => 'ubicacions',
                'label' => 'Ubicación',
                'icono' => '📍',
                'modulo' => 'admin',
                'route_name' => 'administracion.ubicacion.index',
                'path' => '/administracion/ubicacion',
                'campo_nombre' => 'ubicacion',
                'columnas' => ['ubicacion'],
                'permisos' => ['ubicacion_index', 'area_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return 'Ubicación física';
                }
            ],
            [
                'tabla' => 'unidad_administrativas',
                'label' => 'Unidad Administrativa',
                'icono' => '🏛️',
                'modulo' => 'admin',
                'route_name' => 'administracion.unidad_administrativa.index',
                'path' => '/administracion/unidad_administrativa',
                'campo_nombre' => 'name',
                'columnas' => ['name'],
                'permisos' => ['unidad_adminis_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Unidad administrativa';
                }
            ],
            [
                'tabla' => 'usuario_administrativos',
                'label' => 'Usuario Administrativo',
                'icono' => '👤',
                'modulo' => 'admin',
                'route_name' => 'administracion.usuario_administrativo.index',
                'path' => '/administracion/usuario_administrativo',
                'campo_nombre' => 'usuario_administrativo',
                'columnas' => ['usuario_administrativo'],
                'permisos' => ['usuario_administrativo_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Responsable administrativo';
                }
            ],
            [
                'tabla' => 'usuario_de_usos',
                'label' => 'Usuario de Uso',
                'icono' => '👤',
                'modulo' => 'admin',
                'route_name' => 'administracion.usuario_de_uso.index',
                'path' => '/administracion/usuario_de_uso',
                'campo_nombre' => 'usuario_de_uso',
                'columnas' => ['usuario_de_uso'],
                'permisos' => ['usuario_de_uso_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Usuario de asignación directa';
                }
            ],
            [
                'tabla' => 'unidad_medidas',
                'label' => 'Unidad de Medida',
                'icono' => '📏',
                'modulo' => 'admin',
                'route_name' => 'administracion.unidad_medidas.index',
                'path' => '/administracion/unidad_medidas',
                'campo_nombre' => 'unidad_medida',
                'columnas' => ['unidad_medida'],
                'permisos' => ['unidad_medida_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración', 'Permisos_hilda'],
                'subtitle' => function ($row) {
                    return 'Unidad de medida';
                }
            ],
            [
                'tabla' => 'condicions',
                'label' => 'Condición Física',
                'icono' => '✅',
                'modulo' => 'admin',
                'route_name' => 'administracion.condicion_fisica.index',
                'path' => '/administracion/condicion_fisica',
                'campo_nombre' => 'condicion',
                'columnas' => ['condicion'],
                'permisos' => ['condicion_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Condición física de bienes';
                }
            ],
            [
                'tabla' => 'forma_adquisicions',
                'label' => 'Forma de Adquisición',
                'icono' => '🛒',
                'modulo' => 'admin',
                'route_name' => 'administracion.forma_adquisicion.index',
                'path' => '/administracion/forma_adquisicion',
                'campo_nombre' => 'forma_adquisicion',
                'columnas' => ['forma_adquisicion'],
                'permisos' => ['forma_adquisicion_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Modalidad de adquisición';
                }
            ],
            [
                'tabla' => 'colores',
                'label' => 'Color',
                'icono' => '🎨',
                'modulo' => 'admin',
                'route_name' => 'administracion.colors.index',
                'path' => '/administracion/colors',
                'campo_nombre' => 'colore',
                'columnas' => ['colore'],
                'permisos' => ['color_index', 'colores_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Color registrado';
                }
            ],
            [
                'tabla' => 'fabricantes',
                'label' => 'Fabricante / Marca',
                'icono' => '🏭',
                'modulo' => 'admin',
                'route_name' => 'administracion.tipo_impresoras.index',
                'path' => '/administracion/tipo_impresoras',
                'campo_nombre' => 'fabricante',
                'columnas' => ['fabricante'],
                'permisos' => ['fabricante_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Fabricante de equipos';
                }
            ],
            [
                'tabla' => 'presentacions',
                'label' => 'Presentación Consumible',
                'icono' => '📦',
                'modulo' => 'admin',
                'route_name' => 'administracion.tipo_consumibles.index',
                'path' => '/administracion/tipo_consumibles',
                'campo_nombre' => 'presentacion',
                'columnas' => ['presentacion'],
                'permisos' => ['presentacion_index', 'color_index', 'cartucho_index'],
                'roles' => ['Bienes-Publicos', 'Gerente-Admin', 'Administración'],
                'subtitle' => function ($row) {
                    return 'Tipo de presentación';
                }
            ],
        ];

        $resultados = [];

        foreach ($entidades as $config) {
            $tabla = $config['tabla'];

            // 1. Filtrado de seguridad estricto por permisos y roles
            if (!$isAdmin) {
                $hasAccess = false;

                // Verificar coincidencia en permisos del usuario
                if (!empty($config['permisos'])) {
                    foreach ($config['permisos'] as $perm) {
                        if (isset($userPermissions[$perm])) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                // Verificar coincidencia por roles
                if (!$hasAccess && !empty($config['roles'])) {
                    foreach ($config['roles'] as $roleName) {
                        if (isset($userRoles[$roleName])) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                if (!$hasAccess) {
                    continue; // El usuario NO tiene permiso para ver este recurso
                }
            }

            // 2. Comprobar existencia real de la tabla en base de datos
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            // 3. Validar columnas existentes
            $campoNombre = $config['campo_nombre'];
            if (!Schema::hasColumn($tabla, $campoNombre)) {
                $campoNombre = 'id';
            }

            $validCols = array_values(array_filter($config['columnas'], function ($c) use ($tabla) {
                return Schema::hasColumn($tabla, $c);
            }));

            if (empty($validCols)) {
                continue;
            }

            // 4. Ejecutar consulta optimizada
            try {
                $dbQuery = DB::table($tabla);
                $dbQuery->where(function ($q) use ($validCols, $queryLower) {
                    foreach ($validCols as $col) {
                        $q->orWhereRaw("LOWER({$col}) LIKE ?", ["%{$queryLower}%"]);
                    }
                });

                // Límite por tabla para agilidad
                $rows = $dbQuery->limit(5)->get();

                foreach ($rows as $row) {
                    // Identificar qué campo y valor coincidió
                    $campoEncontrado = null;
                    $coincidencia = null;

                    foreach ($validCols as $col) {
                        $val = (string)($row->$col ?? '');
                        if ($val !== '' && mb_stripos($val, $query) !== false) {
                            $campoEncontrado = ucfirst(str_replace('_', ' ', $col));
                            $coincidencia = $val;
                            break;
                        }
                    }

                    // Generar subtítulo informativo
                    $subtitulo = '';
                    if (isset($config['subtitle']) && is_callable($config['subtitle'])) {
                        $subtitulo = $config['subtitle']($row);
                    }

                    // Determinar valor de búsqueda para el enlace de destino
                    $displayNombre = (string)($row->$campoNombre ?? 'Sin título');
                    $searchParam = $coincidencia ?: $displayNombre;

                    // Generar URL hacia el recurso (directa local o salto SSO inter-contenedor)
                    $url = null;
                    if (Route::has($config['route_name'])) {
                        $url = route($config['route_name'], ['search' => $searchParam]);
                    } elseif (Route::has('sso.go')) {
                        $url = route('sso.go', ['module' => $config['modulo']]) . '?redirect=' . urlencode($config['path'] . '?search=' . urlencode($searchParam));
                    } else {
                        $url = $config['path'] . '?search=' . urlencode($searchParam);
                    }

                    $resultados[] = [
                        'id' => $row->id ?? null,
                        'nombre' => $displayNombre,
                        'tipo' => $config['label'],
                        'icono' => $config['icono'],
                        'tabla' => $tabla,
                        'modulo' => $config['modulo'],
                        'campo_encontrado' => $campoEncontrado,
                        'coincidencia' => $coincidencia,
                        'subtitulo' => $subtitulo,
                        'url' => $url,
                    ];
                }
            } catch (\Throwable $e) {
                // Silenciosamente continuar ante cualquier inconsistencia en tabla individual
                continue;
            }
        }

        // Limitar resultados totales devueltos
        $resultados = array_slice($resultados, 0, 30);

        return response()->json($resultados);
    }

    /**
     * Mapeo de rutas legacy para compatibilidad
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
            'estanterias' => route('administracion.estanterias.index'),
            'categorias' => route('administracion.categorias.index'),
            'colores' => route('administracion.colors.index'),
            'unidades_medidas' => route('administracion.unidad_medidas.index'),
            'condiciones' => route('administracion.condicion_fisica.index'),
            'forma_adquisiciones' => route('administracion.forma_adquisicion.index'),
            'fabricantes' => route('administracion.tipo_impresoras.index'),
            'digitalizaciones' => route('gestion-humana.digitalizacion_documentos.index'),
            'presentaciones' => route('administracion.tipo_consumibles.index'),
            'inventario_impresoras' => route('administracion.inventario_impresoras.index'),
            'inventario_oficinas' => route('administracion.inventario_oficinas.index'),
            'salida_impresoras' => route('administracion.salida_impresora.index'),
            'salida_oficinas' => route('administracion.salida_oficina.index'),
            'almacenamiento_impresoras' => route('administracion.almacenamiento_impresoras.index'),
            'salida_almacenamiento_impresoras' => route('administracion.salida_almacen.index'),
            'distribucion_almacenamiento_impresoras' => route('administracion.distribucion_almacenamiento_impresoras.index'),
            'roles' => route('gtic.roles.index'),
            'permissions' => route('gtic.permisos.index'),
            'unidades_administrativas' => route('administracion.unidad_administrativa.index'),
            'tipo_documentos' => route('gestion-humana.tipo_documentos.index'),
            'areas_adscritas' => route('gestion-humana.area_adscrita.index'),
        ];

        return response()->json([
            'ruta' => $rutas[$tipo] ?? '#',
            'tipo' => $tipo
        ]);
    }
}
