<?php
namespace App\Http\Controllers;

use App\Models\Gerencia;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class GerenciasController extends Controller
{

    public function index(Request $request){

        abort_if(Gate::denies('gerencia_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'gerencia');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Gerencia::query()->with('area');

        // Filtros específicos
        $gerenciaFilter = $request->input('gerencia_id') ?: $request->input('gerencia');
        if ($gerenciaFilter) {
            $query->where('id', $gerenciaFilter);
        }

        $areaFilter = $request->input('area_id') ?: $request->input('area');
        if ($areaFilter) {
            $query->where('area_id', $areaFilter);
        }

        // Handle simple search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('gerencia', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('area', function ($sub) use ($search) {
                      $sub->where('area', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $gerencias = $query->orderBy($sort, $direction)->paginate(15);

        // Obtener todas las gerencias y áreas sin paginar para los filtros
        $todasLasGerencias = Gerencia::with('area')->orderBy('gerencia', 'asc')->get();
        $todasLasAreas = Area::orderBy('area', 'asc')->get();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'gerencias' => $gerencias,
                    'todas_las_gerencias' => $todasLasGerencias,
                    'areas' => $todasLasAreas,
                    'permissions' => [
                        'can_create' => Gate::allows('gerencia_create'),
                        'can_edit' => Gate::allows('gerencia_edit'),
                        'can_destroy' => Gate::allows('gerencia_destroy'),
                    ]
                ]
            ]);
        }

        return view('administracion.gerencias.index', [
            'gerencias'=> $gerencias,
            'todasLasGerencias' => $todasLasGerencias,
            'todasLasAreas' => $todasLasAreas,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
   }
    public function show(){
       return view('administracion.gerencias.show', ['gerencias' => Gerencia::get()]);
   }
   public function create(Gerencia $gerencia){
        abort_if(Gate::denies('gerencia_create'), 403);
        //dd($gerencia);
       return view('administracion.gerencias.create',['areas'=> Area::all()]);
   }
   public function store(Request $request){
       abort_if(Gate::denies('gerencia_create'), 403);
       $request->validate([
           'gerencia' => 'required',
           'area_id' => 'required|exists:areas,id',
       ], [
           'gerencia.required' => 'El nombre de la gerencia es obligatorio.',
           'area_id.required' => 'Debe seleccionar un área.',
           'area_id.exists' => 'El área seleccionada no es válida.',
       ]);
        $gerencia = new Gerencia;
       $gerencia->gerencia = $request->gerencia;
       $gerencia->area_id = $request->area_id;
       $gerencia->save();

       if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Gerencia creada correctamente',
               'data' => $gerencia->load('area')
           ], 200);
       }

       return redirect()->route('administracion.gerencias.index')->with('status', 'gerencia creada');

   }
   public function edit($id)
   {
        abort_if(Gate::denies('gerencia_edit'), 403);
        $gerencia = Gerencia::find($id);
       return view('administracion.gerencias.edit', ['gerencia' => $gerencia, 'areas'=> Area::all()]);
   }
   public function update(Request $request,  $id)
   {
        abort_if(Gate::denies('gerencia_edit'), 403);
        $request->validate([
            'gerencia' => 'required',
            'area_id' => 'required|exists:areas,id',
        ], [
            'gerencia.required' => 'El nombre de la gerencia es obligatorio.',
            'area_id.required' => 'Debe seleccionar un área.',
            'area_id.exists' => 'El área seleccionada no es válida.',
        ]);

        $gerencia = Gerencia::findOrFail($id);
        $gerencia->gerencia = $request->input('gerencia');
        $gerencia->area_id = $request->input('area_id');
        $gerencia->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Gerencia actualizada correctamente',
                'data' => $gerencia->load('area')
            ], 200);
        }

        return redirect()->route('administracion.gerencias.index', $gerencia->id)->with('success', 'Excel actualizado correctamente');
   }
   public function destroy($id)
   {
        abort_if(Gate::denies('gerencia_destroy'), 403);
        $gerencia = Gerencia::find($id);
        if ($gerencia) {
            $gerencia->delete();
        }

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Gerencia eliminada correctamente'
            ], 200);
        }

        return redirect()->route('administracion.gerencias.index')->with('Articulo Eliminada');
   }

}
