import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/cart/cartindex.css',
                'resources/css/profile/profileedit.css',
                'resources/css/wishlist/wishlistindex.css',
                'resources/css/checkout/checkoutindex.css',
                'resources/css/checkout/success.css',
                'resources/css/orders/tracking.css',
                'resources/css/reviews/reviews.css',
                'resources/css/settings/settings.css',
                'resources/css/analytics/analytics.css',
                'resources/css/activity-log/acivity-log.css',
            ],
            refresh: true,
        }),
    ],
});
