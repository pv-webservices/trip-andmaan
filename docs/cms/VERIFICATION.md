# Verification results (20 Nov 2026)

Three environments were used. **Nothing has been run on the client's Hostinger
account yet**, and the live site (Astro on Vercel, DNS at GoDaddy) was not changed.

| Environment | What it is |
| --- | --- |
| Local | Windows 11, PHP 8.3.36 (NTS), `php artisan serve`, SQLite |
| MariaDB | MariaDB 11.4.13 in Docker (Hostinger shared hosting runs MariaDB) |
| Hosting rehearsal | Docker: PHP 8.3 + Apache with `.htaccess` (Hostinger's LiteSpeed reads the same rules), GD/WebP, intl, zip, pdo_mysql; the exact Hostinger folder layout (`public_html -> current/public`, `shared/.env`, `shared/storage`), `APP_ENV=production`, `APP_DEBUG=false`, config/route/view caches, connected to MariaDB 11.4. Deployed from the real release archive with `deploy-release.sh`. |

## Automated tests

| Check | Local | MariaDB | Hosting rehearsal |
| --- | --- | --- | --- |
| PHPUnit (`php artisan test`): 64 tests, 540 assertions | ✓ pass | ✓ pass (see note) | — |
| Enquiry composer unit tests (`npm test`): 13 | ✓ pass | — | — |
| Astro fidelity (`compare-with-astro.mjs`): title, meta, robots, canonical, OG, JSON-LD, h1–h3, links, image alt text and visible text of all 32 pages vs `dist/` | ✓ 32/32 identical | — | ✓ 32/32 identical |
| Browser audit (`browser-tests.mjs`, Chrome): axe WCAG 2.1 A/AA on 32 pages, 320–1440 px layouts (no horizontal scroll, ≥24 px tap targets, visible WhatsApp CTA), 64 WhatsApp/tel links, composer validation/no-JS/blocked-popup behaviour, skip link, focus, reduced motion. WhatsApp traffic intercepted; nothing sent. | ✓ 0 violations, all pass | — | ✓ 0 violations, all pass |
| CMS browser checks (`cms-browser-checks.mjs`): 19 checks of the announcement bar and popup | ✓ 19/19 | — | ✓ 19/19 |

Both PHPUnit runs (SQLite and a throwaway MariaDB 11.4 database) passed all 64 tests
and 540 assertions; the suite was re-run on SQLite after Pint formatting.

### What the PHPUnit suite covers

- **Authentication**: guests redirected to login; no registration or e-mail reset
  routes; deactivated admins refused; passwords hashed; login throttled after
  repeated failures; CSRF and session middleware configured on the panel.
- **Draft / preview / publish**: drafts invisible publicly; preview only for signed-in
  admins with `noindex` and `no-store`; publish shows changes on the next request
  (page cache invalidated); discard restores; hidden sections leave no markup;
  `javascript:` links and unsafe rich text rejected/cleaned; unreadable bar colours refused.
- **Independence**: About hero vs homepage hero; one section's image vs others using
  the same file; itinerary days copied per package; one package's price vs another.
- **Announcements and popups**: global/selected pages, disabled, IST start/end
  boundaries, cache lifetime bounded by the next schedule change, popup priority order,
  new campaign key on content change (not on priority change), dialog markup.
- **Packages**: create through the admin form; duplicate/reserved/invalid slugs refused;
  publish → page, listing and sitemap; unpublish/archive → 404 and removed; edits private
  until republished; slug change → 301 without redirect chains; reordering renumbers
  days; consistency warnings and publish blockers; decimal-safe pricing with Indian
  grouping and validity fallback; imported packages "on request"; duplicate is an
  independent draft; featured order.
- **Media**: re-encoding to WebP with 480–1600 px sizes and random safe names;
  PHP/disguised/SVG/HTML uploads rejected; type, size, PDF and MP4/WebM signature
  checks; used files cannot be deleted; usage list; replace removes old files; upload
  and replace through the admin screens; legacy `/images/…` `/videos/…` `/itineraries/…` 301s.
- **SEO and URLs**: every page renders with one h1, canonical/robots per page, valid
  JSON-LD; titles match the old site; sitemap (31 URLs, no noindex pages) and robots;
  trailing-slash and `index.html` redirects; real 404; canonical host redirect;
  security headers; **no cookies for visitors**; WhatsApp and tel links unchanged.
- **Import**: creates all content (15 packages, 6 destinations, 74 sections, 59 media,
  8 photo credits, 112 itinerary days); re-running never overwrites edits; dry run.
- **Admin screens**: 108 admin URLs (every list, form and every section editor) load.

## Hosting rehearsal (manual checks)

| Check | Result |
| --- | --- |
| `deploy-release.sh` first deploy: extract, link shared storage/.env, migrate, caches, atomic switch | ✓ |
| Deploy refuses an incomplete `shared/.env` (added after an empty `.env` silently fell back to defaults in the rehearsal) | ✓ |
| `cms:import` from the bundled original media | ✓ |
| `/about` → `/about/`, `/index.html` → `/`, legacy media → `/storage/media/…`, 404 page | ✓ |
| `/.env`, `/composer.json`, `/storage/../.env` not reachable; `.php` and `.svg` placed in uploads → 403 | ✓ |
| `Cache-Control` immutable for `/build/assets/*`, 1 week for media; guest page cache HIT | ✓ |
| Upload + publish, then deploy a second release: file and content still there | ✓ |
| `deploy-release.sh --rollback`: previous code live, content and uploads kept | ✓ |
| `backup.sh`, then database restore and media-only restore after deleting uploads | ✓ |

## Not verified (needs the Hostinger account)

- PHP version, extensions (GD WebP, intl, zip), upload limits and SSH on the plan.
- Whether `public_html` may be a symlink on the plan (alternative documented).
- LiteSpeed-specific behaviour, Hostinger CDN/cache settings, real HTTPS/HSTS.
- Performance under real traffic on shared hosting.
- Cron job and `mysqldump` availability on the account.
- DNS cutover (requires the owner's authorisation).

## Known content follow-ups

- The Privacy page says the site is hosted on Vercel; update it at cutover.
- Prices remain "on request" until the client confirms them (the 6-night PDF lists
  ₹25,000–30,000 pp, deliberately not published).
