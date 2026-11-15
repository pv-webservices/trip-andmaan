// Static SEO, link and contact audit of the built site (dist/).
//   npm run build && npm run audit
// Exits non-zero and lists every issue found.
import { readFile, readdir, stat } from 'node:fs/promises';
import path from 'node:path';

const SITE = 'https://tripandaman.in';
const WHATSAPP = '919646969646';
const TEL = '+919646969646';
const dist = path.resolve('dist');
const issues = [];
const fail = (where, message) => issues.push(`${where}: ${message}`);

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true });
  const nested = await Promise.all(
    entries.map((e) =>
      e.isDirectory() ? walk(path.join(dir, e.name)) : [path.join(dir, e.name)],
    ),
  );
  return nested.flat();
}
const exists = async (file) =>
  stat(file).then(
    () => true,
    () => false,
  );
const decode = (s) =>
  s
    .replace(/&amp;/g, '&')
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
const attr = (tag, name) => {
  // A bare attribute (e.g. `alt`) is an empty value, as in HTML.
  const m = tag.match(
    new RegExp(`\\s${name}(?:=(?:"([^"]*)"|'([^']*)'))?(?=[\\s>/])`, 'i'),
  );
  return m ? decode(m[1] ?? m[2] ?? '') : undefined;
};
const meta = (html, key, value) => {
  for (const tag of html.match(/<meta\b[^>]*>/gi) ?? [])
    if (attr(tag, key) === value) return attr(tag, 'content');
  return undefined;
};
const pagePath = (file) => {
  const rel = path.relative(dist, file).split(path.sep).join('/');
  if (rel === '404.html') return '/404.html';
  return `/${rel.replace(/index\.html$/, '')}`;
};

const files = await walk(dist);
const pages = files.filter((f) => f.endsWith('.html'));
const titles = new Map();
const descriptions = new Map();
const indexable = new Set();
const noindexed = new Set();

