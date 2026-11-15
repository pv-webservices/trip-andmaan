import { execFileSync } from 'node:child_process';
import { destinations } from './destinations';
import { publishedPackages } from './packages';

export interface SitemapEntry {
  path: string;
  /** Source files whose last commit date is the page's lastmod. */
  sources: string[];
}

const LAYOUT = ['src/layouts/BaseLayout.astro'];
const page = (
  path: string,
  file: string,
  data: string[] = [],
): SitemapEntry => ({
  path,
  sources: [`src/pages/${file}`, ...data, ...LAYOUT],
});

/** Every indexable page. Keep noindex pages (404, image credits) out. */
export const sitemapEntries: SitemapEntry[] = [
  page('/', 'index.astro', ['src/data/site.ts', 'src/data/packages.ts']),
  page('/about/', 'about.astro'),
  page('/tour-packages/', 'tour-packages/index.astro', [
    'src/data/packages.ts',
  ]),
  page(
    '/tour-packages/customized-trip/',
    'tour-packages/customized-trip.astro',
    ['src/components/EnquiryForm.astro'],
  ),
  page('/destinations/', 'destinations/index.astro', [
    'src/data/destinations.ts',
  ]),
  page('/experiences/', 'experiences.astro', ['src/data/site.ts']),
  page('/gallery/', 'gallery.astro'),
  page('/contact/', 'contact.astro', ['src/components/EnquiryForm.astro']),
  page('/travel-information/', 'travel-information.astro'),
  page('/privacy/', 'privacy.astro'),
  ...destinations.map((d) =>
    page(`/destinations/${d.slug}/`, 'destinations/[slug].astro', [
      'src/data/destinations.ts',
    ]),
  ),
  ...publishedPackages.map((p) =>
    page(`/tour-packages/${p.slug}/`, 'tour-packages/[slug].astro', [
      'src/data/packages.ts',
      'src/data/itinerary-days.ts',
    ]),
  ),
];

/** ISO date of the latest commit touching the sources, or undefined without git history. */
export function lastModified(sources: string[]): string | undefined {
  try {
    const out = execFileSync(
      'git',
      ['log', '-1', '--format=%cI', '--', ...sources],
      {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'ignore'],
      },
    ).trim();
    return out ? out.slice(0, 10) : undefined;
  } catch {
    return undefined;
  }
}
