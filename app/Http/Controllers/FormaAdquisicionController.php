<?php

namespace App\Http\Controllers;

use App\Models\FormaAdquisicion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FormaAdquisicionController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('forma_adquisicion_index'), 403);

        $search = trim($request->get('search', '')) ?: trim($request->get('forma_adquisicion', ''));
        $sort = $request->get('sort', 'forma_adquisicion');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['forma_adquisicion', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'forma_adquisicion';

        $query = DB::table('forma_adquisicions')
            ->select('id', 'forma_adquisicion');

        if (!empty($search)) {
            $query->where('forma_adquisicion', 'LIKE', '%' . $search . '%');
        }

        $forma_adquisicions = $query->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'forma_adquisicions' => $forma_adquisicions,
                    'permissions' => [
                        'can_create' => Gate::allows('forma_adquisicion_create'),
                        'can_edit' => Gate::allows('forma_adquisicion_edit'),
                        'can_destroy' => Gate::allows('forma_adquisicion_destroy'),
                    ]
                ]
            ]);
        }

        $todasLasFormasAdquisicion = FormaAdquisicion::all();
        return view('administracion.forma_adquisicion.index', compact('forma_adquisicions', 'search', 'sort', 'direction', 'todasLasFormasAdquisicion'));
    }

    public function show()
    {
        return view('administracion.forma_adquisicion.show', ['forma_adquisicions' => FormaAdquisicion::get()]);
    }

    public function create(FormaAdquisicion $forma_adquisicion)
    {
        abort_if(Gate::denies('forma_adquisicion_create'), 403);
        return view('administracion.forma_adquisicion.create', ['forma_adquisicion' => $forma_adquisicion]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('forma_adquisicion_create'), 403);

        $request->validate([
            'forma_adquisicion' => 'required|unique:forma_adquisicions,forma_adquisicion',
        ], [
            'forma_adquisicion.required' => 'El nombre de la forma de adquisición es obligatorio.',
            'forma_adquisicion.unique' => 'Esta forma de adquisición ya se encuentra registrada.',
        ]);

        $forma_adquisicion = new FormaAdquisicion;
        $forma_adquisicion->forma_adquisicion = $request->forma_adquisicion;
        $forma_adquisicion->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Forma de Adquisición creada exitosamente',
                'data' => $forma_adquisicion
            ], 200);
        }

        return redirect()->route('administracion.forma_adquisicion.index')->with('status', 'Forma Adquisición creada');
    }

    public function edit(FormaAdquisicion $forma_adquisicion)
    {
        abort_if(Gate::denies('forma_adquisicion_edit'), 403);
        return view('administracion.forma_adquisicion.edit', compact('forma_adquisicion'));
    }

    public function update(Request $request, FormaAdquisicion $forma_adquisicion)
    {
        abort_if(Gate::denies('forma_adquisicion_edit'), 403);

        $request->validate([
            'forma_adquisicion' => 'required|unique:forma_adquisicions,forma_adquisicion,' . $forma_adquisicion->id,
        ], [
            'forma_adquisicion.required' => 'El nombre de la forma de adquisición es obligatorio.',
            'forma_adquisicion.unique' => 'Esta forma de adquisición ya se encuentra registrada.',
        ]);

        $forma_adquisicion->update($request->only('forma_adquisicion'));

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Forma de Adquisición actualizada exitosamente',
                'data' => $forma_adquisicion
            ], 200);
        }

        return redirect()->route('administracion.forma_adquisicion.index');
    }

    public function destroy(FormaAdquisicion $forma_adquisicion)
    {
        abort_if(Gate::denies('forma_adquisicion_destroy'), 403);
        $forma_adquisicion->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Forma de Adquisición eliminada correctamente'
            ], 200);
        }

        return redirect()->route('administracion.forma_adquisicion.index')->with('Forma Adquisición Eliminada');
    }
}
