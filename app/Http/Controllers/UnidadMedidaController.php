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

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'unidad_medidas' => $unidad_medidas,
                    'permissions' => [
                        'can_create' => Gate::allows('unidad_medida_create'),
                        'can_edit' => Gate::allows('unidad_medida_edit'),
                        'can_destroy' => Gate::allows('unidad_medida_destroy'),
                    ]
                ]
            ]);
        }

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
       abort_if(Gate::denies('unidad_medida_create'), 403);
       $request->validate([
           'unidad_medida' => 'required|unique:unidad_medidas,unidad_medida',
       ], [
           'unidad_medida.required' => 'El nombre de la unidad de medida es obligatorio.',
           'unidad_medida.unique' => 'Esta unidad de medida ya se encuentra registrada.',
       ]);
        $unidad_medida = new UnidadMedida;
       $unidad_medida->unidad_medida = $request->unidad_medida;
       $unidad_medida->save();

       if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Unidad de medida creada correctamente',
               'data' => $unidad_medida
           ], 200);
       }

       return redirect()->route('administracion.unidad_medidas.index')->with('status', 'unidad creado');

   }
   public function edit(UnidadMedida $unidad_medida)
   {
        abort_if(Gate::denies('unidad_medida_edit'), 403);
       return view('administracion.unidad_medidas.edit', compact('unidad_medida'));
   }
   public function update(Request $request, UnidadMedida $unidad_medida)
   {
       abort_if(Gate::denies('unidad_medida_edit'), 403);
       $request->validate([
           'unidad_medida' => 'required|unique:unidad_medidas,unidad_medida,' . $unidad_medida->id,
       ], [
           'unidad_medida.required' => 'El nombre de la unidad de medida es obligatorio.',
           'unidad_medida.unique' => 'Esta unidad de medida ya se encuentra registrada.',
       ]);
      $unidad_medida->update($request->only('unidad_medida'));

      if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
          return response()->json([
              'status' => 200,
              'success' => true,
              'message' => 'Unidad de medida actualizada correctamente',
              'data' => $unidad_medida
          ], 200);
      }

       return redirect()->route('administracion.unidad_medidas.index');
   }
   public function destroy(UnidadMedida $unidad_medida)
   {
        abort_if(Gate::denies('unidad_medida_destroy'), 403);
       $unidad_medida->delete();

       if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
           return response()->json([
               'status' => 200,
               'success' => true,
               'message' => 'Unidad de medida eliminada correctamente'
           ], 200);
       }

       return redirect()->route('administracion.unidad_medidas.index')->with('unidad Eliminado');
   }
}
