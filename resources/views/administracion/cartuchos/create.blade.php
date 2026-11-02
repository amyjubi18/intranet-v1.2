@can('cartucho_create')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ 'Consumible'}}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 ">
        <section class="flex flex-col items-center h-screen  rounded md:flex-row ">
            <div class="hidden h-screen bg-blue-600 lg:block md:w-1/2 xl:w-2/3">
            <img src="/img/fondo-intranet.jpg" class="object-cover w-full h-full " >
            </div>

            <div class="w-full mx-auto px-6 py-4 mt-6 overflow-hidden bg-white sm:max-w-md sm:rounded-lg">

                <h1 class="py-3 pb-0 text-3xl font-bold text-center text-blue-900 pb-4">Registrar Consumible</h1>

                <form action="{{route('administracion.cartuchos.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf


                    <div>
                        <x-input-label for="cartucho" :value="__('Consumible:')" />
                        <x-text-input id="area" class="block w-full mt-1" type="text" name="cartucho" :value="old('cartucho')"  autofocus />
                        <x-input-error :messages="$errors->get('cartucho')" class="mt-2" />
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Color:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="colore_id" id="colore_id">
                            <option value="">Seleccione</option>
                            @foreach ($colores as $color)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$color->id}}">{{$color->colore}}</option>

                            @endforeach
                        </select>


                        <x-input-error :messages="$errors->get('colore_id')" class="mt-2" />

                    </div>
                </div>

                <div>
                    <label for="" class="block mt-1 text-black">Impresora:</label>
                    <div class="flex">
                    <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="impresora_id" id="impresora_id">
                        <option value="">Seleccione</option>
                        @foreach ($impresoras as $impresora)
                        <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$impresora->id}}">{{$impresora->impresora}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('impresora_id')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label for="" class="block mt-1 text-black">Fabricante:</label>
                    <div class="flex">
                    <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="fabricante_id" id="fabricante_id">
                        <option value="">Seleccione</option>
                        @foreach ($fabricantes as $fabricante)
                        <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$fabricante->id}}">{{$fabricante->fabricante}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('fabricante_id')" class="mt-2" />
                    </div>
                </div>

                <div class="flex mt-3">



                    <div class="flex">
                    <label for="" class="block text-black mr-3 pt-3 ">Stock Min:</label>
                    <input type="number" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="stock_min" size="100" maxlength="250" id="stock_min" >
                    <x-input-error :messages="$errors->get('stock_min')" class="mt-2" />
                    </div>
                </div>
                    <div class="flex mt-4 justify-evenly">

                        <a class="inline-flex shadow-md items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 " href="{{ route('administracion.cartuchos.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
                        </a>

                        <x-primary-button class="ml-4" onsubmit="return route('administracion.cartuchos.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                        </x-primary-button>
                    </div>


                </form>

            </div>
        </div>
    </section>
    </body>
</html>
@endcan
