<div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
<form wire:submit.prevent="submit" class="space-y-4">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Gerencias:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="gerencia_id" id="gerencia_id"  wire:model="gerencia_id">
                <option value="">Seleccione</option>
                @foreach ($gerencias->unique('gerencia') as $gerencia)
                    <option value="{{ $gerencia->id }}">
                        {{ $gerencia->gerencia }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('gerencia_id')" class="mt-2" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-black">Áreas:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="area_id" id="area_id"  wire:model="area_id">
                <option value="">Seleccione</option>
                @foreach ($areas as $area)
                    <option value="{{ $area->id }}">
                        {{ $area->area }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
        </div>
    </div>

    @if($gerencia_id && $area_id)
    <div class="flex items-center gap-4 justify-between">
        <div class="flex-1">
            <label class="block text-sm font-medium mb-1 text-black"> Impresora:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="inventario_impresora_id" id="inventario_impresora_id"  wire:model="inventario_impresora_id">
                <option value="">Seleccione la impresora</option>
                @foreach ($inventario_impresoras as $inv)
                    <option value="{{ $inv->id }}">
                        {{ $inv->impresoras->impresora ?? '' }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('inventario_impresora_id')" class="mt-2" />
        </div>
        @if($inventario_impresora_id)
        <div class="flex-none justify-center bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-lg p-2">
            <img class="w-32 h-32 object-cover rounded-lg" src="{{ $imagen_impresora }}" alt="Imagen de Impresora">
        </div>
        @endif
    </div>

    @if($inventario_impresora_id)
    <div class="mt-4 hidden">
        <label class="block text-sm font-medium mb-1 text-black">Impresora:</label>
        <input type="text" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" value="{{ $impresora_display }}" readonly>
    </div>

    <div class="flex mt-4 justify-between ">
        <div class="block w-full">
            <label class="block text-sm font-medium mb-1 text-black">Consumible:</label>
            <input type="text" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" value="{{ $cartucho_display }}" readonly>
        </div>
        <div class="block ml-2 w-full">
            <label class="block text-sm font-medium mb-1 text-black">Color:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="colore_display" id="colore_display" disabled wire:model="colore_display">
        </div>
    </div>

    <div class="flex mt-4 justify-between">
        <div class="block w-full">
            <label class="block text-sm font-medium mb-1 text-black">Tipo de Consumibles:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="fabricante_display" id="fabricante_display" disabled wire:model="fabricante_display">
        </div>
        <div class="block ml-2 w-full" >
            <label class="block text-sm font-medium mb-1 text-black">Fecha de Ingreso:</label>
            <input type="date"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="fecha_ingreso" id="fecha_ingreso" disabled wire:model="fecha_ingreso">
        </div>
    </div>

    @endif
    @endif

    <div class="grid grid-cols-3 gap-4 mt-4">
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Unidades Disponibles:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="unidades" id="unidades" disabled wire:model="unidades">
            <x-input-error :messages="$errors->get('unidades')" class="mt-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Cantidad Solicitada:</label>
            <input type="number"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required min="0"
                name="cantidad" id="cantidad" wire:model="cantidad">
            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Fecha de Salida:</label>
            <input type="date"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="fecha_salida" id="fecha_salida" required wire:model="fecha_salida">
            <x-input-error :messages="$errors->get('fecha_salida')" class="mt-2" />
        </div>
    </div>

    <div class="flex mt-4 justify-between">

        <div class="block ml-2 w-full">
            <label class="block text-sm font-medium mb-1 text-black">Ajustes de Inventario:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="ajuste_inventario" id="ajuste_inventario" required wire:model="ajuste_inventario">
            <x-input-error :messages="$errors->get('ajuste_inventario')" class="mt-2" />
        </div>
    </div>





    <div class="flex mt-4 justify-evenly">

        <a class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 "
            href="{{ route('administracion.salida_impresora.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24"
                style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                <path
                    d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z">
                </path>
            </svg>{{ __('Regresar') }}
        </a>

        <button type="submit" class="ml-4 inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24"
                style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                <path
                    d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z">
                </path>
            </svg> {{ __('Guardar') }}
        </button>
    </div>

</form>
</div>
