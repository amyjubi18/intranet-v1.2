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
        $search = $request->get('search');
        $query = Role::query()->with('permissions');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('permissions', function($subq) use ($search) {
                      $subq->where('name', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        // Ordenamiento
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $roles = $query->paginate(12)->appends($request->query());

        /* return view('gtic.roles.index', compact('roles')); */
        return view('gtic.roles.index', [
            'roles' => $roles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         /* abort_if(Gate::denies('role_create'), 403);  */
        $permissions = Permission::all()->pluck('name', 'id');
        return view('gtic.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = Role::create($request->only('name'));
        $role->permissions()->sync($request->input('permissions', []));

        if ($request->action === 'save_and_new') {
            return redirect()->route('gtic.roles.create')->with('success', 'Rol creado exitosamente. Puede crear otro.');
        }

        return redirect()->route('gtic.roles.index')->with('success', 'Rol guardado exitosamente.');
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
        $permissions= Permission::all()->pluck('name','id');
        $role->load('permissions');
        return view('gtic.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $role->update($request->only('name'));
        $role->permissions()->sync($request->input('permissions',[]));
        return redirect()->route('gtic.roles.index');
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
}

