<?php

namespace App\Http\Controllers;
use App\Models\UnidadAdministrativa;
use App\Http\Requests\SaveUniAdminRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UnidadAdministrativaController extends Controller
{
    

    public function index(Request $request){

        abort_if(Gate::denies('unidad_adminis_index'), 403);

        $search =trim($request->get('search'));
        
        $unidad_administrativas = DB::table('unidad_administrativas')->select('id', 'name')->where('name', 'LIKE', '%'.$search.'%')->orderBy('name', 'asc')->paginate(15);
        return view('administracion.unidad_administrativa.index', compact('unidad_administrativas', 'search'));

    }
     public function show(){
        return view('administracion.unidad_administrativa.show', ['unidad_administrativas' => UnidadAdministrativa::get()]);
    }
    public function create(UnidadAdministrativa $unidad_administrativa){
         abort_if(Gate::denies('unidad_adminis_create'), 403);
         //dd($color);
        return view('administracion.unidad_administrativa.create',['unidad_administrativa'=> $unidad_administrativa]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'name' => 'required|unique:unidad_administrativas',

        ]);
        //color::create($request->validated());
         $unidad_administrativa = new UnidadAdministrativa;
        $unidad_administrativa->name = $request->name;
        $unidad_administrativa->save();
        return redirect()->route('administracion.unidad_administrativa.index')->with('status', 'Unidad Administrativa creada');

    }
    public function edit(UnidadAdministrativa $unidad_administrativa)
    {
         abort_if(Gate::denies('unidad_adminis_edit'), 403);
        return view('administracion.unidad_administrativa.edit', compact('unidad_administrativa'));
    }
    public function update(Request $request, UnidadAdministrativa $unidad_administrativa)
    {
       $unidad_administrativa->update($request->only('name'));
        return redirect()->route('administracion.unidad_administrativa.index');
    }
    public function destroy(UnidadAdministrativa $unidad_administrativa)
    {
         abort_if(Gate::denies('unidad_adminis_destroy'), 403);
        $unidad_administrativa->delete();
        return redirect()->route('administracion.unidad_administrativa.index')->with('Unidad Administrativa Eliminada');
    }
}
