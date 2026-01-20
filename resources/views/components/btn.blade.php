{{--
    Universal Button Component
    Usage:
    <x-btn>Default Button</x-btn>
    <x-btn variant="primary">Primary</x-btn>
    <x-btn variant="secondary">Secondary</x-btn>
    <x-btn variant="danger">Danger</x-btn>
    <x-btn variant="ghost">Ghost</x-btn>
    <x-btn size="sm">Small</x-btn>
    <x-btn size="lg">Large</x-btn>
    <x-btn :loading="true">Loading...</x-btn>
    <x-btn href="/link">Link Button</x-btn>
    <x-btn icon="cart">With Icon</x-btn>
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'icon' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'btn';
    $variantClasses = match($variant) {
        'primary' => 'btn--primary',
        'secondary' => 'btn--secondary',
        'danger' => 'btn--danger',
        'ghost' => 'btn--ghost',
        'success' => 'btn--success',
        'outline' => 'btn--outline',
        default => 'btn--primary',
    };
    $sizeClasses = match($size) {
        'sm' => 'btn--sm',
        'lg' => 'btn--lg',
        'xl' => 'btn--xl',
        default => 'btn--md',
    };
    $stateClasses = $loading ? 'btn--loading' : '';
    $classes = "$baseClasses $variantClasses $sizeClasses $stateClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="btn-icon">
                @include("components.icons.$icon")
            </span>
        @endif
        <span class="btn-text">{{ $slot }}</span>
        @if($loading)
            <span class="btn-spinner"></span>
        @endif
    </a>
@else
    <button 
        type="{{ $type }}" 
        {{ $disabled || $loading ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($icon)
            <span class="btn-icon">
                @include("components.icons.$icon")
            </span>
        @endif
        <span class="btn-text">{{ $slot }}</span>
        @if($loading)
            <span class="btn-spinner"></span>
        @endif
    </button>
@endif
