<?php

namespace App\Http\Controllers;


use App\Models\Articulo;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class ArticuloController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('articulo_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        $articulos = Articulo::query()->with('unidad_medida')
            ->when($request->input('articulo_id'), function($query) use ($request) {
                return $query->where('id', $request->input('articulo_id'));
            })
            ->when($request->input('unidad_medida_id') ?: $request->input('unidad'), function($query, $unidadId) {
                return $query->where('unidad_medida_id', $unidadId);
            })
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('articulo', 'LIKE', '%'. $search . '%')
                        ->orWhere('id', 'LIKE', '%'. $search . '%')
                        ->orWhereHas('unidad_medida', function ($sub) use ($search) {
                            $sub->where('unidad_medida', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15);

        $all_articulos = Articulo::with('unidad_medida')->get();
        $unidad_medidas = UnidadMedida::orderBy('unidad_medida', 'asc')->get();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'articulos' => $articulos,
                    'unidad_medidas' => $unidad_medidas,
                    'permissions' => [
                        'can_create' => Gate::allows('articulo_create'),
                        'can_edit' => Gate::allows('articulo_edit'),
                        'can_destroy' => Gate::allows('articulo_destroy'),
                    ]
                ]
            ]);
        }

        return view('administracion.articulos.index', ['articulos'=> $articulos, 'all_articulos' => $all_articulos, 'search' => $search, 'sort' => $sort, 'direction' => $direction]);
   }
    public function show(){
       return view('administracion.articulos.show', ['articulos' => Articulo::get()]);
   }
   public function create(Articulo $articulo){
        abort_if(Gate::denies('articulo_create'), 403);
        //dd($articulo);
       return view('administracion.articulos.create',['unidad_medidas'=> UnidadMedida::all()]);
   }
   public function store(Request $request)
{
    $request->validate([
        'articulo' => 'required|string|min:2|max:100|unique:articulos,articulo',
        'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        'stock_min' => 'required|integer|min:0',
    ], [
        'articulo.required' => 'El nombre del artículo es obligatorio',
        'articulo.min' => 'El artículo debe tener al menos 2 caracteres',
        'articulo.max' => 'El artículo no puede exceder 100 caracteres',
        'articulo.unique' => 'Este artículo ya existe',
        'unidad_medida_id.required' => 'Debe seleccionar una unidad de medida',
        'unidad_medida_id.exists' => 'La unidad de medida seleccionada no existe',
        'stock_min.required' => 'El stock mínimo es obligatorio',
        'stock_min.integer' => 'El stock mínimo debe ser un número entero',
        'stock_min.min' => 'El stock mínimo no puede ser negativo',
    ]);

    // Generar un nuevo ID para el artículo
    $lastArticulo = Articulo::orderBy('id', 'desc')->first();
    $newId = $lastArticulo ? sprintf('%04d', (int)$lastArticulo->id + 1) : '0001';

    // Verificar si el nuevo ID ya existe
    while (Articulo::where('id', $newId)->exists()) {
        $newId = sprintf('%04d', (int)$newId + 1);
    }

    // Crear el nuevo artículo
    $articulo = new Articulo;
    $articulo->id = $newId; // Asignar el nuevo ID
    $articulo->articulo = $request->articulo;
    $articulo->stock_min = $request->stock_min;
    $articulo->unidad_medida_id = $request->unidad_medida_id;
    $articulo->save();

    if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Artículo creado correctamente',
            'data' => $articulo->load('unidad_medida')
        ], 200);
    }

    return redirect()->route('administracion.articulos.index')->with('status', 'Artículo creado correctamente');
}
   public function edit($id)
   {
        abort_if(Gate::denies('articulo_edit'), 403);
        $articulo = Articulo::find($id);
       return view('administracion.articulos.edit', ['articulo' => $articulo, 'unidad_medidas'=> UnidadMedida::all()]);
   }
   public function update(Request $request, $id)
{
    $request->validate([
        'articulo' => 'required|string|min:2|max:100|unique:articulos,articulo,' . $id,
        'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        'stock_min' => 'required|integer|min:0',
    ], [
        'articulo.required' => 'El nombre del artículo es obligatorio',
        'articulo.min' => 'El artículo debe tener al menos 2 caracteres',
        'articulo.max' => 'El artículo no puede exceder 100 caracteres',
        'articulo.unique' => 'Este artículo ya existe',
        'unidad_medida_id.required' => 'Debe seleccionar una unidad de medida',
        'unidad_medida_id.exists' => 'La unidad de medida seleccionada no existe',
        'stock_min.required' => 'El stock mínimo es obligatorio',
        'stock_min.integer' => 'El stock mínimo debe ser un número entero',
        'stock_min.min' => 'El stock mínimo no puede ser negativo',
    ]);

    // Buscar el artículo por su ID
    $articulo = Articulo::find($id);

    // Verificar si el artículo existe
    if (!$articulo) {
        return redirect()->route('administracion.articulos.index')->with('error', 'Artículo no encontrado');
    }

    // Actualizar los campos del artículo
    $articulo->articulo = $request->input('articulo');
    $articulo->unidad_medida_id = $request->input('unidad_medida_id');
    $articulo->stock_min = $request->input('stock_min');

    // Guardar los cambios
    $articulo->save();

    if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'Artículo actualizado correctamente',
            'data' => $articulo->load('unidad_medida')
        ], 200);
    }

    return redirect()->route('administracion.articulos.index')->with('success', 'Artículo actualizado correctamente');
}
   public function destroy($id)
   {
        abort_if(Gate::denies('articulo_destroy'), 403);
        $articulo = Articulo::find($id);
        if ($articulo) {
            $articulo->delete();
        }

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Artículo eliminado correctamente'
            ], 200);
        }

        return redirect()->route('administracion.articulos.index')->with('Articulo Eliminada');
   }
}
