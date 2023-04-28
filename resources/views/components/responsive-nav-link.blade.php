@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex w-full pl-3 pr-4 py-2 text-center text-base font-medium text-white bg-blue-900 focus:outline-none focus:text-white focus:bg-blue-500 focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'flex w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-center text-base font-medium text-white hover:text-white hover:bg-blue-500 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
