@props([
    'label' => null,
    'name',
    'id' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'rows' => 4,
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

    <textarea 
        name="{{ $name }}"
        id="{{ $inputId }}"
        rows="{{ $rows }}"
        {{ $attributes->except('class')->merge(['class' => 'form-textarea' . ($hasError ? ' form-textarea--error' : '')]) }}
        @if($required) required @endif
    >{{ old($name, $slot) }}</textarea>

    @if($hasError)
        <p class="form-error">{{ $errorMessage }}</p>
    @elseif($help)
        <p class="form-help">{{ $help }}</p>
    @endif
</div>
