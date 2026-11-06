@can('bienes_index')
<x-app-layout>
    @section('title', 'Bienes Públicos')
    @section('subtitle', 'Bienes Nacionales ')
    <style>
        .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
        .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
        .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }
    </style>

    <div id="bienes-publicos-root" data-react-component="BienesPublicos">
        {{-- Fallback de carga inicial mientras React se monta --}}
        <div class="px-6 py-8">
            <div class="bg-white border border-white/20 backdrop-blur-md rounded-2xl p-10 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Cargando Bienes Públicos...</p>
                <p class="text-xs text-slate-400 mt-1">Conectando con el inventario de bienes nacionales...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
