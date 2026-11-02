@can('documentos_create')
<x-app-layout>
    @section('title', 'Cargar Documento')
    @section('subtitle', 'Registrar nuevo documento con uno o más PDFs')

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
        .form-select{ backdrop-filter:blur(8px); border-radius:.75rem; padding:.75rem 1rem; width:100%; color:var(--text); }
        .form-select:focus{ outline:none; box-shadow:0 0 0 3px rgba(30,58,138,0.1); }
        .upload-area { border: 2px dashed var(--card-border); border-radius: 0.75rem; padding: 1rem; text-align: center; transition: all 0.3s ease; cursor: pointer; background: var(--card-bg); min-height: 180px; display: flex; align-items: center; justify-content: center; }
        .upload-area:hover { border-color: var(--color-primary); background: rgba(30, 58, 138, 0.05); }
        .upload-area.dragover { border-color: var(--color-secondary); background: rgba(34, 197, 94, 0.05); transform: scale(1.02); }
        .preview-pdf { width: 100%; height: 300px; border-radius: 0.75rem; border: 1px solid var(--card-border); }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
        .pdf-badge { position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
        .pdf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .pdf-item { position: relative; border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px 8px 8px; text-align: center; transition: all 0.2s; }
        .pdf-item:hover { border-color: #3b82f6; transform: scale(1.02); }
        .pdf-item-icon { width: 50px; height: 60px; margin: 0 auto 8px; background: #fef2f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .pdf-item-name { font-size: 10px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .btn-delete-pdf { position: absolute; top: -6px; left: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ef4444; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; opacity: 1; transition: opacity 0.2s; z-index: 10; }
        .btn-delete-pdf:hover { background: #dc2626; transform: scale(1.1); }
    </style>

    <div class="container max-w-6xl px-6 pb-20 mx-auto">
        <div class="p-8 glass rounded-2xl fade-in">
            <form id="crearDocumentoForm" action="{{route('administracion.documentos.store')}}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Campo: Número del Expediente y Descripción -->
                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                    <div class="mb-6">
                        <label class="form-label">Número del Expediente:</label>
                        <input type="text" name="name" class="bg-blue-100 border-none form-input" placeholder="Ingresa el número de expediente..." value="{{ old('name') }}" required>
                        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="form-label">Descripción del Archivo:</label>
                        <input type="text" name="description" class="bg-blue-100 border-none form-input" placeholder="Ingresa la descripción del documento..." value="{{ old('description') }}" required>
                        @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Campo: Fecha del Documento -->
                <div class="mb-6">
                    <label class="form-label">Fecha del Documento:</label>
                    <input type="date" name="fecha" class="bg-blue-100 border-none form-input w-fit" value="{{ old('fecha', date('Y-m-d')) }}" required>
                    @error('fecha')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                 <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                <!-- Campo: Estantería -->
                <div class="mb-6">
                    <label class="form-label">Estantería 📚:</label>
                    <select name="estanteria_id" class="bg-blue-100 border-none form-select" required>
                        <option value="">Seleccionar estantería...</option>
                        @foreach($estanterias as $estanteria)
                            <option value="{{ $estanteria->id }}" {{ old('estanteria_id') == $estanteria->id ? 'selected' : '' }}>
                                {{ $estanteria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('estanteria_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Campo: Categoría -->
                <div class="mb-6">
                    <label class="form-label">Categoría 📁:</label>
                    <select name="categoria_id" class="bg-blue-100 border-none form-select">
                        <option value="">Seleccionar categoría...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                 </div>
                <!-- Área de carga de múltiples PDFs -->
                <div class="mb-8">
                    <label class="form-label">Documentos PDF (puede seleccionar múltiples):</label>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Área de carga -->
                        <div id="uploadArea" class="upload-area">
                            <div class="space-y-2">
                                <div class="text-2xl">📄</div>
                                <div>
                                    <p class="mb-2 text-sm font-semibold">Seleccionar PDFs</p>
                                    <p class="mb-3 text-xs text-slate-400">Arrastra y suelta PDFs aquí o haz clic para seleccionar</p>
                                    <button type="button" id="selectPdfsBtn" class="btn btn-primary">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7,10 12,15 17,10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                        Seleccionar Archivos
                                    </button>
                                </div>
                                <div class="text-xs text-slate-500">
                                    Formatos: PDF<br>
                                    Máximo: 10MB por archivo
                                </div>
                            </div>
                            <input type="file" id="pdfsInput" name="pdfs[]" accept=".pdf" class="hidden" multiple>
                        </div>

                        <!-- Lista de PDFs seleccionados -->
                        <div>
                            <label class="form-label">Documentos seleccionados:</label>
                            <div id="pdfsContainer" class="glass rounded-xl p-4 min-h-[180px]">
                                <div id="pdfsPlaceholder" class="flex flex-col items-center justify-center h-full py-8 text-center text-slate-400">
                                    <div class="mb-3 text-5xl">📄</div>
                                    <p class="mb-2 text-base font-semibold">Sin documentos</p>
                                    <p class="text-sm">Selecciona uno o más PDFs</p>
                                </div>
                                <div id="pdfsList" class="hidden pdf-grid"></div>
                            </div>
                            <div id="pdfsInfo" class="hidden p-4 mt-4 rounded-lg glass">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm"><span class="text-slate-400">Total:</span> <span id="totalPdfs" class="font-semibold">0</span> documento(s)</span>
                                    <button type="button" id="clearAllPdfs" class="text-sm text-red-500 hover:text-red-700">Limpiar todo</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('pdfs')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-between gap-4 pt-6 border-t border-white/10">
                    <x-primary-link href="{{ route('administracion.documentos.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95z"></path></svg>
                        Regresar
                    </x-primary-link>
                    <x-primary-button type="submit" class="btn-save">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Guardar Documento(s)
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
        let selectedFiles = [];

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
            const validTypes = ['application/pdf'];
            const maxSize = 10 * 1024 * 1024;
            if (!validTypes.includes(file.type)) { showToast('Solo se permiten archivos PDF.', 'error'); return false; }
            if (file.size > maxSize) { showToast('El archivo es demasiado grande. Máximo 10MB.', 'error'); return false; }
            return true;
        }

        function renderPdfsList() {
            const placeholder = document.getElementById('pdfsPlaceholder');
            const list = document.getElementById('pdfsList');
            const info = document.getElementById('pdfsInfo');

            if (selectedFiles.length === 0) {
                placeholder.classList.remove('hidden');
                list.classList.add('hidden');
                info.classList.add('hidden');
                return;
            }

            placeholder.classList.add('hidden');
            list.classList.remove('hidden');
            info.classList.remove('hidden');
            document.getElementById('totalPdfs').textContent = selectedFiles.length;

            const categorias = @json($categorias);

            list.innerHTML = selectedFiles.map((file, index) => `
                <div class="pdf-item" title="${file.name}">
                    <button type="button" class="btn-delete-pdf" onclick="removePdf(${index})" title="Quitar">×</button>
                    <div class="pdf-badge">${index + 1}</div>
                    <div class="pdf-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                        </svg>
                    </div>
                    <p class="pdf-item-name">${file.name}</p>
                    <select name="categoria_archivo_id[]" class="w-full p-1 mt-2 text-xs border border-gray-300 rounded" required>
                        <option value="">Categoría</option>
                        ${categorias.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('')}
                    </select>
                    <p class="mt-1 text-xs text-gray-400">${formatFileSize(file.size)}</p>
                </div>
            `).join('');
        }

        function removePdf(index) {
            selectedFiles.splice(index, 1);
            renderPdfsList();
            updateInput();
            showToast('Documento removido', 'info');
        }

        function updateInput() {
            const input = document.getElementById('pdfsInput');
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (validateFile(file)) {
                    const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
                    if (!exists) {
                        selectedFiles.push(file);
                    }
                }
            });
            renderPdfsList();
            updateInput();
            if (selectedFiles.length > 0) {
                showToast(`${selectedFiles.length} documento(s) cargado(s)`, 'success');
            }
        }

        // Event listeners
        const uploadArea = document.getElementById('uploadArea');
        const pdfsInput = document.getElementById('pdfsInput');
        const selectPdfsBtn = document.getElementById('selectPdfsBtn');
        const clearAllPdfs = document.getElementById('clearAllPdfs');

        selectPdfsBtn.addEventListener('click', () => pdfsInput.click());
        pdfsInput.addEventListener('change', (e) => handleFiles(e.target.files));
        uploadArea.addEventListener('click', () => pdfsInput.click());
        uploadArea.addEventListener('dragover', (e) => { e.preventDefault(); uploadArea.classList.add('dragover'); });
        uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('dragover'));
        uploadArea.addEventListener('drop', (e) => { e.preventDefault(); uploadArea.classList.remove('dragover'); handleFiles(e.dataTransfer.files); });
        clearAllPdfs.addEventListener('click', () => { selectedFiles = []; renderPdfsList(); updateInput(); showToast('Todos los documentos removidos', 'info'); });

        document.getElementById('crearDocumentoForm').addEventListener('submit', (e) => {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                showToast('Debe seleccionar al menos un documento PDF', 'error');
                return;
            }
            showToast('Guardando documento(s)...', 'info');
        });

        document.addEventListener('DOMContentLoaded', () => showToast('Módulo de carga de documento cargado', 'info'));
    </script>
</x-app-layout>
@endcan

