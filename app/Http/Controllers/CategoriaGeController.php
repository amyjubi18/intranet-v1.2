<?php

namespace App\Http\Controllers;
use App\Models\CategoriaGeneral;
use App\Http\Requests\SaveCategoriaGeRequest;
use Illuminate\Http\Request;

class CategoriaGeController extends Controller
{

    public function index(Request $request){

        /*         abort_if(Gate::denies('categorias_index'), 403);
         */
    $categoria_generals = CategoriaGeneral::paginate(5);

    return view('administracion.categoria_general.index', ['categoria_generals'=> $categoria_generals]);
    }
    public function show(){
        return view('administracion.categoria_general.show', ['categoria_general' => CategoriaGeneral::get()]);
    }
    public function create(CategoriaGeneral $categoria_general){
        /* abort_if(Gate::denies('categorias_create'), 403); */
        return view('administracion.categoria_general.create',['categoria_general'=>$categoria_general]);
    }
    public function store(SaveCategoriaGeRequest $request){

        CategoriaGeneral::create($request->validated());
        return redirect()->route('administracion.categoria_general.create')->with('status', 'Estado del Uso creada');
    }
    public function edit(CategoriaGeneral $categoria_general)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.categoria_general.edit', compact('categoria_general'));
    }
    public function update(Request $request, CategoriaGeneral $categoria_general)
    {
       $categoria_general->update($request->only('categoria_general'));
        return redirect()->route('administracion.categoria_general.index');
    }
    public function destroy(CategoriaGeneral $categoria_general)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $categoria_general->delete();
        return redirect()->route('administracion.categoria_general.index')->with('Categoría Eliminada');
    }
}
