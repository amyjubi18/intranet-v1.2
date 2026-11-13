<?php

namespace App\Http\Controllers;
use App\Models\SalidaOficina;
use App\Models\Articulo;
use App\Models\Area;
use App\Models\Gerencia;
use App\Models\InventarioOficina;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;
use Illuminate\Support\Facades\Hash;

class SalidaOficController extends Controller
{
    public function index(Request $request)
{
    abort_if(Gate::denies('salidaofic_index'), 403);

    $query = SalidaOficina::with(['articulos', 'gerencias', 'areas', 'unidad_medidas', 'users'])
        ->leftJoin('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
        ->select('salida_oficinas.*', 'inventario_oficinas.unidades_disponibles', 'inventario_oficinas.unidades as unidades_totales')
        ->orderBy('salida_oficinas.id', 'desc');

    // 1. Filtros de Fecha de Salida
    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
        $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
    } elseif ($request->filled('fecha_desde')) {
        $query->whereDate('fecha_salida', '>=', $request->fecha_desde);
    } elseif ($request->filled('fecha_hasta')) {
        $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
    }

    // 2. Filtros de Fecha de Ingreso
    if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
        $query->whereHas('inventario_oficinas', function($q) use ($request) {
            $q->whereBetween('fecha_ingreso', [$request->fecha_ingreso_desde, $request->fecha_ingreso_hasta]);
        });
    } elseif ($request->filled('fecha_ingreso_desde')) {
        $query->whereHas('inventario_oficinas', function($q) use ($request) {
            $q->whereDate('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
        });
    } elseif ($request->filled('fecha_ingreso_hasta')) {
        $query->whereHas('inventario_oficinas', function($q) use ($request) {
            $q->whereDate('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
        });
    }

    // 3. Filtro por Gerencia
    $gerenciaId = $request->input('gerencia', $request->filterType === 'gerencia' ? $request->secondFilter : null);
    if ($gerenciaId) {
        $gerencia = Gerencia::with('area')->find($gerenciaId);
        if ($gerencia) {
            $areaIds = $gerencia->area ? [$gerencia->area->id] : [];
            $query->where(function($q) use ($gerenciaId, $areaIds) {
                $q->where('salida_oficinas.gerencia_id', $gerenciaId)
                  ->orWhereIn('salida_oficinas.area_id', $areaIds);
            });
        } else {
            $query->where('salida_oficinas.gerencia_id', $gerenciaId);
        }
    }

    // 4. Filtro por Área
    $areaId = $request->input('area', $request->filterType === 'area' ? $request->secondFilter : null);
    if ($areaId) {
        $query->where('salida_oficinas.area_id', $areaId);
    }

    // 5. Filtro por Artículo
    $articuloId = $request->input('articulo', $request->filterType === 'articulo' ? $request->secondFilter : null);
    if ($articuloId) {
        $query->where('salida_oficinas.articulo_id', $articuloId);
    }

    // 6. Búsqueda por Texto
    if ($request->filled('busqueda')) {
        $search = $request->busqueda;
        $query->where(function($q) use ($search) {
            $q->where('salida_oficinas.cantidad', 'like', '%' . $search . '%')
              ->orWhere('salida_oficinas.ajuste_inventario', 'like', '%' . $search . '%')
              ->orWhereHas('articulos', fn($subQ) => $subQ->where('articulo', 'like', '%' . $search . '%'))
              ->orWhereHas('gerencias', fn($subQ) => $subQ->where('gerencia', 'like', '%' . $search . '%'))
              ->orWhereHas('areas', fn($subQ) => $subQ->where('area', 'like', '%' . $search . '%'))
              ->orWhereHas('users', fn($subQ) => $subQ->where('name', 'like', '%' . $search . '%'));
        });
    }

    // Paginación manteniendo los parámetros en la URL
    $salida_oficinas = $query->paginate(10)->withQueryString();
    $salidasAgrupadas = $salida_oficinas->getCollection()->groupBy('articulo_id');

    $gerencias = Gerencia::with('area')->get();
    $areas = Area::all();
    $articulos = Articulo::all();

    $stockPorArticulo = [];
    $sumaUnidadesPorArticulo = [];
    foreach ($articulos as $articulo) {
        $stockPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)->sum('unidades_disponibles');
        $sumaUnidadesPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)->sum('unidades');
    }

