<?php

namespace App\Http\Controllers;

use App\Models\Colore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class ColoresController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('colores_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'colore');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['id', 'colore', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'colore';
        }

        $query = Colore::query();

        if ($search) {
            $query->where('colore', 'LIKE', '%' . $search . '%');
        }

        if ($request->filled('colore_id')) {
            $query->where('id', $request->input('colore_id'));
        }

        $colores = $query->orderBy($sort, $direction)->paginate(15);
        $all_colores = Colore::orderBy('colore')->get();

        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'colores' => $colores,
                    'all_colores' => $all_colores,
                    'permissions' => [
                        'can_create' => auth()->user()?->can('colores_create') ?? false,
                        'can_edit' => auth()->user()?->can('colores_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('colores_destroy') ?? false,
                    ]
                ],
                'message' => 'Tipos de consumibles obtenidos exitosamente'
            ], 200);
        }

        return view('administracion.tipo_consumibles.index', [
            'colores' => $colores,
            'all_colores' => $all_colores,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('colores_create'), 403);
        return view('administracion.tipo_consumibles.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('colores_create'), 403);

        $request->validate([
            'colore' => 'required|string|max:191|unique:colores,colore',
        ], [
            'colore.required' => 'El tipo de consumible es obligatorio.',
            'colore.unique' => 'Este tipo de consumible ya se encuentra registrado.',
        ]);

        $colore = new Colore;
        $colore->colore = trim($request->colore);
        $colore->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de consumible registrado exitosamente.',
                'data' => $colore
            ], 201);
        }

        return redirect()->route('administracion.tipo_consumibles.index')->with('status', 'Color creado');
    }

    public function edit(Colore $tipo_consumible)
    {
        abort_if(Gate::denies('colores_edit'), 403);
        return view('administracion.tipo_consumibles.edit', compact('tipo_consumible'));
    }

    public function update(Request $request, Colore $tipo_consumible)
    {
        abort_if(Gate::denies('colores_edit'), 403);

        $request->validate([
            'colore' => 'required|string|max:191|unique:colores,colore,' . $tipo_consumible->id,
        ], [
            'colore.required' => 'El tipo de consumible es obligatorio.',
            'colore.unique' => 'Este tipo de consumible ya se encuentra registrado.',
        ]);

        $tipo_consumible->update([
            'colore' => trim($request->colore)
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de consumible actualizado correctamente.',
                'data' => $tipo_consumible
            ], 200);
        }

        return redirect()->route('administracion.tipo_consumibles.index')->with('success', 'Tipo de consumible actualizado');
    }

    public function destroy(Request $request, Colore $tipo_consumible)
    {
        abort_if(Gate::denies('colores_destroy'), 403);

        try {
            // Validar si tiene consumibles asociados
            if ($tipo_consumible->cartuchos()->exists()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar porque tiene consumibles asociados.'
                    ], 422);
                }
                return back()->with('error', 'No se puede eliminar porque tiene consumibles asociados.');
            }

            $tipo_consumible->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de consumible eliminado correctamente.'
                ], 200);
            }

            return redirect()->route('administracion.tipo_consumibles.index')->with('status', 'Color Eliminado');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al eliminar el registro.');
        }
    }
}
