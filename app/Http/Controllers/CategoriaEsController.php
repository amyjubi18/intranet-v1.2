<?php

namespace App\Http\Controllers;
use App\Models\CategoriaEspecifica;
use App\Http\Requests\SaveCategoriaEsRequest;
use Illuminate\Http\Request;

class CategoriaEsController extends Controller
{
    public function index(Request $request){

        /*         abort_if(Gate::denies('categorias_index'), 403);
         */
    $categoria_especificas = CategoriaEspecifica::paginate(5);

    return view('administracion.categoria_especifica.index', ['categoria_especificas'=> $categoria_especificas]);
    }
    public function show(){
        return view('administracion.categoria_especifica.show', ['categoria_especifica' => CategoriaEspecifica::get()]);
    }
    public function create(CategoriaEspecifica $categoria_especifica){
        /* abort_if(Gate::denies('categorias_create'), 403); */
        return view('administracion.categoria_especifica.create',['categoria_especifica'=>$categoria_especifica]);
    }
    public function store(SaveCategoriaEsRequest $request){

        CategoriaEspecifica::create($request->validated());
        return redirect()->route('administracion.categoria_especifica.create')->with('status', 'Categoria Específica creada');
    }
    public function edit(CategoriaEspecifica $categoria_especifica)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.categoria_especifica.edit', compact('categoria_especifica'));
    }
    public function update(Request $request, CategoriaEspecifica $categoria_especifica)
    {
       $categoria_especifica->update($request->only('categoria_especifica'));
        return redirect()->route('administracion.categoria_especifica.index');
    }
    public function destroy(CategoriaEspecifica $categoria_especifica)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $categoria_especifica->delete();
        return redirect()->route('administracion.categoria_especifica.index')->with('Categoría Eliminada');
    }
}
