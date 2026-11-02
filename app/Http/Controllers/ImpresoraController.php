<?php

namespace App\Http\Controllers;
use App\Models\Impresora;
use App\Models\Gerencia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;

class ImpresoraController extends Controller
{
   public function index(Request $request){

        abort_if(Gate::denies('impresora_index'), 403);

        $search = trim($request->get('busqueda', ''));
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        // Validar sort
        $allowedSorts = ['id', 'impresora', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $query = Impresora::query();

        // Aplicar filtro por nombre de impresora (usando el nombre del filtro como en consumibles)
        if ($request->filled('nombre-impresora')) {
            $query->where('id', $request->input('nombre-impresora'));
        }

        // Aplicar filtro por gerencia
        if ($request->filled('gerencia_id')) {
            $query->whereHas('gerencias', function ($q) use ($request) {
                $q->where('gerencias.id', $request->input('gerencia_id'));
            });
        }

        // Aplicar filtro por fecha de creación
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        // Aplicar búsqueda general
        if ($search) {
            $query->where('impresora', 'like', "%{$search}%");
        }

        $impresoras = $query->with('gerencias')->orderBy($sort, $direction)->paginate(15)->appends($request->all());
        $all_impresoras = Impresora::all(); // Para el filtro
        $gerencias = Gerencia::with('area')->get(); // Para el filtro

       return view('administracion.impresoras.index', [
           'impresoras'=> $impresoras,
           'all_impresoras' => $all_impresoras,
           'gerencias' => $gerencias,
           'search' => $search,
           'sort' => $sort,
           'direction' => $direction
       ]);
   }

   public function create(Impresora $impresora){
        abort_if(Gate::denies('impresora_create'), 403);
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();
        return view('administracion.impresoras.create',[
            'impresora'=> $impresora,
            'gerencias' => $gerencias
        ]);
   }
   public function store(Request $request){
       $request->validate([
           'impresora' => 'required|unique:impresoras',
           'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
           'gerencia_ids' => 'nullable|array',
           'gerencia_ids.*' => 'exists:gerencias,id',
       ]);

       $impresora = new Impresora;
       $impresora->impresora = $request->impresora;

       if ($request->hasFile('imagen')) {
           $imagePath = $request->file('imagen')->store('impresoras', 'public');
           $impresora->imagen = $imagePath;
       }

       $impresora->user_id = auth()->id();
       $impresora->save();

       // Sincronizar las gerencias seleccionadas
       if ($request->has('gerencia_ids')) {
           $impresora->gerencias()->sync($request->gerencia_ids);
       }

       return redirect()->route('administracion.impresoras.index')->with('status', 'impresora creado');
   }
   public function edit(Impresora $impresora)
   {
        abort_if(Gate::denies('impresora_edit'), 403);
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();
        return view('administracion.impresoras.edit', compact('impresora', 'gerencias'));
   }
   public function update(Request $request, Impresora $impresora)
   {
       $request->validate([
           'impresora' => 'required|unique:impresoras,impresora,' . $impresora->id,
           'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
           'gerencia_ids' => 'nullable|array',
           'gerencia_ids.*' => 'exists:gerencias,id',
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

       // Sincronizar las gerencias seleccionadas
       if ($request->has('gerencia_ids')) {
           $impresora->gerencias()->sync($request->gerencia_ids);
       } else {
           $impresora->gerencias()->sync([]);
       }

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
