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

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'area');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Area::query();

        // Filtros específicos
        if ($request->has('area') && $request->area) {
            $query->where('id', $request->area);
        }

        // Búsqueda general
        if ($search) {
            $query->where('area', 'LIKE', '%'.$search.'%');
        }

        $areas = $query->orderBy($sort, $direction)->paginate(15);

        // Obtener todas las áreas sin paginar para los filtros
        $todasLasAreas = Area::orderBy('area', 'asc')->get();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'areas' => $areas,
                    'permissions' => [
                        'can_create' => Gate::allows('area_create'),
                        'can_edit' => Gate::allows('area_edit'),
                        'can_destroy' => Gate::allows('area_destroy'),
                    ]
                ]
            ]);
        }

       return view('administracion.areas.index', ['areas'=> $areas, 'todasLasAreas' => $todasLasAreas, 'search' => $search, 'sort' => $sort, 'direction' => $direction]);
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
       abort_if(Gate::denies('area_create'), 403);
       $request->validate([
           'area' => 'required|unique:areas,area',
       ], [
           'area.required' => 'El nombre del área es obligatorio.',
           'area.unique' => 'Esta área ya se encuentra registrada.',
       ]);
        $area = new Area;
       $area->area = $request->area;
       $area->save();

       if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Área creada correctamente',
               'data' => $area
           ], 200);
       }

       return redirect()->route('administracion.areas.index')->with('status', 'area creado');

   }
   public function edit(Area $area)
   {
        abort_if(Gate::denies('area_edit'), 403);
       return view('administracion.areas.edit', compact('area'));
   }
   public function update(Request $request, Area $area)
   {
       abort_if(Gate::denies('area_edit'), 403);
       $request->validate([
           'area' => 'required|unique:areas,area,' . $area->id,
       ], [
           'area.required' => 'El nombre del área es obligatorio.',
           'area.unique' => 'Esta área ya se encuentra registrada.',
       ]);

       $area->update($request->only('area'));

       if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Área actualizada correctamente',
               'data' => $area
           ], 200);
       }

       return redirect()->route('administracion.areas.index')->with('status', 'Área actualizada correctamente');
   }
   public function destroy(Area $area)
   {
        abort_if(Gate::denies('area_destroy'), 403);
       $area->delete();

       if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Área eliminada correctamente'
           ], 200);
       }

       return redirect()->route('administracion.areas.index')->with('area Eliminado');
   }
}
