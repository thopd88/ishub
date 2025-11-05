import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import ReactDOMServer from 'react-dom/server';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: async (name) => {
            const pages = import.meta.glob([
                './pages/**/*.tsx',
                '../../Modules/*/resources/js/pages/**/*.tsx',
            ]);

            // Try main app pages first
            let pagePath = `./pages/${name}.tsx`;
            if (pages[pagePath]) {
                return pages[pagePath]();
            }

            // Try module pages: blog/create -> Modules/Blog/resources/js/pages/create.tsx
            const parts = name.split('/');
            if (parts.length > 1) {
                const moduleName =
                    parts[0].charAt(0).toUpperCase() + parts[0].slice(1);
                const pageFile = parts.slice(1).join('/');
                pagePath = `../../Modules/${moduleName}/resources/js/pages/${pageFile}.tsx`;

                if (pages[pagePath]) {
                    return pages[pagePath]();
                }
            }

            throw new Error(`Page not found: ${name}`);
        },
        setup: ({ App, props }) => {
            return <App {...props} />;
        },
    }),
);
