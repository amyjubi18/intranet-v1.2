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

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'nombre');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Estanteria::query()->withCount('documentos');

        if ($search) {
            $query->where('nombre', 'LIKE', '%' . $search . '%');
        }

        $estanterias = $query->orderBy($sort, $direction)->paginate(15);

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

                // Usar storage_path para mejor compatibilidad
                $directorio = storage_path('app/public/estanterias');

                // Crear directorio si no existe
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $imagen->move($directorio, $imagenNombre);
                $estanteria->imagen = $imagenNombre;
            }

            $estanteria->save();

            DB::commit();
            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería creada correctamente');
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
        return redirect()->route('administracion.documentos.index', ['estanteria_id' => $id]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        abort_if(Gate::denies('estanterias_edit'), 403);
        $estanteria = Estanteria::find($id);
        return view('administracion.estanterias.edit', ['estanteria' => $estanteria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estanteria $estanteria)
    {
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
                    unlink(storage_path('app/public/estanterias/' . $estanteria->imagen));
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
            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería actualizada correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estanteria $estanteria)
    {
        abort_if(Gate::denies('estanterias_destroy'), 403);

        try {
            DB::beginTransaction();

            // Eliminar imagen si existe
            if ($estanteria->imagen && file_exists(storage_path('app/public/estanterias/' . $estanteria->imagen))) {
                unlink(storage_path('app/public/estanterias/' . $estanteria->imagen));
            }

            $estanteria->delete();

            DB::commit();
            return redirect()->route('administracion.estanterias.index')
                ->with('success', 'Estantería eliminada correctamente');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

