import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/features/layout.css',
                'resources/js/features/layout.js',
                'resources/css/features/dashboard.css',
                'resources/js/features/dashboard.js',
                'resources/css/features/analytics.css',
                'resources/js/features/analytics.js',
                'resources/css/features/products.css',
                'resources/js/features/products.js',
                'resources/css/features/orders.css',
                'resources/js/features/orders.js',
                'resources/css/features/warehouses.css',
                'resources/js/features/warehouses.js',
                'resources/css/features/users.css',
                'resources/js/features/users.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
