@can('estanterias_index')
<x-app-layout>
    @section('title', 'Estanterías')
    @section('subtitle', 'Gestión de estanterías')

    <style>
        body { box-sizing: border-box; }
        :root{ --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B; --card-bg:white; --card-border:rgba(15,23,42,0.08); --text:#0f172a; }
        body{ font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%); color:var(--text); }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
        .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
        .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #d33 !important; color: white !important; }
        .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #3085d6 !important; color: white !important; }
    </style>

    <div id="estanterias-management-root" data-react-component="EstanteriasManagement">
        {{-- Fallback de carga inicial mientras React se monta --}}
        <div class="px-6 py-6">
            <div class="glass rounded-2xl p-10 text-center animate-pulse">
                <div class="w-10 h-10 border-4 border-blue-900 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm font-semibold text-slate-700">Cargando Gestión de Estanterías...</p>
                <p class="text-xs text-slate-400 mt-1">Conectando con la base de datos de estanterías...</p>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan
