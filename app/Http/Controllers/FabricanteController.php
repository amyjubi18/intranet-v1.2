<?php

namespace App\Http\Controllers;
use App\Models\Fabricante;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class FabricanteController extends Controller
{
public function index(Request $request){

        abort_if(Gate::denies('fabricante_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'fabricante');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Fabricante::query();

        if ($search) {
            $query->where('fabricante', 'LIKE', '%'.$search.'%');
        }

        $fabricantes = $query->orderBy($sort, $direction)->paginate(15);

       return view('administracion.tipo_impresoras.index', [
           'fabricantes'=> $fabricantes,
           'search' => $search,
           'sort' => $sort,
           'direction' => $direction
       ]);
   }

   public function create(){
        abort_if(Gate::denies('fabricante_create'), 403);
       return view('administracion.tipo_impresoras.create');
   }
   public function store(Request $request){
       //dd($fabricante);
       $request->validate([
           'fabricante' => 'required|unique:fabricantes',

       ]);
       //fabricante::create($request->validated());
        $fabricante = new Fabricante;
       $fabricante->fabricante = $request->fabricante;
       $fabricante->save();
       return redirect()->route('administracion.tipo_impresoras.index')->with('status', 'tipo consumible creado');

   }
   public function edit(Fabricante $tipo_impresora)
   {
        abort_if(Gate::denies('fabricante_edit'), 403);
       return view('administracion.tipo_impresoras.edit', compact('tipo_impresora'));
   }
   public function update(Request $request, Fabricante $tipo_impresora)
   {
      $tipo_impresora->update($request->only('fabricante'));
       return redirect()->route('administracion.tipo_impresoras.index')->with('success', 'Tipo consumible actualizado correctamente');
   }
   public function destroy(Fabricante $tipo_impresora)
   {
        abort_if(Gate::denies('fabricante_destroy'), 403);
       $tipo_impresora->delete();
       return redirect()->route('administracion.tipo_impresoras.index')->with('Tipo impresora eliminado');
   }
}
