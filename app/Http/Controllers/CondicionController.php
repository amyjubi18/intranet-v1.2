<?php

namespace App\Http\Controllers;

use App\Models\Condicion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CondicionController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('condicion_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'condicion');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['condicion', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'condicion';

        $query = DB::table('condicions')
            ->select('id', 'condicion');

        if (!empty($search)) {
            $query->where('condicion', 'LIKE', '%' . $search . '%');
        }

        $condicions = $query->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'condicions' => $condicions,
                    'permissions' => [
                        'can_create' => Gate::allows('condicion_create'),
                        'can_edit' => Gate::allows('condicion_edit'),
                        'can_destroy' => Gate::allows('condicion_destroy'),
                    ]
                ]
            ]);
        }

        $todasLasCondiciones = Condicion::all();
        return view('administracion.condicion_fisica.index', compact('condicions', 'search', 'sort', 'direction', 'todasLasCondiciones'));
    }

    public function show()
    {
        return view('administracion.condicion_fisica.show', ['condicions' => Condicion::get()]);
    }

    public function create(Condicion $condicion)
    {
        abort_if(Gate::denies('condicion_create'), 403);
        return view('administracion.condicion_fisica.create', ['condicion' => $condicion]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('condicion_create'), 403);

        $request->validate([
            'condicion' => 'required|unique:condicions,condicion',
        ], [
            'condicion.required' => 'El nombre de la condición física es obligatorio.',
            'condicion.unique' => 'Esta condición física ya se encuentra registrada.',
        ]);

        $condicion = new Condicion;
        $condicion->condicion = $request->condicion;
        $condicion->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Condición física creada exitosamente',
                'data' => $condicion
            ], 200);
        }

        return redirect()->route('administracion.condicion_fisica.index')->with('status', 'Condición Física creada');
    }

    public function edit(Condicion $condicion)
    {
        abort_if(Gate::denies('condicion_edit'), 403);
        return view('administracion.condicion_fisica.edit', compact('condicion'));
    }

    public function update(Request $request, Condicion $condicion)
    {
        abort_if(Gate::denies('condicion_edit'), 403);

        $request->validate([
            'condicion' => 'required|unique:condicions,condicion,' . $condicion->id,
        ], [
            'condicion.required' => 'El nombre de la condición física es obligatorio.',
            'condicion.unique' => 'Esta condición física ya se encuentra registrada.',
        ]);

        $condicion->update($request->only('condicion'));

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Condición física actualizada exitosamente',
                'data' => $condicion
            ], 200);
        }

        return redirect()->route('administracion.condicion_fisica.index');
    }

    public function destroy(Condicion $condicion)
    {
        abort_if(Gate::denies('condicion_destroy'), 403);
        $condicion->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Condición física eliminada correctamente'
            ], 200);
        }

        return redirect()->route('administracion.condicion_fisica.index')->with('Condición Física Eliminado');
    }
}
