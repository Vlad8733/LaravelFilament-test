@props([
    'label' => null,
    'type' => 'text',
    'name',
    'id' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $required ? 'form-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    @if($icon)
        <div class="form-input-wrapper">
            <span class="form-input-icon">{!! $icon !!}</span>
    @endif

    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        {{ $attributes->except('class')->merge(['class' => 'form-input' . ($hasError ? ' form-input--error' : '')]) }}
        @if($required) required @endif
    >

    @if($icon)
        </div>
    @endif

    @if($hasError)
        <p class="form-error">{{ $errorMessage }}</p>
    @elseif($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
