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


        $gerencias = Gerencia::query()->with('area');

        // Filtros específicos
        if ($request->has('gerencia') && $request->gerencia) {
            $gerencias->where('id', $request->gerencia);
        }

        if ($request->has('area') && $request->area) {
            $gerencias->where('area_id', $request->area);
        }

        // Handle simple search (fallback)
        $gerencias->when(request('search'), function($query){
            return $query->where('gerencia', 'LIKE', '%'. request('search') . '%')
            ->orWhereHas('area', function ($q){
                $q->where('area', 'LIKE', '%' . request('search') . '%');
            });
        });

        $gerencias = $gerencias->orderBy('gerencia', 'asc')->paginate(5);

        // Obtener todas las gerencias y áreas sin paginar para los filtros
        $todasLasGerencias = Gerencia::orderBy('gerencia', 'asc')->get();
        $todasLasAreas = Area::orderBy('area', 'asc')->get();


        return view('administracion.gerencias.index', ['gerencias'=> $gerencias, 'todasLasGerencias' => $todasLasGerencias, 'todasLasAreas' => $todasLasAreas]);
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
