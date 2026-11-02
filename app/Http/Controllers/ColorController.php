<?php

namespace App\Http\Controllers;
use App\Models\Color;
use App\Http\Requests\SaveColorRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request){

        abort_if(Gate::denies('color_index'), 403);

        $search = trim($request->get('search', ''));
        $sort = $request->get('sort', 'color');
        $direction = $request->get('direction', 'asc');

        // Validar dirección
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $colors = DB::table('colors')
            ->select('id', 'color')
            ->where('color', 'LIKE', '%'.$search.'%')
            ->orderBy($sort, $direction)
            ->paginate(15);

        $todosLosColors = Color::all();
        return view('administracion.colors.index', compact('colors', 'search', 'sort', 'direction', 'todosLosColors'));
    }
     public function show(){
        return view('administracion.colors.show', ['colors' => Color::get()]);
    }
    public function create(Color $color){
         abort_if(Gate::denies('color_create'), 403);
         //dd($color);
        return view('administracion.colors.create',['color'=> $color]);
    }
    public function store(Request $request){
        //dd($color);
        $request->validate([
            'color' => 'required|unique:colors',

        ]);
        //color::create($request->validated());
         $color = new Color;
        $color->color = $request->color;
        $color->save();
        return redirect()->route('administracion.colors.index')->with('status', 'Color creado');

    }
    public function edit(Color $color)
    {
         abort_if(Gate::denies('color_edit'), 403);
        return view('administracion.colors.edit', compact('color'));
    }
    public function update(Request $request, Color $color)
    {
       $color->update($request->only('color'));
        return redirect()->route('administracion.colors.index');
    }
    public function destroy(Color $color)
    {
         abort_if(Gate::denies('color_destroy'), 403);
        $color->delete();
        return redirect()->route('administracion.colors.index')->with('Color Eliminado');
    }
}
