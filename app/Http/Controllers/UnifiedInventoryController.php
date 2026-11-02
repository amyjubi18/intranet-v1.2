<?php

namespace App\Http\Controllers;

use App\Models\InventarioImpresora;
use App\Models\InventarioOficina;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\Articulo;
use App\Models\UnidadMedida;
use App\Models\Proveedore;
use App\Models\Gerencia;
use App\Models\Area;
use App\Models\AlmacenamientoImpresora;
use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\SalidaImpresora;
use App\Models\SalidaOficina;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class UnifiedInventoryController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        $lowStockItems = [];

        // Query para inventario de impresoras
        $queryImpresora = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores')
            ->select('*', DB::raw("'impresora' as tipo"));

        // Query para inventario de oficina
        $queryOficina = InventarioOficina::with('articulos', 'unidad_medidas', 'proveedores')
            ->select('*', DB::raw("'oficina' as tipo"));

        // Aplicar filtros a ambos queries
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $queryImpresora->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
            $queryOficina->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        }

        if ($request->filled('filterType') && $request->filled('secondFilter')) {
            $filterType = $request->input('filterType');
            $secondFilter = $request->input('secondFilter');
            switch ($filterType) {
                case 'proveedore_id':
                    $queryImpresora->where('proveedore_id', $secondFilter);
                    $queryOficina->where('proveedore_id', $secondFilter);
                    break;
                case 'gerencia_id':
                    $queryImpresora->where('gerencia_id', $secondFilter);
                    break;
                case 'area_id':
                    $queryImpresora->where('area_id', $secondFilter);
                    break;

                case 'cartucho_id':
                    $queryImpresora->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $queryImpresora->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $queryImpresora->where('impresora_id', $secondFilter);
                    break;
                case 'fabricante_id':
                    $queryImpresora->where('fabricante_id', $secondFilter);
                    break;
                case 'articulo_id':
                    $queryOficina->where('articulo_id', $secondFilter);
                    break;
                case 'unidad_medida_id':
                    $queryOficina->where('unidad_medida_id', $secondFilter);
                    break;
            }
        }

        if ($request->filled('n_factura')) {
            $queryImpresora->where('n_factura', 'like', '%' . $request->input('n_factura') . '%');
            $queryOficina->where('n_factura', 'like', '%' . $request->input('n_factura') . '%');
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $queryImpresora->where(function ($q) use ($busqueda) {
                $q->where('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  })

                  ->orWhereHas('fabricantes', function ($q7) use ($busqueda) {
                      $q7->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
            $queryOficina->where(function ($q) use ($busqueda) {
                $q->where('descripcion', 'like', "%{$busqueda}%")
                  ->orWhere('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('articulos', function ($q2) use ($busqueda) {
                      $q2->where('articulo', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Unir los resultados
        $inventarioImpresora = $queryImpresora->get();
        $inventarioOficina = $queryOficina->get();
        $inventarioUnificado = $inventarioImpresora->concat($inventarioOficina)->sortByDesc('fecha_ingreso');

        // Paginación manual
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $inventarioUnificado->forPage($currentPage, $perPage),
            $inventarioUnificado->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        );
        $paginatedItems->appends($request->all());

        // Estadísticas combinadas
        $totalItems = InventarioImpresora::sum('unidades') + InventarioOficina::sum('unidades');
        $totalItemsDisponibles = InventarioImpresora::sum('unidades_disponibles') + InventarioOficina::sum('unidades_disponibles');
        $porcentajeDisponible = $totalItems > 0 ? round(($totalItemsDisponibles / $totalItems) * 100, 1) : 0;

        $itemsEsteMes = InventarioImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        InventarioOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        AlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaAlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        DistribucionAlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Cartucho::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Colore::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Impresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Fabricante::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Articulo::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        UnidadMedida::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Area::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Gerencia::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Proveedore::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Calcular stock bajo correctamente considerando salidas y stock_min dinámicos
        $almacenamientoBajo = AlmacenamientoImpresora::whereRaw('(unidades - COALESCE((SELECT SUM(cantidad) FROM salida_almacenamiento_impresoras WHERE almacenamiento_impresora_id = almacenamiento_impresoras.id), 0)) < (SELECT stock_min FROM cartuchos WHERE cartuchos.id = almacenamiento_impresoras.cartucho_id)')->count();
        $oficinaBajo = InventarioOficina::whereRaw('(unidades - COALESCE((SELECT SUM(cantidad) FROM salida_oficinas WHERE inventario_oficina_id = inventario_oficinas.id), 0)) < (SELECT stock_min FROM articulos WHERE articulos.id = inventario_oficinas.articulo_id)')->count();
        $stockBajo = $almacenamientoBajo  + $oficinaBajo;

        // Poblar lowStockItems
        $lowStockItems = collect();

        // Items de almacenamiento con stock bajo (usando GREATEST para evitar negativos)
        $almacenamientoItems = AlmacenamientoImpresora::with('cartuchos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_almacenamiento_impresoras WHERE almacenamiento_impresora_id = almacenamiento_impresoras.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($almacenamientoItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->cartuchos->cartucho ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'almacenamiento',
                    'color' => $stock < 3 ? 'red' : 'yellow'
                ]);
            }
        }

        // Items de inventario impresora con stock bajo
        $impresoraItems = InventarioImpresora::with('cartuchos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_impresoras WHERE inventario_impresora_id = inventario_impresoras.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($impresoraItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->cartuchos->cartucho ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'artículo',
                    'color' => $stock < 3 ? 'red' : 'yellow'
                ]);
            }
        }

        // Items de inventario oficina con stock bajo
        $oficinaItems = InventarioOficina::with('articulos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_oficinas WHERE inventario_oficina_id = inventario_oficinas.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($oficinaItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->articulos->articulo ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'artículo',
                    'color' => $stock < 3 ? 'red' : 'yellow'
                ]);
            }
        }

        $totalTipos = Cartucho::distinct('id')->count() + Articulo::distinct('id')->count();

        // Última fecha de ingreso
        $ultimaFechaImpresora = InventarioImpresora::orderBy('fecha_ingreso', 'desc')->first();
        $ultimaFechaOficina = InventarioOficina::orderBy('fecha_ingreso', 'desc')->first();
        $ultimaFecha = $ultimaFechaImpresora && $ultimaFechaOficina ?
            ($ultimaFechaImpresora->fecha_ingreso > $ultimaFechaOficina->fecha_ingreso ? $ultimaFechaImpresora : $ultimaFechaOficina) :
            ($ultimaFechaImpresora ?: $ultimaFechaOficina);
        $ultimasFechas = $ultimaFecha ? \Carbon\Carbon::parse($ultimaFecha->fecha_ingreso)->format('d/m/Y') : 'N/A';

        // Datos para filtros
        $cartuchos = Cartucho::all();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $articulos = Articulo::all();
        $unidadMedidas = UnidadMedida::all();
        $areas = Area::all();
        $proveedores = Proveedore::all();
        $gerencias = Gerencia::all();
        $users = \App\Models\User::all();

        // Obtener actividades recientes del registro de actividades con filtros
        $queryActivities = Activity::with('causer');

        // Filtros para actividades

        if ($request->filled('activity_user')) {
            $queryActivities->where('causer_id', $request->input('activity_user'));
        }

        if ($request->filled('activity_action')) {
            $queryActivities->where('event', $request->input('activity_action'));
        }

        if ($request->filled('activity_model')) {
            $queryActivities->where('subject_type', 'like', '%' . $request->input('activity_model') . '%');
        }

        $recentActivities = $queryActivities->latest()->paginate(5);

        \Log::info('Recent activities count after filter: ' . $recentActivities->count());

        // Contar actividades de este mes
        $activitiesThisMonth = Activity::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Todos los consumibles utilizados basados en salidas del almacén
        $topConsumibles = SalidaAlmacenamientoImpresora::join('almacenamiento_impresoras', 'salida_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->selectRaw('cartuchos.cartucho as name, SUM(salida_almacenamiento_impresoras.cantidad) as total_salidas')
            ->groupBy('cartuchos.id', 'cartuchos.cartucho')
            ->orderBy('total_salidas', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'value' => $item->total_salidas
                ];
            });

        // Todos los materiales consumidos basados en salidas de oficina
        $topMateriales = SalidaOficina::join('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->join('articulos', 'inventario_oficinas.articulo_id', '=', 'articulos.id')
            ->selectRaw('articulos.articulo as name, SUM(salida_oficinas.cantidad) as total_salidas')
            ->groupBy('articulos.id', 'articulos.articulo')
            ->orderBy('total_salidas', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'value' => $item->total_salidas
                ];
            });

        return view('administracion.reportes.index', compact(
            'paginatedItems',
            'totalItems',
            'totalItemsDisponibles',
            'porcentajeDisponible',
            'itemsEsteMes',
            'stockBajo',
            'totalTipos',
            'ultimasFechas',
            'cartuchos',
            'colores',
            'impresoras',
            'fabricantes',
            'articulos',
            'unidadMedidas',
            'areas',
            'proveedores',
            'gerencias',
            'users',
            'recentActivities',
            'activitiesThisMonth',
            'topConsumibles',
            'topMateriales',
            'lowStockItems'
        ));
    }

    /**
     * Export Report by Gerencia and Area to PDF
     */
    public function exportGerenciaAreaReportPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        $gerenciaId = $request->input('gerencia_id');
        $areaId = $request->input('area_id');

        // Count SalidaOficina filtered by gerencia and area
        $salidaOficinaCountQuery = SalidaOficina::query();
        if ($gerenciaId) {
            $salidaOficinaCountQuery->where('gerencia_id', $gerenciaId);
        }
        if ($areaId) {
            $salidaOficinaCountQuery->where('area_id', $areaId);
        }
        $salidaOficinaCount = $salidaOficinaCountQuery->count();

        // Count SalidaAlmacenamientoImpresora filtered by gerencia and area through DistribucionAlmacenamientoImpresora
        $salidaAlmacenCountQuery = SalidaAlmacenamientoImpresora::whereHas('distribucionAlmacenamientoImpresora', function ($query) use ($gerenciaId, $areaId) {
            if ($gerenciaId) {
                $query->where('gerencia_id', $gerenciaId);
            }
            if ($areaId) {
                $query->whereHas('gerencia', function ($q) use ($areaId) {
                    $q->where('area_id', $areaId);
                });
            }
        });
        $salidaAlmacenCount = $salidaAlmacenCountQuery->count();

        $gerencia = null;
        $area = null;

        if ($gerenciaId) {
            $gerencia = \App\Models\Gerencia::with('area')->find($gerenciaId);
        }
        if ($areaId) {
            $area = \App\Models\Area::find($areaId);
        }

        $pdf = \PDF::loadView('administracion.reportes.gerencia_area_report_pdf', compact(
            'salidaOficinaCount',
            'salidaAlmacenCount',
            'gerencia',
            'area'
        ));

        return $pdf->download('reporte_gerencia_area.pdf');
    }

    /**
     * Export to Excel
     */
