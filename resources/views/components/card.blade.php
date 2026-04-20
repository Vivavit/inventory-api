@props([
    'class' => '',
    'hover' => true
])

@php
    $baseClasses = 'bg-white rounded-xl p-6 border border-gray-200 shadow-sm transition-all duration-200';
    $hoverClasses = $hover ? 'hover:shadow-lg hover:-translate-y-1' : '';
    $classes = $baseClasses . ' ' . $hoverClasses . ' ' . $class;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>