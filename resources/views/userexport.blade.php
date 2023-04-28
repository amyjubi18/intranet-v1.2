<table class="table w-full text-black sm:p-4">



    <thead class='text-sm font-semibold text-white uppercase bg-blue-900 border-4 border-white '>
        <tr class="border-4 border-blue-900 ">

        <th class='px-6 py-4 text-center border-4 border-blue-900 '>NOMBRE</th>
        <th class='px-6 py-4 text-center border-4 border-blue-900 '>CORREO ELECTRÓNICO</th>
        <th class='px-6 py-4 text-center border-4 border-blue-900 '>ROLES</th>
        {{--<th colspan="2" class='px-10 py-4 text-center border-4 border-blue-900 '>OPCIÓN</th> --}}
        </tr>
    </thead >
    <tbody class='py-4 text-center border-4 border-blue-900'>
        @foreach ($users as $user)
            <tr class='px-6 py-4 my-4 text-black border-4 border-blue-900'>

                <td class='py-4 text-center border-4 border-blue-900'>{{$user->name}}</td>
                <td class='py-4 text-center border-4 border-blue-900'>{{$user->email}}</td>
                <td class='py-4 text-center border-4 border-blue-900'>
                    @foreach ($user->roles as $role)
                   {{$role->name}}

                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>

</table>
