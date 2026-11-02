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

class SalidaOficController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        // Query base con relaciones - JOIN para obtener unidades_disponibles directamente
        $query = SalidaOficina::with(['articulos', 'gerencias', 'areas', 'unidad_medidas', 'users'])
            ->leftJoin('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->select('salida_oficinas.*', 'inventario_oficinas.unidades_disponibles', 'inventario_oficinas.unidades as unidades_totales')
            ->orderBy('salida_oficinas.id', 'desc');

        // Aplicar filtros según el tipo seleccionado
        if ($request->filled('filterType')) {
            switch ($request->filterType) {
                case 'fecha':
                    // Filtro por fecha de salida
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('fecha_salida', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('fecha_salida', '<=', $request->fecha_hasta);
                    }
                    break;

                case 'fecha_ingreso':
                    // Filtro por fecha de ingreso (desde inventario_oficinas)
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
                    // Filtro por gerencia Y sus áreas relacionadas
                    if ($request->filled('secondFilter')) {
                        // Obtener la gerencia seleccionada con sus áreas
                        $gerencia = Gerencia::with('area')->find($request->secondFilter);

                        if ($gerencia) {
                            // Obtener los IDs de las áreas relacionadas a esta gerencia
                            $areaIds = $gerencia->area ? [$gerencia->area->id] : [];

                            // Filtrar por gerencia_id O por las áreas relacionadas
                            $query->where(function($q) use ($request, $areaIds) {
                                $q->where('gerencia_id', $request->secondFilter)
                                  ->orWhereIn('area_id', $areaIds);
                            });
                        } else {
                            $query->where('gerencia_id', $request->secondFilter);
                        }
                    }
                    break;

                case 'area':
                    // Filtro por área
                    if ($request->filled('secondFilter')) {
                        $query->where('area_id', $request->secondFilter);
                    }
                    break;

                case 'articulo':
                    // Filtro por artículo
                    if ($request->filled('secondFilter')) {
                        $query->where('salida_oficinas.articulo_id', $request->secondFilter);
                    }
                    break;

                case 'busqueda':
                    // Búsqueda general en múltiples campos
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

        // Paginar resultados
        $salida_oficinas = $query->paginate(10);

        // Agrupar resultados por artículo para la vista (manteniendo el orden descendente)
        $salidasAgrupadas = $salida_oficinas->getCollection()->groupBy('articulo_id');

        // Obtener datos para filtros
        $gerencias = Gerencia::with('area')->get();
        $areas = Area::all();
        $articulos = Articulo::all();

        // Calcular sumatoria de unidades_disponibles del inventario para cada artículo
        // Esto obtiene el stock total real disponible para cada artículo
        $stockPorArticulo = [];
        // Calcular sumatoria de unidades (columna unidades, no unidades_disponibles) para cada artículo
        $sumaUnidadesPorArticulo = [];
        foreach ($articulos as $articulo) {
            $stockPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)
                ->sum('unidades_disponibles');
            $sumaUnidadesPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)
                ->sum('unidades');
        }

        // Estadísticas para las tarjetas
        $ultimasFechasSalida = SalidaOficina::orderBy('fecha_salida', 'desc')->first();
        $ultimasFechasSalida = $ultimasFechasSalida ? \Carbon\Carbon::parse($ultimasFechasSalida->fecha_salida)->format('d/m/Y') : 'N/A';

        $totalSalidas = SalidaOficina::count();
        $salidasEsteMes = SalidaOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Items bajo stock (cantidad retirada > stock_min del artículo)
        $salidasBajoStock = SalidaOficina::with('articulos')
            ->whereHas('articulos', function($q) {
                $q->whereRaw('salida_oficinas.cantidad < articulos.stock_min');
            })
            ->take(5)
            ->get();
        $countSalidasBajoStock = $salidasBajoStock->count();

        // Obtener registros de inventario_oficinas para el artículo seleccionado
        $inventarioFiltrado = null;
        if ($request->filled('filterType') && $request->filterType === 'articulo' && $request->filled('secondFilter')) {
            $inventarioFiltrado = InventarioOficina::with(['articulos', 'unidad_medidas', 'ubicacion', 'proveedores'])
                ->where('articulo_id', $request->secondFilter)
                ->orderBy('fecha_ingreso', 'desc')
                ->get();
        }

        return view('administracion.salida_oficina.index', [
            'salida_oficinas' => $salida_oficinas,
            'salidasAgrupadas' => $salidasAgrupadas,
            'stockPorArticulo' => $stockPorArticulo,
            'sumaUnidadesPorArticulo' => $sumaUnidadesPorArticulo,
            'inventarioFiltrado' => $inventarioFiltrado,
            'ultimasFechasSalida' => $ultimasFechasSalida,
            'totalSalidas' => $totalSalidas,
            'salidasEsteMes' => $salidasEsteMes,
            'salidasBajoStock' => $salidasBajoStock,
            'countSalidasBajoStock' => $countSalidasBajoStock,
            'gerencias' => $gerencias,
            'areas' => $areas,
            'articulos' => $articulos,
        ]);
    }
    public function create()
    {
     abort_if(Gate::denies('salidaofic_create'), 403);

        return view('administracion.salida_oficina.create', [
            "inventario_oficinas" => InventarioOficina::all(),
            "articulos" => Articulo::all(),
            "unidad_medidas" => UnidadMedida::all(),
            "areas" => Area::all(),
            /*  "users" => User::all(),  */
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
        //dd($documento);
        abort_if(Gate::denies('salidaofic_edit'), 403);

        $salida_oficina = SalidaOficina::find($id);

        return view('administracion.salida_oficina.edit', ['salida_oficina' => $salida_oficina, 'inventario_oficinas' => InventarioOficina::all(), 'articulos' => Articulo::all(),'unidad_medidas' => UnidadMedida::all(), 'areas' => Area::all()]);



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
        $salida_oficina =SalidaOficina::find($id);

        $salida_oficina->fecha_salida =$request->input('fecha_salida');
        $salida_oficina->inventario_oficina_id =$request->input('inventario_oficina_id');
        $salida_oficina->unidad_medida_id =$request->input('unidad_medida_id');
        $salida_oficina->articulo_id =$request->input('articulo_id');
        $salida_oficina->cantidad =$request->input('cantidad');
        $salida_oficina->area_id =$request->input('area_id');
        $salida_oficina->save();

       /* $biene->update($data); */



        return redirect()->route('administracion.salida_oficina.index', $salida_oficina->id)->with('success', 'Excel actualizado correctamente');
    }
    public function destroy($id)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        try {
            // Iniciar transacción para asegurar integridad de datos
            DB::transaction(function () use ($id) {
                $salida_oficina = SalidaOficina::findOrFail($id);

                // Restaurar las unidades disponibles en el inventario
                if ($salida_oficina->inventario_oficina_id && $salida_oficina->cantidad) {
                    $inventario = InventarioOficina::find($salida_oficina->inventario_oficina_id);
                    if ($inventario) {
                        $inventario->unidades_disponibles += $salida_oficina->cantidad;
                        $inventario->save();
                    }
                }

                // Eliminar el registro de salida
                $salida_oficina->delete();
            });

            return redirect()->route('administracion.salida_oficina.index')
                ->with('datos', 'Registro eliminado correctamente. Las unidades han sido restauradas al inventario.');

        } catch (\Exception $e) {
            return redirect()->route('administracion.salida_oficina.index')
                ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        // Convertir request a array para pasar a la exportación
        $filters = $request->all();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalidaOficinaExport($filters),
            'salida_oficina.xlsx'
        );
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        $query = SalidaOficina::with(['articulos', 'gerencias', 'areas', 'unidad_medidas', 'users'])
            ->leftJoin('inventario_oficinas', 'salida_oficinas.inventario_oficina_id', '=', 'inventario_oficinas.id')
            ->select('salida_oficinas.*', 'inventario_oficinas.unidades_disponibles');

        // Aplicar los mismos filtros que en index
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

        // Calcular estadísticas para el PDF
        $totalRegistros = $salida_oficinas->count();
        $totalUnidades = $salida_oficinas->sum('cantidad');

        // Calcular sumaUnidadesPorArticulo (igual que en el índice)
        $sumaUnidadesPorArticulo = [];
        $articulos = Articulo::all();
        foreach ($articulos as $articulo) {
            $sumaUnidadesPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)
                ->sum('unidades');
        }

        // Agregar campos calculados a cada registro
        $salida_oficinas->each(function ($item) use ($sumaUnidadesPorArticulo) {
            // stockTotalReal: viene del campo historico_unidades_disponibles o del join
            $item->stockTotalReal = $item->historico_unidades_disponibles ?? $item->unidades_disponibles ?? 0;
            // sumaUnidades: suma de unidades del inventario para este artículo
            $item->sumaUnidades = $sumaUnidadesPorArticulo[$item->articulo_id] ?? 0;
        });

        // Generar descripción de filtros aplicados
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
