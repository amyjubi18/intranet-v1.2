@can('inventarioofic_edit')
@section('title', 'Editar')
@section('subtitle', 'Editar Inventario de Oficina')
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Inventario de Oficina') }}
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
                <form action="{{route('administracion.inventario_oficinas.update', $inventario_oficina->id)}}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg p-6">
                    @csrf
                    @method('PUT')
                    <div class="rounded-lg">
                        <div class="space-y-4">
                            <!-- Fecha de Ingreso - Full width -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Fecha de Ingreso:</label>
                                <input type="date" name="fecha_ingreso" class="w-fit px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('fecha_ingreso', $inventario_oficina->fecha_ingreso)}}">
                                <x-input-error :messages="$errors->get('fecha_ingreso')" class="mt-2" />
                            </div>

                            <!-- Artículo - Full width -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Artículo:</label>
                                <select name="articulo_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($articulos->sortBy('articulo') as $articulo)
                                    <option value="{{$articulo->id}}"
                                        @if ($inventario_oficina->articulo_id == $articulo->id) selected @endif>
                                        {{ str_pad($articulo->id, 4, '0', STR_PAD_LEFT) }}-{{$articulo->articulo}}-({{optional($articulo->unidad_medida)->unidad_medida}})
                                    </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('articulo_id')" class="mt-2" />
                            </div>

                            <!-- Unidad de Medida y Ubicación side by side -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Unidad de Medida:</label>
                                    <select name="unidad_medida_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($unidad_medidas->sortBy('unidad_medida') as $unidad_medida)
                                        <option value="{{$unidad_medida->id}}"
                                            @if ($inventario_oficina->unidad_medida_id == $unidad_medida->id) selected @endif>{{$unidad_medida->unidad_medida}}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('unidad_medida_id')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Ubicación:</label>
                                    <select name="ubicacion_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($ubicacions as $ubicacion)
                                        <option value="{{$ubicacion->id}}"
                                            @if ($inventario_oficina->ubicacion_id == $ubicacion->id) selected @endif>{{$ubicacion->ubicacion}}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('ubicacion_id')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Proveedor side by side with Cantidad -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Proveedor:</label>
                                    <select name="proveedore_id" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($proveedores as $proveedore)
                                        <option value="{{$proveedore->id}}"
                                            @if ($inventario_oficina->proveedore_id == $proveedore->id) selected @endif>{{$proveedore->proveedor}}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('proveedore_id')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-black">Cantidad:</label>
                                    <input type="number" name="unidades" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('unidades', $inventario_oficina->unidades)}}">
                                    <x-input-error :messages="$errors->get('unidades')" class="mt-2" />
                                </div>
                            </div>

                            <!-- Número de Factura -->
                            <div>
                                <label class="block text-sm font-medium mb-1 text-black">Número de Factura:</label>
                                <input type="text" name="n_factura" class="w-full px-3 py-2 border-none bg-blue-100 rounded-lg focus:ring-blue-900 focus:border-blue-500" required value="{{old('n_factura', $inventario_oficina->n_factura)}}">
                                <x-input-error :messages="$errors->get('n_factura')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between items-center mt-6 gap-2">

                            <x-primary-link href="{{ route('administracion.inventario_oficinas.index') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>
                                {{ __('Regresar') }}
                            </x-primary-link>
                            <x-primary-button>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg>
                                {{ __('Guardar') }}
                            </x-primary-button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
@endcan

