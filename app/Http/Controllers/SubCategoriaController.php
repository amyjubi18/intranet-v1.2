<?php

namespace App\Http\Controllers;
use App\Models\SubCategoria;
use App\Http\Requests\SaveSubCategoriaRequest;
use Illuminate\Http\Request;

class SubCategoriaController extends Controller
{
    public function index(Request $request){

        /*         abort_if(Gate::denies('categorias_index'), 403);
         */
    $sub_categorias = SubCategoria::paginate(5);

    return view('administracion.sub_categoria.index', ['sub_categorias'=> $sub_categorias]);
    }
    public function show(){
        return view('administracion.sub_categoria.show', ['sub_categoria' => SubCategoria::get()]);
    }
    public function create(SubCategoria $sub_categoria){
        /* abort_if(Gate::denies('categorias_create'), 403); */
        return view('administracion.sub_categoria.create',['sub_categoria'=>$sub_categoria]);
    }
    public function store(SaveSubCategoriaRequest $request){

        SubCategoria::create($request->validated());
        return redirect()->route('administracion.sub_categoria.create')->with('status', 'Subcategoría Creada');
    }
    public function edit(SubCategoria $sub_categoria)
    {
/*         abort_if(Gate::denies('categorias_edit'), 403);
 */        return view('administracion.sub_categoria.edit', compact('sub_categoria'));
    }
    public function update(Request $request, SubCategoria $sub_categoria)
    {
       $sub_categoria->update($request->only('sub_categoria'));
        return redirect()->route('administracion.sub_categoria.index');
    }
    public function destroy(SubCategoria $sub_categoria)
    {
        /* abort_if(Gate::denies('categorias_destroy'), 403); */
        $sub_categoria->delete();
        return redirect()->route('administracion.sub_categoria.index')->with('Subcategoría Eliminada');
    }

}
