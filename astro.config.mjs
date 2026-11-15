import { defineConfig } from 'astro/config';
// Canonical origin (keep in sync with SITE_URL in src/data/site.ts).
export default defineConfig({
  output: 'static',
  site: 'https://tripandaman.in',
  trailingSlash: 'always',
  build: { format: 'directory' },
  vite: { server: { allowedHosts: true } },
});
