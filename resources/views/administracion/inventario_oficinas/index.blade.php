@can('inventarioofic_index')
<x-app-layout>
    @section('title', 'Inventario de Oficinas')
    @section('subtitle', 'Historial de inventario de oficinas')

    <div class="container mx-auto px-4 py-6">
        <div id="inventario-oficinas-root" data-react-component="InventarioOficinas">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-600">Cargando Inventario de Oficina...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
