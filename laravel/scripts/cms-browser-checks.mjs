// Browser checks for CMS features: announcement bar and popup banner.
// Creates temporary records through artisan, checks them in Chrome, then
// deletes them. Run with the app served on BASE (default http://127.0.0.1:8000):
//   PHP=php node laravel/scripts/cms-browser-checks.mjs
import { spawnSync } from 'node:child_process';
import path from 'node:path';
import { createRequire } from 'node:module';

const root = path.resolve(import.meta.dirname, '..', '..');
const require = createRequire(path.join(root, 'package.json'));
const { chromium } = require('playwright');
const BASE = process.env.BASE ?? 'http://127.0.0.1:8000';
const PHP = process.env.PHP ?? 'php';
const failures = [];
const check = (ok, message) => {
  console.log(`${ok ? '✓' : '✗'} ${message}`);
  if (!ok) failures.push(message);
};
// ARTISAN overrides how artisan is run, e.g. over SSH or in a container:
//   ARTISAN="ssh user@host php ~/domains/site/current/artisan"
const tinker = (code) => {
  const [cmd, ...prefix] = process.env.ARTISAN
    ? process.env.ARTISAN.split(' ')
    : [PHP, 'artisan'];
  const r = spawnSync(cmd, [...prefix, 'tinker', '--execute', code], {
    cwd: path.join(root, 'laravel'),
    encoding: 'utf8',
    env: { ...process.env, MSYS_NO_PATHCONV: '1' },
  });
  if (r.status !== 0) throw new Error(r.stderr || r.stdout);
  return r.stdout.trim();
};
const cleanup = () =>
  tinker(
    "App\\Models\\Announcement::where('text','like','QA %')->get()->each->delete(); App\\Models\\Popup::where('name','like','QA %')->get()->each->delete();",
  );

