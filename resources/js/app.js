import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

createInertiaApp({
    title: (title) => title ? `${title} - Sistema Tecnoweb` : 'Sistema Tecnoweb',
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);

        if (typeof Ziggy !== 'undefined') {
            app.use(ZiggyVue, Ziggy);
        }

        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
