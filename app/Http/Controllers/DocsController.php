<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoArchivo;
use App\Models\Estanteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
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

        // Búsqueda general
        $searchGeneral = $request->get('search_general');

        // Filtros avanzados
        $filterType = $request->get('filterType');
        $secondFilter = $request->get('secondFilter');
        $search = $request->get('search');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        // Filtro por estantería
        $estanteriaId = $request->get('estanteria_id');

        $documentos = Documento::query()->with('archivos.categoria', 'estanteria')
            // Búsqueda general
            ->when($searchGeneral, function ($query) use ($searchGeneral) {
                return $query->where('name', 'LIKE', '%' . $searchGeneral . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchGeneral . '%')
                    ->orWhereHas('archivos', function ($q) use ($searchGeneral) {
                        $q->where('nombre_archivo', 'LIKE', '%' . $searchGeneral . '%');
                    });
            })
            // Filtro por Nº Expediente
            ->when($filterType === 'name' && $search, function ($query) use ($search) {
                return $query->where('name', 'LIKE', '%' . $search . '%');
            })
            // Filtro por Descripción
            ->when($filterType === 'description' && $search, function ($query) use ($search) {
                return $query->where('description', 'LIKE', '%' . $search . '%');
            })
            // Filtro por rango de fechas
            ->when($filterType === 'fecha' && $fechaDesde && $fechaHasta, function ($query) use ($fechaDesde, $fechaHasta) {
                return $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
            })
            // Filtro por estantería
            ->when($estanteriaId, function ($query) use ($estanteriaId) {
                return $query->where('estanteria_id', $estanteriaId);
            })
            ->latest()->paginate(10);

        return view('administracion.documentos.index', [
            'documentos' => $documentos,
            'categorias' => Categoria::all(),
            'estanterias' => Estanteria::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('documentos_create'), 403);
        return view('administracion.documentos.create', [
            "categorias" => Categoria::all(),
            "estanterias" => Estanteria::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdfs.*' => 'file|mimes:pdf',
            'categoria_archivo_id.*' => 'required|exists:categorias,id'
        ]);

        try {
            DB::beginTransaction();

            $documento = new Documento;
            $documento->name = $request->get('name');
            $documento->description = $request->get('description');
            $documento->fecha = $request->get('fecha');
            $documento->user_id = $request->user()->id;
            $documento->estanteria_id = $request->get('estanteria_id');
            $documento->save();

            // Guardar múltiples PDFs
            if ($request->hasFile('pdfs')) {
                $categoriasArchivos = $request->get('categoria_archivo_id', []);

                foreach ($request->file('pdfs') as $index => $pdf) {
                    $fileName = Str::slug(pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    // Evitar conflictos de nombres
                    while (file_exists(public_path('archivos/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('archivos/'), $path);

                    DocumentoArchivo::create([
                        'documento_id' => $documento->id,
                        'nombre_archivo' => $pdf->getClientOriginalName(),
                        'ruta' => $path,
                        'categoria_id' => $categoriasArchivos[$index] ?? null,
                        'user_id' => $request->user()->id
                    ]);
                }
            }

            DB::commit();
            return redirect()->route("administracion.documentos.index")
                ->with('success', 'Documento(s) guardado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
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
        abort_if(Gate::denies('documentos_edit'), 403);
        $documento = Documento::with('archivos.categoria')->find($id);
        return view('administracion.documentos.edit', [
            'documento' => $documento,
            'categorias' => Categoria::all(),
            'estanterias' => Estanteria::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Documento $documento)
    {
        try {
            DB::beginTransaction();

            // Actualizar datos principales
            $documento->update([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'fecha' => $request->get('fecha'),
                'estanteria_id' => $request->get('estanteria_id'),
            ]);

            // Actualizar categorías de archivos existentes
            if ($request->has('categoria_existente')) {
                foreach ($request->get('categoria_existente') as $archivoId => $categoriaId) {
                    $archivo = DocumentoArchivo::find($archivoId);
                    if ($archivo) {
                        $archivo->update(['categoria_id' => $categoriaId]);
                    }
                }
            }

            // Agregar nuevos PDFs si se proporcionan
            if ($request->hasFile('pdfs')) {
                $categoriasArchivos = $request->get('categoria_archivo_id', []);

                foreach ($request->file('pdfs') as $index => $pdf) {
                    $fileName = Str::slug(pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    // Evitar conflictos de nombres
                    while (file_exists(public_path('archivos/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('archivos/'), $path);

                    DocumentoArchivo::create([
                        'documento_id' => $documento->id,
                        'nombre_archivo' => $pdf->getClientOriginalName(),
                        'ruta' => $path,
                        'categoria_id' => $categoriasArchivos[$index] ?? null,
                        'user_id' => $request->user()->id
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('administracion.documentos.index')
                ->with('success', 'Documento actualizado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar un archivo específico
     */
    public function destroyArchivo(DocumentoArchivo $archivo)
    {
        try {
            DB::beginTransaction();

            // Eliminar archivo físico
            if ($archivo->ruta && file_exists(public_path('archivos/' . $archivo->ruta))) {
                unlink(public_path('archivos/' . $archivo->ruta));
            }

            $documento_id = $archivo->documento_id;
            $archivo->delete();

            DB::commit();
            return redirect()->route('administracion.documentos.edit', $documento_id)
                ->with('success', 'Archivo eliminado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documento $documento)
    {
        abort_if(Gate::denies('documentos_destroy'), 403);

        try {
            DB::beginTransaction();

            // Eliminar todos los archivos asociados
            foreach ($documento->archivos as $archivo) {
                if ($archivo->ruta && file_exists(public_path('archivos/' . $archivo->ruta))) {
                    unlink(public_path('archivos/' . $archivo->ruta));
                }
                $archivo->delete();
            }

            $documento->delete();

            DB::commit();
            return redirect()->route('administracion.documentos.index')
                ->with('success', 'Documento(s) eliminado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function export(){
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
