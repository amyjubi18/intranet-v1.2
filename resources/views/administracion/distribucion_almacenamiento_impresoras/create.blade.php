@can('inventarioimpre_create')
@section('title', 'Nueva Distribución')
@section('subtitle', 'Asignar unidades de almacenamiento a una gerencia')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet" />
 @livewireStyles
<x-app-layout>

    <div class="p-6">
        <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
            <livewire:distribucion-almacenamiento-impresora.crear />
        </div>
    </div>

 @livewireScripts
</x-app-layout>
@endcan
