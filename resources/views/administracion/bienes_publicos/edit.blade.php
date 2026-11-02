@can('bienes_edit')
@section('title', 'Editar')
@section('subtitle', 'Editar Bien Público')
<x-app-layout>

    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    </x-slot>

    <div class="w-full p-6">
        <div class="max-w-4xl p-6 mx-auto bg-white rounded-lg shadow-lg">
            <form action="{{ route('administracion.bienes_publicos.update', $biene->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 rounded-lg">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Sede:</label>
                            <input type="text" value="{{ old('sede', $biene->sede) }}" name="sede" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Fecha de Adquisición:</label>
                            <input type="date" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $biene->fecha_adquisicion) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Unidad Administrativa:</label>
                            <select id="unidad_administrativa" name="unidad_administrativa_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($unidad_administrativas as $ua)
                                <option value="{{ $ua->id }}" {{ $ua->id == $biene->unidad_administrativa_id ? 'selected' : '' }}>{{ $ua->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Usuario Administrativo:</label>
                            <select id="usuario_administrativo" name="usuario_administrativo_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($usuario_administrativos as $ua)
                                <option value="{{ $ua->id }}" {{ $ua->id == $biene->usuario_administrativo_id ? 'selected' : '' }}>{{ $ua->usuario_administrativo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-black">Usuario de Uso:</label>
                        <select id="usuario_de_uso" name="usuario_de_uso_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($usuario_de_usos as $uu)
                            <option value="{{ $uu->id }}" {{ $uu->id == $biene->usuario_de_uso_id ? 'selected' : '' }}>{{ $uu->usuario_de_uso }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-black">N° Interno del Bien:</label>
                        <input type="text" name="n_interno_bien" value="{{ old('n_interno_bien', $biene->n_interno_bien) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-black">Descripción:</label>
                        <select id="descripcion" name="descripcion" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg descripcion-select campo-select" required>
                            <option value="">Seleccione o escriba una nueva...</option>
                            @if(isset($descripciones) && count($descripciones) > 0)
                                @foreach($descripciones as $desc)
                                    <option value="{{ $desc }}" {{ $desc == $biene->descripcion ? 'selected' : '' }}>{{ $desc }}</option>
                                @endforeach
                            @endif
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Puede seleccionar una descripción existente O escribir una nueva</p>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm font-medium text-black">Forma de Adquisición:</label>
                        <select name="forma_adquisicion_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                            <option value="">Seleccione</option>
                            @foreach ($forma_adquisiciones as $fa)
                            <option value="{{ $fa->id }}" {{ $fa->id == $biene->forma_adquisicion_id ? 'selected' : '' }}>{{ $fa->forma_adquisicion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-3">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">N° Factura:</label>
                            <input type="text" name="n_factura" value="{{ old('n_factura', $biene->n_factura) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Valor Factura (Bs.):</label>
                            <input type="number" step="0.01" name="valor_factura" value="{{ old('valor_factura', isset($biene->valor_factura) && $biene->valor_factura !== '' ? $biene->valor_factura : '0.00') }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Moneda:</label>
                            <select name="moneda" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="Bs." {{ $biene->moneda == 'Bs.' ? 'selected' : '' }}>Bolívar (Bs.)</option>
                                <option value="USD" {{ $biene->moneda == 'USD' ? 'selected' : '' }}>Dólar (USD)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Estado del Uso:</label>
                            <select name="estado_del_uso_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($estado_del_usos as $eu)
                                <option value="{{ $eu->id }}" {{ $eu->id == $biene->estado_del_uso_id ? 'selected' : '' }}>{{ $eu->estado_del_uso }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Condición Física:</label>
                            <select name="condicion_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($condiciones as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $biene->condicion_id ? 'selected' : '' }}>{{ $c->condicion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Marca:</label>
                            <input type="text" name="marca" value="{{ old('marca', $biene->marca) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Modelo:</label>
                            <input type="text" name="modelo" value="{{ old('modelo', $biene->modelo) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Color:</label>
                            <select name="color_id" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($colors as $color)
                                <option value="{{ $color->id }}" {{ $color->id == $biene->color_id ? 'selected' : '' }}>{{ $color->color }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Serial:</label>
                            <input type="text" name="serial" value="{{ old('serial', $biene->serial) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="p-4 mt-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start gap-4">
                            <!-- Current QR Preview -->
                            <div class="flex-shrink-0">
                                @if($biene->n_interno_bien)
                                    <img id="current-qr-image"
                                         src="https://quickchart.io/chart?cht=qr&chs=150x150&chl={{ urlencode($biene->qr_code ?: url('/bienes/qr/' . $biene->n_interno_bien)) }}"
                                         alt="Código QR"
                                         class="w-24 h-24 border-2 border-blue-300 rounded-lg cursor-pointer hover:scale-110 transition-transform"
                                         onclick="openQrModal()">
                                @else
                                    <div id="current-qr-image" class="w-24 h-24 bg-gray-200 border-2 border-gray-300 rounded-lg flex items-center justify-center">
                                        <span class="text-3xl">❌</span>
                                    </div>
                                @endif
                                <!-- Hidden input to store QR code -->
                                <input type="hidden" name="qr_code" id="qr_code_input" value="{{ $biene->qr_code ?? '' }}">
                            </div>

                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-blue-800">Código QR del Bien</h4>
                                <p class="mt-1 text-xs text-blue-700">
                                    Genera un código QR basado en el N° Interno o carga una imagen QR existente.
                                </p>

                                <div class="mt-3">
                                    <label class="block mb-1 text-xs font-medium text-blue-800">Opciones de QR:</label>
                                    <div class="flex flex-wrap gap-2">
                                        <!-- Botón generar QR -->
                                        <button type="button" onclick="generateEditQR()" class="px-3 py-2 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                                            🔄 Generar QR Automático
                                        </button>

                                        <!-- Input cargar QR -->
                                        <label class="px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg cursor-pointer hover:bg-blue-700">
                                            📤 Cargar QR Existente
                                            <input type="file" accept="image/*" class="hidden" onchange="loadEditQR(this)">
                                        </label>

                                        <!-- Botón escanear QR -->
                                        <button type="button" onclick="scanEditQR()" class="px-3 py-2 text-xs font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                                            📷 Escanear QR Físico
                                        </button>
                                    </div>
                                </div>

                                @if($biene->n_interno_bien)
                                    <p class="mt-2 text-xs text-blue-600">
                                        <strong>N° Interno:</strong> {{ $biene->n_interno_bien }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <script>
                        // Global variables for QR scanner
                        let html5QrcodeScanner = null;

                        function openQrModal() {
                            const nInterno = '{{ $biene->n_interno_bien }}';
                            const descripcion = '{{ $biene->descripcion }}';
                            const qrImageUrl = 'https://quickchart.io/chart?cht=qr&chs=300x300&chl=' + encodeURIComponent('/bienes/qr/' + nInterno);

                            Swal.fire({
                                title: 'Código QR del Bien',
                                html: '<div class="text-center">' +
                                    '<p class="mb-2 text-sm font-semibold text-gray-700">' + descripcion + '</p>' +
                                    '<p class="mb-4 text-xs text-gray-500">N° Interno: ' + nInterno + '</p>' +
                                    '<img src="' + qrImageUrl + '" alt="Código QR" class="w-48 h-48 mx-auto border-2 border-gray-300 rounded-lg">' +
                                    '</div>',
                                showCloseButton: true,
                                showConfirmButton: true,
                                confirmButtonText: 'Descargar QR',
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar',
                                width: '400px'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const link = document.createElement('a');
                                    link.href = qrImageUrl;
                                    link.download = 'QR_Bien_' + nInterno + '.png';
                                    link.click();
                                }
                            });
                        }

                        // Generate QR for edit form
                        function generateEditQR() {
                            const nInterno = document.querySelector('input[name="n_interno_bien"]').value;

                            if (!nInterno) {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Debe tener un N° Interno del bien',
                                    icon: 'error'
                                });
                                return;
                            }

                            const qrUrl = 'https://quickchart.io/chart?cht=qr&chs=150x150&chl=' + encodeURIComponent('/bienes/qr/' + nInterno);
                            const imgElement = document.getElementById('current-qr-image');
                            const qrInput = document.getElementById('qr_code_input');

                            if (imgElement) {
                                imgElement.src = qrUrl;
                                imgElement.classList.remove('hidden');
                                imgElement.onclick = openQrModal;
                            }

                            if (qrInput) {
                                qrInput.value = '/bienes/qr/' + nInterno;
                            }

                            Swal.fire({
                                title: 'QR Generado',
                                text: 'Código QR generado correctamente',
                                icon: 'success'
                            });
                        }

                        // Load QR image from file
                        function loadEditQR(input) {
                            const file = input.files[0];
                            if (!file) return;

                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const imgElement = document.getElementById('current-qr-image');
                                const qrInput = document.getElementById('qr_code_input');

                                if (imgElement) {
                                    imgElement.src = e.target.result;
                                    imgElement.classList.remove('hidden');
                                    imgElement.onclick = function() {
                                        Swal.fire({
                                            title: 'Código QR Cargado',
                                            html: '<img src="' + e.target.result + '" class="w-48 h-48 mx-auto border-2 border-gray-300 rounded-lg">',
                                            showCloseButton: true,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Descargar',
                                            showCancelButton: true,
                                            cancelButtonText: 'Cerrar',
                                            width: '400px'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                const link = document.createElement('a');
                                                link.href = e.target.result;
                                                link.download = 'QR_Bien_{{ $biene->n_interno_bien }}.png';
                                                link.click();
                                            }
                                        });
                                    };
                                }

                                if (qrInput) {
                                    qrInput.value = e.target.result;
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
                        function scanEditQR() {
                            Swal.fire({
                                title: 'Escanear Código QR',
                                html: `
                                    <div id="reader" class="w-full"></div>
                                    <p class="mt-2 text-xs text-gray-500">Coloca el código QR dentro del recuadro</p>
                                    <input type="text" id="manualQrInputEdit"
                                           class="w-full mt-3 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                           placeholder="O ingresa el código manualmente">
                                    <button onclick="manualScanEditQR()"
                                            class="mt-3 px-4 py-2 bg-blue-900 text-white rounded-lg text-sm w-full">
                                        Buscar
                                    </button>
                                `,
                                showConfirmButton: false,
                                showCancelButton: true,
                                cancelButtonText: 'Cerrar',
                                width: '400px',
                                didOpen: () => {
                                    initEditQrScanner();
                                },
                                willClose: () => {
                                    stopQrScanner();
                                }
                            });
                        }

                        function initEditQrScanner() {
                            if (typeof Html5QrcodeScanner !== 'undefined') {
                                html5QrcodeScanner = new Html5QrcodeScanner(
                                    "reader",
                                    { fps: 10, qrbox: { width: 250, height: 250 } },
                                    false
                                );
                                html5QrcodeScanner.render(onScanSuccessEdit, onScanFailureEdit);
                            } else {
                                document.getElementById('reader').innerHTML = '<p class="text-center text-gray-500 py-4">Cámara no disponible. Usa el campo manual abajo.</p>';
                            }
                        }

                        function onScanSuccessEdit(decodedText, decodedResult) {
                            stopQrScanner();
                            processScannedQREdit(decodedText);
                        }

                        function onScanFailureEdit(error) {
                            // Ignore scan failures
                        }

                        function manualScanEditQR() {
                            const input = document.getElementById('manualQrInputEdit');
                            if (input && input.value) {
                                stopQrScanner();
                                Swal.close();
                                processScannedQREdit(input.value);
                            }
                        }

                        function processScannedQREdit(decodedText) {
                            let nInterno = decodedText;

                            if (decodedText.includes('/bienes/qr/')) {
                                const parts = decodedText.split('/bienes/qr/');
                                nInterno = parts[1];
                            }

                            // Set N° Interno value
                            const nInternoInput = document.querySelector('input[name="n_interno_bien"]');
                            const qrInput = document.getElementById('qr_code_input');

                            if (nInternoInput) {
                                nInternoInput.value = nInterno;
                            }

                            // Update QR image
                            const qrUrl = 'https://quickchart.io/chart?cht=qr&chs=150x150&chl=' + encodeURIComponent('/bienes/qr/' + nInterno);
                            const imgElement = document.getElementById('current-qr-image');

                            if (imgElement) {
                                imgElement.src = qrUrl;
                            }

                            if (qrInput) {
                                qrInput.value = '/bienes/qr/' + nInterno;
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
                    </script>

                    <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Comentario:</label>
                            <input type="text" name="comentario" value="{{ old('comentario', $biene->comentario) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-black">Fecha Comentario:</label>
                            <input type="date" name="fecha_comentario" value="{{ old('fecha_comentario', $biene->fecha_comentario) }}" class="w-full px-3 py-2 bg-blue-100 border-none rounded-lg campo-texto" required>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <x-primary-link href="{{ route('administracion.bienes_publicos.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95z"></path></svg>
                        Regresar
                    </x-primary-link>
                    <x-primary-button type="submit" class="btn-save">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg>
                        Guardar
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar Tom Select para descripción
            const descSelect = document.getElementById('descripcion');
            if (descSelect && !descSelect.tomselect) {
                new TomSelect(descSelect, {
                    create: true,
                    maxItems: 1,
                    createOnBlur: true,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: "Seleccione o escriba una nueva...",
                    plugins: ['dropdown_input']
                });
            }
        });
    </script>
</x-app-layout>
@endcan

