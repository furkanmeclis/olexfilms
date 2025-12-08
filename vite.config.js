import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/car-part-picker.css',
                'resources/js/app.js',
                'resources/js/filament/car-part-picker.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
