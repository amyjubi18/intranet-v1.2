@can('documentos_create')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <title>{{ 'Cargar Documentos' }}</title>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')
        <title>Cargar</title>

    </head>

    <body class="antialiased ">
        <section class="flex flex-col items-center h-screen border-4 border-blue-900 rounded md:flex-row ">
            <div class="hidden h-screen bg-blue-600 lg:block md:w-1/2 xl:w-2/3">
                <img src="/img/fondo-intranet.jpg" class="object-cover w-full h-full" alt="">
            </div>
            <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white sm:max-w-md sm:rounded-lg">
                <h1 class="my-1 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Cargar Documento</h1>

                        <div class="items-center justify-center w-full mb-1 h-200 touch-none">




                <form action="{{route("administracion.docs.store")}}" method="POST" enctype="multipart/form-data">
                    @csrf
                <div class="mt-3">




                                <label class="block text-black" for="default_size">Subir Documento</label>
                        <input class="block w-full text-lg bg-white border border-blue-900 rounded-lg cursor-pointer placeholder:py-3 dark:text-gray-400 focus:outline-none 0" type="file" name="pdf"  size="150" accept=".pdf" maxlength="150" id="" required>
                        <x-input-error :messages="$errors->get('pdf')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                                    <label for="" class="block text-black">Número del Expediente:</label>
                        <input type="text" placeholder="Ingresa el Nombre del Archivo" class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="name"  size="70" maxlength="70" id="" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                                    <label for="" class="block text-black">Description del Archivo:</label>
                        <input type="text" placeholder="Ingresa la descripción del Archivo" class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="description" size="100" maxlength="250" id="" required>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                    <label for="" class="block mt-2 text-black">Fecha del Documento: </label>
                        <input type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 "  name="fecha" size="100" maxlength="250" id="" required>
                        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                    </div>
                    <div>
                        <label for="" class="block mt-1 text-black">Categorías</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="categoria_id">
                            <option value="">Seleccione la categoria</option>
                            @foreach ($categorias as $categoria)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$categoria->id}}">{{$categoria->name}}</option>

                            @endforeach
                        </select>
                        @can('categorias_create')
                        <button class="px-4 py-3 mx-4 text-base font-semibold text-white bg-blue-900 rounded-lg w-30 hover:bg-blue-700" name="submit" type="submit">

                            <a href="categorias/create" class="flex text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M2.165 19.551c.186.28.499.449.835.449h15c.4 0 .762-.238.919-.606l3-7A.998.998 0 0 0 21 11h-1V8c0-1.103-.897-2-2-2h-6.655L8.789 4H4c-1.103 0-2 .897-2 2v13h.007a1 1 0 0 0 .158.551zM18 8v3H6c-.4 0-.762.238-.919.606L4 14.129V8h14z"></path></svg>Nueva Categoria</a>
                            </button>
                        @endcan
                        <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />

                    </div>
                </div>
                    <div class="flex mt-4 justify-evenly">

                        <a class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 " href="{{ route('administracion.docs.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
                        </a>

                        <x-primary-button class="ml-4" onsubmit="return route('administracion.docs.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                        </x-primary-button>
                    </div>

                </form>
                </div>

                            </div>
                    </div>
                    </div>


            </div>
            </div>
        </section>
    </body>
</html>
@endcan
