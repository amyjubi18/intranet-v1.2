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
        abort_if(Gate::denies('role_index'), 403);
        $roles = Role::query()->with('permissions')->when(request('search'), function($query){
           return $query->where('name', 'LIKE','%'.request('search').'%')->orWhereHas('permissions', function($q){
            $q->where('name','LIKE','%'.request('search').'%');
           });
        })->latest()->paginate(5);
        return view('gtic.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('role_create'), 403);
        $permissions = Permission::all()->pluck('name', 'id');
/*         dd($permisos);
 */        return view('gtic.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role= Role::create($request->only('name'));
        $role->syncPermissions($request->input('permissions',[]));
        return redirect()->route('gtic.roles.index');
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
        abort_if(Gate::denies('role_edit'), 403);
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
        $role->syncPermissions($request->input('permissions',[]));
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
