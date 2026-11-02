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

       /*  $search = $request->search;
        $permissions = Permission::where('name','LIKE','%'.$search.'%')->first()->orderBy('name','asc')->paginate(10); */

        $search =trim($request->get('search'));
        $permissions = DB::table('permissions')->select('id', 'name')->where('name', 'LIKE', '%'.$search.'%')->orderBy('name', 'asc')->paginate(15);
        return view('gtic.permisos.index', compact('permissions', 'search'));

        /* return view('gtic.permisos.index' ,['permissions'=>$permissions]); */
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
        Permission::create($request->only('name'));
        return redirect()->route('gtic.permisos.index');
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
       $permission->update($request->only('name'));
        return redirect()->route('gtic.permisos.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        abort_if(Gate::denies('permission_destroy'), 403);
        $permission->delete();
        return redirect()->route('gtic.permisos.index')->with('Permiso eliminado');
    }
}
