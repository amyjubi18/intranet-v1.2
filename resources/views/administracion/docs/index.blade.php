@can('documentos_index')

<title>{{ 'Documentos' }}</title>
@vite('resources/css/app.css')
<x-app-layout>
<body>
    <x-slot name="header">
        <h2 class="text-4xl font-semibold leading-tight text-center text-blue-800">
            {{ __('Documentos') }}
        </h2>
    </x-slot>
    <div class="p-4 m-6">
        <div>
            <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('administracion.docs.create')}}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>Subir Documento</a>
            <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('export')}}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-2" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 16h2V7h3l-4-5-4 5h3z"></path><path d="M5 22h14c1.103 0 2-.897 2-2v-9c0-1.103-.897-2-2-2h-4v2h4v9H5v-9h4V9H5c-1.103 0-2 .897-2 2v9c0 1.103.897 2 2 2z"></path></svg>Exportar excel</a>
        </div>
        <form action="{{route('administracion.docs.index')}}" method="GET" class="flex-grow">
            <input type="search" name="search" placeholder="Buscar" value="{{request('search')}}"
            class="w-1/2 px-4 py-2 border border-gray-200 rounded">
            <button class="py-2.5 px-4 text-white bg-blue-900 rounded-lg ml-2 inline-flex hover:bg-blue-700" type="submit">Buscar<svg xmlns="http://www.w3.org/2000/svg" class="ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button>
                <a href="{{route('administracion.docs.index')}}" class="px-4 py-3 ml-2 text-white bg-blue-900 rounded-lg hover:bg-blue-700">Actualizar<svg xmlns="http://www.w3.org/2000/svg" class="inline-flex ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"></path></svg></a>
        </form>
        <form action="{{route('administracion.docs.index')}}" method="GET" class="flex-grow">

            <div class="flex p-3 mt-4 w-fit justify-evenly" >
                <div class="inline-block">
                <label for="" class="p-2 mt-2 text-black w-max">Fecha Inicial</label>
                    <input type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer inline-flex  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-fit pl-3 p-2.5 "  name="fecha_inicial" size="100" maxlength="250" id="" required >
                    <x-input-error :messages="$errors->get('fecha_inicial')" class="mt-2" />
                    </div>
                <div class="inline-block">
                <label for="" class="p-2 mt-2 text-black ">Fecha Final</label>
                    <input type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer inline-flex  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-fit pl-3 p-2.5 "  name="fecha_final" size="100" maxlength="250" id="" required >
                    <x-input-error :messages="$errors->get('fecha_final')" class="mt-2" />
                    </div>
                        <button class="inline-flex px-4 py-3 mt-3 mb-2 ml-2 text-white bg-blue-900 rounded-lg hover:bg-blue-700" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button>
            </div>
        </form>
        <table class="table w-full text-black sm:p-4">



            <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
                <tr class="border-4 border-blue-900 ">

                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>NúMERO DE EXPEDIENTE</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>DESCRIPCIÓN</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>FECHA</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>DOCUMENTO</th>
                    <th class='px-8 py-4 text-center border-4 border-blue-900 '>CATEGORÍA</th>
                    <th colspan="3" class='px-10 py-4 text-center border-4 border-blue-900 '>OPCIÓN</th>
            </thead >
            <tbody class='py-4 text-center border-4 border-blue-900'>
                @forelse ($documentos as $dato)
                    <tr class='px-6 py-4 my-4 text-black border-4 border-blue-900'>

                        <td class='py-4 text-center border-4 border-blue-900'>{{$dato->name}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'>{{$dato->description}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->fecha}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->documento}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->categoria->name}}</td>
                        <td class='items-center px-3 py-4 border-4 border-blue-900 '><a href="/archivos/{{$dato->documento}}" target="blank_" class=" py-2.5 text-white font-semibold bg-blue-900 rounded-lg flex text-center px-3 pl-4 hover:bg-blue-700">Visualizar<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="ml-1" viewBox="0 0 22 20" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M12 4.998c-1.836 0-3.356.389-4.617.971L3.707 2.293 2.293 3.707l3.315 3.316c-2.613 1.952-3.543 4.618-3.557 4.66l-.105.316.105.316C2.073 12.382 4.367 19 12 19c1.835 0 3.354-.389 4.615-.971l3.678 3.678 1.414-1.414-3.317-3.317c2.614-1.952 3.545-4.618 3.559-4.66l.105-.316-.105-.316c-.022-.068-2.316-6.686-9.949-6.686zM4.074 12c.103-.236.274-.586.521-.989l5.867 5.867C6.249 16.23 4.523 13.035 4.074 12zm9.247 4.907-7.48-7.481a8.138 8.138 0 0 1 1.188-.982l8.055 8.054a8.835 8.835 0 0 1-1.763.409zm3.648-1.352-1.541-1.541c.354-.596.572-1.28.572-2.015 0-.474-.099-.924-.255-1.349A.983.983 0 0 1 15 11a1 1 0 0 1-1-1c0-.439.288-.802.682-.936A3.97 3.97 0 0 0 12 7.999c-.735 0-1.419.218-2.015.572l-1.07-1.07A9.292 9.292 0 0 1 12 6.998c5.351 0 7.425 3.847 7.926 5a8.573 8.573 0 0 1-2.957 3.557z"></path></svg></a></td>



                            {{-- @forelse ($dato->categorias as $categoria)
                            <span class="p-2 text-white rounded-lg bg-cyan-600">{{$categoria->name}}</span>
                            @empty
                            <span class="p-2 text-white bg-red-700 rounded-lg">No tiene roles</span>
                            @endforelse --}}
                            @can('documentos_edit')

                       <td class='px-3 py-4 text-center border-4 border-blue-900 justify-items-center '>
                            <a href="{{route('administracion.docs.edit', $dato->id)}}" target="blank_" class="w-fit inline-flex flex justify-around px-3 py-2.5  text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none font-semibold">Editar<svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg></a></td>
                            @endcan
                            @can('documentos_destroy')
                        <td class='px-3 py-4 text-center border-4 border-blue-900'>
                             <form action="{{ route('administracion.docs.destroy', $dato->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quiere eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex px-4 py-2.5 mt-3 items-center rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none" type="submit">Eliminar<svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg></button>
                            </form>
                            @endcan


                 @empty
                        No hay ningun permiso registrado
                     </tr>
                @endforelse
            </tbody>

        </table>
        <div>
            {{$documentos->links()}}
        </div>
    </div>
</body>
</x-app-layout>
@endcan
