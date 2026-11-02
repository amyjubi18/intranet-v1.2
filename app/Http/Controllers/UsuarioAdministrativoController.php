<?php

namespace App\Http\Controllers;
use App\Models\UsuarioAdministrativo;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class UsuarioAdministrativoController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('usuario_administrativo_index'), 403);


        $usuario_administrativo = UsuarioAdministrativo::query()->with('unidad_administrativa');

        //filtros específicos
        if ($request->has('usuario') && $request->usuario) {
            $usuario_administrativo->where('id', $request->usuario);
        }
        if ($request->has('unidad') && $request->unidad) {
            $usuario_administrativo->where('unidad_administrativa_id', $request->unidad);
        }
        //manejar búsqueda simple (fallback)
        $usuario_administrativo->when(request('search'), function ($query) {
            return $query->where('usuario_administrativo', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('unidad_administrativa', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        $usuario_administrativos = $usuario_administrativo->orderBy('usuario_administrativo', 'asc')->paginate(10);

        $todosLosUsuariosAdministrativos = UsuarioAdministrativo::orderBy('usuario_administrativo', 'asc')->get();
        $todasLasUnidadAdministrativas = UnidadAdministrativa::orderBy('name', 'asc')->get();
        /* $usuario_administrativos = UsuarioAdministrativo::query()->with('unidad_administrativa')->when(request('search'), function ($query) {
            return $query->where('usuario_administrativo', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('unidad_administrativa', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        })->latest()->paginate(15); */


        return view('administracion.usuario_administrativo.index',[
            'usuario_administrativos' => $usuario_administrativos, 'todasLasUnidadesAdministrativas' => $todasLasUnidadAdministrativas, 'todosLosUsuariosAdministrativos' => $todosLosUsuariosAdministrativos
        ]);

    }
     public function show(){
        return view('administracion.usuario_administrativo.show', ['usuario_administrativos' => UsuarioAdministrativo::get()]);
    }
    public function create(){
         abort_if(Gate::denies('usuario_administrativo_create'), 403);
         //dd($color);
        return view('administracion.usuario_administrativo.create',['unidad_administrativas'=> UnidadAdministrativa::all() ]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'usuario_administrativo' => 'required|unique:usuario_administrativos',
            'unidad_administrativa_id' => 'required',

        ]);
        //color::create($request->validated());
        $usuario_administrativo = new UsuarioAdministrativo;
        $usuario_administrativo->id = $request->get('id');
        $usuario_administrativo->usuario_administrativo = $request->get('usuario_administrativo');
        $usuario_administrativo->unidad_administrativa_id = $request->get('unidad_administrativa_id');
        $usuario_administrativo->save();
        return redirect()->route('administracion.usuario_administrativo.index')->with('status', 'Usuario Creado');

    }
    public function edit($id)
    {
        abort_if(Gate::denies('usuario_administrativo_edit'), 403);
        $usuario_administrativo = UsuarioAdministrativo::find($id);

        return view('administracion.usuario_administrativo.edit', ['usuario_administrativo' => $usuario_administrativo, 'unidad_administrativas' => UnidadAdministrativa::all()]);
    }
    public function update(Request $request, $id)
    {


        $validation = $request->validate([
            'usuario_administrativo' => 'required',
            'unidad_administrativa' => 'required',

        ]);

        $usuario_administrativo = UsuarioAdministrativo::find($id);
        $usuario_administrativo->usuario_administrativo =$request->input('usuario_administrativo');
        $usuario_administrativo->unidad_administrativa_id =$request->input('unidad_administrativa');
        $usuario_administrativo->save();
        DB::commit();

        return redirect()->route('administracion.usuario_administrativo.index', $usuario_administrativo->id)->with('success', 'Bien actualizado correctamente');
    }
    public function destroy($id)
    {
         abort_if(Gate::denies('usuario_administrativo_destroy'), 403);
         $usuario_administrativo = UsuarioAdministrativo::find($id);
        $usuario_administrativo->delete();
        return redirect()->route('administracion.usuario_administrativo.index')->with('Usuario Eliminado');
    }
}
