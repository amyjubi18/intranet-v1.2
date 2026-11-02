<div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
<form wire:submit.prevent="submit" class="space-y-4">


        <div>
            <label class="block text-sm font-medium mb-1 text-black">Almacenamiento Impresora:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="almacenamiento_impresora_id" id="almacenamiento_impresora_id" wire:model="almacenamiento_impresora_id">
                <option value="">Seleccione</option>
                @foreach ($almacenamiento_impresoras as $almacenamiento)
                    <option value="{{ $almacenamiento->id }}">
                     Impresora: {{ $almacenamiento->impresoras->impresora ?? '' }} - Consumible {{ $almacenamiento->cartuchos->cartucho ?? '' }} </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('almacenamiento_impresora_id')" class="mt-2" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-black">Distribución (Gerencia):</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="distribucion_almacenamiento_impresora_id" id="distribucion_almacenamiento_impresora_id" wire:model="distribucion_almacenamiento_impresora_id">
                <option value="">Seleccione</option>
                @foreach ($distribuciones as $distribucion)
                    <option value="{{ $distribucion->id }}">
                        {{ $distribucion->gerencia->gerencia ?? '' }} - {{ $distribucion->gerencia->area->area ?? '' }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('distribucion_almacenamiento_impresora_id')" class="mt-2" />

    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
    @if($almacenamiento_impresora_id)
    <div>
            <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
            <p class="text-lg font-bold text-gray-700">{{ $impresora }}</p>

        </div>
        <div class="w-36 h-36 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center flex-shrink-0 self-center md:self-start mt-2">
                @if($imagen_impresora)
                    <img src="{{ asset('storage/' . $imagen_impresora) }}" alt="Impresora" class="w-36 h-36 object-cover rounded-xl">
                @else
                    <span class="text-blue-400" style="font-size: 32px;">🖨️</span>
                @endif
            </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
            <p class="text-lg font-bold text-gray-700">{{ $consumible }}</p>
        </div>
         <div>
            <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumible:</label>
            <p class="text-lg font-bold text-gray-700">{{ $color }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Tipo de Impresorsa:</label>
            <p class="text-lg font-bold text-gray-700">{{ $tipo }}</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Fecha de ingreso:</label>
            <p class="text-lg font-bold text-gray-700">{{ $fecha_ingreso ? \Carbon\Carbon::parse($fecha_ingreso)->format('d-m-Y') : '' }}</p>
        </div>
    <div class="mt-4">
        <label class="block text-sm font-medium mb-1 text-black">Unidades Disponibles:</label>
        <p class="text-xl font-bold text-green-600">{{ $unidades_disponibles }}</p>
        {{-- <input type="text" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" value="{{ $unidades_disponibles }}" readonly> --}}
    </div>
    @endif

    @if($distribucion_almacenamiento_impresora_id)

    <div class="mt-4">
        <label class="block text-sm font-medium mb-1 text-black">Unidades Asignadas:</label>
        <p class="text-xl font-bold text-blue-600">{{ $unidades_asignadas }}</p>
        {{-- <input type="text" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" value="{{ $unidades_asignadas }}" readonly> --}}
    </div>
    @endif
    <div class="mt-4">
            <label class="block text-sm font-medium mb-1 text-black">Cantidad Solicitada:</label>
            <input type="number"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required min="0"
                name="cantidad" id="cantidad" wire:model="cantidad">
            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
        </div>
    </div>


    <div class="grid grid-cols-2 gap-4 mt-4">

        <div>
            <label class="block text-sm font-medium mb-1 text-black">Fecha de Salida:</label>
            <input type="date"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="fecha_salida" id="fecha_salida" required wire:model="fecha_salida">
            <x-input-error :messages="$errors->get('fecha_salida')" class="mt-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Ajustes de Inventario:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="ajuste_inventario" id="ajuste_inventario" required wire:model="ajuste_inventario">
            <x-input-error :messages="$errors->get('ajuste_inventario')" class="mt-2" />
        </div>
    </div>

    <div class="flex mt-4 justify-evenly">
        <x-primary-link href="{{ route('administracion.salida_almacen.index') }}">
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
