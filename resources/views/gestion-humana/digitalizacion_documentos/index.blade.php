@can('digitalizaciones_index')
<x-app-layout>
    @section('title', 'Digitalización de Documentos')
    @section('subtitle', 'Gestión de documentos digitalizados')

    <div class="container mx-auto px-4 py-6">
        <div id="digitalizacion-documentos-root" data-react-component="DigitalizacionDocumentos">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-600">Cargando Digitalización de Documentos...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
