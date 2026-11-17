<?php

namespace App\Http\Controllers;

use App\Models\Impresora;
use App\Models\Gerencia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Http\Request;

class ImpresoraController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('impresora_index'), 403);

        $search = trim($request->get('busqueda', $request->get('search', '')));
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        // Validar sort
        $allowedSorts = ['id', 'impresora', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $query = Impresora::query()->with(['gerencias.area']);

        // Aplicar filtro por nombre de impresora
        if ($request->filled('nombre-impresora') || $request->filled('impresora_id')) {
            $impId = $request->input('nombre-impresora', $request->input('impresora_id'));
            $query->where('id', $impId);
        }

        // Aplicar filtro por gerencia
        if ($request->filled('gerencia_id')) {
            $query->whereHas('gerencias', function ($q) use ($request) {
                $q->where('gerencias.id', $request->input('gerencia_id'));
            });
        }

        // Aplicar filtro por fecha de creación
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        // Aplicar búsqueda general
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('impresora', 'like', "%{$search}%")
                  ->orWhereHas('gerencias', function ($gq) use ($search) {
                      $gq->where('gerencia', 'like', "%{$search}%")
                         ->orWhereHas('area', function ($aq) use ($search) {
                             $aq->where('area', 'like', "%{$search}%");
                         });
                  });
            });
        }

        $impresoras = $query->orderBy($sort, $direction)->paginate(15)->appends($request->all());
        $all_impresoras = Impresora::orderBy('impresora')->get();
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();

        if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'impresoras' => $impresoras,
                    'all_impresoras' => $all_impresoras,
                    'gerencias' => $gerencias,
                    'permissions' => [
                        'can_create' => auth()->user()?->can('impresora_create') ?? false,
                        'can_edit' => auth()->user()?->can('impresora_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('impresora_destroy') ?? false,
                    ]
                ],
                'message' => 'Impresoras obtenidas exitosamente'
            ], 200);
        }

        return view('administracion.impresoras.index', [
            'impresoras' => $impresoras,
            'all_impresoras' => $all_impresoras,
            'gerencias' => $gerencias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    public function create(Impresora $impresora)
    {
        abort_if(Gate::denies('impresora_create'), 403);
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();
        return view('administracion.impresoras.create', [
            'impresora' => $impresora,
            'gerencias' => $gerencias
        ]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('impresora_create'), 403);

        $request->validate([
            'impresora' => 'required|string|max:191|unique:impresoras,impresora',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'gerencia_ids' => 'nullable|array',
            'gerencia_ids.*' => 'exists:gerencias,id',
        ], [
            'impresora.required' => 'El nombre del modelo de impresora es obligatorio.',
            'impresora.unique' => 'Esta impresora ya se encuentra registrada.',
            'imagen.image' => 'El archivo debe ser una imagen válida (JPG, PNG, WEBP, GIF).',
            'imagen.max' => 'La imagen no puede pesar más de 5MB.'
        ]);

        $impresora = new Impresora;
        $impresora->impresora = trim($request->impresora);

        if ($request->hasFile('imagen')) {
            $imagePath = $request->file('imagen')->store('impresoras', 'public');
            $impresora->imagen = $imagePath;
        }

        $impresora->user_id = auth()->id();
        $impresora->save();

        // Sincronizar las gerencias seleccionadas
        if ($request->has('gerencia_ids') && is_array($request->gerencia_ids)) {
            $impresora->gerencias()->sync($request->gerencia_ids);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $impresora->load('gerencias.area');
            return response()->json([
                'success' => true,
                'message' => 'Impresora registrada exitosamente.',
                'data' => $impresora
            ], 201);
        }

        return redirect()->route('administracion.impresoras.index')->with('status', 'impresora creado');
    }

    public function edit(Impresora $impresora)
    {
        abort_if(Gate::denies('impresora_edit'), 403);
        $gerencias = Gerencia::with('area')->orderBy('gerencia')->get();
        return view('administracion.impresoras.edit', compact('impresora', 'gerencias'));
    }

    public function update(Request $request, Impresora $impresora)
    {
        abort_if(Gate::denies('impresora_edit'), 403);

        $request->validate([
            'impresora' => 'required|string|max:191|unique:impresoras,impresora,' . $impresora->id,
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'gerencia_ids' => 'nullable|array',
            'gerencia_ids.*' => 'exists:gerencias,id',
        ], [
            'impresora.required' => 'El nombre del modelo de impresora es obligatorio.',
            'impresora.unique' => 'Esta impresora ya se encuentra registrada.',
            'imagen.image' => 'El archivo debe ser una imagen válida (JPG, PNG, WEBP, GIF).',
            'imagen.max' => 'La imagen no puede pesar más de 5MB.'
        ]);

        $impresora->impresora = trim($request->impresora);

        if ($request->hasFile('imagen')) {
            // Delete old image if exists
            if ($impresora->imagen && Storage::disk('public')->exists($impresora->imagen)) {
                Storage::disk('public')->delete($impresora->imagen);
            }
            $imagePath = $request->file('imagen')->store('impresoras', 'public');
            $impresora->imagen = $imagePath;
        }

        $impresora->user_id = auth()->id();
        $impresora->save();

        // Sincronizar las gerencias seleccionadas
        if ($request->has('gerencia_ids')) {
            $gerenciaIds = is_array($request->gerencia_ids) ? $request->gerencia_ids : [];
            $impresora->gerencias()->sync($gerenciaIds);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $impresora->load('gerencias.area');
            return response()->json([
                'success' => true,
                'message' => 'Impresora actualizada exitosamente.',
                'data' => $impresora
            ], 200);
        }

        return redirect()->route('administracion.impresoras.index');
    }

    public function destroy(Request $request, Impresora $impresora)
    {
        abort_if(Gate::denies('impresora_destroy'), 403);

        try {
            // Verificar si tiene consumibles o inventario
            if (method_exists($impresora, 'cartuchos') && $impresora->cartuchos()->exists()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar la impresora porque tiene consumibles asociados.'
                    ], 422);
                }
                return back()->with('error', 'No se puede eliminar porque tiene consumibles asociados.');
            }

            // Delete associated image if exists
            if ($impresora->imagen && Storage::disk('public')->exists($impresora->imagen)) {
                Storage::disk('public')->delete($impresora->imagen);
            }

            $impresora->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Impresora eliminada exitosamente.'
                ], 200);
            }

            return redirect()->route('administracion.impresoras.index')->with('impresora Eliminado');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al eliminar la impresora.');
        }
    }
}
