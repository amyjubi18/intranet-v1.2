<?php

namespace App\Http\Controllers;

use App\Models\Cartucho;
use App\Models\Colore;
use App\Models\Impresora;
use App\Models\Fabricante;
use App\Models\Gerencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;

class CartuchoController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('cartucho_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
        $allowedSorts = ['id', 'cartucho', 'impresora_id', 'fabricante_id', 'colore_id', 'stock_min', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $query = Cartucho::query()->with([
            'colore',
            'impresora.gerencias.area',
            'fabricante'
        ]);

        // Filtro por búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cartucho', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('colore', function ($cq) use ($search) {
                      $cq->where('colore', 'LIKE', '%' . $search . '%');
                  })
                  ->orWhereHas('impresora', function ($iq) use ($search) {
                      $iq->where('impresora', 'LIKE', '%' . $search . '%');
                  })
                  ->orWhereHas('fabricante', function ($fq) use ($search) {
                      $fq->where('fabricante', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        // Filtros específicos
        if ($request->filled('nombre-impresora') || $request->filled('impresora_id')) {
            $impId = $request->input('nombre-impresora', $request->input('impresora_id'));
            $query->where('impresora_id', $impId);
        }

        if ($request->filled('consumible') || $request->filled('cartucho_id')) {
            $cId = $request->input('consumible', $request->input('cartucho_id'));
            $query->where('id', $cId);
        }

        if ($request->filled('color') || $request->filled('colore_id')) {
            $colId = $request->input('color', $request->input('colore_id'));
            $query->where('colore_id', $colId);
        }

        if ($request->filled('tipo') || $request->filled('fabricante_id')) {
            $fabId = $request->input('tipo', $request->input('fabricante_id'));
            $query->where('fabricante_id', $fabId);
        }

        // Filtro por gerencia (a través de la relación con impresoras)
        if ($request->filled('gerencia_id')) {
            $gerId = $request->input('gerencia_id');
            $query->whereHas('impresora.gerencias', function ($q) use ($gerId) {
                $q->where('gerencias.id', $gerId);
            });
        }

        $cartuchos = $query->orderBy($sort, $direction)->paginate(15);

        // Opciones para filtros y modales
        $impresoras = Impresora::with('gerencias.area')->orderBy('impresora')->get();
        $colores = Colore::orderBy('colore')->get();
        $fabricantes = Fabricante::orderBy('fabricante')->get();
        $all_cartuchos = Cartucho::with(['colore', 'impresora', 'fabricante'])->orderBy('cartucho')->get();
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();

        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'cartuchos' => $cartuchos,
                    'catalogs' => [
                        'impresoras' => $impresoras,
                        'colores' => $colores,
                        'fabricantes' => $fabricantes,
                        'all_cartuchos' => $all_cartuchos,
                        'gerencias' => $gerencias,
                    ],
                    'permissions' => [
                        'can_create' => auth()->user()?->can('cartucho_create') ?? false,
                        'can_edit' => auth()->user()?->can('cartucho_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('cartucho_destroy') ?? false,
                    ]
                ],
                'message' => 'Consumibles obtenidos exitosamente'
            ], 200);
        }

        return view('administracion.consumibles.index', [
            'cartuchos' => $cartuchos,
            'impresoras' => $impresoras,
            'colores' => $colores,
            'fabricantes' => $fabricantes,
            'all_cartuchos' => $all_cartuchos,
            'gerencias' => $gerencias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function create(Cartucho $cartucho)
    {
        abort_if(Gate::denies('cartucho_create'), 403);
        return view('administracion.consumibles.create', [
            'colores' => Colore::orderBy('colore')->get(),
            'impresoras' => Impresora::orderBy('impresora')->get(),
            'fabricantes' => Fabricante::with('colores')->orderBy('fabricante')->get()
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('cartucho_create'), 403);

        $rules = [
            'cartucho' => 'required|string|max:191',
            'stock_min' => 'required|numeric|min:0',
            'impresora_id' => 'required|exists:impresoras,id',
            'fabricante_id' => 'required|exists:fabricantes,id',
        ];

        // Validar según el modo de selección de color
        if ($request->has('colore_ids') && is_array($request->colore_ids) && count($request->colore_ids) > 0) {
            $rules['colore_ids'] = 'required|array|min:1';
            $rules['colore_ids.*'] = 'exists:colores,id';
        } else {
            $rules['colore_id'] = 'required|exists:colores,id';
        }

        $request->validate($rules, [
            'cartucho.required' => 'El nombre/modelo del consumible es obligatorio.',
            'stock_min.required' => 'El stock mínimo es obligatorio.',
            'impresora_id.required' => 'Debe seleccionar una impresora compatible.',
            'fabricante_id.required' => 'Debe seleccionar un tipo/fabricante.',
            'colore_id.required' => 'Debe seleccionar un tipo de consumible / color.',
        ]);

        $colorId = null;
        if ($request->has('colore_ids') && is_array($request->colore_ids) && count($request->colore_ids) > 0) {
            $colorId = $request->colore_ids[0];
        } else {
            $colorId = $request->colore_id;
        }

        $cartucho = new Cartucho;
        $cartucho->cartucho = trim($request->cartucho);
        $cartucho->stock_min = $request->stock_min;
        $cartucho->impresora_id = $request->impresora_id;
        $cartucho->fabricante_id = $request->fabricante_id;
        $cartucho->colore_id = $colorId;
        $cartucho->save();

        if ($request->wantsJson() || $request->ajax()) {
            $cartucho->load(['colore', 'impresora.gerencias.area', 'fabricante']);
            return response()->json([
                'success' => true,
                'message' => 'Consumible registrado exitosamente.',
                'data' => $cartucho
            ], 201);
        }

        return redirect()->route('administracion.consumibles.index')->with('status', 'consumible creado');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('cartucho_edit'), 403);
        $cartucho = Cartucho::findOrFail($id);
        return view('administracion.consumibles.edit', [
            'cartucho' => $cartucho,
            'colores' => Colore::orderBy('colore')->get(),
            'impresoras' => Impresora::orderBy('impresora')->get(),
            'fabricantes' => Fabricante::orderBy('fabricante')->get()
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('cartucho_edit'), 403);

        $request->validate([
            'cartucho' => 'required|string|max:191',
            'colore_id' => 'required|exists:colores,id',
            'stock_min' => 'required|numeric|min:0',
            'impresora_id' => 'required|exists:impresoras,id',
            'fabricante_id' => 'required|exists:fabricantes,id',
        ], [
            'cartucho.required' => 'El nombre/modelo del consumible es obligatorio.',
            'stock_min.required' => 'El stock mínimo es obligatorio.',
            'impresora_id.required' => 'Debe seleccionar una impresora compatible.',
            'fabricante_id.required' => 'Debe seleccionar un tipo/fabricante.',
            'colore_id.required' => 'Debe seleccionar un color.',
        ]);

        $cartucho = Cartucho::findOrFail($id);
        $cartucho->cartucho = trim($request->input('cartucho'));
        $cartucho->colore_id = $request->input('colore_id');
        $cartucho->stock_min = $request->input('stock_min');
        $cartucho->impresora_id = $request->input('impresora_id');
        $cartucho->fabricante_id = $request->input('fabricante_id');
        $cartucho->save();

        if ($request->wantsJson() || $request->ajax()) {
            $cartucho->load(['colore', 'impresora.gerencias.area', 'fabricante']);
            return response()->json([
                'success' => true,
                'message' => 'Consumible actualizado correctamente.',
                'data' => $cartucho
            ], 200);
        }

        return redirect()->route('administracion.consumibles.index', $cartucho->id)->with('success', 'Consumible actualizado correctamente');
    }

    public function destroy(Request $request, $id)
    {
        abort_if(Gate::denies('cartucho_destroy'), 403);

        try {
            $cartucho = Cartucho::findOrFail($id);

            // Verificar si tiene movimientos en almacenamiento o inventario
            if (method_exists($cartucho, 'almacenamientos') && $cartucho->almacenamientos()->exists()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el consumible porque tiene registros en almacenamiento.'
                    ], 422);
                }
                return back()->with('error', 'No se puede eliminar porque tiene registros asociados.');
            }

            $cartucho->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consumible eliminado correctamente.'
                ], 200);
            }

            return redirect()->route('administracion.consumibles.index')->with('status', 'Consumible Eliminado');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el consumible: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al eliminar el registro.');
        }
    }
}
