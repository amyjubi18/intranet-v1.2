@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-blue-900 focus:border-blue-700 focus:ring-blue-500 rounded-md shadow-sm']) !!}>
