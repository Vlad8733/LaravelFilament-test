@props([
    'product',
    'showActions' => true,
    'showBadge' => true,
])

@php
    $isOnSale = $product->sale_price && $product->sale_price < $product->price;
    $isNew = $product->created_at->gt(now()->subDays(7));
    $isOutOfStock = $product->stock_quantity <= 0;
@endphp

<div {{ $attributes->merge(['class' => 'product-card card card--hover']) }}>
    {{-- Image --}}
    <a href="{{ route('products.show', $product) }}" class="product-card__image">
        @if($product->images && count($product->images) > 0)
            <img 
                src="{{ Storage::url($product->images[0]) }}" 
                alt="{{ $product->name }}"
                loading="lazy"
            >
        @else
            <div class="flex items-center justify-center h-full bg-muted">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="text-muted" width="48" height="48">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        @if($showBadge)
            @if($isOnSale)
                <span class="card__badge card__badge--danger">
                    -{{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
                </span>
            @elseif($isNew)
                <span class="card__badge card__badge--success">{{ __('New') }}</span>
            @endif
        @endif
    </a>

    {{-- Content --}}
    <div class="product-card__content">
        {{-- Category --}}
        @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->id]) }}" class="text-xs text-muted mb-1 block">
                {{ $product->category->name }}
            </a>
        @endif

        {{-- Title --}}
        <a href="{{ route('products.show', $product) }}">
            <h3 class="product-card__title">{{ $product->name }}</h3>
        </a>

        {{-- Rating --}}
        @if($product->reviews_count > 0)
            <div class="flex items-center gap-1 mt-2">
                <div class="flex text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($product->average_rating))
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="14" height="14">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                        @endif
                    @endfor
                </div>
                <span class="text-xs text-muted">({{ $product->reviews_count }})</span>
            </div>
        @endif

        {{-- Price --}}
        <div class="product-card__price">
            @if($isOnSale)
                <span class="product-card__old-price">${{ number_format($product->price, 2) }}</span>
                <span>${{ number_format($product->sale_price, 2) }}</span>
            @else
                <span>${{ number_format($product->price, 2) }}</span>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    @if($showActions)
        <div class="product-card__actions">
            @if($isOutOfStock)
                <button class="btn btn--secondary btn--sm w-full" disabled>
                    {{ __('Out of Stock') }}
                </button>
            @else
                <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn--primary btn--sm w-full">
                        <x-icons.cart class="w-4 h-4" />
                        {{ __('Add to Cart') }}
                    </button>
                </form>
            @endif

            @auth
                <form action="{{ route('wishlist.toggle', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn--icon btn--secondary btn--sm" title="{{ __('Add to Wishlist') }}">
                        @if(auth()->user()->wishlists()->where('product_id', $product->id)->exists())
                            <x-icons.heart class="text-danger" fill="currentColor" />
                        @else
                            <x-icons.heart />
                        @endif
                    </button>
                </form>
            @endauth
        </div>
    @endif
</div>
