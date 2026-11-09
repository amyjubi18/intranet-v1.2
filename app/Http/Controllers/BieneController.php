<?php

namespace App\Http\Controllers;

use App\Models\Biene;
use App\Models\UsuarioDeUso;
use App\Models\UsuarioAdministrativo;
use App\Models\UnidadAdministrativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Exports\BienesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;

class BieneController extends Controller
{
    /**
     * Generate QR code URL for a given n_interno_bien using API
     */
    public function generateQrCodeUrl($n_interno_bien): string
    {
        // Generate URL that will open the bien details when scanned
        $baseUrl = url('/');
        return $baseUrl . '/bienes/qr/' . urlencode($n_interno_bien);
    }

    /**
     * Generate QR code image URL using quickchart API
     */
    public function generateQrCodeImageUrl($n_interno_bien): string
    {
        $qrCodeUrl = $this->generateQrCodeUrl($n_interno_bien);
        // Using quickchart.io for QR code generation
        return 'https://quickchart.io/chart?cht=qr&chs=200x200&chl=' . urlencode($qrCodeUrl);
    }

    /**
     * Route to handle QR code scanning - redirects to bien details
     */
    public function scanQrCode($n_interno_bien)
    {
        $n_interno_bien = urldecode($n_interno_bien);
        $biene = Biene::where('n_interno_bien', $n_interno_bien)->first();

        if ($biene) {
            return redirect()->route('administracion.bienes_publicos.edit', $biene->id);
        }

        return redirect()->route('administracion.bienes_publicos.index')
            ->with('error', 'No se encontró ningún bien con el código QR: ' . $n_interno_bien);
    }

    /**
     * API endpoint to find bien by QR code
     */
    public function findByQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $n_interno_bien = $request->qr_code;
        $biene = Biene::with(['unidad_administrativas', 'usuario_administrativos', 'usuario_de_usos',
                              'forma_adquisicions', 'estado_del_usos', 'condicions', 'colors'])
                    ->where('n_interno_bien', $n_interno_bien)
                    ->first();

        if ($biene) {
            return response()->json([
                'success' => true,
                'biene' => $biene,
                'message' => 'Bien encontrado'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró ningún bien con este código QR'
        ], 404);
    }

