<title>{{ 'Permisos' }}</title>

@vite('resources/css/app.css')
<x-app-layout>
    <body>
        <x-slot name="header">
            <h2 class="text-4xl font-semibold leading-tight text-center text-blue-800">
                {{ __('Permisos') }}
            </h2>
        </x-slot>
        <div class="p-4 px-20 m-6">

            <div>
                <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('gtic.permisos.create')}}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>Añadir nuevos permisos</a>
            </div>
            <form action="{{route('gtic.permisos.index')}}" method="GET" class="flex-grow">
                <input type="search" name="search" placeholder="Buscar" value="{{request('search')}}"
                class="w-1/2 px-4 py-2 border border-gray-200 rounded">
                <button class="py-2.5 px-4 text-white bg-blue-900 rounded-lg ml-2 inline-flex hover:bg-blue-500" type="submit">Buscar<svg xmlns="http://www.w3.org/2000/svg" class="ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 18a7.952 7.952 0 0 0 4.897-1.688l4.396 4.396 1.414-1.414-4.396-4.396A7.952 7.952 0 0 0 18 10c0-4.411-3.589-8-8-8s-8 3.589-8 8 3.589 8 8 8zm0-14c3.309 0 6 2.691 6 6s-2.691 6-6 6-6-2.691-6-6 2.691-6 6-6z"></path><path d="M11.412 8.586c.379.38.588.882.588 1.414h2a3.977 3.977 0 0 0-1.174-2.828c-1.514-1.512-4.139-1.512-5.652 0l1.412 1.416c.76-.758 2.07-.756 2.826-.002z"></path></svg></button>
                <a href="{{route('gtic.permisos.index')}}" class="px-4 py-3 ml-2 text-white bg-blue-900 rounded-lg hover:bg-blue-500">Actualizar<svg xmlns="http://www.w3.org/2000/svg" class="inline-flex ml-2" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M10 11H7.101l.001-.009a4.956 4.956 0 0 1 .752-1.787 5.054 5.054 0 0 1 2.2-1.811c.302-.128.617-.226.938-.291a5.078 5.078 0 0 1 2.018 0 4.978 4.978 0 0 1 2.525 1.361l1.416-1.412a7.036 7.036 0 0 0-2.224-1.501 6.921 6.921 0 0 0-1.315-.408 7.079 7.079 0 0 0-2.819 0 6.94 6.94 0 0 0-1.316.409 7.04 7.04 0 0 0-3.08 2.534 6.978 6.978 0 0 0-1.054 2.505c-.028.135-.043.273-.063.41H2l4 4 4-4zm4 2h2.899l-.001.008a4.976 4.976 0 0 1-2.103 3.138 4.943 4.943 0 0 1-1.787.752 5.073 5.073 0 0 1-2.017 0 4.956 4.956 0 0 1-1.787-.752 5.072 5.072 0 0 1-.74-.61L7.05 16.95a7.032 7.032 0 0 0 2.225 1.5c.424.18.867.317 1.315.408a7.07 7.07 0 0 0 2.818 0 7.031 7.031 0 0 0 4.395-2.945 6.974 6.974 0 0 0 1.053-2.503c.027-.135.043-.273.063-.41H22l-4-4-4 4z"></path></svg></a>
            </form>
            <table class="table w-full text-black sm:p-4">



                <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
                    <tr class="border-4 border-blue-900 ">
                        <th class='px-6 py-4 text-center border-4 border-blue-900 '>ID</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>PERMISOS</th>

                   <th colspan="2" class='py-4 text-center border-4 border-blue-900 w-fit '>OPCIÓN</th>
                </thead >
                <tbody class='py-4 text-center border-4 border-blue-900'>
                    @forelse ($permissions as $permission)
                        <tr class='px-6 py-2 my-4 text-black border-4 border-blue-900'>
                            <td class='py-2 text-center border-4 border-blue-900'> {{$permission->id}}</td>
                            <td class='px-10 py-2 text-center border-4 border-blue-900'>
                                {{$permission->name}}</td>

                           {{-- <td class='py-4 text-center border-4 border-blue-900'><a href="{{route('gtic.permisos.show', $permiso->id)}}"  class="px-4 py-2.5 text-white bg-blue-900 rounded-lg">Permiso</a></td> --}}
                            <td class='flex items-center justify-center py-2 text-center border-4 '> <a href="{{route('gtic.permisos.edit', $permission->id)}}" target="blank_" class="w-fit inline-flex flex justify-around px-4 py-2.5 mr-2  text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none">Editar <svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg></a>
                        <form action="{{ route('gtic.permisos.destroy', $permission->id) }}" method="POST" onsubmit="return confirm('¿Seguro?')">
                                    @csrf
                                    @method('DELETE')
                                <button class="inline-flex px-4 py-2.5 mt-3 items-center rounded-lg font-semibold text-center text-white bg-red-600  transition duration-150 ease-in-out  hover:text-white hover:bg-red-500 focus:outline-none" type="submit">Eliminar <svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M6 7H5v13a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7H6zm4 12H8v-9h2v9zm6 0h-2v-9h2v9zm.618-15L15 2H9L7.382 4H3v2h18V4z"></path></svg></button>
                        </form></td>

                         @empty
                         No hay ningun permiso registrado
                         </tr>

                    @endforelse
                </tbody>

            </table>
            <div>
                {{$permissions->links()}}
            </div>
        </div>
    </body>
</x-app-layout>