    $ultimasFechasSalida = SalidaOficina::orderBy('fecha_salida', 'desc')->first();
    $ultimasFechasSalida = $ultimasFechasSalida ? \Carbon\Carbon::parse($ultimasFechasSalida->fecha_salida)->format('d/m/Y') : 'N/A';
    $totalSalidas = SalidaOficina::count();
    $salidasEsteMes = SalidaOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

    $salidasBajoStock = SalidaOficina::with('articulos')
        ->whereHas('articulos', function($q) {
            $q->whereRaw('salida_oficinas.cantidad < articulos.stock_min');
        })
        ->take(5)
        ->get();
    $countSalidasBajoStock = $salidasBajoStock->count();

    $inventarioFiltrado = null;
    if ($articuloId) {
        $inventarioFiltrado = InventarioOficina::with(['articulos', 'unidad_medidas', 'ubicacion', 'proveedores'])
            ->where('articulo_id', $articuloId)
            ->orderBy('fecha_ingreso', 'desc')
            ->get();
    }

    return view('administracion.salida_oficina.index', compact(
        'salida_oficinas', 'salidasAgrupadas', 'stockPorArticulo',
        'sumaUnidadesPorArticulo', 'inventarioFiltrado', 'ultimasFechasSalida',
        'totalSalidas', 'salidasEsteMes', 'salidasBajoStock',
        'countSalidasBajoStock', 'gerencias', 'areas', 'articulos'
    ));
}

    public function create()
    {
        abort_if(Gate::denies('salidaofic_create'), 403);

        return view('administracion.salida_oficina.create', [
            "inventario_oficinas" => InventarioOficina::all(),
            "articulos" => Articulo::all(),
            "unidad_medidas" => UnidadMedida::all(),
            "areas" => Area::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_salida' =>'required|date|before_or_equal:today',
            'inventario_oficina_id' => 'required',
            'unidad_medida_id' => 'required',
            'articulo_id' => 'required',
            'cantidad' => 'required|integer|min:1',
            'area_id' => 'required',
        ]);

        $salida_oficina = new SalidaOficina;
        $salida_oficina->fecha_salida = $request->fecha_salida;
        $salida_oficina->inventario_oficina_id = $request->inventario_oficina_id;
        $salida_oficina->cantidad = $request->cantidad;
        $salida_oficina->unidad_medida_id = $request->unidad_medida_id;
        $salida_oficina->articulo_id = $request->articulo_id;
        $salida_oficina->area_id = $request->area_id;
        $salida_oficina->user_id = auth()->id();
        $salida_oficina->save();

        return redirect()->route('administracion.salida_oficina.index')->with('datos', 'Registro exitoso');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('salidaofic_edit'), 403);

        $salida_oficina = SalidaOficina::find($id);

        return view('administracion.salida_oficina.edit', [
            'salida_oficina' => $salida_oficina,
            'inventario_oficinas' => InventarioOficina::all(),
            'articulos' => Articulo::all(),
            'unidad_medidas' => UnidadMedida::all(),
            'areas' => Area::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha_salida' =>'required|date|before_or_equal:today',
            'inventario_oficina_id' => 'required',
            'unidad_medida_id' => 'required',
            'articulo_id' => 'required',
            'cantidad' => 'required|integer|min:1',
            'area_id' => 'required',
        ]);

        $salida_oficina = SalidaOficina::find($id);
        $salida_oficina->fecha_salida = $request->input('fecha_salida');
        $salida_oficina->inventario_oficina_id = $request->input('inventario_oficina_id');
        $salida_oficina->unidad_medida_id = $request->input('unidad_medida_id');
        $salida_oficina->articulo_id = $request->input('articulo_id');
        $salida_oficina->cantidad = $request->input('cantidad');
        $salida_oficina->area_id = $request->input('area_id');
        $salida_oficina->save();

        return redirect()->route('administracion.salida_oficina.index', $salida_oficina->id)->with('success', 'Registro actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        try {
            DB::transaction(function () use ($id) {
                $salida_oficina = SalidaOficina::findOrFail($id);

                if ($salida_oficina->inventario_oficina_id && $salida_oficina->cantidad) {
                    $inventario = InventarioOficina::find($salida_oficina->inventario_oficina_id);
                    if ($inventario) {
                        $inventario->unidades_disponibles += $salida_oficina->cantidad;
                        $inventario->save();
                    }
                }

                $salida_oficina->delete();
            });

            return redirect()->route('administracion.salida_oficina.index')
                ->with('datos', 'Registro eliminado correctamente. Las unidades han sido restauradas al inventario.');
        } catch (\Exception $e) {
            return redirect()->route('administracion.salida_oficina.index')
                ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }

    public function destroyMultiple(Request $request)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

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
                foreach ($ids as $id) {
                    $salida_oficina = SalidaOficina::findOrFail($id);

                    if ($salida_oficina->inventario_oficina_id && $salida_oficina->cantidad) {
                        $inventario = InventarioOficina::find($salida_oficina->inventario_oficina_id);
                        if ($inventario) {
                            $inventario->unidades_disponibles += $salida_oficina->cantidad;
                            $inventario->save();
                        }
                    }

                    $salida_oficina->delete();
                }
            });

            return redirect()->route('administracion.salida_oficina.index')
                ->with('datos', "Se eliminaron {$count} registro(s) correctamente. Las unidades han sido restauradas al inventario.");
        } catch (\Exception $e) {
            return redirect()->route('administracion.salida_oficina.index')
                ->with('error', 'Error al eliminar los registros: ' . $e->getMessage());
        }
    }

    public function destroyAll(Request $request)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        try {
            DB::transaction(function () {
                $allSalidas = SalidaOficina::all();

                foreach ($allSalidas as $salida_oficina) {
                    if ($salida_oficina->inventario_oficina_id && $salida_oficina->cantidad) {
                        $inventario = InventarioOficina::find($salida_oficina->inventario_oficina_id);
                        if ($inventario) {
                            $inventario->unidades_disponibles += $salida_oficina->cantidad;
                            $inventario->save();
                        }
                    }
                }

                SalidaOficina::query()->delete();
            });

            return redirect()->route('administracion.salida_oficina.index')
                ->with('datos', "Se eliminaron todos los registros correctamente. Las unidades han sido restauradas al inventario.");
        } catch (\Exception $e) {
            return redirect()->route('administracion.salida_oficina.index')
                ->with('error', 'Error al eliminar los registros: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        $filters = $request->all();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalidaOficinaExport($filters),
            'salida_oficina.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        $query = SalidaOficina::with(['articulos', 'gerencias', 'areas', 'unidad_medidas', 'users'])
            ->leftJoin('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->select('salida_oficinas.*', 'inventario_oficinas.unidades_disponibles');

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('salida_oficinas.id', array_filter($ids));
        }

        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_salida', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
        }

        if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('inventario_oficinas', function($q) use ($request) {
                $q->whereBetween('fecha_ingreso', [$request->fecha_ingreso_desde, $request->fecha_ingreso_hasta]);
            });
        } elseif ($request->filled('fecha_ingreso_desde')) {
            $query->whereHas('inventario_oficinas', function($q) use ($request) {
                $q->whereDate('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
            });
        } elseif ($request->filled('fecha_ingreso_hasta')) {
            $query->whereHas('inventario_oficinas', function($q) use ($request) {
                $q->whereDate('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
            });
        }

        if ($request->filled('filterType')) {
            switch ($request->filterType) {
                case 'fecha':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('fecha_salida', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('fecha_salida', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'fecha_ingreso':
                    if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
                        $query->whereHas('inventario_oficinas', function($q) use ($request) {
                            $q->whereBetween('fecha_ingreso', [$request->fecha_ingreso_desde, $request->fecha_ingreso_hasta]);
                        });
                    } elseif ($request->filled('fecha_ingreso_desde')) {
                        $query->whereHas('inventario_oficinas', function($q) use ($request) {
                            $q->where('fecha_ingreso', '>=', $request->fecha_ingreso_desde);
                        });
                    } elseif ($request->filled('fecha_ingreso_hasta')) {
                        $query->whereHas('inventario_oficinas', function($q) use ($request) {
                            $q->where('fecha_ingreso', '<=', $request->fecha_ingreso_hasta);
                        });
                    }
                    break;
                case 'gerencia':
                    if ($request->filled('secondFilter')) {
                        $query->where('gerencia_id', $request->secondFilter);
                    }
                    break;
                case 'area':
                    if ($request->filled('secondFilter')) {
                        $query->where('area_id', $request->secondFilter);
                    }
                    break;
                case 'articulo':
                    if ($request->filled('secondFilter')) {
                        $query->where('salida_oficinas.articulo_id', $request->secondFilter);
                    }
                    break;
                case 'busqueda':
                    if ($request->filled('busqueda')) {
                        $search = $request->busqueda;
                        $query->where(function($q) use ($search) {
                            $q->where('cantidad', 'like', '%' . $search . '%')
                              ->orWhere('ajuste_inventario', 'like', '%' . $search . '%')
                              ->orWhereHas('articulos', function($subQ) use ($search) {
                                  $subQ->where('articulo', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('gerencias', function($subQ) use ($search) {
                                  $subQ->where('gerencia', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('areas', function($subQ) use ($search) {
                                  $subQ->where('area', 'like', '%' . $search . '%');
                              })
                              ->orWhereHas('users', function($subQ) use ($search) {
                                  $subQ->where('name', 'like', '%' . $search . '%');
                              });
                        });
                    }
                    break;
            }
        }

        $salida_oficinas = $query->orderBy('salida_oficinas.id', 'desc')->get();

        $totalRegistros = $salida_oficinas->count();
        $totalUnidades = $salida_oficinas->sum('cantidad');

        $sumaUnidadesPorArticulo = [];
        $articulos = Articulo::all();
        foreach ($articulos as $articulo) {
            $sumaUnidadesPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)
                ->sum('unidades');
        }

        $salida_oficinas->each(function ($item) use ($sumaUnidadesPorArticulo) {
            $item->stockHistorico = $item->historico_unidades_disponibles ?? 0;
            $item->stockRestante = max(0, $item->stockHistorico - $item->cantidad);
        });

        $filtersApplied = $request->filled('filterType');
        $filterDescription = '';
        if ($filtersApplied) {
            $filterParts = [];
            switch ($request->filterType) {
                case 'fecha':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $filterParts[] = 'Fecha: ' . $request->fecha_desde . ' al ' . $request->fecha_hasta;
                    }
                    break;
                case 'fecha_ingreso':
                    if ($request->filled('fecha_ingreso_desde') && $request->filled('fecha_ingreso_hasta')) {
                        $filterParts[] = 'Fecha Ingreso: ' . $request->fecha_ingreso_desde . ' al ' . $request->fecha_ingreso_hasta;
                    }
                    break;
                case 'gerencia':
                    if ($request->filled('secondFilter')) {
                        $gerencia = Gerencia::find($request->secondFilter);
                        $filterParts[] = 'Gerencia: ' . ($gerencia ? $gerencia->gerencia : 'N/A');
                    }
                    break;
                case 'area':
                    if ($request->filled('secondFilter')) {
                        $area = Area::find($request->secondFilter);
                        $filterParts[] = 'Área: ' . ($area ? $area->area : 'N/A');
                    }
                    break;
                case 'articulo':
                    if ($request->filled('secondFilter')) {
                        $articulo = Articulo::find($request->secondFilter);
                        $filterParts[] = 'Artículo: ' . ($articulo ? $articulo->articulo : 'N/A');
                    }
                    break;
                case 'busqueda':
                    if ($request->filled('busqueda')) {
                        $filterParts[] = 'Búsqueda: ' . $request->busqueda;
                    }
                    break;
            }
            $filterDescription = implode(', ', $filterParts);
        }

        $pdf = \PDF::loadView('administracion.salida_oficina.pdf', compact(
            'salida_oficinas',
            'totalRegistros',
            'totalUnidades',
            'filtersApplied',
            'filterDescription',
            'sumaUnidadesPorArticulo'
        ));

        return $pdf->download('salida_oficina.pdf');
    }
}