    public function index(Request $request)
    {
        abort_if(Gate::denies('bienes_index'), 403);

        $query = Biene::with(['unidad_administrativas', 'usuario_administrativos', 'usuario_de_usos', 'forma_adquisicions', 'estado_del_usos', 'condicions', 'colors']);

        if ($request->filled('filterType')) {
            switch ($request->filterType) {
                case 'fecha':
                    if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                        $query->whereBetween('fecha_adquisicion', [$request->fecha_desde, $request->fecha_hasta]);
                    } elseif ($request->filled('fecha_desde')) {
                        $query->where('fecha_adquisicion', '>=', $request->fecha_desde);
                    } elseif ($request->filled('fecha_hasta')) {
                        $query->where('fecha_adquisicion', '<=', $request->fecha_hasta);
                    }
                    break;
                case 'n_interno_bien':
                    if ($request->filled('secondFilter')) {
                        $query->where('n_interno_bien', $request->secondFilter);
                    }
                    break;
                case 'descripcion':
                    if ($request->filled('secondFilter')) {
                        $query->where('descripcion', $request->secondFilter);
                    }
                    break;
                case 'unidad_administrativa_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('unidad_administrativa_id', $request->secondFilter);
                    }
                    break;
                case 'usuario_de_uso_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('usuario_de_uso_id', $request->secondFilter);
                    }
                    break;
                case 'usuario_administrativo_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('usuario_administrativo_id', $request->secondFilter);
                    }
                    break;
                case 'estado_del_uso_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('estado_del_uso_id', $request->secondFilter);
                    }
                    break;
                case 'forma_adquisicion_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('forma_adquisicion_id', $request->secondFilter);
                    }
                    break;
                case 'marca':
                    if ($request->filled('secondFilter')) {
                        $query->where('marca', $request->secondFilter);
                    }
                    break;
                case 'modelo':
                    if ($request->filled('secondFilter')) {
                        $query->where('modelo', $request->secondFilter);
                    }
                    break;
                case 'condicion_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('condicion_id', $request->secondFilter);
                    }
                    break;
                case 'color_id':
                    if ($request->filled('secondFilter')) {
                        $query->where('color_id', $request->secondFilter);
                    }
                    break;
                case 'serial':
                    if ($request->filled('secondFilter')) {
                        $query->where('serial', 'like', '%' . $request->secondFilter . '%');
                    }
                    break;
                case 'codigo_qr':
                    if ($request->filled('busqueda')) {
                        // Search by n_interno_bien (which is stored in qr_code URL)
                        $query->where('n_interno_bien', $request->busqueda);
                    }
                    break;
                case 'busqueda':
                    if ($request->filled('busqueda')) {
                        $search = $request->busqueda;
                        $query->where(function($q) use ($search) {
                            $q->where('descripcion', 'like', '%' . $search . '%')
                              ->orWhere('n_factura', 'like', '%' . $search . '%')
                              ->orWhere('n_interno_bien', 'like', '%' . $search . '%')
                              ->orWhere('marca', 'like', '%' . $search . '%')
                              ->orWhere('modelo', 'like', '%' . $search . '%')
                              ->orWhere('serial', 'like', '%' . $search . '%')
                              ->orWhere('sede', 'like', '%' . $search . '%');
                        });
                    }
                    break;
            }
        }

        $bienes = $query->orderBy('id', 'desc')->paginate(10);
        $ultimoBien = Biene::orderBy('fecha_adquisicion', 'desc')->first();
        $valorTotal = Biene::sum('valor_factura');
        $bienesEnUso = Biene::where('estado_del_uso_id', 6)->count();
        $totalBienes = Biene::count();

        $estado_del_usos = \App\Models\EstadoDelUso::all();
        $condiciones = \App\Models\Condicion::all();
        $unidad_administrativas = \App\Models\UnidadAdministrativa::all();
        $forma_adquisiciones = \App\Models\FormaAdquisicion::all();
        $colors = \App\Models\Color::orderBy('color', 'asc')->get();
        $descripciones = Biene::distinct()->pluck('descripcion')->filter()->sort()->values();
        $marcas = Biene::distinct()->pluck('marca')->filter()->sort()->values();
        $modelos = Biene::distinct()->pluck('modelo')->filter()->sort()->values();
        $n_internos = Biene::distinct()->pluck('n_interno_bien')->filter()->sort()->values();
        $usuarios_de_uso = \App\Models\UsuarioDeUso::all();
        $usuarios_administrativos = \App\Models\UsuarioAdministrativo::all();

        return view('administracion.bienes_publicos.index', compact(
            'bienes', 'ultimoBien', 'valorTotal', 'bienesEnUso', 'totalBienes',
            'estado_del_usos', 'condiciones', 'unidad_administrativas',
            'forma_adquisiciones', 'colors', 'descripciones', 'marcas',
            'modelos', 'n_internos', 'usuarios_de_uso', 'usuarios_administrativos'
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('bienes_create'), 403);

        $descripciones = Biene::distinct()->pluck('descripcion')->filter()->sort()->values();

        return view('administracion.bienes_publicos.create', [
            "usuario_administrativos" => UsuarioAdministrativo::all(),
            "unidad_administrativas" => UnidadAdministrativa::all(),
            "usuario_de_usos" => UsuarioDeUso::all(),
            "forma_adquisiciones" => \App\Models\FormaAdquisicion::all(),
            "estado_del_usos" => \App\Models\EstadoDelUso::all(),
            "colors" => \App\Models\Color::orderBy('color', 'asc')->get(),
            "condiciones" => \App\Models\Condicion::all(),
            "descripciones" => $descripciones,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sede' => 'required|array',
            'sede.*' => 'required|string',
            'unidad_administrativa_id' => 'required|array',
            'unidad_administrativa_id.*' => 'required|integer',
            'usuario_administrativo_id' => 'required|array',
            'usuario_administrativo_id.*' => 'required|integer',
            'usuario_de_uso_id' => 'required|array',
            'usuario_de_uso_id.*' => 'required|integer',
            'n_interno_bien' => 'required|array',
            'n_interno_bien.*' => 'required|string',
            'descripcion' => 'required|array',
            'descripcion.*' => 'required|string',
            'forma_adquisicion_id' => 'required|array',
            'forma_adquisicion_id.*' => 'required|integer',
            'fecha_adquisicion' => 'required|array',
            'fecha_adquisicion.*' => 'required|date',
            'n_factura' => 'required|array',
            'n_factura.*' => 'required|string',
            'valor_factura' => 'required|array',
            'valor_factura.*' => 'required|numeric',
            'moneda' => 'required|array',
            'moneda.*' => 'required|string',
            'estado_del_uso_id' => 'required|array',
            'estado_del_uso_id.*' => 'required|integer',
            'condicion_id' => 'required|array',
            'condicion_id.*' => 'required|integer',
            'marca' => 'required|array',
            'marca.*' => 'required|string',
            'modelo' => 'required|array',
            'modelo.*' => 'required|string',
            'color_id' => 'required|array',
            'color_id.*' => 'required|integer',
            'serial' => 'required|array',
            'serial.*' => 'required|string',
            'comentario' => 'nullable|array',
            'comentario.*' => 'nullable|string',
            'fecha_comentario' => 'required|array',
            'fecha_comentario.*' => 'required|date',
            'qr_code' => 'nullable|array',
            'qr_code.*' => 'nullable|string',
        ]);

        $guardados = 0;

        DB::beginTransaction();
        try {
            $count = count($request->sede);

            for ($i = 0; $i < $count; $i++) {
                $biene = new Biene;
                $biene->sede = $request->sede[$i];
                $biene->unidad_administrativa_id = $request->unidad_administrativa_id[$i];
                $biene->usuario_administrativo_id = $request->usuario_administrativo_id[$i];
                $biene->usuario_de_uso_id = $request->usuario_de_uso_id[$i];
                $biene->n_interno_bien = $request->n_interno_bien[$i];
                $biene->descripcion = $request->descripcion[$i];
                $biene->forma_adquisicion_id = $request->forma_adquisicion_id[$i];
                $biene->fecha_adquisicion = $request->fecha_adquisicion[$i];
                $biene->n_factura = $request->n_factura[$i];
                $biene->valor_factura = $request->valor_factura[$i];
                $biene->moneda = $request->moneda[$i];
                $biene->estado_del_uso_id = $request->estado_del_uso_id[$i];
                $biene->condicion_id = $request->condicion_id[$i];
                $biene->marca = $request->marca[$i];
                $biene->modelo = $request->modelo[$i];
                $biene->color_id = $request->color_id[$i];
                $biene->serial = $request->serial[$i];
                $biene->comentario = $request->comentario[$i] ?? '';
                $biene->fecha_comentario = $request->fecha_comentario[$i];

                // Save QR code - use uploaded/generated or generate automatically
                $qrCodeFromForm = !empty($request->qr_code[$i]) ? $request->qr_code[$i] : null;
                if (!empty($qrCodeFromForm)) {
                    $biene->qr_code = $qrCodeFromForm;
                } elseif (!empty($request->n_interno_bien[$i])) {
                    // Generate QR code automatically based on n_interno_bien
                    $biene->qr_code = $this->generateQrCodeUrl($request->n_interno_bien[$i]);
                }

                $biene->save();
                $guardados++;
            }

            DB::commit();

            return redirect()->route('administracion.bienes_publicos.index')
                ->with('datos', "Se guardaron {$guardados} registro(s) exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al guardar: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        abort_if(Gate::denies('bienes_edit'), 403);
        $biene = Biene::find($id);
        $descripciones = Biene::distinct()->pluck('descripcion')->filter()->sort()->values();
        return view('administracion.bienes_publicos.edit', [
            'biene' => $biene,
            'usuario_administrativos' => UsuarioAdministrativo::all(),
            'usuario_de_usos' => UsuarioDeUso::all(),
            'forma_adquisiciones' => \App\Models\FormaAdquisicion::all(),
            'estado_del_usos' => \App\Models\EstadoDelUso::all(),
            'colors' => \App\Models\Color::orderBy('color', 'asc')->get(),
            'condiciones' => \App\Models\Condicion::all(),
            'unidad_administrativas' => UnidadAdministrativa::all(),
            'descripciones' => $descripciones,
        ]);
    }

    public function update(Request $request, $id)
    {
        $biene = Biene::find($id);
        $biene->sede = $request->input('sede');
        $biene->unidad_administrativa_id = $request->input('unidad_administrativa_id');
        $biene->usuario_administrativo_id = $request->input('usuario_administrativo_id');
        $biene->usuario_de_uso_id = $request->input('usuario_de_uso_id');
        $biene->n_interno_bien = $request->input('n_interno_bien');
        $biene->descripcion = $request->input('descripcion');
        $biene->forma_adquisicion_id = $request->input('forma_adquisicion_id');
        $biene->fecha_adquisicion = $request->input('fecha_adquisicion');
        $biene->n_factura = $request->input('n_factura');
        $biene->valor_factura = $request->input('valor_factura');
        $biene->moneda = $request->input('moneda');
        $biene->estado_del_uso_id = $request->input('estado_del_uso_id');
        $biene->condicion_id = $request->input('condicion_id');
        $biene->marca = $request->input('marca');
        $biene->modelo = $request->input('modelo');
        $biene->color_id = $request->input('color_id');
        $biene->serial = $request->input('serial');
        $biene->comentario = $request->input('comentario');
        $biene->fecha_comentario = $request->input('fecha_comentario');

        // Save QR code - use uploaded/generated or generate automatically
        $qrCodeFromForm = $request->has('qr_code') ? $request->input('qr_code') : null;
        if (!empty($qrCodeFromForm)) {
            $biene->qr_code = $qrCodeFromForm;
        } elseif (!empty($request->input('n_interno_bien'))) {
            // Generate QR code automatically based on n_interno_bien
            $biene->qr_code = $this->generateQrCodeUrl($request->input('n_interno_bien'));
        }

        $biene->save();
        return redirect()->route("administracion.bienes_publicos.index", $biene->id)->with('success', 'Bien actualizado correctamente');
    }

    public function destroy($id)
    {
        abort_if(Gate::denies('bienes_destroy'), 403);
        $biene = Biene::find($id);
        $biene->delete();
        return redirect()->route('administracion.bienes_publicos.index')->with('datos', 'Registro eliminado');
    }

    public function exportExcel(Request $request)
    {
        abort_if(Gate::denies('bienes_index'), 403);
        return Excel::download(new BienesExport($request->all()), 'bienes_publicos.xlsx');
    }

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('bienes_index'), 403);
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 300);

        $query = Biene::with(['unidad_administrativas', 'usuario_administrativos', 'usuario_de_usos', 'forma_adquisicions', 'estado_del_usos', 'condicions', 'colors']);

        if ($request->filled('busqueda')) {
            $search = $request->busqueda;
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', '%' . $search . '%')
                  ->orWhere('n_factura', 'like', '%' . $search . '%')
                  ->orWhere('n_interno_bien', 'like', '%' . $search . '%')
                  ->orWhere('marca', 'like', '%' . $search . '%')
                  ->orWhere('modelo', 'like', '%' . $search . '%')
                  ->orWhere('serial', 'like', '%' . $search . '%')
                  ->orWhere('sede', 'like', '%' . $search . '%');
            });
        }

        $bienes = $query->get();
        $totalBienes = $bienes->count();
        $valorTotal = $bienes->sum(function($item) {
            $valor = $item->valor_factura;
            if (empty($valor)) return 0;
            return is_numeric($valor) ? (float) $valor : (float) str_replace(',', '.', str_replace(' ', '', $valor));
        });
        $statsEnTransito = $bienes->where('estado_del_uso_id', 6)->count();
        $statsTotalEnUso = $bienes->where('estado_del_uso_id', 1)->count();

        $pdf = PDF::loadView('administracion.bienes_publicos.pdf', compact('bienes', 'totalBienes', 'valorTotal', 'statsEnTransito', 'statsTotalEnUso'));
        return $pdf->download('bienes_publicos.pdf');
    }

    public function getUsuariosAdministrativos($unidad_id)
    {
        $usuarios = UsuarioAdministrativo::where('unidad_administrativa_id', $unidad_id)
            ->select('id', 'usuario_administrativo')
            ->orderBy('usuario_administrativo')
            ->get();
        return response()->json($usuarios);
    }

    public function getUsuariosDeUso($usuario_admin_id)
    {
        $usuarioAdmin = UsuarioAdministrativo::find($usuario_admin_id);
        if (!$usuarioAdmin) {
            return response()->json([]);
        }
        $usuarios = UsuarioDeUso::where('unidad_administrativa_id', $usuarioAdmin->unidad_administrativa_id)
            ->select('id', 'usuario_de_uso')
            ->orderBy('usuario_de_uso')
            ->get();
        return response()->json($usuarios);
    }

    public function getDescripciones(Request $request)
    {
        $query = $request->get('q', '');
        $descripciones = Biene::distinct()->pluck('descripcion')->filter()->sort()->values();
        if (!empty($query)) {
            $descripciones = $descripciones->filter(function($desc) use ($query) {
                return stripos($desc, $query) !== false;
            })->values();
        }
        return response()->json($descripciones->toArray());
    }

    public function storeDescripcion(Request $request)
    {
        $request->validate(['descripcion' => 'required|string|max:255']);
        $exists = Biene::where('descripcion', $request->descripcion)->exists();
        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Esta descripción ya existe'], 422);
        }
        return response()->json(['success' => true, 'descripcion' => $request->descripcion, 'message' => 'Descripción válida']);
    }
}

