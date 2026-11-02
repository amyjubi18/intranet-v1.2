<?php

namespace App\Http\Controllers;
use App\Models\Area;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class AreaController extends Controller
{
   public function index(Request $request){

        abort_if(Gate::denies('area_index'), 403);

        $areas = Area::query();

        // Filtros específicos
        if ($request->has('area') && $request->area) {
            $areas->where('id', $request->area);
        }

        $areas = $areas->orderBy('area', 'asc')->paginate(5);

        // Obtener todas las áreas sin paginar para los filtros
        $todasLasAreas = Area::orderBy('area', 'asc')->get();

       return view('administracion.areas.index', ['areas'=> $areas, 'todasLasAreas' => $todasLasAreas]);
   }
    public function show(){
       return view('administracion.areas.show', ['areas' => Area::get()]);
   }
   public function create(Area $area){
        abort_if(Gate::denies('area_create'), 403);
        //dd($area);
       return view('administracion.areas.create',['area'=> $area]);
   }
   public function store(Request $request){
       //dd($area);
       $request->validate([
           'area' => 'required|unique:areas',

       ]);
       //area::create($request->validated());
        $area = new Area;
       $area->area = $request->area;
       $area->save();
       return redirect()->route('administracion.areas.index')->with('status', 'area creado');

   }
   public function edit(Area $area)
   {
        abort_if(Gate::denies('area_edit'), 403);
       return view('administracion.areas.edit', compact('area'));
   }
   public function update(Request $request, Area $area)
   {
      $area->update($request->only('area'));
       return redirect()->route('administracion.areas.index');
   }
   public function destroy(Area $area)
   {
        abort_if(Gate::denies('area_destroy'), 403);
       $area->delete();
       return redirect()->route('administracion.areas.index')->with('area Eliminado');
   }
}
