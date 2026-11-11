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

        // Obtener registros únicos por nombre de impresora + nombre de consumible + nombre de color
        $todos = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();

        // Agrupar por nombre de impresora + nombre de consumible + nombre de color
        $almacenamientos = $todos->groupBy(function($item) {
            $impresora = $item->impresoras?->impresora ?? '';
            $cartucho = $item->cartuchos?->cartucho ?? '';
            $color = $item->colores?->colore ?? '';
            return $impresora . '|' . $cartucho . '|' . $color;
        })->map(function($group) {
            return $group->sortByDesc('id')->first();
        })->values();

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

        // Obtener el almacenamiento seleccionado
        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')->find($request->almacenamiento_impresora_id);

        // Calcular unidades disponibles por nombre de impresora + consumible + color
        $nombreImpresora = $almacenamiento->impresoras?->impresora ?? '';
        $nombreCartucho = $almacenamiento->cartuchos?->cartucho ?? '';
        $nombreColor = $almacenamiento->colores?->colore ?? '';

        $todos = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')->get();
        $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                && ($item->colores?->colore ?? '') === $nombreColor;
        });

        $totalRegistrados = $coincidentes->sum('unidades');

        $totalAsignado = DistribucionAlmacenamientoImpresora::whereHas('almacenamientoImpresora', function($q) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            $q->whereHas('impresoras', function($q2) use ($nombreImpresora) {
                $q2->where('impresora', $nombreImpresora);
            })->whereHas('cartuchos', function($q2) use ($nombreCartucho) {
                $q2->where('cartucho', $nombreCartucho);
            })->whereHas('colores', function($q2) use ($nombreColor) {
                $q2->where('colore', $nombreColor);
            });
        })->sum('unidades_asignadas');

        $disponible = $totalRegistrados - $totalAsignado;

        if ($request->unidades_asignadas > $disponible) {
            return back()->withErrors(['unidades_asignadas' => 'Las unidades asignadas exceden las disponibles.']);
        }

        DistribucionAlmacenamientoImpresora::create(array_merge($request->all(), [
            'unidades_asignadas_originales' => $request->unidades_asignadas,
            'user_id' => auth()->id(),
        ]));

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

        $distribucion = DistribucionAlmacenamientoImpresora::with(
            'almacenamientoImpresora.cartuchos',
            'almacenamientoImpresora.colores',
            'almacenamientoImpresora.impresoras',
            'almacenamientoImpresora.fabricantes',
            'gerencia.area'
        )->findOrFail($id);

        // Obtener registros únicos por nombre de impresora + nombre de consumible + nombre de color
        $todos = AlmacenamientoImpresora::with('cartuchos', 'colores', 'impresoras', 'fabricantes')->get();

        // Agrupar por nombre de impresora + nombre de consumible + nombre de color
        $almacenamientos = $todos->groupBy(function($item) {
            $impresora = $item->impresoras?->impresora ?? '';
            $cartucho = $item->cartuchos?->cartucho ?? '';
            $color = $item->colores?->colore ?? '';
            return $impresora . '|' . $cartucho . '|' . $color;
        })->map(function($group) {
            return $group->sortByDesc('id')->first();
        })->values();

        // Obtener los datos de la impresora actual para seleccionarla automáticamente
        $impresoraActual = '';
        $cartuchoActual = '';
        $colorActual = '';

        if ($distribucion->almacenamientoImpresora) {
            $impresoraActual = $distribucion->almacenamientoImpresora->impresoras?->impresora ?? '';
            $cartuchoActual = $distribucion->almacenamientoImpresora->cartuchos?->cartucho ?? '';
            $colorActual = $distribucion->almacenamientoImpresora->colores?->colore ?? '';
        }

        $gerencias = Gerencia::with('area')->get();

        // Calculate available units for the current distribucion's almacenamiento
        if ($distribucion->almacenamientoImpresora) {
            $almacenamiento = $distribucion->almacenamientoImpresora;
            $nombreImpresora = $almacenamiento->impresoras?->impresora ?? '';
            $nombreCartucho = $almacenamiento->cartuchos?->cartucho ?? '';
            $nombreColor = $almacenamiento->colores?->colore ?? '';

            // Calcular total de unidades registradas con el mismo nombre de impresora, consumible y color
            $totalRegistrados = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
                return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                    && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                    && ($item->colores?->colore ?? '') === $nombreColor;
            })->sum('unidades');

            // Calcular total asignado excluyendo el registro actual
            $totalAsignado = DistribucionAlmacenamientoImpresora::whereHas('almacenamientoImpresora', function($q) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
                $q->whereHas('impresoras', function($q2) use ($nombreImpresora) {
                    $q2->where('impresora', $nombreImpresora);
                })->whereHas('cartuchos', function($q2) use ($nombreCartucho) {
                    $q2->where('cartucho', $nombreCartucho);
                })->whereHas('colores', function($q2) use ($nombreColor) {
                    $q2->where('colore', $nombreColor);
                });
            })
            ->where('id', '!=', $id)
            ->sum('unidades_asignadas');

            $distribucion->almacenamientoImpresora->unidades_disponibles_calculadas = $totalRegistrados - $totalAsignado;
        }

        return view('administracion.distribucion_almacenamiento_impresoras.edit', compact(
            'distribucion', 'almacenamientos', 'gerencias', 'impresoraActual', 'cartuchoActual', 'colorActual'
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
        $almacenamiento = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')->find($request->almacenamiento_impresora_id);

        // Calcular unidades disponibles por nombre de impresora + consumible + color
        $nombreImpresora = $almacenamiento->impresoras?->impresora ?? '';
        $nombreCartucho = $almacenamiento->cartuchos?->cartucho ?? '';
        $nombreColor = $almacenamiento->colores?->colore ?? '';

        $todos = AlmacenamientoImpresora::with('impresoras', 'cartuchos', 'colores')->get();
        $coincidentes = $todos->filter(function($item) use ($nombreImpresora, $nombreCartucho, $nombreColor) {
            return ($item->impresoras?->impresora ?? '') === $nombreImpresora
                && ($item->cartuchos?->cartucho ?? '') === $nombreCartucho
                && ($item->colores?->colore ?? '') === $nombreColor;
        });

        $totalRegistrados = $coincidentes->sum('unidades');

        $totalAsignado = DistribucionAlmacenamientoImpresora::whereHas('almacenamientoImpresora', function($q) use ($nombreImpresora, $nombreCartucho, $nombreColor, $id) {
            $q->whereHas('impresoras', function($q2) use ($nombreImpresora) {
                $q2->where('impresora', $nombreImpresora);
            })->whereHas('cartuchos', function($q2) use ($nombreCartucho) {
                $q2->where('cartucho', $nombreCartucho);
            })->whereHas('colores', function($q2) use ($nombreColor) {
                $q2->where('colore', $nombreColor);
            });
        })
        ->where('id', '!=', $id)
        ->sum('unidades_asignadas');

        $disponible = $totalRegistrados - $totalAsignado;

        if ($request->unidades_asignadas > $disponible) {
            return back()->withErrors(['unidades_asignadas' => 'Las unidades asignadas exceden las disponibles.']);
        }

        // Obtener las unidades originales para calcular la diferencia
        // Note: unidadesOriginales ya no se usa ya que no se modifica unidades_disponibles

        $distribucion->update(array_merge($request->all(), [
            'user_id' => auth()->id(),
        ]));

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

        if ($request->filled('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('distribucion_almacenamiento_impresoras.id', array_filter($ids));
        }

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
