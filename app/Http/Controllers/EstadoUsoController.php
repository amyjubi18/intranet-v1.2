<?php

namespace App\Http\Controllers;

use App\Models\EstadoDelUso;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EstadoUsoController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('estado_uso_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'estado_del_uso');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['estado_del_uso', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'estado_del_uso';

        $query = DB::table('estado_del_usos')
            ->select('id', 'estado_del_uso');

        if (!empty($search)) {
            $query->where('estado_del_uso', 'LIKE', '%' . $search . '%');
        }

        $estado_del_usos = $query->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'estado_del_usos' => $estado_del_usos,
                    'permissions' => [
                        'can_create' => Gate::allows('estado_uso_create'),
                        'can_edit' => Gate::allows('estado_uso_edit'),
                        'can_destroy' => Gate::allows('estado_uso_destroy'),
                    ]
                ]
            ]);
        }

        $todosLosEstadosDelUso = EstadoDelUso::all();
        return view('administracion.estado_del_uso.index', compact('estado_del_usos', 'search', 'sort', 'direction', 'todosLosEstadosDelUso'));
    }

    public function show()
    {
        return view('administracion.estado_del_uso.show', ['estado_del_usos' => EstadoDelUso::get()]);
    }

    public function create(EstadoDelUso $estado_del_uso)
    {
        abort_if(Gate::denies('estado_uso_create'), 403);
        return view('administracion.estado_del_uso.create', ['estado_del_uso' => $estado_del_uso]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('estado_uso_create'), 403);

        $request->validate([
            'estado_del_uso' => 'required|unique:estado_del_usos,estado_del_uso',
        ], [
            'estado_del_uso.required' => 'El nombre del estado del uso es obligatorio.',
            'estado_del_uso.unique' => 'Este estado del uso ya se encuentra registrado.',
        ]);

        $estado_del_uso = new EstadoDelUso;
        $estado_del_uso->estado_del_uso = $request->estado_del_uso;
        $estado_del_uso->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Estado del uso creado exitosamente',
                'data' => $estado_del_uso
            ], 200);
        }

        return redirect()->route('administracion.estado_del_uso.index')->with('status', 'Estado del uso creado');
    }

    public function edit(EstadoDelUso $estado_del_uso)
    {
        abort_if(Gate::denies('estado_uso_edit'), 403);
        return view('administracion.estado_del_uso.edit', compact('estado_del_uso'));
    }

    public function update(Request $request, EstadoDelUso $estado_del_uso)
    {
        abort_if(Gate::denies('estado_uso_edit'), 403);

        $request->validate([
            'estado_del_uso' => 'required|unique:estado_del_usos,estado_del_uso,' . $estado_del_uso->id,
        ], [
            'estado_del_uso.required' => 'El nombre del estado del uso es obligatorio.',
            'estado_del_uso.unique' => 'Este estado del uso ya se encuentra registrado.',
        ]);

        $estado_del_uso->update($request->only('estado_del_uso'));

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Estado del uso actualizado exitosamente',
                'data' => $estado_del_uso
            ], 200);
        }

        return redirect()->route('administracion.estado_del_uso.index');
    }

    public function destroy(EstadoDelUso $estado_del_uso)
    {
        abort_if(Gate::denies('estado_uso_destroy'), 403);
        $estado_del_uso->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Estado del uso eliminado correctamente'
            ], 200);
        }

        return redirect()->route('administracion.estado_del_uso.index')->with('Estado del uso Eliminado');
    }
}
