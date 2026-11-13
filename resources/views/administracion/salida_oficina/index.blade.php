@can('salidaofic_index')
<x-app-layout>
    @section('title', 'Salida de Oficina')
    @section('subtitle', 'Despacho y control de inventario de oficinas')

    <div class="container mx-auto px-4 py-6">
        <div id="salidas-oficinas-root" data-react-component="SalidasOficinas">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Cargando Salidas de Oficina...</p>
                <p class="text-xs text-slate-400 mt-1">Conectando con el inventario y despachos...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
