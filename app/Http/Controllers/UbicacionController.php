<?php

namespace App\Http\Controllers;
use App\Models\Ubicacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('ubicacion_index'), 403);

      /* $ubicacions = Ubicacion::first()->orderBy('ubicacion','asc')->paginate(5); */


      $ubicacions = Ubicacion::paginate(15);

       return view('administracion.ubicacion.index', ['ubicacions'=> $ubicacions]);
   }

   public function create(Ubicacion $ubicacion){
        abort_if(Gate::denies('ubicacion_create'), 403);
        //dd($ubicacion);
       return view('administracion.ubicacion.create',['ubicacion'=> $ubicacion]);
   }
   public function store(Request $request){
       //dd($ubicacion);
       $request->validate([
           'ubicacion' => 'required|unique:ubicacions',

       ]);
       //ubicacion::create($request->validated());
        $ubicacion = new Ubicacion;
       $ubicacion->ubicacion = $request->ubicacion;
       $ubicacion->save();
       return redirect()->route('administracion.ubicacion.index')->with('status', 'ubicacion creado');

   }
   public function edit(Ubicacion $ubicacion)
   {
       abort_if(Gate::denies('ubicacion_edit'), 403);
       return view('administracion.ubicacion.edit', compact('ubicacion'));
   }
   public function update(Request $request, Ubicacion $ubicacion)
   {
      $ubicacion->update($request->only('ubicacion'));
       return redirect()->route('administracion.ubicacion.index');
   }
   public function destroy(Ubicacion $ubicacion)
   {
       abort_if(Gate::denies('ubicacion_destroy'), 403);
       $ubicacion->delete();
       return redirect()->route('administracion.ubicacion.index')->with('ubicacion Eliminado');
   }
}
