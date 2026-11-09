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
        $selectedColor = trim($request->get('selected_color', ''));
        $sort = $request->get('sort', 'color');
        $direction = $request->get('direction', 'asc');

        // Validar campos de ordenamiento y dirección
        $sort = in_array($sort, ['color', 'id', 'created_at']) ? $sort : 'color';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        $query = Color::query();

        if (!empty($selectedColor)) {
            $query->where('color', $selectedColor);
        } elseif (!empty($search)) {
            $query->where('color', 'LIKE', '%'.$search.'%');
        }

        $colors = $query->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $todosLosColors = Color::orderBy('color', 'asc')->get();

        return view('administracion.colors.index', compact('colors', 'search', 'selectedColor', 'sort', 'direction', 'todosLosColors'));
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