public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventarioUnificadoExport($request->all()), 'reportes.xlsx');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Query para inventario de impresoras
        $queryImpresora = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores')
            ->select('*', DB::raw("'impresora' as tipo"));

        // Query para inventario de oficina
        $queryOficina = InventarioOficina::with('articulos', 'unidad_medidas', 'proveedores')
            ->select('*', DB::raw("'oficina' as tipo"));

        // Aplicar los mismos filtros que en index
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $queryImpresora->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
            $queryOficina->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        }

        if ($request->filled('filterType') && $request->filled('secondFilter')) {
            $filterType = $request->input('filterType');
            $secondFilter = $request->input('secondFilter');
            switch ($filterType) {
                case 'proveedore_id':
                    $queryImpresora->where('proveedore_id', $secondFilter);
                    $queryOficina->where('proveedore_id', $secondFilter);
                    break;

                case 'cartucho_id':
                    $queryImpresora->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $queryImpresora->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $queryImpresora->where('impresora_id', $secondFilter);
                    break;
                case 'fabricante_id':
                    $queryImpresora->where('fabricante_id', $secondFilter);
                    break;
                case 'articulo_id':
                    $queryOficina->where('articulo_id', $secondFilter);
                    break;
                case 'unidad_medida_id':
                    $queryOficina->where('unidad_medida_id', $secondFilter);
                    break;
            }
        }

        if ($request->filled('n_factura')) {
            $queryImpresora->where('n_factura', 'like', '%' . $request->input('n_factura') . '%');
            $queryOficina->where('n_factura', 'like', '%' . $request->input('n_factura') . '%');
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $queryImpresora->where(function ($q) use ($busqueda) {
                $q->where('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q7) use ($busqueda) {
                      $q7->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
            $queryOficina->where(function ($q) use ($busqueda) {
                $q->where('descripcion', 'like', "%{$busqueda}%")
                  ->orWhere('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('articulos', function ($q2) use ($busqueda) {
                      $q2->where('articulo', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q4) use ($busqueda) {
                      $q4->where('proveedor', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Unir los resultados
        $inventarioImpresora = $queryImpresora->get();
        $inventarioOficina = $queryOficina->get();
        $inventarioUnificado = $inventarioImpresora->concat($inventarioOficina)->sortByDesc('fecha_ingreso');

        $pdf = \PDF::loadView('administracion.reportes.pdf', compact('inventarioUnificado'));
        return $pdf->download('reportes.pdf');
    }

    /**
     * Export Summary to PDF
     */
    public function exportSummaryPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Resumen de Almacenamiento Impresoras
        $totalToner = AlmacenamientoImpresora::sum('unidades_disponibles');
        $totalItemsAlmacenamiento = AlmacenamientoImpresora::count();
        $stockBajoAlmacenamiento = AlmacenamientoImpresora::where('unidades_disponibles', '<', 5)->count();

        // Resumen de Inventario Oficina
        $totalItemsOficina = InventarioOficina::sum('unidades_disponibles');
        $totalRegistrosOficina = InventarioOficina::count();
        $stockBajoOficina = InventarioOficina::where('unidades_disponibles', '<', 5)->count();

        // Datos para el PDF
        $summary = [
            'totalToner' => $totalToner,
            'totalItemsAlmacenamiento' => $totalItemsAlmacenamiento,
            'stockBajoAlmacenamiento' => $stockBajoAlmacenamiento,
            'totalItemsOficina' => $totalItemsOficina,
            'totalRegistrosOficina' => $totalRegistrosOficina,
            'stockBajoOficina' => $stockBajoOficina,
        ];

        $pdf = \PDF::loadView('administracion.reportes.summary_pdf', compact('summary'));
        return $pdf->download('resumen_inventario.pdf');
    }

    /**
     * Export Consumption Analysis to PDF
     */
    public function exportConsumptionPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Todos los consumibles utilizados basados en salidas del almacén
        // Ahora incluyendo el nombre de la impresora
        $consumibles = SalidaAlmacenamientoImpresora::join('almacenamiento_impresoras', 'salida_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->leftJoin('impresoras', 'cartuchos.impresora_id', '=', 'impresoras.id')
            ->selectRaw('cartuchos.cartucho as name, COALESCE(impresoras.impresora, "Sin asignar") as printer_name, SUM(salida_almacenamiento_impresoras.cantidad) as total_salidas')
            ->groupBy('cartuchos.id', 'cartuchos.cartucho', 'impresoras.impresora')
            ->orderBy('total_salidas', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'printer_name' => $item->printer_name,
                    'value' => $item->total_salidas
                ];
            });

        // Todos los materiales consumidos basados en salidas de oficina
        $materiales = SalidaOficina::join('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->join('articulos', 'inventario_oficinas.articulo_id', '=', 'articulos.id')
            ->selectRaw('articulos.articulo as name, SUM(salida_oficinas.cantidad) as total_salidas')
            ->groupBy('articulos.id', 'articulos.articulo')
            ->orderBy('total_salidas', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'value' => $item->total_salidas
                ];
            });

        $pdf = \PDF::loadView('administracion.reportes.consumption_pdf', compact('consumibles', 'materiales'));
        return $pdf->download('analisis_consumo.pdf');
    }

    /**
     * Export Suppliers Report to PDF
     */
    public function exportSuppliersPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        $proveedores = Proveedore::orderBy('proveedor')->get();

        $pdf = \PDF::loadView('administracion.reportes.suppliers_pdf', compact('proveedores'));
        return $pdf->download('reporte-proveedores.pdf');
    }

    /**
     * Export Low Stock Report to PDF
     */
    public function exportLowStockPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Poblar lowStockItems
        $lowStockItems = collect();

        // Items de almacenamiento con stock bajo (usando GREATEST para evitar negativos)
        $almacenamientoItems = AlmacenamientoImpresora::with('cartuchos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_almacenamiento_impresoras WHERE almacenamiento_impresora_id = almacenamiento_impresoras.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($almacenamientoItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->cartuchos->cartucho ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'Consumible',
                    'categoria' => 'Almacenamiento',
                    'color' => $stock < 3 ? 'red' : 'yellow',
                    'fecha_ingreso' => $item->fecha_ingreso ?? null,
                ]);
            }
        }

        // Items de inventario impresora con stock bajo
        $impresoraItems = InventarioImpresora::with('cartuchos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_impresoras WHERE inventario_impresora_id = inventario_impresoras.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($impresoraItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->cartuchos->cartucho ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'Consumible',
                    'categoria' => 'Inventario Impresora',
                    'color' => $stock < 3 ? 'red' : 'yellow',
                    'fecha_ingreso' => $item->fecha_ingreso ?? null,
                ]);
            }
        }

        // Items de inventario oficina con stock bajo
        $oficinaItems = InventarioOficina::with('articulos')
            ->select('*', DB::raw('GREATEST(0, unidades - COALESCE((SELECT SUM(cantidad) FROM salida_oficinas WHERE inventario_oficina_id = inventario_oficinas.id), 0)) as stock_disponible'))
            ->having('stock_disponible', '<', 5)
            ->get();
        foreach ($oficinaItems as $item) {
            $stock = max(0, $item->stock_disponible);
            if ($stock < 5) {
                $lowStockItems->push([
                    'nombre' => $item->articulos->articulo ?? 'N/A',
                    'stock' => $stock,
                    'tipo' => 'Artículo',
                    'categoria' => 'Inventario Oficina',
                    'color' => $stock < 3 ? 'red' : 'yellow',
                    'fecha_ingreso' => $item->fecha_ingreso ?? null,
                ]);
            }
        }

        // Ordenar por stock disponible ascendente (más crítico primero)
        $lowStockItems = $lowStockItems->sortBy('stock');

        $pdf = \PDF::loadView('administracion.reportes.lowstock_pdf', compact('lowStockItems'));
        return $pdf->download('reporte-stock-critico.pdf');
    }

    /**
     * Export Audit Report to PDF
     */
    public function exportAuditPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Obtener datos de inventario de oficina
        $inventarioOficina = InventarioOficina::with('articulos', 'unidad_medidas', 'users', 'proveedores')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        // Obtener datos de salidas de oficina
        $salidaOficina = SalidaOficina::with('inventario_oficinas.articulos', 'inventario_oficinas.unidad_medidas', 'inventario_oficinas.proveedores', 'users')
            ->orderBy('fecha_salida', 'desc')
            ->get();

        // Obtener datos de almacenamiento de impresoras
        $almacenamientoImpresoras = AlmacenamientoImpresora::with('cartuchos.impresora', 'cartuchos.fabricante', 'colores', 'proveedores', 'users')
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        // Obtener datos de distribuciones de almacén
        $distribucionAlmacen = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos', 'almacenamientoImpresora.colores', 'gerencia', 'area', 'almacenamientoImpresora.proveedores', 'users')
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener datos de salidas de almacén
        $salidaAlmacen = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos', 'almacenamientoImpresora.colores', 'almacenamientoImpresora.proveedores', 'users', 'distribucionAlmacenamientoImpresora.gerencia', 'distribucionAlmacenamientoImpresora.area')
            ->orderBy('fecha_salida', 'desc')
            ->get();

        $pdf = \PDF::loadView('administracion.reportes.audit_pdf', compact(
            'inventarioOficina',
            'salidaOficina',
            'almacenamientoImpresoras',
            'distribucionAlmacen',
            'salidaAlmacen'
        ));
        return $pdf->download('auditoria_inventario.pdf');
    }

    /**
     * Show reports page
     */
    public function reportes(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index') && Gate::denies('inventarioofic_index'), 403);

        // Query para inventario de impresoras
        $queryImpresora = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores')
            ->select('*', DB::raw("'impresora' as tipo"));

        // Query para inventario de oficina
        $queryOficina = InventarioOficina::with('articulos', 'unidad_medidas', 'proveedores')
            ->select('*', DB::raw("'oficina' as tipo"));

        // Unir los resultados
        $inventarioImpresora = $queryImpresora->get();
        $inventarioOficina = $queryOficina->get();
        $paginatedItems = $inventarioImpresora->concat($inventarioOficina)->sortByDesc('fecha_ingreso');

        // Estadísticas para reportes
        $totalItems = InventarioImpresora::sum('unidades') + InventarioOficina::sum('unidades');
        $totalItemsDisponibles = InventarioImpresora::sum('unidades_disponibles') + InventarioOficina::sum('unidades_disponibles');

        // Calcular total de tóner disponible considerando las salidas
        $totalTonerInicial = AlmacenamientoImpresora::sum('unidades');
        $totalTonerSalidas = SalidaAlmacenamientoImpresora::sum('cantidad');
        $totalTonerDisponible = $totalTonerInicial - $totalTonerSalidas;

        $stockBajoAlmacenamiento = AlmacenamientoImpresora::whereRaw('unidades_disponibles < (SELECT stock_min FROM cartuchos WHERE cartuchos.id = almacenamiento_impresoras.cartucho_id)')->count();
        $stockBajoOficina = InventarioOficina::whereRaw('unidades_disponibles < (SELECT stock_min FROM articulos WHERE articulos.id = inventario_oficinas.articulo_id)')->count();

        // Obtener stock bajo dinámico en salidas almacen considerando historico_unidades_asignadas_restantes y stock_min
        $stockBajoSalidaAlmacen = SalidaAlmacenamientoImpresora::join('almacenamiento_impresoras', 'salida_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->whereRaw('salida_almacenamiento_impresoras.historico_unidades_asignadas_restantes < cartuchos.stock_min')
            ->count();

        $stockBajo = $stockBajoAlmacenamiento + $stockBajoOficina + $stockBajoSalidaAlmacen;

        // Crear lista lowStockItems con los mismos criterios para la vista
        $lowStockItems = collect();

        $almacenamientoItems = AlmacenamientoImpresora::with('cartuchos')
            ->select('*', DB::raw('(unidades_disponibles) as stock_disponible'))
            ->whereRaw('unidades_disponibles < (SELECT stock_min FROM cartuchos WHERE cartuchos.id = almacenamiento_impresoras.cartucho_id)')
            ->get();

        foreach ($almacenamientoItems as $item) {
            $lowStockItems->push([
                'nombre' => $item->cartuchos->cartucho ?? 'N/A',
                'stock' => $item->stock_disponible,
                'tipo' => 'Consumible',
                'categoria' => 'Almacenamiento',
                'color' => $item->stock_disponible < 3 ? 'red' : 'yellow',
            ]);
        }

        $oficinaItems = InventarioOficina::with('articulos')
            ->select('*', DB::raw('(unidades_disponibles) as stock_disponible'))
            ->whereRaw('unidades_disponibles < (SELECT stock_min FROM articulos WHERE articulos.id = inventario_oficinas.articulo_id)')
            ->get();

        foreach ($oficinaItems as $item) {
            $lowStockItems->push([
                'nombre' => $item->articulos->articulo ?? 'N/A',
                'stock' => $item->stock_disponible,
                'tipo' => 'Artículo',
                'categoria' => 'Inventario Oficina',
                'color' => $item->stock_disponible < 3 ? 'red' : 'yellow',
            ]);
        }

        // Ordenar lowStockItems por stock asc (más crítico primero)
        $lowStockItems = $lowStockItems->sortBy('stock')->values();

        $itemsEsteMes = InventarioImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        InventarioOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        AlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaAlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        SalidaOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        DistribucionAlmacenamientoImpresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Cartucho::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Colore::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Impresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Fabricante::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Articulo::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        UnidadMedida::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Area::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Gerencia::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() +
                        Proveedore::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        return view('administracion.reportes.reportes', compact(
            'paginatedItems',
            'totalItems',
            'totalItemsDisponibles',
            'totalTonerDisponible',
            'stockBajo',
            'itemsEsteMes',
            'lowStockItems' // pass lowStockItems to view
        ));
    }

}
