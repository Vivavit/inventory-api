@props([
    'type' => 'default',
    'class' => ''
])

@php
    $baseClasses = 'inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md';

    $typeClasses = [
        'default' => 'bg-gray-100 text-gray-800',
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'danger' => 'bg-red-100 text-red-800',
        'info' => 'bg-blue-100 text-blue-800',
    ];

    $classes = $baseClasses . ' ' . ($typeClasses[$type] ?? $typeClasses['default']) . ' ' . $class;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>