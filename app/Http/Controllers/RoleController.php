<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /*  abort_if(Gate::denies('role_index'), 403);  */
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Role::query()->with('permissions');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('permissions', function($subq) use ($search) {
                      $subq->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $roles = $query->orderBy($sort, $direction)->paginate(15)->appends($request->query());

        return view('gtic.roles.index', [
            'roles' => $roles,
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
         /* abort_if(Gate::denies('role_create'), 403);  */
        $permissions = Permission::orderBy('name')->pluck('name', 'id');
        return view('gtic.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-zA-Z_]+$/',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'El nombre del rol es obligatorio',
            'name.max' => 'El nombre no puede exceder 50 caracteres',
            'name.unique' => 'Este nombre de rol ya existe',
            'name.regex' => 'El nombre solo puede contener letras y guiones bajos',
            'permissions.required' => 'Debe seleccionar al menos un permiso',
            'permissions.array' => 'Los permisos deben ser una lista válida',
            'permissions.min' => 'Debe seleccionar al menos un permiso',
            'permissions.*.exists' => 'Uno de los permisos seleccionados no existe',
        ]);

        try {
            $role = Role::create($request->only('name'));
            $role->permissions()->sync($request->input('permissions', []));

            if ($request->action === 'save_and_new') {
                return redirect()->route('gtic.roles.create')->with('success', 'Rol creado exitosamente. Puede crear otro.');
            }

            return redirect()->route('gtic.roles.index')->with('success', 'Rol guardado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al crear el rol: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        return view('gtic.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        /* abort_if(Gate::denies('role_edit'), 403);  */
        $permissions = Permission::orderBy('name')->pluck('name','id');
        $role->load('permissions');
        return view('gtic.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $role->id . '|regex:/^[a-zA-Z_]+$/',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => 'El nombre del rol es obligatorio',
            'name.max' => 'El nombre no puede exceder 50 caracteres',
            'name.unique' => 'Este nombre de rol ya existe',
            'name.regex' => 'El nombre solo puede contener letras y guiones bajos',
            'permissions.required' => 'Debe seleccionar al menos un permiso',
            'permissions.array' => 'Los permisos deben ser una lista válida',
            'permissions.min' => 'Debe seleccionar al menos un permiso',
            'permissions.*.exists' => 'Uno de los permisos seleccionados no existe',
        ]);

        $role->update($request->only('name'));
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('gtic.roles.index')->with('success', 'Rol actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        abort_if(Gate::denies('role_destroy'), 403);
        $role->delete();
        return redirect()->route('gtic.roles.index')->with('Rol eliminado');
    }

    /**
     * Verificar si el nombre del rol ya existe (para validación AJAX)
     */
    public function checkName(Request $request)
    {
        $name = $request->get('name', '');
        $excludeId = $request->get('exclude_id', null);

        if (empty($name)) {
            return response()->json(['exists' => false]);
        }

        $query = Role::where('name', $name);

        // Excluir el ID actual si se está editando
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}

