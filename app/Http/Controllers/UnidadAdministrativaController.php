<?php

namespace App\Http\Controllers;
use App\Models\UnidadAdministrativa;
use App\Http\Requests\SaveUniAdminRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class UnidadAdministrativaController extends Controller
{
    public function index(Request $request){

/*         abort_if(Gate::denies('categorias_index'), 403);
 */
       $unidad_administrativas = UnidadAdministrativa::paginate(5);

        return view('administracion.unidad_administrativa.index', ['unidad_administrativas'=> $unidad_administrativas]);
    }
    public function show(){
        return view('administracion.unidad_administrativa.show', ['unidad_administrativa' => UnidadAdministrativa::get()]);
    }
    public function create(Request $request, UnidadAdministrativa $unidad_administrativa){
        /* abort_if(Gate::denies('categorias_create'), 403); */

        return view('administracion.unidad_administrativa.create',['unidad_administrativa'=>$unidad_administrativa]);
    }
    public function store(SaveUniAdminRequest $request){

        /* UnidadAdministrativa::create($request->only('name'));
        return redirect()->route('administracion.unidad_administrativa.index'); */
         UnidadAdministrativa::create($request->validated());
        return redirect()->route('administracion.unidad_administrativa.index')->with('status', 'Unidad administrativa creada');
    }
    public function edit(UnidadAdministrativa $unidad_administrativa)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.unidad_administrativa.edit', compact('unidad_administrativa'));
    }
    public function update(Request $request, UnidadAdministrativa $unidad_administrativa)
    {
       $unidad_administrativa->update($request->only('unidad_administrativa'));
        return redirect()->route('administracion.unidad_administrativa.index');
    }
    public function destroy(UnidadAdministrativa $unidad_administrativa)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $unidad_administrativa->delete();
        return redirect()->route('administracion.unidad_administrativa.index')->with('Unidad administrativa Eliminada');
    }
}
