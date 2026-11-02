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

    $articulos = Articulo::query()->with('unidad_medida')
        ->when($request->input('articulo_id'), function($query) use ($request) {
            return $query->where('id', $request->input('articulo_id'));
        })
        ->when($request->input('unidad_medida_id'), function($query) use ($request) {
            return $query->where('unidad_medida_id', $request->input('unidad_medida_id'));
        })
        ->when($request->input('search'), function($query) use ($request) {
            return $query->where('articulo', 'LIKE', '%'. $request->input('search') . '%')
                ->orWhere('id', 'LIKE', '%'. $request->input('search') . '%') // Búsqueda por ID
                ->orWhereHas('unidad_medida', function ($q) use ($request) {
                    $q->where('unidad_medida', 'LIKE', '%' . $request->input('search') . '%');
                });
        })->orderBy('id', 'desc')->latest()->paginate(15);

    $all_articulos = Articulo::with('unidad_medida')->get();

        return view('administracion.articulos.index', ['articulos'=> $articulos, 'all_articulos' => $all_articulos]);
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
        'articulo' => 'required',
        'unidad_medida_id' => 'required',
        'stock_min' => 'required',
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

    return redirect()->route('administracion.articulos.index')->with('status', 'Articulo creado');
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
        'articulo' => 'required',
        'unidad_medida_id' => 'required',
        'stock_min' => 'required',
        //'id' => 'required|unique:articulos,id,' . $id, // Asegúrate de que el nuevo ID sea único
    ]);

    // Buscar el artículo por su ID
    $articulo = Articulo::find($id);

    // Verificar si el artículo existe
    if (!$articulo) {
        return redirect()->route('administracion.articulos.index')->with('error', 'Articulo no encontrado');
    }

    // Actualizar los campos del artículo
    //$articulo->id = sprintf('%04d', $request->input('id')); // Actualizar el ID
    $articulo->articulo = $request->input('articulo');
    $articulo->unidad_medida_id = $request->input('unidad_medida_id');
    $articulo->stock_min = $request->input('stock_min');

    // Guardar los cambios
    $articulo->save();

    return redirect()->route('administracion.articulos.index')->with('success', 'Articulo actualizado correctamente');
}
   public function destroy($id)
   {
        abort_if(Gate::denies('articulo_destroy'), 403);
        $articulo = Articulo::find($id);
       $articulo->delete();
       return redirect()->route('administracion.articulos.index')->with('Articulo Eliminada');
   }
}
