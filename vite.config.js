import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const basePath = (env.VITE_BASE_PATH || env.APP_BASE_PATH || '').replace(/\/$/, '');
    const assetBase = basePath ? `${basePath}/build/` : '/build/';

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
                detectTls: false,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            tailwindcss(),
        ],
        resolve: {
            alias: {
                '@': '/resources/js',
                'ziggy': path.resolve(__dirname, 'vendor/tightenco/ziggy/dist/index.esm.js'),
                'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy/dist/index.js'),
            },
        },
        base: assetBase,

        server: mode === 'development' ? {
            host: '0.0.0.0',
            port: 5173,
            strictPort: false,
            hmr: {
                host: 'localhost',
                port: 5173,
            },
        } : undefined,

        build: {
            manifest: true,
            outDir: 'public/build',
            emptyOutDir: true,
            rollupOptions: {
                input: {
                    app: 'resources/js/app.js',
                    css: 'resources/css/app.css',
                },
            },
        },
    };
});
