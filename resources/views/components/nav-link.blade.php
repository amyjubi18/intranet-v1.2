@props(['active'])

@php
$classes = ($active ?? false)
? 'inline-flex items-center px-4 pt-1   text-sm font-medium leading-4 text-white  hover:text-white focus:outline-none  transition duration-150 ease-in-out  '
            : 'inline-flex items-center px-4 pt-1  text-sm font-medium leading-4 text-white  hover:text-white   focus:outline-none focus:text-white    transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" class="mr-1" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;"><path d="M12.71 2.29a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42A1 1 0 0 0 3 13h1v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7h1a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71zM6 20v-9.59l6-6 6 6V20z"></path></svg>{{ $slot }}
</a>
