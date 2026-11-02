<?php

namespace App\Http\Controllers;
use App\Models\Presentacion;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PresentacionController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('presentacion_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'presentacion');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $presentacions = DB::table('presentacions')
            ->select('id', 'presentacion')
            ->where('presentacion', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        return view('registros-insumos.medicamentos.presentacion.index', compact('presentacions', 'search', 'sort', 'direction'));

    }
     public function show(){
        return view('presentacion.show', ['presentacions' => Presentacion::get()]);
    }
    public function create(Presentacion $presentacion){
         abort_if(Gate::denies('presentacion_create'), 403);
         //dd($presentacion);
        return view('registros-insumos.medicamentos.presentacion.create',['presentacion'=> $presentacion]);
    }
    public function store(Request $request){
        //dd($material);
        $request->validate([
            'presentacion' => 'required|unique:presentacions',

        ]);
        //presentacion::create($request->validated());
         $presentacion = new Presentacion;
        $presentacion->presentacion = $request->presentacion;
        $presentacion->save();
        return redirect()->route('registros-insumos.medicamentos.presentacion.index')->with('status', 'Presentación creada');

    }
    public function edit(Presentacion $presentacion)
    {
        abort_if(Gate::denies('presentacion_edit'), 403);
        return view('registros-insumos.medicamentos.presentacion.edit', compact('presentacion'));
    }
    public function update(Request $request, Presentacion $presentacion)
    {
       $presentacion->update($request->only('presentacion'));
        return redirect()->route('registros-insumos.medicamentos.presentacion.index');
    }
    public function destroy(Presentacion $presentacion)
    {
         abort_if(Gate::denies('presentacion_destroy'), 403);
        $presentacion->delete();
        return redirect()->route('registros-insumos.medicamentos.presentacion.index')->with('Presentación Eliminada');
    }
}
