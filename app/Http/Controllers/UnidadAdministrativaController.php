<?php

namespace App\Http\Controllers;

use App\Models\UnidadAdministrativa;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UnidadAdministrativaController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('unidad_adminis_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';
        $allowedSorts = ['name', 'id', 'created_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'name';

        $query = DB::table('unidad_administrativas')
            ->select('id', 'name');

        if (!empty($search)) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $unidad_administrativas = $query->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'unidad_administrativas' => $unidad_administrativas,
                    'permissions' => [
                        'can_create' => Gate::allows('unidad_adminis_create'),
                        'can_edit' => Gate::allows('unidad_adminis_edit'),
                        'can_destroy' => Gate::allows('unidad_adminis_destroy'),
                    ]
                ]
            ]);
        }

        return view('administracion.unidad_administrativa.index', compact('unidad_administrativas', 'search', 'sort', 'direction'));
    }

    public function show()
    {
        return view('administracion.unidad_administrativa.show', ['unidad_administrativas' => UnidadAdministrativa::get()]);
    }

    public function create(UnidadAdministrativa $unidad_administrativa)
    {
        abort_if(Gate::denies('unidad_adminis_create'), 403);
        return view('administracion.unidad_administrativa.create', ['unidad_administrativa' => $unidad_administrativa]);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('unidad_adminis_create'), 403);

        $request->validate([
            'name' => 'required|unique:unidad_administrativas,name',
        ], [
            'name.required' => 'El nombre de la unidad administrativa es obligatorio.',
            'name.unique' => 'Esta unidad administrativa ya se encuentra registrada.',
        ]);

        $unidad_administrativa = new UnidadAdministrativa;
        $unidad_administrativa->name = $request->name;
        $unidad_administrativa->save();

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Unidad Administrativa creada exitosamente',
                'data' => $unidad_administrativa
            ], 200);
        }

        return redirect()->route('administracion.unidad_administrativa.index')->with('status', 'Unidad Administrativa creada');
    }

    public function edit(UnidadAdministrativa $unidad_administrativa)
    {
        abort_if(Gate::denies('unidad_adminis_edit'), 403);
        return view('administracion.unidad_administrativa.edit', compact('unidad_administrativa'));
    }

    public function update(Request $request, UnidadAdministrativa $unidad_administrativa)
    {
        abort_if(Gate::denies('unidad_adminis_edit'), 403);

        $request->validate([
            'name' => 'required|unique:unidad_administrativas,name,' . $unidad_administrativa->id,
        ], [
            'name.required' => 'El nombre de la unidad administrativa es obligatorio.',
            'name.unique' => 'Esta unidad administrativa ya se encuentra registrada.',
        ]);

        $unidad_administrativa->update($request->only('name'));

        if ($request->wantsJson() || $request->is('api/*') || $request->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Unidad Administrativa actualizada exitosamente',
                'data' => $unidad_administrativa
            ], 200);
        }

        return redirect()->route('administracion.unidad_administrativa.index');
    }

    public function destroy(UnidadAdministrativa $unidad_administrativa)
    {
        abort_if(Gate::denies('unidad_adminis_destroy'), 403);
        $unidad_administrativa->delete();

        if (request()->wantsJson() || request()->is('api/*') || request()->ajax()) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Unidad Administrativa eliminada correctamente'
            ], 200);
        }

        return redirect()->route('administracion.unidad_administrativa.index')->with('Unidad Administrativa Eliminada');
    }
}
