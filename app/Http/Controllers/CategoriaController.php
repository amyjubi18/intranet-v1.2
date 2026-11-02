<?php

namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Http\Requests\SaveCategoriaRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('categorias_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $categorias = DB::table('categorias')
            ->select('id', 'name')
            ->where('name', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        return view('administracion.categorias.index', compact('categorias', 'search', 'sort', 'direction'));
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
        return redirect()->route('administracion.categorias.index')->with('status', 'Categoria creada');
    }

    // Método para crear categoría vía AJAX desde modal
    public function storeAjax(Request $request)
    {
        abort_if(Gate::denies('categorias_create'), 403);

        $request->validate([
            'name' => 'required|unique:categorias,name|max:255'
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con ese nombre',
            'name.max' => 'El nombre no puede exceder 255 caracteres'
        ]);

        $categoria = Categoria::create(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'categoria' => [
                'id' => $categoria->id,
                'name' => $categoria->name
            ]
        ]);
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
