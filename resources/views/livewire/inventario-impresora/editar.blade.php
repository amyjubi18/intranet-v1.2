<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Inventario de Consumible</h2>
        <a href="{{ route('administracion.inventario_impresoras.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>

    <form wire:submit.prevent="submit" class="space-y-6">
        <!-- Fecha de Ingreso -->
        <div>
            <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Ingreso</label>
            <input type="date" wire:model="fecha_ingreso" id="fecha_ingreso" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('fecha_ingreso') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Impresora -->
        <div>
            <label for="impresora_id" class="block text-sm font-medium text-gray-700 mb-2">Impresora</label>
            <select wire:model.live="impresora_id" id="impresora_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Impresora</option>
                @foreach($impresoras as $impresora)
                    <option value="{{ $impresora->id }}">{{ $impresora->impresora }}</option>
                @endforeach
            </select>
            @error('impresora_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Cartucho -->
        <div>
            <label for="cartucho_id" class="block text-sm font-medium text-gray-700 mb-2">Consumible</label>
            <select wire:model.live="cartucho_id" id="cartucho_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Consumible</option>
                @foreach($cartuchos as $cartucho)
                    <option value="{{ $cartucho->id }}">{{ $cartucho->cartucho }}</option>
                @endforeach
            </select>
            @error('cartucho_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Color -->
        <div>
            <label for="colore_id" class="block text-sm font-medium text-gray-700 mb-2">Color</label>
            <select wire:model="colore_id" id="colore_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Color</option>
                @foreach($colores as $color)
                    <option value="{{ $color->id }}">{{ $color->colore }}</option>
                @endforeach
            </select>
            @error('colore_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Tipo Consumible -->
        <div>
            <label for="fabricante_id" class="block text-sm font-medium text-gray-700 mb-2">Tipo Consumible</label>
            <select wire:model="fabricante_id" id="fabricante_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Tipo</option>
                @foreach($fabricantes as $fabricante)
                    <option value="{{ $fabricante->id }}">{{ $fabricante->fabricante }}</option>
                @endforeach
            </select>
            @error('fabricante_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Proveedor -->
        <div>
            <label for="proveedore_id" class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
            <select wire:model="proveedore_id" id="proveedore_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Proveedor</option>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}">{{ $proveedor->proveedor }}</option>
                @endforeach
            </select>
            @error('proveedore_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Ubicación -->
        <div>
            <label for="ubicacion_id" class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
            <select wire:model="ubicacion_id" id="ubicacion_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Ubicación</option>
                @foreach($ubicacions as $ubicacion)
                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->ubicacion }}</option>
                @endforeach
            </select>
            @error('ubicacion_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
            <select wire:model="area_id" id="area_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar Área</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->area }}</option>
                @endforeach
            </select>
            @error('area_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Unidades -->
        <div>
            <label for="unidades" class="block text-sm font-medium text-gray-700 mb-2">Unidades</label>
            <input type="number" wire:model="unidades" id="unidades" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('unidades') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Número de Factura -->
        <div>
            <label for="n_factura" class="block text-sm font-medium text-gray-700 mb-2">Número de Factura</label>
            <input type="text" wire:model="n_factura" id="n_factura" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('n_factura') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('administracion.inventario_impresoras.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Actualizar
            </button>
        </div>
    </form>
</div>
