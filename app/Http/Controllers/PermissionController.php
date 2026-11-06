<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        abort_if(Gate::denies('permission_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $permissions = DB::table('permissions')
            ->select('id', 'name')
            ->where('name', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'permissions' => $permissions,
                    'auth_permissions' => [
                        'can_create' => auth()->user()?->can('permission_create') ?? true,
                        'can_edit' => auth()->user()?->can('permission_edit') ?? true,
                        'can_destroy' => auth()->user()?->can('permission_destroy') ?? true,
                    ]
                ],
                'message' => 'Permisos obtenidos con éxito'
            ], 200);
        }

        return view('gtic.permisos.index', compact('permissions', 'search', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         abort_if(Gate::denies('permission_create'), 403);
        return view('gtic.permisos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ], [
            'name.required' => 'El nombre del permiso es obligatorio',
            'name.unique' => 'Este permiso ya existe',
        ]);

        $permission = Permission::create($request->only('name'));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 201,
                'success' => true,
                'data' => $permission,
                'message' => 'Permiso creado exitosamente'
            ], 201);
        }

        return redirect()->route('gtic.permisos.index')->with('success', 'Permiso creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)

    {
         abort_if(Gate::denies('permission_show'), 403);
        return view('gtic.permisos.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        abort_if(Gate::denies('permission_edit'), 403);
        return view('gtic.permisos.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ], [
            'name.required' => 'El nombre del permiso es obligatorio',
            'name.unique' => 'Este permiso ya existe',
        ]);

        $permission->update($request->only('name'));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $permission,
                'message' => 'Permiso actualizado exitosamente'
            ], 200);
        }

        return redirect()->route('gtic.permisos.index')->with('success', 'Permiso actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission, Request $request)
    {
        abort_if(Gate::denies('permission_destroy'), 403);
        $permission->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Permiso eliminado exitosamente'
            ], 200);
        }

        return redirect()->route('gtic.permisos.index')->with('Permiso eliminado');
    }

    /**
     * Verificar si el nombre del permiso ya existe (para validación AJAX)
     */
    public function checkName(Request $request)
    {
        $name = $request->get('name', '');
        $excludeId = $request->get('exclude_id', null);

        if (empty($name)) {
            return response()->json(['exists' => false]);
        }

        $query = Permission::where('name', $name);

        // Excluir el ID actual si se está editando
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}
