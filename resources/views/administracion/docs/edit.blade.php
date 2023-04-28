@can('documentos_edit')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <title>{{ 'Editar Documento' }}</title>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')

    </head>
    <body class="font-sans antialiased text-gray-900 ">
        {{-- <a href="{{'/administracion/docs/create'}}"></a> --}}

        <section class="flex flex-col items-center h-screen border-4 border-blue-900 rounded md:flex-row ">
            <div class="hidden h-screen bg-blue-600 lg:block md:w-1/2 xl:w-2/3">
                <img src="/img/fondo-intranet.jpg" class="object-cover w-full h-full" alt="">
            </div>
            <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white sm:max-w-md sm:rounded-lg">
                <h1 class="my-1 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Editar Documento</h1>

                        <div class="items-center justify-center w-full mb-1 h-200 touch-none">


                <form action="{{route('administracion.docs.update', $documento->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                <div class="mt-3">
                        <a type="button" href="{{$documento->documento}}" target="_blank" class="inline-flex items-center px-4 py-2 mb-4 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M8.267 14.68c-.184 0-.308.018-.372.036v1.178c.076.018.171.023.302.023.479 0 .774-.242.774-.651 0-.366-.254-.586-.704-.586zm3.487.012c-.2 0-.33.018-.407.036v2.61c.077.018.201.018.313.018.817.006 1.349-.444 1.349-1.396.006-.83-.479-1.268-1.255-1.268z"></path><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9.498 16.19c-.309.29-.765.42-1.296.42a2.23 2.23 0 0 1-.308-.018v1.426H7v-3.936A7.558 7.558 0 0 1 8.219 14c.557 0 .953.106 1.22.319.254.202.426.533.426.923-.001.392-.131.723-.367.948zm3.807 1.355c-.42.349-1.059.515-1.84.515-.468 0-.799-.03-1.024-.06v-3.917A7.947 7.947 0 0 1 11.66 14c.757 0 1.249.136 1.633.426.415.308.675.799.675 1.504 0 .763-.279 1.29-.663 1.615zM17 14.77h-1.532v.911H16.9v.734h-1.432v1.604h-.906V14.03H17v.74zM14 9h-1V4l5 5h-4z"></path></svg> Abrir documento
                        </a>


                        <label class="block text-black" for="default_size">Actualizar Documento</label>
                        <input class="block w-full text-lg bg-white border border-blue-900 rounded-lg cursor-pointer placeholder:py-3 dark:text-gray-400 focus:outline-none 0" type="file" name="pdf"  size="150" accept=".pdf" maxlength="150" id="" value="{{old('documento', $documento->documento)}}" >
                    </div>
                    <div class="mt-3">
                                    <label for="" class="block text-black">Número del Expediente:</label>
                        <input type="text" placeholder="Ingresa el Nombre del Archivo" class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="name"  size="70" maxlength="70" value="{{old('name', $documento->name)}}" id="" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                                    <label for="" class="block text-black">Description del Archivo:</label>
                        <input type="text" placeholder="Ingresa la descripción del Archivo" class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="description" size="100" maxlength="250" id="" value="{{old('description', $documento->description)}}" required>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div>
                    <label for="" class="block mt-2 text-black">Fecha del Documento: </label>
                        <input type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 " value="{{old('fecha', $documento->fecha)}}"  name="fecha" size="100" maxlength="250" id="" required>
                        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                    </div>
                    <div>
                        {{-- <input type="text" name="" id="" value="{{old('fecha', $documento->categoria)}}"> --}}¨



                            <div class="flex mt-1">

                                <label for="" class="block pb-2 mr-2 text-black">Categorías:</label>
                                <select name="categoria" id="categoria" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                                    @foreach ($categorias as  $categoria)
                                    <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$categoria->id}}" @if ($categoria->id == $documento->categoria_id)
                                        {{'selected'}}
                                    @endif>{{$categoria->name}}</option>

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

                        <x-primary-button class="ml-4" onsubmit="return confirm('¿Los datos son correctos?'); route('administracion.docs.index')">
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
