@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 pt-1 border-b-2  text-sm font-medium leading-6 rounded-md text-white  hover:text-white focus:outline-none focus:border-blue-900 transition duration-150 ease-in-out '
            : 'inline-flex items-center px-3 pt-1  border-b-2 border-transparent text-sm font-medium leading-6 rounded-md text-white  hover:text-white  hover:border-gray-300   focus:outline-none focus:text-white  focus:border-gray-300  transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="mr-2" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M19 7h-1V2H6v5H5a3 3 0 0 0-3 3v7a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2v-7a3 3 0 0 0-3-3zM8 4h8v3H8V4zm0 16v-4h8v4H8zm11-8h-4v-2h4v2z"></path></svg> {{ $slot }}
</a>




