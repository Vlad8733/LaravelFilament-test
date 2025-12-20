<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TicketController;
use App\Livewire\AdminTicketChat;

Route::get('/search', [SearchController::class, 'index'])->name('search.global');

// Главная
Route::get('/', fn() => view('welcome'))->name('home');

// Гостевые маршруты: регистрация / вход
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
});

// Выход
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Подтверждение пароля (auth)
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'confirm']);
});

// Профиль пользователя
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/accounts/store-child', [AccountController::class, 'storeChild'])->name('profile.accounts.store-child');
});

// Продукты
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
});

// Категории
Route::get('/category/{category:slug}', [ProductController::class, 'category'])->name('category.show');

// Корзина - ВСЕ маршруты в одном месте
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add/{productId}', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{itemId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{itemId}', [CartController::class, 'remove'])->name('remove');
    Route::get('/count', [CartController::class, 'getCartCount'])->name('count');
    Route::post('/coupon/apply', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/coupon/remove', [CartController::class, 'removeCoupon'])->name('coupon.remove');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CartController::class, 'checkout'])->name('show');
    Route::post('/', [CartController::class, 'placeOrder'])->name('place');
    Route::get('/success/{order}', [CartController::class, 'success'])->name('success');
});

// Order verification
Route::get('/checkout/verify/{orderId}', [CartController::class, 'verifyOrder'])->name('checkout.verify');
Route::post('/checkout/verify/{orderId}', [CartController::class, 'verifyOrderPost'])->name('checkout.verify.post');

// Отзывы
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// API для купонов
Route::post('/api/coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');

// Wishlist
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::get('/items', [WishlistController::class, 'getItems'])->name('items');
    Route::get('/count', [WishlistController::class, 'getCount'])->name('count');
    Route::post('/add/{productId}', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{productId}', [WishlistController::class, 'remove'])->name('remove');
});

// Продукты продавца
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/products', [SellerController::class, 'products'])->name('products');
});

// Support Tickets Routes
Route::middleware(['auth'])->prefix('support')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
});

// Notifications Routes
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::delete('/', [App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('destroy-all');
});

// Order tracking routes
Route::get('/track-order', function () {
    return view('orders.tracking-search');
})->name('orders.tracking.search');

Route::post('/track-order', [App\Http\Controllers\OrderTrackingController::class, 'search'])->name('orders.tracking.search.post');
Route::get('/track-order/{orderNumber}', [App\Http\Controllers\OrderTrackingController::class, 'show'])->name('orders.tracking.show');
