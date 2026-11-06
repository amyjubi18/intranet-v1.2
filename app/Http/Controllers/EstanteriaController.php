<?php

namespace App\Http\Controllers;

use App\Models\Estanteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class EstanteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(Gate::denies('estanterias_index'), 403);

        $search = trim($request->get('search_general') ?: $request->get('search', ''));
        $sort = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Estanteria::query()->withCount('documentos');

        if ($search) {
            $query->where('nombre', 'LIKE', '%' . $search . '%');
        }

        $perPage = (int) $request->input('per_page', 15);
        $estanterias = $query->orderBy($sort, $direction)->paginate($perPage);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'estanterias' => $estanterias,
                    'permissions' => [
                        'can_create' => auth()->user()?->can('estanterias_create') ?? false,
                        'can_edit' => auth()->user()?->can('estanterias_edit') ?? false,
                        'can_destroy' => auth()->user()?->can('estanterias_destroy') ?? false,
                    ]
                ],
                'message' => 'Estanterías obtenidas exitosamente'
            ], 200);
        }

        return view('administracion.estanterias.index', [
            'estanterias' => $estanterias,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('estanterias_create'), 403);
        return view('administracion.estanterias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('estanterias_create'), 403);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $estanteria = new Estanteria;
            $estanteria->nombre = $request->get('nombre');
            $estanteria->user_id = $request->user()->id;

            // Manejo de imagen
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');
                $imagenNombre = Str::slug($request->get('nombre'), '-') . '-' . time() . '.' . $imagen->getClientOriginalExtension();

                $directorio = storage_path('app/public/estanterias');

                if (!file_exists($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $imagen->move($directorio, $imagenNombre);
                $estanteria->imagen = $imagenNombre;
            }

            $estanteria->save();

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Estantería creada correctamente',
                    'data' => $estanteria->loadCount('documentos')
                ], 200);
            }

            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería creada correctamente');
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
    public function show(string $id, Request $request)
    {
        abort_if(Gate::denies('estanterias_index'), 403);
        $estanteria = Estanteria::withCount('documentos')->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $estanteria
            ], 200);
        }

        return redirect()->route('administracion.documentos.index', ['estanteria_id' => $id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        abort_if(Gate::denies('estanterias_edit'), 403);
        $estanteria = Estanteria::withCount('documentos')->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $estanteria
            ], 200);
        }

        return view('administracion.estanterias.edit', ['estanteria' => $estanteria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        abort_if(Gate::denies('estanterias_edit'), 403);

        $estanteria = Estanteria::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $estanteria->nombre = $request->get('nombre');

            // Manejo de imagen nueva
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($estanteria->imagen && file_exists(storage_path('app/public/estanterias/' . $estanteria->imagen))) {
                    @unlink(storage_path('app/public/estanterias/' . $estanteria->imagen));
                }

                $imagen = $request->file('imagen');
                $imagenNombre = Str::slug($request->get('nombre'), '-') . '-' . time() . '.' . $imagen->getClientOriginalExtension();
                $directorio = storage_path('app/public/estanterias');

                if (!file_exists($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $imagen->move($directorio, $imagenNombre);
                $estanteria->imagen = $imagenNombre;
            }

            $estanteria->save();

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Estantería actualizada correctamente',
                    'data' => $estanteria->fresh()->loadCount('documentos')
                ], 200);
            }

            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería actualizada correctamente');
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
        abort_if(Gate::denies('estanterias_destroy'), 403);

        try {
            DB::beginTransaction();

            $estanteria = Estanteria::findOrFail($id);

            // Eliminar imagen si existe
            if ($estanteria->imagen && file_exists(storage_path('app/public/estanterias/' . $estanteria->imagen))) {
                @unlink(storage_path('app/public/estanterias/' . $estanteria->imagen));
            }

            $estanteria->delete();

            DB::commit();

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 200,
                    'success' => true,
                    'message' => 'Estantería eliminada correctamente'
                ], 200);
            }

            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería eliminada correctamente');
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
}
