<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\AreaAdscrita;
use Illuminate\Http\Request;

class AreaAdscritaController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('area_adscrita_index'), 403);

        $search =trim($request->get('search'));
        $area_adscritas = DB::table('area_adscritas')->select('id', 'area_adscrita')->where('area_adscrita', 'LIKE', '%'.$search.'%')->orderBy('area_adscrita', 'asc')->paginate(15);
        return view('gestion-humana.area_adscrita.index', compact('area_adscritas', 'search'));

    }
    public function create(AreaAdscrita $area_adscrita){
         abort_if(Gate::denies('area_adscrita_create'), 403);
         //dd($area_adscrita);
        return view('gestion-humana.area_adscrita.create',['area_adscrita'=> $area_adscrita]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'area_adscrita' => 'required|unique:area_adscritas',

        ]);
        //color::create($request->validated());
         $area_adscrita = new AreaAdscrita;
        $area_adscrita->area_adscrita = $request->area_adscrita;
        $area_adscrita->save();
        return redirect()->route('gestion-humana.area_adscrita.index')->with('status', 'area adscrita creado');

    }
    public function edit(AreaAdscrita $area_adscrita)
    {
         abort_if(Gate::denies('area_adscrita_edit'), 403);
        return view('gestion-humana.area_adscrita.edit', compact('area_adscrita'));
    }
    public function update(Request $request, AreaAdscrita $area_adscrita)
    {
       $area_adscrita->update($request->only('area_adscrita'));
        return redirect()->route('gestion-humana.area_adscrita.index');
    }
    public function destroy(AreaAdscrita $area_adscrita)
    {
         abort_if(Gate::denies('area_adscrita_destroy'), 403);
        $area_adscrita->delete();
        return redirect()->route('gestion-humana.area_adscrita.index')->with('Area Adscrita Eliminado');
    }
}
