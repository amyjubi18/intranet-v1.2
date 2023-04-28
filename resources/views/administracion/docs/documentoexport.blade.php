<table class="table w-full text-black sm:p-4">



    <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
        <tr class="border-4 border-blue-900 ">

            <th class='px-6 py-4 text-center border-4 border-blue-900 '>NúMERO DE EXPEDIENTE</th>
            <th class='px-6 py-4 text-center border-4 border-blue-900 '>DESCRIPCIÓN</th>
            <th class='px-6 py-4 text-center border-4 border-blue-900 '>FECHA</th>
            <th class='px-6 py-4 text-center border-4 border-blue-900 '>DOCUMENTO</th>
            <th class='px-8 py-4 text-center border-4 border-blue-900 '>CATEGORÍA</th>

    </thead >
    <tbody class='py-4 text-center border-4 border-blue-900'>
        @foreach ($documentos as $dato)
                    <tr class='px-6 py-4 my-4 text-black border-4 border-blue-900'>

                        <td class='py-4 text-center border-4 border-blue-900'>{{$dato->name}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'>{{$dato->description}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->fecha}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->documento}}</td>
                        <td class='py-4 text-center border-4 border-blue-900'> {{$dato->categoria->name}}</td>

                @endforeach
                    </tr>
    </tbody>

</table>
