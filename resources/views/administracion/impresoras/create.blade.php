@can('impresora_create')
<x-app-layout>
    @section('title', 'Crear Impresora')
    @section('subtitle', 'Registrar nueva impresora')

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

            <form id="crearImpresoraForm" action="{{ route('administracion.impresoras.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Campo de impresora -->
                <div class="mb-6">
                    <label class="form-label">Impresora:</label>
                    <input
                        type="text"
                        name="impresora"
                        class="form-input border-none bg-blue-100 "
                        placeholder="Ingrese el nombre de la impresora..."
                        value="{{ old('impresora') }}"
                        required
                    >
                    @error('impresora')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

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
                                <img id="previewImage" class="preview-image hidden bg-gradient-to-br from-blue-500/20 to-purple-500/20" alt="Vista previa">
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
        // Estado de la aplicación
        let selectedFile = null;

        // Elementos del DOM
        const form = document.getElementById('crearImpresoraForm');
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
            showToast('Creando impresora...', 'info');
        });

        // Inicialización
        function init() {
            showToast('Módulo de creación de impresora cargado', 'info');
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', init);
    </script>
</x-app-layout>
@endcan

