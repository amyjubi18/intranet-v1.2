<?php

namespace App\Http\Controllers;
use App\Models\EstadoDelUso;
use App\Http\Requests\SaveEstadoUsoRequest;

use Illuminate\Http\Request;

class EstadoUsoController extends Controller
{
    public function index(Request $request){

        /*         abort_if(Gate::denies('categorias_index'), 403);
         */
    $estado_del_usos = EstadoDelUso::paginate(5);

    return view('administracion.estado_del_uso.index', ['estado_del_usos'=> $estado_del_usos]);
    }
    public function show(){
        return view('administracion.estado_del_uso.show', ['estado_del_uso' => EstadoDelUso::get()]);
    }
    public function create(EstadoDelUso $estado_del_uso){
        /* abort_if(Gate::denies('categorias_create'), 403); */
        return view('administracion.estado_del_uso.create',['estado_del_uso'=>$estado_del_uso]);
    }
    public function store(SaveEstadoUsoRequest $request){

        EstadoDelUso::create($request->validated());
        return redirect()->route('administracion.estado_del_uso.create')->with('status', 'Estado del Uso creada');
    }
    public function edit(EstadoDelUso $estado_del_uso)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.estado_del_uso.edit', compact('estado_del_uso'));
    }
    public function update(Request $request, EstadoDelUso $estado_del_uso)
    {
       $estado_del_uso->update($request->only('estado_del_uso'));
        return redirect()->route('administracion.estado_del_uso.index');
    }
    public function destroy(EstadoDelUso $estado_del_uso)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $estado_del_uso->delete();
        return redirect()->route('administracion.estado_del_uso.index')->with('Estado del Uso Eliminado');
    }
}
