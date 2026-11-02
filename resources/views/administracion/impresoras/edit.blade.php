@can('impresora_edit')
<x-app-layout>
    @section('title', 'Editar Impresora')
    @section('subtitle', 'Modificar información de la impresora')

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
         .permissions-input-container {
            border: 2px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            padding: 1rem;
            background: rgba(59, 130, 246, 0.02);
        }

        .permission-input-row {
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

        .added-permissions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(59, 130, 246, 0.1);
        }

        .permission-tag {
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

        .permission-tag button {
            background: none;
            border: none;
            color: #1e40af;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .permission-tag button:hover {
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

            <form id="editarImpresoraForm" action="{{ route('administracion.impresoras.update', $impresora->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Campo de impresora -->
                <div class="mb-6">
                    <label class="form-label">Impresora:</label>
                    <input
                        type="text"
                        name="impresora"
                        class="form-input border-none bg-blue-100 "
                        placeholder="Ingrese el nombre de la impresora..."
                        value="{{ old('impresora', $impresora->impresora) }}"
                        required
                    >
                    @error('impresora')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo de Gerencia -->
                <div class="mb-6">
                    <label class="form-label">Gerencias:</label>
                    <div class="permissions-input-container border-4 justify-center items-center border-blue-200 rounded-lg " style="padding: 1.5rem;">
                        <div class="permission-input-row">
                            <select id="gerenciaSelect" class="form-input" style="font-size: 0.95rem;">
                                <option value="">Seleccionar gerencia...</option>
                                @foreach($gerencias as $gerencia)
                                    <option value="{{ $gerencia->id }}">{{ $gerencia->gerencia }}@if($gerencia->area) - {{ $gerencia->area->area }}@endif</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary btn-icon" onclick="addGerencia()" style="padding: 1rem;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                            </button>
                        </div>

                        <div id="addedGerencias" class="added-permissions" style="min-height: 60px; max-height: 200px; overflow-y: auto;">
                            @php
                                $selectedGerencias = old('gerencia_ids', $impresora->gerencias()->pluck('gerencias.id')->toArray());
                            @endphp
                            @if($selectedGerencias)
                                @foreach($selectedGerencias as $gerenciaId)
                                    @php
                                        $gerenciaData = $gerencias->firstWhere('id', $gerenciaId);
                                    @endphp
                                    @if($gerenciaData)
                                        <div class="permission-tag" data-gerencia-id="{{ $gerenciaId }}">
                                            {{ $gerenciaData->gerencia }}
                                            <input type="hidden" name="gerencia_ids[]" value="{{ $gerenciaId }}">
                                            <button type="button" onclick="removeGerencia(this)">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay gerencias agregadas aún</p>
                            @endif
                        </div>
                    </div>
                    @error('gerencia_ids')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                    // Variables globales
                    let selectedGerencias = @json(old('gerencia_ids', $impresora->gerencias()->pluck('gerencias.id')->toArray()));

                    // Lista de gerencias disponibles
                    const allGerencias = @json($gerencias->mapWithKeys(function($g) { return [$g->id => $g->gerencia . ($g->area ? ' - ' . $g->area->area : '')]; }));

                    // Actualizar select de gerencias
                    function updateGerenciaSelect() {
                        const select = document.getElementById('gerenciaSelect');
                        const allOptions = select.querySelectorAll('option');
                        allOptions.forEach(option => {
                            option.style.display = '';
                        });
                        selectedGerencias.forEach(gerenciaId => {
                            const option = select.querySelector(`option[value="${gerenciaId}"]`);
                            if (option) {
                                option.style.display = 'none';
                            }
                        });
                    }

                    // Añadir gerencia
                    function addGerencia() {
                        const select = document.getElementById('gerenciaSelect');
                        const gerenciaId = select.value;

                        if (!gerenciaId) {
                            alert('Seleccione una gerencia válida');
                            return;
                        }

                        if (selectedGerencias.includes(gerenciaId)) {
                            alert('Esta gerencia ya fue agregada');
                            return;
                        }

                        selectedGerencias.push(gerenciaId);
                        updateGerenciaSelect();
                        renderGerencias();
                    }

                    // Renderizar gerencias
                    function renderGerencias() {
                        const container = document.getElementById('addedGerencias');

                        if (selectedGerencias.length === 0) {
                            container.innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay gerencias agregadas aún</p>';
                            return;
                        }

                        container.innerHTML = '';

                        selectedGerencias.forEach((gerenciaId) => {
                            if (allGerencias[gerenciaId]) {
                                const tag = document.createElement('div');
                                tag.className = 'permission-tag';
                                tag.setAttribute('data-gerencia-id', gerenciaId);
                                tag.innerHTML = `
                                    ${allGerencias[gerenciaId]}
                                    <input type="hidden" name="gerencia_ids[]" value="${gerenciaId}">
                                    <button type="button" onclick="removeGerencia(this)">
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

                    // Eliminar gerencia
                    function removeGerencia(button) {
                        const tag = button.closest('.permission-tag');
                        const gerenciaId = tag.getAttribute('data-gerencia-id');

                        selectedGerencias = selectedGerencias.filter(g => g != gerenciaId);
                        tag.remove();

                        if (selectedGerencias.length === 0) {
                            document.getElementById('addedGerencias').innerHTML = '<p style="color: #64748b; font-size: 0.875rem; text-align: center; width: 100%; padding: 1rem 0;">No hay gerencias agregadas aún</p>';
                        }

                        updateGerenciaSelect();
                    }

                    // Inicializar
                    document.addEventListener('DOMContentLoaded', function() {
                        updateGerenciaSelect();
                    });
                </script>

                <!-- Área de carga de imagen -->
                <div class="mb-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- Botón de selección y área de drag & drop -->
                        <div>
                            <label class="form-label">Imagen Referencial:</label>
                            <div id="uploadArea" class="upload-area">
                                <div class="space-y-2">
                                    <div class="text-2xl">📁</div>
                                    <div>
                                        <p class="text-sm font-semibold mb-2">Seleccionar imagen</p>
                                        <p class="text-xs text-slate-400 mb-3">Arrastra y suelta una imagen aquí o haz clic para seleccionar</p>
                                        <button type="button" id="selectImageBtn" class="btn btn-primary">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                <polyline points="7,10 12,15 17,10"/>
                                                <line x1="12" y1="15" x2="12" y2="3"/>
                                            </svg>
                                            Seleccionar Archivo
                                        </button>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        Formatos soportados: PNG, JPG, JPEG, WEBP, GIF<br>
                                        Tamaño máximo: 5MB
                                    </div>
                                </div>
                            </div>
                            <input type="file" id="imageInput" name="imagen" accept=".png,.jpg,.jpeg,.webp,.gif" class="hidden">
                        </div>

                        <!-- Vista previa -->
                        <div>
                            <label class="form-label">Vista Previa:</label>
                            <div id="previewContainer" class="glass rounded-xl p-4 min-h-[220px] flex items-center justify-center">
                                <div id="previewPlaceholder" class="text-center text-slate-400 bg-gradient-to-br from-blue-500/20 to-purple-500/20">
                                    <div class="text-5xl mb-3">🖨️</div>
                                    <p class="text-base font-semibold mb-2">Sin imagen</p>
                                    <p class="text-sm">La vista previa aparecerá aquí</p>
                                </div>
                                @if($impresora->imagen)
                                    <img id="previewImage" class="preview-image bg-gradient-to-br from-blue-500/20 to-purple-500/20" src="{{ asset('storage/' . $impresora->imagen) }}" alt="Vista previa">
                                @else
                                    <img id="previewImage" class="preview-image hidden bg-gradient-to-br from-blue-500/20 to-purple-500/20" alt="Vista previa">
                                @endif
                            </div>

                            <!-- Información de la imagen -->
                            <div id="imageInfo" class="hidden mt-4 p-4 glass rounded-lg">
                                <div class="text-sm space-y-1">
                                    <div><span class="text-slate-400">Nombre:</span> <span id="fileName"></span></div>
                                    <div><span class="text-slate-400">Tamaño:</span> <span id="fileSize"></span></div>
                                    <div><span class="text-slate-400">Tipo:</span> <span id="fileType"></span></div>
                                </div>
                                <button type="button" id="removeImageBtn" class="btn btn-secondary mt-3 text-sm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3,6 5,6 21,6"/>
                                        <path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                                    </svg>
                                    Quitar Imagen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Botones de acción -->
                <div class="flex justify-between items-center gap-4 pt-6 border-t border-white/10">
                    <x-primary-link href="{{ route('administracion.impresoras.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                            {{ __('Regresar') }}
                        </x-primary-link>
                    <x-primary-button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Actualizar
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

    <script>
        // Estado de la aplicación
        let selectedFile = null;

        // Elementos del DOM
        const form = document.getElementById('editarImpresoraForm');
        const uploadArea = document.getElementById('uploadArea');
        const imageInput = document.getElementById('imageInput');
        const selectImageBtn = document.getElementById('selectImageBtn');
        const previewContainer = document.getElementById('previewContainer');
        const previewPlaceholder = document.getElementById('previewPlaceholder');
        const previewImage = document.getElementById('previewImage');
        const imageInfo = document.getElementById('imageInfo');
        const removeImageBtn = document.getElementById('removeImageBtn');

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

        // Validar archivo
        function validateFile(file) {
            const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/gif'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!validTypes.includes(file.type)) {
                showToast('Tipo de archivo no válido. Use PNG, JPG, JPEG, WEBP o GIF.', 'error');
                return false;
            }

            if (file.size > maxSize) {
                showToast('El archivo es demasiado grande. Máximo 5MB.', 'error');
                return false;
            }

            return true;
        }

        // Formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Mostrar vista previa
        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
                previewPlaceholder.classList.add('hidden');

                // Mostrar información del archivo
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('fileType').textContent = file.type;
                imageInfo.classList.remove('hidden');

                // Ajustar altura del área de carga para alinear con la información de la imagen
                setTimeout(() => {
                    const previewContainer = document.getElementById('previewContainer');
                    const imageInfo = document.getElementById('imageInfo');
                    const uploadArea = document.getElementById('uploadArea');

                    if (previewContainer && imageInfo && uploadArea) {
                        const previewHeight = previewContainer.offsetHeight;
                        const imageInfoHeight = imageInfo.offsetHeight;
                        const totalHeight = previewHeight + imageInfoHeight + 16; // 16px es el gap (mt-4)

                        uploadArea.style.minHeight = totalHeight + 'px';
                    }
                }, 100);
            };
            reader.readAsDataURL(file);
        }

        // Limpiar vista previa
        function clearPreview() {
            previewImage.src = '';
            previewImage.classList.add('hidden');
            previewPlaceholder.classList.remove('hidden');
            imageInfo.classList.add('hidden');
            selectedFile = null;
            imageInput.value = '';

            // Restaurar altura original del área de carga
            const uploadArea = document.getElementById('uploadArea');
            if (uploadArea) {
                uploadArea.style.minHeight = '220px';
            }
        }

        // Manejar selección de archivo
        function handleFileSelect(file) {
            if (validateFile(file)) {
                selectedFile = file;
                showPreview(file);
                showToast('Imagen cargada correctamente', 'success');
            }
        }

        // Event listeners para carga de imagen
        selectImageBtn.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                handleFileSelect(file);
            }
        });

        // Drag and drop
        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Quitar imagen
        removeImageBtn.addEventListener('click', () => {
            clearPreview();
            showToast('Imagen removida', 'info');
        });

        // Envío del formulario
        form.addEventListener('submit', (e) => {
            // No prevenir el envío, dejar que Laravel maneje la validación
            showToast('Actualizando impresora...', 'info');
        });

        // Inicialización
        function init() {
            // Mostrar imagen actual si existe
            const currentImage = document.getElementById('previewImage');
            if (currentImage && !currentImage.classList.contains('hidden')) {
                previewPlaceholder.classList.add('hidden');
                imageInfo.classList.remove('hidden');

                // Simular información del archivo actual (si está disponible)
                // Nota: Para archivos existentes, la información detallada no está disponible
                // Solo mostramos que hay una imagen cargada
                document.getElementById('fileName').textContent = 'Imagen actual';
                document.getElementById('fileSize').textContent = 'N/A';
                document.getElementById('fileType').textContent = 'Imagen existente';

                // Ajustar altura del área de carga
                setTimeout(() => {
                    const previewContainer = document.getElementById('previewContainer');
                    const imageInfo = document.getElementById('imageInfo');
                    const uploadArea = document.getElementById('uploadArea');

                    if (previewContainer && imageInfo && uploadArea) {
                        const previewHeight = previewContainer.offsetHeight;
                        const imageInfoHeight = imageInfo.offsetHeight;
                        const totalHeight = previewHeight + imageInfoHeight + 16;

                        uploadArea.style.minHeight = totalHeight + 'px';
                    }
                }, 100);
            }

            showToast('Módulo de edición de impresora cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan
