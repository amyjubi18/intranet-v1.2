<?php

namespace App\Http\Controllers;
use App\Models\FormaAdquisicion;
use App\Http\Requests\SaveFormaAdquiRequest;

use Illuminate\Http\Request;

class FormaAdquisicionController extends Controller
{
    public function index(Request $request){

        /*         abort_if(Gate::denies('categorias_index'), 403);
         */
    $forma_adquisicions = FormaAdquisicion::paginate(5);

    return view('administracion.forma_adquisicion.index', ['forma_adquisicions'=> $forma_adquisicions]);
    }
    public function show(){
        return view('administracion.forma_adquisicion.show', ['forma_adquisicion' => FormaAdquisicion::get()]);
    }
    public function create(FormaAdquisicion $forma_adquisicion){
        /* abort_if(Gate::denies('categorias_create'), 403); */
        return view('administracion.forma_adquisicion.create',['forma_adquisicion'=>$forma_adquisicion]);
    }
    public function store(SaveFormaAdquiRequest $request){

        FormaAdquisicion::create($request->validated());
        return redirect()->route('administracion.forma_adquisicion.create')->with('status', 'Forma de Adquisición creada');
    }
    public function edit(FormaAdquisicion $forma_adquisicion)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.forma_adquisicion.edit', compact('forma_adquisicion'));
    }
    public function update(Request $request, FormaAdquisicion $forma_adquisicion)
    {
       $forma_adquisicion->update($request->only('forma_adquisicion'));
        return redirect()->route('administracion.forma_adquisicion.index');
    }
    public function destroy(FormaAdquisicion $forma_adquisicion)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $forma_adquisicion->delete();
        return redirect()->route('administracion.forma_adquisicion.index')->with('Forma de adquisición Eliminada');
    }
}
