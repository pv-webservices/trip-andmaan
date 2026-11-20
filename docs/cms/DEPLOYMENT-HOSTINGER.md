# Deploying the Trip Andaman CMS to Hostinger

Everything runs on one Hostinger web hosting account: PHP (Laravel), MySQL/MariaDB
and the uploaded media. Production never runs Node.js; the CSS/JS is compiled into
the release package.

> **Not yet verified on the client's Hostinger account.** The Hostinger account
> connected during development had no Trip Andaman website, so PHP version,
> extensions, SSH access and limits below are Hostinger's usual Business/Cloud
> plan capabilities, not checked values. Check each item in the "Before the first
> deploy" list on the real account. Everything else here was rehearsed on Linux
> with PHP 8.3 + Apache (`.htaccess`) + MariaDB 11.4 — see `VERIFICATION.md`.

> **Do not point tripandaman.in at Hostinger or replace the live site without the
> owner's explicit go-ahead.** The live site is the Astro build on Vercel (DNS at
> GoDaddy). Test on Hostinger's temporary domain first.

## 1. Requirements

| Item | Requirement |
| --- | --- |
| Plan | A Hostinger plan with **SSH access** (Business, Cloud or higher). Premium/Single plans may lack SSH; without SSH you cannot run migrations or create the first admin. |
| PHP | **8.3 or newer** (hPanel › Websites › Advanced › PHP Configuration) |
| PHP extensions | `ctype dom fileinfo filter hash iconv intl json libxml mbstring openssl pcre session tokenizer xmlreader zip` (from `composer check-platform-reqs`), plus **`gd` with WebP**, `pdo_mysql`, `exif` (photo rotation; optional) |
| PHP limits | `upload_max_filesize` ≥ 64M, `post_max_size` ≥ 70M, `memory_limit` ≥ 256M (512M recommended for large photos), `max_execution_time` ≥ 60 |
| Database | MySQL 8 or MariaDB 10.6+ (Hostinger: hPanel › Databases › MySQL Databases) |
| Disk | ~150 MB per release (5 kept) + media (~25 MB today, grows with uploads) + backups |

Check GD WebP support over SSH: `php -r 'var_dump(gd_info()["WebP Support"]);'` → `bool(true)`.

## 2. Folder layout on the server

```
~/domains/tripandaman.in/
├── public_html -> current/public        ← the ONLY folder the web server exposes
├── current -> releases/<newest>
├── releases/<name>/                     ← application code, replaced by each release
├── shared/.env                          ← secrets (chmod 600), never in a release
├── shared/storage/                      ← uploads (app/public/media), sessions, cache, logs
├── backups/                             ← created by bin/backup.sh
└── deploy-release.sh
```

Application code, `vendor/`, `.env` and `storage/` are all outside `public_html`.
Uploaded media lives in `shared/storage/app/public`, which no release replaces, and
is served through the `public/storage` link that the deploy script recreates.

## 3. Build the release (on your computer)

Needs PHP 8.3+, Composer and Node.js 22+ locally.

```bash
node laravel/scripts/export-astro-content.mjs   # only if src/data/*.ts changed
bash laravel/bin/package-release.sh             # → dist-release/trip-andaman-<stamp>-<commit>.tar.gz
```

The archive contains the app, `vendor/` (no dev packages), compiled `public/build/`,
Filament assets, and the original images/videos/PDF for the first import. It
contains no `.env`, database, uploads, logs or tests.

## 4. Before the first deploy (hPanel)

1. **Website**: add the site. Start with the free temporary domain (`*.hostingersite.com`).
2. **PHP Configuration**: select PHP 8.3+, enable the extensions above, raise the limits.
3. **Databases › MySQL Databases**: create a database and user with a strong
   generated password. The app connects to `127.0.0.1:3306` (not the remote
   `srvNNNN.hstgr.io` host).
4. **SSH Access**: enable it and note the host, port and username.
5. **SSL**: issue the free certificate for the domain and turn on **Force HTTPS**.
6. **Cache Manager / CDN**: leave the LiteSpeed page cache **off** for this site
   (the app has its own page cache that is cleared on every publish). If you turn
   on Hostinger CDN, set `TRUSTED_PROXIES` (see `.env.example`) and purge the CDN
   after publishing.

## 5. First deploy

Upload `dist-release/*.tar.gz` and `dist-release/deploy-release.sh` to
`~/domains/<domain>/` (File Manager or `scp -P <port>`), then over SSH:

```bash
cd ~/domains/<domain>
mkdir -p shared && nano shared/.env      # paste laravel/.env.example and fill it in
```

