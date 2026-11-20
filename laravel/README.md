# Trip Andaman — Laravel site and admin panel

Server-rendered Trip Andaman website (Laravel 13 + Blade) with a Filament admin panel
at `/admin`. It replaces the static Astro build in the repository root, keeping its
templates, CSS, browser scripts, URLs, metadata and WhatsApp/phone enquiries.

Documentation: [`../docs/cms/`](../docs/cms/)
— [PLAN](../docs/cms/PLAN.md) (inventory, database design) ·
[DEPLOYMENT-HOSTINGER](../docs/cms/DEPLOYMENT-HOSTINGER.md) ·
[BACKUP-RESTORE](../docs/cms/BACKUP-RESTORE.md) ·
[ADMIN-GUIDE](../docs/cms/ADMIN-GUIDE.md) ·
[VERIFICATION](../docs/cms/VERIFICATION.md)

## Local development

Requirements: PHP 8.3+ (extensions: intl, gd with WebP, zip, pdo_sqlite or pdo_mysql,
fileinfo, mbstring), Composer, Node.js 22+.

```bash
cd laravel
composer install && npm install
cp .env.example .env            # then for local use: APP_ENV=local, APP_DEBUG=true,
                                # APP_URL=http://127.0.0.1:8000, DB_CONNECTION=sqlite,
                                # SESSION_SECURE_COOKIE=false, CMS_CANONICAL_HOST=
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan cms:import          # content + media from ../public and database/content/
php artisan cms:create-admin
npm run build                   # or: npm run dev
php artisan serve               # http://127.0.0.1:8000, admin at /admin
```

## Commands

| Command | Purpose |
| --- | --- |
| `php artisan cms:import [--dry-run]` | Import the existing site content. Creates only what is missing; never overwrites edits. |
| `php artisan cms:create-admin` | Create an administrator (interactive). There is no public registration. |
| `php artisan cms:reset-password <email>` | Password recovery over SSH (no e-mail needed). |
| `php artisan test` | PHPUnit feature and unit tests |
| `npm test` | Unit tests of the WhatsApp enquiry composer |
| `node scripts/export-astro-content.mjs` (from repo root) | Re-export `src/data/*.ts` to `database/content/*.json` |
| `node scripts/compare-with-astro.mjs` (repo root, server running) | Compare every page with the Astro build in `../dist` |
| `node scripts/browser-tests.mjs` (repo root, server running) | axe WCAG 2.1 AA, 320–1440px layouts, WhatsApp/tel CTAs, composer |
| `node scripts/cms-browser-checks.mjs` (repo root, server running) | Announcement bar and popup behaviour in Chrome |
| `bash bin/package-release.sh` (repo root) | Build the Hostinger release archive |
| `bin/deploy-release.sh`, `bin/backup.sh`, `bin/restore.sh` | Server-side deploy/rollback, backup and restore |

## Code map

```
app/Cms/            Site (request content), PageMap (pages & sections), section types
                    (Sections/*), Fields (form building blocks), Sanitizer, SafeUrl, Price
app/Media/          MediaUploader (validation, storage), ImageProcessor (GD → WebP sizes),
                    MediaUsageRecorder (where each file is used), MediaOptions (picker)
app/Models/         Page, Section, Package (+PackageDay, PackageMedia), Destination, Media,
                    MediaUsage, Announcement, Popup, Redirect, Setting, User
app/Filament/       Admin screens (Pages & sections, Packages, Destinations, Media,
                    Announcements, Popups, Redirects, Administrators)
app/Http/           SiteController, middleware: PageCache, PublicUrls, PreviewMode, SecurityHeaders
resources/views/    layouts/site, pages/*, components/*, partials/* (ported from src/)
resources/css/site/ the Astro stylesheets unchanged; cms.css for the additions
resources/js/       site.ts + enquiry.ts from the Astro site; cms.ts (announcements, popups)
database/content/   import seed (JSON exported from src/data + pages.php transcription)
routes/             site.php (public, no sessions), web.php (/preview), pages.php
```
