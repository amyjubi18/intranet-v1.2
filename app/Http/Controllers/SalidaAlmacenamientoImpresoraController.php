<?php

namespace App\Http\Controllers;

use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalidaAlmacenamientoImpresoraExport;
use Illuminate\Support\Facades\DB;
use Exception;

class SalidaAlmacenamientoImpresoraController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $query = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora', 'distribucionAlmacenamientoImpresora')->orderBy('id', 'desc');

        // Filtros específicos
        // Filtro de fecha mejorado para fecha_salida - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('fecha_salida', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
        }

        // Filtro de fecha mejorado para fecha_ingreso del almacenamiento - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->fecha_ingreso_desde, $request->fecha_ingreso_hasta]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            // Solo fecha_ingreso_desde - filtrar desde esa fecha hasta ahora
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            // Solo fecha_ingreso_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
            });
        }

        // Filtros dinámicos como en distribucion_almacenamiento_impresoras
        if ($request->has('gerencia') && $request->gerencia) {
            $query->whereHas('distribucionAlmacenamientoImpresora', function ($q) use ($request) {
                $q->where('gerencia_id', $request->gerencia);
            });
        }

        if ($request->has('area') && $request->area) {
            $query->whereHas('distribucionAlmacenamientoImpresora.gerencia', function ($q) use ($request) {
                $q->where('area_id', $request->area);
            });
        }

        if ($request->has('cartucho') && $request->cartucho) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('cartuchos', function ($subQ) use ($request) {
                    $subQ->where('id', $request->cartucho);
                });
            });
        }

        if ($request->has('impresora') && $request->impresora) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->where('impresora_id', $request->impresora);
            });
        }

        if ($request->has('colore') && $request->colore) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('colores', function ($subQ) use ($request) {
                    $subQ->where('id', $request->colore);
                });
            });
        }

        if ($request->has('fabricante') && $request->fabricante) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('fabricantes', function ($subQ) use ($request) {
                    $subQ->where('id', $request->fabricante);
                });
            });
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('id', 'like', "%{$busqueda}%")
                  ->orWhere('fecha_salida', 'like', "%{$busqueda}%")
                  ->orWhere('cantidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('almacenamientoImpresora', function ($q2) use ($busqueda) {
                      $q2->where('fecha_ingreso', 'like', "%{$busqueda}%")
                         ->orWhereHas('cartuchos', function ($q3) use ($busqueda) {
                             $q3->where('cartucho', 'like', "%{$busqueda}%");
                         })
                         ->orWhereHas('colores', function ($q3) use ($busqueda) {
                             $q3->where('colore', 'like', "%{$busqueda}%");
                         })
                         ->orWhereHas('impresoras', function ($q3) use ($busqueda) {
                             $q3->where('impresora', 'like', "%{$busqueda}%");
                         })
                         ->orWhereHas('fabricantes', function ($q3) use ($busqueda) {
                             $q3->where('fabricante', 'like', "%{$busqueda}%");
                         });
                  })
                  ->orWhereHas('distribucionAlmacenamientoImpresora', function ($q2) use ($busqueda) {
                      $q2->whereHas('gerencia', function ($q3) use ($busqueda) {
                          $q3->where('gerencia', 'like', "%{$busqueda}%")
                             ->orWhereHas('area', function ($q4) use ($busqueda) {
                                 $q4->where('area', 'like', "%{$busqueda}%");
                             });
                      });
                  });
            });
        }

        $salida_almacenamiento_impresoras = $query->paginate(5)->appends($request->all());

        // Calculate stats
        $totalSalidas = SalidaAlmacenamientoImpresora::sum('cantidad');
        $totalSalidasRegistradas = SalidaAlmacenamientoImpresora::count();
        $salidasEsteMes = SalidaAlmacenamientoImpresora::whereMonth('fecha_salida', now()->month)->whereYear('fecha_salida', now()->year)->count();
        $totalItemsSalidos = SalidaAlmacenamientoImpresora::sum('cantidad');
        $salidasBajoStock = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos')
            ->join('almacenamiento_impresoras', 'salida_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->whereRaw('salida_almacenamiento_impresoras.historico_unidades_asignadas_restantes < cartuchos.stock_min')
            ->select('salida_almacenamiento_impresoras.*')
            ->get()
            ->unique('almacenamiento_impresora_id');

        $countSalidasBajoStock = count($salidasBajoStock);

        // Calculate unidades disponibles bajo stock_min threshold
        $unidadesDisponiblesBajoStock = AlmacenamientoImpresora::with('cartuchos')
            ->whereRaw('unidades_disponibles < (SELECT stock_min FROM cartuchos WHERE cartuchos.id = almacenamiento_impresoras.cartucho_id)')
            ->get();

        // Get latest entry date
        $ultimasFechasSalida = SalidaAlmacenamientoImpresora::orderBy('fecha_salida', 'desc')->limit(1)->pluck('fecha_salida')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        })->implode('');

        // Fetch filter options
        $cartuchos = \App\Models\Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = \App\Models\Colore::all();
        $impresoras = \App\Models\Impresora::all();
        $fabricantes = \App\Models\Fabricante::all();
        $gerencias = Gerencia::all();
        $areas = Area::all();

        return view('administracion.salida_almacen.index', compact(
            'salida_almacenamiento_impresoras', 'totalSalidas', 'totalSalidasRegistradas', 'salidasEsteMes', 'totalItemsSalidos', 'salidasBajoStock', 'ultimasFechasSalida',
            'cartuchos', 'colores', 'impresoras', 'fabricantes', 'gerencias', 'areas', 'unidadesDisponiblesBajoStock', 'countSalidasBajoStock'
        ));
    }

    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        return Excel::download(new SalidaAlmacenamientoImpresoraExport($request), 'salida_almacen.xlsx');
    }

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $query = SalidaAlmacenamientoImpresora::with('almacenamientoImpresora', 'distribucionAlmacenamientoImpresora');

        // Apply the same filters
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

        // Filtro de fecha mejorado para fecha_ingreso del almacenamiento - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->input('fecha_ingreso_desde'), $request->input('fecha_ingreso_hasta')]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            // Solo fecha_ingreso_desde - filtrar desde esa fecha hasta ahora
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->input('fecha_ingreso_desde'));
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            // Solo fecha_ingreso_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->input('fecha_ingreso_hasta'));
            });
        }
        // Filtros dinámicos como en distribucion_almacenamiento_impresoras
        if ($request->has('gerencia') && $request->gerencia) {
            $query->whereHas('distribucionAlmacenamientoImpresora', function ($q) use ($request) {
                $q->where('gerencia_id', $request->gerencia);
            });
        }

        if ($request->has('area') && $request->area) {
            $query->whereHas('distribucionAlmacenamientoImpresora.gerencia', function ($q) use ($request) {
                $q->where('area_id', $request->area);
            });
        }

        if ($request->has('cartucho') && $request->cartucho) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('cartuchos', function ($subQ) use ($request) {
                    $subQ->where('id', $request->cartucho);
                });
            });
        }

        if ($request->has('impresora') && $request->impresora) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->where('impresora_id', $request->impresora);
            });
        }

        if ($request->has('colore') && $request->colore) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('colores', function ($subQ) use ($request) {
                    $subQ->where('id', $request->colore);
                });
            });
        }

        if ($request->has('fabricante') && $request->fabricante) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereHas('fabricantes', function ($subQ) use ($request) {
                    $subQ->where('id', $request->fabricante);
                });
            });
        }

        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where(function ($q) use ($busqueda) {
                $q->where('cantidad', 'like', "%{$busqueda}%");
            });
        }

        $salida_almacenamiento_impresoras = $query->get();

        $pdf = \PDF::loadView('administracion.salida_almacen.pdf', compact('salida_almacenamiento_impresoras'))->setPaper('a4', 'landscape');
        return $pdf->download('salida_almacen.pdf');
    }

    public function create()
    {
        abort_if(Gate::denies('salidaimpre_create'), 403);

        return view('administracion.salida_almacen.create', [
            "almacenamiento_impresoras" => AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->whereRaw('(unidades - (SELECT COALESCE(SUM(cantidad), 0) FROM salida_almacenamiento_impresoras WHERE almacenamiento_impresora_id = almacenamiento_impresoras.id)) > 0')->get(),
            "distribuciones" => DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora', 'gerencia')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_salida' => 'required|date|before_or_equal:today',
            'almacenamiento_impresora_id' => 'required',
            'distribucion_almacenamiento_impresora_id' => 'required',
            'cantidad' => 'required|integer|min:1',
        ]);

        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($request->distribucion_almacenamiento_impresora_id);

        if (!$almacenamiento || !$distribucion) {
            return redirect()->back()->withErrors(['error' => 'Datos no encontrados']);
        }

        if ($distribucion->unidades_asignadas < $request->cantidad) {
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades asignadas']);
        }

        // Restar de unidades_asignadas en distribucion
        $distribucion->unidades_asignadas -= $request->cantidad;
        $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
        $distribucion->save();

        // Restar de unidades_disponibles en almacenamiento
        $almacenamiento->unidades_disponibles -= $request->cantidad;
        $almacenamiento->save();

        SalidaAlmacenamientoImpresora::create([
            'fecha_salida' => $request->fecha_salida,
            'almacenamiento_impresora_id' => $request->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $request->distribucion_almacenamiento_impresora_id,
            'cantidad' => $request->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles + $request->cantidad, // Antes de restar
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales,
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas,
            'ajuste_inventario' => $almacenamiento->unidades_disponibles + $request->cantidad - $request->cantidad,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Salida realizada exitosamente.');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('salidaimpre_edit'), 403);

        $salida = SalidaAlmacenamientoImpresora::find($id);

        return view('administracion.salida_almacen.edit', [
            'salida' => $salida,
            'almacenamiento_impresoras' => AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get(),
            'distribuciones' => DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora', 'gerencia')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_salida' => 'required|date|before_or_equal:today',
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'distribucion_almacenamiento_impresora_id' => 'required|exists:distribucion_almacenamiento_impresoras,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $salida = SalidaAlmacenamientoImpresora::find($id);
        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($request->distribucion_almacenamiento_impresora_id);

        if (!$almacenamiento || !$distribucion) {
            return redirect()->back()->withErrors(['error' => 'Datos no encontrados']);
        }

        // Restaurar unidades anteriores
        $distribucion->unidades_asignadas += $salida->cantidad;
        $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
        $almacenamiento->unidades_disponibles += $salida->cantidad;

        // Verificar si hay suficientes unidades
        if ($distribucion->unidades_asignadas < $request->cantidad || $almacenamiento->unidades_disponibles < $request->cantidad) {
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades disponibles']);
        }

        $salida->update([
            'fecha_salida' => $request->fecha_salida,
            'almacenamiento_impresora_id' => $request->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $request->distribucion_almacenamiento_impresora_id,
            'cantidad' => $request->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles - $request->cantidad,
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales,
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas,
            'ajuste_inventario' => $almacenamiento->unidades_disponibles - $request->cantidad,
            'user_id' => auth()->id(),
        ]);

        // Actualizar unidades
        $distribucion->unidades_asignadas -= $request->cantidad;
        $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
        $distribucion->save();
        $almacenamiento->unidades_disponibles -= $request->cantidad;
        $almacenamiento->save();

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $salida = SalidaAlmacenamientoImpresora::find($id);
        if (!$salida) {
            return redirect()->route('administracion.salida_almacen.index')->withErrors(['error' => 'Registro no encontrado']);
        }

        try {
            // Restaurar unidades
            $distribucion = DistribucionAlmacenamientoImpresora::find($salida->distribucion_almacenamiento_impresora_id);
            $almacenamiento = AlmacenamientoImpresora::find($salida->almacenamiento_impresora_id);
            if ($distribucion && $almacenamiento) {
                $distribucion->unidades_asignadas += $salida->cantidad;
                $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
                $almacenamiento->unidades_disponibles += $salida->cantidad;
                $distribucion->save();
                $almacenamiento->save();
            }

            $salida->delete();
            return redirect()->route('administracion.salida_almacen.index')->with('success', 'Registro eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('administracion.salida_almacen.index')->withErrors(['error' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }
}
