@props([
    'variant' => 'primary', // primary, success, warning, danger, gray
])

@php
    $variantClass = [
        'primary' => 'badge--primary',
        'success' => 'badge--success',
        'warning' => 'badge--warning',
        'danger' => 'badge--danger',
        'error' => 'badge--danger',
        'gray' => 'badge--gray',
        'secondary' => 'badge--gray',
    ][$variant] ?? 'badge--primary';
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $variantClass]) }}>
    {{ $slot }}
</span>
