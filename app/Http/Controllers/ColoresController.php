<?php

namespace App\Http\Controllers;
use App\Models\Colore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class ColoresController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('colores_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'colore');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Colore::query();

        if ($search) {
            $query->where('colore', 'LIKE', '%'.$search.'%');
        }

        $colores = $query->orderBy($sort, $direction)->paginate(15);

       return view('administracion.tipo_consumibles.index', [
           'colores'=> $colores,
           'search' => $search,
           'sort' => $sort,
           'direction' => $direction
       ]);
   }

   public function create(){
        abort_if(Gate::denies('colores_create'), 403);
       return view('administracion.tipo_consumibles.create');
   }
   public function store(Request $request){
       $request->validate([
           'colore' => 'required|unique:colores',

       ]);
        $colore = new Colore;
       $colore->colore = $request->colore;
       $colore->save();
       return redirect()->route('administracion.tipo_consumibles.index')->with('status', 'Color creado');

   }
   public function edit(Colore $tipo_consumible)
   {
        abort_if(Gate::denies('colores_edit'), 403);
       return view('administracion.tipo_consumibles.edit', compact('tipo_consumible'));
   }
   public function update(Request $request, Colore $tipo_consumible)
   {
      $tipo_consumible->update($request->only('colore'));
       return redirect()->route('administracion.tipo_consumibles.index');
   }
   public function destroy(Colore $tipo_consumible)
   {
        abort_if(Gate::denies('colores_destroy'), 403);
       $tipo_consumible->delete();
       return redirect()->route('administracion.tipo_consumibles.index')->with('color Eliminado');
   }
}
