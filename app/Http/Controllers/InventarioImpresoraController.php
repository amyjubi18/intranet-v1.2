<?php

namespace App\Http\Controllers;

use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Proveedore;
use App\Models\Gerencia;
use App\Models\Impresora;
use App\Models\Area;
use App\Models\Fabricante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;
use App\Models\InventarioImpresora;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventarioImpresoraExport;
class InventarioImpresoraController extends Controller
{
    public function index(Request $request)
    {
         abort_if(Gate::denies('inventarioimpre_index'), 403);

        $query = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores');

        // Apply filters
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
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
                case 'gerencia_id':
                    $query->where('gerencia_id', $secondFilter);
                    break;
                case 'area_id':
                    $query->where('area_id', $secondFilter);
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
                  ->orWhereHas('gerencias', function ($q5) use ($busqueda) {
                      $q5->where('gerencia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('areas', function ($q6) use ($busqueda) {
                      $q6->where('area', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('fabricantes', function ($q8) use ($busqueda) {
                      $q8->where('fabricante', 'like', "%{$busqueda}%");
                  });
            });
        }

        $inventario_impresoras = $query->paginate(5)->appends($request->all());

        // Calculate stats
        $totalMateriales = InventarioImpresora::sum('unidades');
        $totalConsumiblesRegistrados = InventarioImpresora::count();
        $porcentajeConsumibles = $totalMateriales > 0 ? round((InventarioImpresora::sum('unidades_disponibles') / $totalMateriales) * 100, 1) : 0;
        $consumiblesEsteMes = \App\Models\Impresora::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $stockBajo = InventarioImpresora::where('unidades_disponibles', '<', 5)->count();
        $totalImpresoras = Impresora::distinct('id')->count();

        // Get latest entry date (last date in d/m format)
        $ultimasFechas = InventarioImpresora::orderBy('fecha_ingreso', 'desc')->limit(1)->pluck('fecha_ingreso')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m');
        })->implode('');

        // Fetch filter options
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $areas = Area::all();
        $proveedores = Proveedore::all();
        $gerencias = Gerencia::with('area')->get();

        return view('administracion.inventario_impresoras.index', compact(
            'inventario_impresoras', 'totalMateriales', 'totalConsumiblesRegistrados', 'porcentajeConsumibles', 'consumiblesEsteMes', 'stockBajo', 'totalImpresoras', 'ultimasFechas',
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'proveedores', 'gerencias'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('inventarioimpre_create'), 403);

        // Get data for form
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $areas = Area::all();
        $proveedores = Proveedore::all();
        $gerencias = Gerencia::with('area')->get();

        // Get inventory records for dashboard
        $inventario_impresoras = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores')->paginate(10);

        // Calculate stats
        $totalMateriales = InventarioImpresora::sum('unidades');
        $totalConsumibles = Cartucho::distinct('id')->count();
        $stockBajo = InventarioImpresora::where('unidades_disponibles', '<', 5)->count();
        $totalImpresoras = Impresora::distinct('id')->count();

        return view('administracion.inventario_impresoras.create', compact(
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'proveedores', 'gerencias',
            'inventario_impresoras', 'totalMateriales', 'totalConsumibles', 'stockBajo', 'totalImpresoras'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_ingreso.*' =>'required',
            'cartucho_id.*' => 'required',
            'colore_id.*' => 'required',
            'impresora_id.*' => 'required',
            'fabricante_id.*' => 'required',
            'proveedore_id.*' => 'required',
            'area_id.*' => 'required',
            'unidades.*' => 'required',
            'gerencia_id.*' => 'required',
            'n_factura.*' => 'required',
        ]);

        $records = [];
        foreach ($request->fecha_ingreso as $index => $fecha) {
            $inventario_impresora = new InventarioImpresora;
            $inventario_impresora->fecha_ingreso = $fecha;
            $inventario_impresora->cartucho_id = $request->cartucho_id[$index];
            $inventario_impresora->colore_id = $request->colore_id[$index];
            $inventario_impresora->impresora_id = $request->impresora_id[$index];
            $inventario_impresora->fabricante_id = $request->fabricante_id[$index];
            $inventario_impresora->proveedore_id = $request->proveedore_id[$index];
            $inventario_impresora->gerencia_id = $request->gerencia_id[$index];
            $inventario_impresora->area_id = $request->area_id[$index];
            $inventario_impresora->unidades = $request->unidades[$index];
            $inventario_impresora->unidades_disponibles = $request->unidades[$index];
            $inventario_impresora->n_factura = $request->n_factura[$index];
            $inventario_impresora->save();
            $records[] = $inventario_impresora;
        }

        return redirect()->route('administracion.inventario_impresoras.index')->with('datos', 'Registros exitosos: ' . count($records));

    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(Gate::denies('inventarioimpre_edit'), 403);

        $inventario_impresora = InventarioImpresora::find($id);

        return view('administracion.inventario_impresoras.edit', ['inventario_impresora' =>
        $inventario_impresora,
        'cartuchos' => Cartucho::with('colore', 'impresora', 'fabricante')->get(),
        'colores' => Colore::all(),
        'impresoras' => Impresora::all(),
        'fabricantes' => Fabricante::all(),
        'areas' => Area::all(),
        'proveedores' => Proveedore::all(),
        'gerencias' => Gerencia::with('area')->get(),

    ]);
    }

