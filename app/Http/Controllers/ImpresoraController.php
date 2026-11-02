<?php

namespace App\Http\Controllers;
use App\Models\Impresora;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class ImpresoraController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('impresora_index'), 403);

        $query = Impresora::query();

        // Aplicar filtro por nombre de impresora
        if ($request->filled('impresora_id')) {
            $query->where('id', $request->input('impresora_id'));
        }

        // Aplicar búsqueda general
        if ($request->filled('busqueda')) {
            $busqueda = $request->input('busqueda');
            $query->where('impresora', 'like', "%{$busqueda}%");
        }

        $impresoras = $query->paginate(5)->appends($request->all());
        $all_impresoras = Impresora::all(); // Para el filtro

       return view('administracion.impresoras.index', ['impresoras'=> $impresoras, 'all_impresoras' => $all_impresoras]);
   }

   public function create(Impresora $impresora){
        abort_if(Gate::denies('impresora_create'), 403);
        //dd($impresora);
       return view('administracion.impresoras.create',['impresora'=> $impresora]);
   }
   public function store(Request $request){
       $request->validate([
           'impresora' => 'required|unique:impresoras',
           'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
       ]);

       $impresora = new Impresora;
       $impresora->impresora = $request->impresora;

       if ($request->hasFile('imagen')) {
           $imagePath = $request->file('imagen')->store('impresoras', 'public');
           $impresora->imagen = $imagePath;
       }

       $impresora->user_id = auth()->id();
       $impresora->save();
       return redirect()->route('administracion.impresoras.index')->with('status', 'impresora creado');
   }
   public function edit(Impresora $impresora)
   {
        abort_if(Gate::denies('impresora_edit'), 403);
       return view('administracion.impresoras.edit', compact('impresora'));
   }
   public function update(Request $request, Impresora $impresora)
   {
       $request->validate([
           'impresora' => 'required|unique:impresoras,impresora,' . $impresora->id,
           'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
       ]);

       $impresora->impresora = $request->impresora;

       if ($request->hasFile('imagen')) {
           // Delete old image if exists
           if ($impresora->imagen && \Storage::disk('public')->exists($impresora->imagen)) {
               \Storage::disk('public')->delete($impresora->imagen);
           }
           $imagePath = $request->file('imagen')->store('impresoras', 'public');
           $impresora->imagen = $imagePath;
       }

       $impresora->user_id = auth()->id();
       $impresora->save();
       return redirect()->route('administracion.impresoras.index');
   }
   public function destroy(Impresora $impresora)
   {
        abort_if(Gate::denies('impresora_destroy'), 403);

        // Delete associated image if exists
        if ($impresora->imagen && \Storage::disk('public')->exists($impresora->imagen)) {
            \Storage::disk('public')->delete($impresora->imagen);
        }

       $impresora->delete();
       return redirect()->route('administracion.impresoras.index')->with('impresora Eliminado');
   }
}
