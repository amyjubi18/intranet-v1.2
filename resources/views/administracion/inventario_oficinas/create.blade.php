@can('inventarioofic_create')
@section('title', 'Registrar')
@section('subtitle', 'Registrar Inventario de Oficinas')
<x-app-layout>

    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
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

    /* Ocultar el template */
    #row-template {
      display: none;
    }
    </style>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Template oculto para clonar filas -->
        <template id="row-template">
            <div class="form-row bg-white/20 p-4 shadow-lg rounded-lg">
                <div class="space-y-4">
                    <!-- Fecha de Ingreso - Full width -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-black">Fecha de Factura:</label>
                        <input type="date" name="fecha_ingreso[]" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required>
                    </div>

                    <!-- Articulo and Unidad de Medida side by side -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-black">Artículo:</label>
                            <select name="articulo_id[]" class="articulo-select border-none bg-blue-200 w-full px-3 py-2 rounded-lg focus:ring-blue-900" required>
                                <option value="">Seleccione</option>
                                @foreach ($articulos->sortBy('articulo') as $articulo)
                                <option value="{{ $articulo->id }}">{{ str_pad($articulo->id, 4, '0', STR_PAD_LEFT) }} - {{ $articulo->articulo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="unidad-medida-div">
                            <label class="block text-sm font-medium mb-1 text-black">Unidad de Medida:</label>
                            <span class="unidad-medida-display block border-none bg-blue-100 w-full px-3 py-2 rounded-lg text-gray-700">Seleccione artículo primero</span>
                            <input type="hidden" name="unidad_medida_id[]" class="unidad-medida-hidden bg-blue-100 w-full px-3 py-2 rounded-lg">
                        </div>
                    </div>

                    <!-- Proveedor -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                        <select name="proveedore_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900" required>
                            <option value="">Seleccione</option>
                            @foreach ($proveedores as $proveedore)
                            <option value="{{ $proveedore->id }}">{{ $proveedore->proveedor }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ubicación -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-black">Ubicación:</label>
                        <select name="ubicacion_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900">
                            <option value="">Seleccione (Opcional)</option>
                            @foreach ($ubicacions as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->ubicacion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cantidad and Número de Factura side by side -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-black">Cantidad:</label>
                            <input type="number" name="unidades[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                            <input type="text" name="n_factura[]" class="w-full px-3 border-none bg-blue-100 py-2 rounded-lg focus:ring-blue-900" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="remove-row flex mt-2 bg-red-500 hover:bg-red-600 text-white px-3 py-3 rounded-lg transition-all"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg> Remover</button>
            </div>
        </template>

        <!-- Custom Sidebar for this page -->
        <div class="w-full lg:col-span-1 p-4 h-auto bg-white shadow-lg rounded-lg">
            <h2 class="text-xl font-bold mb-4 text-center text-blue-950">Visualizar Registros</h2>
            <div id="pending-records" class="grid grid-cols-1 gap-4 mt-4">
                <!-- Pending records will be added here -->
            </div>
            <div id="no-records" class="text-center text-gray-500 mt-4">
                No hay registros pendientes
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full lg:col-span-3 p-6">
            <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
                <form id="batch-form" action="{{ route('administracion.inventario_oficinas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    <div id="form-rows" class="space-y-4">
                        <!-- Initial row -->
                        <div class="form-row bg-white/20 p-4 shadow-lg rounded-lg">
                            <div class="space-y-4">
                                <!-- Fecha de Ingreso - Full width -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Fecha de Factura:</label>
                                    <input type="date" name="fecha_ingreso[]" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required>
                                </div>

                                <!-- Articulo and Unidad de Medida side by side -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Artículo:</label>
                                        <select name="articulo_id[]" class="articulo-select border-none bg-blue-200 w-full px-3 py-2 rounded-lg focus:ring-blue-900" required>
                                            <option value="">Seleccione</option>
                                            @foreach ($articulos->sortBy('articulo') as $articulo)
                                            <option value="{{ $articulo->id }}">{{ str_pad($articulo->id, 4, '0', STR_PAD_LEFT) }} - {{ $articulo->articulo }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="unidad-medida-div">
                                        <label class="block text-sm font-medium mb-1 text-black">Unidad de Medida:</label>
                                        <span class="unidad-medida-display block border-none bg-blue-100 w-full px-3 py-2 rounded-lg text-gray-700">Seleccione artículo primero</span>
                                        <input type="hidden" name="unidad_medida_id[]" class="unidad-medida-hidden bg-blue-100 w-full px-3 py-2 rounded-lg">
                                    </div>
                                </div>

                                <!-- Proveedor -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                                    <select name="proveedore_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($proveedores as $proveedore)
                                        <option value="{{ $proveedore->id }}">{{ $proveedore->proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Ubicación -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Ubicación:</label>
                                    <select name="ubicacion_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900">
                                        <option value="">Seleccione (Opcional)</option>
                                        @foreach ($ubicacions as $ubicacion)
                                        <option value="{{ $ubicacion->id }}">{{ $ubicacion->ubicacion }}</option>
                                        @endforeach
                                    </select>
                                </div>



                                <!-- Cantidad and Número de Factura side by side -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Cantidad:</label>
                                        <input type="number" name="unidades[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                                        <input type="text" name="n_factura[]" class="w-full px-3 border-none bg-blue-100 py-2 rounded-lg focus:ring-blue-900" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="remove-row flex mt-2 bg-red-500 hover:bg-red-600 text-white px-3 py-3 rounded-lg transition-all hidden"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg> Remover</button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6 gap-4">
                        <button type="button" id="add-row" class="btn-save"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>
                            Agregar Registro
                        </button>
                            <x-primary-link href="{{ route('administracion.inventario_oficinas.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                                {{ __('Regresar') }}
                            </x-primary-link>
                            <x-primary-button  onsubmit="return route('administracion.inventario_oficinas.index')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                            </x-primary-button>

                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Data from Laravel
        const articulos = @json($articulos);
        const unidades_medidas = @json($unidad_medidas);


        let rowCount = 1;
        const tomSelectInstances = [];

        // Initialize Tom Select for articulo selects
        function initTomSelect(selectElement) {
            if (selectElement.tomselect) return selectElement.tomselect;

            return new TomSelect(selectElement, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Buscar artículo...",
                onChange: function(value) {
                    const row = selectElement.closest('.form-row');
                    if (row) {
                        handleArticuloChange(row, value);
                    }
                },
                plugins: ['dropdown_input'],
            });
        }

        // Handle articulo change
        function handleArticuloChange(row, selectedArticuloId) {
            const unidadMedidaDisplay = row.querySelector('.unidad-medida-display');
            const unidadMedidaHidden = row.querySelector('.unidad-medida-hidden');

            if (selectedArticuloId) {
                const selectedArticulo = articulos.find(articulo => articulo.id == selectedArticuloId);
                if (selectedArticulo && selectedArticulo.unidad_medida_id) {
                    const unidadMedida = unidades_medidas.find(um => um.id == selectedArticulo.unidad_medida_id);
                    if (unidadMedida) {
                        unidadMedidaDisplay.textContent = unidadMedida.unidad_medida;
                        unidadMedidaHidden.value = unidadMedida.id;
                    } else {
                        unidadMedidaDisplay.textContent = 'Unidad de medida no encontrada';
                        unidadMedidaHidden.value = '';
                    }
                } else {
                    unidadMedidaDisplay.textContent = 'No tiene unidad de medida';
                    unidadMedidaHidden.value = '';
                }
            } else {
                unidadMedidaDisplay.textContent = 'Seleccione artículo primero';
                unidadMedidaHidden.value = '';
            }
            updatePendingRecords();
        }

        // Update pending records in sidebar
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
                const fecha = row.querySelector('input[name="fecha_ingreso[]"]').value || 'No ingresada';
                const articuloSelect = row.querySelector('.articulo-select');
                const articuloText = articuloSelect?.tomselect?.getOption(articuloSelect.value)?.textContent || articuloSelect?.selectedOptions[0]?.text || 'No seleccionado';
                const unidadMedida = row.querySelector('.unidad-medida-display').textContent || 'No seleccionada';
                const proveedor = row.querySelector('select[name="proveedore_id[]"]').selectedOptions[0]?.text || 'No seleccionado';
                const ubicacion = row.querySelector('select[name="ubicacion_id[]"]').selectedOptions[0]?.text || 'No seleccionada';

                const unidades = row.querySelector('input[name="unidades[]"]').value || '0';
                const factura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = `
                    <h3 class="font-bold">Registro ${index + 1}: ${articuloText}</h3>
                    <p>Fecha: ${fecha}</p>
                    <p>Unidad de Medida: ${unidadMedida}</p>
                    <p>Proveedor: ${proveedor}</p>
                    <p>Ubicación: ${ubicacion}</p>
                    <p>Cantidad: ${unidades}</p>
                    <p>Factura: ${factura}</p>
                `;
                pendingRecordsDiv.appendChild(recordDiv);
            });
        }

        // Add event listeners to inputs
        function addInputListeners(row) {
            row.querySelectorAll('input[name="fecha_ingreso[]"], input[name="unidades[]"], input[name="n_factura[]"]').forEach(el => {
                el.addEventListener('input', updatePendingRecords);
            });
            row.querySelectorAll('select[name="proveedore_id[]"], select[name="ubicacion_id[]"]').forEach(el => {
                el.addEventListener('change', updatePendingRecords);
            });
        }

        // Add row function
        document.getElementById('add-row').addEventListener('click', () => {
            const formRows = document.getElementById('form-rows');

            // Usar el template en lugar de clonar la fila existente
            const template = document.getElementById('row-template');
            const newRow = template.content.cloneNode(true);

            // La fila ya viene limpia del template, solo inicializar Tom Select
            const clonedSelect = newRow.querySelector('.articulo-select');

            // Initialize Tom Select para la nueva fila
            initTomSelect(clonedSelect);

            // Add input listeners
            addInputListeners(newRow);

            formRows.appendChild(newRow);
            const hr = document.createElement('hr');
            hr.className = 'my-4';
            formRows.appendChild(hr);
            rowCount++;
            updatePendingRecords();
        });

        // Remove row
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
                const button = e.target.classList.contains('remove-row') ? e.target : e.target.closest('.remove-row');
                const row = button.closest('.form-row');
                const hr = row.nextElementSibling;

                // Destroy Tom Select instance if exists
                const select = row.querySelector('.articulo-select');
                if (select?.tomselect) {
                    select.tomselect.destroy();
                }

                if (hr && hr.tagName === 'HR') hr.remove();
                row.remove();
                rowCount--;
                updatePendingRecords();
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            const firstRow = document.querySelector('.form-row');
            if (firstRow) {
                const firstSelect = firstRow.querySelector('.articulo-select');
                if (firstSelect) {
                    initTomSelect(firstSelect);
                }
                addInputListeners(firstRow);
                updatePendingRecords();
            }
        });

    </script>
</x-app-layout>
@endcan

