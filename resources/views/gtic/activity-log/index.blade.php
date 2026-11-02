@extends('layouts.app')

@section('title', 'Registro de Actividades')
@section('subtitle', 'Historial de cambios en el sistema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Registro de Actividades</h1>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-2 text-left whitespace-nowrap">Usuario</th>
                        <th class="px-4 py-2 text-left whitespace-nowrap">Acción</th>
                        <th class="px-4 py-2 text-left whitespace-nowrap">Modelo</th>
                        <th class="px-4 py-2 text-left">Descripción</th>
                        <th class="px-4 py-2 text-left whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($activities as $activity)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $activity->created_at->format('d/m/Y h:i:s A') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-bold text-blue-900">
                           <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->name : 'Sistema' }} </b>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
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
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ class_basename($activity->subject_type ?? 'N/A') }}</td>
                        <td class="px-4 py-2 break-words">
                            {{-- @if($activity->description)
                                {{ Str::limit($activity->description, 50) }}
                            @else --}}
                                @if($activity->event == 'created')
                                   <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->name : 'Sistema' }}</b> hizo un registro en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @elseif($activity->event == 'updated')
                                    <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->name : 'Sistema' }} </b> realizó un cambio en el modelo {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @elseif($activity->event == 'deleted')
                                    <b class="text-blue-900"> {{ $activity->causer ? $activity->causer->name : 'Sistema' }}</b> hizo una eliminación en  {{ class_basename($activity->subject_type ?? 'N/A') }}
                                @else
                                    {{ $activity->properties['url'] ?? request()->getPathInfo() }}
                                @endif
                           {{--  @endif --}}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <a href="{{ route('gtic.activity-log.show', $activity->id) }}"
                               class="text-blue-600 hover:text-blue-800">Ver detalles</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center">
            <div>
                {{ $activities->links() }}
            </div>
            <div class="flex space-x-2">
               {{--  <a href="{{ route('gtic.activity-log.export.excel') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    📊 Exportar Excel
                </a> --}}
                <a href="{{ route('gtic.activity-log.export.pdf') }}" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    📄 Exportar PDF
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
