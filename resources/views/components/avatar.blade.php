@props([
    'size' => 'md', // xs, sm, md, lg, xl
    'image' => null,
    'initials' => null,
])

@php
    $sizeClass = [
        'xs' => 'avatar--xs',
        'sm' => 'avatar--sm',
        'md' => 'avatar--md',
        'lg' => 'avatar--lg',
        'xl' => 'avatar--xl',
    ][$size] ?? 'avatar--md';
@endphp

<div {{ $attributes->merge(['class' => 'avatar ' . $sizeClass]) }}>
    @if($image)
        <img src="{{ $image }}" alt="" loading="lazy">
    @elseif($initials)
        {{ strtoupper(substr($initials, 0, 2)) }}
    @else
        {{ $slot }}
    @endif
</div>
