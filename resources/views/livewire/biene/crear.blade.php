<div class="w-full h-full px-6 py-4 mt-6 overflow-y-scroll bg-white sm:max-w-xl sm:rounded-lg ">
    <h1 class="my-1 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Formulario</h1>

    <form wire:submit.prevent="submit" class="">
    @csrf


    <div class="mt-3">
    <label for="" class="block text-black bold"> Sede:</label>
    <input type="text" id="sede" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="sede" size="100" maxlength="250" required wire:model="sede">
    <x-input-error :messages="$errors->get('sede')" class="mt-2" />
    </div>

    <div>
        <label for="" class="block mt-1 text-black">Unidad Administrativa</label>
        <div class="flex">
            <select wire:model="unidad_administrativa_id" id="unidad-administrativa" class="block w-full px-4 py-3 mt-2 border border-blue-900 rounded-lg" name="unidad_administrativa_id">
                <option value="">Seleccione</option>
                @foreach ($unidad_administrativas as $unidad)
                    <option value="{{ $unidad->id }}">{{ $unidad->name }}</option>
                @endforeach
            </select>


        <x-input-error :messages="$errors->get('unidad_administrativa_id')" class="mt-2" />

    </div>
</div>


    <div>
        <label for="" class="block mt-1 text-black">Usuario Administrativo</label>
        <div class="flex">
        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blue-500 " name="usuario_administrativo_id" id="usuario-administrativo"  wire:model="usuario_administrativo_id">
            <option value="">Seleccione</option>
            @foreach ($usuario_administrativos as $usuario_administrativo)
            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"  value="{{$usuario_administrativo->id}}">{{$usuario_administrativo->usuario_administrativo }} -{{$usuario_administrativo->unidad_administrativa->name}}</option>

            @endforeach
        </select>


        <x-input-error :messages="$errors->get('usuario_administrativo_id')" class="mt-2" />

    </div>
</div>

<div>
        <label for="" class="block mt-1 text-black">Usuario de Uso</label>
        <div class="flex">
            <select wire:model="usuario_de_uso_id" id="usuario-de-uso" class="block w-full px-4 py-3 mt-2 border border-blue-900 rounded-lg" name="usuario_de_uso_id">
                <option value="">Seleccione</option>
                @foreach ($filtered_usuario_de_usos as $usuario)
                    <option value="{{ $usuario->id }}">{{ $usuario->usuario_de_uso }}</option>
                @endforeach
            </select>

        <x-input-error :messages="$errors->get('usuario_de_uso_id')" class="mt-2" />

    </div>
</div>




    <div class="mt-3">
    <label for="" class="block text-black"> Código interno del bien:</label>
    <input type="text" id="n-interno" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="n_interno_bien" size="100" maxlength="250" required wire:model.lazy="n_interno_bien">
    <x-input-error :messages="$errors->get('n_interno_bien')" class="mt-2" />
    </div>
    <div class="mt-3">
    <label for="" class="block text-black">Descripción:</label>

    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="descripcion" size="100" maxlength="250" id="descripcion" required wire:model="descripcion">
    <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
    </div>


    <div>
        <label for="" class="block mt-1 text-black">Forma de Adquisición</label>
        <div class="flex">
        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="forma_adquisicion_id" id="forma-adqui" wire:model="forma_adquisicion_id">
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
    <input datepicker datepicker-formart="dd/mm/yyyy" type="date" class=" mt-3 border border-blue-900  rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 "  name="fecha_adquisicion" size="100" maxlength="250" id="fecha-adquisicion" required wire:model="fecha_adquisicion">
        <x-input-error :messages="$errors->get('fecha_adquisicion')" class="mt-2" />
    </div>
    <div class="mt-3">
    <label for="" class="block text-black">N° Documento:</label>
    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="n_factura" size="100" maxlength="250" id="n-factura" required wire:model="n_factura">
    <x-input-error :messages="$errors->get('n_factura')" class="mt-2" />
    </div>
    <div class="mt-3">
    <label for="" class="block text-black">Valor Adquisición:</label>
    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="valor_factura" size="100" maxlength="250" id="valor-factura" required wire:model="valor_factura">
    <x-input-error :messages="$errors->get('valor_factura')" class="mt-2" />
    </div>
    <div class="mt-3 ">
    <label for="" class="block text-black">Moneda:</label>
    <input type="text" value="BOLÍVAR"  class="w-full px-4 py-2 mr-2  border rounded-lg focus:border-blue-900 focus:bg-gray-300 focus:outline-none " {{-- readonly --}}  name="moneda"  size="70" maxlength="70" id="moneda" {{-- disabled --}} wire:model="moneda">
    <x-input-error :messages="$errors->get('moneda')" class="mt-2" />
    </div>

    <div>
        <label for="" class="block mt-1 text-black">Estado del uso</label>
        <div class="flex">
        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blie-500 " name="estado_del_uso_id" id="estado" wire:model="estado_del_uso_id">
            <option value="">Seleccione</option>
            @foreach ($estado_del_usos as $estado_del_uso)
            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$estado_del_uso->id}}">{{$estado_del_uso->estado_del_uso}}</option>

            @endforeach
        </select>


        <x-input-error :messages="$errors->get('estado_del_uso_id')" class="mt-2" />

    </div>
