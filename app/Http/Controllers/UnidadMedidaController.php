<?php

namespace App\Http\Controllers;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class UnidadMedidaController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('unidad_medida_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'unidad_medida');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $unidad_medidas = DB::table('unidad_medidas')
            ->select('id', 'unidad_medida')
            ->where('unidad_medida', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        return view('administracion.unidad_medidas.index', compact('unidad_medidas', 'search', 'sort', 'direction'));
   }
    public function show(){
       return view('administracion.unidad_medidas.show', ['unidad_medidas' => UnidadMedida::get()]);
   }
   public function create(UnidadMedida $unidad_medida){
        abort_if(Gate::denies('unidad_medida_create'), 403);
        //dd($unidad);
       return view('administracion.unidad_medidas.create',['unidad_medida'=> $unidad_medida]);
   }
   public function store(Request $request){
       //dd($unidad);
       $request->validate([
           'unidad_medida' => 'required|unique:unidad_medidas',

       ]);
       //unidad::create($request->validated());
        $unidad_medida = new UnidadMedida;
       $unidad_medida->unidad_medida = $request->unidad_medida;
       $unidad_medida->save();
       return redirect()->route('administracion.unidad_medidas.index')->with('status', 'unidad creado');

   }
   public function edit(UnidadMedida $unidad_medida)
   {
        abort_if(Gate::denies('unidad_medida_edit'), 403);
       return view('administracion.unidad_medidas.edit', compact('unidad_medida'));
   }
   public function update(Request $request, UnidadMedida $unidad_medida)
   {
      $unidad_medida->update($request->only('unidad_medida'));
       return redirect()->route('administracion.unidad_medidas.index');
   }
   public function destroy(UnidadMedida $unidad_medida)
   {
        abort_if(Gate::denies('unidad_medida_destroy'), 403);
       $unidad_medida->delete();
       return redirect()->route('administracion.unidad_medidas.index')->with('unidad Eliminado');
   }
}
