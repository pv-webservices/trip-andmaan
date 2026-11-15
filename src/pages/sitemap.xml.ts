import type { APIRoute } from 'astro';
import { SITE_URL } from '../data/site';
import { lastModified, sitemapEntries } from '../data/sitemap';

const escapeXml = (s: string) =>
  s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('"', '&quot;');

export const GET: APIRoute = () => {
  const urls = sitemapEntries.map(({ path, sources }) => {
    const lastmod = lastModified(sources);
    return `<url><loc>${escapeXml(new URL(path, SITE_URL).href)}</loc>${lastmod ? `<lastmod>${lastmod}</lastmod>` : ''}</url>`;
  });
  return new Response(
    `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls.join('\n')}\n</urlset>\n`,
    { headers: { 'Content-Type': 'application/xml; charset=utf-8' } },
  );
};
