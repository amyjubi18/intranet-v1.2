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

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'ubicacion');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Ubicacion::query();

        if ($search) {
            $query->where('ubicacion', 'LIKE', '%'.$search.'%');
        }

        $ubicacions = $query->orderBy($sort, $direction)->paginate(15);

       return view('administracion.ubicacion.index', [
           'ubicacions'=> $ubicacions,
           'search' => $search,
           'sort' => $sort,
           'direction' => $direction
       ]);
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
