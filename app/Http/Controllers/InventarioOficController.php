<?php
namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use App\Models\Articulo;
use App\Models\Proveedore;
use App\Models\Ubicacion;
use App\Models\Area;
use App\Models\Gerencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;
use App\Models\InventarioOficina;
use Illuminate\Support\Facades\Hash;

class InventarioOficController extends Controller
{
    public function index(Request $request)
    {
         abort_if(Gate::denies('inventarioofic_index'), 403);

        // Filtros
        $query = InventarioOficina::with(['proveedores', 'articulos', 'unidad_medidas'])->orderBy('id', 'desc');

        // Aplicar filtros según el tipo seleccionado
        if ($request->filled('filterType')) {
            switch ($request->filterType) {
                case 'fecha':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('fecha_ingreso', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('fecha_ingreso', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('fecha_ingreso', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'fecha_creacion':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('created_at', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('created_at', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'item':
                    if ($request->filled('secondFilter')) {
                        $query->where('id', $request->secondFilter);
                    }
                    break;
                case 'proveedore_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('proveedore_id', $request->secondFilter);
                    }
                    break;
                case 'articulo_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('articulo_id', $request->secondFilter);
                    }
                    break;
                case 'unidad_medida_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('unidad_medida_id', $request->secondFilter);
                    }
                    break;

                case 'n_factura':
                    if ($request->filled('secondFilter')) {
                        $query->where('n_factura', $request->secondFilter);
                    } elseif ($request->filled('n_factura')) {
                        $query->where('n_factura', 'like', '%' . $request->n_factura . '%');
                    }
                    break;
case 'busqueda':
                    if ($request->filled('busqueda')) {
                        $search = $request->busqueda;
                        $query->where(function($q) use ($search) {
                            $q->where('n_factura', 'like', '%' . $search . '%')
                              ->orWhereHas('proveedores', function($subQ) use ($search) {
                                  $subQ->where('proveedor', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('articulos', function($subQ) use ($search) {
                                  $subQ->where('articulo', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('unidad_medidas', function($subQ) use ($search) {
                                  $subQ->where('unidad_medida', 'like', '%' . $search . '%');
                              });
                        });
                    }
                    break;
            }
        }

        // Obtener los registros paginados
        $inventario_oficinas_paginated = $query->paginate(15);

        // Determinar si estamos filtrando por artículo específico (para mostrar todos los registros individuales)
        $mostrarTodosRegistros = $request->filled('filterType') && $request->filterType === 'articulo_id' && $request->filled('secondFilter');

        // Calcular totales por artículo ANTES de cualquier agrupación
        $totalesPorArticulo = [];
        $tempCollection = collect($inventario_oficinas_paginated->items());
        foreach ($tempCollection as $item) {
            $articuloId = $item->articulo_id;
            if (!isset($totalesPorArticulo[$articuloId])) {
                $totalesPorArticulo[$articuloId] = [
                    'unidades' => $tempCollection->where('articulo_id', $articuloId)->sum('unidades'),
                    'unidades_disponibles' => $tempCollection->where('articulo_id', $articuloId)->sum('unidades_disponibles'),
                ];
            }
        }

        // Agrupar por articulo_id y sumar unidades disponibles (solo si NO estamos filtrando por un artículo específico)
        if ($mostrarTodosRegistros) {
            // Cuando filtramos por artículo específico, mostrar cada registro individualmente
            $inventario_oficinas = $inventario_oficinas_paginated;
        } else {
            // Agrupar normalmente pero mantener todos los registros individuales
            $groupedItems = $inventario_oficinas_paginated->getCollection()->groupBy('articulo_id')->map(function ($items) {
                $firstItem = $items->first();
                $totalUnidadesDisponibles = $items->sum('unidades_disponibles');
                $totalUnidades = $items->sum('unidades');

                return (object) [
                    'id' => $firstItem->id,
                    'articulo_id' => $firstItem->articulo_id,
                    'articulo' => $firstItem->articulos,
                    'proveedore_id' => $firstItem->proveedore_id,
                    'proveedores' => $firstItem->proveedores,
                    'unidad_medida_id' => $firstItem->unidad_medida_id,
                    'unidad_medidas' => $firstItem->unidad_medidas,
                    'fecha_ingreso' => $firstItem->fecha_ingreso,
                    'n_factura' => $firstItem->n_factura,
                    'unidades' => $totalUnidades,
                    'unidades_disponibles' => $totalUnidadesDisponibles,
                    'suma_unidades' => $totalUnidades,
                    'created_at' => $firstItem->created_at,
                    'user_id' => $firstItem->user_id,
                    'items_count' => $items->count(),
                    'original_items' => $items,
                    'articulos' => $firstItem->articulos
                ];
            })->values();

            $inventario_oficinas = new \Illuminate\Pagination\LengthAwarePaginator(
                $groupedItems,
                $inventario_oficinas_paginated->total(),
                $inventario_oficinas_paginated->perPage(),
                $inventario_oficinas_paginated->currentPage(),
                ['path' => $inventario_oficinas_paginated->path(), 'pageName' => $inventario_oficinas_paginated->getPageName()]
            );
        }

        $ultimaFecha = InventarioOficina::orderBy('fecha_ingreso', 'desc')->first();
        $ultimasFechas = $ultimaFecha ? \Carbon\Carbon::parse($ultimaFecha->fecha_ingreso)->format('d/m/Y') : 'N/A';

        $totalItemsRegistrados = InventarioOficina::count();
        $itemsEsteMes = InventarioOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('unidades');
        $porcentajeItems = $totalItemsRegistrados > 0 ? round(($itemsEsteMes / $totalItemsRegistrados) * 100, 1) : 0;

        $articulosStockBajo = InventarioOficina::select('articulo_id', DB::raw('SUM(unidades_disponibles) as total_disponibles'), DB::raw('MIN((SELECT stock_min FROM articulos WHERE articulos.id = inventario_oficinas.articulo_id)) as stock_min'))
            ->with('articulos')
            ->groupBy('articulo_id')
            ->havingRaw('SUM(unidades_disponibles) < MIN((SELECT stock_min FROM articulos WHERE articulos.id = inventario_oficinas.articulo_id))')
            ->orderBy(DB::raw('SUM(unidades_disponibles)'), 'asc')
            ->take(2)
            ->get();

        $stockBajo = $articulosStockBajo->count();

        $articuloMasRegistros = InventarioOficina::select('articulo_id', DB::raw('count(*) as total_registros'))
            ->with('articulos')
            ->groupBy('articulo_id')
            ->orderBy('total_registros', 'desc')
            ->first();

        $proveedores = Proveedore::all();
        $articulos = Articulo::all();
        $unidad_medidas = UnidadMedida::all();
        $facturas = InventarioOficina::distinct()->pluck('n_factura')->filter()->sort()->values();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'inventario' => $inventario_oficinas,
                    'metrics' => [
                        'ultimasFechas' => $ultimasFechas,
                        'totalItemsRegistrados' => $totalItemsRegistrados,
                        'itemsEsteMes' => $itemsEsteMes,
                        'porcentajeItems' => $porcentajeItems,
                        'stockBajo' => $stockBajo,
                        'articulosStockBajo' => $articulosStockBajo,
                        'articuloMasRegistros' => $articuloMasRegistros,
                    ],
                    'catalogs' => [
                        'proveedores' => $proveedores,
                        'articulos' => $articulos,
                        'unidad_medidas' => $unidad_medidas,
                        'facturas' => $facturas,
                    ],
                    'permissions' => [
                        'can_create' => auth()->user()?->can('inventarioofic_create') ?? false,
                        'can_edit' => auth()->user()?->can('inventarioofic_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('inventarioofic_destroy') ?? false,
                        'can_salida' => auth()->user()?->can('salidaofic_create') ?? false,
                        'can_export' => true,
                    ]
                ],
                'message' => 'Inventario de oficina obtenido exitosamente'
            ], 200);
        }

        return view('administracion.inventario_oficinas.index', compact(
            'inventario_oficinas',
            'ultimasFechas',
            'totalItemsRegistrados',
            'itemsEsteMes',
            'porcentajeItems',
            'stockBajo',
            'articulosStockBajo',
            'articuloMasRegistros',
            'mostrarTodosRegistros',
            'totalesPorArticulo',
            'proveedores',
            'articulos',
            'unidad_medidas',
            'facturas',
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('inventarioofic_create'), 403);
        return view('administracion.inventario_oficinas.create', [
            "unidad_medidas" => UnidadMedida::all(),
            "articulos" => Articulo::all(),
            "proveedores" => Proveedore::all(),
            "ubicacions" => Ubicacion::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_ingreso' =>'required|array',
            'unidad_medida_id' => 'required|array',
            'articulo_id' => 'required|array',
            'proveedore_id' => 'required|array',
            'unidades' => 'required|array|min:1',
            'n_factura' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->fecha_ingreso as $index => $fecha) {
            if (!empty($request->articulo_id[$index]) && !empty($request->unidades[$index])) {
                $inventario_oficina = new InventarioOficina;
                $inventario_oficina->fecha_ingreso = $fecha;
                $inventario_oficina->unidad_medida_id = $request->unidad_medida_id[$index];
                $inventario_oficina->articulo_id = $request->articulo_id[$index];
                $inventario_oficina->proveedore_id = $request->proveedore_id[$index];
                $inventario_oficina->ubicacion_id = $request->ubicacion_id[$index] ?? null;
                $inventario_oficina->unidades = $request->unidades[$index];
                $inventario_oficina->unidades_disponibles = $request->unidades[$index];
                $inventario_oficina->n_factura = $request->n_factura[$index];
                $inventario_oficina->user_id = auth()->id();
                $inventario_oficina->save();
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->route('administracion.inventario_oficinas.index')->with('datos', "Se guardaron {$count} registro(s) exitosamente");
        } else {
            return redirect()->back()->with('error', 'No se pudo guardar ningún registro');
        }
    }

    public function edit($id)
    {
        abort_if(Gate::denies('inventarioofic_edit'), 403);
        $inventario_oficina = InventarioOficina::find($id);
        return view('administracion.inventario_oficinas.edit', [
            'inventario_oficina' => $inventario_oficina,
            'unidad_medidas' => UnidadMedida::all(),
            'articulos' => Articulo::all(),
            'proveedores' => Proveedore::all(),
            'ubicacions' => Ubicacion::all(),
        ]);
    }

    public function show()
    {
        return view('administracion.inventario_oficinas.show', ['inventario_oficina' => InventarioOficina::get()]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_ingreso' =>'required|date|before_or_equal:today',
            'unidad_medida_id' => 'required',
            'articulo_id' => 'required',
            'proveedore_id' => 'required',
            'unidades' => 'required|integer|min:1',
            'n_factura' => 'required',
        ]);
        $inventario_oficina = InventarioOficina::find($id);
        $inventario_oficina->fecha_ingreso = $request->input('fecha_ingreso');
        $inventario_oficina->unidad_medida_id = $request->input('unidad_medida_id');
        $inventario_oficina->articulo_id = $request->input('articulo_id');
        $inventario_oficina->proveedore_id = $request->input('proveedore_id');
        $inventario_oficina->n_factura = $request->input('n_factura');
        $inventario_oficina->unidades = $request->input('unidades');
        $inventario_oficina->user_id = auth()->id();
        $inventario_oficina->save();

        return redirect()->route('administracion.inventario_oficinas.index', $inventario_oficina->id)->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('inventarioofic_destroy'), 403);
        $inventario_oficina = InventarioOficina::find($id);
        $inventario_oficina->delete();
        return redirect()->route('administracion.inventario_oficinas.index')->with('success', 'Registro eliminado correctamente');
    }

    public function destroyMultiple(Request $request)
    {
        abort_if(Gate::denies('inventarioofic_destroy'), 403);

        $request->validate([
            'ids' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        $ids = json_decode($request->ids, true);
        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->back()->with('error', 'No se seleccionaron registros para eliminar.');
        }

        $count = count($ids);

        try {
            DB::transaction(function () use ($ids) {
                InventarioOficina::whereIn('id', $ids)->delete();
            });
            return redirect()->route('administracion.inventario_oficinas.index')->with('datos', "Se eliminaron {$count} registro(s) correctamente.");
        } catch (\Exception $e) {
            return redirect()->route('administracion.inventario_oficinas.index')->with('error', 'Error al eliminar los registros: ' . $e->getMessage());
        }
    }

    public function destroyAll(Request $request)
    {
        abort_if(Gate::denies('inventarioofic_destroy'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        $count = InventarioOficina::count();

        try {
            DB::transaction(function () {
                InventarioOficina::query()->delete();
            });
            return redirect()->route('administracion.inventario_oficinas.index')->with('datos', "Se eliminaron todos los registros ({$count}) correctamente.");
        } catch (\Exception $e) {
            return redirect()->route('administracion.inventario_oficinas.index')->with('error', 'Error al eliminar los registros: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('inventarioofic_index'), 403);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventarioOficinaExport($request->all()), 'inventario_oficinas.xlsx');
    }

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioofic_index'), 403);

        $query = InventarioOficina::with(['proveedores']);

        if ($request->filled('filterType')) {
            switch ($request->filterType) {
                case 'fecha':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('fecha_ingreso', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('fecha_ingreso', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('fecha_ingreso', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'fecha_creacion':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('created_at', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('created_at', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'item':
                    if ($request->filled('secondFilter')) {
                        $query->where('id', $request->secondFilter);
                    }
                    break;
                case 'proveedore_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('proveedore_id', $request->secondFilter);
                    }
                    break;
                case 'articulo_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('articulo_id', $request->secondFilter);
                    }
                    break;
                case 'unidad_medida_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('unidad_medida_id', $request->secondFilter);
                    }
                    break;
                case 'n_factura':
                    if ($request->filled('secondFilter')) {
                        $query->where('n_factura', $request->secondFilter);
                    } elseif ($request->filled('n_factura')) {
                        $query->where('n_factura', 'like', '%' . $request->n_factura . '%');
                    }
                    break;
                case 'busqueda':
                    if ($request->filled('busqueda')) {
                        $search = $request->busqueda;
                        $query->where(function($q) use ($search) {
                            $q->where('n_factura', 'like', '%' . $search . '%')
                              ->orWhereHas('proveedores', function($subQ) use ($search) {
                                  $subQ->where('proveedor', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('articulos', function($subQ) use ($search) {
                                  $subQ->where('articulo', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('unidad_medidas', function($subQ) use ($search) {
                                  $subQ->where('unidad_medida', 'like', '%' . $search . '%');
                              });
                        });
                    }
                    break;
            }
        }

        $inventario_oficinas = $query->get();
        $totalItems = $inventario_oficinas->count();
        $totalUnidades = $inventario_oficinas->sum('unidades');
        $totalUnidadesDisponibles = $inventario_oficinas->sum('unidades_disponibles');

        $pdf = \PDF::loadView('administracion.inventario_oficinas.pdf', compact('inventario_oficinas', 'totalItems', 'totalUnidades', 'totalUnidadesDisponibles'));
        return $pdf->download('inventario_oficinas.pdf');
    }
}
