<div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
    <form wire:submit.prevent="submit" class="space-y-4">

        <!-- Almacenamiento -->
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Almacenamiento:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="almacenamiento_impresora_id" id="almacenamiento_impresora_id" wire:model.live="almacenamiento_impresora_id">
                <option value="">Seleccione almacenamiento</option>
                @php
                    $groupedAlmacenamientos = \App\Models\AlmacenamientoImpresora::with('cartuchos', 'impresoras', 'colores')->get()->groupBy('impresora_id');
                @endphp
                @foreach ($groupedAlmacenamientos as $printerId => $almacenamientos)
                    @php
                        $printer = $almacenamientos->first()->impresoras;
                    @endphp
                    <optgroup label="{{ $printer ? $printer->impresora : 'Impresora Desconocida' }} ">
                        @foreach ($almacenamientos as $almacenamiento)
                            <option value="{{ $almacenamiento->id }}" {{ $almacenamiento_impresora_id == $almacenamiento->id ? 'selected' : '' }}>
                                 Consumible: {{ $almacenamiento->cartuchos->cartucho ?? 'N/A' }} - Tipo de Consumible: {{ $almacenamiento->colores->colore ?? 'N/A' }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('almacenamiento_impresora_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>



        <!-- Unidades Disponibles -->
        @if($almacenamiento_impresora_id)


        <!-- Información del Almacenamiento -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
                <input type="text"
                    class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                    value="{{ $impresora_display }}" readonly>
            </div>
            @if($imagen_impresora)
            <div>
                <label class="block text-sm font-medium mb-1 text-black">Imagen de la Impresora:</label>
                <div class="flex justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
                    <img src="{{ $imagen_impresora }}" alt="Imagen de la Impresora" class="w-36 h-36 object-cover rounded-lg">
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
            <div>

                <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
                <input type="text"
                    class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                    value="{{ $cartucho_display }}" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
                <input type="text"
                    class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                    value="{{ $colore_display }}" readonly>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
            <div>
                <label class="block text-sm font-medium mb-1 text-black">Tipo de Impresora:</label>
                <input type="text"
                    class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                    value="{{ $fabricante_display }}" readonly>
            </div>
            <div>
            <label class="block text-sm font-medium mb-1 text-black">Unidades Disponibles:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                value="{{ $unidades_disponibles }}" readonly>
        </div>

        </div>
        @endif

        <!-- Unidades Asignadas -->
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Unidades Asignadas:</label>
            <input type="number"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required min="1"
                name="unidades_asignadas" id="unidades_asignadas" wire:model="unidades_asignadas">
            @error('unidades_asignadas')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Gerencia -->
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Gerencia:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="gerencia_id" id="gerencia_id" wire:model="gerencia_id">
                <option value="">Seleccione gerencia</option>
                @foreach (\App\Models\Gerencia::with('area')->get() as $gerencia)
                    <option value="{{ $gerencia->id }}">
                        {{ $gerencia->gerencia }} ({{ $gerencia->area->area ?? 'N/A' }})
                    </option>
                @endforeach
            </select>
            @error('gerencia_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>




        <!-- Botones -->
        <div class="flex mt-4 justify-evenly">
            <x-primary-link href="{{ route('administracion.distribucion_almacenamiento_impresoras.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24"
                    style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                    <path
                        d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z">
                    </path>
                </svg>
                {{ __('Regresar') }}
            </x-primary-link>

            <x-primary-button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24"
                    style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                    <path
                        d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z">
                    </path>
                </svg>
                {{ __('Guardar') }}
            </x-primary-button>
        </div>

    </form>
</div>
