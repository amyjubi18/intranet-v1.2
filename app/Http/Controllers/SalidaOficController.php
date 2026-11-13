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
use Illuminate\Support\Facades\Hash;
use Exception;

class SalidaOficController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salidaofic_index'), 403);

        $query = SalidaOficina::with(['articulos.unidad_medida', 'gerencias', 'areas', 'unidad_medidas', 'users', 'inventario_oficinas'])
            ->orderBy('salida_oficinas.id', 'desc');

        // Filtro por IDs seleccionados
        if ($request->filled('selected_ids')) {
            $selectedIds = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            $query->whereIn('salida_oficinas.id', array_filter($selectedIds));
        }

        // 1. Filtros de Fecha de Salida
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $query->whereBetween('fecha_salida', [$request->fecha_desde, $request->fecha_hasta]);
        } elseif ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_salida', '>=', $request->fecha_desde);
        } elseif ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_salida', '<=', $request->fecha_hasta);
        }

        // 2. Filtros de Fecha de Ingreso del inventario
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

        // 3. Filtro por Gerencia (casada con sus áreas)
        $gerenciaParam = $request->input('gerencia_id', $request->input('gerencia', $request->filterType === 'gerencia' ? $request->secondFilter : null));
        if ($gerenciaParam) {
            $gerencia = is_numeric($gerenciaParam) ? Gerencia::with('area')->find($gerenciaParam) : Gerencia::where('gerencia', $gerenciaParam)->first();
            if ($gerencia) {
                // Obtener todos los IDs y áreas asociadas a esta Gerencia
                $sameGerenciaRows = Gerencia::where('gerencia', $gerencia->gerencia)->get();
                $sameGerenciaIds = $sameGerenciaRows->pluck('id')->toArray();
                $sameGerenciaAreaIds = $sameGerenciaRows->pluck('area_id')->filter()->toArray();

                $query->where(function($q) use ($sameGerenciaIds, $sameGerenciaAreaIds) {
                    $q->whereIn('salida_oficinas.gerencia_id', $sameGerenciaIds)
                      ->orWhere(function($subQ) use ($sameGerenciaAreaIds) {
                          $subQ->whereNull('salida_oficinas.gerencia_id')
                               ->whereIn('salida_oficinas.area_id', $sameGerenciaAreaIds);
                      });
                });
            } else {
                $query->where('salida_oficinas.gerencia_id', $gerenciaParam);
            }
        }

        // 4. Filtro por Área
        $areaId = $request->input('area_id', $request->input('area', $request->filterType === 'area' ? $request->secondFilter : null));
        if ($areaId) {
            $query->where('salida_oficinas.area_id', $areaId);
        }

        // 5. Filtro por Artículo
        $articuloId = $request->input('articulo_id', $request->input('articulo', $request->filterType === 'articulo' ? $request->secondFilter : null));
        if ($articuloId) {
            $query->where('salida_oficinas.articulo_id', $articuloId);
        }

        // 6. Búsqueda por Texto
        if ($request->filled('busqueda')) {
            $search = trim($request->busqueda);
            $query->where(function($q) use ($search) {
                $q->where('salida_oficinas.cantidad', 'like', '%' . $search . '%')
                  ->orWhere('salida_oficinas.ajuste_inventario', 'like', '%' . $search . '%')
                  ->orWhereHas('articulos', fn($subQ) => $subQ->where('articulo', 'like', '%' . $search . '%'))
                  ->orWhereHas('gerencias', fn($subQ) => $subQ->where('gerencia', 'like', '%' . $search . '%'))
                  ->orWhereHas('areas', fn($subQ) => $subQ->where('area', 'like', '%' . $search . '%'))
                  ->orWhereHas('users', function($subQ) use ($search) {
                      $subQ->where('name', 'like', '%' . $search . '%')
                           ->orWhere('lastname', 'like', '%' . $search . '%')
                           ->orWhere('username', 'like', '%' . $search . '%');
                  });
            });
        }

        // Métricas
        $ultimasFechasSalidaRecord = SalidaOficina::orderBy('fecha_salida', 'desc')->first();
        $ultimasFechasSalida = $ultimasFechasSalidaRecord ? \Carbon\Carbon::parse($ultimasFechasSalidaRecord->fecha_salida)->format('d/m/Y') : 'N/A';
        $totalSalidas = SalidaOficina::count();
        $salidasEsteMes = SalidaOficina::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        // Artículos con bajo stock
        $articulosBajoStock = Articulo::with('unidad_medida')->get()->filter(function($art) {
            $stockDisp = (int) InventarioOficina::where('articulo_id', $art->id)->sum('unidades_disponibles');
            return $stockDisp < (int) $art->stock_min;
        })->take(5)->values();
        $countSalidasBajoStock = $articulosBajoStock->count();

        // Catálogos con mapeo bidireccional Gerencia <-> Área
        $gerenciasRaw = Gerencia::with('area')->orderBy('gerencia')->get();
        $areasRaw = Area::orderBy('area')->get();

        $areas = $areasRaw->map(function($a) use ($gerenciasRaw) {
            $matchingGerencias = $gerenciasRaw->where('area_id', $a->id);
            return [
                'id' => $a->id,
                'area' => $a->area,
                'gerencia_id' => $matchingGerencias->first()?->id ?? null,
                'gerencia_name' => $matchingGerencias->first()?->gerencia ?? null,
                'gerencia_ids' => $matchingGerencias->pluck('id')->values()->all(),
                'gerencia_names' => $matchingGerencias->pluck('gerencia')->unique()->values()->all(),
            ];
        });

        $gerencias = $gerenciasRaw->map(function($g) {
            return [
                'id' => $g->id,
                'gerencia' => $g->gerencia,
                'area_id' => $g->area_id,
                'area_name' => $g->area?->area ?? null,
            ];
        });

        $unidad_medidas = UnidadMedida::all();

        // Artículos con stock calculado
        $articulos = Articulo::with('unidad_medida')->orderBy('articulo')->get()->map(function($art) {
            $stockDisp = InventarioOficina::where('articulo_id', $art->id)->sum('unidades_disponibles');
            $stockTotal = InventarioOficina::where('articulo_id', $art->id)->sum('unidades');
            return [
                'id' => $art->id,
                'articulo' => $art->articulo,
                'stock_min' => $art->stock_min,
                'unidad_medida_id' => $art->unidad_medida_id,
                'unidad_medida' => $art->unidad_medida ? $art->unidad_medida->unidad_medida : 'UNIDAD',
                'stock_disponible' => (int) $stockDisp,
                'stock_total' => (int) $stockTotal,
            ];
        });

        // Respuesta JSON para el componente React
        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            $perPage = $request->input('per_page', 15);
            $salidasPaginated = $query->paginate($perPage);

            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'salidas' => $salidasPaginated,
                    'metrics' => [
                        'ultimasFechasSalida' => $ultimasFechasSalida,
                        'totalSalidas' => $totalSalidas,
                        'salidasEsteMes' => $salidasEsteMes,
                        'countSalidasBajoStock' => $countSalidasBajoStock,
                        'articulosBajoStock' => $articulosBajoStock,
                    ],
                    'catalogs' => [
                        'articulos' => $articulos,
                        'gerencias' => $gerencias,
                        'areas' => $areas,
                        'unidad_medidas' => $unidad_medidas,
                    ],
                    'permissions' => [
                        'can_create' => auth()->user()?->can('salidaofic_create') ?? false,
                        'can_edit' => auth()->user()?->can('salidaofic_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('salidaofic_destroy') ?? false,
                        'can_export' => true,
                    ]
                ],
                'message' => 'Salidas de oficina obtenidas exitosamente'
            ], 200);
        }

        // Vista Blade que monta el componente React
        return view('administracion.salida_oficina.index', compact(
            'ultimasFechasSalida', 'totalSalidas', 'salidasEsteMes',
            'countSalidasBajoStock', 'gerencias', 'areas', 'articulos'
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('salidaofic_create'), 403);
        return redirect()->route('administracion.salida_oficina.index');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('salidaofic_create'), 403);

        $request->validate([
            'fecha_salida' => 'required|date|before_or_equal:today',
            'articulo_id' => 'required|exists:articulos,id',
            'cantidad' => 'required|integer|min:1',
            'gerencia_id' => 'required|exists:gerencias,id',
            'area_id' => 'required|exists:areas,id',
            'ajuste_inventario' => 'nullable|string|max:500',
        ]);

        $articuloId = $request->articulo_id;
        $cantidad = (int) $request->cantidad;

        // Validar stock disponible total del artículo
        $stockDisponible = (int) InventarioOficina::where('articulo_id', $articuloId)->sum('unidades_disponibles');

        if ($cantidad > $stockDisponible) {
            $msg = "No se puede retirar {$cantidad} unidades. Solo hay {$stockDisponible} unidades disponibles en inventario.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        try {
            $salida = DB::transaction(function () use ($request, $articuloId, $cantidad, $stockDisponible) {
                // Obtener lotes de inventario ordenados por fecha de ingreso FIFO
                $inventarios = InventarioOficina::where('articulo_id', $articuloId)
                    ->where('unidades_disponibles', '>', 0)
                    ->orderBy('fecha_ingreso', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                if ($inventarios->isEmpty()) {
                    throw new Exception('No hay lotes de inventario disponibles con stock para este artículo.');
                }

                $restante = $cantidad;
                $primaryInventarioId = $inventarios->first()->id;
                $unidadMedidaId = $inventarios->first()->unidad_medida_id;

                foreach ($inventarios as $inv) {
                    if ($restante <= 0) break;
                    $aDescontar = min($inv->unidades_disponibles, $restante);
                    $inv->unidades_disponibles -= $aDescontar;
                    $inv->save();
                    $restante -= $aDescontar;
                }

                // Resolver gerencia_id
                $gerenciaId = $request->gerencia_id ?: (Area::find($request->area_id)?->gerencia_id ?: Gerencia::first()->id);
                $ajusteInventario = $request->ajuste_inventario ?: 'Despacho de oficina';

                return SalidaOficina::create([
                    'fecha_salida' => $request->fecha_salida,
                    'unidad_medida_id' => $unidadMedidaId,
                    'inventario_oficina_id' => $primaryInventarioId,
                    'cantidad' => $cantidad,
                    'articulo_id' => $articuloId,
                    'gerencia_id' => $gerenciaId,
                    'area_id' => $request->area_id,
                    'ajuste_inventario' => $ajusteInventario,
                    'historico_unidades_disponibles' => $stockDisponible,
                    'user_id' => auth()->id() ?? 1,
                ]);
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salida registrada correctamente. Se descontaron ' . $cantidad . ' unidades del inventario.',
                    'data' => $salida->load(['articulos.unidad_medida', 'gerencias', 'areas', 'unidad_medidas', 'users', 'inventario_oficinas'])
                ], 201);
            }

            return redirect()->route('administracion.salida_oficina.index')->with('success', 'Salida registrada exitosamente');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al registrar salida: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error al registrar salida: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        abort_if(Gate::denies('salidaofic_edit'), 403);
        return redirect()->route('administracion.salida_oficina.index');
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('salidaofic_edit'), 403);

        $request->validate([
            'fecha_salida' => 'required|date|before_or_equal:today',
            'articulo_id' => 'required|exists:articulos,id',
            'cantidad' => 'required|integer|min:1',
            'gerencia_id' => 'required|exists:gerencias,id',
            'area_id' => 'required|exists:areas,id',
            'ajuste_inventario' => 'nullable|string|max:500',
        ]);

        try {
            $salida = DB::transaction(function () use ($request, $id) {
                $salidaOriginal = SalidaOficina::lockForUpdate()->findOrFail($id);

                $oldArticuloId = $salidaOriginal->articulo_id;
                $newArticuloId = (int) $request->articulo_id;
                $oldCantidad = (int) $salidaOriginal->cantidad;
                $newCantidad = (int) $request->cantidad;

                // Caso 1: Mismo artículo
                if ($oldArticuloId === $newArticuloId) {
                    $diff = $newCantidad - $oldCantidad;

                    if ($diff > 0) {
                        // Se requieren más unidades -> comprobar stock disponible
                        $stockDisponible = (int) InventarioOficina::where('articulo_id', $newArticuloId)->sum('unidades_disponibles');
                        if ($diff > $stockDisponible) {
                            throw new Exception("Stock insuficiente. Se necesitan {$diff} unidades adicionales pero solo hay {$stockDisponible} disponibles.");
                        }

                        // Descontar la diferencia FIFO
                        $inventarios = InventarioOficina::where('articulo_id', $newArticuloId)
                            ->where('unidades_disponibles', '>', 0)
                            ->orderBy('fecha_ingreso', 'asc')
                            ->orderBy('id', 'asc')
                            ->lockForUpdate()
                            ->get();

                        $restante = $diff;
                        foreach ($inventarios as $inv) {
                            if ($restante <= 0) break;
                            $aDescontar = min($inv->unidades_disponibles, $restante);
                            $inv->unidades_disponibles -= $aDescontar;
                            $inv->save();
                            $restante -= $aDescontar;
                        }
                    } elseif ($diff < 0) {
                        // Se devuelven unidades al inventario
                        $aRestaurar = abs($diff);
                        $invTarget = InventarioOficina::find($salidaOriginal->inventario_oficina_id);
                        if (!$invTarget) {
                            $invTarget = InventarioOficina::where('articulo_id', $newArticuloId)->first();
                        }
                        if ($invTarget) {
                            $invTarget->unidades_disponibles += $aRestaurar;
                            $invTarget->save();
                        }
                    }
                } else {
                    // Caso 2: Cambió el artículo
                    // 1. Restaurar cantidad al artículo anterior
                    $invOld = InventarioOficina::find($salidaOriginal->inventario_oficina_id)
                        ?? InventarioOficina::where('articulo_id', $oldArticuloId)->first();
                    if ($invOld) {
                        $invOld->unidades_disponibles += $oldCantidad;
                        $invOld->save();
                    }

                    // 2. Verificar stock del nuevo artículo
                    $stockNuevo = (int) InventarioOficina::where('articulo_id', $newArticuloId)->sum('unidades_disponibles');
                    if ($newCantidad > $stockNuevo) {
                        throw new Exception("Stock insuficiente para el nuevo artículo. Se solicitaron {$newCantidad} unidades pero solo hay {$stockNuevo} disponibles.");
                    }

                    // 3. Descontar del nuevo artículo FIFO
                    $inventarios = InventarioOficina::where('articulo_id', $newArticuloId)
                        ->where('unidades_disponibles', '>', 0)
                        ->orderBy('fecha_ingreso', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $restante = $newCantidad;
                    foreach ($inventarios as $inv) {
                        if ($restante <= 0) break;
                        $aDescontar = min($inv->unidades_disponibles, $restante);
                        $inv->unidades_disponibles -= $aDescontar;
                        $inv->save();
                        $restante -= $aDescontar;
                    }

                    $salidaOriginal->inventario_oficina_id = $inventarios->first()?->id;
                    $salidaOriginal->unidad_medida_id = $inventarios->first()?->unidad_medida_id ?? Articulo::find($newArticuloId)->unidad_medida_id;
                    $salidaOriginal->articulo_id = $newArticuloId;
                }

                // Resolver gerencia
                $gerenciaId = $request->gerencia_id ?: (Area::find($request->area_id)?->gerencia_id ?: $salidaOriginal->gerencia_id);
                $ajusteInventario = $request->ajuste_inventario ?: ($salidaOriginal->ajuste_inventario ?: 'Despacho de oficina');

                $salidaOriginal->fecha_salida = $request->fecha_salida;
                $salidaOriginal->cantidad = $newCantidad;
                $salidaOriginal->area_id = $request->area_id;
                $salidaOriginal->gerencia_id = $gerenciaId;
                $salidaOriginal->ajuste_inventario = $ajusteInventario;
                $salidaOriginal->save();

                return $salidaOriginal;
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salida actualizada correctamente y stock sincronizado.',
                    'data' => $salida->load(['articulos.unidad_medida', 'gerencias', 'areas', 'unidad_medidas', 'users', 'inventario_oficinas'])
                ], 200);
            }

            return redirect()->route('administracion.salida_oficina.index')->with('success', 'Registro actualizado correctamente');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            $msg = 'La contraseña ingresada es incorrecta.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        try {
            DB::transaction(function () use ($id) {
                $salida = SalidaOficina::lockForUpdate()->findOrFail($id);

                if ($salida->articulo_id && $salida->cantidad) {
                    // Restaurar al lote original o al primer lote disponible del artículo
                    $inv = InventarioOficina::find($salida->inventario_oficina_id)
                        ?? InventarioOficina::where('articulo_id', $salida->articulo_id)->first();
                    if ($inv) {
                        $inv->unidades_disponibles += $salida->cantidad;
                        $inv->save();
                    }
                }

                $salida->delete();
            });

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado correctamente. Las unidades han sido restauradas al inventario.'
                ], 200);
            }

            return redirect()->route('administracion.salida_oficina.index')
                ->with('datos', 'Registro eliminado correctamente. Las unidades han sido restauradas al inventario.');
        } catch (Exception $e) {
            $msg = 'Error al eliminar el registro: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->route('administracion.salida_oficina.index')->with('error', $msg);
        }
    }

    public function destroyMultiple(Request $request)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        $request->validate([
            'ids' => 'required',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            $msg = 'La contraseña ingresada es incorrecta.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $ids = is_array($request->ids) ? $request->ids : json_decode($request->ids, true);
        if (!is_array($ids) || count($ids) === 0) {
            $msg = 'No se seleccionaron registros para eliminar.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $count = count($ids);

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $id) {
                    $salida = SalidaOficina::lockForUpdate()->find($id);
                    if (!$salida) continue;

                    if ($salida->articulo_id && $salida->cantidad) {
                        $inv = InventarioOficina::find($salida->inventario_oficina_id)
                            ?? InventarioOficina::where('articulo_id', $salida->articulo_id)->first();
                        if ($inv) {
                            $inv->unidades_disponibles += $salida->cantidad;
                            $inv->save();
                        }
                    }

                    $salida->delete();
                }
            });

            $msg = "Se eliminaron {$count} registro(s) correctamente. Las unidades han sido restauradas al inventario.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg], 200);
            }

            return redirect()->route('administracion.salida_oficina.index')->with('datos', $msg);
        } catch (Exception $e) {
            $msg = 'Error al eliminar registros: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->route('administracion.salida_oficina.index')->with('error', $msg);
        }
    }

    public function destroyAll(Request $request)
    {
        abort_if(Gate::denies('salidaofic_destroy'), 403);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            $msg = 'La contraseña ingresada es incorrecta.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        try {
            DB::transaction(function () {
                $allSalidas = SalidaOficina::lockForUpdate()->get();

                foreach ($allSalidas as $salida) {
                    if ($salida->articulo_id && $salida->cantidad) {
                        $inv = InventarioOficina::find($salida->inventario_oficina_id)
                            ?? InventarioOficina::where('articulo_id', $salida->articulo_id)->first();
                        if ($inv) {
                            $inv->unidades_disponibles += $salida->cantidad;
                            $inv->save();
                        }
                    }
                }

                SalidaOficina::query()->delete();
            });

            $msg = "Se eliminaron todos los registros correctamente. Las unidades han sido restauradas al inventario.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg], 200);
            }

            return redirect()->route('administracion.salida_oficina.index')->with('datos', $msg);
        } catch (Exception $e) {
            $msg = 'Error al eliminar todos los registros: ' . $e->getMessage();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 500);
            }
            return redirect()->route('administracion.salida_oficina.index')->with('error', $msg);
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

        if ($request->filled('gerencia_id')) {
            $gerParam = $request->gerencia_id;
            $ger = is_numeric($gerParam) ? Gerencia::with('area')->find($gerParam) : Gerencia::where('gerencia', $gerParam)->first();
            if ($ger) {
                $sameGerIds = Gerencia::where('gerencia', $ger->gerencia)->pluck('id')->toArray();
                $sameGerAreaIds = Gerencia::where('gerencia', $ger->gerencia)->pluck('area_id')->filter()->toArray();
                $query->where(function($q) use ($sameGerIds, $sameGerAreaIds) {
                    $q->whereIn('salida_oficinas.gerencia_id', $sameGerIds)
                      ->orWhere(function($subQ) use ($sameGerAreaIds) {
                          $subQ->whereNull('salida_oficinas.gerencia_id')
                               ->whereIn('salida_oficinas.area_id', $sameGerAreaIds);
                      });
                });
            } else {
                $query->where('salida_oficinas.gerencia_id', $gerParam);
            }
        }

        if ($request->filled('area_id')) {
            $query->where('salida_oficinas.area_id', $request->area_id);
        }

        if ($request->filled('articulo_id')) {
            $query->where('salida_oficinas.articulo_id', $request->articulo_id);
        }

        if ($request->filled('busqueda')) {
            $search = trim($request->busqueda);
            $query->where(function($q) use ($search) {
                $q->where('salida_oficinas.cantidad', 'like', '%' . $search . '%')
                  ->orWhere('salida_oficinas.ajuste_inventario', 'like', '%' . $search . '%')
                  ->orWhereHas('articulos', fn($subQ) => $subQ->where('articulo', 'like', '%' . $search . '%'))
                  ->orWhereHas('gerencias', fn($subQ) => $subQ->where('gerencia', 'like', '%' . $search . '%'))
                  ->orWhereHas('areas', fn($subQ) => $subQ->where('area', 'like', '%' . $search . '%'))
                  ->orWhereHas('users', fn($subQ) => $subQ->where('name', 'like', '%' . $search . '%'));
            });
        }

        $salida_oficinas = $query->orderBy('salida_oficinas.id', 'desc')->get();
        $totalRegistros = $salida_oficinas->count();
        $totalUnidades = $salida_oficinas->sum('cantidad');

        $sumaUnidadesPorArticulo = [];
        $articulos = Articulo::all();
        foreach ($articulos as $articulo) {
            $sumaUnidadesPorArticulo[$articulo->id] = InventarioOficina::where('articulo_id', $articulo->id)->sum('unidades');
        }

        $salida_oficinas->each(function ($item) use ($sumaUnidadesPorArticulo) {
            $item->stockHistorico = $item->historico_unidades_disponibles ?? 0;
            $item->stockRestante = max(0, $item->stockHistorico - $item->cantidad);
        });

        $filtersApplied = $request->filled('articulo_id') || $request->filled('gerencia_id') || $request->filled('area_id') || $request->filled('busqueda') || $request->filled('fecha_desde');
        $filterDescription = '';
        if ($filtersApplied) {
            $parts = [];
            if ($request->filled('articulo_id')) {
                $art = Articulo::find($request->articulo_id);
                if ($art) $parts[] = 'Artículo: ' . $art->articulo;
            }
            if ($request->filled('gerencia_id')) {
                $ger = Gerencia::find($request->gerencia_id);
                if ($ger) $parts[] = 'Gerencia: ' . $ger->gerencia;
            }
            if ($request->filled('area_id')) {
                $ar = Area::find($request->area_id);
                if ($ar) $parts[] = 'Área: ' . $ar->area;
            }
            if ($request->filled('busqueda')) {
                $parts[] = 'Búsqueda: ' . $request->busqueda;
            }
            if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
                $parts[] = 'Fecha: ' . ($request->fecha_desde ?: '...') . ' al ' . ($request->fecha_hasta ?: '...');
            }
            $filterDescription = implode(', ', $parts);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('administracion.salida_oficina.pdf', compact(
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