cleanup();
const browser = await chromium.launch({ channel: 'chrome' });
try {
  // 1. No announcements: no bar and no reserved space.
  let context = await browser.newContext();
  let page = await context.newPage();
  await page.goto(`${BASE}/about/`);
  check((await page.locator('.announce').count()) === 0, 'no announcement bar markup when none apply');
  const headerTopEmpty = await page.locator('.site-header').evaluate((h) => h.getBoundingClientRect().top);

  // 2. Long announcements scroll, can be paused, and push the header down.
  tinker(
    "App\\Models\\Announcement::create(['text'=>'QA Monsoon departures are open for June and July with flexible dates and island stays','link_url'=>'/tour-packages/','link_label'=>'See packages','is_enabled'=>true,'sort'=>1]); App\\Models\\Announcement::create(['text'=>'QA Ferry timetables change in the monsoon season; we confirm every crossing before you travel','is_enabled'=>true,'sort'=>2]);",
  );
  await page.goto(`${BASE}/about/`);
  const bar = page.locator('.announce');
  check(await bar.isVisible(), 'announcement bar is shown');
  const animated = await page.locator('.announce-track').evaluate((t) => getComputedStyle(t).animationName);
  check(animated === 'announce-scroll', 'long announcements scroll');
  const headerTop = await page.locator('.site-header').evaluate((h) => h.getBoundingClientRect().top);
  check(headerTop > headerTopEmpty, 'header sits below the bar (no overlap)');
  const toggle = page.getByRole('button', { name: 'Pause announcements' });
  await toggle.click();
  check(
    (await page.locator('.announce-track').evaluate((t) => getComputedStyle(t).animationPlayState)) === 'paused' &&
      (await page.getByRole('button', { name: 'Play announcements' }).getAttribute('aria-pressed')) === 'true',
    'pause button stops scrolling and reports its state',
  );
  check(
    (await page.locator('[data-announce-copy] a').first().getAttribute('tabindex')) === '-1',
    'duplicate scrolling copy is hidden from keyboard and screen readers',
  );
  await page.setViewportSize({ width: 320, height: 700 });
  check(
    (await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)) <= 0,
    'no horizontal scroll at 320px with the bar',
  );
  await context.close();

  // 3. Reduced motion: static, no animation, no pause button.
  context = await browser.newContext({ reducedMotion: 'reduce' });
  page = await context.newPage();
  await page.goto(`${BASE}/about/`);
  check(
    (await page.locator('.announce-track').evaluate((t) => getComputedStyle(t).animationName)) === 'none' &&
      !(await page.locator('.announce-toggle').isVisible()),
    'reduced motion: announcements are static',
  );
  await context.close();

  // 4. Page targeting.
  tinker(
    "App\\Models\\Announcement::where('text','like','QA %')->update(['targeting'=>'selected','page_keys'=>json_encode(['contact'])]); App\\Cms\\ContentVersion::bump();",
  );
  context = await browser.newContext();
  page = await context.newPage();
  await page.goto(`${BASE}/about/`);
  check((await page.locator('.announce').count()) === 0, 'targeted announcement hidden on other pages');
  await page.goto(`${BASE}/contact/`);
  check((await page.locator('.announce').count()) === 1, 'targeted announcement shown on its page');
  await context.close();

  // 5. Popup: delay, focus, Escape, frequency, new campaign.
  const id = tinker(
    "echo App\\Models\\Popup::create(['name'=>'QA offer','heading'=>'QA Early bird offer','description'=>'Plan your winter trip early.','button_label'=>'See packages','button_url'=>'/tour-packages/','is_enabled'=>true,'delay_seconds'=>1,'frequency'=>'session','priority'=>10])->id;",
  );
  context = await browser.newContext();
  page = await context.newPage();
  await page.goto(`${BASE}/gallery/`);
  const dialog = page.locator('dialog.promo-dialog');
  check(!(await dialog.isVisible()), 'popup waits for its delay');
  await dialog.waitFor({ state: 'visible', timeout: 4000 });
  check(true, 'popup opens after the delay');
  check(
    (await page.evaluate(() => document.activeElement?.getAttribute('aria-label'))) === 'Close',
    'focus moves to the close button',
  );
  const dialogBox = await dialog.boundingBox();
  const viewport = page.viewportSize();
  check(dialogBox.width <= viewport.width && dialogBox.height <= viewport.height, 'popup fits the viewport');
  await page.keyboard.press('Escape');
  check(!(await dialog.isVisible()), 'Escape closes the popup');
  await page.reload();
  await page.waitForTimeout(2000);
  check(!(await dialog.isVisible()), '"once per session" popup does not return after reload');

  tinker(`App\\Models\\Popup::find(${id})->update(['heading'=>'QA New campaign']);`);
  await page.reload();
  await page.locator('dialog.promo-dialog').waitFor({ state: 'visible', timeout: 4000 });
  check(true, 'a changed campaign is shown again (dismissal not inherited)');
  await page.getByRole('button', { name: 'Close' }).click();
  check(!(await page.locator('dialog.promo-dialog').isVisible()), 'close button closes the popup');

  // Mobile size.
  const mobile = await browser.newContext({ viewport: { width: 375, height: 667 } });
  const mp = await mobile.newPage();
  await mp.goto(`${BASE}/`);
  await mp.locator('dialog.promo-dialog').waitFor({ state: 'visible', timeout: 4000 });
  const mBox = await mp.locator('dialog.promo-dialog').boundingBox();
  check(mBox.width <= 375 && mBox.height <= 667, 'popup fits a 375×667 phone screen');
  await mp.screenshot({ path: path.join(root, 'laravel', 'storage', 'logs', 'popup-mobile.png') });
  await mobile.close();
  await context.close();
} finally {
  await browser.close();
  cleanup();
}
console.log(failures.length ? `\n${failures.length} failure(s)` : '\nAll CMS browser checks passed.');
process.exitCode = failures.length ? 1 : 0;
