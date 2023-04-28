@can('bienes_edit')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <title>{{ 'Editar Formulario' }}</title>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')

    </head>
    <body class="antialiased bg-blue-900">
        <section class="flex flex-col items-center h-screen border-4 border-blue-900 rounded md:flex-row ">
            <div class="bg-blue-900 h-max lg:block md:w-1/2 xl:w-2/3">
                <img src="/img/fondo-intranet.jpg" class="object-cover w-full h-max ">
            </div>
            <div class="w-full h-full px-6 py-4 mt-6 overflow-y-scroll bg-white sm:max-w-md sm:rounded-lg">
                <h1 class="my-1 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Editar</h1>

                        <div class="items-center justify-center block w-full mb-1 h-220 ">


                <form action="{{route('administracion.bienes_publicos.update', $biene->id)}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mt-3">
                                    <label for="" class="block text-black">Sede:</label>
                        <input type="text" placeholder="Ingresa el Nombre del Archivo" class="w-full px-4 py-2 mr-2 bg-gray-300 border rounded-lg focus:border-blue-900 focus:bg-gray-300 focus:outline-none active:bg-gray-300"  name="sede"  size="70" maxlength="70" value="{{old('sede', $biene->sede)}}" disabled id="">
                        <x-input-error :messages="$errors->get('sede')" class="mt-2" />
                    </div>

                     <div class="flex mt-2">

                        <label for="" class="block mr-2 text-black">Unidad Administrativa:</label>
                        <select name="unidad_administrativa" id="unidad_administrativa" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                            @foreach ($unidad_administrativas as  $unidad_administrativa)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$unidad_administrativa->id}}" @if ($unidad_administrativa->id == $biene->unidad_administrativa_id)
                                {{'selected'}}
                            @endif>{{$unidad_administrativa->unidad_administrativa}}</option>

                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3">
                    <label for="" class="block text-black">N° Interno del bien:</label>
                    <input type="text" class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="n_interno_bien"  size="70" maxlength="70" value="{{old('n_interno_bien', $biene->n_interno_bien)}}" id="">
                    <x-input-error :messages="$errors->get('n_interno_bien')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                    <label for="" class="block text-black">Descripción:</label>
                    <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="descripcion"  size="70" maxlength="70" value="{{old('descripcion', $biene->descripcion)}}" id="">
                    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>
                    <div class="flex mt-2">

                        <label for="" class="block mr-2 text-black">Forma de Adquisición:</label>
                        <select name="forma_adquisicion" id="forma_adquisicion" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                            @foreach ($forma_adquisicions as  $forma_adquisicion)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$forma_adquisicion->id}}" @if ($forma_adquisicion->id == $biene->forma_adquisicion_id)
                                {{'selected'}}
                            @endif>{{$forma_adquisicion->forma_adquisicion}}</option>

                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="" class="block mt-2 text-black">Fecha de adquisición:</label>
                        <input type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 "  name="fecha_adquisicion" size="100" maxlength="250" id="" value="{{old('fecha_adquisicion', $biene->fecha_adquisicion)}}">
                            <x-input-error :messages="$errors->get('fecha_adquisicion')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">N° de Factura:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="n_factura"  size="70" maxlength="70" value="{{old('n_factura', $biene->n_factura)}}" id="">
                            <x-input-error :messages="$errors->get('n_factura')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Valor de Factura:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="valor_factura"  size="70" maxlength="70" value="{{old('valor_factura', $biene->valor_factura)}}" id="">
                            <x-input-error :messages="$errors->get('valor_factura')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Moneda:</label>
                            <input type="text"  class="w-full px-4 py-2 mr-2 bg-gray-300 border rounded-lg focus:border-blue-900 focus:bg-gray-300 focus:outline-none active:bg-gray-300" disabled  name="moneda"  size="70" maxlength="70" value="{{old('moneda', $biene->moneda)}}" id="">
                            <x-input-error :messages="$errors->get('moneda')" class="mt-2" />
                        </div>
                        <div class="flex mt-2">

                            <label for="" class="block mr-2 text-black">Estado del Uso:</label>
                            <select name="estado_del_uso" id="estado_del_uso" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                                @foreach ($estado_del_usos as  $estado_del_uso)
                                <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$estado_del_uso->id}}" @if ($estado_del_uso->id == $biene->estado_del_uso_id)
                                    {{'selected'}}
                                @endif>{{$estado_del_uso->estado_del_uso}}</option>

                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Condición Física:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="condicion_fisica"  size="70" maxlength="70" value="{{old('condicion_fisica', $biene->condicion_fisica)}}" id="">
                            <x-input-error :messages="$errors->get('condicion_fisica')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Marca:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="marca"  size="70" maxlength="70" value="{{old('marca', $biene->marca)}}" id="">
                            <x-input-error :messages="$errors->get('marca')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Modelo:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="modelo"  size="70" maxlength="70" value="{{old('modelo', $biene->modelo)}}" id="">
                            <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                        </div>
                        <div class="mt-3">
                            <label for="" class="block text-black">Serial:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="serial"  size="70" maxlength="70" value="{{old('serial', $biene->serial)}}" id="">
                            <x-input-error :messages="$errors->get('serial')" class="mt-2" />
                        </div>
                        <div class="flex mt-2">

                            <label for="" class="block mr-2 text-black">Categoría General:</label>
                            <select name="categoria_general" id="categoria_general" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                                @foreach ($categoria_generals as  $categoria_general)
                                <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$categoria_general->id}}" @if ($categoria_general->id == $biene->categoria_general_id)
                                    {{'selected'}}
                                @endif>{{$categoria_general->categoria_general}}</option>

                                @endforeach
                            </select>
                        </div>
                        <div class="flex mt-2">

                            <label for="" class="block mr-1 mr-2 text-black">Subcategoría: </label>
                            <select name="sub_categoria" id="sub_categoria" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                                @foreach ($sub_categorias as  $sub_categoria)
                                <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$sub_categoria->id}}" @if ($sub_categoria->id == $biene->sub_categoria_id)
                                    {{'selected'}}
                                @endif>{{$sub_categoria->sub_categoria}}</option>

                                @endforeach
                            </select>
                        </div>
                        <div class="flex mt-2">

                            <label for="" class="block mr-2 text-black">Categoría Específica:</label>
                            <select name="categoria_especifica" id="categoria_especifica" class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-black rounded-lg shadow hover:border-gray-500 ">
                                @foreach ($categoria_especificas as  $categoria_especifica)
                                <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$categoria_especifica->id}}" @if ($categoria_especifica->id == $biene->categoria_especifica_id)
                                    {{'selected'}}
                                @endif>{{$categoria_especifica->categoria_especifica}}</option>

                                @endforeach
                            </select>
                        </div>


                        <div class="mt-3">
                            <label for="" class="block text-black">Código Contable:</label>
                            <input type="text"  class="w-full px-4 py-3 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none"  name="codigo_contable"  size="70" maxlength="70" value="{{old('codigo_contable', $biene->codigo_contable)}}" id="">
                            <x-input-error :messages="$errors->get('codigo_contable')" class="mt-2" />
                        </div>
                    <div class="flex mt-4 justify-evenly">

                        <a class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 " href="{{ route('administracion.bienes_publicos.index') }}">
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
