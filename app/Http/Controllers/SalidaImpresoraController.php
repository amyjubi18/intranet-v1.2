<?php

namespace App\Http\Controllers;

use App\Models\SalidaImpresora;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\Area;
use App\Models\Gerencia;
use App\Models\InventarioImpresora;
use App\Models\Proveedore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalidaImpresoraExport;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;

class SalidaImpresoraController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $query = SalidaImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'inventario_impresoras');

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('salida_impresoras.id', array_filter($ids));
        }

        // Filtro de fecha mejorado para fecha_salida - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('fecha_salida', '>=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('fecha_salida', '<=', $request->input('fecha_hasta'));
        }

        // Filtro de fecha mejorado para fecha_ingreso del inventario - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->input('fecha_ingreso_desde'), $request->input('fecha_ingreso_hasta')]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            // Solo fecha_ingreso_desde - filtrar desde esa fecha hasta ahora
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->input('fecha_ingreso_desde'));
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            // Solo fecha_ingreso_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->input('fecha_ingreso_hasta'));
            });
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

        if ($request->filled('cartucho_id')) {
            $query->where('cartucho_id', $request->cartucho_id);
        }
        if ($request->filled('colore_id')) {
            $query->where('colore_id', $request->colore_id);
        }
        if ($request->filled('impresora_id')) {
            $query->where('impresora_id', $request->impresora_id);
        }
        if ($request->filled('gerencia_id')) {
            $query->where('gerencia_id', $request->gerencia_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('fabricante_id')) {
            $query->where('fabricante_id', $request->fabricante_id);
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('cantidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
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

        $perPage = $request->get('per_page', 10);
        $salida_impresoras = $query->paginate($perPage)->appends($request->all());

        // Calculate stats
        $totalSalidas = SalidaImpresora::sum('cantidad');
        $totalSalidasRegistradas = SalidaImpresora::count();
        $salidasEsteMes = SalidaImpresora::whereMonth('fecha_salida', now()->month)->whereYear('fecha_salida', now()->year)->count();
        $totalItemsSalidos = SalidaImpresora::sum('cantidad');
        $salidasBajoStock = InventarioImpresora::where('unidades_disponibles', '<', 5)->count();

        // Get latest entry date (last date in d/m format)
        $ultimasFechasSalida = SalidaImpresora::orderBy('fecha_salida', 'desc')->limit(1)->pluck('fecha_salida')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m');
        })->implode('') ?: 'N/A';

        // Fetch filter options
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $areas = Area::all();
        $gerencias = Gerencia::with('area')->get();
        $inventario_impresoras = InventarioImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes', 'areas', 'gerencias')->where('unidades_disponibles', '>', 0)->get();

        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $salida_impresoras,
                'metrics' => [
                    'totalSalidas' => $totalSalidas,
                    'totalSalidasRegistradas' => $totalSalidasRegistradas,
                    'salidasEsteMes' => $salidasEsteMes,
                    'totalItemsSalidos' => $totalItemsSalidos,
                    'salidasBajoStock' => $salidasBajoStock,
                    'ultimasFechasSalida' => $ultimasFechasSalida,
                ],
                'catalogs' => [
                    'cartuchos' => $cartuchos,
                    'colores' => $colores,
                    'impresoras' => $impresoras,
                    'fabricantes' => $fabricantes,
                    'areas' => $areas,
                    'gerencias' => $gerencias,
                    'inventario_impresoras' => $inventario_impresoras,
                ],
                'permissions' => [
                    'can_create' => Gate::allows('salidaimpre_create'),
                    'can_edit' => Gate::allows('salidaimpre_edit'),
                    'can_destroy' => Gate::allows('salidaimpre_destroy'),
                ]
            ]);
        }

        return view('administracion.salida_impresora.index', compact(
            'salida_impresoras', 'totalSalidas', 'totalSalidasRegistradas', 'salidasEsteMes', 'totalItemsSalidos', 'salidasBajoStock', 'ultimasFechasSalida',
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias'
        ));
    }

    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        return Excel::download(new SalidaImpresoraExport($request), 'salida_impresoras.xlsx');
    }

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $query = SalidaImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias', 'inventario_impresoras');

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('salida_impresoras.id', array_filter($ids));
        }

        // Filtro de fecha mejorado para fecha_salida - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('fecha_salida', '>=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('fecha_salida', '<=', $request->input('fecha_hasta'));
        }

        // Filtro de fecha mejorado para fecha_ingreso del inventario - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->input('fecha_ingreso_desde'), $request->input('fecha_ingreso_hasta')]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            // Solo fecha_ingreso_desde - filtrar desde esa fecha hasta ahora
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->input('fecha_ingreso_desde'));
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            // Solo fecha_ingreso_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereHas('inventario_impresoras', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->input('fecha_ingreso_hasta'));
            });
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

        if ($request->filled('cartucho_id')) {
            $query->where('cartucho_id', $request->cartucho_id);
        }
        if ($request->filled('colore_id')) {
            $query->where('colore_id', $request->colore_id);
        }
        if ($request->filled('impresora_id')) {
            $query->where('impresora_id', $request->impresora_id);
        }
        if ($request->filled('gerencia_id')) {
            $query->where('gerencia_id', $request->gerencia_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('fabricante_id')) {
            $query->where('fabricante_id', $request->fabricante_id);
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('cantidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('cartuchos', function ($q2) use ($busqueda) {
                      $q2->where('cartucho', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                      $q3->where('impresora', 'like', "%{$busqueda}%");
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

        $salida_impresoras = $query->get();

        $pdf = \PDF::loadView('administracion.salida_impresora.pdf', compact('salida_impresoras'));
        return $pdf->download('salida_impresoras.pdf');
    }
    public function create()
    {
        abort_if(Gate::denies('salidaimpre_create'), 403);

        return view('administracion.salida_impresora.create', [
            "inventario_impresoras" => InventarioImpresora::with('impresoras', 'cartuchos', 'colores', 'fabricantes', 'areas', 'gerencias')->where('unidades_disponibles', '>', 0)->get(),
            "cartuchos" => Cartucho::with('colore', 'impresora', 'fabricante')->get(),
            "colores" => Colore::all(),
            "impresoras" => Impresora::all(),
            "fabricantes" => Fabricante::all(),
            "areas" => Area::all(),
            "gerencias" => Gerencia::with('area')->get(),
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'fecha_salida' => 'required',
            'inventario_impresora_id' => 'required',
            'cantidad' => 'required',
        ]);

        $inventario = InventarioImpresora::find($request->inventario_impresora_id);

        if (!$inventario) {
            return redirect()->back()->withErrors(['inventario_impresora_id' => 'Inventario no encontrado']);
        }

        if ($inventario->unidades_disponibles < $request->cantidad) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No hay suficientes unidades disponibles.'], 422);
            }
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades disponibles']);
        }

        $salida_impresora = new SalidaImpresora;
        $salida_impresora->fecha_salida = $request->fecha_salida;
        $salida_impresora->inventario_impresora_id = $request->inventario_impresora_id;
        $salida_impresora->cartucho_id = $inventario->cartucho_id;
        $salida_impresora->colore_id = $inventario->colore_id;
        $salida_impresora->impresora_id = $inventario->impresora_id;
        $salida_impresora->fabricante_id = $inventario->fabricante_id;
        $salida_impresora->gerencia_id = $inventario->gerencia_id;
        $salida_impresora->area_id = $inventario->area_id;
        $salida_impresora->cantidad = $request->cantidad;
        $salida_impresora->historico_unidades_disponibles = $inventario->unidades_disponibles - $request->cantidad;
        $salida_impresora->ajuste_inventario = $inventario->unidades_disponibles - $request->cantidad;
        $salida_impresora->save();

        // Actualizar unidades disponibles en inventario
        $inventario->unidades_disponibles -= $request->cantidad;
        $inventario->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Salida de consumible registrada exitosamente.', 'data' => $salida_impresora]);
        }

        return redirect()->route('administracion.salida_impresora.index')->with('datos', 'Registro exitoso');
    }
    public function edit($id)
    {
        abort_if(Gate::denies('salidaimpre_edit'), 403);

        $salida_impresora = SalidaImpresora::find($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $salida_impresora
            ]);
        }

        return view('administracion.salida_impresora.edit', [
            'salida_impresora' => $salida_impresora,
            'inventario_impresoras' => InventarioImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes', 'areas', 'gerencias')->get(),
            'cartuchos' => Cartucho::with('colore', 'impresora', 'fabricante')->get(),
            'colores' => Colore::all(),
            'impresoras' => Impresora::all(),
            'fabricantes' => Fabricante::all(),
            'areas' => Area::all(),
            'gerencias' => Gerencia::with('area')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_salida' => 'required',
            'inventario_impresora_id' => 'required',
            'cantidad' => 'required|numeric|min:1',
        ]);

        $salida_impresora = SalidaImpresora::find($id);
        if (!$salida_impresora) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }
            return redirect()->back()->withErrors(['id' => 'Registro no encontrado']);
        }

        $inventario = InventarioImpresora::find($request->inventario_impresora_id);
        if (!$inventario) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Inventario no encontrado.'], 404);
            }
            return redirect()->back()->withErrors(['inventario_impresora_id' => 'Inventario no encontrado']);
        }

        // Restaurar unidades anteriores
        $inventario->unidades_disponibles += $salida_impresora->cantidad;

        // Verificar si hay suficientes unidades después del cambio
        if ($inventario->unidades_disponibles < $request->cantidad) {
            $inventario->unidades_disponibles -= $salida_impresora->cantidad; // revertir
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No hay suficientes unidades disponibles.'], 422);
            }
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades disponibles']);
        }

        $salida_impresora->fecha_salida = $request->fecha_salida;
        $salida_impresora->inventario_impresora_id = $request->inventario_impresora_id;
        $salida_impresora->cartucho_id = $inventario->cartucho_id;
        $salida_impresora->colore_id = $inventario->colore_id;
        $salida_impresora->impresora_id = $inventario->impresora_id;
        $salida_impresora->fabricante_id = $inventario->fabricante_id;
        $salida_impresora->gerencia_id = $inventario->gerencia_id;
        $salida_impresora->area_id = $inventario->area_id;
        $salida_impresora->cantidad = $request->cantidad;
        $salida_impresora->historico_unidades_disponibles = $inventario->unidades_disponibles - $request->cantidad;
        $salida_impresora->ajuste_inventario = $inventario->unidades_disponibles - $request->cantidad;
        $salida_impresora->save();

        // Actualizar unidades disponibles en inventario
        $inventario->unidades_disponibles -= $request->cantidad;
        $inventario->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Salida de consumible actualizada correctamente.', 'data' => $salida_impresora]);
        }

        return redirect()->route('administracion.salida_impresora.index')->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('salidaimpre_destroy'), 403);

        $salida_impresora = SalidaImpresora::find($id);
        if (!$salida_impresora) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }
            return redirect()->route('administracion.salida_impresora.index')->with('error', 'Registro no encontrado');
        }

        // Restaurar unidades al inventario
        $inventario = InventarioImpresora::find($salida_impresora->inventario_impresora_id);
        if ($inventario) {
            $inventario->unidades_disponibles += $salida_impresora->cantidad;
            $inventario->save();
        }

        $salida_impresora->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
        }

        return redirect()->route('administracion.salida_impresora.index')->with('success', 'Registro eliminado correctamente');
    }

    public function destroyMultiple(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_destroy'), 403);

        $request->validate([
            'ids' => 'required',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'La contraseña ingresada es incorrecta.'], 422);
            }
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);
        if (!is_array($ids) || count($ids) === 0) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No se seleccionaron registros para eliminar.'], 422);
            }
            return redirect()->back()->with('error', 'No se seleccionaron registros para eliminar.');
        }

        $salidas = SalidaImpresora::whereIn('id', $ids)->get();
        foreach ($salidas as $s) {
            $inv = InventarioImpresora::find($s->inventario_impresora_id);
            if ($inv) {
                $inv->unidades_disponibles += $s->cantidad;
                $inv->save();
            }
            $s->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => count($salidas) . ' registros eliminados correctamente.']);
        }

        return redirect()->route('administracion.salida_impresora.index')->with('success', count($salidas) . ' registros eliminados correctamente.');
    }

    public function destroyAll(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_destroy'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'La contraseña ingresada es incorrecta.'], 422);
            }
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        $salidas = SalidaImpresora::all();
        foreach ($salidas as $s) {
            $inv = InventarioImpresora::find($s->inventario_impresora_id);
            if ($inv) {
                $inv->unidades_disponibles += $s->cantidad;
                $inv->save();
            }
            $s->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Todos los registros de salida fueron eliminados.']);
        }

        return redirect()->route('administracion.salida_impresora.index')->with('success', 'Todos los registros de salida fueron eliminados.');
    }
}
