<?php

namespace App\Http\Controllers;
use App\Models\FormaAdquisicion;
use App\Http\Requests\SaveFormaAdquiRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FormaAdquisicionController extends Controller
{


    public function index(Request $request){

        abort_if(Gate::denies('forma_adquisicion_index'), 403);

        // Aceptar tanto 'search' como 'forma_adquisicion' como parámetros de búsqueda
        $search = trim($request->get('search')) ?: trim($request->get('forma_adquisicion'));
        $forma_adquisicions = DB::table('forma_adquisicions')
            ->select('id', 'forma_adquisicion')
            ->where('forma_adquisicion', 'LIKE', '%'.$search.'%')
            ->orderBy('forma_adquisicion', 'asc')
            ->paginate(15);
        $todasLasFormasAdquisicion = FormaAdquisicion::all();
        return view('administracion.forma_adquisicion.index', compact('forma_adquisicions', 'search', 'todasLasFormasAdquisicion'));

    }
     public function show(){
        return view('administracion.forma_adquisicion.show', ['forma_adquisicions' => FormaAdquisicion::get()]);
    }
    public function create(FormaAdquisicion $forma_adquisicion){
         abort_if(Gate::denies('forma_adquisicion_create'), 403);
         //dd($color);
        return view('administracion.forma_adquisicion.create',['forma_adquisicion'=> $forma_adquisicion]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'forma_adquisicion' => 'required|unique:forma_adquisicions',

        ]);
        //color::create($request->validated());
         $forma_adquisicion = new FormaAdquisicion;
        $forma_adquisicion->forma_adquisicion = $request->forma_adquisicion;
        $forma_adquisicion->save();
        return redirect()->route('administracion.forma_adquisicion.index')->with('status', 'Forma Adquisición creada');

    }
    public function edit(FormaAdquisicion $forma_adquisicion)
    {
         abort_if(Gate::denies('forma_adquisicion_edit'), 403);
        return view('administracion.forma_adquisicion.edit', compact('forma_adquisicion'));
    }
    public function update(Request $request, FormaAdquisicion $forma_adquisicion)
    {
       $forma_adquisicion->update($request->only('forma_adquisicion'));
        return redirect()->route('administracion.forma_adquisicion.index');
    }
    public function destroy(FormaAdquisicion $forma_adquisicion)
    {
         abort_if(Gate::denies('forma_adquisicion_destroy'), 403);
        $forma_adquisicion->delete();
        return redirect()->route('administracion.forma_adquisicion.index')->with('Forma Adquisición Eliminada');
    }
}
