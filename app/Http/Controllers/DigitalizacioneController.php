<?php

namespace App\Http\Controllers;

use App\Models\Digitalizacione;
use App\Models\DigitalizacionDocumento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Gate;
use App\Models\AreaAdscrita;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;

class DigitalizacioneController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('digitalizaciones_index'), 403);

        $search_general = $request->get('search_general', '');
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        $query = Digitalizacione::query()
            ->with('area_adscrita')
            ->with('documentos.tipo_documento');

        // Filtro por búsqueda general
        if ($search_general) {
            $query->where(function ($q) use ($search_general) {
                $q->where('cedula', 'LIKE', '%' . $search_general . '%')
                    ->orWhere('nombre_apellido', 'LIKE', '%' . $search_general . '%')
                    ->orWhere('salud', 'LIKE', '%' . $search_general . '%')
                    ->orWhereHas('area_adscrita', function ($q2) use ($search_general) {
                        $q2->where('area_adscrita', 'LIKE', '%' . $search_general . '%');
                    });
            });
        }

        // Filtros avanzados
        if (request('filterType') && request('secondFilter')) {
            if (request('filterType') == 'area_adscrita_id') {
                $query->where('area_adscrita_id', request('secondFilter'));
            } elseif (request('filterType') == 'tipo_usuario') {
                $query->where('salud', request('secondFilter'));
            }
        }

        // Filtros de texto
        if (request('filterType') && request('search') && in_array(request('filterType'), ['cedula', 'nombre_apellido'])) {
            $query->where(request('filterType'), 'LIKE', '%' . request('search') . '%');
        }

        // Filtro por fecha
        if (request('filterType') == 'fecha' && request('fecha_desde') && request('fecha_hasta')) {
            $query->whereBetween('created_at', [request('fecha_desde'), request('fecha_hasta')]);
        }

        $digitalizaciones = $query->orderBy($sort, $direction)->paginate(15);

        return view('gestion-humana.digitalizacion_documentos.index', [
            'digitalizaciones' => $digitalizaciones,
            'areas_adscritas' => AreaAdscrita::all(),
            'tipo_documentos' => TipoDocumento::all(),
            'search_general' => $search_general,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('digitalizaciones_create'), 403);
        return view('gestion-humana.digitalizacion_documentos.create', [
            'area_adscritas' => AreaAdscrita::all(),
            'tipo_documentos' => TipoDocumento::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'required',
            'nombre_apellido' => 'required',
            'pdfs.*' => 'file|mimes:pdf',
            'tipo_documento_id.*' => 'required|exists:tipo_documentos,id'
        ]);

        try {
            DB::beginTransaction();

            // Crear digitalización
            $digitalizacione = new Digitalizacione;
            $digitalizacione->cedula = $request->get('cedula');
            $digitalizacione->nombre_apellido = $request->get('nombre_apellido');
            $digitalizacione->salud = $request->get('salud');
            $digitalizacione->area_adscrita_id = $request->get('area_adscrita_id');
            $digitalizacione->user_id = $request->user()->id;
            $digitalizacione->save();

            // Guardar múltiples PDFs con sus tipos de documento
            if ($request->hasFile('pdfs')) {
                $tipoDocumentos = $request->get('tipo_documento_id', []);
                foreach ($request->file('pdfs') as $index => $pdf) {
                    $fileName = Str::slug(pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    while (file_exists(public_path('documentos_ogh/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('documentos_ogh/'), $path);

                    DigitalizacionDocumento::create([
                        'digitalizacione_id' => $digitalizacione->id,
                        'nombre_archivo' => $pdf->getClientOriginalName(),
                        'ruta' => $path,
                        'user_id' => $request->user()->id,
                        'tipo_documento_id' => $tipoDocumentos[$index] ?? null
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('gestion-humana.digitalizacion_documentos.index')
                ->with('success', 'Documento(s) digitalizado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(Gate::denies('digitalizaciones_edit'), 403);
        $digitalizacione = Digitalizacione::with('documentos.tipo_documento')->find($id);
        return view('gestion-humana.digitalizacion_documentos.edit', [
            'digitalizacione' => $digitalizacione,
            'area_adscritas' => AreaAdscrita::all(),
            'tipo_documentos' => TipoDocumento::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Digitalizacione $digitalizacione)
    {
        try {
            DB::beginTransaction();

            // Actualizar datos principales
            $digitalizacione->update([
                'cedula' => $request->get('cedula'),
                'nombre_apellido' => $request->get('nombre_apellido'),
                'salud' => $request->get('salud'),
                'area_adscrita_id' => $request->get('area_adscrita_id'),
            ]);

            // Actualizar tipos de documento de documentos existentes
            if ($request->has('tipo_documento_existente')) {
                foreach ($request->get('tipo_documento_existente') as $documentoId => $tipoDocumentoId) {
                    $documento = DigitalizacionDocumento::find($documentoId);
                    if ($documento) {
                        $documento->update(['tipo_documento_id' => $tipoDocumentoId]);
                    }
                }
            }

            // Agregar nuevos PDFs si se proporcionan
            if ($request->hasFile('pdfs')) {
                $tipoDocumentos = $request->get('tipo_documento_id', []);
                foreach ($request->file('pdfs') as $index => $pdf) {
                    $fileName = Str::slug(pathinfo($pdf->getClientOriginalName(), PATHINFO_FILENAME), '-') . '.' . $pdf->getClientOriginalExtension();
                    $path = $fileName;

                    while (file_exists(public_path('documentos_ogh/' . $path))) {
                        $path = Str::random(10) . '-' . $fileName;
                    }

                    $pdf->move(public_path('documentos_ogh/'), $path);

                    DigitalizacionDocumento::create([
                        'digitalizacione_id' => $digitalizacione->id,
                        'nombre_archivo' => $pdf->getClientOriginalName(),
                        'ruta' => $path,
                        'user_id' => $request->user()->id,
                        'tipo_documento_id' => $tipoDocumentos[$index] ?? null
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('gestion-humana.digitalizacion_documentos.index')
                ->with('success', 'Documento actualizado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar un documento específico
     */
    public function destroyDocumento(DigitalizacionDocumento $documento)
    {
        try {
            DB::beginTransaction();

            // Eliminar archivo físico
            if ($documento->ruta && file_exists(public_path('documentos_ogh/' . $documento->ruta))) {
                unlink(public_path('documentos_ogh/' . $documento->ruta));
            }

            $digitalizacione_id = $documento->digitalizacione_id;
            $documento->delete();

            DB::commit();
            return redirect()->route('gestion-humana.digitalizacion_documentos.edit', $digitalizacione_id)
                ->with('success', 'Documento eliminado correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Digitalizacione $digitalizacione)
    {
        abort_if(Gate::denies('digitalizaciones_destroy'), 403);

        try {
            DB::beginTransaction();

            // Eliminar todos los documentos asociados
            foreach ($digitalizacione->documentos as $doc) {
                if ($doc->ruta && file_exists(public_path('documentos_ogh/' . $doc->ruta))) {
                    unlink(public_path('documentos_ogh/' . $doc->ruta));
                }
                $doc->delete();
            }

            $digitalizacione->delete();

            DB::commit();
            return redirect()->route('gestion-humana.digitalizacion_documentos.index')
                ->with('success', 'Documento(s) eliminado(s) correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
