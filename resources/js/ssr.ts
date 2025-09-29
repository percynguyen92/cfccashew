import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { createI18nInstance, extractLocaleFromInertiaProps } from './lib/i18n';
import { renderToString } from 'vue/server-renderer';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) => {
                const initialLocale = extractLocaleFromInertiaProps({
                    initialPage: props.initialPage,
                });
                const i18n = createI18nInstance(initialLocale, {
                    attachRouterListener: false,
                });

                return createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(i18n);
            },
        }),
    { cluster: true },
);
