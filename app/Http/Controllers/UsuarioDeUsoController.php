<?php

namespace App\Http\Controllers;
use App\Models\UsuarioDeUso;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class UsuarioDeUsoController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('usuario_de_uso_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'usuario_de_uso');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = UsuarioDeUso::query()->with('unidad_administrativa');

        //filtros específicos
        if ($request->has('usuario') && $request->usuario) {
            $query->where('id', $request->usuario);
        }
        if ($request->has('unidad') && $request->unidad) {
            $query->where('unidad_administrativa_id', $request->unidad);
        }

        // Búsqueda simple
        if ($search) {
            $query->where('usuario_de_uso', 'LIKE', '%' . $search . '%')
                ->orWhereHas('unidad_administrativa', function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%');
                });
        }

        $usuario_de_usos = $query->orderBy($sort, $direction)->paginate(15);

        $todosLosUsuariosDeUso = UsuarioDeUso::orderBy('usuario_de_uso', 'asc')->get();
        $todasLasUnidadesAdministrativas = UnidadAdministrativa::orderBy('name', 'asc')->get();

        return view('administracion.usuario_de_uso.index',[
            'usuario_de_usos' => $usuario_de_usos,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'todasLasUnidadesAdministrativas' => $todasLasUnidadesAdministrativas,
            'todosLosUsuariosDeUso' => $todosLosUsuariosDeUso
        ]);
    }

    public function create(){
         abort_if(Gate::denies('usuario_de_uso_create'), 403);
         //dd($color);
        return view('administracion.usuario_de_uso.create',['unidad_administrativas'=> UnidadAdministrativa::all() ]);
    }

    public function store(Request $request){
        //dd($color);
        $request->validate([
            'usuario_de_uso' => 'required|unique:usuario_de_usos',
            'unidad_administrativa_id' => 'required',

        ]);
        //color::create($request->validated());
        $usuario_de_uso = new UsuarioDeUso;
        $usuario_de_uso->id = $request->get('id');
        $usuario_de_uso->usuario_de_uso = $request->get('usuario_de_uso');
        $usuario_de_uso->unidad_administrativa_id = $request->get('unidad_administrativa_id');
        $usuario_de_uso->save();
        return redirect()->route('administracion.usuario_de_uso.index')->with('status', 'Usuario Creado');

    }

    public function edit($id)
    {
        abort_if(Gate::denies('usuario_de_uso_edit'), 403);
        $usuario_de_uso = UsuarioDeUso::find($id);

        return view('administracion.usuario_de_uso.edit', ['usuario_de_uso' => $usuario_de_uso, 'unidad_administrativas' => UnidadAdministrativa::all()]);
    }

    public function update(Request $request, $id)
    {


        $validation = $request->validate([
            'usuario_de_uso' => 'required',
            'unidad_administrativa_id' => 'required',

        ]);

        $usuario_de_uso = UsuarioDeUso::find($id);
        $usuario_de_uso->usuario_de_uso = $request->input('usuario_de_uso');
        $usuario_de_uso->unidad_administrativa_id = $request->input('unidad_administrativa_id');
        $usuario_de_uso->save();

        return redirect()->route('administracion.usuario_de_uso.index', $usuario_de_uso->id)->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
         abort_if(Gate::denies('usuario_de_uso_destroy'), 403);
         $usuario_de_uso = UsuarioDeUso::find($id);
        $usuario_de_uso->delete();
        return redirect()->route('administracion.usuario_de_uso.index')->with('Usuario Eliminado');
    }
}
