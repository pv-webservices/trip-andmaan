import { defineConfig } from 'astro/config';
const site = process.env.PUBLIC_SITE_URL;
export default defineConfig({
  output: 'static',
  ...(site ? { site } : {}),
  trailingSlash: 'always',
  vite: { server: { allowedHosts: true } },
});
