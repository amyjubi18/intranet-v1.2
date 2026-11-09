<?php

namespace App\Http\Controllers;

use App\Models\UsuarioDeUso;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UsuarioDeUsoController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('usuario_de_uso_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'usuario_de_uso');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['usuario_de_uso', 'id', 'unidad_administrativa_id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'usuario_de_uso';

        $query = UsuarioDeUso::query()->with('unidad_administrativa');

        // Filtros específicos
        if ($request->has('usuario') && $request->usuario) {
            $query->where('id', $request->usuario);
        }
        if ($request->has('unidad') && $request->unidad) {
            $query->where('unidad_administrativa_id', $request->unidad);
        }

        // Búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('usuario_de_uso', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('unidad_administrativa', function ($sub) use ($search) {
                      $sub->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $usuario_de_usos = $query->orderBy($sort, $direction)->paginate(15);

        $todosLosUsuariosDeUso = UsuarioDeUso::orderBy('usuario_de_uso', 'asc')->get(['id', 'usuario_de_uso']);
        $todasLasUnidadesAdministrativas = UnidadAdministrativa::orderBy('name', 'asc')->get(['id', 'name']);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'usuario_de_usos' => $usuario_de_usos,
                    'unidades_administrativas' => $todasLasUnidadesAdministrativas,
                    'todos_los_usuarios' => $todosLosUsuariosDeUso,
                    'permissions' => [
                        'can_create' => Gate::allows('usuario_de_uso_create'),
                        'can_edit' => Gate::allows('usuario_de_uso_edit'),
                        'can_destroy' => Gate::allows('usuario_de_uso_destroy'),
                    ]
                ]
            ]);
        }

        return view('administracion.usuario_de_uso.index', [
            'usuario_de_usos' => $usuario_de_usos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'todasLasUnidadesAdministrativas' => $todasLasUnidadesAdministrativas,
            'todosLosUsuariosDeUso' => $todosLosUsuariosDeUso
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('usuario_de_uso_create'), 403);
        return view('administracion.usuario_de_uso.create', ['unidad_administrativas' => UnidadAdministrativa::all()]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('usuario_de_uso_create'), 403);

        $request->validate([
            'usuario_de_uso' => 'required|unique:usuario_de_usos,usuario_de_uso',
            'unidad_administrativa_id' => 'required|exists:unidad_administrativas,id',
        ], [
            'usuario_de_uso.required' => 'El nombre del usuario de uso es obligatorio.',
            'usuario_de_uso.unique' => 'Este usuario de uso ya se encuentra registrado.',
            'unidad_administrativa_id.required' => 'Debe seleccionar una unidad administrativa.',
            'unidad_administrativa_id.exists' => 'La unidad administrativa seleccionada no es válida.',
        ]);

        $usuario_de_uso = new UsuarioDeUso;
        $usuario_de_uso->usuario_de_uso = $request->get('usuario_de_uso');
        $usuario_de_uso->unidad_administrativa_id = $request->get('unidad_administrativa_id');
        $usuario_de_uso->save();
        $usuario_de_uso->load('unidad_administrativa');

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario de uso registrado exitosamente',
                'data' => $usuario_de_uso
            ], 200);
        }

        return redirect()->route('administracion.usuario_de_uso.index')->with('status', 'Usuario Creado');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('usuario_de_uso_edit'), 403);
        $usuario_de_uso = UsuarioDeUso::with('unidad_administrativa')->findOrFail($id);

        return view('administracion.usuario_de_uso.edit', [
            'usuario_de_uso' => $usuario_de_uso,
            'unidad_administrativas' => UnidadAdministrativa::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('usuario_de_uso_edit'), 403);

        $usuario_de_uso = UsuarioDeUso::findOrFail($id);

        $request->validate([
            'usuario_de_uso' => 'required|unique:usuario_de_usos,usuario_de_uso,' . $id,
            'unidad_administrativa_id' => 'required|exists:unidad_administrativas,id',
        ], [
            'usuario_de_uso.required' => 'El nombre del usuario de uso es obligatorio.',
            'usuario_de_uso.unique' => 'Este usuario de uso ya se encuentra registrado.',
            'unidad_administrativa_id.required' => 'Debe seleccionar una unidad administrativa.',
            'unidad_administrativa_id.exists' => 'La unidad administrativa seleccionada no es válida.',
        ]);

        $usuario_de_uso->usuario_de_uso = $request->input('usuario_de_uso');
        $usuario_de_uso->unidad_administrativa_id = $request->input('unidad_administrativa_id');
        $usuario_de_uso->save();
        $usuario_de_uso->load('unidad_administrativa');

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario de uso actualizado correctamente',
                'data' => $usuario_de_uso
            ], 200);
        }

        return redirect()->route('administracion.usuario_de_uso.index', $usuario_de_uso->id)->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('usuario_de_uso_destroy'), 403);
        $usuario_de_uso = UsuarioDeUso::findOrFail($id);
        $usuario_de_uso->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Usuario de uso eliminado correctamente'
            ], 200);
        }

        return redirect()->route('administracion.usuario_de_uso.index')->with('Usuario Eliminado');
    }
}
