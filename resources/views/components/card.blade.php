@props([
    'class' => '',
    'hover' => true
])

@php
    $baseClasses = 'surface-card';
    $hoverClasses = $hover ? 'surface-card--soft' : '';
    $classes = $baseClasses . ' ' . $hoverClasses . ' ' . $class;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
