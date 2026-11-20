// Compares the Laravel-rendered pages with the Astro build in ../dist:
// title, meta tags, canonical, JSON-LD, headings, link targets, image alt text
// and visible text. Run with the Laravel server on BASE (default http://127.0.0.1:8000).
//   node laravel/scripts/compare-with-astro.mjs
import { readFile } from 'node:fs/promises';
import path from 'node:path';

const BASE = process.env.BASE ?? 'http://127.0.0.1:8000';
const root = path.resolve(import.meta.dirname, '..', '..');
const sitemap = await readFile(path.join(root, 'dist', 'sitemap.xml'), 'utf8');
const paths = [...sitemap.matchAll(/<loc>https:\/\/tripandaman\.in([^<]*)<\/loc>/g)].map((m) => m[1]);
paths.push('/image-credits/');

const decode = (s) =>
  s.replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#x27;/g, "'").replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(Number(n)));
const all = (html, re) => [...html.matchAll(re)].map((m) => decode(m[1]).trim());
const strip = (html) =>
  decode(
    html
      .replace(/<script[\s\S]*?<\/script>/g, ' ')
      .replace(/<style[\s\S]*?<\/style>/g, ' ')
      .replace(/<svg[\s\S]*?<\/svg>/g, ' ')
      .replace(/<!--[\s\S]*?-->/g, ' ')
      .replace(/<[^>]+>/g, ' '),
  )
    .replace(/\s+/g, ' ')
    .trim();
// Media URLs differ by design (/images/... vs /storage/media/...); compare everything else.
const hrefs = (html) =>
  all(html, /<a\b[^>]*href="([^"]*)"/g).filter((h) => !/\.(webp|mp4|pdf|jpg|png)$/.test(h));
const ld = (html) =>
  all(html, /<script type="application\/ld\+json">([\s\S]*?)<\/script>/g).map((j) => {
    const parsed = JSON.parse(j);
    return JSON.stringify(parsed, (k, v) => (k === 'image' || k === 'url' && String(v).match(/\.(webp|jpg|png)$/) ? '[img]' : v));
  });

const facts = (html) => ({
  title: all(html, /<title>([^<]*)<\/title>/g),
  description: all(html, /<meta name="description" content="([^"]*)"/g),
  robots: all(html, /<meta name="robots" content="([^"]*)"/g),
  canonical: all(html, /<link rel="canonical" href="([^"]*)"/g),
  ogTitle: all(html, /<meta property="og:title" content="([^"]*)"/g),
  ogUrl: all(html, /<meta property="og:url" content="([^"]*)"/g),
  jsonLd: ld(html),
  h1: all(html, /<h1[^>]*>([\s\S]*?)<\/h1>/g).map(strip),
  h2: all(html, /<h2[^>]*>([\s\S]*?)<\/h2>/g).map(strip),
  h3: all(html, /<h3[^>]*>([\s\S]*?)<\/h3>/g).map(strip),
  links: hrefs(html),
  // Astro writes alt="" as a bare `alt` attribute; normalise before comparing.
  alts: all(html.replace(/(<img\b[^>]*?\s)alt(?=[\s>/])/g, '$1alt=""'), /<img\b[^>]*alt="([^"]*)"/g),
  text: strip(html.replace(/[\s\S]*<main[^>]*>/, '').replace(/<\/main>[\s\S]*/, '')),
});

let failures = 0;
for (const p of paths) {
  const file = path.join(root, 'dist', p, 'index.html');
  const astro = facts(await readFile(file, 'utf8'));
  const res = await fetch(BASE + p);
  const laravel = facts(await res.text());
  const diffs = [];
  if (res.status !== 200) diffs.push(`status ${res.status}`);
  for (const key of Object.keys(astro)) {
    const a = JSON.stringify(astro[key]);
    const b = JSON.stringify(laravel[key]);
    if (a !== b) {
      if (Array.isArray(astro[key])) {
        const missing = astro[key].filter((x) => !laravel[key].includes(x));
        const extra = laravel[key].filter((x) => !astro[key].includes(x));
        diffs.push(`${key}: ${missing.length || extra.length ? `missing ${JSON.stringify(missing).slice(0, 400)} extra ${JSON.stringify(extra).slice(0, 400)}` : 'order differs'}`);
      } else {
        let i = 0;
        while (i < a.length && a[i] === b[i]) i++;
        diffs.push(`${key}: differs at …${a.slice(Math.max(0, i - 60), i + 80)}… vs …${b.slice(Math.max(0, i - 60), i + 80)}…`);
      }
    }
  }
  if (diffs.length) {
    failures++;
    console.log(`\n✗ ${p}\n  ${diffs.join('\n  ')}`);
  } else {
    console.log(`✓ ${p}`);
  }
}
console.log(`\n${paths.length - failures}/${paths.length} pages identical in content and metadata`);
process.exitCode = failures ? 1 : 0;
