import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/cart/cartindex.css',
                'resources/js/cart/cartindex.js',
                'resources/css/products/productindex.css',
                'resources/js/products/productindex.js',
                'resources/css/products/show.css',
                'resources/css/wishlist/wishlistindex.css',
                'resources/js/wishlist/wishlistindex.js',
                'resources/css/profile/profileedit.css',
                'resources/js/profile/profileedit.js',
                'resources/js/account/accounts.js',
                'resources/css/checkout/checkoutindex.css',
                'resources/js/checkout/checkoutindex.js',
                'resources/css/checkout/success.css',
                'resources/js/checkout/success.js',
                'resources/css/orders/tracking.css',
            ],
            refresh: true,
        }),
    ],
});
