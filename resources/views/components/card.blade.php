{{-- 
    Universal Card Component
    Usage: 
    <x-card>Content</x-card>
    <x-card class="p-6" hover>Content</x-card>
    <x-card :padding="false">Content without padding</x-card>
--}}
@props([
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'card' . 
        ($padding ? ' card--padded' : '') . 
        ($hover ? ' card--hover' : '')
]) }}>
    {{ $slot }}
</div>