</div>



<div>
        <label for="" class="block mt-1 text-black">Condición Física</label>
        <div class="flex">
        <select  class="block w-full px-4 py-3 pr-8 mt-2 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blue-500 " name="condicion_id" id="condicion" wire:model="condicion_id">
            <option value="">Seleccione</option>
            @foreach ($condicions as $condicion)
            <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2" value="{{$condicion->id}}">{{$condicion->condicion}}</option>

            @endforeach
        </select>


        <x-input-error :messages="$errors->get('condicion_id')" class="mt-2" />

        </div>
    </div>



    <div class="mt-3">
    <label for="" class="block text-black">Marca:</label>
    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="marca" size="100" maxlength="250" id="marca" required wire:model="marca">
    <x-input-error :messages="$errors->get('marca')" class="mt-2" />
    </div>
    <div class="mt-3">
    <label for="" class="block text-black">Modelo:</label>
    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="modelo" size="100" maxlength="250" id="modelo" required wire:model="modelo">
    <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
    </div>


    <div>
        <label for="" class="block mt-1 text-black font-semibold">Color:</label>
        <div class="flex items-center gap-2 mt-2">
            <span id="livewireColorPreview"
                  class="w-10 h-10 rounded-xl border-2 border-white ring-2 ring-gray-300 shadow-sm flex-shrink-0 transition-colors"
                  style="background-color: #cccccc;"
                  title="Muestra del color"></span>
            <select class="block w-full px-4 py-3 pr-8 leading-tight bg-white border border-blue-900 rounded-lg shadow hover:border-blue-500 font-medium"
                    name="color_id"
                    id="color"
                    wire:model="color_id"
                    onchange="const opt = this.options[this.selectedIndex]; document.getElementById('livewireColorPreview').style.backgroundColor = (opt ? opt.getAttribute('data-color') : '#cccccc') || '#cccccc';">
                <option value="" data-color="#cccccc">Seleccione</option>
                @foreach ($colors as $color)
                <option class="px-4 py-3 pr-8 hover:bg-gray-200 hover:px-2"
                        value="{{$color->id}}"
                        data-color="{{ $color->color_css }}">
                    {{$color->color}}
                </option>
                @endforeach
            </select>
        </div>
        <x-input-error :messages="$errors->get('color_id')" class="mt-2" />
    </div>




    <div class="mt-3">
    <label for="" class="block text-black">Serial:</label>
    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 mb-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="serial" size="100" maxlength="250" id="serial" required wire:model="serial">
    <x-input-error :messages="$errors->get('serial')" class="mt-2" />
    </div>



    <div>
    <label for="" class="block text-black">Comentarios:</label>



    <input type="text" placeholder="" class="w-full px-4 py-2 mt-2 mb-2 bg-white border rounded-lg focus:border-blue-900 focus:bg-white focus:outline-none" name="comentario" size="100" maxlength="250" id="comentario" required wire:model="comentario">
    <x-input-error :messages="$errors->get('comentario')" class="mt-2" />
    </div>
    <div>
    <label for="" class=" text-black">Fecha de comentario:</label>
    <input datepicker datepicker-formart="dd/mm/yyyy" type="date" class=" mt-3 border border-blue-900   rounded-lg cursor-pointer  text-gray-900 text-sm rounded-lg focus:ring-blue-900 focus:border-blue-500 block w-full pl-3 p-2.5 "  name="fecha_comentario" size="100" maxlength="250" id="fecha-comentario" required wire:model="fecha_comentario">
        <x-input-error :messages="$errors->get('fecha_comentario')" class="mt-2" />
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

<div name="visualizar" class="visualizar w-full h-full px-6 py-4 mt-6 ml-3 overflow-y-scroll text-center bg-white sm:max-w-xl sm:rounded-lg">
    <h1 class="my-1 mb-3 text-4xl font-bold text-center text-blue-900" style="padding-top: -10px">Visualizar</h1>


    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white text-bold">Sede: </h4>
        <p  class="text-center underline decoration-solid" id="mostrar-sede"> </p>
    </div>

    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Unidad Administrativa: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-unidad"> </p>
    </div>

    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Usuario Administrativo: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-usuarioadmin"> </p>
    </div>

    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Usuario de Uso: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-usuario"> </p>
    </div>
    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Código interno del bien: </h4>
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
        <h4 class="ml-3 mr-2 font-medium text-white">N° documento: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-n-factura"> </p>

    </div>
    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Valor Adquisición: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-valor-factura"> </p>
    </div>
    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Moneda: </h4>
        <p class="text-center underline decoration-solid" >BOLÍVAR </p>
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
        <h4 class="ml-3 mr-2 font-medium text-white">Marca: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-marca"> </p>
    </div>
    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Modelo: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-modelo"> </p>
    </div>

    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Color: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-color"> </p>
    </div>


    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Serial: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-serial"> </p>
    </div>

    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Comentarios: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-comentario"> </p>
    </div>
    <div class="flex p-4 mt-3 text-white bg-gray-500 rounded-lg shadow-lg shadow-blue-200/50">
        <h4 class="ml-3 mr-2 font-medium text-white">Fecha de Comentario: </h4>
        <p class="text-center underline decoration-solid" id="mostrar-fecha-comentario"> </p>
    </div>







