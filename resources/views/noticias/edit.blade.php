@extends('layouts.app')
@section('title', 'Editar Noticia')
@section('subtitle', 'Modificar noticia existente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="glass rounded-2xl p-6">
        <form action="{{ route('noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Título de la Noticia *</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $noticia->titulo) }}" class="w-full p-3 border rounded-lg bg-slate-50
                    " required placeholder="Ingrese el título de la noticia">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Categoría</label>
                    <select name="categoria" class="w-full p-3 border rounded-lg bg-slate-50
                    ">
                        <option value="General" {{ $noticia->categoria == 'General' ? 'selected' : '' }}>General</option>
                        <option value="Corporativo" {{ $noticia->categoria == 'Corporativo' ? 'selected' : '' }}>Corporativo</option>
                        <option value="Eventos" {{ $noticia->categoria == 'Eventos' ? 'selected' : '' }}>Eventos</option>
                        <option value="Capacitación" {{ $noticia->categoria == 'Capacitación' ? 'selected' : '' }}>Capacitación</option>
                        <option value="Logros" {{ $noticia->categoria == 'Logros' ? 'selected' : '' }}>Logros</option>
                        <option value="Reconocimientos" {{ $noticia->categoria == 'Reconocimientos' ? 'selected' : '' }}>Reconocimientos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Contenido *</label>
                    <textarea name="contenido" rows="8" class="w-full p-3 border rounded-lg bg-slate-50
                    " required placeholder="Escriba el contenido de la noticia">{{ old('contenido', $noticia->contenido) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Imagen (opcional)</label>
                    @if($noticia->imagen)
                        <div class="mb-2">
                            <img src="{{ asset('storage/noticias/' . $noticia->imagen) }}" alt="Imagen actual" class="w-32 h-32 object-cover rounded-lg">
                            <p class="text-xs text-slate-400 mt-1">Imagen actual</p>
                        </div>
                    @endif
                    <input type="file" name="imagen" accept="image/*" class="w-full p-3 border rounded-lg bg-slate-50
                    ">
                    <p class="text-xs text-slate-400 mt-1">Tamaño máximo: 2MB. Formatos: JPEG, PNG, JPG, GIF, SVG</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="publicada" id="publicada" value="1" {{ $noticia->publicada ? 'checked' : '' }} class="w-5 h-5 rounded">
                    <label for="publicada" class="text-sm font-medium">Publicar inmediatamente</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('inicio') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Noticia</button>
            </div>
        </form>
    </div>
</div>
@endsection

