<?php

namespace App\Http\Controllers;
use App\Models\EstadoDelUso;
use App\Http\Requests\SaveEstadoUsoRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class EstadoUsoController extends Controller
{

    public function index(Request $request){

        abort_if(Gate::denies('estado_uso_index'), 403);

        $search = trim($request->get('search'));
        $estado_del_usos = DB::table('estado_del_usos')
            ->select('id', 'estado_del_uso')
            ->where('estado_del_uso', 'LIKE', '%'.$search.'%')
            ->orderBy('estado_del_uso', 'asc')
            ->paginate(15);
        $todosLosEstadosDelUso = EstadoDelUso::all();
        return view('administracion.estado_del_uso.index', compact('estado_del_usos', 'search', 'todosLosEstadosDelUso'));

    }
     public function show(){
        return view('administracion.estado_del_uso.show', ['estado_del_usos' => EstadoDelUso::get()]);
    }
    public function create(EstadoDelUso $estado_del_uso){
         abort_if(Gate::denies('estado_uso_create'), 403);
         //dd($color);
        return view('administracion.estado_del_uso.create',['estado_del_uso'=> $estado_del_uso]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'estado_del_uso' => 'required|unique:estado_del_usos',

        ]);
        //color::create($request->validated());
         $estado_del_uso = new EstadoDelUso;
        $estado_del_uso->estado_del_uso = $request->estado_del_uso;
        $estado_del_uso->save();
        return redirect()->route('administracion.estado_del_uso.index')->with('status', 'Estado del uso creado');

    }
    public function edit(EstadoDelUso $estado_del_uso)
    {
         abort_if(Gate::denies('estado_uso_edit'), 403);
        return view('administracion.estado_del_uso.edit', compact('estado_del_uso'));
    }
    public function update(Request $request, EstadoDelUso $estado_del_uso)
    {
       $estado_del_uso->update($request->only('estado_del_uso'));
        return redirect()->route('administracion.estado_del_uso.index');
    }
    public function destroy(EstadoDelUso $estado_del_uso)
    {
         abort_if(Gate::denies('estado_uso_destroy'), 403);
        $estado_del_uso->delete();
        return redirect()->route('administracion.estado_del_uso.index')->with('Estado del uso Eliminado');
    }
}
