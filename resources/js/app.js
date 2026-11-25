import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import VueApexCharts from 'vue3-apexcharts';

createInertiaApp({
    title: (title) => (title ? `${title} — Jelajah Sulteng` : 'Jelajah Sulteng — Dinas Pariwisata Sulawesi Tengah'),
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const savedTheme = localStorage.getItem('jelajah_tema') || 'terang';
        document.documentElement.setAttribute('data-tema', savedTheme === 'gelap' ? 'gelap' : 'terang');

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(VueApexCharts)
            .component('Head', Head)
            .component('Link', Link)
            .mount(el);
    },
    progress: {
        color: '#0E7C86',
        showSpinner: false,
    },
});
