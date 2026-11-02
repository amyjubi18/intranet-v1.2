<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Salida de Consumible</h2>
        <a href="{{ route('administracion.salida_impresora.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>

    <form wire:submit.prevent="submit" class="space-y-6">
        <!-- Fecha de Salida -->
        <div>
            <label for="fecha_salida" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Salida</label>
            <input type="date" wire:model="fecha_salida" id="fecha_salida" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('fecha_salida') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Gerencia -->
        <div>
            <label for="gerencia_id" class="block text-sm font-medium text-gray-700 mb-2">Gerencia</label>
            <select wire:model.live="gerencia_id" id="gerencia_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Gerencia</option>
                @foreach($gerencias as $gerencia)
                    <option value="{{ $gerencia->id }}">{{ $gerencia->gerencia }}</option>
                @endforeach
            </select>
            @error('gerencia_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Área -->
        <div>
            <label for="area_id" class="block text-sm font-medium text-gray-700 mb-2">Área</label>
            <select wire:model.live="area_id" id="area_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Área</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->area }}</option>
                @endforeach
            </select>
            @error('area_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Inventario Impresora -->
        <div>
            <label for="inventario_impresora_id" class="block text-sm font-medium text-gray-700 mb-2">Inventario Impresora</label>
            <select wire:model.live="inventario_impresora_id" id="inventario_impresora_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Inventario</option>
                @foreach($inventario_impresoras as $inventario)
                    <option value="{{ $inventario->id }}">
                        {{ $inventario->impresoras->impresora ?? 'N/A' }} - {{ $inventario->cartuchos->cartucho ?? 'N/A' }} - {{ $inventario->colores->colore ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
            @error('inventario_impresora_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Información del Inventario Seleccionado -->
        @if($inventario_impresora_id)
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-4">Información del Consumible</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Impresora:</p>
                    <p class="font-semibold">{{ $impresora_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Consumible:</p>
                    <p class="font-semibold">{{ $cartucho_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Color:</p>
                    <p class="font-semibold">{{ $colore_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tipo Consumible:</p>
                    <p class="font-semibold">{{ $fabricante_display }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Fecha de Ingreso:</p>
                    <p class="font-semibold">{{ $fecha_ingreso ? \Carbon\Carbon::parse($fecha_ingreso)->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Unidades Disponibles:</p>
                    <p class="font-semibold">{{ $unidades }}</p>
                </div>
            </div>
            <div class="mt-4">
                <img src="{{ $imagen_impresora }}" alt="Impresora" class="w-20 h-20 object-cover rounded-lg">
            </div>
        </div>
        @endif

        <!-- Cantidad -->
        <div>
            <label for="cantidad" class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
            <input type="number" wire:model="cantidad" id="cantidad" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('cantidad') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Ajuste Inventario -->
        <div>
            <label for="ajuste_inventario" class="block text-sm font-medium text-gray-700 mb-2">Ajuste Inventario</label>
            <input type="text" wire:model="ajuste_inventario" id="ajuste_inventario" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('administracion.salida_impresora.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Actualizar
            </button>
        </div>
    </form>
</div>
