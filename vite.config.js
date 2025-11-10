import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/tooltip.css',
                'resources/js/app.js',
                'resources/js/confirmation.js',
                'resources/js/custom.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        rollupOptions: {
            external: [],
            output: {
                manualChunks: undefined,
            }
        },
        commonjsOptions: {
            include: [/laravel-echo/, /pusher-js/, /node_modules/]
        }
    },
    optimizeDeps: {
        include: ['laravel-echo', 'pusher-js']
    }
});
