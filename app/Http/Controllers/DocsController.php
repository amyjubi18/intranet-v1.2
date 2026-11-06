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

        // Parámetros de búsqueda y ordenamiento
        $searchGeneral = $request->get('search_general') ?: $request->get('search');
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        // Filtros avanzados
        $filterType = $request->get('filterType');
        $secondFilter = $request->get('secondFilter');
        $search = $request->get('search');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        // Filtro por estantería
        $estanteriaId = $request->get('estanteria_id');

        $query = Documento::query()->with('archivos.categoria', 'estanteria', 'categoria');

        // Búsqueda general
        if ($searchGeneral) {
            $query->where(function ($q) use ($searchGeneral) {
                $q->where('name', 'LIKE', '%' . $searchGeneral . '%')
                    ->orWhere('description', 'LIKE', '%' . $searchGeneral . '%')
                    ->orWhereHas('archivos', function ($sub) use ($searchGeneral) {
                        $sub->where('nombre_archivo', 'LIKE', '%' . $searchGeneral . '%');
                    });
            });
        }

        // Filtro por Nº Expediente
        if ($filterType === 'name' && $search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        // Filtro por Descripción
        if ($filterType === 'description' && $search) {
            $query->where('description', 'LIKE', '%' . $search . '%');
        }

        // Filtro por Categoría
        if ($filterType === 'categoria_id' && $secondFilter) {
            $query->where('categoria_id', $secondFilter);
        }

        // Filtro por rango de fechas
        if ($filterType === 'fecha' && $fechaDesde && $fechaHasta) {
            $query->whereBetween('fecha', [$fechaDesde, $fechaHasta]);
        }

        // Filtro por estantería
        if ($estanteriaId) {
            $query->where('estanteria_id', $estanteriaId);
        }

        $perPage = (int) $request->input('per_page', 10);
        $documentos = $query->orderBy($sort, $direction)->paginate($perPage);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'documentos' => $documentos,
                    'categorias' => Categoria::orderBy('name')->get(),
                    'estanterias' => Estanteria::withCount('documentos')->get(),
                    'permissions' => [
                        'can_create' => auth()->user()?->can('documentos_create') ?? false,
                        'can_edit' => auth()->user()?->can('documentos_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('documentos_destroy') ?? false,
                    ]
                ],
                'message' => 'Documentos obtenidos exitosamente'
            ], 200);
        }

        return view('administracion.documentos.index', [
            'documentos' => $documentos,
            'categorias' => Categoria::orderBy('name')->get(),
            'estanterias' => Estanteria::withCount('documentos')->get(),
            'sort' => $sort,
            'direction' => $direction,
            'search_general' => $searchGeneral
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('documentos_create'), 403);
        return view('administracion.documentos.create', [
            "categorias" => Categoria::orderBy('name')->get(),
            "estanterias" => Estanteria::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('documentos_create'), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'fecha' => 'required|date',
            'estanteria_id' => 'required|exists:estanterias,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'pdfs.*' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        try {
            DB::beginTransaction();

            $documento = new Documento;
            $documento->name = $request->get('name');
            $documento->description = $request->get('description');
            $documento->fecha = $request->get('fecha');
            $documento->user_id = $request->user()->id;
            $documento->estanteria_id = $request->get('estanteria_id');
            $documento->categoria_id = $request->get('categoria_id');
            $documento->save();

            // Guardar múltiples PDFs
            if ($request->hasFile('pdfs')) {
                $categoriasArchivos = $request->get('categoria_archivo_id', []);

                // Asegurar que directorio existe
                $destDir = public_path('archivos');
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                foreach ($request->file('pdfs') as $index => $pdf) {
                    $originalName = $pdf->getClientOriginalName();
                    $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    // Evitar conflictos de nombres
                    while (file_exists(public_path('archivos/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('archivos/'), $path);

                    DocumentoArchivo::create([
                        'documento_id' => $documento->id,
                        'nombre_archivo' => $originalName,
                        'ruta' => $path,
                        'categoria_id' => $categoriasArchivos[$index] ?? $request->get('categoria_id'),
                        'user_id' => $request->user()->id
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Documento guardado correctamente',
                    'data' => $documento->load('archivos.categoria', 'estanteria', 'categoria')
                ], 200);
            }

            return redirect()->route("administracion.documentos.index")
                ->with('success', 'Documento(s) guardado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        abort_if(Gate::denies('documentos_index'), 403);
        $documento = Documento::with('archivos.categoria', 'estanteria', 'categoria')->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $documento
            ], 200);
        }

        return redirect()->route('administracion.documentos.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        abort_if(Gate::denies('documentos_edit'), 403);
        $documento = Documento::with('archivos.categoria', 'estanteria', 'categoria')->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'documento' => $documento,
                    'categorias' => Categoria::orderBy('name')->get(),
                    'estanterias' => Estanteria::all()
                ]
            ], 200);
        }

        return view('administracion.documentos.edit', [
            'documento' => $documento,
            'categorias' => Categoria::orderBy('name')->get(),
            'estanterias' => Estanteria::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('documentos_edit'), 403);

        $documento = Documento::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'fecha' => 'required|date',
            'estanteria_id' => 'required|exists:estanterias,id',
            'categoria_id' => 'nullable|exists:categorias,id',
            'pdfs.*' => 'nullable|file|mimes:pdf|max:10240'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar datos principales
            $documento->update([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'fecha' => $request->get('fecha'),
                'estanteria_id' => $request->get('estanteria_id'),
                'categoria_id' => $request->get('categoria_id'),
            ]);

            // Actualizar categorías de archivos existentes
            if ($request->has('categoria_existente')) {
                foreach ($request->get('categoria_existente') as $archivoId => $categoriaId) {
                    $archivo = DocumentoArchivo::find($archivoId);
                    if ($archivo && $archivo->documento_id == $documento->id) {
                        $archivo->update(['categoria_id' => $categoriaId ?: null]);
                    }
                }
            }

            // Agregar nuevos PDFs si se proporcionan
            if ($request->hasFile('pdfs')) {
                $categoriasArchivos = $request->get('categoria_archivo_id', []);

                $destDir = public_path('archivos');
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                foreach ($request->file('pdfs') as $index => $pdf) {
                    $originalName = $pdf->getClientOriginalName();
                    $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    // Evitar conflictos de nombres
                    while (file_exists(public_path('archivos/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('archivos/'), $path);

                    DocumentoArchivo::create([
                        'documento_id' => $documento->id,
                        'nombre_archivo' => $originalName,
                        'ruta' => $path,
                        'categoria_id' => $categoriasArchivos[$index] ?? $request->get('categoria_id'),
                        'user_id' => $request->user()->id
                    ]);
                }
            }

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Documento actualizado correctamente',
                    'data' => $documento->fresh()->load('archivos.categoria', 'estanteria', 'categoria')
                ], 200);
            }

            return redirect()->route('administracion.documentos.index')
                ->with('success', 'Documento actualizado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar un archivo específico
     */
    public function destroyArchivo($id, Request $request)
    {
        try {
            DB::beginTransaction();

            $archivo = DocumentoArchivo::findOrFail($id);

            // Eliminar archivo físico
            if ($archivo->ruta && file_exists(public_path('archivos/' . $archivo->ruta))) {
                @unlink(public_path('archivos/' . $archivo->ruta));
            }

            $documento_id = $archivo->documento_id;
            $archivo->delete();

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Archivo eliminado correctamente'
                ], 200);
            }

            return redirect()->route('administracion.documentos.edit', $documento_id)
                ->with('success', 'Archivo eliminado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        abort_if(Gate::denies('documentos_destroy'), 403);

        try {
            DB::beginTransaction();

            $documento = Documento::with('archivos')->findOrFail($id);

            // Eliminar todos los archivos asociados
            foreach ($documento->archivos as $archivo) {
                if ($archivo->ruta && file_exists(public_path('archivos/' . $archivo->ruta))) {
                    @unlink(public_path('archivos/' . $archivo->ruta));
                }
                $archivo->delete();
            }

            $documento->delete();

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Documento(s) eliminado(s) correctamente'
                ], 200);
            }

            return redirect()->route('administracion.documentos.index')
                ->with('success', 'Documento(s) eliminado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function export(){
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
