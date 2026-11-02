@can('salidaimpre_create')
@section('title', 'Registrar')
@section('subtitle', 'Registrar Salida Almacen')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet" />
 @livewireStyles
<x-app-layout>

    <div class="p-6">
        <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
            <livewire:salida-almacenamiento-impresora.crear />
        </div>
    </div>

 @livewireScripts
</x-app-layout>
@endcan
