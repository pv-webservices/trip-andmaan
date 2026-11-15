import { SITE_URL } from './site';

export const organizationId = `${SITE_URL}/#organization`;
export const websiteId = `${SITE_URL}/#website`;

type Crumb = readonly [label: string, href: string];

/** Serialise JSON-LD for a <script> tag, escaping "<" so content cannot close it. */
export const jsonLd = (graph: Record<string, unknown>[]): string =>
  JSON.stringify({ '@context': 'https://schema.org', '@graph': graph }).replace(
    /</g,
    '\u003c',
  );

/** BreadcrumbList from Home through the given crumbs to the current page. */
export const breadcrumbSchema = (
  crumbs: readonly Crumb[],
  current: string,
  path: string,
): Record<string, unknown> => ({
  '@type': 'BreadcrumbList',
  itemListElement: [
    ['Home', '/'] as Crumb,
    ...crumbs,
    [current, path] as Crumb,
  ].map(([name, href], i) => ({
    '@type': 'ListItem',
    position: i + 1,
    name,
    item: new URL(href, SITE_URL).href,
  })),
});

export interface Faq {
  q: string;
  a: string;
}

export const faqSchema = (faqs: readonly Faq[]): Record<string, unknown> => ({
  '@type': 'FAQPage',
  mainEntity: faqs.map(({ q, a }) => ({
    '@type': 'Question',
    name: q,
    acceptedAnswer: { '@type': 'Answer', text: a },
  })),
});

/**
 * Keeps generated meta descriptions within ~160 characters, cutting at a
 * word boundary so search results do not truncate mid-word.
 */
export function clampDescription(text: string, max = 160): string {
  const clean = text.replace(/\s+/g, ' ').trim();
  if (clean.length <= max) return clean;
  const cut = clean.slice(0, max - 1);
  return `${cut.slice(0, cut.lastIndexOf(' ')).replace(/[,;:.\s—-]+$/, '')}…`;
}