    public function show()
    {
        //abort_if(Gate::denies('user_show'), 403);
        // $user = User::findOrFail($id);
        // dd($user);

        return view('administracion.inventario_impresoras.show', ['inventario_impresora' => InventarioImpresora::get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([

            'fecha_ingreso' =>'required',
            'cartucho_id' => 'required',
            'colore_id' => 'required',
            'impresora_id' => 'required',
            'fabricante_id' => 'required',
            'proveedore_id' => 'required',
            'gerencia_id' => 'required',
            'area_id' => 'required',
            'unidades' => 'required',
            'n_factura' => 'required',

        ]);
        $inventario_impresora =InventarioImpresora::find($id);
        //dd($inventario_impresora);
        $inventario_impresora->fecha_ingreso =$request->input('fecha_ingreso');
        $inventario_impresora->cartucho_id =$request->input('cartucho_id');
        $inventario_impresora->colore_id =$request->input('colore_id');
        $inventario_impresora->impresora_id =$request->input('impresora_id');
        $inventario_impresora->fabricante_id =$request->input('fabricante_id');
        $inventario_impresora->proveedore_id =$request->input('proveedore_id');
        $inventario_impresora->gerencia_id =$request->input('gerencia_id');
        $inventario_impresora->area_id =$request->input('area_id');
        $inventario_impresora->n_factura =$request->input('n_factura');
        $inventario_impresora->unidades =$request->input('unidades');
        $inventario_impresora->unidades_disponibles =$request->input('unidades');

        $inventario_impresora->save();

        return redirect()->route('administracion.inventario_impresoras.index', $inventario_impresora->id)->with('success', 'Excel actualizado correctamente');
    }



    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index'), 403);

        return Excel::download(new InventarioImpresoraExport($request), 'inventario_impresoras.xlsx');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('inventarioimpre_index'), 403);

        $query = InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'proveedores');

        // Apply the same filters as in index
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_ingreso', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
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
                case 'gerencia_id':
                    $query->where('gerencia_id', $secondFilter);
                    break;
                case 'area_id':
                    $query->where('area_id', $secondFilter);
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
        }

        $inventario_impresoras = $query->get();

        $pdf = \PDF::loadView('administracion.inventario_impresoras.pdf', compact('inventario_impresoras'));
        return $pdf->download('inventario_impresoras.pdf');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_if(Gate::denies('inventarioimpre_destroy'), 403);
        $inventario_impresora = InventarioImpresora::find($id);
        $inventario_impresora->delete();
        return redirect()->route('administracion.inventario_impresoras.index')->with('Fila eliminada');
    }
}
