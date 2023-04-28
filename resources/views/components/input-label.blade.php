@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-base text-black font-semibold']) }}>
    {{ $value ?? $slot }}
</label>
