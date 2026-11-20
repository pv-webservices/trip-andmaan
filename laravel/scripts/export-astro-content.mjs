// Exports the Astro site's TypeScript data files to JSON for `php artisan cms:import`.
// Run from the repository root:  node laravel/scripts/export-astro-content.mjs
// Uses Node's built-in TypeScript type stripping (Node 22.18+ / 24); local only.
import { existsSync } from 'node:fs';
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import { registerHooks } from 'node:module';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

// Astro resolves "./images" to "./images.ts" and imports JSON without
// attributes; teach Node the same two rules for this script only.
registerHooks({
  resolve(specifier, context, next) {
    if (specifier.startsWith('.') && !path.extname(specifier) && context.parentURL) {
      const candidate = new URL(`${specifier}.ts`, context.parentURL);
      if (existsSync(fileURLToPath(candidate))) return next(candidate.href, context);
    }
    return next(specifier, context);
  },
  load(url, context, next) {
    if (url.endsWith('.json')) {
      return { format: 'module', shortCircuit: true, source: `export default ${String(next(url, { ...context, format: 'json', importAttributes: { type: 'json' } }).source)};` };
    }
    return next(url, context);
  },
});

const root = path.resolve(import.meta.dirname, '..', '..');
const out = path.join(root, 'laravel', 'database', 'content');
const load = (file) => import(pathToFileURL(path.join(root, 'src', 'data', file)).href);

const { packages } = await load('packages.ts');
const { destinations } = await load('destinations.ts');
const { reels } = await load('reels.ts');
const { testimonials } = await load('testimonials.ts');
const site = await load('site.ts');
const json = async (file) => JSON.parse(await readFile(path.join(root, 'src', 'data', file), 'utf8'));

const write = (name, value) => writeFile(path.join(out, name), `${JSON.stringify(value, null, 2)}\n`);

await mkdir(out, { recursive: true });
await write('packages.json', packages);
await write('destinations.json', destinations);
await write('reels.json', reels);
await write('testimonials.json', testimonials);
await write('site.json', {
  siteUrl: site.SITE_URL,
  whatsappNumber: site.WHATSAPP_NUMBER,
  site: site.site,
  navigation: site.navigation,
  experiences: site.experiences,
  planning: site.planning,
  promises: site.promises,
});
await write('image-manifest.json', await json('image-manifest.json'));
await write('credits.json', await json('credits.json'));
console.log(`Exported ${packages.length} packages, ${destinations.length} destinations, ${reels.length} reels to ${path.relative(root, out)}`);
