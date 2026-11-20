import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Compiled locally (npm run build) into public/build/ and shipped with each
// release. Production never runs Node.js.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/site.css', 'resources/js/site-entry.ts'],
            refresh: true,
        }),
    ],
    build: {
        assetsInlineLimit: 0,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
