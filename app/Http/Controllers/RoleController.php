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

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => [
                    'roles' => $roles,
                    'available_permissions' => Permission::orderBy('name')->get(['id', 'name']),
                    'permissions' => [
                        'can_create' => auth()->user()?->can('role_create') ?? true,
                        'can_edit' => auth()->user()?->can('role_edit') ?? true,
                        'can_destroy' => auth()->user()?->can('role_destroy') ?? true,
                    ]
                ],
                'message' => 'Roles obtenidos con éxito'
            ], 200);
        }

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

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 201,
                    'success' => true,
                    'data' => $role->load('permissions'),
                    'message' => 'Rol guardado exitosamente.'
                ], 201);
            }

            if ($request->action === 'save_and_new') {
                return redirect()->route('gtic.roles.create')->with('success', 'Rol creado exitosamente. Puede crear otro.');
            }

            return redirect()->route('gtic.roles.index')->with('success', 'Rol guardado exitosamente.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 500,
                    'success' => false,
                    'message' => 'Error al crear el rol: ' . $e->getMessage()
                ], 500);
            }
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

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $role->load('permissions'),
                'message' => 'Rol actualizado exitosamente'
            ], 200);
        }

        return redirect()->route('gtic.roles.index')->with('success', 'Rol actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role, Request $request)
    {
        abort_if(Gate::denies('role_destroy'), 403);
        $role->delete();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ], 200);
        }

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

