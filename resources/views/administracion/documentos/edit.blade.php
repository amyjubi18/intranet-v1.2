@can('documentos_edit')
<x-app-layout>
    @section('title', 'Editar Documento')
    @section('subtitle', 'Actualizar documento y sus archivos PDF')

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
        .upload-area { border: 2px dashed var(--card-border); border-radius: 0.75rem; padding: 1rem; text-align: center; transition: all 0.3s ease; cursor: pointer; background: var(--card-bg); min-height: 100px; display: flex; align-items: center; justify-content: center; }
        .upload-area:hover { border-color: var(--color-primary); background: rgba(30, 58, 138, 0.05); }
        .upload-area.dragover { border-color: var(--color-secondary); background: rgba(34, 197, 94, 0.05); transform: scale(1.02); }
        .fade-in{ animation:fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn{ from{ opacity:0; transform:translateY(10px); } to{ opacity:1; transform:translateY(0); } }
        .pdf-badge { position: absolute; top: -8px; right: -6px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; z-index: 5; }
        .pdf-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .pdf-item { position: relative; border: 2px solid #e2e8f0; border-radius: 8px; padding: 12px 8px 8px; text-align: center; transition: all 0.2s; }
        .pdf-item:hover { border-color: #3b82f6; transform: scale(1.02); }
        .pdf-item-icon { width: 50px; height: 60px; margin: 0 auto 8px; background: #fef2f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .pdf-item-name { font-size: 10px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .btn-delete-pdf { position: absolute; top: -6px; left: -6px; width: 20px; height: 20px; border-radius: 50%; background: #ef4444; color: white; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; opacity: 1; transition: opacity 0.2s; z-index: 10; }
        .btn-delete-pdf:hover { background: #dc2626; transform: scale(1.1); }
        .swal2-popup .swal2-actions { display: flex !important; opacity: 1 !important; }
        .swal2-popup .swal2-confirm { opacity: 1 !important; visibility: visible !important; background-color: #F52727 !important; color: white !important; }
        .swal2-popup .swal2-cancel { opacity: 1 !important; visibility: visible !important; background-color: #2780F5 !important; color: white !important; }

        /* PDF Zoom Controls */
        .pdf-zoom-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pdf-zoom-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.2s;
        }
        .pdf-zoom-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        .pdf-zoom-btn:active {
            transform: scale(0.95);
        }
        .pdf-zoom-level {
            color: white;
            font-size: 12px;
            min-width: 45px;
            text-align: center;
        }
        #pdfContainer {
            overflow: auto;
            max-height: calc(100vh - 100px);
            transition: transform 0.3s ease;
        }
    </style>

    <div class="container px-6 mx-auto">
        <div class="p-8 glass rounded-2xl fade-in">
            <form id="editarDocumentoForm" action="{{route('administracion.documentos.update', $documento->id)}}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Archivos existentes -->
                @if($documento->archivos->count() > 0)
                <div class="mb-6">
                    <label class="form-label">Archivos Actuales ({{ $documento->archivos->count() }}):</label>
                    <div class="pdf-grid">
                        @foreach($documento->archivos as $archivo)
                        <div class="pdf-item">
                            <button type="button" class="btn-delete-pdf" onclick="deleteArchivo({{ $archivo->id }}, '{{ $archivo->nombre_archivo }}')" title="Eliminar archivo">×</button>
                            <div class="pdf-badge">{{ $loop->iteration }}</div>
                            <div class="pdf-item-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-8.5 7.5c0 .828-.672 1.5-1.5 1.5H9v2H7.5V7H10c.828 0 1.5.672 1.5 1.5v1zm5 2c0 .828-.672 1.5-1.5 1.5h-2.5V7H15c.828 0 1.5.672 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14a2 2 0 0 0 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                                </svg>
                            </div>
                            <select name="categoria_existente[{{ $archivo->id }}]" class="w-full p-1 mt-2 text-xs border border-gray-300 rounded" required>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ $archivo->categoria_id == $categoria->id ? 'selected' : '' }}>{{ $categoria->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 pdf-item-name" title="{{ $archivo->nombre_archivo }}">{{ $archivo->nombre_archivo }}</p>
                            <button type="button" onclick="viewPdf('{{ asset('archivos/' . $archivo->ruta) }}', '{{ $archivo->nombre_archivo }}')" class="p-2 mt-1 text-xs text-white bg-blue-900 rounded-md hover:bg-blue-800">Ver</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Campo: Número del Expediente y Descripción -->
                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                    <div class="mb-6">
                        <label class="form-label">Número del Expediente:</label>
                        <input type="text" name="name" class="bg-blue-100 border-none form-input" value="{{ old('name', $documento->name) }}" required>
                        @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mb-6">
                        <label class="form-label">Descripción del Archivo:</label>
                        <input type="text" name="description" class="bg-blue-100 border-none form-input" value="{{ old('description', $documento->description) }}" required>
                        @error('description')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="form-label">Fecha del Documento:</label>
                    <input type="date" name="fecha" class="bg-blue-100 border-none form-input w-fit" value="{{ old('fecha', $documento->fecha) }}" required>
                    @error('fecha')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                 <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                <!-- Campo: Estantería -->
                <div class="mb-6">
                    <label class="form-label">Estantería 📚:</label>
                    <select name="estanteria_id" class="bg-blue-100 border-none form-select" required>
                        <option value="">Seleccionar estantería...</option>
                        @foreach($estanterias as $estanteria)
                            <option value="{{ $estanteria->id }}" {{ old('estanteria_id', $documento->estanteria_id) == $estanteria->id ? 'selected' : '' }}>
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
                            <option value="{{ $categoria->id }}" {{ old('categoria_id', $documento->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                 </div>
                <!-- Área para agregar nuevos PDFs -->
                <div class="mb-8">
                    <label class="form-label">Agregar Más Documentos PDF:</label>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div id="uploadArea" class="upload-area">
                            <div class="space-y-2">
                                <div class="text-2xl">📄➕</div>
                                <p class="text-sm font-semibold">Añadir Nuevos PDFs</p>
                                <button type="button" id="selectPdfsBtn" class="btn btn-primary">Seleccionar Archivos</button>
                                <p class="text-xs text-slate-500">Arrastra PDFs aquí o haz clic</p>
                            </div>
                            <input type="file" id="pdfsInput" name="pdfs[]" accept=".pdf" class="hidden" multiple>
                        </div>
                        <div>
                            <label class="form-label">Nuevos Documentos:</label>
                            <div id="pdfsContainer" class="glass rounded-xl p-4 min-h-[100px]">
                                <div id="pdfsPlaceholder" class="py-4 text-center text-slate-400">Sin nuevos documentos</div>
                                <div id="pdfsList" class="hidden pdf-grid"></div>
                            </div>
                            <div id="pdfsInfo" class="hidden p-3 mt-3 text-sm rounded-lg glass">
                                <span><span class="text-slate-400">Total:</span> <span id="totalPdfs" class="font-semibold">0</span> nuevo(s)</span>
                                <button type="button" id="clearAllPdfs" class="ml-3 text-red-500 hover:text-red-700">Limpiar</button>
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
                    <x-primary-button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-blue-900 rounded-md btn hover:bg-blue-700">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Actualizar Documento(s)
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Modal PDF - Full Screen -->
        <div id="pdfModal" class="fixed inset-0 z-[9999] hidden bg-black/90" style="position:fixed;top:0;left:0;right:0;bottom:0;">
            <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;margin:0;padding:0;">
                <div style="position:relative;width:100%;height:100%;background:white;overflow:hidden;margin:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px;background:#1e3a8a;margin:0;">
                        <h3 class="text-xl font-semibold text-white" id="pdfModalTitle">Documento</h3>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <!-- Controles de zoom -->
                            <div class="pdf-zoom-controls">
                                <button type="button" onclick="zoomOut()" class="pdf-zoom-btn" title="Disminuir">−</button>
                                <span class="pdf-zoom-level" id="zoomLevel">100%</span>
                                <button type="button" onclick="zoomIn()" class="pdf-zoom-btn" title="Ampliar">+</button>
                                <button type="button" onclick="resetZoom()" class="pdf-zoom-btn" title="Restablecer" style="font-size:14px;">⟳</button>
                            </div>
                            <button type="button" onclick="closePdfModal()" class="text-3xl font-bold text-white hover:text-gray-300" style="background:none;border:none;cursor:pointer;margin-left:12px;">×</button>
                        </div>
                    </div>
                    <div id="pdfContainer" style="margin:0;padding:0;height:calc(100% - 72px);background:#f3f4f6;">
                        <iframe id="pdfFrame" style="width:100%;height:100%;border:none;transition:transform 0.3s ease;"></iframe>
                    </div>
                </div>
            </div>
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
        let newFiles = [];

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast'), icon = document.getElementById('toastIcon'), msgEl = document.getElementById('toastMsg');
            icon.textContent = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' }[type] || '✅';
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3000);
        }

        function validateFile(file) {
            if (file.type !== 'application/pdf') { showToast('Solo PDF permitido.', 'error'); return false; }
            if (file.size > 10 * 1024 * 1024) { showToast('Máximo 10MB.', 'error'); return false; }
            return true;
        }

        function renderPdfsList() {
            const placeholder = document.getElementById('pdfsPlaceholder'), list = document.getElementById('pdfsList'), info = document.getElementById('pdfsInfo');
            if (newFiles.length === 0) { placeholder.classList.remove('hidden'); list.classList.add('hidden'); info.classList.add('hidden'); return; }
            placeholder.classList.add('hidden'); list.classList.remove('hidden'); info.classList.remove('hidden');
            document.getElementById('totalPdfs').textContent = newFiles.length;
            const categorias = @json($categorias);

            list.innerHTML = newFiles.map((file, i) => `
                <div class="pdf-item">
                    <button type="button" class="btn-delete-pdf" onclick="removeNewPdf(${i})" style="opacity:1">×</button>
                    <div class="pdf-item-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 2H8a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
                        </svg>
                    </div>
                    <p class="pdf-item-name">${file.name}</p>
                    <select name="categoria_archivo_id[]" class="w-full p-1 mt-2 text-xs border border-gray-300 rounded" required>
                        <option value="">Categoría</option>
                        ${categorias.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('')}
                    </select>
                </div>
            `).join('');
        }

        function removeNewPdf(i) { newFiles.splice(i, 1); renderPdfsList(); updateInput(); showToast('Documento removido', 'info'); }
        function updateInput() { const input = document.getElementById('pdfsInput'); const dt = new DataTransfer(); newFiles.forEach(f => dt.items.add(f)); input.files = dt.files; }
        function handleFiles(files) { Array.from(files).forEach(f => { if (validateFile(f) && !newFiles.some(x => x.name === f.name && x.size === f.size)) newFiles.push(f); }); renderPdfsList(); updateInput(); if (newFiles.length > 0) showToast(`${newFiles.length} documento(s)`, 'success'); }

        function viewPdf(url, name) {
            // Reset zoom
            pdfZoom = 100;
            updateZoomDisplay();
            document.getElementById('pdfFrame').style.transform = 'scale(1)';

            document.getElementById('pdfModal').classList.remove('hidden');
            document.getElementById('pdfFrame').src = url + '#toolbar=0&navpanes=0&scrollbar=1';
            document.getElementById('pdfModalTitle').textContent = name;
            document.body.style.overflow = 'hidden';
        }

        let pdfZoom = 100;

        function zoomIn() {
            if (pdfZoom < 200) {
                pdfZoom += 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function zoomOut() {
            if (pdfZoom > 50) {
                pdfZoom -= 25;
                updateZoomDisplay();
                document.getElementById('pdfFrame').style.transform = `scale(${pdfZoom / 100})`;
            }
        }

        function resetZoom() {
            pdfZoom = 100;
            updateZoomDisplay();
            document.getElementById('pdfFrame').style.transform = 'scale(1)';
        }

        function updateZoomDisplay() {
            document.getElementById('zoomLevel').textContent = pdfZoom + '%';
        }

        function closePdfModal() {
            document.getElementById('pdfModal').classList.add('hidden');
            document.getElementById('pdfFrame').src = '';
            document.body.style.overflow = 'auto';
        }

        function deleteArchivo(id, name) {
            Swal.fire({
                title: '¿Eliminar archivo?',
                text: `"${name}" será eliminado permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/administracion/documentos/archivo/${id}`;
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        document.getElementById('selectPdfsBtn').onclick = () => document.getElementById('pdfsInput').click();
        document.getElementById('pdfsInput').onchange = e => handleFiles(e.target.files);
        document.getElementById('uploadArea').onclick = () => document.getElementById('pdfsInput').click();
        document.getElementById('uploadArea').ondragover = e => { e.preventDefault(); document.getElementById('uploadArea').classList.add('dragover'); };
        document.getElementById('uploadArea').ondragleave = () => document.getElementById('uploadArea').classList.remove('dragover');
        document.getElementById('uploadArea').ondrop = e => { e.preventDefault(); document.getElementById('uploadArea').classList.remove('dragover'); handleFiles(e.dataTransfer.files); };
        document.getElementById('clearAllPdfs').onclick = () => { newFiles = []; renderPdfsList(); updateInput(); showToast('Limpiado', 'info'); };
        document.getElementById('pdfModal').onclick = e => { if (e.target === document.getElementById('pdfModal')) closePdfModal(); };
        document.onkeydown = e => { if (e.key === 'Escape') closePdfModal(); };
        document.getElementById('editarDocumentoForm').onsubmit = (e) => {
            showToast('Guardando...', 'info');
        };
        document.addEventListener('DOMContentLoaded', () => showToast('Módulo cargado', 'info'));
    </script>
</x-app-layout>
@endcan

