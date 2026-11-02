@can('cartucho_create')
<x-app-layout>
    @section('title', 'Crear Consumible')
    @section('subtitle', 'Registrar nuevo consumible')

    <style>
        body {
            box-sizing: border-box;
        }

        :root{
            --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B;
            --bg-from:#f8fafc; --bg-to:#e2e8f0;
            --card-bg:white; --card-border:rgba(15,23,42,0.08);
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
        .form-input{
            backdrop-filter:blur(8px);
            border-radius:.75rem;
            padding:.75rem 1rem;
            width:100%;
            color:var(--text);
            transition:.2s;
        }
        .form-input:focus{
            outline:none;
            border-color:var(--color-primary);
            box-shadow:0 0 0 3px rgba(30,58,138,0.1);
        }
        .form-label{
            display:block;
            font-weight:600;
            margin-bottom:.5rem;
            color:var(--text);
        }
        .form-select{
            backdrop-filter:blur(8px);
            border-radius:.75rem;
            padding:.75rem 1rem;
            width:100%;
            color:var(--text);
            transition:.2s;
        }
        .form-select:focus{
            outline:none;
            box-shadow:0 0 0 3px rgba(30,58,138,0.1);
        }

        /* Área de carga de imagen */
        .upload-area {
            border: 2px dashed var(--card-border);
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-area:hover {
            border-color: var(--color-primary);
            background: rgba(30, 58, 138, 0.05);
        }

        .upload-area.dragover {
            border-color: var(--color-secondary);
            background: rgba(34, 197, 94, 0.05);
            transform: scale(1.02);
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 0.75rem;
            box-shadow: 0 8px 24px rgba(2,6,23,.25);
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

            <form id="crearConsumibleForm" action="{{ route('administracion.consumibles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Campo de consumible -->
                <div class="mb-6">
                    <label class="form-label">Consumible:</label>
                    <input
                        type="text"
                        name="cartucho"
                        class="form-input border-none bg-blue-100 "
                        placeholder="Ingrese el nombre del consumible..."
                        value="{{ old('cartucho') }}"
                        required
                    >
                    @error('cartucho')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campos en grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Color -->
                    <div>
                        <label class="form-label">Tipo Consumible:</label>
                        <select name="colore_id" class="form-select border-none bg-blue-100 " required>
                            <option value="">Seleccione un tipo</option>
                            @foreach ($colores as $color)
                                <option value="{{ $color->id }}" {{ old('colore_id') == $color->id ? 'selected' : '' }}>
                                    {{ $color->colore }}
                                </option>
                            @endforeach
                        </select>
                        @error('colore_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Impresora -->
                    <div>
                        <label class="form-label">Impresora:</label>
                        <select name="impresora_id" id="impresora_id" class="form-select border-none bg-blue-100 " required>
                            <option value="">Seleccione una impresora</option>
                            @foreach ($impresoras as $impresora)
                                <option value="{{ $impresora->id }}" {{ old('impresora_id') == $impresora->id ? 'selected' : '' }}>
                                  {{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT)}} - {{ $impresora->impresora }}
                                </option>
                            @endforeach
                        </select>
                        @error('impresora_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Tipo Consumible -->
                    <div>
                        <label class="form-label">Tipo Impresora:</label>
                        <select name="fabricante_id "  class="form-select border-none bg-blue-100 " required>
                            <option value="">Seleccione un tipo</option>
                            @foreach ($fabricantes as $fabricante)
                                <option value="{{ $fabricante->id }}" {{ old('fabricante_id') == $fabricante->id ? 'selected' : '' }}>
                                    {{ $fabricante->fabricante }}
                                </option>
                            @endforeach
                        </select>
                        @error('fabricante_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Mínimo -->
                    <div>
                        <label class="form-label">Stock Mínimo:</label>
                        <input
                            type="number"
                            name="stock_min"
                            class="form-input border-none bg-blue-100 "
                            placeholder="0"
                            value="{{ old('stock_min') }}"
                            min="0"
                            required
                        >
                        @error('stock_min')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Imagen de la Impresora Seleccionada -->
                <div class="mb-8">
                    <label class="form-label">Imagen de la Impresora:</label>
                    <div id="printerImageContainer" class="glass rounded-xl p-4 min-h-[220px] flex items-center justify-center">
                        <div id="noPrinterSelected" class="text-center text-slate-400">
                            <div class="text-5xl mb-3">🖨️</div>
                            <p class="text-base font-semibold mb-2">Sin impresora seleccionada</p>
                            <p class="text-sm">Selecciona una impresora para ver su imagen</p>
                        </div>
                        <img id="printerImage" class="preview-image hidden bg-gradient-to-br from-blue-500/20 to-purple-500/20" alt="Imagen de la impresora">
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between items-center gap-4 pt-6 border-t border-white/10">

                    <x-primary-link href="{{ route('administracion.consumibles.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                            {{ __('Regresar') }}
                        </x-primary-link>
                    <x-primary-button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Guardar
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 hidden z-50">
        <div class="glass rounded-xl px-4 py-3 flex items-center gap-2">
            <div id="toastIcon">✅</div>
            <div id="toastMsg" class="text-sm"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Elementos del DOM
        const form = document.getElementById('crearConsumibleForm');
        const impresoraSelect = document.getElementById('impresora_id');
        const printerImageContainer = document.getElementById('printerImageContainer');
        const noPrinterSelected = document.getElementById('noPrinterSelected');
        const printerImage = document.getElementById('printerImage');

        // Datos de impresoras (pasados desde PHP)
        const impresoras = @json($impresoras);

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

        // Actualizar imagen de la impresora
        function updatePrinterImage() {
            const selectedImpresoraId = impresoraSelect.value;

            if (selectedImpresoraId) {
                const impresora = impresoras.find(imp => imp.id == selectedImpresoraId);
                if (impresora && impresora.imagen) {
                    printerImage.src = '/storage/' + impresora.imagen;
                    printerImage.classList.remove('hidden');
                    noPrinterSelected.classList.add('hidden');
                } else {
                    printerImage.classList.add('hidden');
                    noPrinterSelected.classList.remove('hidden');
                }
            } else {
                printerImage.classList.add('hidden');
                noPrinterSelected.classList.remove('hidden');
            }
        }

        // Event listener para cambio de impresora
        impresoraSelect.addEventListener('change', updatePrinterImage);

        // Envío del formulario
        form.addEventListener('submit', (e) => {
            // No prevenir el envío, dejar que Laravel maneje la validación
            showToast('Creando consumible...', 'info');
        });

        // Inicialización
        function init() {
            updatePrinterImage(); // Mostrar imagen si hay una impresora preseleccionada
            showToast('Módulo de creación de consumible cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan
