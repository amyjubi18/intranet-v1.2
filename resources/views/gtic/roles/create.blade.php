<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{'Crear Rol'}}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 ">
        <section class="flex flex-col items-center h-full border-4 border-blue-900 rounded md:flex-row ">
            <div class="hidden h-full bg-blue-600 lg:block md:w-1/2 xl:w-2/3">
                <img src="/img/FONDO-INTRANET-alargado.jpg" class="object-contain w-full h-full" alt="">
            </div>

            <div class="w-full px-6 py-4 mt-2 overflow-hidden bg-white sm:max-w-md sm:rounded-lg">

                <h1 class="py-2 pb-0 text-3xl font-bold text-center text-blue-900">Crea un Rol</h1>
    <form method="POST" action="{{ route('gtic.roles.store') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre del Rol:')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="name" :value="__('Elegir Permisos:')" />
        <table>
            <tbody>
                @foreach ($permissions as $id => $permission)



            <tr>
                <td>
                    <div>

                        <label>
                            <input type="checkbox" name="permissions[]" value="{{$id}}" id="">
                            <span>
                                <span></span>
                            </span>
                        </label>
                    </div>
                </td>
                <td>{{$permission}}</td>
            </tr>
            @endforeach
        </tbody>
        </table>

        </div>
        <div class="flex justify-evenly mt-4">

            <a class="inline-flex items-center px-4 py-2 bg-blue-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 " href="{{ route('gtic.permisos.index') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M11 7.05V4a1 1 0 0 0-1-1 1 1 0 0 0-.7.29l-7 7a1 1 0 0 0 0 1.42l7 7A1 1 0 0 0 11 18v-3.1h.85a10.89 10.89 0 0 1 8.36 3.72 1 1 0 0 0 1.11.35A1 1 0 0 0 22 18c0-9.12-8.08-10.68-11-10.95zm.85 5.83a14.74 14.74 0 0 0-2 .13A1 1 0 0 0 9 14v1.59L4.42 11 9 6.41V8a1 1 0 0 0 1 1c.91 0 8.11.2 9.67 6.43a13.07 13.07 0 0 0-7.82-2.55z"></path></svg>{{ __('Regresar') }}
            </a>

            <x-primary-button class="ml-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M5 21h14a2 2 0 0 0 2-2V8a1 1 0 0 0-.29-.71l-4-4A1 1 0 0 0 16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2zm10-2H9v-5h6zM13 7h-2V5h2zM5 5h2v4h8V5h.59L19 8.41V19h-2v-5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v5H5z"></path></svg>{{ __('Registrar') }}
            </x-primary-button>
        </div>
    </form>
