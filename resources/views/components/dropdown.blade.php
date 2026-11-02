@props(['align' => 'right', 'width' => '300', 'contentClasses' => ' bg-blue-900	', 'content' => null])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'origin-top-left left-0';
        break;
    case 'top':
        $alignmentClasses = 'origin-top';
        break;
    case 'right':
    default:
        $alignmentClasses = 'origin-top-right right-0';
        break;
}

switch ($width) {
    case '350':
        $width = 'w-48 bg-blue-900';
        break;
}
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-0 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-0 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50  {{ $width }} rounded-md border-1 border-blue-950 w-48	shadow-md shadow-gray-500 ml-3 text-center{{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="rounded-md
         {{ $contentClasses }}">
         {!! $content !!}
        </div>
    </div>
</div>
