<?php
namespace App\Http\Controllers;

use App\Models\Cartucho;
use App\Models\Colore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class CartuchoController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('cartucho_index'), 403);
        $cartuchos = Cartucho::query()->with('colore', 'impresora', 'fabricante');

        // Filtro por búsqueda general
        if ($request->has('search') && $request->search) {
            $cartuchos->where('cartucho', 'LIKE', '%'. $request->search . '%')
                ->orWhereHas('colore', function ($q) use ($request) {
                    $q->where('colore', 'LIKE', '%' . $request->search . '%');
                })
                ->orWhereHas('impresora', function ($q) use ($request) {
                    $q->where('impresora', 'LIKE', '%' . $request->search . '%');
                })
                ->orWhereHas('fabricante', function ($q) use ($request) {
                    $q->where('fabricante', 'LIKE', '%' . $request->search . '%');
                });
        }

        // Filtros específicos
        if ($request->has('nombre-impresora') && $request->{'nombre-impresora'}) {
            $cartuchos->where('impresora_id', $request->{'nombre-impresora'});
        }

        if ($request->has('consumible') && $request->consumible) {
            $cartuchos->where('id', $request->consumible);
        }

        if ($request->has('color') && $request->color) {
            $cartuchos->where('colore_id', $request->color);
        }

        if ($request->has('tipo') && $request->tipo) {
            $cartuchos->where('fabricante_id', $request->tipo);
        }

        $cartuchos = $cartuchos->orderBy('cartucho', 'asc')->latest()->paginate(5);

        // Load all options for filters
        $impresoras = \App\Models\Impresora::all();
        $colores = Colore::whereHas('cartuchos')->get();
        $fabricantes = \App\Models\Fabricante::whereHas('cartuchos')->get();
        $all_cartuchos = Cartucho::all();

        return view('administracion.consumibles.index', [
            'cartuchos' => $cartuchos,
            'impresoras' => $impresoras,
            'colores' => $colores,
            'fabricantes' => $fabricantes,
            'all_cartuchos' => $all_cartuchos
        ]);
   }

   public function create(Cartucho $cartucho){
        abort_if(Gate::denies('cartucho_create'), 403);
        //dd($cartucho);
       return view('administracion.consumibles.create',['colores'=> Colore::all(), 'impresoras' => \App\Models\Impresora::all(), 'fabricantes' => \App\Models\Fabricante::all()]);
   }
   public function store(Request $request){
       //dd($cartucho);
       $request->validate([
           'cartucho' => 'required',
           'colore_id' => 'required',
           'stock_min' => 'required',
           'impresora_id' => 'required',
           'fabricante_id' => 'required',

       ]);
       //cartucho::create($request->validated());
       $cartucho = new Cartucho;
       $cartucho->cartucho = $request->cartucho;
       $cartucho->stock_min = $request->stock_min;
       $cartucho->colore_id = $request->colore_id;
       $cartucho->impresora_id = $request->impresora_id;
       $cartucho->fabricante_id = $request->fabricante_id;
       $cartucho->save();
       return redirect()->route('administracion.consumibles.index')->with('status', 'consumible creado');

   }
   public function edit($id)
   {
        abort_if(Gate::denies('cartucho_edit'), 403);
        $cartucho = Cartucho::find($id);
       return view('administracion.consumibles.edit', ['cartucho' => $cartucho, 'colores'=> Colore::all(), 'impresoras' => \App\Models\Impresora::all(), 'fabricantes' => \App\Models\Fabricante::all()]);
   }
   public function update(Request $request,  $id)
   {

    $request->validate([
        'cartucho' => 'required',
        'colore_id' => 'required',
        'stock_min' => 'required',
        'impresora_id' => 'required',
        'fabricante_id' => 'required',

    ]);

    $cartucho = Cartucho::find($id);

    $cartucho->cartucho = $request->input('cartucho');
    $cartucho->colore_id = $request->input('colore_id');
    $cartucho->stock_min = $request->input('stock_min');
    $cartucho->impresora_id = $request->input('impresora_id');
    $cartucho->fabricante_id = $request->input('fabricante_id');
      $cartucho->save();
       return redirect()->route('administracion.consumibles.index', $cartucho->id)->with('success', 'Consumible actualizado correctamente');
   }
   public function destroy($id)
   {
        abort_if(Gate::denies('cartucho_destroy'), 403);
        $cartucho = Cartucho::find($id);
       $cartucho->delete();
       return redirect()->route('administracion.consumibles.index')->with('Consumible Eliminada');
   }
}
