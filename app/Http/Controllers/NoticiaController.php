<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{
    /**
     * Allowed roles for managing news
     */
    protected $allowedRoles = ['Admin', 'GTIC', 'Gerente-Admin', 'gerente_ogh','coordinacion_ogh
'];

    /**
     * Check if user has permission to manage news
     */
    protected function canManageNews()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Check if user has any of the allowed roles
        foreach ($this->allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * API: Get all published news (for home page)
     */
    public function apiIndex()
    {
        $noticias = Noticia::where('publicada', true)
            ->orderBy('fecha_publicacion', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $noticias
        ]);
    }

    /**
     * API: Get single news item
     */
    public function apiShow($id)
    {
        $noticia = Noticia::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $noticia
        ]);
    }

    /**
     * API: Store a newly created news (for modal form)
     */
    public function apiStore(Request $request)
    {
        if (!$this->canManageNews()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para crear noticias.'
            ], 403);
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $noticia = new Noticia();
        $noticia->titulo = $request->titulo;
        $noticia->contenido = $request->contenido;
        $noticia->categoria = $request->categoria;
        $noticia->user_id = Auth::id();

        // Handle publicada field: 1 = yes (publish), 0 = no (don't publish)
        $noticia->publicada = $request->publicada == '1' || $request->publicada === true || $request->publicada === 1;

        // Set publication date only if published
        if ($noticia->publicada) {
            $noticia->fecha_publicacion = now();
        } else {
            $noticia->fecha_publicacion = null;
        }

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/noticias', $imageName);
            $noticia->imagen = $imageName;
        }

        $noticia->save();

        return response()->json([
            'success' => true,
            'message' => $noticia->publicada ? 'Noticia creada y publicada exitosamente.' : 'Noticia creada exitosamente (sin publicar).',
            'data' => $noticia
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para gestionar noticias.');
        }

        $noticias = Noticia::orderBy('fecha_publicacion', 'desc')->get();
        return redirect()->route('inicio');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para crear noticias.');
        }

    return redirect()->route('inicio');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para crear noticias.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $noticia = new Noticia();
        $noticia->titulo = $request->titulo;
        $noticia->contenido = $request->contenido;
        $noticia->categoria = $request->categoria;
        $noticia->user_id = Auth::id();

        // Handle publicada field: 1 = yes (publish), 0 = no (don't publish)
        $noticia->publicada = $request->has('publicada');

        // Set publication date only if published
        if ($noticia->publicada) {
            $noticia->fecha_publicacion = now();
        } else {
            $noticia->fecha_publicacion = null;
        }

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/noticias', $imageName);
            $noticia->imagen = $imageName;
        }

        $noticia->save();

        if ($noticia->publicada) {
            return redirect()->route('noticias.index')->with('success', 'Noticia creada y publicada exitosamente.');
        } else {
            return redirect()->route('noticias.index')->with('success', 'Noticia creada exitosamente (sin publicar).');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $noticia = Noticia::findOrFail($id);
        return view('noticias.show', compact('noticia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para editar noticias.');
        }

        $noticia = Noticia::findOrFail($id);
        return view('noticias.edit', compact('noticia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para editar noticias.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required',
            'categoria' => 'required|string|max:100',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $noticia = Noticia::findOrFail($id);
        $noticia->titulo = $request->titulo;
        $noticia->contenido = $request->contenido;
        $noticia->categoria = $request->categoria;
        $noticia->publicada = $request->has('publicada');

        if ($request->hasFile('imagen')) {
            // Delete old image if exists
            if ($noticia->imagen) {
                \Storage::delete('public/noticias/' . $noticia->imagen);
            }

            $image = $request->file('imagen');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/noticias', $imageName);
            $noticia->imagen = $imageName;
        }

        $noticia->save();

        return redirect()->route('inicio')->with('success', 'Noticia actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para eliminar noticias.');
        }

        $noticia = Noticia::findOrFail($id);

        // Delete image if exists
        if ($noticia->imagen) {
            \Storage::delete('public/noticias/' . $noticia->imagen);
        }

        $noticia->delete();

        return redirect()->route('inicio')->with('success', 'Noticia eliminada exitosamente.');
    }

    /**
     * Publish all unpublished news
     */
    public function publishAll()
    {
        if (!$this->canManageNews()) {
            return redirect()->route('inicio')->with('error', 'No tienes permiso para gestionar noticias.');
        }

        $count = Noticia::where('publicada', false)->update(['publicada' => true]);

        return redirect()->route('inicio')->with('success', "$count noticias publicadas exitosamente.");
    }
}
