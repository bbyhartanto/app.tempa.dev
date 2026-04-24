import './bootstrap';
import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        // Try multiple possible paths for the component
        const possiblePaths = [
            `./Pages/${name}.vue`,
            `./Pages/${name}/Index.vue`,
            `./Pages/${name}/index.vue`,
        ];
        
        for (const path of possiblePaths) {
            if (pages[path]) {
                return pages[path];
            }
        }
        
        throw new Error(`Page not found: ${name}`);
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
});
