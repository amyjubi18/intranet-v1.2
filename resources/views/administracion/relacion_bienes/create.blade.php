
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <title>{{ 'Rellenar Formulario' }}</title>

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')
        <title>Rellenar Formulário</title>

    </head>

    <body class="antialiased bg-blue-900" style="background-image: url('/img/fondo-intranet.jpg');background-repeat: no-repeat; background-size: cover; ">
        <section class="flex flex-col items-center justify-center h-screen border-4 border-blue-900 rounded md:flex-row ">
            {{-- <div class="bg-blue-900 h-max lg:block md:w-1/2 xl:w-2/3">
                <img src="/img/fondo-intranet.jpg" class="object-cover w-full h-max ">
            </div> --}}
            <div class="w-full h-full px-6 py-4 mt-6 overflow-y-scroll bg-white sm:max-w-xl sm:rounded-lg ">
                <h1 class="my-1 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Formulario</h1>

                        <div class="items-center justify-center block w-full mb-1 h-220 ">





                <form action="{{route("administracion.relacion_bienes.store")}}" method="POST" enctype="multipart/form-data" class="form">
                    @csrf
                <div class="mt-3">

                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Usuario</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="user_id" id="estado">
                            <option value="">Seleccione</option>
                            @foreach ($users as $user)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$user->id}}">{{$user->name}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                        </div>
                    </div>
                    <div class="mt-3 ">
                        <label for="" class="block text-black">Sede:</label>
                        <input type="text"  value="Codecyt"  id="sede" class="w-full px-4 py-2 mr-2 bg-gray-300 border rounded-lg focus:border-blue-900 focus:bg-gray-300 focus:outline-none active:bg-gray-300" {{-- readonly disabled --}} name="sede"  size="70" maxlength="70" id="" >
                        <x-input-error :messages="$errors->get('sede')" class="mt-2" />
                        </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Unidad Administrativa:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="unidad_administrativa_id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($unidad_administrativas as $unidad_administrativa)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$unidad_administrativa->id}}">{{$unidad_administrativa->unidad_administrativa}}</option>

                            @endforeach
                        </select>


                        <x-input-error :messages="$errors->get('unidad_administrativa_id')" class="mt-2" />

                        </div>
                    </div>


                    <div>
                        <label for="" class="block mt-1 text-black">N° interno de bien:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($bienes as $biene)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$biene->id}}">{{$biene->n_interno_bien}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id')" class="mt-2" />

                        </div>
                    </div>


                    <div>
                        <label for="" class="block mt-1 text-black">Descripción:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($bienes as $biene)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$biene->id}}">{{$biene->descripcion}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id')" class="mt-2" />

                        </div>
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Forma de Adquisición</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="forma_adquisicion_id" id="forma-adqui">
                            <option value=""> Seleccione</option>
                            @foreach ($forma_adquisicions as $forma_adquisicion)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$forma_adquisicion->id}}">{{$forma_adquisicion->forma_adquisicion}}</option>

                            @endforeach
                        </select>

                        

                        <x-input-error :messages="$errors->get('forma_adquisicion_id')" class="mt-2" />

                    </div>
                </div>
                    <div>
                    <label for="" class="block mt-2 text-black">Fecha de adquisición:</label>
                    <input datepicker datepicker-formart="dd/mm/yyyy" type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 "  name="fecha_adquisicion" size="100" maxlength="250" id="fecha" required >
                        <x-input-error :messages="$errors->get('fecha_adquisicion')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                    <label for="" class="block text-black">N° de factura:</label>
                    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="n_factura" size="100" maxlength="250" id="n-factura" required>
                    <x-input-error :messages="$errors->get('n_factura')" class="mt-2" />
                    </div>
                    <div class="mt-3">
                    <label for="" class="block text-black">Valor de factura:</label>
                    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="valor_factura" size="100" maxlength="250" id="valor-factura" required>
                    <x-input-error :messages="$errors->get('valor_factura')" class="mt-2" />
                    </div>
                    <div class="mt-3 ">
                    <label for="" class="block text-black">Moneda:</label>
                    <input type="text" value="Bolívares"  class="w-full px-4 py-2 mr-2 bg-gray-300 border rounded-lg focus:border-blue-900 focus:bg-gray-300 focus:outline-none active:bg-gray-300" {{-- readonly --}}  name="moneda"  size="70" maxlength="70" id="" {{-- disabled --}} >
                    <x-input-error :messages="$errors->get('moneda')" class="mt-2" />
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Estado del uso</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="estado_del_uso_id" id="estado">
                            <option value="">Seleccione</option>
                            @foreach ($estado_del_usos as $estado_del_uso)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$estado_del_uso->id}}">{{$estado_del_uso->estado_del_uso}}</option>

                            @endforeach
                        </select>

                        <button class="px-4 py-3 mx-4 text-base font-semibold text-white bg-blue-900 rounded-lg w-fit w-30 hover:bg-blue-700" name="submit" type="submit">

                            <a href="{{route ('administracion.estado_del_uso.create')}}" class="flex text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M2.165 19.551c.186.28.499.449.835.449h15c.4 0 .762-.238.919-.606l3-7A.998.998 0 0 0 21 11h-1V8c0-1.103-.897-2-2-2h-6.655L8.789 4H4c-1.103 0-2 .897-2 2v13h.007a1 1 0 0 0 .158.551zM18 8v3H6c-.4 0-.762.238-.919.606L4 14.129V8h14z"></path></svg>Nuevo Estado del Uso</a>
                            </button>

                        <x-input-error :messages="$errors->get('estado_del_uso_id')" class="mt-2" />

                    </div>
                </div>



                    <div class="mt-3">
                    <label for="" class="block text-black">Condición Física:</label>
                    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="condicion_fisica" id="condicion" size="100" maxlength="250" id="" required>
                    <x-input-error :messages="$errors->get('condicion_fisica')" class="mt-2" />
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Marca:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($bienes as $biene)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$biene->id}}">{{$biene->marca}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id')" class="mt-2" />

                        </div>
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Modelo:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($bienes as $biene)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$biene->id}}">{{$biene->modelo}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id')" class="mt-2" />

                        </div>
                    </div>

                    <div>
                        <label for="" class="block mt-1 text-black">Serial:</label>
                        <div class="flex">
                        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="id" id="unidad-admin"  >
                            <option value="">Seleccione</option>
                            @foreach ($bienes as $biene)
                            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$biene->id}}">{{$biene->serial}}</option>

                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id')" class="mt-2" />

                        </div>
                    </div>








                </div>
                    <div class="flex mt-4 justify-evenly">

                        <a class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-900 border border-transparent rounded-md hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 " href="{{ route('administracion.bienes_publicos.index') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
                        </a>

                        <x-primary-button class="ml-4" onsubmit="return route('administracion.docs.index')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg> {{ __('Guardar') }}
                        </x-primary-button>
                    </div>

                </form>
                </div>

                {{-- EDITOR EN TIEMPO REAL --}}

                <div class="w-full h-full px-6 py-4 mt-6 ml-3 overflow-y-scroll text-center bg-white sm:max-w-xl sm:rounded-lg">
                    <h1 class="my-1 mb-3 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Visualizar</h1>


                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Unidad Administrativa: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-unidad"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">N° interno de bien: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-n-interno"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Descripción: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-descripcion"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Forma de Adquisición: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-forma"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Fecha de Adquisición: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-fecha"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">N° de Factura: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-n-factura"> </p>

                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Valor de Factura: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-valor-factura"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Moneda: </h4>
                        <p class="text-center underline decoration-solid" >Bolívares </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Estado del Uso: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-estado"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Condición Física: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-condicion"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Estado del Uso: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-estado"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Marca: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-marca"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Modelo: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-modelo"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Serial: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-serial"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Categoría General: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-categoria-general"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Subcategoría: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-subcategoria"> </p>
                    </div>
                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Categoría Específica: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-categoria-especifica"> </p>
                    </div>

                    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
                        <h4 class="ml-3 mr-2 font-medium text-white">Código Contable: </h4>
                        <p class="text-center underline decoration-solid" id="mostrar-codigo"> </p>
                    </div>





                </div>
                {{-- --------------------------------- --}}
                            </div>
                    </div>
                    </div>


            </div>
            </div>
        </section>
        <script>
            /* const inputsede = document.getElementById('sede');
            const mostrarsede = document.querySelector('#mostrar-sede') */
            const selectunidad = document.getElementById('unidad-admin');
            const  mostrarunidad = document.querySelector('#mostrar-unidad');
            const inputnumerointerno = document.querySelector('#n-interno');
            const mostrarnumerointerno = document.querySelector('#mostrar-n-interno');
            const inputdescripcion = document.querySelector('#descripcion');
            const mostrardescripcion = document.querySelector('#mostrar-descripcion');
            const selectforma = document.getElementById('forma-adqui');
            const  mostrarforma = document.querySelector('#mostrar-forma');
            const inputfecha = document.querySelector('#fecha');
            const mostrarfecha = document.querySelector('#mostrar-fecha');
            const inputnumerofactura = document.querySelector('#n-factura');
            const mostrarnumerofactura = document.querySelector('#mostrar-n-factura');
            const inputvalorfactura = document.querySelector('#valor-factura');
            const mostrarvalorfactura = document.querySelector('#mostrar-valor-factura');
            const selectestado = document.getElementById('estado');
            const  mostrarestado = document.querySelector('#mostrar-estado');
            const inputcondicion = document.querySelector('#condicion');
            const mostrarcondicion = document.querySelector('#mostrar-condicion');
            const inputmarca = document.querySelector('#marca');
            const mostrarmarca = document.querySelector('#mostrar-marca');
            const inputmodelo = document.querySelector('#modelo');
            const mostrarmodelo = document.querySelector('#mostrar-modelo');
            const inputserial = document.querySelector('#serial');
            const mostrarserial = document.querySelector('#mostrar-serial');
            const selectcategoriageneral = document.getElementById('categoria-general');
            const  mostrarcategoriageneral = document.querySelector('#mostrar-categoria-general');
            const selectsubcategoria = document.getElementById('sub-categoria');
            const  mostrarsubcategoria = document.querySelector('#mostrar-subcategoria');
            const selectcategoriaespecifica = document.getElementById('categoria-especifica');
            const  mostrarcategoriaespecifica = document.querySelector('#mostrar-categoria-especifica');
            const inputcodigo = document.querySelector('#codigo-contable');
            const mostrarcodigo = document.querySelector('#mostrar-codigo');

            /* inputsede.addEventListener("change", () => {
            mostrarsede.innerText = inputsede.value;
            }) */
            selectunidad.addEventListener("change", function handleChange(event) {
                mostrarunidad.innerText = selectunidad.options[selectunidad.selectedIndex].text;
            })
            inputnumerointerno.addEventListener("change", () => {
            mostrarnumerointerno.innerText = inputnumerointerno.value;
            })
            inputdescripcion.addEventListener("change", () => {
            mostrardescripcion.innerText = inputdescripcion.value;
            })
            selectforma.addEventListener("change", function handleChange(event) {
                mostrarforma.innerText = selectforma.options[selectforma.selectedIndex].text;
            })
            inputfecha.addEventListener("change", () => {
            mostrarfecha.innerText = inputfecha.value;
            })
            inputnumerofactura.addEventListener("change", () => {
            mostrarnumerofactura.innerText = inputnumerofactura.value;
            })
            inputvalorfactura.addEventListener("change", () => {
            mostrarvalorfactura.innerText = inputvalorfactura.value;
            })
            selectestado.addEventListener("change", function handleChange(event) {
                mostrarestado.innerText = selectestado.options[selectestado.selectedIndex].text;
            })
            inputcondicion.addEventListener("change", () => {
            mostrarcondicion.innerText = inputcondicion.value;
            })
            inputmarca.addEventListener("change", () => {
            mostrarmarca.innerText = inputmarca.value;
            })
            inputmodelo.addEventListener("change", () => {
            mostrarmodelo.innerText = inputmodelo.value;
            })
            inputserial.addEventListener("change", () => {
            mostrarserial.innerText = inputserial.value;
            })
            selectcategoriageneral.addEventListener("change", function handleChange(event) {
                mostrarcategoriageneral.innerText = selectcategoriageneral.options[selectcategoriageneral.selectedIndex].text;
            })
            selectsubcategoria.addEventListener("change", function handleChange(event) {
                mostrarsubcategoria.innerText = selectsubcategoria.options[selectsubcategoria.selectedIndex].text;
            })
            selectcategoriaespecifica.addEventListener("change", function handleChange(event) {
                mostrarcategoriaespecifica.innerText = selectcategoriaespecifica.options[selectcategoriaespecifica.selectedIndex].text;
            })
            inputcodigo.addEventListener("change", () => {
            mostrarcodigo.innerText = inputcodigo.value;
            })
        </script>
    </body>
</html>
