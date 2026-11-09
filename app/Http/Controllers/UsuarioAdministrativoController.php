<?php

namespace App\Http\Controllers;

use App\Models\UsuarioAdministrativo;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UsuarioAdministrativoController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('usuario_administrativo_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'usuario_administrativo');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['usuario_administrativo', 'id', 'unidad_administrativa_id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'usuario_administrativo';

        $query = UsuarioAdministrativo::query()->with('unidad_administrativa');

        // Filtros específicos
        if ($request->has('usuario') && $request->usuario) {
            $query->where('id', $request->usuario);
        }
        if ($request->has('unidad') && $request->unidad) {
            $query->where('unidad_administrativa_id', $request->unidad);
        }

        // Búsqueda en tiempo real
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('usuario_administrativo', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('unidad_administrativa', function ($sub) use ($search) {
                      $sub->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $usuario_administrativos = $query->orderBy($sort, $direction)->paginate(15);

        $todosLosUsuariosAdministrativos = UsuarioAdministrativo::orderBy('usuario_administrativo', 'asc')->get(['id', 'usuario_administrativo']);
        $todasLasUnidadAdministrativas = UnidadAdministrativa::orderBy('name', 'asc')->get(['id', 'name']);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'usuario_administrativos' => $usuario_administrativos,
                    'unidades_administrativas' => $todasLasUnidadAdministrativas,
                    'todos_los_usuarios' => $todosLosUsuariosAdministrativos,
                    'permissions' => [
                        'can_create' => Gate::allows('usuario_administrativo_create'),
                        'can_edit' => Gate::allows('usuario_administrativo_edit'),
                        'can_destroy' => Gate::allows('usuario_administrativo_destroy'),
                    ]
                ]
            ]);
        }

        return view('administracion.usuario_administrativo.index', [
            'usuario_administrativos' => $usuario_administrativos,
            'todasLasUnidadesAdministrativas' => $todasLasUnidadAdministrativas,
            'todosLosUsuariosAdministrativos' => $todosLosUsuariosAdministrativos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function show()
    {
        return view('administracion.usuario_administrativo.show', ['usuario_administrativos' => UsuarioAdministrativo::all()]);
    }

    public function create()
    {
        abort_if(Gate::denies('usuario_administrativo_create'), 403);
        return view('administracion.usuario_administrativo.create', ['unidad_administrativas' => UnidadAdministrativa::all()]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('usuario_administrativo_create'), 403);

        $request->validate([
            'usuario_administrativo' => 'required|unique:usuario_administrativos,usuario_administrativo',
            'unidad_administrativa_id' => 'required|exists:unidad_administrativas,id',
        ], [
            'usuario_administrativo.required' => 'El nombre del usuario administrativo es obligatorio.',
            'usuario_administrativo.unique' => 'Este usuario administrativo ya se encuentra registrado.',
            'unidad_administrativa_id.required' => 'Debe seleccionar una unidad administrativa.',
            'unidad_administrativa_id.exists' => 'La unidad administrativa seleccionada no es válida.',
        ]);

        $usuario_administrativo = new UsuarioAdministrativo;
        $usuario_administrativo->usuario_administrativo = $request->get('usuario_administrativo');
        $usuario_administrativo->unidad_administrativa_id = $request->get('unidad_administrativa_id');
        $usuario_administrativo->save();
        $usuario_administrativo->load('unidad_administrativa');

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario administrativo registrado exitosamente',
                'data' => $usuario_administrativo
            ], 200);
        }

        return redirect()->route('administracion.usuario_administrativo.index')->with('status', 'Usuario Creado');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('usuario_administrativo_edit'), 403);
        $usuario_administrativo = UsuarioAdministrativo::with('unidad_administrativa')->findOrFail($id);

        return view('administracion.usuario_administrativo.edit', [
            'usuario_administrativo' => $usuario_administrativo,
            'unidad_administrativas' => UnidadAdministrativa::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('usuario_administrativo_edit'), 403);

        $usuario_administrativo = UsuarioAdministrativo::findOrFail($id);
        $unidadId = $request->input('unidad_administrativa_id', $request->input('unidad_administrativa'));

        $request->merge(['unidad_administrativa_id' => $unidadId]);

        $request->validate([
            'usuario_administrativo' => 'required|unique:usuario_administrativos,usuario_administrativo,' . $id,
            'unidad_administrativa_id' => 'required|exists:unidad_administrativas,id',
        ], [
            'usuario_administrativo.required' => 'El nombre del usuario administrativo es obligatorio.',
            'usuario_administrativo.unique' => 'Este usuario administrativo ya se encuentra registrado.',
            'unidad_administrativa_id.required' => 'Debe seleccionar una unidad administrativa.',
            'unidad_administrativa_id.exists' => 'La unidad administrativa seleccionada no es válida.',
        ]);

        $usuario_administrativo->usuario_administrativo = $request->input('usuario_administrativo');
        $usuario_administrativo->unidad_administrativa_id = $unidadId;
        $usuario_administrativo->save();
        $usuario_administrativo->load('unidad_administrativa');

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario administrativo actualizado correctamente',
                'data' => $usuario_administrativo
            ], 200);
        }

        return redirect()->route('administracion.usuario_administrativo.index', $usuario_administrativo->id)->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('usuario_administrativo_destroy'), 403);
        $usuario_administrativo = UsuarioAdministrativo::findOrFail($id);
        $usuario_administrativo->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario administrativo eliminado correctamente'
            ], 200);
        }

        return redirect()->route('administracion.usuario_administrativo.index')->with('Usuario Eliminado');
    }
}
