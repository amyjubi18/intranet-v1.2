<div class="bg-white p-6 shadow-lg rounded-lg max-w-4xl mx-auto">
<form wire:submit.prevent="submit" class="space-y-4">


    <!-- Artículo y Unidades -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Artículo:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="articulo_id" id="articulo_id" wire:model="articulo_id">
                <option value="">Seleccione</option>
                @if($articulos && $articulos->isNotEmpty())
                    @foreach ($articulos as $articulo)
                        <option value="{{ $articulo->id }}">
                            {{ str_pad($articulo->id, 4, '0', STR_PAD_LEFT) }} - {{ $articulo->articulo }}
                        </option>
                    @endforeach
                @else
                    <option disabled>No hay artículos con stock disponible</option>
                @endif
            </select>
            <x-input-error :messages="$errors->get('articulo_id')" class="mt-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Unidad de Medida:</label>
            <input type="text"
               class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 font-medium text-gray-700"
                name="unidad_medida_nombre" id="unidad_medida_nombre" wire:model="unidad_medida_nombre" readonly placeholder="Seleccione un artículo">
            <input type="hidden" name="unidad_medida_id" id="unidad_medida_id" wire:model="unidad_medida_id">
            <x-input-error :messages="$errors->get('unidad_medida_id')" class="mt-2" />
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Fecha de Ingreso:</label>
            <input type="date"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="fecha_ingreso" id="fecha_ingreso" wire:model="fecha_ingreso" disabled>
            <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Unidades Disponibles:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 font-bold text-green-600"
                name="unidades" id="unidades" wire:model="unidades" readonly>
            <x-input-error :messages="$errors->get('unidades')" class="mt-2" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-black">Cantidad Solicitada:</label>
            <input type="number"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900" required min="0"
                name="cantidad" id="cantidad" wire:model="cantidad">
            <x-input-error :messages="$errors->get('cantidad')" class="mt-2" />
        </div>
    </div>

    <!-- Unidad de Medida y Fechas -->








    <!-- Gerencia y Área -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1 text-black">Gerencia:</label>
            <select
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900"
                name="gerencia_id" id="gerencia_id" wire:model="gerencia_id">
                <option value="">Seleccione</option>
                @foreach ($gerencias as $gerencia)
                    <option value="{{ $gerencia->id }}">
                        {{ $gerencia->gerencia }} - ({{ optional($gerencia->area)->area }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('gerencia_id')" class="mt-2" />
        </div>

        <div>
            <label class="block text-sm font-medium mb-1 text-black">Área:</label>
            <input type="text"
                class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 font-medium text-gray-700"
                name="area_nombre" id="area_nombre" wire:model="area_nombre" readonly placeholder="Seleccione una gerencia">
            <input type="hidden" name="area_id" id="area_id" wire:model="area_id">
            <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
        </div>
    </div>

    <!-- Ajuste de Inventario -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
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
            name="ajuste_inventario" id="ajuste_inventario" wire:model="ajuste_inventario">
        <x-input-error :messages="$errors->get('ajuste_inventario')" class="mt-2" />
    </div>
    </div>
    <!-- Botones -->
    <div class="flex mt-4 justify-evenly">

        <x-primary-link href="{{ route('administracion.salida_oficina.index') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                        {{ __('Regresar') }}
            </x-primary-link>

        <x-primary-button type="submit" class="btn-save">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17,21 17,13 7,13 7,21"/>
                    <polyline points="7,3 7,8 15,8"/>
                </svg>
                        Guardar
        </x-primary-button>
    </div>

</form>
</div>

