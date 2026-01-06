<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [SearchController::class, 'index'])->name('search.global');

// Static pages
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'sendContact'])->name('pages.contact.send');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/recently-viewed', [PageController::class, 'recentlyViewed'])->name('pages.recently-viewed');

// Home
Route::get('/', fn () => view('welcome'))->name('home');

// Guest routes: registration / login
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Password confirmation (auth)
Route::middleware('auth')->group(function () {
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'showConfirmForm'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'confirm']);
});

// User profile
Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/accounts/create-child', [AccountController::class, 'createChild'])->name('profile.accounts.create-child');
    Route::post('/profile/accounts/store-child', [AccountController::class, 'storeChild'])->name('profile.accounts.store-child');
    Route::post('/profile/accounts/switch', [AccountController::class, 'switchAccount'])->name('profile.accounts.switch');
});

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// Admin CSV export for products (protected by auth + EnsureUserIsAdmin)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->get('/admin/products/export', [\App\Http\Controllers\Admin\ProductExportController::class, 'export'])
    ->name('admin.products.export');

// Alternate export route outside Filament prefix to avoid panel route conflicts
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->get('/products/export', [\App\Http\Controllers\Admin\ProductExportController::class, 'export'])
    ->name('products.export');

// Admin CSV import for products (form + POST)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->get('/admin/products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'showForm'])
    ->name('admin.products.import.form');

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->post('/admin/products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'import'])
    ->name('admin.products.import');

// Alternate import routes outside Filament prefix to avoid panel route conflicts
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->get('/products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'showForm'])
    ->name('products.import.form');

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->post('/products/import', [\App\Http\Controllers\Admin\ProductImportController::class, 'import'])
    ->name('products.import');

// Download failed import CSV
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->get('/admin/imports/{import}/download-failed', [\App\Http\Controllers\Admin\ImportJobDownloadController::class, 'download'])
    ->name('admin.imports.download_failed');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
});

// Categories
Route::get('/category/{category:slug}', [ProductController::class, 'category'])->name('category.show');

// Cart - all routes in one place
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

// Reviews
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('product.reviews.store');

// Coupons API
Route::post('/api/coupons/validate', [CouponController::class, 'validateCoupon'])->name('coupons.validate');

// Wishlist
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::get('/items', [WishlistController::class, 'getItems'])->name('items');
    Route::get('/count', [WishlistController::class, 'getCount'])->name('count');
    Route::post('/add/{productId}', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{productId}', [WishlistController::class, 'remove'])->name('remove');
});

// Compare Products
Route::prefix('compare')->name('compare.')->group(function () {
    Route::get('/', [App\Http\Controllers\CompareController::class, 'index'])->name('index');
    Route::get('/items', [App\Http\Controllers\CompareController::class, 'items'])->name('items');
    Route::get('/count', [App\Http\Controllers\CompareController::class, 'count'])->name('count');
    Route::post('/add/{productId}', [App\Http\Controllers\CompareController::class, 'add'])->name('add');
    Route::post('/toggle/{productId}', [App\Http\Controllers\CompareController::class, 'toggle'])->name('toggle');
    Route::delete('/remove/{productId}', [App\Http\Controllers\CompareController::class, 'remove'])->name('remove');
    Route::delete('/clear', [App\Http\Controllers\CompareController::class, 'clear'])->name('clear');
});

// Support Tickets Routes
Route::middleware(['auth'])->prefix('support')->name('tickets.')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('index');
    Route::get('/create', [TicketController::class, 'create'])->name('create');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
    Route::post('/{ticket}/close', [TicketController::class, 'close'])->name('close');
    Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])->name('reopen');
});

// Notifications Routes
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'unread'])->name('unread');
    Route::post('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/', [App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('destroy-all');
});

// Order tracking routes
Route::get('/track-order', function () {
    return view('orders.tracking-search');
})->name('orders.tracking.search');

Route::post('/track-order', [App\Http\Controllers\OrderTrackingController::class, 'search'])->name('orders.tracking.search.post');
Route::get('/track-order/{orderNumber}', [App\Http\Controllers\OrderTrackingController::class, 'show'])->name('orders.tracking.show');

// Refund Routes
Route::middleware(['auth'])->prefix('refunds')->name('refunds.')->group(function () {
    Route::get('/', [App\Http\Controllers\RefundController::class, 'index'])->name('index');
    Route::get('/request/{order}', [App\Http\Controllers\RefundController::class, 'create'])->name('create');
    Route::post('/request/{order}', [App\Http\Controllers\RefundController::class, 'store'])->name('store');
    Route::get('/{refund}', [App\Http\Controllers\RefundController::class, 'show'])->name('show');
    Route::post('/{refund}/cancel', [App\Http\Controllers\RefundController::class, 'cancel'])->name('cancel');
});

// Customer Reviews Routes
Route::middleware(['auth'])->prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/', [App\Http\Controllers\CustomerReviewController::class, 'index'])->name('index');
    Route::get('/order/{order}', [App\Http\Controllers\CustomerReviewController::class, 'create'])->name('create');
    Route::post('/order/{order}', [App\Http\Controllers\CustomerReviewController::class, 'store'])->name('store');
    Route::get('/{review}', [App\Http\Controllers\CustomerReviewController::class, 'show'])->name('show');
    Route::get('/{review}/edit', [App\Http\Controllers\CustomerReviewController::class, 'edit'])->name('edit');
    Route::put('/{review}', [App\Http\Controllers\CustomerReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [App\Http\Controllers\CustomerReviewController::class, 'destroy'])->name('destroy');
});

// Settings Routes
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');
    Route::post('/locale', [App\Http\Controllers\SettingsController::class, 'updateLocale'])->name('locale');
});

// Analytics Routes
Route::middleware(['auth'])->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('index');
    Route::get('/data', [App\Http\Controllers\AnalyticsController::class, 'getData'])->name('data');
});

// Invoice Routes (public access by order number, protected by ID)
Route::prefix('invoice')->name('invoice.')->group(function () {
    Route::get('/order/{orderNumber}', [App\Http\Controllers\InvoiceController::class, 'downloadByNumber'])->name('download.number');
    Route::get('/{order}/download', [App\Http\Controllers\InvoiceController::class, 'download'])->name('download');
    Route::get('/{order}/view', [App\Http\Controllers\InvoiceController::class, 'view'])->name('view');
});

// Language switch route
Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ru', 'lv'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('language.switch');

// User activity log (middleware attached)
Route::middleware(['auth', \App\Http\Middleware\LogUserActivity::class])->group(function () {
    Route::get('/activity-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity_log.index');
});
