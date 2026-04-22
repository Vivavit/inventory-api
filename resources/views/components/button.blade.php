@props([
    'type' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
    'class' => ''
])

@php
    $baseClasses = 'btn';

    $typeClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-outline-secondary',
        'danger' => 'btn-danger',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
    ];

    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $classes = $baseClasses . ' ' . ($typeClasses[$type] ?? $typeClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . $class;

    if ($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
