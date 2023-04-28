@can('bienes_index')
<title>{{ 'Bienes Públicos' }}</title>
@vite('resources/css/app.css')
<x-app-layout>
    <body>
        <x-slot name="header">
            <h2 class="text-4xl font-semibold leading-tight text-center text-blue-800">
                {{ __('Bienes Públicos') }}
            </h2>
        </x-slot>
        <div class="p-4 pb-2 m-4 mb-2">
            <div>
                <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('administracion.bienes_publicos.create')}}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>Formulario</a>


                <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('export')}}"><svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 16h2V7h3l-4-5-4 5h3z"></path><path d="M5 22h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-4v2h4v9H5v-9h4V9H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2z"></path></svg>Exportar</a>
            </div>
            <form action="{{route('administracion.bienes_publicos.index')}}" method="GET" class="flex-grow">
                <input type="search" name="search" placeholder="Buscar" value="{{request('search')}}"
                class="w-1/2 px-4 py-2 border border-gray-200 rounded">
                <button class="py-2.5 px-4 text-white bg-blue-900 rounded-lg ml-2 inline-flex hover:bg-blue-500" type="submit">Buscar<svg xmlns="http://www.w3.org/2000/svg" class="ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button>
                    <a href="{{route('administracion.bienes_publicos.index')}}" class="px-4 py-3 ml-2 text-white bg-blue-900 rounded-lg hover:bg-blue-500">Actualizar<svg xmlns="http://www.w3.org/2000/svg" class="inline-flex ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"></path></svg></a>
            </form>
        </div>
        <div>
            <table class="block w-full overflow-x-scroll text-black table-auto sm:p-4">



                <thead class='w-full text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white'>
                    <tr class="border-4 border-blue-900 ">
                        <th class='px-6 py-4 text-center border-4 border-blue-900 '></th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>SEDE</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>UNIDAD ADMINISTRATIVA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>N° INTERNO DEL BIEN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>DESCRIPCIÓN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>FORMA DE ADQUISICIÓN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>FECHA DE ADQUISICIÓN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>N° DE FACTURA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>VALOR DE FACTURA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>MONEDA</th>
                    <th class='w-auto px-6 py-4 text-center border-4 border-blue-900 '>ESTADO DEL USO DEL BIEN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>CONDICIÓN FÍSICA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>MARCA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>MODELO</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>SERIAL</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>CATEGORÍA GENERAL</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>SUBCATEGORÍA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>CATEGORÍA ESPECÍFICA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>CÓDIGO CONTABLE</th>


                    <th colspan="2" class='px-10 py-4 text-center border-4 border-blue-900 '>OPCIÓN</th>
                </thead >
                 <tbody class='py-4 text-center border-4 border-blue-900'>
                    @forelse ($bienes as $dato)
                        <tr class='px-6 py-4 my-4 text-black border-4 border-blue-900'>
                            <td>
                                <input type="checkbox" wire:model="selectedBienes.{{$dato->id}}">
                            </td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->sede}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'> {{$dato->unidad_administrativas->unidad_administrativa}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->n_interno_bien}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->descripcion}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->forma_adquisicions->forma_adquisicion}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->fecha_adquisicion}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->n_factura}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->valor_factura}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->moneda}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->estado_del_usos->estado_del_uso}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->condicion_fisica}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->marca}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->modelo}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->serial}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->categoria_generals->categoria_general}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->sub_categorias->sub_categoria}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->categoria_especificas->categoria_especifica}}</td>
                            <td class='py-4 text-center border-4 border-blue-900'>{{$dato->codigo_contable}}</td>
                             <td class='px-3 py-4 text-center border-4 border-blue-900 justify-items-center '>
                                <a href="{{route('administracion.bienes_publicos.edit', $dato->id)}}" target="blank_" class="w-fit inline-flex flex justify-around px-4 py-2.5 font-semibold text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-non">Editar<svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg></a></td>
                                <td class='px-3 py-4 text-center border-4 border-blue-900'>
                                    <form action="{{ route('administracion.bienes_publicos.destroy', $dato->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quiere eliminar esta fila?')">
                                       @csrf
                                       @method('DELETE')
                                       <button class="inline-flex px-4 py-2.5 mt-3 items-center rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none" type="submit">Eliminar<svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg></button>
                                   </form>
                            </td>

                     @empty
                            No hay ningun permiso registrado
                         </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
             <div class="px-10 pb-10 m-6">
                {{$bienes->links()}}
            </div>
        </div>
    </body>
    </x-app-layout>
    @endcan
