@extends('layouts.app')

@section('title', 'Detalles de Actividad')
@section('subtitle', 'Información detallada del cambio realizado')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detalles de Actividad</h1>
            <a href="{{ route('gtic.activity-log.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ← Volver
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-4">Información General</h2>
                <div class="space-y-3">
                    <div>
                        <label class="font-medium text-gray-700">Fecha:</label>
                        <p>{{ $activity->created_at->format('d/m/Y h:i:s A') }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Usuario:</label>
                        <p>{{ $activity->causer ? $activity->causer->name : 'Sistema' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Acción:</label>
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($activity->event == 'created') bg-green-100 text-green-800
                            @elseif($activity->event == 'updated') bg-blue-100 text-blue-800
                            @elseif($activity->event == 'deleted') bg-red-100 text-red-800
                            @elseif($activity->event == 'exported') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($activity->event == 'created') Registro
                            @elseif($activity->event == 'updated') Actualización
                            @elseif($activity->event == 'deleted') Eliminación
                            @elseif($activity->event == 'exported') Exportación
                            @else {{ ucfirst($activity->event) }}
                            @endif
                        </span>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Modelo:</label>
                        <p>{{ class_basename($activity->subject_type ?? 'N/A') }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">ID del Registro:</label>
                        <p>{{ $activity->subject_id ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-gray-700">Ruta:</label>
                        <p>{{ $activity->properties['url'] ?? request()->getPathInfo() }}</p>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-4">Cambios Realizados</h2>
                @if($activity->changes())
                    <div class="space-y-4">
                        @if(isset($activity->changes()['old']))
                        <div>
                            <h3 class="font-medium text-red-700 mb-2">Valores Anteriores:</h3>
                            <pre class="bg-red-50 p-3 rounded text-sm overflow-x-auto">{{ json_encode($activity->changes()['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        @endif

                        @if(isset($activity->changes()['attributes']))
                        <div>
                            <h3 class="font-medium text-green-700 mb-2">Valores Nuevos:</h3>
                            <pre class="bg-green-50 p-3 rounded text-sm overflow-x-auto">{{ json_encode($activity->changes()['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">No hay cambios registrados para esta actividad.</p>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-4">Descripción</h2>
            <p class="bg-gray-50 p-4 rounded">
                @if($activity->event == 'created')
                    {{ $activity->causer ? $activity->causer->name : 'Sistema' }} hizo un registro en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                @elseif($activity->event == 'updated')
                    {{ $activity->causer ? $activity->causer->name : 'Sistema' }} realizó un cambio en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                @elseif($activity->event == 'deleted')
                    {{ $activity->causer ? $activity->causer->name : 'Sistema' }} hizo una eliminación en el registro {{ class_basename($activity->subject_type ?? 'N/A') }}
                @else
                    {{ $activity->description ?: ($activity->properties['url'] ?? request()->getPathInfo()) }}
                @endif
            </p>
        </div>

        @if($activity->properties)
        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-4">Propiedades Adicionales</h2>
            <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
    </div>
</div>
@endsection
