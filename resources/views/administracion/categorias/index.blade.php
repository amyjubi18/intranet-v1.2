@can('categorias_index')
<x-app-layout>
    @section('title', 'Categorías')
    @section('subtitle', 'Gestión de categorías')

    <div class="container mx-auto px-4 py-6">
        <div id="categorias-management-root" data-react-component="CategoriasManagement">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div class="bg-white/80 backdrop-blur-md rounded-2xl p-10 shadow-sm border border-slate-200 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Cargando Gestión de Categorías...</p>
                <p class="text-xs text-slate-400 mt-1">Conectando con la base de datos de categorías...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
