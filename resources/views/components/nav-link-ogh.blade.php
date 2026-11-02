@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 pt-1 border-b-2  text-sm font-medium rounded-md leading-10 text-white hover:bg-blue-700 hover:text-white focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-3 pt-1 border-b-2 border-transparent text-sm rounded-md font-medium leading-10 text-white  hover:text-white hover:bg-blue-700 hover:border-gray-300  focus:outline-none focus:text-white  focus:border-gray-300  transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="mr-1" viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 3.33A1.67 1.67 0 1 1 10.33 7 1.67 1.67 0 0 1 12 5.33zm3.33 12.5-1.66.84-1.39-3.89h-.56l-1.39 3.89-1.66-.84 1.66-4.72v-1.66L7 10.33l.56-1.66 3.33 1.11h2.22l3.33-1.11.56 1.66-3.33 1.12v1.66z"></path></svg>{{ $slot }}
</a>
