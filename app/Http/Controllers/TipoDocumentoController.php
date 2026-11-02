<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class TipoDocumentoController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('tipo_documento_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'tipo_documento');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $tipo_documentos = DB::table('tipo_documentos')
            ->select('id', 'tipo_documento')
            ->where('tipo_documento', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        return view('gestion-humana.tipo_documentos.index', compact('tipo_documentos', 'search', 'sort', 'direction'));

    }
    public function create(TipoDocumento $tipo_documento){
         abort_if(Gate::denies('tipo_documento_create'), 403);
         //dd($tipo_documento);
        return view('gestion-humana.tipo_documentos.create',['tipo_documento'=> $tipo_documento]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'tipo_documento' => 'required|unique:tipo_documentos',

        ]);
        //color::create($request->validated());
         $tipo_documento = new TipoDocumento;
        $tipo_documento->tipo_documento = $request->tipo_documento;
        $tipo_documento->save();
        return redirect()->route('gestion-humana.tipo_documentos.index')->with('status', 'area adscrita creado');

    }
    public function edit(TipoDocumento $tipo_documento)
    {
         abort_if(Gate::denies('tipo_documento_edit'), 403);
        return view('gestion-humana.tipo_documentos.edit', compact('tipo_documento'));
    }
    public function update(Request $request, TipoDocumento $tipo_documento)
    {
       $tipo_documento->update($request->only('tipo_documento'));
        return redirect()->route('gestion-humana.tipo_documentos.index');
    }
    public function destroy(TipoDocumento $tipo_documento)
    {
         abort_if(Gate::denies('tipo_documento_destroy'), 403);
        $tipo_documento->delete();
        return redirect()->route('gestion-humana.tipo_documentos.index')->with('Area Adscrita Eliminado');
    }
}
