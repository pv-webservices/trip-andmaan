import type { APIRoute } from 'astro';
import { destinations } from '../data/destinations';
import { packages } from '../data/packages';
export const GET: APIRoute = () => {
  const origin = import.meta.env.PUBLIC_SITE_URL;
  const routes = [
    '/',
    '/about/',
    '/destinations/',
    '/tour-packages/',
    '/tour-packages/customized-trip/',
    '/experiences/',
    '/gallery/',
    '/contact/',
    '/privacy/',
    '/travel-information/',
    '/image-credits/',
    ...destinations.map((d) => `/destinations/${d.slug}/`),
    ...packages
      .filter((p) => p.published)
      .map((p) => `/tour-packages/${p.slug}/`),
  ];
  const escape = (s: string) =>
    s
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('"', '&quot;');
  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${origin ? routes.map((route) => `<url><loc>${escape(new URL(route, origin).href)}</loc></url>`).join('') : ''}</urlset>`,
    { headers: { 'Content-Type': 'application/xml' } },
  );
};
