@can('salidaimpre_edit')
@section('title', 'Editar')
@section('subtitle', 'Editar Salida Impresora')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet" />
 @livewireStyles
<x-app-layout>

    <div class="p-6">
        <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
            <livewire:salida-impresora.editar :salida-impresora="$salida_impresora" />
        </div>
    </div>

 @livewireScripts
</x-app-layout>
@endcan
