
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

        /* Selector de colores */
        .colors-input-container {
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.02);
        }

        .color-input-row {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .btn-icon {
            padding: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .added-colors {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(59, 130, 246, 0.1);
        }

        .color-tag {
            background: rgba(59, 130, 246, 0.1);
            color: #1e40af;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .color-tag button {
            background: none;
            border: none;
            color: #1e40af;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .color-tag button:hover {
            color: #dc2626;
        }

        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: white;
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 2000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .toast.success {
            border-color: rgba(34, 197, 94, 0.3);
            background: rgba(34, 197, 94, 0.05);
        }

        .toast.error {
            border-color: rgba(220, 38, 38, 0.3);
            background: rgba(220, 38, 38, 0.05);
        }

        .toast-icon {
            font-size: 1.25rem;
        }

        .toast-message {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1e3a8a;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                padding: 1rem;
            }

            .title-section h1 {
                font-size: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
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
                    <!-- Fabricante / Tipo de Impresora -->
                    <div>
                        <label class="form-label">Tipo de Impresora:</label>
                        <select name="fabricante_id" id="fabricante_id" class="form-select border-none bg-blue-100 " required>
                            <option value="">Seleccione un Tipo de Impresora</option>
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

                <!-- Campo de Colores (dinámico según fabricante) -->
                <div class="mb-6" id="coloresSection" style="display: none;">
                    <label class="form-label">Colores:</label>
                    <div class="colors-input-container border-4 justify-center items-center border-blue-200 rounded-lg" style="padding: 1.5rem;">
                        <div class="color-input-row grid grid-cols-2 gap-4">
                            <select id="colorSelect" class="rounded-lg w-10/12" style="font-size: 0.95rem;">
                                <option class="p-2" value="">Seleccionar color...</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-icon w-fit" onclick="addColor()" style="padding: 1rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                            </button>
                        </div>

                        <div id="addedColors" class="added-colors" style="min-height: 60px; max-height: 200px; overflow-y: auto;">
                            @if(old('colore_ids'))
                                @foreach(old('colore_ids') as $colorId)
                                    @php
                                        $colorData = $colores->firstWhere('id', $colorId);
                                    @endphp
                                    @if($colorData)
                                        <div class="color-tag" data-color-id="{{ $colorId }}">
                                            {{ $colorData->colore }}
                                            <input type="hidden" name="colore_ids[]" value="{{ $colorId }}">
                                            <button type="button" onclick="removeColor(this)">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay colores agregados aún</p>
                            @endif
                        </div>
                    </div>
                    @error('colore_ids')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo de Color único (para fabricantes sin colores múltiples) -->
                <div class="mb-6" id="singleColorSection">
                    <label class="form-label">Tipo Consumible:</label>
                    <select name="colore_id" id="colore_id" class="form-select border-none bg-blue-100 " required>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

    <script>
        // Elementos del DOM
        const form = document.getElementById('crearConsumibleForm');
        const fabricanteSelect = document.getElementById('fabricante_id');
        const coloresSection = document.getElementById('coloresSection');
        const singleColorSection = document.getElementById('singleColorSection');
        const colorSelect = document.getElementById('colorSelect');
        const addedColors = document.getElementById('addedColors');
        const impresoraSelect = document.getElementById('impresora_id');
        const printerImageContainer = document.getElementById('printerImageContainer');
        const noPrinterSelected = document.getElementById('noPrinterSelected');
        const printerImage = document.getElementById('printerImage');

        // Datos de impresoras (pasados desde PHP)
        const impresoras = @json($impresoras);

        // Datos de fabricantes con sus colores (pasados desde PHP)
        const fabricantes = @json($fabricantes);

        // Todos los colores disponibles
        const allColores = @json($colores->mapWithKeys(function($c) { return [$c->id => $c->colore]; }));

        // Variables globales para colores
        let selectedColores = @json(old('colore_ids', []));

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

        // Actualizar selector de colores según fabricante
        function updateColorOptions() {
            const fabricanteId = fabricanteSelect.value;
            const colorSelect = document.getElementById('colorSelect');

            // Limpiar opciones actuales
            colorSelect.innerHTML = '<option class="p-2" value="">Seleccionar color...</option>';

            if (fabricanteId) {
                const fabricante = fabricantes.find(f => f.id == fabricanteId);

                if (fabricante && fabricante.colores && fabricante.colores.length > 0) {
                    // El fabricante tiene colores asociados - mostrar selector múltiple
                    coloresSection.style.display = 'block';
                    singleColorSection.style.display = 'none';

                    // Agregar opciones de colores del fabricante
                    fabricante.colores.forEach(color => {
                        const option = document.createElement('option');
                        option.value = color.id;
                        option.textContent = color.colore;
                        colorSelect.appendChild(option);
                    });

                    // Actualizar el select de color único para validación
                    document.getElementById('colore_id').required = false;
                } else {
                    // El fabricante no tiene colores asociados - mostrar selector único
                    coloresSection.style.display = 'none';
                    singleColorSection.style.display = 'block';
                    document.getElementById('colore_id').required = true;
                }
            } else {
                // No hay fabricante seleccionado
                coloresSection.style.display = 'none';
                singleColorSection.style.display = 'block';
                document.getElementById('colore_id').required = true;
            }
        }

        // Actualizar select de colores (ocultar los ya seleccionados)
        function updateColorSelect() {
            const select = document.getElementById('colorSelect');
            const allOptions = select.querySelectorAll('option');
            allOptions.forEach(option => {
                option.style.display = '';
            });
            selectedColores.forEach(colorId => {
                const option = select.querySelector(`option[value="${colorId}"]`);
                if (option) {
                    option.style.display = 'none';
                }
            });
        }

        // Añadir color
        function addColor() {
            const select = document.getElementById('colorSelect');
            const colorId = select.value;

            if (!colorId) {
                alert('Seleccione un color válido');
                return;
            }

            if (selectedColores.includes(colorId)) {
                alert('Este color ya fue agregado');
                return;
            }

            selectedColores.push(colorId);
            updateColorSelect();
            renderColors();
        }

        // Renderizar colores
        function renderColors() {
            const container = document.getElementById('addedColors');

            if (selectedColores.length === 0) {
                container.innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay colores agregados aún</p>';
                return;
            }

            container.innerHTML = '';

            selectedColores.forEach((colorId) => {
                if (allColores[colorId]) {
                    const tag = document.createElement('div');
                    tag.className = 'color-tag';
                    tag.setAttribute('data-color-id', colorId);
                    tag.innerHTML = `
                        ${allColores[colorId]}
                        <input type="hidden" name="colore_ids[]" value="${colorId}">
                        <button type="button" onclick="removeColor(this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>
                    `;
                    container.appendChild(tag);
                }
            });
        }

        // Eliminar color
        function removeColor(button) {
            const tag = button.closest('.color-tag');
            const colorId = tag.getAttribute('data-color-id');

            selectedColores = selectedColores.filter(c => c != colorId);
            tag.remove();

            if (selectedColores.length === 0) {
                document.getElementById('addedColors').innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay colores agregados aún</p>';
            }

            updateColorSelect();
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

        // Event listeners
        fabricanteSelect.addEventListener('change', updateColorOptions);
        impresoraSelect.addEventListener('change', updatePrinterImage);

        // Envío del formulario
        form.addEventListener('submit', function(e) {
            // Si hay colores seleccionados en el modo múltiple, asegurarnos de que hay al menos uno
            if (coloresSection.style.display !== 'none' && selectedColores.length === 0) {
                e.preventDefault();
                alert('Por favor seleccione al menos un color');
                return;
            }
            showToast('Creando consumible...', 'info');
        });

        // Inicialización
        function init() {
            updateColorOptions();
            updatePrinterImage();
            if (selectedColores.length > 0) {
                updateColorSelect();
            }
            showToast('Módulo de creación de consumible cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan

