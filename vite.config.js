import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // 管理后台已迁 Filament（Livewire）；旧 Vue 入口保留在 backup/pre-filament-admin-*
                'resources/js/frontend/main.js',
                'resources/js/blade-skin-mount.js',
            ],
            refresh: true,
        }),
    ],
});