for (const file of pages) {
  const html = await readFile(file, 'utf8');
  const route = pagePath(file);
  const where = route;

  // Title and description.
  const title = decode(
    html.match(/<title>([^<]*)<\/title>/)?.[1]?.trim() ?? '',
  );
  if (!title) fail(where, 'missing <title>');
  else if (title.length > 65)
    fail(where, `title is ${title.length} chars (max 65): "${title}"`);
  titles.set(title, [...(titles.get(title) ?? []), route]);
  const description = meta(html, 'name', 'description') ?? '';
  if (description.length < 70 || description.length > 170)
    fail(where, `description is ${description.length} chars (70–170)`);
  descriptions.set(description, [
    ...(descriptions.get(description) ?? []),
    route,
  ]);

  // Robots / canonical.
  const robots = meta(html, 'name', 'robots') ?? '';
  const canonical = html.match(/<link\b[^>]*rel="canonical"[^>]*>/i);
  const isNoindex = /noindex/.test(robots);
  if (isNoindex) {
    noindexed.add(route);
    if (robots !== 'noindex, follow')
      fail(where, `robots should be "noindex, follow", got "${robots}"`);
    if (canonical) fail(where, 'noindex page must not have a canonical tag');
  } else {
    indexable.add(route);
    if (!/index, follow, max-image-preview:large/.test(robots))
      fail(
        where,
        `robots meta "${robots}" missing index, follow, max-image-preview:large`,
      );
    const href = canonical ? attr(canonical[0], 'href') : undefined;
    if (href !== `${SITE}${route}`)
      fail(
        where,
        `canonical "${href}" is not self-referencing ${SITE}${route}`,
      );
    if (meta(html, 'property', 'og:url') !== `${SITE}${route}`)
      fail(where, 'og:url missing or wrong');
  }
  if (route === '/404.html' && !isNoindex)
    fail(where, '404 page must be noindex');

  // Open Graph / Twitter.
  for (const key of [
    'og:title',
    'og:description',
    'og:type',
    'og:image',
    'og:image:width',
    'og:image:height',
    'og:image:alt',
  ])
    if (!meta(html, 'property', key)) fail(where, `missing ${key}`);
  for (const key of [
    'twitter:card',
    'twitter:title',
    'twitter:description',
    'twitter:image',
  ])
    if (!meta(html, 'name', key)) fail(where, `missing ${key}`);
  const ogImage = meta(html, 'property', 'og:image') ?? '';
  if (!ogImage.startsWith(SITE))
    fail(where, 'og:image must be an absolute URL on the site');
  else if (!(await exists(path.join(dist, new URL(ogImage).pathname))))
    fail(where, `og:image ${ogImage} not built`);

  // Favicons / manifest on every page.
  for (const needle of [
    'href="/favicon.ico"',
    'href="/favicon.svg"',
    'href="/favicon-96x96.png"',
    'rel="apple-touch-icon"',
    'rel="manifest"',
  ])
    if (!html.includes(needle)) fail(where, `missing head link ${needle}`);

  // Headings: one h1, no skipped levels.
  const levels = [...html.matchAll(/<h([1-6])\b/gi)].map((m) => Number(m[1]));
  if (levels.filter((l) => l === 1).length !== 1)
    fail(
      where,
      `expected exactly one h1, found ${levels.filter((l) => l === 1).length}`,
    );
  let deepest = 0;
  for (const level of levels) {
    if (level > deepest + 1) {
      fail(where, `heading level skips from h${deepest || 0} to h${level}`);
      break;
    }
    deepest = Math.max(deepest, level);
  }

  // Images need an alt attribute (alt="" is allowed for decorative images).
  for (const img of html.match(/<img\b[^>]*>/gi) ?? [])
    if (attr(img, 'alt') === undefined)
      fail(where, `image without alt: ${img.slice(0, 80)}`);

  // JSON-LD parses, has an organisation, no email.
  const blocks = [
    ...html.matchAll(
      /<script type="application\/ld\+json">([\s\S]*?)<\/script>/g,
    ),
  ];
  if (blocks.length === 0) fail(where, 'no JSON-LD');
  for (const [, json] of blocks) {
    if (json.includes('<')) fail(where, 'JSON-LD contains an unescaped "<"');
    try {
      JSON.parse(json);
    } catch {
      fail(where, 'JSON-LD does not parse');
    }
    if (/"email"|@[a-z0-9-]+\.[a-z]{2,}/i.test(json))
      fail(where, 'JSON-LD contains an email');
  }
  if (!html.includes('"@type":"TravelAgency"'))
    fail(where, 'missing TravelAgency JSON-LD');
  if (route !== '/' && !isNoindex && !html.includes('"@type":"BreadcrumbList"'))
    fail(where, 'inner page missing BreadcrumbList');

  // No email anywhere; no form that posts data.
  if (/mailto:|formsubmit|nodemailer|smtp/i.test(html))
    fail(where, 'email or form-mailer reference found');
  if (/[a-z0-9._%+-]+@(?:gmail|yahoo|outlook|hotmail)\.com/i.test(html))
    fail(where, 'email address found');
  if (/type="email"/i.test(html)) fail(where, 'email input found');
  for (const form of html.match(/<form\b[^>]*>/gi) ?? [])
    if (attr(form, 'action') !== undefined || attr(form, 'method') !== 'dialog')
      fail(
        where,
        `form must be method="dialog" with no action (no native submission): ${form.slice(0, 80)}`,
      );

  // Contact links point at the confirmed number.
  for (const m of html.matchAll(/href="(https:\/\/wa\.me\/[^"]*)"/g)) {
    const url = new URL(decode(m[1]));
    if (url.pathname !== `/${WHATSAPP}`)
      fail(where, `WhatsApp link to wrong number: ${url.pathname}`);
    if (!url.searchParams.get('text'))
      fail(where, 'WhatsApp link without prefilled text');
  }
  for (const m of html.matchAll(/href="tel:([^"]*)"/g))
    if (m[1] !== TEL) fail(where, `tel link to unexpected number ${m[1]}`);
  for (const a of html.match(/<a\b[^>]*target="_blank"[^>]*>/gi) ?? [])
    if (!/rel="[^"]*noopener/.test(a))
      fail(where, `target=_blank without rel=noopener: ${a.slice(0, 80)}`);

  // Internal links and assets resolve.
  const refs = new Set();
  for (const m of html.matchAll(/\s(?:href|src|poster)="([^"]+)"/g))
    refs.add(decode(m[1]));
  for (const m of html.matchAll(/\ssrcset="([^"]+)"/g))
    for (const part of decode(m[1]).split(','))
      refs.add(part.trim().split(/\s+/)[0]);
  for (const ref of refs) {
    if (!ref.startsWith('/') || ref.startsWith('//')) continue;
    const clean = ref.split(/[?#]/)[0];
    if (/\/index\.html$/.test(clean))
      fail(where, `link to ${clean} (use the directory URL)`);
    if (!clean.endsWith('/') && !path.extname(clean))
      fail(where, `link without trailing slash: ${clean}`);
    const target = path.join(
      dist,
      decodeURIComponent(clean),
      clean.endsWith('/') ? 'index.html' : '',
    );
    if (!(await exists(target)))
      fail(where, `broken internal link/asset ${ref}`);
  }
}

for (const [title, routes] of titles)
  if (routes.length > 1)
    fail('site', `duplicate title "${title}" on ${routes.join(', ')}`);
for (const [d, routes] of descriptions)
  if (routes.length > 1)
    fail(
      'site',
      `duplicate description on ${routes.join(', ')}: "${d.slice(0, 60)}…"`,
    );

// URLs are lowercase and hyphenated.
for (const route of [...indexable, ...noindexed])
  if (
    route !== '/404.html' &&
    !/^\/(?:[a-z0-9]+(?:-[a-z0-9]+)*\/)*$/.test(route)
  )
    fail(route, 'URL is not lowercase-hyphenated with a trailing slash');

// Sitemap ↔ indexability.
const sitemap = await readFile(path.join(dist, 'sitemap.xml'), 'utf8');
const locs = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map(
  (m) => new URL(m[1]).pathname,
);
for (const m of sitemap.matchAll(/<lastmod>([^<]+)<\/lastmod>/g))
  if (!/^\d{4}-\d{2}-\d{2}$/.test(m[1]))
    fail('sitemap.xml', `bad lastmod ${m[1]}`);
for (const loc of locs) {
  if (noindexed.has(loc)) fail('sitemap.xml', `noindex page listed: ${loc}`);
  else if (!indexable.has(loc))
    fail('sitemap.xml', `URL has no built page: ${loc}`);
}
for (const route of indexable)
  if (!locs.includes(route))
    fail('sitemap.xml', `indexable page missing: ${route}`);
if (new Set(locs).size !== locs.length) fail('sitemap.xml', 'duplicate URLs');

// robots.txt.
const robots = await readFile(path.join(dist, 'robots.txt'), 'utf8');
if (!robots.includes(`Sitemap: ${SITE}/sitemap.xml`))
  fail('robots.txt', 'missing Sitemap directive');
if (/^Disallow:\s*\/\s*$/m.test(robots))
  fail('robots.txt', 'blocks the whole site');
if (/Disallow:.*(\.css|\.js|_astro)/.test(robots))
  fail('robots.txt', 'blocks CSS/JS');

// Required static files; no raw masters or oversized images shipped.
for (const file of [
  'favicon.ico',
  'favicon.svg',
  'favicon-48x48.png',
  'favicon-96x96.png',
  'apple-touch-icon.png',
  'web-app-manifest-192x192.png',
  'web-app-manifest-512x512.png',
  'site.webmanifest',
  '404.html',
])
  if (!(await exists(path.join(dist, file)))) fail('dist', `missing ${file}`);
for (const file of files) {
  const rel = path.relative(dist, file).split(path.sep).join('/');
  if (
    /^source-files\/|new material|\.(jpe?g|png)$/i.test(rel) &&
    !/^(images\/brand\/|apple-touch-icon|favicon|web-app-manifest)/.test(rel)
  )
    fail('dist', `unexpected raw/master file shipped: ${rel}`);
  if (/\.(webp|jpe?g|png)$/i.test(rel) && (await stat(file)).size > 400 * 1024)
    fail('dist', `image over 400 KB: ${rel}`);
  if (/\.(html|js|css|json|txt|xml)$/.test(rel)) {
    const text = await readFile(file, 'utf8');
    if (/sk-[A-Za-z0-9_-]{20,}|OPENAI_API_KEY/.test(text))
      fail(rel, 'possible API credential');
  }
}

if (issues.length) {
  console.error(
    `SEO audit found ${issues.length} issue(s):\n- ${issues.join('\n- ')}`,
  );
  process.exit(1);
}
console.log(
  `SEO audit passed: ${pages.length} pages (${indexable.size} indexable, ${noindexed.size} noindex), ${locs.length} sitemap URLs, no broken internal links, no email references.`,
);
