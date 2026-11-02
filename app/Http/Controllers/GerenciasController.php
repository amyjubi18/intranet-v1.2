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
        if ($request->has('gerencia') && $request->gerencia) {
            $query->where('id', $request->gerencia);
        }

        if ($request->has('area') && $request->area) {
            $query->where('area_id', $request->area);
        }

        // Handle simple search
        if ($search) {
            $query->where('gerencia', 'LIKE', '%'.$search.'%')
                ->orWhereHas('area', function ($q) use ($search) {
                    $q->where('area', 'LIKE', '%' . $search . '%');
                });
        }

        $gerencias = $query->orderBy($sort, $direction)->paginate(15);

        // Obtener todas las gerencias y áreas sin paginar para los filtros
        $todasLasGerencias = Gerencia::orderBy('gerencia', 'asc')->get();
        $todasLasAreas = Area::orderBy('area', 'asc')->get();

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
       //dd($gerencia);
       $request->validate([
           'gerencia' => 'required',
           'area_id' => 'required',

       ]);
       //gerencia::create($request->validated());
        $gerencia = new Gerencia;
       $gerencia->gerencia = $request->gerencia;
       $gerencia->area_id = $request->area_id;
       $gerencia->save();
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

    $request->validate([
        'gerencia' => 'required',
        'area_id' => 'required',
    ]);

    $gerencia = Gerencia::find($id);

    $gerencia->gerencia = $request->input('gerencia');
    $gerencia->area_id = $request->input('area_id');
      $gerencia->save();
       return redirect()->route('administracion.gerencias.index', $gerencia->id)->with('success', 'Excel actualizado correctamente');
   }
   public function destroy($id)
   {
        abort_if(Gate::denies('gerencia_destroy'), 403);
        $gerencia = Gerencia::find($id);
       $gerencia->delete();
       return redirect()->route('administracion.gerencias.index')->with('Articulo Eliminada');
   }

}
