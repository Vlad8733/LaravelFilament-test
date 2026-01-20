@props([
    'label' => null,
    'name',
    'id' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'options' => [],
    'placeholder' => null,
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

    <select 
        name="{{ $name }}"
        id="{{ $inputId }}"
        {{ $attributes->except('class')->merge(['class' => 'form-select' . ($hasError ? ' form-select--error' : '')]) }}
        @if($required) required @endif
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ old($name) == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
        @endforeach
        
        {{ $slot }}
    </select>

    @if($hasError)
        <p class="form-error">{{ $errorMessage }}</p>
    @elseif($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
