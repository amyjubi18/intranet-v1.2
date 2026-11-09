@can('bienes_create')
@section('title', 'Registrar')
@section('subtitle', 'Registrar Bienes Públicos')
<x-app-layout>

    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    </x-slot>

    <style>
        .btn-save {
      flex: 1;
      padding: 1.125rem 2rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.625rem;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 91, 175, 0.4);
    }

    .btn-save:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .btn-save svg {
      width: 20px;
      height: 20px;
    }
    </style>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <div class="w-full h-auto p-4 bg-white rounded-lg shadow-lg lg:col-span-1">
            <h2 class="mb-4 text-xl font-bold text-center text-blue-950">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="inline-block mr-1" style="fill: rgba(30, 58, 138, 1);"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"></path></svg>
                Visualizar Registros
            </h2>
            <div id="pending-records" class="grid grid-cols-1 gap-4 mt-4"></div>
            <div id="no-records" class="mt-4 text-center text-gray-500">No hay registros pendientes</div>
        </div>

        <div class="w-full p-6 lg:col-span-3">
            <div class="max-w-4xl p-6 mx-auto bg-white rounded-lg shadow-lg">
                <form id="batch-form" action="{{ route('administracion.bienes_publicos.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 rounded-lg">
                    @csrf
                    <div id="form-rows" class="space-y-4">
                        <div class="p-4 rounded-lg shadow-lg form-row bg-white/20">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Sede:</label>
