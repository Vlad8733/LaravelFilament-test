@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
])

@php
    $classes = [
        'info' => 'alert--info',
        'success' => 'alert--success',
        'warning' => 'alert--warning',
        'danger' => 'alert--danger',
        'error' => 'alert--danger',
    ];
    
    $icons = [
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />',
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
        'danger' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . ($classes[$type] ?? 'alert--info')]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="alert__icon">
        {!! $icons[$type] ?? $icons['info'] !!}
    </svg>
    
    <div class="alert__content">
        @if($title)
            <div class="alert__title">{{ $title }}</div>
        @endif
        <div class="alert__message">{{ $slot }}</div>
    </div>
    
    @if($dismissible)
        <button @click="show = false" class="alert__close" aria-label="{{ __('Dismiss') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
