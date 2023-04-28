<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Http\Requests\SaveCategoriaRequest;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('categorias_index'), 403);

       $categorias = Categoria::paginate(5);

        return view('administracion.categorias.index', ['categorias'=> $categorias]);
    }
    public function show(){
        return view('administracion.categorias.show', ['categoria' => Categoria::get()]);
    }
    public function create(Categoria $categoria){
        abort_if(Gate::denies('categorias_create'), 403);
        return view('administracion.categorias.create',['categoria'=> $categoria]);
    }
    public function store(SaveCategoriaRequest $request){

        Categoria::create($request->validated());
        /* return redirect()->route('administracion.cargar')->with('status', 'Categoria creada'); */
        return redirect()->route('administracion.docs.create')->with('status', 'Categoria creada');
    }
    public function edit(Categoria $categoria)
    {
        abort_if(Gate::denies('categorias_edit'), 403);
        return view('administracion.categorias.edit', compact('categoria'));
    }
    public function update(Request $request, Categoria $categoria)
    {
       $categoria->update($request->only('name'));
        return redirect()->route('administracion.categorias.index');
    }
    public function destroy(Categoria $categoria)
    {
        abort_if(Gate::denies('categorias_destroy'), 403);
        $categoria->delete();
        return redirect()->route('administracion.categorias.index')->with('Categría Eliminada');
    }
}