<input type="text" value="CODECYT" name="sede[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Fecha de Adquisición:</label>
                                        <input type="date" name="fecha_adquisicion[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Unidad Administrativa:</label>
                                        <select id="unidad_administrativa_0" name="unidad_administrativa_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg unidad-administrativa campo-select" required>
                                            <option value="">Seleccione</option>
                                            @foreach ($unidad_administrativas as $ua)
                                            <option value="{{ $ua->id }}">{{ $ua->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Usuario Administrativo:</label>
                                        <select id="usuario_administrativo_0" name="usuario_administrativo_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg usuario-administrativo campo-select" required>
                                            <option value="">Seleccione una unidad primero</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-black">Usuario de Uso:</label>
                                    <select id="usuario_de_uso_0" name="usuario_de_uso_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg usuario-de-uso campo-select" required>
                                        <option value="">Seleccione un usuario administrativo primero</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-black">N° Interno del Bien:</label>
                                    <input type="text" name="n_interno_bien[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-black">Descripción:</label>
                                    <select id="descripcion_0" name="descripcion[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg descripcion-select campo-select" required>
                                        <option value="">Seleccione o escriba una nueva...</option>
                                        @if(isset($descripciones) && count($descripciones) > 0)
                                            @foreach($descripciones as $desc)
                                                <option value="{{ $desc }}">{{ $desc }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Puede seleccionar una descripción existente O escribir una nueva</p>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-black">Forma de Adquisición:</label>
                                    <select name="forma_adquisicion_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($forma_adquisiciones as $fa)
                                        <option value="{{ $fa->id }}">{{ $fa->forma_adquisicion }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-3">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">N° Factura:</label>
                                        <input type="text" name="n_factura[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Valor Factura (Bs.):</label>
                                        <input type="number" step="0.01" name="valor_factura[]" value="0.00" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Moneda:</label>
                                        <select name="moneda[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                            <option value="Bs.">Bolívar (Bs.)</option>
                                            <option value="USD">Dólar (USD)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Estado del Uso:</label>
                                        <select name="estado_del_uso_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                            <option value="">Seleccione</option>
                                            @foreach ($estado_del_usos as $eu)
                                            <option value="{{ $eu->id }}">{{ $eu->estado_del_uso }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Condición Física:</label>
                                        <select name="condicion_id[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                            <option value="">Seleccione</option>
                                            @foreach ($condiciones as $c)
                                            <option value="{{ $c->id }}">{{ $c->condicion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Marca:</label>
                                        <input type="text" name="marca[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Modelo:</label>
                                        <input type="text" name="modelo[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Color:</label>
                                        <div class="flex items-center gap-2">
                                            <span class="color-badge-preview w-9 h-9 rounded-xl border-2 border-white ring-2 ring-gray-300 shadow-sm flex-shrink-0 transition-colors"
                                                  style="background-color: #cccccc;"
                                                  title="Muestra del color"></span>
                                            <select name="color_id[]"
                                                    class="color-select-input w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select font-medium"
                                                    onchange="updateRowColorPreview(this)"
                                                    required>
                                                <option value="" data-color="#cccccc">Seleccione</option>
                                                @foreach ($colors as $color)
                                                <option value="{{ $color->id }}" data-color="{{ $color->color_css }}">{{ $color->color }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Serial:</label>
                                        <input type="text" name="serial[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                </div>

                                <!-- QR Code Section -->
                                <div class="p-4 mt-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start gap-4">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-blue-800">Código QR del Bien</h4>
                                            <p class="mt-1 text-xs text-blue-700">
                                                Genera un código QR basado en el N° Interno o carga una imagen QR existente.
                                            </p>

                                            <div class="mt-3">
                                                <label class="block mb-1 text-xs font-medium text-blue-800">Opciones de QR:</label>
                                                <div class="flex flex-wrap gap-2">
                                                    <!-- Botón generar QR -->
                                                    <button type="button" onclick="generateRowQR(this)" class="px-3 py-2 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                                                        🔄 Generar QR Automático
                                                    </button>

                                                    <!-- Input cargar QR -->
                                                    <label class="px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg cursor-pointer hover:bg-blue-700">
                                                        📤 Cargar QR Existente
                                                        <input type="file" accept="image/*" class="hidden" onchange="loadQRImage(this)">
                                                    </label>

                                                    <!-- Botón escanear QR -->
                                                    <button type="button" onclick="scanRowQR(this)" class="px-3 py-2 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                                        📷 Escanear QR Físico
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Vista previa del QR -->
                                            <div id="qr-preview-0" class="mt-3 hidden">
                                                <img src="" alt="Vista previa QR" class="w-24 h-24 border border-blue-300 rounded-lg">
                                                <input type="hidden" name="qr_code[]" value="">
                                                <button type="button" onclick="removeQRPreview(this)" class="ml-2 text-xs text-red-600 hover:underline">Quitar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Comentario:</label>
                                        <input type="text" name="comentario[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto">
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-medium text-black">Fecha Comentario:</label>
                                        <input type="date" name="fecha_comentario[]" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="flex hidden px-3 py-3 mt-2 text-white transition-all bg-red-500 rounded-lg remove-row hover:bg-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);margin-right: 5px;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg>
                                Remover
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-6 gap-4">
                        <button type="button" id="add-row" class="btn-save">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>
                            Agregar Registro
                        </button>
                            <x-primary-link href="{{ route('administracion.bienes_publicos.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg> Regresar
                            </x-primary-link>
                            <x-primary-button type="submit" class="btn-save">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg>
                                Guardar
                            </x-primary-button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let rowCount = 1;
        const tomSelectInstances = [];

        function initDescripcionTomSelect(selectElement) {
            if (selectElement.tomselect) return selectElement.tomselect;
            return new TomSelect(selectElement, {
                create: true,
                maxItems: 1,
                createOnBlur: true,
                sortField: { field: "text", direction: "asc" },
                placeholder: "Seleccione o escriba una nueva...",
                plugins: ['dropdown_input'],
                onItemAdd: function() { updatePendingRecords(); }
            });
        }

        function initTomSelect(selectElement) {
            if (selectElement.tomselect) return selectElement.tomselect;
            return new TomSelect(selectElement, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "Buscar...",
                plugins: ['dropdown_input'],
            });
        }

        async function loadUsuariosAdministrativos(rowIndex) {
            const unidadSelect = document.getElementById('unidad_administrativa_' + rowIndex);
            const usuarioAdminSelect = document.getElementById('usuario_administrativo_' + rowIndex);
            const usuarioUsoSelect = document.getElementById('usuario_de_uso_' + rowIndex);
            if (!unidadSelect || !usuarioAdminSelect) return;
            const unidadId = unidadSelect.value;
            usuarioAdminSelect.innerHTML = '<option value="">Seleccione</option>';
            usuarioUsoSelect.innerHTML = '<option value="">Seleccione un usuario administrativo primero</option>';
            if (usuarioAdminSelect.tomselect) usuarioAdminSelect.tomselect.clear();
            if (usuarioUsoSelect.tomselect) usuarioUsoSelect.tomselect.clear();
            if (unidadId) {
                usuarioAdminSelect.disabled = true;
                usuarioAdminSelect.querySelector('option').textContent = 'Cargando...';
                try {
                    const response = await fetch('/api/bienes_publicos/usuarios-administrativos/' + unidadId);
                    const usuarios = await response.json();
                    usuarioAdminSelect.innerHTML = '<option value="">Seleccione</option>';
                    usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id;
                        option.textContent = usuario.usuario_administrativo;
                        usuarioAdminSelect.appendChild(option);
                    });
                } catch (error) {
                    usuarioAdminSelect.innerHTML = '<option value="">Error al cargar</option>';
                }
                usuarioAdminSelect.disabled = false;
            }
        }

        async function loadUsuariosDeUso(rowIndex) {
            const usuarioAdminSelect = document.getElementById('usuario_administrativo_' + rowIndex);
            const usuarioUsoSelect = document.getElementById('usuario_de_uso_' + rowIndex);
            if (!usuarioAdminSelect || !usuarioUsoSelect) return;
            const usuarioAdminId = usuarioAdminSelect.value;
            usuarioUsoSelect.innerHTML = '<option value="">Seleccione</option>';
            if (usuarioUsoSelect.tomselect) usuarioUsoSelect.tomselect.clear();
            if (usuarioAdminId) {
                usuarioUsoSelect.disabled = true;
                usuarioUsoSelect.querySelector('option').textContent = 'Cargando...';
                try {
                    const response = await fetch('/api/bienes_publicos/usuarios-de-uso/' + usuarioAdminId);
                    const usuarios = await response.json();
                    usuarioUsoSelect.innerHTML = '<option value="">Seleccione</option>';
                    usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id;
                        option.textContent = usuario.usuario_de_uso;
                        usuarioUsoSelect.appendChild(option);
                    });
                } catch (error) {
                    usuarioUsoSelect.innerHTML = '<option value="">Error al cargar</option>';
                }
                usuarioUsoSelect.disabled = false;
            }
        }

        function updateRowColorPreview(selectEl) {
            if (!selectEl) return;
            const container = selectEl.closest('.flex');
            const preview = container ? container.querySelector('.color-badge-preview') : null;
            if (preview) {
                const opt = selectEl.options[selectEl.selectedIndex];
                const hex = opt ? opt.getAttribute('data-color') : '#cccccc';
                preview.style.backgroundColor = hex || '#cccccc';
            }
            updatePendingRecords();
        }

        function setupDependentSelects(row) {
            const unidadSelect = row.querySelector('.unidad-administrativa');
            const usuarioAdminSelect = row.querySelector('.usuario-administrativo');
            if (unidadSelect) {
                unidadSelect.addEventListener('change', function() {
                    loadUsuariosAdministrativos(this.id.split('_').pop());
                });
            }
            if (usuarioAdminSelect) {
                usuarioAdminSelect.addEventListener('change', function() {
                    loadUsuariosDeUso(this.id.split('_').pop());
                });
            }
        }

        function setupRealTimeUpdates(row) {
            row.querySelectorAll('.campo-texto').forEach(function(input) {
                input.addEventListener('input', function() {
                    updatePendingRecords();
                });
            });
            row.querySelectorAll('.campo-select').forEach(function(select) {
                if (select.tomselect) {
                    select.tomselect.on('change', function() {
                        updatePendingRecords();
                    });
                } else {
                    select.addEventListener('change', function() {
                        updatePendingRecords();
                    });
                }
            });
        }

        function getSelectText(selectElement) {
            if (!selectElement) return 'No seleccionado';
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            return selectedOption ? selectedOption.textContent : 'No seleccionado';
        }

        function updatePendingRecords() {
            const pendingRecordsDiv = document.getElementById('pending-records');
            const noRecords = document.getElementById('no-records');
            pendingRecordsDiv.innerHTML = '';
            const rows = document.querySelectorAll('.form-row');
            if (rows.length === 0) {
                noRecords.classList.remove('hidden');
                return;
            }
            noRecords.classList.add('hidden');
            rows.forEach((row, index) => {
                const descSelect = row.querySelector('.descripcion-select');
                let descripcion = descSelect && descSelect.tomselect ? descSelect.tomselect.getValue() : (descSelect ? descSelect.value : '');
                if (!descripcion) descripcion = 'No ingresada';

                const sede = row.querySelector('input[name="sede[]"]').value || 'CODECYT';
                const fechaAdquisicion = row.querySelector('input[name="fecha_adquisicion[]"]').value || 'No ingresada';
                const unidadAdministrativa = row.querySelector('.unidad-administrativa');
                const usuarioAdministrativo = row.querySelector('.usuario-administrativo');
                const usuarioDeUso = row.querySelector('.usuario-de-uso');
                const nInternoBien = row.querySelector('input[name="n_interno_bien[]"]').value || 'No ingresado';
                const formaAdquisicion = row.querySelector('select[name="forma_adquisicion_id[]"]');
                const nFactura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';
                const valorFactura = row.querySelector('input[name="valor_factura[]"]').value || '0';
                const moneda = row.querySelector('select[name="moneda[]"]');
                const estadoDelUso = row.querySelector('select[name="estado_del_uso_id[]"]');
                const condicion = row.querySelector('select[name="condicion_id[]"]');
                const marca = row.querySelector('input[name="marca[]"]').value || 'No ingresada';
                const modelo = row.querySelector('input[name="modelo[]"]').value || 'No ingresada';
                const color = row.querySelector('select[name="color_id[]"]');
                const colorOpt = color && color.selectedIndex >= 0 ? color.options[color.selectedIndex] : null;
                const colorHex = colorOpt ? (colorOpt.getAttribute('data-color') || '#cccccc') : '#cccccc';
                const colorText = getSelectText(color);
                const serial = row.querySelector('input[name="serial[]"]').value || 'No ingresado';
                const comentario = row.querySelector('input[name="comentario[]"]').value || 'Sin comentario';
                const fechaComentario = row.querySelector('input[name="fecha_comentario[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = '<div class="flex items-center justify-between mb-2"><h3 class="font-bold text-blue-900">Bien #' + (index + 1) + '</h3><span class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded">' + sede + '</span></div>' +
                    '<div class="mb-2 space-y-1 text-xs text-gray-600">' +
                    '<p><span class="font-bold text-gray-800 ">Sede:</span> ' + sede + '</p>' +
                    '<p><span class="font-bold text-gray-800">Fecha Adquisición:</span> ' + fechaAdquisicion + '</p>' +
                    '<p><span class="font-bold text-gray-800">Unidad Administrativa:</span> ' + getSelectText(unidadAdministrativa) + '</p>' +
                    '<p><span class="font-bold text-gray-800">Usuario Administrativo:</span> ' + getSelectText(usuarioAdministrativo) + '</p>' +
                    '<p><span class="font-bold text-gray-800">Usuario de Uso:</span> ' + getSelectText(usuarioDeUso) + '</p>' +
                    '<p><span class="font-bold text-gray-800">N° Interno:</span> ' + nInternoBien + '</p>' +
                    '<p><span class="font-bold text-gray-800">Descripción:</span> ' + descripcion + '</p>' +
                    '<p><span class="font-bold text-gray-800">Forma Adquisición:</span> ' + getSelectText(formaAdquisicion) + '</p>' +
                    '<p><span class="font-bold text-gray-800">N° Factura:</span> ' + nFactura + '</p>' +
                    '<p><span class="font-bold text-gray-800">Valor Factura:</span> ' + valorFactura + '</p>' +
                    '<p><span class="font-bold text-gray-800">Moneda:</span> ' + getSelectText(moneda) + '</p>' +
                    '<p><span class="font-bold text-gray-800">Estado del Uso:</span> ' + getSelectText(estadoDelUso) + '</p>' +
                    '<p><span class="font-bold text-gray-800">Condición:</span> ' + getSelectText(condicion) + '</p>' +
                    '<p><span class="font-bold text-gray-800">Marca:</span> ' + marca + '</p>' +
                    '<p><span class="font-bold text-gray-800">Modelo:</span> ' + modelo + '</p>' +
                    '<p class="flex items-center gap-1.5"><span class="font-bold text-gray-800">Color:</span> <span class="inline-block w-3.5 h-3.5 rounded-full border border-gray-300" style="background-color: ' + colorHex + ';"></span> ' + colorText + '</p>' +
                    '<p><span class="font-bold text-gray-800">Serial:</span> ' + serial + '</p>' +
                    '<p><span class="font-bold text-gray-800">Comentario:</span> ' + comentario + '</p>' +
                    '<p><span class="font-bold text-gray-800">Fecha Comentario:</span> ' + fechaComentario + '</p>' +
                    '</div>';
                pendingRecordsDiv.appendChild(recordDiv);
            });
        }

        document.getElementById('add-row').addEventListener('click', function() {
            const formRows = document.getElementById('form-rows');
            const firstRow = formRows.querySelector('.form-row');
            const newRow = firstRow.cloneNode(true);
            const newIndex = rowCount;

            // Resetear el badge del preview de color en la nueva fila
            const newColorPreview = newRow.querySelector('.color-badge-preview');
            if (newColorPreview) {
                newColorPreview.style.backgroundColor = '#cccccc';
            }
            const ids = ['unidad_administrativa', 'usuario_administrativo', 'usuario_de_uso', 'descripcion'];
            ids.forEach(function(baseId) {
                const el = newRow.querySelector('#' + baseId + '_0');
                if (el) {
                    el.id = baseId + '_' + newIndex;
                    el.value = '';
                    if (el.tomselect) el.tomselect.clear();
                }
            });
            newRow.querySelectorAll('input[type="text"], input[type="number"], input[type="date"]').forEach(function(el) {
                if (!el.hasAttribute('disabled')) el.value = '';
            });
            newRow.querySelectorAll('select').forEach(function(el) {
                if (!el.classList.contains('unidad-administrativa') && !el.classList.contains('usuario-administrativo') && !el.classList.contains('usuario-de-uso')) {
                    el.selectedIndex = 0;
                }
                if (el.tomselect) el.tomselect.clear();
            });
            newRow.querySelector('.remove-row').classList.remove('hidden');
            const descSelect = newRow.querySelector('.descripcion-select');
            if (descSelect) initDescripcionTomSelect(descSelect);
            newRow.querySelectorAll('select').forEach(function(select) {
                if (!select.classList.contains('unidad-administrativa') && !select.classList.contains('usuario-administrativo') && !select.classList.contains('usuario-de-uso') && !select.classList.contains('descripcion-select')) {
                    initTomSelect(select);
                }
            });
            setupDependentSelects(newRow);
            setupRealTimeUpdates(newRow);
            formRows.appendChild(newRow);
            const hr = document.createElement('hr');
            hr.className = 'my-4';
            formRows.appendChild(hr);
            rowCount++;
            updatePendingRecords();
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
                const button = e.target.classList.contains('remove-row') ? e.target : e.target.closest('.remove-row');
                const row = button.closest('.form-row');
                const hr = row.nextElementSibling;
                row.querySelectorAll('select').forEach(function(el) {
                    if (el.tomselect) el.tomselect.destroy();
                });
                if (hr && hr.tagName === 'HR') hr.remove();
                row.remove();
                rowCount--;
                updatePendingRecords();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const firstRow = document.querySelector('.form-row');
            if (firstRow) {
                const descSelect = firstRow.querySelector('.descripcion-select');
                if (descSelect) initDescripcionTomSelect(descSelect);
                firstRow.querySelectorAll('select').forEach(function(select) {
                    if (!select.classList.contains('unidad-administrativa') && !select.classList.contains('usuario-administrativo') && !select.classList.contains('usuario-de-uso') && !select.classList.contains('descripcion-select')) {
                        tomSelectInstances.push(initTomSelect(select));
                    }
                });
                setupDependentSelects(firstRow);
                setupRealTimeUpdates(firstRow);
                updatePendingRecords();
            }
        });

        // ==========================================
        // QR CODE FUNCTIONS FOR CREATE VIEW
        // ==========================================

        let html5QrcodeScanner = null;
        let currentRowElement = null;

        // Generate QR for a specific row
        function generateRowQR(button) {
            const row = button.closest('.form-row');
            const nInternoInput = row.querySelector('input[name="n_interno_bien[]"]');
            const nInterno = nInternoInput ? nInternoInput.value : '';

            if (!nInterno) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe ingresar el N° Interno del bien primero',
                    icon: 'error'
                });
                return;
            }

            const qrUrl = 'https://quickchart.io/chart?cht=qr&chs=200x200&chl=' + encodeURIComponent('/bienes/qr/' + nInterno);
            const previewDiv = row.querySelector('#qr-preview-0') || row.querySelector('[id^="qr-preview-"]');

            if (previewDiv) {
                previewDiv.classList.remove('hidden');
                previewDiv.querySelector('img').src = qrUrl;
                previewDiv.querySelector('input[type="hidden"]').value = qrUrl;
            }

            Swal.fire({
                title: 'QR Generado',
                text: 'Código QR generado correctamente para: ' + nInterno,
                icon: 'success'
            });
        }

        // Load QR image from file
        function loadQRImage(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const row = input.closest('.form-row');
                const previewDiv = row.querySelector('#qr-preview-0') || row.querySelector('[id^="qr-preview-"]');

                if (previewDiv) {
                    previewDiv.classList.remove('hidden');
                    previewDiv.querySelector('img').src = e.target.result;
                    previewDiv.querySelector('input[type="hidden"]').value = e.target.result;
                }

                Swal.fire({
                    title: 'QR Cargado',
                    text: 'Imagen QR cargada correctamente',
                    icon: 'success'
                });
            };
            reader.readAsDataURL(file);
        }

        // Scan QR from physical code
        function scanRowQR(button) {
            currentRowElement = button.closest('.form-row');

            Swal.fire({
                title: 'Escanear Código QR',
                html: `
                    <div id="reader" class="w-full"></div>
                    <p class="mt-2 text-xs text-gray-500">Coloca el código QR dentro del recuadro</p>
                    <input type="text" id="manualQrInput"
                           class="w-full mt-3 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                           placeholder="O ingresa el código manualmente">
                    <button onclick="manualScanQR()"
                            class="mt-3 px-4 py-2 bg-blue-900 text-white rounded-lg text-sm w-full">
                        Buscar
                    </button>
                `,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                width: '400px',
                didOpen: () => {
                    initHtml5QrCodeScanner();
                },
                willClose: () => {
                    stopQrScanner();
                }
            });
        }

        function initHtml5QrCodeScanner() {
            if (typeof Html5QrcodeScanner !== 'undefined') {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader",
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    false
                );
                html5QrcodeScanner.render(onScanSuccessRow, onScanFailureRow);
            } else {
                // Fallback - show manual input
                document.getElementById('reader').innerHTML = '<p class="text-center text-gray-500 py-4">Cámara no disponible. Usa el campo manual abajo.</p>';
            }
        }

        function onScanSuccessRow(decodedText, decodedResult) {
            stopQrScanner();
            processScannedQR(decodedText);
        }

        function onScanFailureRow(error) {
            // Ignore scan failures
        }

        function manualScanQR() {
            const input = document.getElementById('manualQrInput');
            if (input && input.value) {
                stopQrScanner();
                Swal.close();
                processScannedQR(input.value);
            }
        }

        function processScannedQR(decodedText) {
            let nInterno = decodedText;

            // Extract n_interno from URL if scanned URL
            if (decodedText.includes('/bienes/qr/')) {
                const parts = decodedText.split('/bienes/qr/');
                nInterno = parts[1];
            }

            // Set N° Interno value in the row
            if (currentRowElement) {
                const nInternoInput = currentRowElement.querySelector('input[name="n_interno_bien[]"]');
                if (nInternoInput) {
                    nInternoInput.value = nInterno;
                }

                // Generate QR preview
                const qrUrl = 'https://quickchart.io/chart?cht=qr&chs=200x200&chl=' + encodeURIComponent('/bienes/qr/' + nInterno);
                const previewDiv = currentRowElement.querySelector('#qr-preview-0') || currentRowElement.querySelector('[id^="qr-preview-"]');

                if (previewDiv) {
                    previewDiv.classList.remove('hidden');
                    previewDiv.querySelector('img').src = qrUrl;
                    previewDiv.querySelector('input[type="hidden"]').value = qrUrl;
                }
            }

            Swal.fire({
                title: 'QR Escaneado',
                text: 'Código QR procesado: ' + nInterno,
                icon: 'success'
            });
        }

        function stopQrScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => console.error(error));
            }
        }

        // Remove QR preview
        function removeQRPreview(button) {
            const previewDiv = button.closest('[id^="qr-preview-"]');
            if (previewDiv) {
                previewDiv.classList.add('hidden');
                previewDiv.querySelector('img').src = '';
                previewDiv.querySelector('input[type="hidden"]').value = '';
            }
        }
    </script>
</x-app-layout>
@endcan

