<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('color_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'color');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['color', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'color';

        $query = DB::table('colors')
            ->select('id', 'color');

        if (!empty($search)) {
            $query->where('color', 'LIKE', '%' . $search . '%');
        }

        $colors = $query->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'colors' => $colors,
                    'permissions' => [
                        'can_create' => Gate::allows('color_create'),
                        'can_edit' => Gate::allows('color_edit'),
                        'can_destroy' => Gate::allows('color_destroy'),
                    ]
                ]
            ]);
        }

        $todosLosColors = Color::all();
        return view('administracion.colors.index', compact('colors', 'search', 'sort', 'direction', 'todosLosColors'));
    }

    public function show()
    {
        return view('administracion.colors.show', ['colors' => Color::get()]);
    }

    public function create(Color $color)
    {
        abort_if(Gate::denies('color_create'), 403);
        return view('administracion.colors.create', ['color' => $color]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('color_create'), 403);

        $request->validate([
            'color' => 'required|unique:colors,color',
        ], [
            'color.required' => 'El nombre del color es obligatorio.',
            'color.unique' => 'Este color ya se encuentra registrado.',
        ]);

        $color = new Color;
        $color->color = $request->color;
        $color->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Color registrado exitosamente',
                'data' => $color
            ], 200);
        }

        return redirect()->route('administracion.colors.index')->with('status', 'Color creado');
    }

    public function edit(Color $color)
    {
        abort_if(Gate::denies('color_edit'), 403);
        return view('administracion.colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        abort_if(Gate::denies('color_edit'), 403);

        $request->validate([
            'color' => 'required|unique:colors,color,' . $color->id,
        ], [
            'color.required' => 'El nombre del color es obligatorio.',
            'color.unique' => 'Este color ya se encuentra registrado.',
        ]);

        $color->update($request->only('color'));

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Color actualizado correctamente',
                'data' => $color
            ], 200);
        }

        return redirect()->route('administracion.colors.index');
    }

    public function destroy(Color $color)
    {
        abort_if(Gate::denies('color_destroy'), 403);
        $color->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Color eliminado correctamente'
            ], 200);
        }

        return redirect()->route('administracion.colors.index')->with('Color Eliminado');
    }
}
