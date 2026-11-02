<?php

namespace App\Http\Controllers;

use App\Models\User;
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
use Carbon\Carbon;
class RelacionBienesController extends Controller
{
    public function index(Request $request)
    {

    $bienes = Biene::paginate(5);

    return view('administracion.relacion_bienes.index', ['bienes'=> $bienes]);

    }
    public function create()
    {

        return view('administracion.relacion_bienes.create', [
            "bienes"=> Biene::all(),
            "unidad_administrativas" => UnidadAdministrativa::all(),
            "forma_adquisicions" => FormaAdquisicion::all(),
            "estado_del_usos" => EstadoDelUso::all(),
            "categoria_generals" => CategoriaGeneral::all(),
            "sub_categorias" => SubCategoria::all(),
            "categoria_especificas" => CategoriaEspecifica::all(),
            "users" => User::all(),
        ]);
    }
    public function store(Request $request)
    {

        $request->validate([
            'sede' =>'required',
            'unidad_administrativa_id' => 'required',
            'n_interno_bien' => 'required',
            'descripcion' => 'required',
            'forma_adquisicion_id' => 'required',
            'fecha_adquisicion' => 'required',
            'n_factura' => 'required',
            'valor_factura' => 'required',
            'moneda' => 'required',
            'estado_del_uso_id' => 'required',
            'condicion_fisica' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'serial' => 'required',
            'categoria_general_id' => 'required',
            'sub_categoria_id' => 'required',
            'categoria_especifica_id' => 'required',
            'codigo_contable' => 'required',
            'user_id' => 'required',
        ]);
        $biene = new Biene;

            $biene->sede = $request->sede;
            $biene->unidad_administrativa_id = $request->unidad_administrativa_id;
            $biene->n_interno_bien = $request->n_interno_bien;
            $biene->descripcion = $request->descripcion;
            $biene->forma_adquisicion_id = $request->forma_adquisicion_id;
            $biene->fecha_adquisicion = $request->fecha_adquisicion;
            $biene->n_factura = $request->n_factura;
            $biene->valor_factura = $request->valor_factura;
            $biene->moneda = $request->moneda;
            $biene->estado_del_uso_id = $request->estado_del_uso_id;
            $biene->condicion_fisica = $request->condicion_fisica;
            $biene->marca = $request->marca;
            $biene->modelo = $request->modelo;
            $biene->serial = $request->serial;
            $biene->categoria_general_id = $request->categoria_general_id;
            $biene->sub_categoria_id = $request->sub_categoria_id;
            $biene->categoria_especifica_id = $request->categoria_especifica_id;
            $biene->codigo_contable = $request->codigo_contable;
            $biene->user_id = $request->user()->id;
            $biene->save();



        return redirect()->route('administracion.relacion_bienes.create')->with('datos', 'Registro exitoso');
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
        $request->validate([
            'sede' =>'required',
            'unidad_administrativa_id' => 'required',
            'n_interno_bien' => 'required',
            'descripcion' => 'required',
            'forma_adquisicion_id' => 'required',
            'fecha_adquisicion' => 'required',
            'n_factura' => 'required',
            'valor_factura' => 'required',
            'moneda' => 'required',
            'estado_del_uso_id' => 'required',
            'condicion_fisica' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'serial' => 'required',
            'categoria_general_id' => 'required',
            'sub_categoria_id' => 'required',
            'categoria_especifica_id' => 'required',
            'codigo_contable' => 'required'
        ]);
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

       /* $biene->update($data); */



        return redirect()->route('administracion.bienes_publicos.index', $biene->id)->with('success', 'Excel actualizado correctamente');
    }
    public function destroy( $id)
    {
        abort_if(Gate::denies('bienes_destroy'), 403);
        $biene = Biene::find($id);
        $biene->delete();
        return redirect()->route('administracion.bienes_publicos.index')->with('Fila eliminada');
    }


}
