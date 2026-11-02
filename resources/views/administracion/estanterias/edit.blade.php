@can('estanterias_edit')
<x-app-layout>
    @section('title', 'Editar Estantería')
    @section('subtitle', 'Actualizar estantería existente')

    <style>
        body { box-sizing: border-box; }
        :root{ --color-primary:#1e3a8a; --color-secondary:#22C55E; --color-accent:#F59E0B; --card-bg:white; --card-border:rgba(15,23,42,0.08); --text:#0f172a; }
        body{ font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 35%, #cbd5e1 70%, #94a3b8 100%); color:var(--text); }
        .glass{ background:var(--card-bg); border:1px solid var(--card-border); backdrop-filter:blur(8px) }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; border-radius:.75rem; padding:.625rem .9rem; font-weight:600; transition:.2s; border:1px solid var(--card-border) }
        .btn:active{ transform:translateY(1px) }
        .btn-primary{ background:var(--color-primary); color:#fff }
        .form-input{ backdrop-filter:blur(8px); border-radius:.75rem; padding:.75rem 1rem; width:100%; color:var(--text); }
        .form-input:focus{ outline:none; border-color:var(--color-primary); box-shadow:0 0 0 3px rgba(30,58,138,0.1); }
        .form-label{ display:block; font-weight:600; margin-bottom:.5rem; color:var(--text); }
        .upload-area { border: 2px dashed var(--card-border); border-radius: 0.75rem; padding: 2rem; text-align: center; transition: all 0.3s ease; cursor: pointer; background: var(--card-bg); min-height: 200px; display: flex; align-items: center; justify-content: center; }
        .upload-area:hover { border-color: var(--color-primary); background: rgba(30, 58, 138, 0.05); }
        .upload-area.dragover { border-color: var(--color-secondary); background: rgba(34, 197, 94, 0.05); transform: scale(1.02); }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
        .preview-image { max-width: 100%; max-height: 300px; border-radius: 0.75rem; object-fit: contain; }
    </style>

    <div class="container max-w-2xl px-6 pb-20 mx-auto">
        <div class="p-8 glass rounded-2xl fade-in">
            <form id="editarEstanteriaForm" action="{{route('administracion.estanterias.update', $estanteria->id)}}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Imagen actual -->
                @if($estanteria->imagen)
                <div class="mb-6">
                    <label class="form-label">Imagen Actual:</label>
                    <div class="p-4 border border-blue-200 bg-blue-50 rounded-xl">
                        <img src="{{ asset('storage/estanterias/' . $estanteria->imagen) }}" alt="Imagen actual" class="max-w-full mx-auto rounded-lg max-h-48">
                    </div>
                </div>
                @endif

                <!-- Campo: Nombre -->
                <div class="mb-6">
                    <label class="form-label">Nombre de la Estantería:</label>
                    <input type="text" name="nombre" class="bg-blue-100 border-none form-input" value="{{ old('nombre', $estanteria->nombre) }}" required>
                    @error('nombre')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Área de carga de imagen -->
                <div class="mb-8">
                    <label class="form-label">Nueva Imagen (Opcional):</label>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Área de carga -->
                        <div id="uploadArea" class="upload-area">
                            <div class="space-y-2">
                                <div class="text-4xl">🗄️</div>
                                <div>
                                    <p class="mb-2 text-sm font-semibold">Seleccionar Nueva Imagen</p>
                                    <p class="mb-3 text-xs text-slate-400">Arrastra y suelta una imagen o haz clic para seleccionar</p>
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
                                    Formatos: JPEG, PNG, JPG, GIF, SVG<br>
                                    Máximo: 2MB<br>
                                    <span class="text-blue-600">Deja vacío para mantener la actual</span>
                                </div>
                            </div>
                            <input type="file" id="imagenInput" name="imagen" accept="image/*" class="hidden">
                        </div>

                        <!-- Vista previa -->
                        <div>
                            <label class="form-label">Vista Previa:</label>
                            <div id="previewContainer" class="glass rounded-xl p-4 min-h-[200px] flex items-center justify-center">
                                <div id="previewPlaceholder" class="text-center text-slate-400">
                                    <div class="mb-3 text-5xl">🗄️</div>
                                    <p class="mb-2 text-base font-semibold">Sin nueva imagen</p>
                                    <p class="text-sm">La vista previa de la nueva imagen aparecerá aquí</p>
                                </div>
                                <img id="previewImage" class="hidden preview-image" src="" alt="Vista previa">
                            </div>
                            <div id="imagenInfo" class="hidden p-4 mt-4 rounded-lg glass">
                                <div class="space-y-1 text-sm">
                                    <div><span class="text-slate-400">Nombre:</span> <span id="fileName"></span></div>
                                    <div><span class="text-slate-400">Tamaño:</span> <span id="fileSize"></span></div>
                                </div>
                                <button type="button" id="removeImageBtn" class="mt-3 text-sm btn btn-secondary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3,6 5,6 21,6"/>
                                        <path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
                                    </svg>
                                    Quitar Imagen
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('imagen')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-between gap-4 pt-6 border-t border-white/10">
                    <x-primary-link href="{{ route('administracion.estanterias.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95z"></path></svg>
                        Regresar
                    </x-primary-link>
                    <x-primary-button type="submit" class="btn-save">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Actualizar Estantería
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Toast -->
        <div id="toast" class="fixed z-50 hidden bottom-4 right-4">
            <div class="flex items-center gap-2 px-4 py-3 glass rounded-xl">
                <div id="toastIcon">✅</div>
                <div id="toastMsg" class="text-sm"></div>
            </div>
        </div>
    </div>

    <script>
        let selectedFile = null;

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast'), icon = document.getElementById('toastIcon'), msgEl = document.getElementById('toastMsg');
            const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
            icon.textContent = icons[type] || icons.success;
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function validateFile(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
            const maxSize = 2 * 1024 * 1024;

            if (!validTypes.includes(file.type)) {
                showToast('Tipo de archivo no válido. Use JPEG, PNG, JPG, GIF o SVG.', 'error');
                return false;
            }

            if (file.size > maxSize) {
                showToast('El archivo es demasiado grande. Máximo 2MB.', 'error');
                return false;
            }

            return true;
        }

        function showPreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewImage').classList.remove('hidden');
                document.getElementById('previewPlaceholder').classList.add('hidden');

                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('imagenInfo').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function clearPreview() {
            document.getElementById('previewImage').src = '';
            document.getElementById('previewImage').classList.add('hidden');
            document.getElementById('previewPlaceholder').classList.remove('hidden');
            document.getElementById('imagenInfo').classList.add('hidden');
            selectedFile = null;
            document.getElementById('imagenInput').value = '';
        }

        function handleFileSelect(file) {
            if (validateFile(file)) {
                selectedFile = file;
                showPreview(file);
                showToast('Nueva imagen cargada', 'success');
            }
        }

        // Event listeners
        const uploadArea = document.getElementById('uploadArea');
        const imagenInput = document.getElementById('imagenInput');
        const selectImageBtn = document.getElementById('selectImageBtn');
        const removeImageBtn = document.getElementById('removeImageBtn');

        selectImageBtn.addEventListener('click', () => imagenInput.click());

        imagenInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) handleFileSelect(file);
        });

        uploadArea.addEventListener('click', () => imagenInput.click());

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
            const file = e.dataTransfer.files[0];
            if (file) handleFileSelect(file);
        });

        removeImageBtn.addEventListener('click', () => {
            clearPreview();
            showToast('Imagen removida', 'info');
        });

        document.getElementById('editarEstanteriaForm').addEventListener('submit', () => {
            showToast('Actualizando estantería...', 'info');
        });

        document.addEventListener('DOMContentLoaded', () => showToast('Módulo de edición cargado', 'info'));
    </script>
</x-app-layout>
@endcan

