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

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'impresora_id');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Cartucho::query()->with('colore', 'impresora', 'fabricante', 'impresora.gerencias', 'impresora.gerencias.area');

        // Filtro por búsqueda general
        if ($search) {
            $query->where('cartucho', 'LIKE', '%'. $search . '%')
                ->orWhereHas('colore', function ($q) use ($search) {
                    $q->where('colore', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('impresora', function ($q) use ($search) {
                    $q->where('impresora', 'LIKE', '%' . $search . '%');
                })
                ->orWhereHas('fabricante', function ($q) use ($search) {
                    $q->where('fabricante', 'LIKE', '%' . $search . '%');
                });
        }

        // Filtros específicos
        if ($request->has('nombre-impresora') && $request->{'nombre-impresora'}) {
            $query->where('impresora_id', $request->{'nombre-impresora'});
        }

        if ($request->has('consumible') && $request->consumible) {
            $query->where('id', $request->consumible);
        }

        if ($request->has('color') && $request->color) {
            $query->where('colore_id', $request->color);
        }

        if ($request->has('tipo') && $request->tipo) {
            $query->where('fabricante_id', $request->tipo);
        }

        // Filtro por gerencia (a través de la relación con impresoras)
        if ($request->has('gerencia_id') && $request->gerencia_id) {
            $query->whereHas('impresora.gerencias', function ($q) use ($request) {
                $q->where('gerencias.id', $request->gerencia_id);
            });
        }

        $cartuchos = $query->orderBy($sort, $direction)->paginate(15);

        // Load all options for filters
        $impresoras = \App\Models\Impresora::all();
        $colores = Colore::whereHas('cartuchos')->get();
        $fabricantes = \App\Models\Fabricante::whereHas('cartuchos')->get();
        $all_cartuchos = Cartucho::all();
        $gerencias = \App\Models\Gerencia::with('area')->get(); // Para el filtro y mostrar gerencia

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'cartuchos' => $cartuchos,
                    'catalogs' => [
                        'impresoras' => $impresoras,
                        'colores' => $colores,
                        'fabricantes' => $fabricantes,
                        'all_cartuchos' => $all_cartuchos,
                        'gerencias' => $gerencias,
                    ],
                    'permissions' => [
                        'can_create' => auth()->user()?->can('cartucho_create') ?? false,
                        'can_edit' => auth()->user()?->can('cartucho_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('cartucho_destroy') ?? false,
                    ]
                ],
                'message' => 'Consumibles obtenidos exitosamente'
            ], 200);
        }

        return view('administracion.consumibles.index', [
            'cartuchos' => $cartuchos,
            'impresoras' => $impresoras,
            'colores' => $colores,
            'fabricantes' => $fabricantes,
            'all_cartuchos' => $all_cartuchos,
            'gerencias' => $gerencias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
   }

   public function create(Cartucho $cartucho){
        abort_if(Gate::denies('cartucho_create'), 403);
        //dd($cartucho);
       return view('administracion.consumibles.create',[
           'colores'=> Colore::all(),
           'impresoras' => \App\Models\Impresora::all(),
           'fabricantes' => \App\Models\Fabricante::with('colores')->get()
       ]);
   }
   public function store(Request $request){
       //dd($request->all());

       // Validar según el modo de selección de color
       if ($request->has('colore_ids') && is_array($request->colore_ids) && count($request->colore_ids) > 0) {
           // Modo de colores múltiples
           $request->validate([
               'cartucho' => 'required',
               'stock_min' => 'required',
               'impresora_id' => 'required',
               'fabricante_id' => 'required',
               'colore_ids' => 'required|array|min:1',
           ]);
       } else {
           // Modo de color único
           $request->validate([
               'cartucho' => 'required',
               'colore_id' => 'required',
               'stock_min' => 'required',
               'impresora_id' => 'required',
               'fabricante_id' => 'required',
           ]);
       }

       $cartucho = new Cartucho;
       $cartucho->cartucho = $request->cartucho;
       $cartucho->stock_min = $request->stock_min;
       $cartucho->impresora_id = $request->impresora_id;
       $cartucho->fabricante_id = $request->fabricante_id;

       // Guardar el primer color si hay múltiples, o el único
       if ($request->has('colore_ids') && is_array($request->colore_ids) && count($request->colore_ids) > 0) {
           $cartucho->colore_id = $request->colore_ids[0];
       } else {
           $cartucho->colore_id = $request->colore_id;
       }

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
