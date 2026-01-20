@props([
    'icon' => null,
    'title' => null,
    'description' => null,
    'action' => null,
    'actionText' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    @if($icon)
        <div class="empty-state__icon">
            {!! $icon !!}
        </div>
    @else
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="empty-state__icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>
    @endif

    @if($title)
        <h3 class="empty-state__title">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="empty-state__description">{{ $description }}</p>
    @endif

    {{ $slot }}

    @if($actionHref && $actionText)
        <a href="{{ $actionHref }}" class="btn btn--primary">
            {{ $actionText }}
        </a>
    @elseif($action)
        {{ $action }}
    @endif
</div>
