<?php

namespace App\Http\Controllers;

use App\Models\Fabricante;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class FabricanteController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('fabricante_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'fabricante');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['id', 'fabricante', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'fabricante';
        }

        $query = Fabricante::query();

        if ($search) {
            $query->where('fabricante', 'LIKE', '%' . $search . '%');
        }

        if ($request->filled('fabricante_id')) {
            $query->where('id', $request->input('fabricante_id'));
        }

        $fabricantes = $query->orderBy($sort, $direction)->paginate(15);
        $all_fabricantes = Fabricante::orderBy('fabricante')->get();

        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'fabricantes' => $fabricantes,
                    'all_fabricantes' => $all_fabricantes,
                    'permissions' => [
                        'can_create' => auth()->user()?->can('fabricante_create') ?? false,
                        'can_edit' => auth()->user()?->can('fabricante_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('fabricante_destroy') ?? false,
                    ]
                ],
                'message' => 'Tipos de impresoras obtenidos exitosamente'
            ], 200);
        }

        return view('administracion.tipo_impresoras.index', [
            'fabricantes' => $fabricantes,
            'all_fabricantes' => $all_fabricantes,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('fabricante_create'), 403);
        return view('administracion.tipo_impresoras.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('fabricante_create'), 403);

        $request->validate([
            'fabricante' => 'required|string|max:191|unique:fabricantes,fabricante',
        ], [
            'fabricante.required' => 'El tipo de impresora es obligatorio.',
            'fabricante.unique' => 'Este tipo de impresora ya se encuentra registrado.',
        ]);

        $fabricante = new Fabricante;
        $fabricante->fabricante = trim($request->fabricante);
        $fabricante->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de impresora registrado exitosamente.',
                'data' => $fabricante
            ], 201);
        }

        return redirect()->route('administracion.tipo_impresoras.index')->with('status', 'tipo consumible creado');
    }

    public function edit(Fabricante $tipo_impresora)
    {
        abort_if(Gate::denies('fabricante_edit'), 403);
        return view('administracion.tipo_impresoras.edit', compact('tipo_impresora'));
    }

    public function update(Request $request, Fabricante $tipo_impresora)
    {
        abort_if(Gate::denies('fabricante_edit'), 403);

        $request->validate([
            'fabricante' => 'required|string|max:191|unique:fabricantes,fabricante,' . $tipo_impresora->id,
        ], [
            'fabricante.required' => 'El tipo de impresora es obligatorio.',
            'fabricante.unique' => 'Este tipo de impresora ya se encuentra registrado.',
        ]);

        $tipo_impresora->update([
            'fabricante' => trim($request->fabricante)
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tipo de impresora actualizado correctamente.',
                'data' => $tipo_impresora
            ], 200);
        }

        return redirect()->route('administracion.tipo_impresoras.index')->with('success', 'Tipo consumible actualizado correctamente');
    }

    public function destroy(Request $request, Fabricante $tipo_impresora)
    {
        abort_if(Gate::denies('fabricante_destroy'), 403);

        try {
            // Validar si tiene consumibles asociados
            if ($tipo_impresora->cartuchos()->exists()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar porque tiene consumibles asociados.'
                    ], 422);
                }
                return back()->with('error', 'No se puede eliminar porque tiene consumibles asociados.');
            }

            $tipo_impresora->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tipo de impresora eliminado correctamente.'
                ], 200);
            }

            return redirect()->route('administracion.tipo_impresoras.index')->with('status', 'Tipo impresora eliminado');
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
