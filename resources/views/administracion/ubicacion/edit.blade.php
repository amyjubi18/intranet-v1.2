@can('ubicacion_edit')
<x-app-layout>
    @section('title', 'Editar Ubicación')
    @section('subtitle', 'Modificar información de la ubicación')

    <style>
        body {
            box-sizing: border-box;
        }

        :root{
            --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B;
            --bg-from:#f8fafc; --bg-to:#e2e8f0;
            --card-bg:rgb(255, 255, 255); --card-border:rgba(15,23,42,0.08);
            --muted:#64748b; --text:#0f172a;
            --link-hover-bg: rgba(15,23,42,0.06);
            --active-bg: rgba(30,58,138,0.12);
            --active-text: #0f172a;
        }
        body{
            font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
            background: linear-gradient(135deg,var(--bg-from),var(--bg-to));
            color:var(--text);
        }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .btn-secondary{ background:rgba(15,23,42,0.06) }
        .btn-success{ background:var(--color-secondary); color:#fff }
        .btn-warning{ background:var(--color-accent); color:#111827 }

        /* Formulario */
        .form-input, select.form-input {
            backdrop-filter:blur(8px);
            border-radius:.75rem;
            padding:.75rem 1rem;
            width:100%;
            color:var(--text);
            transition:.2s;
        }
        .form-input:focus, select.form-input:focus {
            outline:none;
            box-shadow:0 0 0 3px rgba(30,58,138,0.1);
        }
        .form-label{
            display:block;
            font-weight:600;
            margin-bottom:.5rem;
            color:var(--text);
        }

        /* Animaciones */
        .fade-in{
            animation:fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
        }
    </style>

    <!-- Contenido principal -->
    <div class="container mx-auto px-6 pb-20 max-w-4xl">
        <div class="glass rounded-2xl p-8 fade-in">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl grid place-items-center bg-blue-500/20 mx-auto mb-4">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-2">Editar Ubicación</h2>
                <p class="text-slate-400">Modifica la información de la ubicación seleccionada</p>
            </div>

            <form id="editarUbicacionForm" action="{{ route('administracion.ubicacion.update', $ubicacion->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Campo de ubicación -->
                <div class="mb-6">
                    <label class="form-label" for="ubicacion">Ubicación:</label>
                    <input
                        type="text"
                        id="ubicacion"
                        name="ubicacion"
                        class="form-input border-none bg-blue-100"
                        value="{{ old('ubicacion', $ubicacion->ubicacion) }}"
                        required
                        autofocus
                    >
                    @error('ubicacion')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between items-center gap-4 pt-6 border-t border-white/10">
                    <x-primary-link href="{{ route('administracion.ubicacion.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                        {{ __('Regresar') }}
                    </x-primary-link>
                    <x-primary-button type="submit" class="btn-save">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Actualizar Ubicación
                    </x-primary-button>
                </div>
            </form>
        </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 hidden z-50">
        <div class="glass rounded-xl px-4 py-3 flex items-center gap-2">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
    </div>

    <script>
        // Toast
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msgEl = document.getElementById('toastMsg');

            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };

            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        // Envío del formulario
        document.getElementById('editarUbicacionForm').addEventListener('submit', (e) => {
            showToast('Actualizando ubicación...', 'info');
        });

        // Inicialización
        function init() {
            showToast('Módulo de edición de ubicación cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan
