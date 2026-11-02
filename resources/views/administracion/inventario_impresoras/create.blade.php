@can('inventarioimpre_create')
@section('title', 'Registrar')
@section('subtitle', 'Registrar Inventario Impresoras')
<x-app-layout>


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
                            <input type="radio" name="registro_mode" value="impresora" checked class="mr-2">
                            <span class="text-sm font-medium">Registrar por Impresora</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="registro_mode" value="consumible" class="mr-2">
                            <span class="text-sm font-medium">Registrar por Consumible</span>
                        </label>
                    </div>
                </div>

                <!-- Formulario por Impresora -->
                <div id="form-impresora" class="form-container">
                    <form id="batch-form-impresora" action="{{ route('administracion.inventario_impresoras.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    <div id="form-rows" class="space-y-4">
                        <!-- Initial row -->
                        <div class="form-row bg-white/20 p-4 shadow-lg rounded-lg">
                            <div class="space-y-4">
                                <!-- Fecha de Ingreso - Full width -->
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black ">Fecha de Ingreso:</label>
                                    <input type="date" name="fecha_ingreso[]" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 " required>
                                </div>

                                <!-- Impresora with image on right -->
                                <div class="flex items-center gap-4 justify-between">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                        <select name="impresora_id[]" class="impresora-select border-none bg-blue-100  w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione</option>
                                            @foreach ($impresoras->sortBy('impresora') as $impresora)
                                            <option value="{{ $impresora->id }}" data-imagen="{{ asset('storage/' . $impresora->imagen) }}">{{ $impresora->impresora }}</option>
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
                                            <label class="block text-sm font-medium mb-1 text-black">Colores:</label>
                                            <select name="colore_id[]" class="colore-select border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione cartucho primero</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="fabricante-div hidden">
                                        <label class="block text-sm  font-medium mb-1 text-black">Tipo de Consumible:</label>
                                        <select name="fabricante_id[]" class="fabricante-select border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione cartucho primero</option>
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

                                <!-- Gerencia and Area side by side -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Gerencias:</label>
                                        <select name="gerencia_id[]" class="gerencia-select w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione</option>
                                            @foreach ($gerencias->sortBy('gerencia') as $gerencia)
                                            <option value="{{ $gerencia->id }}">{{ $gerencia->gerencia }} - ({{ optional($gerencia->area)->area }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Áreas:</label>
                                        <select name="area_id[]" class="area-select w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 " required>
                                            <option value="">Seleccione</option>
                                            @foreach ($areas->sortBy('area') as $area)
                                            <option value="{{ $area->id }}">{{ $area->area }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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

                    <div class="flex justify-between items-center mt-6">
                        <button type="button" id="add-row" class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border-none border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none  "> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>
                            Agregar Registro
                        </button>
                        <div class="space-x-2">
                            <a class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none " href="{{ route('administracion.inventario_impresoras.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
                        </a>
                            <x-primary-button class="ml-4" onsubmit="return route('administracion.inventario_impresoras.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                        </x-primary-button>
                        </div>
                    </div>
                </form>
                </div>

                <!-- Formulario por Consumible -->
                <div id="form-consumible" class="form-container hidden">
                    <form id="batch-form-consumible" action="{{ route('administracion.inventario_impresoras.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                        @csrf
                        <div id="form-rows-consumible" class="space-y-4">
                            <!-- Initial row -->
                            <div class="form-row-consumible bg-white/20 p-4 shadow-lg rounded-lg">
                                <div class="space-y-4">
                                    <!-- Fecha de Ingreso - Full width -->
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black ">Fecha de Ingreso:</label>
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
                                                <label class="block text-sm font-medium mb-1 text-black">Colores:</label>
                                                <select name="colore_id[]" class="colore-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                    <option value="">Seleccione cartucho primero</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="fabricante-div-consumible hidden">
                                            <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
                                            <select name="fabricante_id[]" class="fabricante-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione cartucho primero</option>
                                            </select>
                                        </div>
                                        <div class="impresora-div-consumible hidden">
                                            <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                            <select name="impresora_id[]" class="impresora-select-consumible border-none bg-blue-100 w-full px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione cartucho primero</option>
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

                                    <!-- Gerencia and Area side by side -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-black">Gerencias:</label>
                                            <select name="gerencia_id[]" class="gerencia-select-consumible w-full border-none bg-blue-100 px-3 py-2 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione</option>
                                                @foreach ($gerencias->sortBy('gerencia') as $gerencia)
                                                <option value="{{ $gerencia->id }}">{{ $gerencia->gerencia }} - ({{ optional($gerencia->area)->area }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1 text-black">Áreas:</label>
                                            <select name="area_id[]" class="area-select-consumible w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 " required>
                                                <option value="">Seleccione</option>
                                                @foreach ($areas->sortBy('area') as $area)
                                                <option value="{{ $area->id }}">{{ $area->area }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button type="button" id="add-row-consumible" class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border-none border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none  "> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>
                                Agregar Registro
                            </button>
                            <div class="space-x-2">
                                <a class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none " href="{{ route('administracion.inventario_impresoras.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
                            </a>
                                <x-primary-button class="ml-4" onsubmit="return route('administracion.inventario_impresoras.index')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                            </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                    </div>

    <script>
        // Data from Laravel
        const cartuchos = @json($cartuchos);
        const colores = @json($colores);
        const fabricantes = @json($fabricantes);
        const gerencias = @json($gerencias);
        const impresoras = @json($impresoras);

        let rowCount = 1;
        let rowCountConsumible = 1;

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
            newRow.querySelectorAll('input, select').forEach(el => {
                el.value = '';
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });

            // Hide dependent sections
            newRow.querySelector('.dependent-section-consumible').querySelectorAll('.hidden').forEach(el => el.classList.add('hidden'));

            // Show remove button
            newRow.querySelector('.remove-row-consumible').classList.remove('hidden');

            // Add event listeners to new row elements
            newRow.querySelectorAll('input, select').forEach(el => {
                el.addEventListener('input', updatePendingRecordsConsumible);
                el.addEventListener('change', updatePendingRecordsConsumible);
            });

            formRows.appendChild(newRow);
            const hr = document.createElement('hr');
            hr.className = ' my-4';
            formRows.appendChild(hr);
            rowCountConsumible++;
            updatePendingRecordsConsumible();
        });

        // Remove row for impresora
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-row')) {
                const row = e.target.closest('.form-row');
                const hr = row.nextElementSibling;
                if (hr && hr.tagName === 'HR') hr.remove();
                row.remove();
                rowCount--;
                updatePendingRecords();
            }
            if (e.target.classList.contains('remove-row-consumible')) {
                const row = e.target.closest('.form-row-consumible');
                const hr = row.nextElementSibling;
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
                const impresora = row.querySelector('.impresora-select').selectedOptions[0]?.text || 'No seleccionada';
                const cartucho = row.querySelector('.cartucho-select').selectedOptions[0]?.text || 'No seleccionado';
                const colore = row.querySelector('.colore-select').selectedOptions[0]?.text || 'No seleccionado';
                const fabricante = row.querySelector('.fabricante-select').selectedOptions[0]?.text || 'No seleccionado';
                const proveedor = row.querySelector('select[name="proveedore_id[]"]').selectedOptions[0]?.text || 'No seleccionado';
                const ubicacion = row.querySelector('select[name="ubicacion_id[]"]').selectedOptions[0]?.text || 'No seleccionada';
                const gerencia = row.querySelector('.gerencia-select').selectedOptions[0]?.text || 'No seleccionada';
                const area = row.querySelector('.area-select').selectedOptions[0]?.text || 'No seleccionada';
                const unidades = row.querySelector('input[name="unidades[]"]').value || '0';
                const factura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = `
                    <h3 class="font-bold">Registro ${index + 1}: ${impresora}</h3>
                    <p>Fecha: ${fecha}</p>
                    <p>Consumible: ${cartucho}</p>
                    <p>Color: ${colore}</p>
                    <p>Fabricante: ${fabricante}</p>
                    <p>Proveedor: ${proveedor}</p>
                    <p>Ubicación: ${ubicacion}</p>
                    <p>Gerencia: ${gerencia}</p>
                    <p>Área: ${area}</p>
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
                const cartucho = row.querySelector('.cartucho-select-consumible').selectedOptions[0]?.text || 'No seleccionado';
                const colore = row.querySelector('.colore-select-consumible').selectedOptions[0]?.text || 'No seleccionado';
                const fabricante = row.querySelector('.fabricante-select-consumible').selectedOptions[0]?.text || 'No seleccionado';
                const impresora = row.querySelector('.impresora-select-consumible').selectedOptions[0]?.text || 'No seleccionada';
                const proveedor = row.querySelector('select[name="proveedore_id[]"]').selectedOptions[0]?.text || 'No seleccionado';
                const ubicacion = row.querySelector('select[name="ubicacion_id[]"]').selectedOptions[0]?.text || 'No seleccionada';
                const gerencia = row.querySelector('.gerencia-select-consumible').selectedOptions[0]?.text || 'No seleccionada';
                const area = row.querySelector('.area-select-consumible').selectedOptions[0]?.text || 'No seleccionada';
                const unidades = row.querySelector('input[name="unidades[]"]').value || '0';
                const factura = row.querySelector('input[name="n_factura[]"]').value || 'No ingresada';

                const recordDiv = document.createElement('div');
                recordDiv.className = 'bg-white border border-gray-300 rounded-lg p-4 m-2 shadow-lg text-sm';
                recordDiv.innerHTML = `
                    <h3 class="font-bold">Registro ${index + 1}: ${cartucho}</h3>
                    <p>Fecha: ${fecha}</p>
                    <p>Impresora: ${impresora}</p>
                    <p>Color: ${colore}</p>
                    <p>Fabricante: ${fabricante}</p>
                    <p>Proveedor: ${proveedor}</p>
                    <p>Ubicación: ${ubicacion}</p>
                    <p>Gerencia: ${gerencia}</p>
                    <p>Área: ${area}</p>
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

            if (row && target.classList.contains('gerencia-select')) {
                const selectedGerenciaId = target.value;
                const areaSelect = row.querySelector('.area-select');

                areaSelect.innerHTML = '<option value="">Seleccione</option>';

                if (selectedGerenciaId) {
                    const selectedGerencia = gerencias.find(gerencia => gerencia.id == selectedGerenciaId);
                    if (selectedGerencia && selectedGerencia.area) {
                        const option = document.createElement('option');
                        option.value = selectedGerencia.area.id;
                        option.textContent = selectedGerencia.area.area;
                        areaSelect.appendChild(option);
                        areaSelect.value = selectedGerencia.area.id;
                    }
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
                impresoraSelect.innerHTML = '<option value="">Seleccione</option>';

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
                        const impresora = impresoras.find(imp => imp.id == impresoraId);
                        if (impresora) {
                            const option = document.createElement('option');
                            option.value = impresora.id;
                            option.textContent = impresora.impresora;
                            impresoraSelect.appendChild(option);
                            impresoraSelect.value = impresora.id;
                        }
                    }
                } else {
                    rowConsumible.querySelector('.colore-div-consumible').classList.add('hidden');
                    rowConsumible.querySelector('.fabricante-div-consumible').classList.add('hidden');
                    rowConsumible.querySelector('.impresora-div-consumible').classList.add('hidden');
                }
                updatePendingRecordsConsumible();
            }

            if (rowConsumible && target.classList.contains('gerencia-select-consumible')) {
                const selectedGerenciaId = target.value;
                const areaSelect = rowConsumible.querySelector('.area-select-consumible');

                areaSelect.innerHTML = '<option value="">Seleccione</option>';

                if (selectedGerenciaId) {
                    const selectedGerencia = gerencias.find(gerencia => gerencia.id == selectedGerenciaId);
                    if (selectedGerencia && selectedGerencia.area) {
                        const option = document.createElement('option');
                        option.value = selectedGerencia.area.id;
                        option.textContent = selectedGerencia.area.area;
                        areaSelect.appendChild(option);
                        areaSelect.value = selectedGerencia.area.id;
                    }
                }
                updatePendingRecordsConsumible();
            }
        });

        // Initial update
        updatePendingRecords();

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
