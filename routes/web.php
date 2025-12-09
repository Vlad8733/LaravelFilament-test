<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SellerController;

// Главная
Route::get('/', function () {
    return redirect()->route('products.index');
});

// Продукты
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/search', [ProductController::class, 'search'])->name('search');
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
});

// Категории
Route::get('/category/{category:slug}', [ProductController::class, 'category'])->name('category.show');

// Корзина
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');
    Route::post('/add/{productId}', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{productId}', [CartController::class, 'updateQuantity'])->name('update');
    Route::delete('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

// Checkout - ИСПРАВЛЕНО!
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CartController::class, 'checkout'])->name('show');
    Route::post('/', [CartController::class, 'placeOrder'])->name('place');
    Route::get('/success/{order}', [CartController::class, 'success'])->name('success');
});

// Отзывы
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// API для купонов
Route::post('/api/coupons/validate', [CouponController::class, 'validate'])->name('coupons.validate');

// Wishlist
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add/{productId}', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{productId}', [WishlistController::class, 'remove'])->name('remove');
    Route::get('/count', [WishlistController::class, 'getCount'])->name('count');
    Route::get('/items', [WishlistController::class, 'getItems'])->name('items');
});

// Продукты продавца
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/', [SellerController::class, 'index'])->name('dashboard');
    Route::get('/products', [SellerController::class, 'products'])->name('products');
});
