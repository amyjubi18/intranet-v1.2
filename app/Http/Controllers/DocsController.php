<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Gate;

use App\Models\Categoria;
use Illuminate\Http\Request;

class DocsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('documentos_index'), 403);


        $documentos = Documento::query()->with('categoria')->when(request('search'), function ($query) {
            return $query->where('name', 'LIKE', '%' . request('search') . '%')
                ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                ->orWhere('documento', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('categoria', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        })
        ->when(request('fecha_inicial'), function($query) {
            return $query->where('fecha', request('fecha_inicial'))->whereDate('fecha', '<=', date('Y-m-d'))->orWhere('fecha',request('fecha_final'))/* ->whereDate('fecha', '<=', date('Y-m-d')) */ ;
    })/* ->when(request('fecha_final'), function($query) {
        return $query->where('fecha', request('fecha_final'))->whereDate('fecha', '=', date('Y-m-d'));
    }) */
    ->latest()->paginate(5);


        return view('administracion.docs.index', [
            'documentos' => $documentos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        abort_if(Gate::denies('documentos_create'), 403);
        return view('administracion.docs.create', [
            "categorias" => Categoria::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       /*  $validated = $request->validate([
            'pdf' => 'required'
        ]); */
        $request->validate([
            'pdf'=> 'required'
        ]);

        try {


            $documento = new Documento;
            $documento->id = $request->get('id');
            $documento->name = $request->get('name');
            $documento->description = $request->get('description');
            $documento->fecha = $request->get('fecha');


            if ($request->hasFile('pdf')) {
                $archiv = $request->file('pdf');
                $fileName = Str::slug(pathinfo($archiv->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $archiv->getClientOriginalExtension();
                $path = /* '/archivos/'. */ $fileName;
                if ($request->user()->documentos()->where('documento', $path)->count()) {
                    return back()->withErrors(['pdf' => 'Ya existe un documento con ese nombre']);
                }

                $archiv->move(public_path() . '/archivos/', $path);
                $documento->documento = $path;
            }
            $documento->categoria_id = $request->get('categoria_id');
            $documento->user_id = $request->user()->id;
            $documento->save();

            DB::commit();

            return redirect()->route("administracion.docs.index");
        } catch (Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //dd($documento);
        abort_if(Gate::denies('documentos_edit'), 403);
        $documento = Documento::find($id);
        return view('administracion.docs.edit', ['documento'=>$documento, 'categorias'=> Categoria::all()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Documento $documento)
    {
        $data = $request->only('name', 'description', 'fecha', 'categoria_id');

        $documento->update($data);

        if ($request->hasFile('pdf')) {
            $archiv = $request->file('pdf');
            $fileName = Str::slug(pathinfo($archiv->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $archiv->getClientOriginalExtension();
            $archiv->move(public_path() . '/archivos/', $fileName);
            $documento->documento =  /* '/archivos/'. */$fileName;
            $documento->save();
        }

        return redirect()->route('administracion.docs.index', $documento->id)->with('success', 'Documento actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documento $documento)
    {
        abort_if(Gate::denies('documentos_destroy'), 403);
        $documento->delete();
        return redirect()->route('administracion.docs.index')->with('Documento eliminado');
    }
    public function export(){
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
