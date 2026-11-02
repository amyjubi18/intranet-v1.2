<?php

namespace App\Http\Controllers;

use App\Models\DistribucionAlmacenamientoImpresora;
use App\Models\AlmacenamientoImpresora;
use App\Models\Gerencia;
use App\Models\Area;
use App\Models\Cartucho;
use App\Models\Impresora;
use App\Models\Colore;
use App\Models\Fabricante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DistribucionAlmacenamientoImpresoraExport;

class DistribucionAlmacenamientoImpresoraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('dist_almac_index'), 403);

        $query = DistribucionAlmacenamientoImpresora::with('almacenamientoImpresora.cartuchos', 'gerencia.area')->orderBy('id', 'desc');

        // Filtros específicos
        if ($request->has('gerencia') && $request->gerencia) {
            $query->where('gerencia_id', $request->gerencia);
        }

        if ($request->has('area') && $request->area) {
            $query->whereHas('gerencia', function($q) use ($request) {
                $q->where('area_id', $request->area);
            });
        }

        if ($request->has('cartucho') && $request->cartucho) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('cartuchos', function($subQ) use ($request) {
                    $subQ->where('id', $request->cartucho);
                });
            });
        }

        if ($request->has('impresora') && $request->impresora) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->where('impresora_id', $request->impresora);
            });
        }

        if ($request->has('colore') && $request->colore) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('colores', function($subQ) use ($request) {
                    $subQ->where('id', $request->colore);
                });
            });
        }

        if ($request->has('fabricante') && $request->fabricante) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('fabricantes', function($subQ) use ($request) {
                    $subQ->where('id', $request->fabricante);
                });
            });
        }

        // Filtro de búsqueda general
        if ($request->has('busqueda') && $request->busqueda) {
            $search = $request->busqueda;
            $query->where(function($q) use ($search) {
                $q->whereHas('almacenamientoImpresora.cartuchos', function($subQ) use ($search) {
                    $subQ->where('cartucho', 'like', '%' . $search . '%');
                })
                ->orWhereHas('gerencia', function($subQ) use ($search) {
                    $subQ->where('gerencia', 'like', '%' . $search . '%');
                })
                ->orWhereHas('gerencia.area', function($subQ) use ($search) {
                    $subQ->where('area', 'like', '%' . $search . '%');
                })
                ->orWhereHas('almacenamientoImpresora.impresora', function($subQ) use ($search) {
                    $subQ->where('impresora', 'like', '%' . $search . '%');
                })
                ->orWhereHas('almacenamientoImpresora.colores', function($subQ) use ($search) {
                    $subQ->where('color', 'like', '%' . $search . '%');
                });
            });
        }

        // Filtro de fecha mejorado - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            // Ambos campos llenos - filtrar por rango
            $query->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $distribuciones = $query->paginate(10)->appends($request->all());

        // Get options for filters
        $almacenamientos = AlmacenamientoImpresora::with('cartuchos')->get();
        $gerencias = Gerencia::with('area')->get();
        $areas = Area::all();
        $cartuchos = Cartucho::all();
        $impresoras = Impresora::all();
        $colores = Colore::all();
        $tiposConsumible = Fabricante::all();

        // Calculate statistics
        $totalDistribuciones = DistribucionAlmacenamientoImpresora::count();
        $totalUnidadesAsignadas = DistribucionAlmacenamientoImpresora::sum('unidades_asignadas');
        $gerenciasConDistribuciones = DistribucionAlmacenamientoImpresora::distinct('gerencia_id')->count('gerencia_id');

        $porcentajeUnidadesAsignadas = $totalUnidadesAsignadas > 0 ? round(($totalUnidadesAsignadas / AlmacenamientoImpresora::sum('unidades')) * 100, 1) : 0;

        // Gerencia y área con más unidades asignadas
        $gerenciaMasUnidades = DB::table('distribucion_almacenamiento_impresoras')
            ->join('gerencias', 'distribucion_almacenamiento_impresoras.gerencia_id', '=', 'gerencias.id')
            ->join('areas', 'gerencias.area_id', '=', 'areas.id')
            ->select('gerencias.gerencia', 'areas.area', DB::raw('SUM(unidades_asignadas) as total_unidades'))
            ->groupBy('gerencias.id', 'gerencias.gerencia', 'areas.area')
            ->orderBy('total_unidades', 'desc')
            ->first();

        // Impresora con más distribuciones
        $impresoraMasDistribuciones = DB::table('distribucion_almacenamiento_impresoras')
            ->join('almacenamiento_impresoras', 'distribucion_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('impresoras', 'almacenamiento_impresoras.impresora_id', '=', 'impresoras.id')
            ->select('impresoras.impresora', 'impresoras.imagen', DB::raw('COUNT(*) as total_distribuciones'))
            ->groupBy('impresoras.id', 'impresoras.impresora', 'impresoras.imagen')
            ->orderBy('total_distribuciones', 'desc')
            ->first();

        // Impresora que más gasta consumible (más unidades asignadas)
        $impresoraMasGasta = DB::table('distribucion_almacenamiento_impresoras')
            ->join('almacenamiento_impresoras', 'distribucion_almacenamiento_impresoras.almacenamiento_impresora_id', '=', 'almacenamiento_impresoras.id')
            ->join('impresoras', 'almacenamiento_impresoras.impresora_id', '=', 'impresoras.id')
            ->select('impresoras.impresora', 'impresoras.imagen', DB::raw('SUM(unidades_asignadas) as total_unidades'))
            ->groupBy('impresoras.id', 'impresoras.impresora', 'impresoras.imagen')
            ->orderBy('total_unidades', 'desc')
            ->first();

        // Get IDs of almacenamiento items with low stock for highlighting
        $unidadesDisponiblesBajoStock = \App\Models\AlmacenamientoImpresora::join('cartuchos', 'almacenamiento_impresoras.cartucho_id', '=', 'cartuchos.id')
            ->whereColumn('unidades_disponibles', '<', 'cartuchos.stock_min')
            ->pluck('almacenamiento_impresoras.id');

        return view('administracion.distribucion_almacenamiento_impresoras.index', compact(
            'distribuciones', 'almacenamientos', 'gerencias', 'areas', 'cartuchos', 'impresoras', 'colores', 'tiposConsumible', 'totalDistribuciones', 'totalUnidadesAsignadas', 'gerenciasConDistribuciones', 'porcentajeUnidadesAsignadas',
            'gerenciaMasUnidades', 'impresoraMasDistribuciones', 'impresoraMasGasta', 'unidadesDisponiblesBajoStock'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('dist_almac_create'), 403);

        $almacenamientos = AlmacenamientoImpresora::with('cartuchos', 'colores')->get();
        $gerencias = Gerencia::with('area')->get();

        return view('administracion.distribucion_almacenamiento_impresoras.create', compact(
            'almacenamientos', 'gerencias'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'gerencia_id' => 'required|exists:gerencias,id',
            'unidades_asignadas' => 'required|integer|min:1',
        ]);

        // Check if the assigned units don't exceed available units
        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $totalAsignado = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $request->almacenamiento_impresora_id)->sum('unidades_asignadas');
        $disponible = $almacenamiento->unidades_disponibles - $totalAsignado;

        if ($request->unidades_asignadas > $disponible) {
            return back()->withErrors(['unidades_asignadas' => 'Las unidades asignadas exceden las disponibles.']);
        }

        DistribucionAlmacenamientoImpresora::create(array_merge($request->all(), [
            'unidades_asignadas_originales' => $request->unidades_asignadas,
        ]));

        // Update available units in storage
        $almacenamiento->decrement('unidades_disponibles', $request->unidades_asignadas);

        return redirect()->route('administracion.distribucion_almacenamiento_impresoras.index')->with('success', 'Distribución creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_if(Gate::denies('dist_almac_edit'), 403);

        $distribucion = DistribucionAlmacenamientoImpresora::findOrFail($id);
        $almacenamientos = AlmacenamientoImpresora::with('cartuchos', 'colores')->get();
        $gerencias = Gerencia::with('area')->get();

        // Calculate available units for each almacenamiento (original - assigned)
        foreach ($almacenamientos as $almacenamiento) {
            $totalAsignado = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $almacenamiento->id)->sum('unidades_asignadas');
            $almacenamiento->unidades_disponibles_calculadas = $almacenamiento->unidades_disponibles - $totalAsignado;
        }

        // Calculate for the current distribucion's almacenamiento
        if ($distribucion->almacenamiento_impresora) {
            $totalAsignadoActual = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $distribucion->almacenamiento_impresora_id)
                ->where('id', '!=', $id)
                ->sum('unidades_asignadas');
            $distribucion->almacenamiento_impresora->unidades_disponibles_calculadas = $distribucion->almacenamiento_impresora->unidades_disponibles - $totalAsignadoActual;
        }

        return view('administracion.distribucion_almacenamiento_impresoras.edit', compact(
            'distribucion', 'almacenamientos', 'gerencias'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'almacenamiento_impresora_id' => 'required|exists:almacenamiento_impresoras,id',
            'gerencia_id' => 'required|exists:gerencias,id',
            'unidades_asignadas' => 'required|integer|min:1',
        ]);

        $distribucion = DistribucionAlmacenamientoImpresora::findOrFail($id);

        // Check if the assigned units don't exceed available units
        $almacenamiento = AlmacenamientoImpresora::find($request->almacenamiento_impresora_id);
        $totalAsignado = DistribucionAlmacenamientoImpresora::where('almacenamiento_impresora_id', $request->almacenamiento_impresora_id)
            ->where('id', '!=', $id)
            ->sum('unidades_asignadas');
        $disponible = $almacenamiento->unidades_disponibles - $totalAsignado;

        if ($request->unidades_asignadas > $disponible) {
            return back()->withErrors(['unidades_asignadas' => 'Las unidades asignadas exceden las disponibles.']);
        }

        $distribucion->update($request->all());

        // Update available units in storage
        $diferencia = $request->unidades_asignadas - $distribucion->getOriginal('unidades_asignadas');
        $almacenamiento->decrement('unidades_disponibles', $diferencia);

        return redirect()->route('administracion.distribucion_almacenamiento_impresoras.index')->with('success', 'Distribución actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        abort_if(Gate::denies('dist_almac_destroy'), 403);

        $distribucion = DistribucionAlmacenamientoImpresora::findOrFail($id);
        $distribucion->delete();

        // Restore available units in storage
        $almacenamiento = AlmacenamientoImpresora::find($distribucion->almacenamiento_impresora_id);
        $almacenamiento->increment('unidades_disponibles', $distribucion->unidades_asignadas);

        return redirect()->route('administracion.distribucion_almacenamiento_impresoras.index')->with('success', 'Distribución eliminada exitosamente.');
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(new DistribucionAlmacenamientoImpresoraExport($request), "distribucion_almacenamiento_impresoras.xlsx");
    }



    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = DistribucionAlmacenamientoImpresora::with("almacenamientoImpresora.cartuchos", "gerencia.area");

        // Aplicar los mismos filtros que en index
        if ($request->has('gerencia') && $request->gerencia) {
            $query->where('gerencia_id', $request->gerencia);
        }

        if ($request->has('area') && $request->area) {
            $query->whereHas('gerencia', function($q) use ($request) {
                $q->where('area_id', $request->area);
            });
        }

        if ($request->has('cartucho') && $request->cartucho) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('cartuchos', function($subQ) use ($request) {
                    $subQ->where('id', $request->cartucho);
                });
            });
        }

        if ($request->has('impresora') && $request->impresora) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->where('impresora_id', $request->impresora);
            });
        }

        if ($request->has('colore') && $request->colore) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('colores', function($subQ) use ($request) {
                    $subQ->where('id', $request->colore);
                });
            });
        }

        if ($request->has('fabricante') && $request->fabricante) {
            $query->whereHas('almacenamientoImpresora', function($q) use ($request) {
                $q->whereHas('fabricantes', function($subQ) use ($request) {
                    $subQ->where('id', $request->fabricante);
                });
            });
        }

        // Filtro de búsqueda general
        if ($request->has('busqueda') && $request->busqueda) {
            $search = $request->busqueda;
            $query->where(function($q) use ($search) {
                $q->whereHas('almacenamientoImpresora.cartuchos', function($subQ) use ($search) {
                    $subQ->where('cartucho', 'like', '%' . $search . '%');
                })
                ->orWhereHas('gerencia', function($subQ) use ($search) {
                    $subQ->where('gerencia', 'like', '%' . $search . '%');
                })
                ->orWhereHas('gerencia.area', function($subQ) use ($search) {
                    $subQ->where('area', 'like', '%' . $search . '%');
                })
                ->orWhereHas('almacenamientoImpresora.impresora', function($subQ) use ($search) {
                    $subQ->where('impresora', 'like', '%' . $search . '%');
                })
                ->orWhereHas('almacenamientoImpresora.colores', function($subQ) use ($search) {
                    $subQ->where('color', 'like', '%' . $search . '%');
                });
            });
        }

        // Filtro de fecha mejorado - permite filtrar por fechas individuales o rangos
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            // Ambos campos llenos - filtrar por rango
            $query->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            // Solo fecha_desde - filtrar desde esa fecha hasta ahora
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            // Solo fecha_hasta - filtrar desde el inicio hasta esa fecha
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $distribuciones = $query->get();

        $pdf = PDF::loadView("administracion.distribucion_almacenamiento_impresoras.pdf", compact("distribuciones"))->setPaper('a4', 'landscape');

        return $pdf->download("distribucion_almacenamiento_impresoras.pdf");
    }


}
