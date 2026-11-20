<?php

namespace App\Http\Controllers;

use App\Models\SalidaAlmacenamientoImpresora;
use App\Models\AlmacenamientoImpresora;
use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\Area;
use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalidaAlmacenamientoImpresoraExport;
use Illuminate\Support\Facades\DB;
use Exception;

class SalidaAlmacenamientoImpresoraController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $query = SalidaAlmacenamientoImpresora::with([
            'almacenamientoImpresora.impresoras',
            'almacenamientoImpresora.cartuchos',
            'almacenamientoImpresora.colores',
            'almacenamientoImpresora.fabricantes',
            'distribucionAlmacenamientoImpresora.gerencia.area',
            'users'
        ])->orderBy('id', 'desc');

        // Filtro de fecha para fecha_salida
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_salida', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
        }

        // Filtro de fecha para fecha_ingreso del almacenamiento
        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->fecha_ingreso_desde, $request->fecha_ingreso_hasta]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
            });
        }

        // Filtros específicos (soporta tanto nombre singular como _id)
        $gerenciaParam = $request->input('gerencia_id') ?: $request->input('gerencia');
        if ($gerenciaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora', function ($q) use ($gerenciaParam) {
                $q->where('gerencia_id', $gerenciaParam);
            });
        }

        $areaParam = $request->input('area_id') ?: $request->input('area');
        if ($areaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora.gerencia', function ($q) use ($areaParam) {
                $q->where('area_id', $areaParam);
            });
        }

        $cartuchoParam = $request->input('cartucho_id') ?: $request->input('cartucho');
        if ($cartuchoParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($cartuchoParam) {
                $q->where('cartucho_id', $cartuchoParam);
            });
        }

        $impresoraParam = $request->input('impresora_id') ?: $request->input('impresora');
        if ($impresoraParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($impresoraParam) {
                $q->where('impresora_id', $impresoraParam);
            });
        }

        $coloreParam = $request->input('colore_id') ?: $request->input('colore');
        if ($coloreParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($coloreParam) {
                $q->where('colore_id', $coloreParam);
            });
        }

        $fabricanteParam = $request->input('fabricante_id') ?: $request->input('fabricante');
        if ($fabricanteParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($fabricanteParam) {
                $q->where('fabricante_id', $fabricanteParam);
            });
        }

        if ($request->filled('busqueda')) {
            $busqueda = trim($request->input('busqueda'));
            $query->where(function ($q) use ($busqueda) {
                $q->where('id', 'like', "%{$busqueda}%")
                  ->orWhere('fecha_salida', 'like', "%{$busqueda}%")
                  ->orWhere('cantidad', 'like', "%{$busqueda}%")
                  ->orWhereHas('almacenamientoImpresora', function ($q2) use ($busqueda) {
                      $q2->where('fecha_ingreso', 'like', "%{$busqueda}%")
                         ->orWhere('n_factura', 'like', "%{$busqueda}%")
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

        $perPage = (int)$request->input('per_page', 10);
        if ($perPage <= 0 || $perPage > 100) $perPage = 10;
        $salida_almacenamiento_impresoras = $query->paginate($perPage)->appends($request->all());

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

        $unidadesDisponiblesBajoStock = AlmacenamientoImpresora::with('cartuchos')
            ->whereRaw('unidades_disponibles < (SELECT stock_min FROM cartuchos WHERE cartuchos.id = almacenamiento_impresoras.cartucho_id)')
            ->get();

        $ultimasFechasSalida = SalidaAlmacenamientoImpresora::orderBy('fecha_salida', 'desc')->limit(1)->pluck('fecha_salida')->map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        })->implode('');

        // Fetch options
        $cartuchos = Cartucho::with('colore', 'impresora', 'fabricante')->get();
        $colores = Colore::all();
        $impresoras = Impresora::all();
        $fabricantes = Fabricante::all();
        $gerencias = Gerencia::with('area')->get();
        $areas = Area::all();

        // Si es solicitud JSON / AJAX / React
        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            $almacenamientos = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')
                ->where('unidades_disponibles', '>', 0)
                ->get();

            $distribuciones = DistribucionAlmacenamientoImpresora::with([
                'almacenamientoImpresora.cartuchos',
                'almacenamientoImpresora.impresoras',
                'almacenamientoImpresora.colores',
                'gerencia.area'
            ])->where('unidades_asignadas', '>', 0)->get();

            return response()->json([
                'success' => true,
                'data' => $salida_almacenamiento_impresoras,
                'metrics' => [
                    'totalSalidas' => (int)$totalSalidas,
                    'totalSalidasRegistradas' => (int)$totalSalidasRegistradas,
                    'salidasEsteMes' => (int)$salidasEsteMes,
                    'totalItemsSalidos' => (int)$totalItemsSalidos,
                    'salidasBajoStock' => (int)$countSalidasBajoStock,
                    'ultimasFechasSalida' => $ultimasFechasSalida ?: 'N/A',
                ],
                'catalogs' => [
                    'cartuchos' => $cartuchos,
                    'colores' => $colores,
                    'impresoras' => $impresoras,
                    'fabricantes' => $fabricantes,
                    'gerencias' => $gerencias,
                    'areas' => $areas,
                    'almacenamientos' => $almacenamientos,
                    'distribuciones' => $distribuciones,
                ],
                'permissions' => [
                    'can_create' => Gate::allows('salidaimpre_create'),
                    'can_edit' => Gate::allows('salidaimpre_edit'),
                    'can_destroy' => Gate::allows('salidaimpre_index'),
                ]
            ]);
        }

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

        $query = SalidaAlmacenamientoImpresora::with([
            'almacenamientoImpresora.impresoras',
            'almacenamientoImpresora.cartuchos',
            'almacenamientoImpresora.colores',
            'almacenamientoImpresora.fabricantes',
            'distribucionAlmacenamientoImpresora.gerencia.area'
        ]);

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('id', array_filter($ids));
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        } elseif ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_salida', '>=', $request->input('fecha_desde'));
        } elseif ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->input('fecha_ingreso_desde'), $request->input('fecha_ingreso_hasta')]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->input('fecha_ingreso_desde'));
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->input('fecha_ingreso_hasta'));
            });
        }

        $gerenciaParam = $request->input('gerencia_id') ?: $request->input('gerencia');
        if ($gerenciaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora', function ($q) use ($gerenciaParam) {
                $q->where('gerencia_id', $gerenciaParam);
            });
        }

        $areaParam = $request->input('area_id') ?: $request->input('area');
        if ($areaParam) {
            $query->whereHas('distribucionAlmacenamientoImpresora.gerencia', function ($q) use ($areaParam) {
                $q->where('area_id', $areaParam);
            });
        }

        $cartuchoParam = $request->input('cartucho_id') ?: $request->input('cartucho');
        if ($cartuchoParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($cartuchoParam) {
                $q->where('cartucho_id', $cartuchoParam);
            });
        }

        $impresoraParam = $request->input('impresora_id') ?: $request->input('impresora');
        if ($impresoraParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($impresoraParam) {
                $q->where('impresora_id', $impresoraParam);
            });
        }

        $coloreParam = $request->input('colore_id') ?: $request->input('colore');
        if ($coloreParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($coloreParam) {
                $q->where('colore_id', $coloreParam);
            });
        }

        $fabricanteParam = $request->input('fabricante_id') ?: $request->input('fabricante');
        if ($fabricanteParam) {
            $query->whereHas('almacenamientoImpresora', function ($q) use ($fabricanteParam) {
                $q->where('fabricante_id', $fabricanteParam);
            });
        }

        if ($request->filled('busqueda')) {
            $busqueda = trim($request->input('busqueda'));
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
            'ajuste_inventario' => 'nullable|string',
        ]);

        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($request->distribucion_almacenamiento_impresora_id);

        if (!$almacenamiento || !$distribucion) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Consumible o distribución no encontrados en el almacén.'], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Datos no encontrados']);
        }

        if ($distribucion->unidades_asignadas < $request->cantidad) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => "No hay suficientes unidades asignadas. Disponibles para salida: {$distribucion->unidades_asignadas}."], 422);
            }
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades asignadas']);
        }

        // Restar de unidades_asignadas en distribucion
        $distribucion->unidades_asignadas -= $request->cantidad;
        $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
        $distribucion->save();

        // Restar de unidades_disponibles en almacenamiento
        $almacenamiento->unidades_disponibles -= $request->cantidad;
        $almacenamiento->save();

        $salida = SalidaAlmacenamientoImpresora::create([
            'fecha_salida' => $request->fecha_salida,
            'almacenamiento_impresora_id' => $request->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $request->distribucion_almacenamiento_impresora_id,
            'cantidad' => $request->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles + $request->cantidad,
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales ?? ($distribucion->unidades_asignadas + $request->cantidad),
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas,
            'ajuste_inventario' => $request->ajuste_inventario,
            'user_id' => auth()->id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salida de almacén registrada exitosamente.',
                'data' => $salida->load([
                    'almacenamientoImpresora.impresoras',
                    'almacenamientoImpresora.cartuchos',
                    'almacenamientoImpresora.colores',
                    'almacenamientoImpresora.fabricantes',
                    'distribucionAlmacenamientoImpresora.gerencia.area'
                ])
            ]);
        }

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
            'ajuste_inventario' => 'nullable|string',
        ]);

        $salida = SalidaAlmacenamientoImpresora::find($id);
        if (!$salida) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }
            return redirect()->route('administracion.salida_almacen.index')->withErrors(['error' => 'Registro no encontrado']);
        }

        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $distribucion = DistribucionAlmacenamientoImpresora::find($request->distribucion_almacenamiento_impresora_id);

        if (!$almacenamiento || !$distribucion) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Consumible o distribución no encontrados en el almacén.'], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Datos no encontrados']);
        }

        // Restaurar unidades anteriores
        $oldAlmacen = AlmacenamientoImpresora::find($salida->almacenamiento_impresora_id);
        $oldDist = DistribucionAlmacenamientoImpresora::find($salida->distribucion_almacenamiento_impresora_id);
        if ($oldDist) {
            $oldDist->unidades_asignadas += $salida->cantidad;
            $oldDist->unidades_restantes = $oldDist->unidades_asignadas;
            $oldDist->save();
        }
        if ($oldAlmacen) {
            $oldAlmacen->unidades_disponibles += $salida->cantidad;
            $oldAlmacen->save();
        }

        $distribucion->refresh();
        $almacenamiento->refresh();

        if ($distribucion->unidades_asignadas < $request->cantidad || $almacenamiento->unidades_disponibles < $request->cantidad) {
            // Revertir restauración
            if ($oldDist) {
                $oldDist->unidades_asignadas -= $salida->cantidad;
                $oldDist->unidades_restantes = $oldDist->unidades_asignadas;
                $oldDist->save();
            }
            if ($oldAlmacen) {
                $oldAlmacen->unidades_disponibles -= $salida->cantidad;
                $oldAlmacen->save();
            }
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No hay suficientes unidades disponibles para la cantidad solicitada.'], 422);
            }
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficientes unidades disponibles']);
        }

        $salida->update([
            'fecha_salida' => $request->fecha_salida,
            'almacenamiento_impresora_id' => $request->almacenamiento_impresora_id,
            'distribucion_almacenamiento_impresora_id' => $request->distribucion_almacenamiento_impresora_id,
            'cantidad' => $request->cantidad,
            'historico_unidades_disponibles' => $almacenamiento->unidades_disponibles - $request->cantidad,
            'historico_unidades_asignadas_originales' => $distribucion->unidades_asignadas_originales ?? $distribucion->unidades_asignadas,
            'historico_unidades_asignadas_restantes' => $distribucion->unidades_asignadas - $request->cantidad,
            'ajuste_inventario' => $request->ajuste_inventario,
            'user_id' => auth()->id(),
        ]);

        // Actualizar unidades con la nueva cantidad
        $distribucion->unidades_asignadas -= $request->cantidad;
        $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
        $distribucion->save();
        $almacenamiento->unidades_disponibles -= $request->cantidad;
        $almacenamiento->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salida de almacén actualizada exitosamente.',
                'data' => $salida->load([
                    'almacenamientoImpresora.impresoras',
                    'almacenamientoImpresora.cartuchos',
                    'almacenamientoImpresora.colores',
                    'almacenamientoImpresora.fabricantes',
                    'distribucionAlmacenamientoImpresora.gerencia.area'
                ])
            ]);
        }

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Registro actualizado correctamente');
    }

    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $salida = SalidaAlmacenamientoImpresora::find($id);
        if (!$salida) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado.'], 404);
            }
            return redirect()->route('administracion.salida_almacen.index')->withErrors(['error' => 'Registro no encontrado']);
        }

        try {
            $distribucion = DistribucionAlmacenamientoImpresora::find($salida->distribucion_almacenamiento_impresora_id);
            $almacenamiento = AlmacenamientoImpresora::find($salida->almacenamiento_impresora_id);
            if ($distribucion) {
                $distribucion->unidades_asignadas += $salida->cantidad;
                $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
                $distribucion->save();
            }
            if ($almacenamiento) {
                $almacenamiento->unidades_disponibles += $salida->cantidad;
                $almacenamiento->save();
            }

            $salida->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
            }

            return redirect()->route('administracion.salida_almacen.index')->with('success', 'Registro eliminado correctamente');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
            }
            return redirect()->route('administracion.salida_almacen.index')->withErrors(['error' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }

    public function destroyMultiple(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

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

        $salidas = SalidaAlmacenamientoImpresora::whereIn('id', $ids)->get();
        foreach ($salidas as $s) {
            $distribucion = DistribucionAlmacenamientoImpresora::find($s->distribucion_almacenamiento_impresora_id);
            $almacenamiento = AlmacenamientoImpresora::find($s->almacenamiento_impresora_id);
            if ($distribucion) {
                $distribucion->unidades_asignadas += $s->cantidad;
                $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
                $distribucion->save();
            }
            if ($almacenamiento) {
                $almacenamiento->unidades_disponibles += $s->cantidad;
                $almacenamiento->save();
            }
            $s->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => count($salidas) . ' registros eliminados correctamente.']);
        }

        return redirect()->route('administracion.salida_almacen.index')->with('success', count($salidas) . ' registros eliminados correctamente.');
    }

    public function destroyAll(Request $request)
    {
        abort_if(Gate::denies('salidaimpre_index'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'La contraseña ingresada es incorrecta.'], 422);
            }
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        $salidas = SalidaAlmacenamientoImpresora::all();
        foreach ($salidas as $s) {
            $distribucion = DistribucionAlmacenamientoImpresora::find($s->distribucion_almacenamiento_impresora_id);
            $almacenamiento = AlmacenamientoImpresora::find($s->almacenamiento_impresora_id);
            if ($distribucion) {
                $distribucion->unidades_asignadas += $s->cantidad;
                $distribucion->unidades_restantes = $distribucion->unidades_asignadas;
                $distribucion->save();
            }
            if ($almacenamiento) {
                $almacenamiento->unidades_disponibles += $s->cantidad;
                $almacenamiento->save();
            }
            $s->delete();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Todos los registros de salida de almacén han sido eliminados.']);
        }

        return redirect()->route('administracion.salida_almacen.index')->with('success', 'Todos los registros han sido eliminados.');
    }
}
