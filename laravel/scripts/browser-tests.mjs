// Browser checks against the Laravel site (adapted from scripts/browser-tests.mjs).
//   Start the app first (php artisan serve --port=8000), then:
//   node laravel/scripts/browser-tests.mjs            (BASE=http://127.0.0.1:8000 by default)
// Uses the installed Google Chrome (channel "chrome"). All WhatsApp traffic is
// intercepted: no chat is opened and no message is sent. tel: links are only
// read, never clicked.
import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { createRequire } from 'node:module';

const root = path.resolve(import.meta.dirname, '..', '..');
const require = createRequire(path.join(root, 'package.json'));
const { chromium } = require('playwright');
const BASE = process.env.BASE ?? 'http://127.0.0.1:8000';
const WHATSAPP = '919646969646';
const VIEWPORTS = [320, 375, 768, 1024, 1440];
const axeSource = await readFile(require.resolve('axe-core/axe.min.js'), 'utf8');
const results = {
  pages: [],
  viewports: [],
  ctas: [],
  composer: {},
  keyboard: {},
  failures: [],
};
const fail = (where, message) => results.failures.push(`${where}: ${message}`);

async function routes() {
  const xml = await (await fetch(`${BASE}/sitemap.xml`)).text();
  const paths = [...xml.matchAll(/<loc>[^<]*?\.[a-z]+(\/[^<]*)<\/loc>/g)].map((m) => m[1] || '/');
  return [...new Set([...paths, '/image-credits/'])].sort();
}

/** Blocks wa.me and records window.open calls instead of opening anything. */
async function isolate(context) {
  await context.route(
    /https:\/\/(wa\.me|api\.whatsapp\.com|web\.whatsapp\.com)\//,
    (route) => route.abort(),
  );
  await context.addInitScript(() => {
    window.__opened = [];
    window.open = (url) => {
      window.__opened.push(String(url));
      return window.__popupBlocked ? null : { opener: window, closed: false };
    };
  });
}

