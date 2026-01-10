import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Cart
                'resources/css/cart/cartindex.css',
                'resources/css/cart/show.css',
                'resources/js/cart/cartindex.js',
                // Profile
                'resources/css/profile/profileedit.css',
                'resources/js/profile/profileedit.js',
                // Account
                'resources/js/account/accounts.js',
                // Wishlist
                'resources/css/wishlist/wishlistindex.css',
                // Checkout
                'resources/css/checkout/checkoutindex.css',
                'resources/css/checkout/success.css',
                'resources/js/checkout/checkoutindex.js',
                'resources/js/checkout/success.js',
                // Orders
                'resources/css/orders/tracking.css',
                // Reviews
                'resources/css/reviews/reviews.css',
                // Settings
                'resources/css/settings/settings.css',
                // Analytics
                'resources/css/analytics/analytics.css',
                // Activity Log
                'resources/css/activity-log/activity-log.css',
                // Compare
                'resources/css/compare/compare.css',
                // Notifications
                'resources/css/notifications/notifications.css',
                // Products
                'resources/css/products/productindex.css',
                'resources/css/products/show.css',
                'resources/js/products/productindex.js',
                'resources/js/products/show.js',
                // Pages
                'resources/css/pages/pages.css',
                'resources/css/pages/company.css',
                // Tickets
                'resources/css/tickets/tickets.css',
                'resources/css/tickets/ticket-show.css',
                // Refunds
                'resources/css/refunds/refunds.css',
            ],
            refresh: true,
        }),
    ],
});
