@can('dist_almac_edit')
@section('title', 'Editar')
@section('subtitle', 'Editar Distribución de Almacenamiento de Impresoras')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Distribución de Almacenamiento de Impresoras') }}
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

                <form action="{{ route('administracion.distribucion_almacenamiento_impresoras.update', $distribucion) }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    @method('PUT')
                    <div class="rounded-lg">
                        <div class="space-y-4">





                            <!-- Almacenamiento with image on right -->
                            <div class="flex items-center gap-4 justify-between">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                                    <select name="almacenamiento_impresora_id" class="almacenamiento-select w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach($almacenamientos as $almacenamiento)
                                            <option value="{{ $almacenamiento->id }}" data-unidades="{{ $almacenamiento->unidades_disponibles_calculadas }}" data-imagen="{{ asset('storage/' . $almacenamiento->cartuchos?->impresora?->imagen ?? '') }}" data-consumible="{{ $almacenamiento->cartuchos?->cartucho ?? 'N/A' }}" data-color="{{ $almacenamiento->cartuchos?->colore?->colore ?? 'N/A' }}" data-tipo="{{ $almacenamiento->cartuchos?->fabricante?->fabricante ?? 'N/A' }}" {{ $distribucion->almacenamiento_impresora_id == $almacenamiento->id ? 'selected' : '' }}>
                                                {{ $almacenamiento->impresoras?->impresora ?? 'N/A' }}
                                            </option>

                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('almacenamiento_impresora_id')" class="mt-2" />
                                </div>

                                <div class="flex-none justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
                                    <img class="almacenamiento-image w-32 h-32 object-cover rounded-lg " src="{{ asset('storage/' . $distribucion->almacenamientoImpresora?->cartuchos?->impresora?->imagen ?? '') }}" alt="Imagen de Impresora" style="display: {{ $distribucion->almacenamientoImpresora?->cartuchos?->impresora?->imagen ? 'block' : 'none' }};">
                                </div>

                            </div>

                            <!-- Dependent displays: Consumible, Color, Tipo de Consumible -->
                            <div class="dependent-section space-y-2">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
                                        <input type="text" id="consumible-input" value="{{ $distribucion->almacenamientoImpresora?->cartuchos?->cartucho ?? 'N/A' }}" class="w-full px-3 py-2 border-none bg-blue-100  rounded-lg" readonly>
                                    </div>
                                   <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
                                        <input type="text" id="color-input" value="{{ $distribucion->almacenamientoImpresora?->cartuchos?->colore?->colore ?? 'N/A' }}" class="w-full px-3 py-2 border-none bg-blue-100  rounded-lg" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1 text-black">Tipo de Impresora:</label>
                                        <input type="text" id="tipo-input" value="{{ $distribucion->almacenamientoImpresora?->cartuchos?->fabricante?->fabricante ?? 'N/A' }}" class="w-full px-3 py-2 border-none bg-blue-100  rounded-lg" readonly>
                                    </div>
                                </div>

                            </div>

                            <!-- Unidades Disponibles and Unidades Asignadas side by side -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Unidades Disponibles:</label>
                                    <input type="text" id="unidades_asignadas_originales" value="{{ $distribucion->unidades_asignadas_originales ?? 'N/A' }}" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Unidades Asignadas:</label>
                                    <input type="number" name="unidades_asignadas" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required min="1" value="{{ $distribucion->unidades_asignadas }}">
                                    <x-input-error :messages="$errors->get('unidades_asignadas')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Gerencia -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Gerencia:</label>
                                <select name="gerencia_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                    <option value="">Seleccione</option>
                                    @foreach($gerencias as $gerencia)
                                        <option value="{{ $gerencia->id }}" {{ $distribucion->gerencia_id == $gerencia->id ? 'selected' : '' }}>
                                            {{ $gerencia->gerencia }} ({{ $gerencia->area->area ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gerencia_id')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                            <x-primary-link href="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                            {{ __('Regresar') }}
                            </x-primary-link>
                            <x-primary-button onsubmit="return route('administracion.distribucion_almacenamiento_impresoras.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                            </x-primary-button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to update current record in sidebar
        function updateCurrentRecord() {
            const currentRecordDiv = document.getElementById('current-record');
            const almacenamiento = document.querySelector('.almacenamiento-select').selectedOptions[0]?.text || 'No seleccionado';
            const consumible = '{{ $distribucion->almacenamientoImpresora?->cartuchos?->cartucho ?? 'N/A' }}';
            const color = '{{ $distribucion->almacenamientoImpresora?->cartuchos?->colore?->colore ?? 'N/A' }}';
            const tipo = '{{ $distribucion->almacenamientoImpresora?->cartuchos?->fabricante?->fabricante ?? 'N/A' }}';
            const unidadesDisponibles = '{{ $distribucion->almacenamientoImpresora?->unidades_disponibles_calculadas ?? 'N/A' }}';
            const unidadesAsignadas = document.querySelector('input[name="unidades_asignadas"]').value;
            const gerencia = document.querySelector('select[name="gerencia_id"]').selectedOptions[0]?.text || 'No seleccionada';

            currentRecordDiv.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="font-bold mb-2">Registro Actual</h3>
                    <p><strong>Almacenamiento:</strong> ${almacenamiento}</p>
                    <p><strong>Consumible:</strong> ${consumible}</p>
                    <p><strong>Tipo de Consumible:</strong> ${color}</p>
                    <p><strong>Tipo de Impresora:</strong> ${tipo}</p>
                    <p><strong>Unidades Disponibles:</strong> ${unidadesDisponibles}</p>
                    <p><strong>Unidades Asignadas:</strong> ${unidadesAsignadas}</p>
                    <p><strong>Gerencia:</strong> ${gerencia}</p>
                </div>
            `;
        }

        // For almacenamiento -> show image and update dependent fields
        if (document.querySelector('.almacenamiento-select')) {
            document.querySelector('.almacenamiento-select').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const imagenSrc = selectedOption.getAttribute('data-imagen');
                const consumible = selectedOption.getAttribute('data-consumible');
                const color = selectedOption.getAttribute('data-color');
                const tipo = selectedOption.getAttribute('data-tipo');
                const unidades = selectedOption.getAttribute('data-unidades');

                const imagenPreview = document.querySelector('.almacenamiento-image');
                if (imagenSrc && imagenSrc !== '') {
                    imagenPreview.src = imagenSrc;
                    imagenPreview.style.display = 'block';
                } else {
                    imagenPreview.style.display = 'none';
                }

                // Update dependent fields
                document.getElementById('consumible-input').value = consumible;
                document.getElementById('color-input').value = color;
                document.getElementById('tipo-input').value = tipo;
                document.getElementById('unidades-input').value = unidades;

                updateCurrentRecord();
            });
        }

        // Update on input changes
        document.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', updateCurrentRecord);
        });

        // Initial load
        updateCurrentRecord();
    </script>
</x-app-layout>
@endcan
