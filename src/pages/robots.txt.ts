import type { APIRoute } from 'astro';
import { SITE_URL } from '../data/site';
// Crawl everything; noindex pages carry their own robots meta and must stay
// crawlable so search engines can see it.
export const GET: APIRoute = () =>
  new Response(
    `User-agent: *\nAllow: /\n\nSitemap: ${SITE_URL}/sitemap.xml\n`,
    { headers: { 'Content-Type': 'text/plain; charset=utf-8' } },
  );
