<?php

namespace App\Http\Controllers;

use App\Models\Proveedore;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
class ProveedorController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('proveedor_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'proveedor');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Proveedore::query();

        if ($search) {
            $query->where('proveedor', 'LIKE', '%'.$search.'%');
        }

        $proveedores = $query->orderBy($sort, $direction)->paginate(15);

        return view('administracion.proveedores.index', compact('proveedores', 'search', 'sort', 'direction'));
   }
    public function show(){
       return view('administracion.proveedores.show', ['proveedores' => Proveedore::get()]);
   }
   public function create(Proveedore $proveedore){
        abort_if(Gate::denies('proveedor_create'), 403);
       return view('administracion.proveedores.create',['proveedore'=> $proveedore]);
   }
   public function store(Request $request){
       $request->validate([
           'proveedor' => 'required|unique:proveedores',

       ]);
        $proveedore = new Proveedore;
       $proveedore->proveedor = $request->proveedor;
       $proveedore->save();
       return redirect()->route('administracion.proveedores.index')->with('status', 'Proveedor creado');

   }
   public function edit(Proveedore $proveedore)
   {
        abort_if(Gate::denies('proveedor_edit'), 403);
       return view('administracion.proveedores.edit', compact('proveedore'));
   }
   public function update(Request $request, Proveedore $proveedore)
   {
      $proveedore->update($request->only('proveedor'));
       return redirect()->route('administracion.proveedores.index');
   }
   public function destroy(Proveedore $proveedore)
   {
        abort_if(Gate::denies('proveedor_destroy'), 403);
       $proveedore->delete();
       return redirect()->route('administracion.proveedores.index')->with('Proveedor Eliminado');
   }
}