const browser = await chromium.launch({ channel: 'chrome' });
try {
  const context = await browser.newContext();
  await isolate(context);
  const all = await routes();

  // 1. Every page: status, console errors, failed requests, axe WCAG 2.1 AA.
  for (const route of all) {
    const page = await context.newPage();
    const errors = [];
    page.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
    page.on('pageerror', (e) => errors.push(e.message));
    page.on('response', (r) => {
      if (r.url().startsWith(BASE) && r.status() >= 400)
        errors.push(`${r.status()} ${r.url()}`);
    });
    const response = await page.goto(BASE + route, {
      waitUntil: 'networkidle',
    });
    await page.evaluate(axeSource);
    const axe = await page.evaluate(() =>
      window.axe.run(document, {
        runOnly: {
          type: 'tag',
          values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'],
        },
      }),
    );
    const violations = axe.violations.map(
      (v) => `${v.id} (${v.nodes.length}): ${v.nodes[0]?.target.join(' ')}`,
    );
    results.pages.push({
      route,
      status: response.status(),
      consoleErrors: errors,
      axeViolations: violations,
    });
    if (response.status() !== 200) fail(route, `HTTP ${response.status()}`);
    for (const e of errors) fail(route, `console/network error: ${e}`);
    for (const v of violations) fail(route, `axe ${v}`);
    await page.close();
  }

  // 2. Real 404 status for unknown URLs.
  const missing = await fetch(`${BASE}/this-page-does-not-exist/`);
  await missing.text();
  results.notFoundStatus = missing.status;
  if (missing.status !== 404)
    fail('/this-page-does-not-exist/', `expected 404, got ${missing.status}`);

  // 3. Responsive: no horizontal scroll, tap targets, a usable WhatsApp CTA.
  const sample = [
    '/',
    '/contact/',
    '/tour-packages/andaman-explorer-baratang-6n7d/',
    '/destinations/havelock-island/',
    '/gallery/',
  ];
  for (const width of VIEWPORTS) {
    const page = await context.newPage({ viewport: { width, height: 800 } });
    for (const route of sample) {
      await page.goto(BASE + route, { waitUntil: 'networkidle' });
      const check = await page.evaluate(() => {
        const overflow =
          document.documentElement.scrollWidth - window.innerWidth;
        const small = [
          ...document.querySelectorAll(
            'a[href], button, input, select, textarea',
          ),
        ]
          .filter((el) => {
            const r = el.getBoundingClientRect();
            const style = getComputedStyle(el);
            if (!r.width || !r.height || style.visibility === 'hidden')
              return false;
            if (
              el.closest(
                'p, li, dd, figcaption, label, .breadcrumbs, .credit-list',
              )
            )
              return false; // inline text links
            return r.width < 24 || r.height < 24;
          })
          .map((el) => el.outerHTML.slice(0, 80));
        const wa = [
          ...document.querySelectorAll('a[href^="https://wa.me/"]'),
        ].filter((a) => {
          const r = a.getBoundingClientRect();
          return (
            r.width >= 24 &&
            r.height >= 24 &&
            r.bottom > 0 &&
            r.top < window.innerHeight &&
            getComputedStyle(a).visibility !== 'hidden'
          );
        }).length;
        return { overflow, small, visibleWhatsapp: wa };
      });
      results.viewports.push({ width, route, ...check });
      if (check.overflow > 0)
        fail(`${route}@${width}`, `horizontal scroll of ${check.overflow}px`);
      if (check.small.length)
        fail(
          `${route}@${width}`,
          `tap targets under 24px: ${check.small.slice(0, 3).join(' | ')}`,
        );
      if (!check.visibleWhatsapp)
        fail(
          `${route}@${width}`,
          'no WhatsApp CTA visible in the first screen',
        );
    }
    await page.close();
  }

  // 4. Every WhatsApp and tel link on representative pages.
  const page = await context.newPage();
  for (const route of [
    ...sample,
    '/experiences/',
    '/tour-packages/customized-trip/',
    '/about/',
    '/privacy/',
  ]) {
    await page.goto(BASE + route, { waitUntil: 'networkidle' });
    const links = await page.$$eval(
      'a[href^="https://wa.me/"], a[href^="tel:"]',
      (as) =>
        as.map((a) => ({
          href: a.href,
          text: a.textContent.trim().replace(/\s+/g, ' '),
          target: a.target,
          rel: a.rel,
        })),
    );
    for (const link of links) {
      if (link.href.startsWith('tel:')) {
        if (link.href !== 'tel:+919646969646')
          fail(route, `tel link ${link.href}`);
        continue;
      }
      const url = new URL(link.href);
      const text = url.searchParams.get('text') ?? '';
      results.ctas.push({ route, label: link.text, message: text });
      if (url.pathname !== `/${WHATSAPP}`)
        fail(route, `WhatsApp number ${url.pathname}`);
      if (
        !/^Hello Trip Andaman, I would like to enquire about .+\.$/s.test(text)
      )
        fail(route, `unexpected WhatsApp text "${text}"`);
      if (link.target !== '_blank' || !link.rel.includes('noopener'))
        fail(route, `WhatsApp link "${link.text}" missing target/rel`);
    }
  }
  // A WhatsApp link click is a plain navigation (no popup script) — intercepted here.
  await page.goto(`${BASE}/tour-packages/andaman-explorer-baratang-6n7d/`);
  const [request] = await Promise.all([
    context.waitForEvent('request', (r) => r.url().includes('wa.me')),
    page.getByRole('link', { name: 'Get a Quote on WhatsApp' }).click(),
  ]);
  results.ctaNavigation = request.url();
  if (!request.url().startsWith(`https://wa.me/${WHATSAPP}?text=`))
    fail('package CTA', `navigated to ${request.url()}`);
  for (const p of context.pages()) if (p !== page) await p.close();

  // 5. WhatsApp composer.
  const c = results.composer;
  await page.goto(`${BASE}/contact/?interest=Neil%20Island`, {
    waitUntil: 'networkidle',
  });
  await page
    .getByRole('button', { name: 'Open WhatsApp with my enquiry' })
    .click();
  c.emptySubmit = await page.evaluate(() => ({
    summary: document.querySelector('.form-summary').textContent,
    summaryVisible: !document.querySelector('.form-summary').hidden,
    invalid: [...document.querySelectorAll('[aria-invalid="true"]')].map(
      (e) => e.id,
    ),
    describedBy: document
      .querySelector('#full-name')
      .getAttribute('aria-describedby'),
    focused: document.activeElement.id,
    opened: window.__opened.length,
  }));
  if (!c.emptySubmit.summaryVisible) fail('composer', 'no error summary');
  if (c.emptySubmit.focused !== 'full-name')
    fail('composer', `focus on ${c.emptySubmit.focused}, expected full-name`);
  if (c.emptySubmit.describedBy !== 'full-name-error')
    fail('composer', 'error not linked with aria-describedby');
  if (c.emptySubmit.opened)
    fail('composer', 'opened WhatsApp with invalid data');
  for (const id of ['full-name', 'mobile', 'message', 'consent'])
    if (!c.emptySubmit.invalid.includes(id))
      fail('composer', `${id} not marked invalid`);

  await page.getByLabel('Full name').fill('QA Visitor');
  await page.getByLabel(/Mobile number/).fill('12345');
  await page
    .getByLabel('What do you have in mind?')
    .fill('Local QA only: island trip for two.');
  await page.getByRole('checkbox').check();
  // Autofill-style value set by script, without input events.
  await page.evaluate(
    () => (document.querySelector('#travellers').value = '0'),
  );
  await page
    .getByRole('button', { name: 'Open WhatsApp with my enquiry' })
    .click();
  c.invalidPhone = await page.evaluate(() => ({
    mobileError: document.querySelector('#mobile-error').textContent,
    travellersError: document.querySelector('#travellers-error').textContent,
    keptName: document.querySelector('#full-name').value,
    keptInterest: document.querySelector('#interest').value,
    focused: document.activeElement.id,
    opened: window.__opened.length,
  }));
  if (!c.invalidPhone.mobileError) fail('composer', 'short phone accepted');
  if (!c.invalidPhone.travellersError)
    fail('composer', 'script-set travellers=0 accepted');
  if (
    c.invalidPhone.keptName !== 'QA Visitor' ||
    c.invalidPhone.keptInterest !== 'Neil Island'
  )
    fail('composer', 'values lost after failed validation');
  if (c.invalidPhone.opened)
    fail('composer', 'opened WhatsApp with invalid phone');

  await page.getByLabel(/Mobile number/).fill('+91 99999 99999');
  await page.getByLabel(/Number of travellers/).fill('2');
  await page
    .getByRole('button', { name: 'Open WhatsApp with my enquiry' })
    .click();
  c.valid = await page.evaluate(() => ({
    opened: window.__opened,
    status: document.querySelector('.form-status').textContent,
    actionsVisible: !document.querySelector('.composer-actions').hidden,
    keptName: document.querySelector('#full-name').value,
  }));
  const sent = new URL(c.valid.opened[0] ?? 'https://invalid/');
  const text = sent.searchParams.get('text') ?? '';
  c.valid.message = text;
  if (sent.pathname !== `/${WHATSAPP}`) fail('composer', 'opened wrong number');
  for (const part of [
    'QA Visitor',
    '+91 99999 99999',
    'Travellers: 2',
    'Neil Island',
    'island trip for two',
    'Contact page',
  ])
    if (!text.includes(part)) fail('composer', `message missing "${part}"`);
  if (/\bsent\b(?! until)/i.test(c.valid.status.replace('not sent until', '')))
    fail('composer', `status claims delivery: ${c.valid.status}`);
  if (!c.valid.actionsVisible) fail('composer', 'fallback actions not shown');
  if (c.valid.keptName !== 'QA Visitor')
    fail('composer', 'form cleared after opening WhatsApp');

  await page.evaluate(() => {
    window.__popupBlocked = true;
    window.__opened = [];
  });
  await page
    .getByRole('button', { name: 'Open WhatsApp with my enquiry' })
    .click();
  c.blocked = await page.evaluate(() => ({
    status: document.querySelector('.form-status').textContent,
    fallback: document.querySelector('[data-composer-link]').href,
    call: document.querySelector('.composer-actions a[href^="tel:"]')?.href,
  }));
  if (!/did not open/.test(c.blocked.status))
    fail('composer', 'no message when WhatsApp is blocked');
  if (!c.blocked.fallback.includes('QA%20Visitor'))
    fail('composer', 'fallback link lacks the prepared message');
  if (c.blocked.call !== 'tel:+919646969646')
    fail('composer', 'no call fallback');

  // 5b. A submit before the script attaches must not navigate or leak data.
  const early = await context.newPage();
  await early.route('**/*.js', (r) => r.abort());
  await early.goto(`${BASE}/contact/`);
  await early.evaluate(() => {
    document.documentElement.classList.add('js');
    document.querySelector('#full-name').value = 'Leak Test';
    document.querySelector('.whatsapp-composer').requestSubmit();
  });
  await early.waitForTimeout(500);
  c.preScriptSubmitUrl = early.url();
  if (early.url() !== `${BASE}/contact/`)
    fail('composer', `native submit navigated to ${early.url()}`);
  await early.close();

  // 6. JavaScript disabled: form hidden, direct links shown.
  const noJs = await browser.newContext({ javaScriptEnabled: false });
  await noJs.route(/wa\.me/, (r) => r.abort());
  const nj = await noJs.newPage();
  await nj.goto(`${BASE}/contact/`);
  c.noJs = await nj.evaluate(() => ({
    formDisplay: getComputedStyle(document.querySelector('.whatsapp-composer'))
      .display,
    fallbackLinks: [
      ...document.querySelectorAll('.enquiry-form a[href^="https://wa.me/"]'),
    ].filter((a) => a.getClientRects().length).length,
  }));
  if (c.noJs.formDisplay !== 'none')
    fail('composer no-JS', 'form still shown without JavaScript');
  if (c.noJs.fallbackLinks < 1)
    fail('composer no-JS', 'no direct WhatsApp link');
  await noJs.close();

  // 7. Keyboard: skip link, focus visibility; reduced motion.
  const k = results.keyboard;
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  // :focus styles only apply in the foreground tab.
  await page.bringToFront();
  await page.keyboard.press('Tab');
  // The skip link slides into view on focus; wait for it to settle.
  const shown = await page
    .waitForFunction(
      () => document.activeElement.getBoundingClientRect().top >= 0,
      null,
      { timeout: 3000 },
    )
    .then(
      () => true,
      () => false,
    );
  k.firstFocus = await page.evaluate(() => ({
    text: document.activeElement.textContent.trim(),
    rect: document.activeElement.getBoundingClientRect().toJSON(),
    transform: getComputedStyle(document.activeElement).transform,
    outline: getComputedStyle(document.activeElement).outlineStyle,
  }));
  k.firstFocus.visible = shown;
  if (k.firstFocus.text !== 'Skip to content' || !k.firstFocus.visible)
    fail('keyboard', 'skip link is not the first visible focus');
  await page.keyboard.press('Enter');
  k.afterSkip = await page.evaluate(() => document.activeElement.id);
  if (k.afterSkip !== 'main')
    fail('keyboard', `skip link moved focus to "${k.afterSkip}"`);
  await page.keyboard.press('Tab');
  k.nextFocusOutline = await page.evaluate(() => {
    const s = getComputedStyle(document.activeElement);
    return `${s.outlineStyle} ${s.outlineWidth} / ${s.boxShadow}`;
  });
  if (/^none 0px \/ none$/.test(k.nextFocusOutline))
    fail('keyboard', 'no visible focus indicator');
  const reduced = await browser.newContext({ reducedMotion: 'reduce' });
  await isolate(reduced);
  const rp = await reduced.newPage();
  await rp.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  k.reducedMotion = await rp.evaluate(() => ({
    runningAnimations: document
      .getAnimations()
      .filter(
        (a) => a.playState === 'running' && a.effect?.getTiming().duration > 50,
      ).length,
    autoplayingVideos: [...document.querySelectorAll('video')].filter(
      (v) => !v.paused,
    ).length,
  }));
  if (k.reducedMotion.runningAnimations)
    fail(
      'reduced motion',
      `${k.reducedMotion.runningAnimations} animations still running`,
    );
  await reduced.close();
} finally {
  await browser.close();

}

await mkdir('laravel/storage/logs', { recursive: true });
await writeFile(
  'laravel/storage/logs/browser-results.json',
  JSON.stringify(results, null, 2),
);
const axeTotal = results.pages.reduce((n, p) => n + p.axeViolations.length, 0);
console.log(
  `Pages: ${results.pages.length} (axe violations: ${axeTotal}); viewport checks: ${results.viewports.length}; WhatsApp CTAs checked: ${results.ctas.length}; 404 status: ${results.notFoundStatus}.`,
);
if (results.failures.length) {
  console.error(
    `${results.failures.length} failure(s):\n- ${results.failures.join('\n- ')}`,
  );
  process.exit(1);
}
console.log(
  'All browser checks passed. Details: laravel/storage/logs/browser-results.json',
);
