@extends('layouts.app')

@section('title', __('compare.title') . ' - e-Shop')

@push('styles')
    @vite('resources/css/compare/compare.css')
@endpush

@section('content')
<div x-data="comparePage()" class="compare-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="{{ route('products.index') }}">{{ __('products.home') }}</a>
            <span>/</span>
            <span>{{ __('compare.title') }}</span>
        </nav>

        <!-- Page Header -->
        <header class="page-header">
            <div class="header-content">
                <h1>{{ __('compare.title') }}</h1>
                <p class="subtitle">{{ __('compare.comparing') }} {{ $products->count() }} {{ __('compare.items') }}</p>
            </div>
            @if($products->count() > 0)
                <button @click="clearAll()" class="btn-clear">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    {{ __('compare.clear_all') }}
                </button>
            @endif
        </header>

        @if($products->count() > 0)
            <!-- Comparison Table -->
            <div class="compare-table-wrapper">
                <table class="compare-table">
                    <!-- Product Images Row -->
                    <tr class="product-row">
                        <th class="label-cell">{{ __('compare.product') }}</th>
                        @foreach($products as $product)
                            <td class="product-cell" data-product-id="{{ $product->id }}">
                                <div class="product-card">
                                    <button @click="removeProduct({{ $product->id }})" class="remove-btn" title="{{ __('compare.remove_from_compare') }}">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                    <a href="{{ route('products.show', $product->slug) }}" class="product-image">
                                        @if($product->getPrimaryImage())
                                            <img src="{{ asset('storage/' . $product->getPrimaryImage()->image_path) }}" alt="{{ $product->name }}">
                                        @else
                                            <div class="placeholder">
                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </a>
                                    <h3 class="product-name">
                                        <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                    </h3>
                                </div>
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="product-cell empty-cell">
                                <a href="{{ route('products.index') }}" class="add-product-placeholder">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>{{ __('compare.add_to_compare') }}</span>
                                </a>
                            </td>
                        @endfor
                    </tr>

                    <!-- Price Row -->
                    <tr>
                        <th class="label-cell">{{ __('compare.price') }}</th>
                        @foreach($products as $product)
                            <td class="data-cell">
                                <div class="price-display">
                                    @if($product->sale_price)
                                        <span class="price-sale">${{ number_format($product->sale_price, 2) }}</span>
                                        <span class="price-old">${{ number_format($product->price, 2) }}</span>
                                    @else
                                        <span class="price-regular">${{ number_format($product->price, 2) }}</span>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell">—</td>
                        @endfor
                    </tr>

                    <!-- Category Row -->
                    <tr>
                        <th class="label-cell">{{ __('compare.category') }}</th>
                        @foreach($products as $product)
                            <td class="data-cell">{{ $product->category?->name ?? $product->category()->first()?->name ?? '—' }}</td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell">—</td>
                        @endfor
                    </tr>

                    <!-- Rating Row -->
                    <tr>
                        <th class="label-cell">{{ __('compare.rating') }}</th>
                        @foreach($products as $product)
                            <td class="data-cell">
                                <div class="rating-display">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="star {{ $i <= round($product->average_rating ?? 0) ? 'filled' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="rating-value">{{ number_format($product->average_rating ?? 0, 1) }}</span>
                                    <span class="rating-count">({{ $product->reviews_count ?? 0 }})</span>
                                </div>
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell">—</td>
                        @endfor
                    </tr>

                    <!-- Stock Row -->
                    <tr>
                        <th class="label-cell">{{ __('compare.stock') }}</th>
                        @foreach($products as $product)
                            <td class="data-cell">
                                @if($product->isInStock())
                                    <span class="stock-badge in-stock">{{ __('compare.in_stock') }}</span>
                                @else
                                    <span class="stock-badge out-of-stock">{{ __('compare.out_of_stock') }}</span>
                                @endif
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell">—</td>
                        @endfor
                    </tr>

                    <!-- Description Row -->
                    <tr>
                        <th class="label-cell">{{ __('products.description') }}</th>
                        @foreach($products as $product)
                            <td class="data-cell description-cell">
                                <p>{{ Str::limit($product->description, 150) }}</p>
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell">—</td>
                        @endfor
                    </tr>

                    <!-- Specifications Header -->
                    @if(count($attributes) > 0)
                        <tr class="section-header-row">
                            <th colspan="{{ 5 }}" class="section-header">{{ __('compare.specifications') }}</th>
                        </tr>
                        @foreach($attributes as $attr)
                            <tr>
                                <th class="label-cell">{{ ucfirst($attr) }}</th>
                                @foreach($products as $product)
                                    @php
                                        $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications, true);
                                        $value = $specs[$attr] ?? '—';
                                    @endphp
                                    <td class="data-cell">{{ $value }}</td>
                                @endforeach
                                @for($i = $products->count(); $i < 4; $i++)
                                    <td class="data-cell empty-cell">—</td>
                                @endfor
                            </tr>
                        @endforeach
                    @endif

                    <!-- Actions Row -->
                    <tr class="actions-row">
                        <th class="label-cell"></th>
                        @foreach($products as $product)
                            <td class="data-cell">
                                <div class="action-buttons">
                                    <button @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')" 
                                            class="btn-cart"
                                            {{ !$product->isInStock() ? 'disabled' : '' }}>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        </svg>
                                        {{ __('compare.add_to_cart') }}
                                    </button>
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn-view">
                                        {{ __('compare.view_details') }}
                                    </a>
                                </div>
                            </td>
                        @endforeach
                        @for($i = $products->count(); $i < 4; $i++)
                            <td class="data-cell empty-cell"></td>
                        @endfor
                    </tr>
                </table>
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="64" height="64" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M400-40v-80H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h200v-80h80v880h-80ZM200-240h200v-240L200-240Zm360 120v-360l200 240v-520H560v-80h200q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H560Z"/>
                    </svg>
                </div>
                <h2>{{ __('compare.empty') }}</h2>
                <p>{{ __('compare.empty_description') }}</p>
                <a href="{{ route('products.index') }}" class="btn-browse">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 12H4M4 12l6-6M4 12l6 6"/>
                    </svg>
                    {{ __('compare.browse_products') }}
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function comparePage() {
    return {
        async removeProduct(productId) {
            try {
                const response = await fetch(`/compare/remove/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    // Animate out the product cell
                    const cell = document.querySelector(`[data-product-id="${productId}"]`);
                    if (cell) {
                        const td = cell.closest('td');
                        if (td) {
                            td.style.transition = 'all 0.4s ease';
                            td.style.opacity = '0';
                            td.style.transform = 'scale(0.8)';
                        }
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                }
            } catch (error) {
                console.error('Error removing product', error);
            }
        },

        async clearAll() {
            if (!confirm('{{ __('compare.clear_all') }}?')) return;
            try {
                const response = await fetch('/compare/clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error clearing comparison', error);
            }
        },

        async addToCart(productId, productName) {
            try {
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    // Update cart count in header
                    if (window.Alpine && Alpine.store('global')) {
                        Alpine.store('global').cartCount = data.cartCount;
                    }
                }
            } catch (error) {
                console.error('Error adding to cart', error);
            }
        }
    };
}
</script>
@endsection