</div>

{{-- --------------------------------- --}}


<script>
const inputsede = document.getElementById('sede');
const mostrarsede = document.querySelector('#mostrar-sede') ;
const selectunidad = document.getElementById('unidad-administrativa');
const  mostrarunidad = document.querySelector('#mostrar-unidad');

const selectusuarioadmin = document.getElementById('usuario-administrativo');
const  mostrarusuarioadmin = document.querySelector('#mostrar-usuarioadmin');

const selectusuario = document.getElementById('usuario-de-uso');
const  mostrarusuario = document.querySelector('#mostrar-usuario');
const inputnumerointerno = document.querySelector('#n-interno');
const mostrarnumerointerno = document.querySelector('#mostrar-n-interno');
const inputdescripcion = document.querySelector('#descripcion');
const mostrardescripcion = document.querySelector('#mostrar-descripcion');
const selectforma = document.getElementById('forma-adqui');
const  mostrarforma = document.querySelector('#mostrar-forma');

const inputfechaad = document.querySelector('#fecha-aquisicion');
const mostrarfechaad = document.querySelector('#mostrar-fecha');

const inputnumerofactura = document.querySelector('#n-factura');
const mostrarnumerofactura = document.querySelector('#mostrar-n-factura');
const inputvalorfactura = document.querySelector('#valor-factura');
const mostrarvalorfactura = document.querySelector('#mostrar-valor-factura');
const selectestado = document.getElementById('estado');
const  mostrarestado = document.querySelector('#mostrar-estado');
const selectcondicion = document.getElementById('condicion');
const  mostrarcondicion = document.querySelector('#mostrar-condicion');
const inputmarca = document.querySelector('#marca');
const mostrarmarca = document.querySelector('#mostrar-marca');
const inputmodelo = document.querySelector('#modelo');
const mostrarmodelo = document.querySelector('#mostrar-modelo');
const selectcolor = document.getElementById('color');
const  mostrarcolor = document.querySelector('#mostrar-color');
const inputserial = document.querySelector('#serial');
const mostrarserial = document.querySelector('#mostrar-serial');
const inputcomentario = document.querySelector('#comentario');
const mostrarcomentario = document.querySelector('#mostrar-comentario');
const inputfechacomentario = document.querySelector('#fecha-comentario');
const mostrarfechacomentario = document.querySelector('#mostrar-fecha-comentario');

/* inputsede.addEventListener("change", () => {
mostrarsede.innerText = inputsede.value;
}) */

inputsede.addEventListener("change", () => {
mostrarsede.innerText = inputsede.value;
})

selectunidad.addEventListener("change", function handleChange(event) {
mostrarunidad.innerText = selectunidad.options[selectunidad.selectedIndex].text;
})
selectusuarioadmin.addEventListener("change", function handleChange(event) {
mostrarusuarioadmin.innerText = selectusuarioadmin.options[selectusuarioadmin.selectedIndex].text;
})
selectusuario.addEventListener("change", function handleChange(event) {
mostrarusuario.innerText = selectusuario.options[selectusuario.selectedIndex].text;
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
/* inputfechaad.addEventListener("change", () => {
mostrarfechaad.innerText = inputfechaad.value;
}) */
inputnumerofactura.addEventListener("change", () => {
mostrarnumerofactura.innerText = inputnumerofactura.value;
})
inputvalorfactura.addEventListener("change", () => {
mostrarvalorfactura.innerText = inputvalorfactura.value;
})
selectestado.addEventListener("change", function handleChange(event) {
mostrarestado.innerText = selectestado.options[selectestado.selectedIndex].text;
})
selectcondicion.addEventListener("change", function handleChange(event) {
mostrarcondicion.innerText = selectcondicion.options[selectcondicion.selectedIndex].text;
})
inputmarca.addEventListener("change", () => {
mostrarmarca.innerText = inputmarca.value;
})
inputmodelo.addEventListener("change", () => {
mostrarmodelo.innerText = inputmodelo.value;
})
selectcolor.addEventListener("change", function handleChange(event) {
mostrarcolor.innerText = selectcolor.options[selectcolor.selectedIndex].text;
})
inputserial.addEventListener("change", () => {
mostrarserial.innerText = inputserial.value;
})

inputcomentario.addEventListener("change", () => {
mostrarcomentario.innerText = inputcomentario.value;
})
inputfechacomentario.addEventListener("change", () => {
mostrarfechacomentario.innerText = inputfechacomentario.value;
})

</script>
