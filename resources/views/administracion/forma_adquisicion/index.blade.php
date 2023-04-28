<title>{{ 'Forma de Adquisición' }}</title>

@vite('resources/css/app.css')
<x-app-layout>
    <body>
        <x-slot name="header">
            <h2 class="text-4xl font-semibold leading-tight text-center text-blue-800">
                {{ __('Forma de Adquisición') }}
            </h2>
        </x-slot>
        <div class="p-4 px-20 m-6">

            <div>
                <a class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" href="{{route('administracion.forma_adquisicion.create')}}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6z"></path></svg>Añadir nueva forma de adquisición</a>
            </div>

            <table class="table w-full text-black sm:p-4">



                <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
                    <tr class="border-4 border-blue-900 ">
                        <th class='px-6 py-4 text-center border-4 border-blue-900 '>ID</th>
                    <th class='px-6 py-4 text-center border-4 border-blue-900 '>FORMA DE ADQUISICIÓN</th>

                   <th colspan="2" class='py-4 text-center border-4 border-blue-900 w-fit '>OPCIÓN</th>
                </thead >
                <tbody class='py-4 text-center border-4 border-blue-900'>
                    @forelse ($forma_adquisicions as $forma_adquisicion)
                        <tr class='px-6 py-2 my-4 text-black border-4 border-blue-900'>
                            <td class='py-2 text-center border-4 border-blue-900'> {{$forma_adquisicion->id}}</td>
                            <td class='px-10 py-2 text-center border-4 border-blue-900'>
                                {{$forma_adquisicion->forma_adquisicion}}</td>


                            <td class='flex items-center justify-center py-2 text-center border-4 '> <a href="{{route('administracion.forma_adquisicion.edit', $forma_adquisicion->id)}}" target="blank_" class="w-fit inline-flex flex justify-around px-4 py-2.5 mr-2 font-semibold  text-white bg-green-700 rounded-lg transition duration-150 ease-in-out  hover:text-white hover:bg-green-500 focus:outline-none">Editar <svg xmlns="http://www.w3.org/2000/svg" class="ml-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="m16 2.012 3 3L16.713 7.3l-3-3zM4 14v3h3l8.299-8.287-3-3zm0 6h16v2H4z"></path></svg></a>
                        <form action="{{ route('administracion.forma_adquisicion.destroy', $forma_adquisicion->id) }}" method="POST" onsubmit="return confirm('¿Seguro?')">
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
                 {{$forma_adquisicions->links()}}
             </div>
        </div>
    </body>
</x-app-layout>
