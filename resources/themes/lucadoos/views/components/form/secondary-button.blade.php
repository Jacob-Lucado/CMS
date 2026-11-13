@props(['classes' => '', 'type' => 'submit'])
<button type="{{ $type }}" {{ $attributes->merge(['class' => 'los-btn full ' . $classes]) }}>
    {{ $slot }}
</button>
