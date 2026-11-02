@can('almacen_inv_create')
@section('title', 'Registrar')
@section('subtitle', 'Registrar Almacenamiento de Impresoras')
<x-app-layout>

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

    <x-slot name="scripts">
        <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
    </x-slot>

    <div class="flex">
        <!-- Custom Sidebar for this page -->
        <div class="w-1/4 p-4   h-auto opacity-100">
            <h2 class="text-xl font-bold mb-4 text-center text-blue-950">Visualizar Registros</h2>
            <div id="pending-records" class="grid grid-cols-1 gap-4 mt-4">
                <!-- Pending records will be added here -->
            </div>
            <div id="no-records" class="text-center text-gray-500 mt-4">
                No hay registros pendientes
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-3/4 p-6">
            <div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
                <!-- Selector de Modo de Registro -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-center">Seleccione el Modo de Registro</h3>
                    <div class="flex justify-center gap-6">
                        <label class="flex items-center">
                            <input type="radio" name="registro_mode" value="consumible" checked class="mr-2">
                            <span class="text-sm font-medium">Registrar por Consumible</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="registro_mode" value="impresora" class="mr-2">
                            <span class="text-sm font-medium">Registrar por Impresora</span>
                        </label>

                    </div>
                </div>

                <!-- Formulario por Impresora -->
                <div id="form-impresora" class="form-container hidden">
                    <form id="batch-form-impresora" action="{{ route('administracion.almacenamiento_impresoras.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    <div id="form-rows" class="space-y-4">
                        <!-- Initial row -->
                        <div class="form-row bg-white/20 p-4 shadow-lg rounded-lg">
                            <div class="space-y-4">
                                <!-- Fecha de Ingreso - Full width -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black ">Fecha de Factura:</label>
                                    <input type="date" name="fecha_ingreso[]" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 " required>
                                </div>

                                <!-- Impresora with image on right -->
                                <div class="flex items-center gap-4 justify-between">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                        <select name="impresora_id[]" class="impresora-select border-none bg-blue-100  w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione</option>
                                            @foreach ($impresoras->sortBy('impresora') as $impresora)
                                            <option value="{{ $impresora->id }}" data-imagen="{{ asset('storage/' . $impresora->imagen) }}">{{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT) }} - {{ $impresora->impresora }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-none justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
                                        <img class="impresora-image w-32 h-32 object-cover rounded-lg" src="" alt="Imagen de Impresora" style="display: none;">
                                    </div>
                                </div>



                                <!-- Dependent selects: Cartucho and Colore side by side, Fabricante below -->
                                <div class="dependent-section space-y-2 hidden">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
                                            <select name="cartucho_id[]" class="cartucho-select border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione impresora primero</option>
                                            </select>
                                        </div>
                                        <div class="colore-div hidden">
                                            <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
                                            <select name="colore_id[]" class="colore-select border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione consumible primero</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="fabricante-div hidden">
                                        <label class="block text-sm  font-medium mb-1 text-black">Tipo de Impresora:</label>
                                        <select name="fabricante_id[]" class="fabricante-select border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione consumible primero</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Proveedor -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                                    <select name="proveedore_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                        <option value="">Seleccione</option>
                                        @foreach ($proveedores as $proveedore)
                                        <option value="{{ $proveedore->id }}">{{ $proveedore->proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <!-- Cantidad and Número de Factura side by side -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Cantidad:</label>
                                        <input type="number" name="unidades[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                                        <input type="text" name="n_factura[]" class="w-full px-3 border-none bg-blue-100 py-2 rounded-lg focus:ring-blue-900 " required>
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
                            <x-primary-link href="{{ route('administracion.almacenamiento_impresoras.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                                {{ __('Regresar') }}
                            </x-primary-link>
                            <x-primary-button  onsubmit="return route('administracion.almacenamiento_impresoras.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                        </x-primary-button>
                    </div>
                </form>
                </div>

                <!-- Formulario por Consumible -->
                <div id="form-consumible" class="form-container hidden">
                    <form id="batch-form-consumible" action="{{ route('administracion.almacenamiento_impresoras.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                        @csrf
                        <div id="form-rows-consumible" class="space-y-4">
                            <!-- Initial row -->
                            <div class="form-row-consumible bg-white/20 p-4 shadow-lg rounded-lg">
                                <div class="space-y-4">
                                    <!-- Fecha de Ingreso - Full width -->
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black ">Fecha de Factura:</label>
                                        <input type="date" name="fecha_ingreso[]" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 " required>
                                    </div>

                                    <!-- Cartucho first, then dependent selects -->
                                    <div class="dependent-section-consumible space-y-2">
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
                                                <select name="cartucho_id[]" class="cartucho-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                    <option value="">Seleccione</option>
                                                    @foreach ($cartuchos as $cartucho)
                                                    <option value="{{ $cartucho->id }}">{{ $cartucho->cartucho }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="colore-div-consumible hidden">
                                                <label class="block text-sm font-medium mb-1 text-black">Tipo de consumible:</label>
                                                <select name="colore_id[]" class="colore-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                    <option value="">Seleccione consumible primero</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="fabricante-div-consumible hidden">
                                            <label class="block text-sm font-medium mb-1 text-black">Tipo de Impresora:</label>
                                            <select name="fabricante_id[]" class="fabricante-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione consumible primero</option>
                                            </select>
                                        </div>
                                        <div class="impresora-div-consumible hidden">
                                            <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                            <div class="flex items-center gap-4 justify-between">
                                                <div class="flex-1">
                                                    <select name="impresora_id[]" class="impresora-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                        <option value="">Seleccione consumible primero</option>
                                                        @foreach ($impresoras->sortBy('impresora') as $impresora)
                                                        <option value="{{ $impresora->id }}" data-imagen="{{ asset('storage/' . $impresora->imagen) }}">{{ str_pad($impresora->id, 4, '0', STR_PAD_LEFT) }} -  {{ $impresora->impresora }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex-none justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
                                                    <img class="impresora-image w-32 h-32 object-cover rounded-lg" src="" alt="Imagen de Impresora" style="display: none;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Proveedor -->
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                                        <select name="proveedore_id[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione</option>
                                            @foreach ($proveedores as $proveedore)
                                            <option value="{{ $proveedore->id }}">{{ $proveedore->proveedor }}</option>
                                            @endforeach
                                        </select>
                                    </div>



                                    <!-- Cantidad and Número de Factura side by side -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-black">Cantidad:</label>
                                            <input type="number" name="unidades[]" class="w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                                            <input type="text" name="n_factura[]" class="w-full px-3 border-none bg-blue-100 py-2 rounded-lg focus:ring-blue-900 " required>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="remove-row-consumible flex mt-2 bg-red-500 hover:bg-red-600 text-white px-3 py-3 rounded-lg transition-all hidden"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg> Remover</button>
                            </div>

                        <div class="flex justify-between items-center mt-6 gap-4">
                            <button type="button" id="add-row-consumible" class="btn-save"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>
                                Agregar Registro
                            </button>
                                <x-primary-link href="{{ route('administracion.almacenamiento_impresoras.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                                {{ __('Regresar') }}
                            </x-primary-link>
                                <x-primary-button class=" btn-save" onsubmit="return route('administracion.almacenamiento_impresoras.index')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
        </div>

    <script>
        // Data from Laravel
        const cartuchos = @json($cartuchos);
        const colores = @json($colores);
        const fabricantes = @json($fabricantes);
        const impresoras = @json($impresoras);
        const storageUrl = '{{ asset("storage/") }}';

        let rowCount = 1;
        let rowCountConsumible = 1;

        // Initialize Tom Select helper function
        function initTomSelect(selectElement, options = {}) {
            if (selectElement.tomselect) return selectElement.tomselect;

            return new TomSelect(selectElement, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: options.placeholder || "Buscar...",
                plugins: ['dropdown_input'],
                ...options
            });
        }

        // Toggle between forms
        document.addEventListener('change', (e) => {
            if (e.target.name === 'registro_mode') {
                const mode = e.target.value;
                if (mode === 'impresora') {
                    document.getElementById('form-impresora').classList.remove('hidden');
                    document.getElementById('form-consumible').classList.add('hidden');
                    updatePendingRecords();
                } else if (mode === 'consumible') {
                    document.getElementById('form-impresora').classList.add('hidden');
                    document.getElementById('form-consumible').classList.remove('hidden');
                    updatePendingRecordsConsumible();
                }
            }
        });

        // Add row function for impresora
        document.getElementById('add-row').addEventListener('click', () => {
            const formRows = document.getElementById('form-rows');
            const firstRow = formRows.querySelector('.form-row');
            const newRow = firstRow.cloneNode(true);

            // Clear values
            newRow.querySelectorAll('input, select').forEach(el => {
                el.value = '';
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            // Hide dependent section and images
            newRow.querySelector('.dependent-section').classList.add('hidden');
            newRow.querySelector('.impresora-image').style.display = 'none';
            newRow.querySelector('.impresora-image').src = '';

            // Show remove button
            newRow.querySelector('.remove-row').classList.remove('hidden');

            // Add event listeners to new row elements
            newRow.querySelectorAll('input, select').forEach(el => {
                el.addEventListener('input', updatePendingRecords);
                el.addEventListener('change', updatePendingRecords);
            });

            // Initialize Tom Select for the new impresora select
            const newImpresoraSelect = newRow.querySelector('.impresora-select');
            if (newImpresoraSelect) {
                const row = newImpresoraSelect.closest('.form-row');
                const impresoraImage = row.querySelector('.impresora-image');
                new TomSelect(newImpresoraSelect, {
                    valueField: 'id',
                    labelField: 'impresora',
                    searchField: 'impresora',
                    preload: true,
                    load: function(query, callback) {
                        var url = '{{ route("api.impresoras") }}';
                        if (query) {
                            url += '?search=' + encodeURIComponent(query);
                        }
                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(json => {
                                callback(json);
                            })
                            .catch((error) => {
                                console.error('Error loading impresoras:', error);
                                callback();
                            });
                    },
                    onChange: function(value) {
                        if (value) {
                            const selectedItem = this.options[value];
                            if (selectedItem && selectedItem.imagen) {
                                impresoraImage.src = selectedItem.imagen;
                                impresoraImage.style.display = 'block';
                            } else {
                                impresoraImage.style.display = 'none';
                            }
                        } else {
                            impresoraImage.style.display = 'none';
                        }
                    },
                    render: {
                        option: function(item, escape) {
                            return '<div>' + escape(item.impresora) + '</div>';
                        },
                        item: function(item, escape) {
                            return '<div>' + escape(item.impresora) + '</div>';
                        }
                    }
                });
            }

            formRows.appendChild(newRow);
            const hr = document.createElement('hr');
            hr.className = ' my-4';
            formRows.appendChild(hr);
            rowCount++;
            updatePendingRecords();
        });

        // Add row function for consumible
        document.getElementById('add-row-consumible').addEventListener('click', () => {
            const formRows = document.getElementById('form-rows-consumible');
            const firstRow = formRows.querySelector('.form-row-consumible');
            const newRow = firstRow.cloneNode(true);

            // Clear values
            newRow.querySelectorAll('input').forEach(el => {
                el.value = '';
            });

            // Reset selects
            newRow.querySelectorAll('select').forEach(el => {
                el.selectedIndex = 0;
                if (el.tomselect) {
                    el.tomselect.clear();
                }
            });

            // Hide dependent sections
            newRow.querySelectorAll('.hidden').forEach(el => el.classList.add('hidden'));

            // Show remove button
            newRow.querySelector('.remove-row-consumible').classList.remove('hidden');

            // Initialize Tom Select for new row selects
            const newCartuchoSelect = newRow.querySelector('.cartucho-select-consumible');
            if (newCartuchoSelect) {
                initTomSelect(newCartuchoSelect, { placeholder: "Buscar consumible..." });
            }

            const newImpresoraSelect = newRow.querySelector('.impresora-select-consumible');
            if (newImpresoraSelect) {
                initTomSelect(newImpresoraSelect, {
                    placeholder: "Buscar impresora...",
                    onChange: function(value) {
                        const row = this.select.closest('.form-row-consumible');
                        const selectedItem = this.options[value];
                        const imageEl = row.querySelector('.impresora-image');
                        if (selectedItem && selectedItem.imagen) {
                            imageEl.src = selectedItem.imagen;
                            imageEl.style.display = 'block';
                        } else {
                            imageEl.style.display = 'none';
                        }
                    }
                });
            }

            // Add input listeners
            newRow.querySelectorAll('input').forEach(el => {
                el.addEventListener('input', updatePendingRecordsConsumible);
            });
            newRow.querySelectorAll('select[name="proveedore_id[]"]').forEach(el => {
                el.addEventListener('change', updatePendingRecordsConsumible);
            });

            formRows.appendChild(newRow);
            const hr = document.createElement('hr');
            hr.className = ' my-4';
            formRows.appendChild(hr);
            rowCountConsumible++;
            updatePendingRecordsConsumible();
        });

        // Remove row for both forms
        document.addEventListener('click', (e) => {
            const removeImpresora = e.target.classList.contains('remove-row') || e.target.closest('.remove-row');
            const removeConsumible = e.target.classList.contains('remove-row-consumible') || e.target.closest('.remove-row-consumible');

            if (removeImpresora) {
                const button = e.target.classList.contains('remove-row') ? e.target : e.target.closest('.remove-row');
                const row = button.closest('.form-row');
                const hr = row.nextElementSibling;

                // Destroy Tom Select instances
                row.querySelectorAll('select.tomselect').forEach(el => {
                    if (el.tomselect) el.tomselect.destroy();
                });

                if (hr && hr.tagName === 'HR') hr.remove();
                row.remove();
                rowCount--;
                updatePendingRecords();
            }
            if (removeConsumible) {
                const button = e.target.classList.contains('remove-row-consumible') ? e.target : e.target.closest('.remove-row-consumible');
                const row = button.closest('.form-row-consumible');
                const hr = row.nextElementSibling;

                // Destroy Tom Select instances
                row.querySelectorAll('select.tomselect').forEach(el => {
                    if (el.tomselect) el.tomselect.destroy();
                });

                if (hr && hr.tagName === 'HR') hr.remove();
                row.remove();
                rowCountConsumible--;
                updatePendingRecordsConsumible();
            }
        });

        // Update pending records in sidebar for impresora
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
                const imagenSelect = row.querySelector('.impresora-select');
                const tomImpresora = imagenSelect?.tomselect;
                const tomImpresoraText = tomImpresora ? (tomImpresora.getOption(imagenSelect.value)?.textContent || 'No seleccionada') : (imagenSelect?.selectedOptions[0]?.text || 'No seleccionada');
                const cartuchoSelect = row.querySelector('.cartucho-select');
                const tomCartucho = cartuchoSelect?.tomselect;
                const tomCartuchoText = tomCartucho ? (tomCartucho.getOption(cartuchoSelect.value)?.textContent || 'No seleccionado') : (cartuchoSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const coloreSelect = row.querySelector('.colore-select');
                const tomColor = coloreSelect?.tomselect;
                const tomColorText = tomColor ? (tomColor.getOption(coloreSelect.value)?.textContent || 'No seleccionado') : (coloreSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const fabricanteSelect = row.querySelector('.fabricante-select');
                const tomFabricante = fabricanteSelect?.tomselect;
                const tomFabricanteText = tomFabricante ? (tomFabricante.getOption(fabricanteSelect.value)?.textContent || 'No seleccionado') : (fabricanteSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const proveedor = row.querySelector('select[name="proveedore_id[]"]').selectedOptions[0]?.text || 'No seleccionado';

                const unidades = row.querySelector('input[name="unidades[]"]').value || '0';
                const factura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = `
                    <h3 class="font-bold">Registro ${index + 1}: ${tomImpresoraText}</h3>
                    <p>Fecha: ${fecha}</p>
                    <p>Consumible: ${tomCartuchoText}</p>
                    <p>Tipo: ${tomColorText}</p>
                    <p>Fabricante: ${tomFabricanteText}</p>
                    <p>Proveedor: ${proveedor}</p>
                    <p>Cantidad: ${unidades}</p>
                    <p>Factura: ${factura}</p>
                `;
                pendingRecordsDiv.appendChild(recordDiv);
            });
        }

        // Update pending records in sidebar for consumible
        function updatePendingRecordsConsumible() {
            const pendingRecordsDiv = document.getElementById('pending-records');
            const noRecords = document.getElementById('no-records');
            pendingRecordsDiv.innerHTML = '';

            const rows = document.querySelectorAll('.form-row-consumible');
            if (rows.length === 0) {
                noRecords.classList.remove('hidden');
                return;
            }

            noRecords.classList.add('hidden');

            rows.forEach((row, index) => {
                const fecha = row.querySelector('input[name="fecha_ingreso[]"]').value || 'No ingresada';
                const cartuchoSelect = row.querySelector('.cartucho-select-consumible');
                const tomCartucho = cartuchoSelect?.tomselect;
                const tomCartuchoText = tomCartucho ? (tomCartucho.getOption(cartuchoSelect.value)?.textContent || 'No seleccionado') : (cartuchoSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const coloreSelect = row.querySelector('.colore-select-consumible');
                const tomColor = coloreSelect?.tomselect;
                const tomColorText = tomColor ? (tomColor.getOption(coloreSelect.value)?.textContent || 'No seleccionado') : (coloreSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const fabricanteSelect = row.querySelector('.fabricante-select-consumible');
                const tomFabricante = fabricanteSelect?.tomselect;
                const tomFabricanteText = tomFabricante ? (tomFabricante.getOption(fabricanteSelect.value)?.textContent || 'No seleccionado') : (fabricanteSelect?.selectedOptions[0]?.text || 'No seleccionado');
                const imagenSelect = row.querySelector('.impresora-select-consumible');
                const tomImpresora = imagenSelect?.tomselect;
                const tomImpresoraText = tomImpresora ? (tomImpresora.getOption(imagenSelect.value)?.textContent || 'No seleccionada') : (imagenSelect?.selectedOptions[0]?.text || 'No seleccionada');
                const proveedor = row.querySelector('select[name="proveedore_id[]"]').selectedOptions[0]?.text || 'No seleccionado';
                const unidades = row.querySelector('input[name="unidades[]"]').value || '0';
                const factura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = `
                    <h3 class="font-bold">Registro ${index + 1}: ${tomCartuchoText}</h3>
                    <p>Fecha: ${fecha}</p>
                    <p>Impresora: ${tomImpresoraText}</p>
                    <p>Tipo: ${tomColorText}</p>
                    <p>Fabricante: ${tomFabricanteText}</p>
                    <p>Proveedor: ${proveedor}</p>
                    <p>Cantidad: ${unidades}</p>
                    <p>Factura: ${factura}</p>
                `;
                pendingRecordsDiv.appendChild(recordDiv);
            });
        }

        // Dependent selects for impresora
        document.addEventListener('change', (e) => {
            const target = e.target;
            const row = target.closest('.form-row');

            if (row && target.classList.contains('impresora-select')) {
                const selectedImpresoraId = target.value;
                const cartuchoSelect = row.querySelector('.cartucho-select');
                const coloreSelect = row.querySelector('.colore-select');
                const fabricanteSelect = row.querySelector('.fabricante-select');
                const impresoraImage = row.querySelector('.impresora-image');
                const dependentSection = row.querySelector('.dependent-section');

                cartuchoSelect.innerHTML = '<option value="">Seleccione</option>';
                coloreSelect.innerHTML = '<option value="">Seleccione consumible primero</option>';
                fabricanteSelect.innerHTML = '<option value="">Seleccione consumible primero</option>';

                if (selectedImpresoraId) {
                    dependentSection.classList.remove('hidden');
                    const filteredCartuchos = cartuchos.filter(cartucho => cartucho.impresora_id == selectedImpresoraId);
                    filteredCartuchos.forEach(cartucho => {
                        const option = document.createElement('option');
                        option.value = cartucho.id;
                        option.textContent = cartucho.cartucho + ' - (' + (cartucho.colore ? cartucho.colore.colore : '') + ')';
                        cartuchoSelect.appendChild(option);
                    });

                    // Show image
                    const selectedOption = target.selectedOptions[0];
                    const imageUrl = selectedOption.getAttribute('data-imagen');
                    if (imageUrl) {
                        impresoraImage.src = imageUrl;
                        impresoraImage.style.display = 'block';
                    } else {
                        impresoraImage.style.display = 'none';
                    }
                } else {
                    dependentSection.classList.add('hidden');
                    impresoraImage.style.display = 'none';
                }
                updatePendingRecords();
            }

            if (row && target.classList.contains('cartucho-select')) {
                const selectedCartuchoId = target.value;
                const coloreSelect = row.querySelector('.colore-select');
                const fabricanteSelect = row.querySelector('.fabricante-select');

                coloreSelect.innerHTML = '<option value="">Seleccione</option>';
                fabricanteSelect.innerHTML = '<option value="">Seleccione</option>';

                if (selectedCartuchoId) {
                    row.querySelector('.colore-div').classList.remove('hidden');
                    row.querySelector('.fabricante-div').classList.remove('hidden');
                    const selectedCartucho = cartuchos.find(cartucho => cartucho.id == selectedCartuchoId);
                    if (selectedCartucho) {
                        if (selectedCartucho.colore) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.colore.id;
                            option.textContent = selectedCartucho.colore.colore;
                            coloreSelect.appendChild(option);
                            coloreSelect.value = selectedCartucho.colore.id;
                        }
                        if (selectedCartucho.fabricante) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.fabricante.id;
                            option.textContent = selectedCartucho.fabricante.fabricante;
                            fabricanteSelect.appendChild(option);
                            fabricanteSelect.value = selectedCartucho.fabricante.id;
                        }
                    }
                } else {
                    row.querySelector('.colore-div').classList.add('hidden');
                    row.querySelector('.fabricante-div').classList.add('hidden');
                }
                updatePendingRecords();
            }



            // Dependent selects for consumible
            const rowConsumible = target.closest('.form-row-consumible');

            if (rowConsumible && target.classList.contains('cartucho-select-consumible')) {
                const selectedCartuchoId = target.value;
                const coloreSelect = rowConsumible.querySelector('.colore-select-consumible');
                const fabricanteSelect = rowConsumible.querySelector('.fabricante-select-consumible');
                const impresoraSelect = rowConsumible.querySelector('.impresora-select-consumible');

                coloreSelect.innerHTML = '<option value="">Seleccione</option>';
                fabricanteSelect.innerHTML = '<option value="">Seleccione</option>';

                if (selectedCartuchoId) {
                    rowConsumible.querySelector('.colore-div-consumible').classList.remove('hidden');
                    rowConsumible.querySelector('.fabricante-div-consumible').classList.remove('hidden');
                    rowConsumible.querySelector('.impresora-div-consumible').classList.remove('hidden');
                    const selectedCartucho = cartuchos.find(cartucho => cartucho.id == selectedCartuchoId);
                    if (selectedCartucho) {
                        if (selectedCartucho.colore) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.colore.id;
                            option.textContent = selectedCartucho.colore.colore;
                            coloreSelect.appendChild(option);
                            coloreSelect.value = selectedCartucho.colore.id;
                        }
                        if (selectedCartucho.fabricante) {
                            const option = document.createElement('option');
                            option.value = selectedCartucho.fabricante.id;
                            option.textContent = selectedCartucho.fabricante.fabricante;
                            fabricanteSelect.appendChild(option);
                            fabricanteSelect.value = selectedCartucho.fabricante.id;
                        }
                        // Set the specific impresora associated with the cartucho
                        const impresoraId = selectedCartucho.impresora_id;
                        impresoraSelect.value = impresoraId;

                        // Show image
                        const impresoraImage = rowConsumible.querySelector('.impresora-image');
                        const selectedOption = impresoraSelect.selectedOptions[0];
                        const imageUrl = selectedOption ? selectedOption.getAttribute('data-imagen') : null;
                        if (imageUrl) {
                            impresoraImage.src = imageUrl;
                            impresoraImage.style.display = 'block';
                        } else {
                            impresoraImage.style.display = 'none';
                        }
                    }
                } else {
                    rowConsumible.querySelector('.colore-div-consumible').classList.add('hidden');
                    rowConsumible.querySelector('.fabricante-div-consumible').classList.add('hidden');
                    rowConsumible.querySelector('.impresora-div-consumible').classList.add('hidden');
                }
                updatePendingRecordsConsumible();
            }

            if (rowConsumible && target.classList.contains('impresora-select-consumible')) {
                const impresoraImage = rowConsumible.querySelector('.impresora-image');
                const selectedOption = target.selectedOptions[0];
                const imageUrl = selectedOption ? selectedOption.getAttribute('data-imagen') : null;
                if (imageUrl) {
                    impresoraImage.src = imageUrl;
                    impresoraImage.style.display = 'block';
                } else {
                    impresoraImage.style.display = 'none';
                }
                updatePendingRecordsConsumible();
            }


        });

        // Set initial form visibility
        document.getElementById('form-consumible').classList.remove('hidden');
        document.getElementById('form-impresora').classList.add('hidden');

        // Initial update
        updatePendingRecordsConsumible();

        // Add event listeners to all form inputs and selects for impresora
        document.querySelectorAll('#form-rows input, #form-rows select').forEach(el => {
            el.addEventListener('input', updatePendingRecords);
            el.addEventListener('change', updatePendingRecords);
        });

        // Add event listeners to all form inputs and selects for consumible
        document.querySelectorAll('#form-rows-consumible input, #form-rows-consumible select').forEach(el => {
            el.addEventListener('input', updatePendingRecordsConsumible);
            el.addEventListener('change', updatePendingRecordsConsumible);
        });

        // Initialize Tom Select for impresora selects
        document.querySelectorAll('.impresora-select').forEach(select => {
            const row = select.closest('.form-row');
            const impresoraImage = row.querySelector('.impresora-image');
            new TomSelect(select, {
                valueField: 'id',
                labelField: 'impresora',
                searchField: 'impresora',
                preload: true,
                load: function(query, callback) {
                    var url = '{{ route("api.impresoras") }}';
                    if (query) {
                        url += '?search=' + encodeURIComponent(query);
                    }
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(json => {
                            callback(json);
                        })
                        .catch((error) => {
                            console.error('Error loading impresoras:', error);
                            callback();
                        });
                },
                onChange: function(value) {
                    if (value) {
                        // Find the selected item
                        const selectedItem = this.options[value];
                        if (selectedItem && selectedItem.imagen) {
                            impresoraImage.src = selectedItem.imagen;
                            impresoraImage.style.display = 'block';
                        } else {
                            impresoraImage.style.display = 'none';
                        }
                    } else {
                        impresoraImage.style.display = 'none';
                    }
                },
                render: {
                    option: function(item, escape) {
                        return '<div>' + escape(item.impresora) + '</div>';
                    },
                    item: function(item, escape) {
                        return '<div>' + escape(item.impresora) + '</div>';
                    }
                }
            });
        });

    </script>
</x-app-layout>
@endcan
