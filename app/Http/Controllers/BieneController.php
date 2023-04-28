<?php

namespace App\Http\Controllers;

use App\Models\Biene;
use App\Models\CategoriaEspecifica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Livewire;
use Illuminate\Support\Facades\Gate;
use App\Models\UnidadAdministrativa;
use App\Models\FormaAdquisicion;
use App\Models\EstadoDelUso;
use App\Models\CategoriaGeneral;
use App\Models\SubCategoria;
use App\Exports\BienesExport;
use Maatwebsite\Excel\Facades\Excel;

class BieneController extends Controller
{
    public function index(Request $request)
    {
     abort_if(Gate::denies('bienes_index'), 403);
    $bienes = Biene::query()->with('unidad_administrativas') ->with('forma_adquisicions')->with('estado_del_usos')->with('categoria_generals')->with('categoria_especificas')->when(request('search'), function($query){
    return $query->where('sede', 'LIKE','%'.request('search').'%')
    ->orWhere('n_interno_bien', 'LIKE','%'.request('search').'%')
    ->orWhere('descripcion', 'LIKE','%'.request('search').'%')
    ->orWhere('fecha_adquisicion', 'LIKE','%'.request('search').'%')
    ->orWhere('n_factura', 'LIKE','%'.request('search').'%')
    ->orWhere('valor_factura', 'LIKE','%'.request('search').'%')
    ->orWhere('moneda', 'LIKE','%'.request('search').'%')
    ->orWhere('condicion_fisica', 'LIKE','%'.request('search').'%')
    ->orWhere('marca', 'LIKE','%'.request('search').'%')
    ->orWhere('modelo', 'LIKE','%'.request('search').'%')
    ->orWhere('serial', 'LIKE','%'.request('search').'%')
    ->orWhere('codigo_contable', 'LIKE','%'.request('search').'%')
    ->orWhereHas('unidad_administrativas', function($q1){
        $q1->where('unidad_administrativa','LIKE','%'.request('search').'%');
       })
       ->orWhereHas('forma_adquisicions', function($q2){
        $q2->where('forma_adquisicion','LIKE','%'.request('search').'%');
       })
       ->orWhereHas('estado_del_usos', function($q3){
        $q3->where('estado_del_uso','LIKE','%'.request('search').'%');
       })
       ->orWhereHas('categoria_generals', function($q4){
        $q4->where('categoria_general','LIKE','%'.request('search').'%');
       })
       ->orWhereHas('sub_categorias', function($q5){
        $q5->where('sub_categoria','LIKE','%'.request('search').'%');
       })
       ->orWhereHas('categoria_especificas', function($q6){
        $q6->where('categoria_especifica','LIKE','%'.request('search').'%');
       });
    })->latest()->paginate(10);
    /* $bienes = Biene::all(); */
        return view('administracion.bienes_publicos.index',compact('bienes'));
    }
    public function create()
    {
          abort_if(Gate::denies('bienes_create'), 403);

        return view('administracion.bienes_publicos.create', [
            "unidad_administrativas" => UnidadAdministrativa::all(),
            "forma_adquisicions" => FormaAdquisicion::all(),
            "estado_del_usos" => EstadoDelUso::all(),
            "categoria_generals" => CategoriaGeneral::all(),
            "sub_categorias" => SubCategoria::all(),
            "categoria_especificas" => CategoriaEspecifica::all(),
        ]);
    }
    public function store(Request $request)
    {
       /*  $validated = $request->validate([
            'pdf' => 'required'
        ]); */


        try {


            $biene = new Biene;
            $biene->sede = $request->get('sede');
            $biene->n_interno_bien = $request->get('n_interno_bien');
            $biene->descripcion = $request->get('descripcion');
            $biene->fecha_adquisicion = $request->get('fecha_adquisicion');
            $biene->n_factura = $request->get('n_factura');
            $biene->valor_factura = $request->get('valor_factura');
            $biene->moneda = $request->get('moneda');
            $biene->condicion_fisica = $request->get('condicion_fisica');
            $biene->marca = $request->get('marca');
            $biene->modelo = $request->get('modelo');
            $biene->serial = $request->get('serial');
            $biene->codigo_contable = $request->get('codigo_contable');
            /* if ($request->hasFile('pdf')) {
                $archiv = $request->file('pdf');
                $fileName = Str::slug(pathinfo($archiv->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $archiv->getClientOriginalExtension();
                $path = '/archivos/'. $fileName;
                if ($request->user()->documentos()->where('documento', $path)->count()) {
                    return back()->withErrors(['pdf' => 'Ya existe un documento con ese nombre']);
                }

                $archiv->move(public_path() . '/archivos/', $path);
                $documento->documento = $path;
            } */
            $biene->unidad_administrativa_id = $request->get('unidad_administrativa_id');
            $biene->forma_adquisicion_id = $request->get('forma_adquisicion_id');
            $biene->estado_del_uso_id = $request->get('estado_del_uso_id');
            $biene->categoria_general_id = $request->get('categoria_general_id');
            $biene->sub_categoria_id = $request->get('sub_categoria_id');
            $biene->categoria_especifica_id = $request->get('categoria_especifica_id');
            $biene->user_id = $request->user()->id;
            $biene->save();

            DB::commit();

            return redirect()->route("administracion.bienes_publicos.index");
        } catch (Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
 public function edit($id)
    {
        //dd($documento);
        abort_if(Gate::denies('bienes_edit'), 403);

        $biene = Biene::find($id);
        /* $unidad_administrativas = UnidadAdministrativa::pluck('id','unidad_administrativa');
        $forma_adquisicions = FormaAdquisicion::pluck('id' , 'forma_adquisicion');
        $estado_del_usos = EstadoDelUso::pluck('id' , 'estado_del_uso');
        $categoria_generals = CategoriaGeneral::pluck('id' , 'categoria_general');
        $sub_categorias = SubCategoria::pluck('id' , 'sub_categoria');
        $categoria_especificas = CategoriaEspecifica::pluck('id' , 'categoria_especifica'); */

        return view('administracion.bienes_publicos.edit', ['biene' => $biene, 'unidad_administrativas' => UnidadAdministrativa::all(), 'forma_adquisicions' => FormaAdquisicion::all(), 'estado_del_usos' => EstadoDelUso::all(), 'categoria_generals' => CategoriaGeneral::all(), 'sub_categorias' => SubCategoria::all(), 'categoria_especificas' => CategoriaEspecifica::all()]);



    }

     public function update(Request $request, $id)
    {
        $biene =Biene::find($id);

        $biene->sede =$request->input('sede');
        $biene->unidad_administrativa_id =$request->input('unidad_administrativa');
        $biene->n_interno_bien =$request->input('n_interno_bien');
        $biene->descripcion =$request->input('descripcion');
        $biene->forma_adquisicion_id =$request->input('forma_adquisicion');
        $biene->fecha_adquisicion =$request->input('fecha_adquisicion');
        $biene->n_factura =$request->input('n_factura');
        $biene->valor_factura =$request->input('valor_factura');
        $biene->moneda =$request->input('moneda');
        $biene->estado_del_uso_id =$request->input('estado_del_uso');
        $biene->condicion_fisica =$request->input('condicion_fisica');
        $biene->marca =$request->input('marca');
        $biene->modelo =$request->input('modelo');
        $biene->serial =$request->input('serial');
        $biene->categoria_general_id =$request->input('categoria_general');
        $biene->sub_categoria_id =$request->input('sub_categoria');
        $biene->categoria_especifica_id =$request->input('categoria_especifica');
        $biene->codigo_contable =$request->input('codigo_contable');
        $biene->save();

       /*  $biene->update($biene); */



        return redirect()->route('administracion.bienes_publicos.index', $biene->id)->with('success', 'Excel actualizado correctamente');
    }
    public function destroy( $id)
    {
        abort_if(Gate::denies('bienes_destroy'), 403);
        $biene = Biene::find($id);
        $biene->delete();
        return redirect()->route('administracion.bienes_publicos.index')->with('Fila eliminada');
    }
    public function export(){
        return Excel::download(new BienesExport, 'bienes_publicos.xlsx');
    }
}
