@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 pt-1 border-b-2  text-sm font-medium leading-10 text-white rounded-md hover:text-white focus:outline-none focus:border-sky-700 transition duration-150 ease-in-out '
            : 'inline-flex items-center px-3 pt-1  border-b-2 border-transparent text-sm font-medium leading-10 text-white rounded-md hover:text-white  hover:border-gray-300   focus:outline-none focus:text-white  focus:border-gray-300  transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="mr-1" style="fill: rgba(251, 251, 251, 1);transform: ;msFilter:;"><path d="m21.512 6.112-3.89 3.889-3.535-3.536 3.889-3.889a6.501 6.501 0 0 0-8.484 8.486l-6.276 6.275a.999.999 0 0 0 0 1.414l2.122 2.122a.999.999 0 0 0 1.414 0l6.275-6.276a6.501 6.501 0 0 0 7.071-1.414 6.504 6.504 0 0 0 1.414-7.071z"></path></svg>  {{ $slot }}
</a>
