<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\SaveCategoriaRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('categorias_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Categoria::query();

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $perPage = (int) $request->input('per_page', 15);
        $categorias = $query->orderBy($sort, $direction)->paginate($perPage);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'categorias' => $categorias,
                    'permissions' => [
                        'can_create' => auth()->user()?->can('categorias_create') ?? false,
                        'can_edit' => auth()->user()?->can('categorias_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('categorias_destroy') ?? false,
                    ]
                ],
                'message' => 'Categorías obtenidas exitosamente'
            ], 200);
        }

        return view('administracion.categorias.index', compact('categorias', 'search', 'sort', 'direction'));
    }

    public function show($id, Request $request)
    {
        abort_if(Gate::denies('categorias_index'), 403);
        $categoria = Categoria::findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $categoria
            ], 200);
        }

        return view('administracion.categorias.show', ['categoria' => $categoria]);
    }

    public function create(Categoria $categoria)
    {
        abort_if(Gate::denies('categorias_create'), 403);
        return view('administracion.categorias.create', ['categoria' => $categoria]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('categorias_create'), 403);

        $request->validate([
            'name' => 'required|string|max:255|unique:categorias,name'
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con ese nombre',
            'name.max' => 'El nombre no puede exceder 255 caracteres'
        ]);

        $categoria = Categoria::create(['name' => $request->name]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $categoria
            ], 200);
        }

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
            'status' => 200,
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'categoria' => [
                'id' => $categoria->id,
                'name' => $categoria->name
            ],
            'data' => $categoria
        ], 200);
    }

    public function edit($id, Request $request)
    {
        abort_if(Gate::denies('categorias_edit'), 403);
        $categoria = Categoria::findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $categoria
            ], 200);
        }

        return view('administracion.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('categorias_edit'), 403);

        $categoria = Categoria::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categorias,name,' . $categoria->id
        ], [
            'name.required' => 'El nombre de la categoría es obligatorio',
            'name.unique' => 'Ya existe una categoría con ese nombre',
            'name.max' => 'El nombre no puede exceder 255 caracteres'
        ]);

        $categoria->update($request->only('name'));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $categoria
            ], 200);
        }

        return redirect()->route('administracion.categorias.index')->with('status', 'Categoría actualizada');
    }

    public function destroy($id, Request $request)
    {
        abort_if(Gate::denies('categorias_destroy'), 403);

        $categoria = Categoria::findOrFail($id);
        $categoria->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ], 200);
        }

        return redirect()->route('administracion.categorias.index')->with('status', 'Categoría eliminada');
    }
}
