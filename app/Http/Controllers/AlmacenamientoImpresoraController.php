<?php

namespace App\Http\Controllers;

use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Proveedore;
use App\Models\Impresora;
use App\Models\Fabricante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;
use App\Models\AlmacenamientoImpresora;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AlmacenamientoImpresoraExport;

class AlmacenamientoImpresoraController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('almacen_inv_index'), 403);

        // Debug: Log all request parameters
        \Log::info('AlmacenamientoImpresoraController - Request parameters:', $request->all());

        $query = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores', 'users')->orderBy('id', 'desc');

        // Apply filters
        if ($request->filled('filterType') && $request->input('filterType') === 'created_at') {
            // Filtro de fecha mejorado para created_at - permite filtrar por fechas individuales o rangos
            if ($request->filled('fecha_desde_created_at') && $request->filled('fecha_hasta_created_at')) {
                \Log::info('Applying created_at range filter:', [$request->input('fecha_desde_created_at'), $request->input('fecha_hasta_created_at')]);
                $query->whereBetween('created_at', [$request->input('fecha_desde_created_at'), $request->input('fecha_hasta_created_at')]);
            } elseif ($request->filled('fecha_desde_created_at')) {
                // Solo fecha_desde_created_at - filtrar desde esa fecha hasta ahora
                $query->whereDate('created_at', '>=', $request->input('fecha_desde_created_at'));
            } elseif ($request->filled('fecha_hasta_created_at')) {
                // Solo fecha_hasta_created_at - filtrar desde el inicio hasta esa fecha
                $query->whereDate('created_at', '<=', $request->input('fecha_hasta_created_at'));
            }
        } elseif ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            // Filtro de fecha mejorado para fecha_ingreso - permite filtrar por fechas individuales o rangos
            \Log::info('Applying fecha_ingreso range filter:', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
            $query->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('fecha_ingreso', '>=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('fecha_ingreso', '<=', $request->input('fecha_hasta'));
        }
        // Handle secondFilter based on filterType
        if ($request->filled('filterType') && $request->filled('secondFilter')) {
            $filterType = $request->input('filterType');
            $secondFilter = $request->input('secondFilter');
            switch ($filterType) {
                case 'cartucho_id':
                    $query->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $query->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $query->where('impresora_id', $secondFilter);
                    break;
                case 'proveedore_id':
                    $query->where('proveedore_id', $secondFilter);
                    break;
                case 'fabricante_id':
                    $query->where('fabricante_id', $secondFilter);
                    break;
            }
        }

        // Direct filters
        if ($request->filled('cartucho_id')) {
            $query->where('cartucho_id', $request->input('cartucho_id'));
        }
        if ($request->filled('colore_id')) {
            $query->where('colore_id', $request->input('colore_id'));
        }
        if ($request->filled('impresora_id')) {
            $query->where('impresora_id', $request->input('impresora_id'));
        }
        if ($request->filled('proveedore_id')) {
            $query->where('proveedore_id', $request->input('proveedore_id'));
        }
        if ($request->filled('fabricante_id')) {
            $query->where('fabricante_id', $request->input('fabricante_id'));
        }
        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('n_factura', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('colores', function ($q3) use ($busqueda) {
                      $q3->where('colore', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q4) use ($busqueda) {
                      $q4->where('impresora', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('proveedores', function ($q5) use ($busqueda) {
                      $q5->where('proveedor', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q6) use ($busqueda) {
                      $q6->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        // Apply pagination to the query directly
        $almacenamiento_impresoras = $query->paginate(5);

        // Calculate totals per printer, color AND date for display in index
        $allItems = $query->get();
        $totalesPorImpresoraColor = [];
        foreach($allItems as $item) {
            $impresoraId = $item->impresora_id;
            $colorId = $item->colore_id;
            $fechaIngreso = $item->fecha_ingreso;
            $key = $impresoraId . '_' . $colorId . '_' . $fechaIngreso;
            if (!isset($totalesPorImpresoraColor[$key])) {
                $totalesPorImpresoraColor[$key] = [
                    'impresora_id' => $impresoraId,
                    'colore_id' => $colorId,
                    'fecha_ingreso' => $fechaIngreso,
                    'unidades' => $allItems->where('impresora_id', $impresoraId)->where('colore_id', $colorId)->where('fecha_ingreso', $fechaIngreso)->sum('unidades'),
                    'unidades_disponibles' => $allItems->where('impresora_id', $impresoraId)->where('colore_id', $colorId)->where('fecha_ingreso', $fechaIngreso)->sum('unidades_disponibles'),
                ];
            }
        }

        // Calculate stats
        $totalMateriales = AlmacenamientoImpresora::sum('unidades');
        $totalConsumiblesRegistrados = AlmacenamientoImpresora::count();
        $totalDisponiblesCalculadas = AlmacenamientoImpresora::sum('unidades_disponibles');
        $porcentajeConsumibles = $totalMateriales > 0 ? round(($totalDisponiblesCalculadas / $totalMateriales) * 100, 1) : 0;
        $consumiblesEsteMes = \App\Models\Impresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $impresorasEsteMes = Impresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $stockBajoItems = AlmacenamientoImpresora::with('impresoras')
            ->join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->whereColumn('unidades_disponibles', '<', 'cartuchos.stock_min')
            ->select('almacenamiento_impresoras.*')
            ->get();

        $stockBajo = $stockBajoItems->count();
        $totalImpresoras = Impresora::distinct('id')->count();

        // Get IDs of items with low stock for highlighting
        $unidadesDisponiblesBajoStock = $stockBajoItems->pluck('id');

        // Get latest entry date (last date in d/m format)
        $ultimasFechas = AlmacenamientoImpresora::orderBy('fecha_ingreso', 'desc')->limit(1)->pluck('fecha_ingreso')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        })->implode('');

        // Fetch filter options
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $proveedores = Proveedore::all();

        return view('administracion.almacenamiento_impresoras.index', compact(
            'almacenamiento_impresoras', 'totalMateriales', 'totalConsumiblesRegistrados', 'porcentajeConsumibles', 'consumiblesEsteMes', 'stockBajo', 'stockBajoItems', 'totalImpresoras', 'ultimasFechas', 'impresorasEsteMes',
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores', 'unidadesDisponiblesBajoStock', 'totalesPorImpresoraColor'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('almacen_inv_create'), 403);

        // Get data for form
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $proveedores = Proveedore::all();

        // Get inventory records for dashboard
        $almacenamiento_impresoras = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores', 'users')->paginate(10);

        // Calculate stats
        $totalMateriales = AlmacenamientoImpresora::sum('unidades');
        $totalConsumibles = Cartucho::distinct('id')->count();
        $stockBajo = AlmacenamientoImpresora::where('unidades_disponibles', '<', 5)->count();
        $totalImpresoras = Impresora::distinct('id')->count();

        return view('administracion.almacenamiento_impresoras.create', compact(
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores',
            'almacenamiento_impresoras', 'totalMateriales', 'totalConsumibles', 'stockBajo', 'totalImpresoras'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_ingreso.*' =>'required|date|before_or_equal:today',
            'cartucho_id.*' => 'required',
            'colore_id.*' => 'required',
            'impresora_id.*' => 'required',
            'fabricante_id.*' => 'required',
            'proveedore_id.*' => 'required',
            'unidades.*' => 'required|integer|min:1',
            'n_factura.*' => 'required',
            'registro_tipo.*' => 'required|in:impresora,consumible',
        ]);

        $records = [];
        foreach ($request->fecha_ingreso as $index => $fecha) {
            $almacenamiento_impresora = new AlmacenamientoImpresora;
            $almacenamiento_impresora->fecha_ingreso = $fecha;
            $almacenamiento_impresora->cartucho_id = $request->cartucho_id[$index];
            $almacenamiento_impresora->colore_id = $request->colore_id[$index];
            $almacenamiento_impresora->impresora_id = $request->impresora_id[$index];
            $almacenamiento_impresora->fabricante_id = $request->fabricante_id[$index];
            $almacenamiento_impresora->proveedore_id = $request->proveedore_id[$index];

            $almacenamiento_impresora->unidades = $request->unidades[$index];
            $almacenamiento_impresora->unidades_disponibles = $request->unidades[$index];
            $almacenamiento_impresora->n_factura = $request->n_factura[$index];
            $almacenamiento_impresora->user_id = auth()->id();
            $almacenamiento_impresora->save();
            $records[] = $almacenamiento_impresora;
        }

        return redirect()->route('administracion.almacenamiento_impresoras.index')->with('datos', 'Registros exitosos: ' . count($records));
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(Gate::denies('almacen_inv_edit'), 403);

        $almacenamiento_impresora = AlmacenamientoImpresora::find($id);

        return view('administracion.almacenamiento_impresoras.edit', ['almacenamiento_impresora' =>
        $almacenamiento_impresora,
        'cartuchos' => Cartucho::with('colore', 'impresora', 'fabricante')->get(),
        'colores' => Colore::all(),
        'impresoras' => Impresora::all(),
        'fabricantes' => Fabricante::all(),

        'proveedores' => Proveedore::all(),
    ]);
    }

    public function show()
    {
        return view('administracion.almacenamiento_impresoras.show', ['almacenamiento_impresora' => AlmacenamientoImpresora::get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_ingreso' =>'required|date|before_or_equal:today',
            'cartucho_id' => 'required',
            'colore_id' => 'required',
            'impresora_id' => 'required',
            'fabricante_id' => 'required',
            'proveedore_id' => 'required',
            'n_factura' => 'required',
            'unidades' => 'required|integer|min:1',
        ]);
        $almacenamiento_impresora = AlmacenamientoImpresora::find($id);

        // Calcular la diferencia de unidades para ajustar unidades_disponibles
        $unidadesOriginales = $almacenamiento_impresora->unidades;
        $nuevasUnidades = $request->input('unidades');
        $diferencia = $nuevasUnidades - $unidadesOriginales;

        // Actualizar unidades y ajustar unidades_disponibles
        $almacenamiento_impresora->unidades = $nuevasUnidades;
        $almacenamiento_impresora->unidades_disponibles = $almacenamiento_impresora->unidades_disponibles + $diferencia;

        $almacenamiento_impresora->fecha_ingreso = $request->input('fecha_ingreso');
        $almacenamiento_impresora->cartucho_id = $request->input('cartucho_id');
        $almacenamiento_impresora->colore_id = $request->input('colore_id');
        $almacenamiento_impresora->impresora_id = $request->input('impresora_id');
        $almacenamiento_impresora->fabricante_id = $request->input('fabricante_id');
        $almacenamiento_impresora->proveedore_id = $request->input('proveedore_id');
        $almacenamiento_impresora->n_factura = $request->input('n_factura');
        $almacenamiento_impresora->user_id = auth()->id();

        $almacenamiento_impresora->save();

        return redirect()->route('administracion.almacenamiento_impresoras.index', $almacenamiento_impresora->id)->with('success', 'Registro actualizado correctamente');
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index'), 403);

        return Excel::download(new AlmacenamientoImpresoraExport($request), 'almacenamiento_impresoras.xlsx');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index'), 403);

        $query = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'proveedores', 'users');

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('almacenamiento_impresoras.id', array_filter($ids));
        }

        // Apply the same filters as in index
        $filterType = $request->input('filterType', 'fecha_ingreso');
        if ($filterType === 'created_at') {
            // Filtro de fecha mejorado para created_at - permite filtrar por fechas individuales o rangos
            if ($request->filled('fecha_desde_created_at') && $request->filled('fecha_hasta_created_at')) {
                $query->whereBetween('created_at', [$request->input('fecha_desde_created_at'), $request->input('fecha_hasta_created_at')]);
            } elseif ($request->filled('fecha_desde_created_at')) {
                // Solo fecha_desde_created_at - filtrar desde esa fecha hasta ahora
                $query->whereDate('created_at', '>=', $request->input('fecha_desde_created_at'));
            } elseif ($request->filled('fecha_hasta_created_at')) {
                // Solo fecha_hasta_created_at - filtrar desde el inicio hasta esa fecha
                $query->whereDate('created_at', '<=', $request->input('fecha_hasta_created_at'));
            }
        } elseif ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            // Filtro de fecha mejorado para fecha_ingreso - permite filtrar por fechas individuales o rangos
            $query->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('fecha_ingreso', '>=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('fecha_ingreso', '<=', $request->input('fecha_hasta'));
        }
        if ($request->filled('filterType') && $request->filled('secondFilter')) {
            $filterType = $request->input('filterType');
            $secondFilter = $request->input('secondFilter');
            switch ($filterType) {
                case 'cartucho_id':
                    $query->where('cartucho_id', $secondFilter);
                    break;
                case 'colore_id':
                    $query->where('colore_id', $secondFilter);
                    break;
                case 'impresora_id':
                    $query->where('impresora_id', $secondFilter);
                    break;
                case 'proveedore_id':
                    $query->where('proveedore_id', $secondFilter);
                    break;

                case 'fabricante_id':
                    $query->where('fabricante_id', $secondFilter);
                    break;
            }
        }
        if ($request->filled('n_factura')) {
            $query->where('n_factura', 'like', '%' . $request->input('n_factura') . '%');
        }
        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
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
                  ->orWhereHas('fabricantes', function ($q6) use ($busqueda) {
                      $q6->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        $almacenamiento_impresoras = $query->get();

$pdf = \PDF::loadView('administracion.almacenamiento_impresoras.pdf', compact('almacenamiento_impresoras'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
return $pdf->download('almacenamiento_impresoras.pdf');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_if(Gate::denies('almacen_inv_destroy'), 403);
        $almacenamiento_impresora = AlmacenamientoImpresora::find($id);
        $almacenamiento_impresora->delete();
        return redirect()->route('administracion.almacenamiento_impresoras.index')->with('Fila eliminada');
    }
}
