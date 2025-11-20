// Establecer variable de entorno ANTES de importar el plugin para evitar detección automática
import { networkInterfaces } from 'os';

function getLocalIP() {
    const interfaces = networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return 'localhost';
}

const localIP = getLocalIP();
console.log(`Local IP Address: ${localIP}`);

// Establecer VITE_DEV_SERVER_URL para evitar detección automática de TLS en Herd/Valet
if (!process.env.VITE_DEV_SERVER_URL) {
    process.env.VITE_DEV_SERVER_URL = `http://${localIP}:5173`;
}

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({

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
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        cors: {
            origin: true, // Permite cualquier origen en desarrollo
            credentials: true,
        },
        hmr: {
            host: localIP,
            port: 5173,
        },
    },
});
