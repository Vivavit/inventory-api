@props([
    'type' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
    'class' => ''
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 border-none rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2';

    $typeClasses = [
        'primary' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 shadow-md hover:shadow-lg transform hover:-translate-y-0.5',
        'secondary' => 'bg-gray-100 text-green-600 hover:bg-green-50 focus:ring-green-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'warning' => 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
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