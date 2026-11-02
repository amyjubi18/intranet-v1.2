@can('almacen_inv_edit')
@section('title', 'Editar')
@section('subtitle', 'Editar Almacenamiento de Consumibles')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Almacenamiento de Consumibles') }}
        </h2>
    </x-slot>

    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
    </x-slot>

    <div class="flex">


        <!-- Main Content -->
        <div class="w-full p-6">
            <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">

                <form action="{{route('administracion.almacenamiento_impresoras.update', $almacenamiento_impresora->id)}}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    @method('PUT')
                    <div class="rounded-lg">
                        <div class="space-y-4">





                            <!-- Fecha de Ingreso - Full width -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Fecha de Ingreso:</label>
                                <input type="date" name="fecha_ingreso" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('fecha_ingreso', $almacenamiento_impresora->fecha_ingreso)}}">
                                <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-2" />
                            </div>

                            <!-- Impresora with image on right -->
                            <div class="flex items-center gap-4 justify-between">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                    <select name="impresora_id" class="impresora-select w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($impresoras->sortBy('impresora') as $impresora)
                                        <option value="{{ $impresora->id }}" data-imagen="{{ asset('storage/' . $impresora->imagen) }}" @if ($almacenamiento_impresora->impresora_id == $impresora->id) selected @endif>{{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT) }} - {{ $impresora->impresora }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-none justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
                                    <img class="impresora-image w-32 h-32 object-cover rounded-lg " src="" alt="Imagen de Impresora" style="display: none;">
                                </div>
                            </div>

                            <!-- Dependent selects: Cartucho and Colore side by side, Fabricante below -->
                            <div class="dependent-section space-y-2 hidden">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
                                        <select name="cartucho_id" class="cartucho-select w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                            <option value="">Seleccione impresora primero</option>
                                        </select>
                                    </div>
                                    <div class="colore-div hidden">
                                        <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
                                        <select name="colore_id" class="colore-select w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                            <option value="">Seleccione consumible primero</option>
                                        </select>
                                    </div>
                                </div>


                            </div>

                            <!-- Proveedor  side by side -->
                            <div class="grid grid-cols-2 lg:grid-cols-2 gap-2">
                                <div class="fabricante-div hidden">
                                    <label class="block text-sm font-medium mb-1 text-black">Tipo de Impresora:</label>
                                    <select name="fabricante_id" class="fabricante-select w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione consumible primero</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                                    <select name="proveedore_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($proveedores as $proveedore)
                                        <option value="{{ $proveedore->id }}" @if ($almacenamiento_impresora->proveedore_id == $proveedore->id) selected @endif>{{ $proveedore->proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <!-- Número de Factura -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                                <input type="text" name="n_factura" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('n_factura', $almacenamiento_impresora->n_factura)}}">
                            </div>

                            <!-- Unidades -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Unidades:</label>
                                <input type="number" name="unidades" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('unidades', $almacenamiento_impresora->unidades)}}" min="1">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6 gap-2">
                            <x-primary-link href="{{ route('administracion.almacenamiento_impresoras.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                            {{ __('Regresar') }}
                            </x-primary-link>
                            <x-primary-button onsubmit="return route('administracion.almacenamiento_impresoras.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                            </x-primary-button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const impresoraSelect = document.querySelector('.impresora-select');
        const cartuchoSelect = document.querySelector('.cartucho-select');
        const coloreSelect = document.querySelector('.colore-select');
        const fabricanteSelect = document.querySelector('.fabricante-select');
        const imagenPreview = document.querySelector('.impresora-image');
        const dependentSection = document.querySelector('.dependent-section');
        const coloreDiv = document.querySelector('.colore-div');
        const fabricanteDiv = document.querySelector('.fabricante-div');

        const cartuchos = @json($cartuchos);
        const colores = @json($colores);
        const fabricantes = @json($fabricantes);

        // Function to update current record in sidebar
        function updateCurrentRecord() {
            const currentRecordDiv = document.getElementById('current-record');
            const fecha = document.querySelector('input[name="fecha_ingreso"]').value;
            const impresora = impresoraSelect.options[impresoraSelect.selectedIndex]?.text || '';
            const unidades = document.querySelector('input[name="unidades"]').value;
            const nFactura = document.querySelector('input[name="n_factura"]').value;

            currentRecordDiv.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="font-bold mb-2">Registro Actual</h3>
                    <p><strong>Fecha de Ingreso:</strong> ${fecha}</p>
                    <p><strong>Impresora:</strong> ${impresora}</p>
                    <p><strong>Cantidad:</strong> ${unidades}</p>
                    <p><strong>Nº de Factura:</strong> ${nFactura}</p>
                </div>
            `;
        }

        // Initial load: populate dependents and show image
        const initialImpresoraId = '{{ $almacenamiento_impresora->impresora_id }}';
        const initialCartuchoId = '{{ $almacenamiento_impresora->cartucho_id }}';
        const initialColoreId = '{{ $almacenamiento_impresora->colore_id }}';
        const initialFabricanteId = '{{ $almacenamiento_impresora->fabricante_id }}';

        if (initialImpresoraId) {
            const selectedOption = impresoraSelect.querySelector(`option[value="${initialImpresoraId}"]`);
            const imagenSrc = selectedOption?.getAttribute('data-imagen');
            if (imagenSrc && imagenSrc !== '') {
                imagenPreview.src = imagenSrc;
                imagenPreview.style.display = 'block';
            }

            // Show dependent section and populate
            dependentSection.classList.remove('hidden');
            const filteredCartuchos = cartuchos.filter(cartucho => cartucho.impresora_id == initialImpresoraId);
            filteredCartuchos.forEach(cartucho => {
                const option = document.createElement('option');
                option.value = cartucho.id;
                option.textContent = cartucho.cartucho + ' - (' + (cartucho.colore ? cartucho.colore.colore : '') + ')';
                cartuchoSelect.appendChild(option);
            });
            cartuchoSelect.value = initialCartuchoId;

            // Populate colore and fabricante
            if (initialCartuchoId) {
                coloreDiv.classList.remove('hidden');
                fabricanteDiv.classList.remove('hidden');
                const selectedCartucho = cartuchos.find(cartucho => cartucho.id == initialCartuchoId);
                if (selectedCartucho) {
                    if (selectedCartucho.colore) {
                        const option = document.createElement('option');
                        option.value = selectedCartucho.colore.id;
                        option.textContent = selectedCartucho.colore.colore;
                        coloreSelect.appendChild(option);
                        coloreSelect.value = initialColoreId;
                    }
                    if (selectedCartucho.fabricante) {
                        const option = document.createElement('option');
                        option.value = selectedCartucho.fabricante.id;
                        option.textContent = selectedCartucho.fabricante.fabricante;
                        fabricanteSelect.appendChild(option);
                        fabricanteSelect.value = initialFabricanteId;
                    }
                }
            }
        }

        // Update current record on load
        updateCurrentRecord();

        // For impresora -> show image and filter cartuchos
        if (impresoraSelect) {
            impresoraSelect.addEventListener('change', function() {
                const selectedImpresoraId = this.value;
                const selectedOption = this.options[this.selectedIndex];
                const imagenSrc = selectedOption.getAttribute('data-imagen');
                if (imagenSrc && imagenSrc !== '') {
                    imagenPreview.src = imagenSrc;
                    imagenPreview.style.display = 'block';
                } else {
                    imagenPreview.style.display = 'none';
                }

                // Filter cartuchos by impresora
                cartuchoSelect.innerHTML = '<option value="">Seleccione</option>';
                coloreSelect.innerHTML = '<option value="">Seleccione cartucho primero</option>';
                fabricanteSelect.innerHTML = '<option value="">Seleccione cartucho primero</option>';

                if (selectedImpresoraId) {
                    dependentSection.classList.remove('hidden');
                    const filteredCartuchos = cartuchos.filter(cartucho => cartucho.impresora_id == selectedImpresoraId);
                    filteredCartuchos.forEach(cartucho => {
                        const option = document.createElement('option');
                        option.value = cartucho.id;
                        option.textContent = cartucho.cartucho + ' - (' + (cartucho.colore ? cartucho.colore.colore : '') + ')';
                        cartuchoSelect.appendChild(option);
                    });
                } else {
                    dependentSection.classList.add('hidden');
                    coloreDiv.classList.add('hidden');
                    fabricanteDiv.classList.add('hidden');
                }
                updateCurrentRecord();
            });
        }

        // For cartucho -> filter colores and fabricantes
        if (cartuchoSelect) {
            cartuchoSelect.addEventListener('change', function() {
                const selectedCartuchoId = this.value;

                coloreSelect.innerHTML = '<option value="">Seleccione</option>';
                fabricanteSelect.innerHTML = '<option value="">Seleccione</option>';

                if (selectedCartuchoId) {
                    coloreDiv.classList.remove('hidden');
                    fabricanteDiv.classList.remove('hidden');
                    const selectedCartucho = cartuchos.find(cartucho => cartucho.id == selectedCartuchoId);
                    if (selectedCartucho) {
                        // Set colore
                        if (selectedCartucho.colore) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.colore.id;
                            option.textContent = selectedCartucho.colore.colore;
                            coloreSelect.appendChild(option);
                            coloreSelect.value = selectedCartucho.colore.id;
                        }

                        // Set fabricante
                        if (selectedCartucho.fabricante) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.fabricante.id;
                            option.textContent = selectedCartucho.fabricante.fabricante;
                            fabricanteSelect.appendChild(option);
                            fabricanteSelect.value = selectedCartucho.fabricante.id;
                        }
                    }
                } else {
                    coloreDiv.classList.add('hidden');
                    fabricanteDiv.classList.add('hidden');
                }
                updateCurrentRecord();
            });
        }

        // Update on input changes
        document.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', updateCurrentRecord);
        });
    });
    </script>
</x-app-layout>
@endcan