Fill in `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`, `CMS_SITE_URL`.
While testing on the temporary domain set `APP_URL`/`CMS_SITE_URL` to that domain and
leave `CMS_CANONICAL_HOST` empty. Create the app key and paste it in as `APP_KEY=`:

```bash
php -r 'echo "base64:".base64_encode(random_bytes(32)), PHP_EOL;'
chmod 600 shared/.env
bash deploy-release.sh trip-andaman-<stamp>-<commit>.tar.gz
```

The script refuses to run if `.env` lacks the key, database settings,
`APP_ENV=production` or `APP_DEBUG=false`. Then, once only:

```bash
mv public_html public_html.hostinger-default    # keep Hostinger's placeholder
ln -s current/public public_html
cd current
php artisan cms:import          # imports pages, packages, destinations, media
php artisan cms:create-admin    # asks for email, name and password (typed, not shown)
```

`cms:import` only creates what is missing, so re-running it is safe.

If Hostinger does not allow `public_html` to be a symlink on your plan, use hPanel's
option to change the website's document root to `domains/<domain>/current/public`, or
contact Hostinger support; never copy the whole application into `public_html`.

## 6. Later releases

```bash
bash laravel/bin/package-release.sh             # locally
# upload the new archive, then on the server:
cd ~/domains/<domain> && bash current/bin/backup.sh && bash deploy-release.sh <new archive>
```

The script: extracts into `releases/`, links `shared/storage` and `shared/.env`,
runs migrations, rebuilds caches, switches `current` atomically, clears the page
cache and keeps the five newest releases. Content and uploads are untouched.

Rollback: `bash deploy-release.sh --rollback` (see `BACKUP-RESTORE.md` if the newer
release changed the database).

## 7. Cron jobs

**None are required.** Scheduled announcements, popups and prices switch on time
because the page cache never outlives the next schedule change. Recommended:
a daily backup (hPanel › Advanced › Cron Jobs):

```
15 3 * * *  cd /home/<user>/domains/<domain> && bash current/bin/backup.sh --keep 14 >/dev/null 2>&1
```

## 8. Going live on tripandaman.in (needs the owner's authorisation)

1. Test everything on the temporary domain (checklist below).
2. Update the **Privacy** page text, which still says the site is hosted on Vercel
   (Pages › Privacy & enquiries › Text content).
3. Add `tripandaman.in` to the Hostinger website; set `APP_URL` and `CMS_SITE_URL` to
   `https://tripandaman.in` and `CMS_CANONICAL_HOST=tripandaman.in`; run
   `php artisan config:cache` in `current/`.
4. At GoDaddy, point the `A` record of `tripandaman.in` and the `www` record at
   Hostinger (values from hPanel). Keep the Vercel project until DNS has settled.
5. Issue SSL for both names in hPanel; confirm `http://` and `www.` redirect to
   `https://tripandaman.in/`.
6. Resubmit `https://tripandaman.in/sitemap.xml` in Google Search Console.

## 9. Post-deploy checklist (run on the Hostinger site)

- `https://<domain>/`, a package page, a destination page and `/sitemap.xml` load.
- `/about` redirects to `/about/`; an unknown page shows the 404 page with status 404.
- `/images/editorial/hero-1200.webp` redirects to `/storage/media/...` (old links).
- `https://<domain>/.env` and `/storage/...php` return 403/404.
- Sign in at `/admin`, edit a section, Preview, Publish, see the change publicly.
- Upload a JPEG in Media library; it shows its sizes (480–1600 px WebP).
- Run the automated checks from your computer against the Hostinger URL:
  `BASE=https://<domain> node laravel/scripts/browser-tests.mjs` and
  `BASE=https://<domain> node laravel/scripts/compare-with-astro.mjs`
  (the comparison expects `CMS_SITE_URL=https://tripandaman.in`).

## 10. Troubleshooting

| Symptom | Fix |
| --- | --- |
| 500 error, "No application encryption key" | `APP_KEY` missing in `shared/.env`; then `php artisan config:cache` |
| Admin login loops or "Page expired" | Wrong `APP_URL`, or `SESSION_SECURE_COOKIE=true` on plain http (temporary domains without SSL) |
| Uploads fail above ~2 MB | PHP `upload_max_filesize` / `post_max_size` too low |
| "The server cannot create WebP images" | Enable GD with WebP in PHP Configuration |
| Changes to `.env` ignored | `cd current && php artisan config:cache` (config is cached) |
| Stale page after publish | A CDN/LiteSpeed cache in front of the site: purge it or turn it off |
| Logs | `shared/storage/logs/laravel-<date>.log` |
