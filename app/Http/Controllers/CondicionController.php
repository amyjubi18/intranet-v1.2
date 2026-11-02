<?php

namespace App\Http\Controllers;
use App\Models\Condicion;
use App\Http\Requests\SaveCondicionRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CondicionController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('condicion_index'), 403);

        $search = trim($request->get('search'));
        $condicions = DB::table('condicions')
            ->select('id', 'condicion')
            ->where('condicion', 'LIKE', '%'.$search.'%')
            ->orderBy('condicion', 'asc')
            ->paginate(15);
        $todasLasCondiciones = Condicion::all();
        return view('administracion.condicion_fisica.index', compact('condicions', 'search', 'todasLasCondiciones'));

    }
     public function show(){
        return view('administracion.condicion_fisica.show', ['condicions' => Condicion::get()]);
    }
    public function create(Condicion $condicion){
         abort_if(Gate::denies('condicion_create'), 403);
         //dd($condicion);
        return view('administracion.condicion_fisica.create',['condicion'=> $condicion]);
    }
    public function store(Request $request){
        //dd($condicion);
        $request->validate([
            'condicion' => 'required|unique:condicions',

        ]);
        //condicion::create($request->validated());
         $condicion = new Condicion;
        $condicion->condicion = $request->condicion;
        $condicion->save();
        return redirect()->route('administracion.condicion_fisica.index')->with('status', 'Condición Física creada');

    }
    public function edit(Condicion $condicion)
    {
         abort_if(Gate::denies('condicion_edit'), 403);
        return view('administracion.condicion_fisica.edit', compact('condicion'));
    }
    public function update(Request $request, Condicion $condicion)
    {
       $condicion->update($request->only('condicion'));
        return redirect()->route('administracion.condicion_fisica.index');
    }
    public function destroy(Condicion $condicion)
    {
         abort_if(Gate::denies('condicion_destroy'), 403);
        $condicion->delete();
        return redirect()->route('administracion.condicion_fisica.index')->with('Condición Física Eliminado');
    }
}
