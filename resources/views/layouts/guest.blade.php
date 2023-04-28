<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 ">
        <section class="flex flex-col items-center h-screen border-4 border-blue-900 rounded md:flex-row ">
            <div class="hidden h-screen bg-blue-600 lg:block md:w-1/2 xl:w-2/3">
                <img src="../img/fondo-intranet.jpg" class="object-cover w-full h-full" alt="">
            </div>

            <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white sm:max-w-md sm:rounded-lg">
{{--                 <h1 class="my-1 text-5xl font-bold text-center text-blue-900" style="padding-top: -10px">Intranet</h1>
 --}}
                {{ $slot }}
            </div>
        </div>
    </section>
    </body>
</html>
