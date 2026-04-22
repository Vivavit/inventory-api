@props([
    'type' => 'text',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'error' => '',
    'class' => ''
])

@php
    $inputClasses = 'form-control';
    if ($error) {
        $inputClasses .= ' is-invalid';
    }
    $inputClasses .= ' ' . $class;
@endphp

<div class="form-group">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required) <span class="required">*</span> @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => $inputClasses]) }}
    >

    @if($error)
        <p class="text-danger small mt-1 mb-0">{{ $error }}</p>
    @endif
</div>
