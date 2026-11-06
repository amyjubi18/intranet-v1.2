@section('title', 'Registro de Actividades')
@section('subtitle', 'Historial de cambios en el sistema')

<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <div id="activity-log-root" data-react-component="ActivityLog">
            {{-- Fallback de carga inicial mientras React se monta --}}
            <div className="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 text-center animate-pulse">
                <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p className="text-sm font-semibold text-slate-600">Cargando Registro de Actividades...</p>
            </div>
        </div>
    </div>
</x-app-layout>
