@section('title', 'Registro de Actividades')
@section('subtitle', 'Historial de cambios en el sistema')

<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div id="activity-log-root" data-react-component="ActivityLog">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div class="bg-white rounded-lg shadow-md p-6 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Cargando Registro de Actividades...</p>
            </div>
        </div>
    </div>
</x-app-layout>
