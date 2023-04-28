<table class="table w-full text-black sm:p-4">



    <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
        <tr class="border-4 border-blue-900 ">

            <th class='px-6 py-4 text-center border-4 border-blue-900 '>SEDE</th>
            <th class='px-6 py-4 text-center border-4 border-blue-900 '>UNIDAD ADMINISTRATIVA</th>
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

    </thead >
    <tbody class='py-4 text-center border-4 border-blue-900'>
        @foreach ($bienes as $dato)
                        <tr class='px-6 py-4 my-4 text-black border-4 border-blue-900'>

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

                @endforeach
                    </tr>
    </tbody>

</table>
