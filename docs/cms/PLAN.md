# Trip Andaman CMS — content inventory, migration approach and database design

Status: implementation plan written before coding (20 Nov 2026). The Laravel
application lives in `laravel/`. The Astro site in `src/` stays the live
site's source until cutover and is the source of the first content import.

## 1. Existing site inventory

Static Astro 7, 31 indexable URLs + `/image-credits/` and `/404.html` (noindex).
Every enquiry is a WhatsApp link (`wa.me/919646969646?text=…`) or `tel:`;
the enquiry form only composes a WhatsApp message in the browser.

| Page (stable key) | URL | Sections (stable keys) |
| --- | --- | --- |
| `home` | `/` | meta, hero (image + decorative script + hero card), promises, story (collage + pillars), destinations (bento), experiences (tabs), packages (featured + planning cards), film_band (video), moments (photos + video), steps, testimonials, gallery (compact), enquiry_banner, film_dialog |
| `about` | `/about/` | meta, hero, intro (media stack + check list), hosted (photo strip), values, destinations, enquiry_banner |
| `tour_packages` | `/tour-packages/` | meta, hero (+ jump links), groups (length bands), services, customise, faq (FAQPage JSON-LD), enquiry_banner |
| `package_detail` | `/tour-packages/{slug}/` (template) | labels (itinerary, notes, moments, aside, related), default_notes, enquiry_banner |
| `customized_trip` | `/tour-packages/customized-trip/` | meta, hero, intro (image, next steps, note) |
| `destinations` | `/destinations/` | meta, hero, intro, enquiry_banner |
| `destination_detail` | `/destinations/{slug}/` (template) | labels, enquiry_banner |
| `experiences` | `/experiences/` | meta, hero (+ jump links), notice, items, water_sports (reels), enquiry_banner |
| `gallery` | `/gallery/` | meta, hero, gallery (filterable photos), videos (reels + films), enquiry_banner |
| `contact` | `/contact/` | meta, hero, panel (contact cards), form |
| `travel_information` | `/travel-information/` | meta, hero, body (headed paragraphs + button) |
| `privacy` | `/privacy/` | meta, hero, body |
| `image_credits` | `/image-credits/` | meta (noindex), hero, body (credits list comes from media attribution) |
| `not_found` | 404 | meta, body |
| `global` | — | contact, navigation, header, footer, seo, whatsapp_widget, enquiry_banner (shared text), enquiry_form, announcement_bar |

Collections: 15 tour packages (all "price on request"; itineraries built from
shared day blocks in `itinerary-days.ts`), 6 destinations, 8 reels + 2 films,
27 gallery photos, 0 testimonials, 17 credited Wikimedia photos
(`credits.json`), ~70 responsive WebP image sets (`image-manifest.json`),
10 MP4 videos, 1 itinerary PDF.

## 2. Target architecture (Hostinger only)

- Laravel 13 + Blade, PHP 8.3+, MySQL/MariaDB on the same Hostinger account.
- Admin: Filament 5 (self-hosted, Livewire) at `/admin`. No public registration.
- CSS/JS: the existing `src/styles/*.css` and `src/scripts/*.ts` are reused
  through Vite, compiled locally into `public/build/` and shipped in the
  release package. Production runs no Node.js.
- Media: Laravel `public` disk (`storage/app/public/media`), exposed by the
  `public/storage` symlink. In production `storage/` is a shared directory
  outside the release folders, so uploads survive releases.
- Public routes run without sessions or cookies; a guest page cache stores
  rendered HTML keyed by a content version that every publish increments.

## 3. Publishing model

- **Sections** (page content, globals): one row per page section with
  `draft_data` and `published_data` JSON validated by a PHP schema class per
  section type. Saving writes the draft; *Publish* copies it to
  `published_data`; *Discard* reverts the draft.
- **Packages and destinations**: edited relationally (package, itinerary days,
  gallery, pricing). *Publish* writes `published_snapshot`, which is what the
  public site renders. Status: `draft`, `published`, `archived`.
- **Preview**: `/preview/...` (authenticated admins only) renders the same
  templates from drafts / live relational data with `noindex` and
  `Cache-Control: no-store`.
- Every publish bumps the content version (page cache invalidated at once) and
  records `updated_by` / `published_by` with timestamps.
- Schedules (announcements, popups, price validity) are evaluated in
  Asia/Kolkata. The page cache TTL never extends past the next schedule
  boundary, so scheduled items appear and disappear on time without cron.

## 4. Database design

| Table | Purpose / key columns |
| --- | --- |
| `users` | admins: name, email, password (bcrypt), `is_active`, `last_login_at` |
| `pages` | `key` (unique, stable), title, `path`, `kind` (page/template/global), sort |
| `sections` | `page_id`, `key` (unique per page), `type`, sort, `draft_data`, `published_data`, `published_at`, `draft_updated_at`, `updated_by`, `published_by` |
| `media` | disk path, `type` (image/video/document), mime, size, width, height, `variants` (JSON width→path), alt, caption, credit fields (title, author, license, license URL, source URL, changes), `legacy_key`, `legacy_paths` |
| `media_usages` | `media_id`, polymorphic owner (`section`, `package`, `destination`, `popup`…), `field` path, label — rebuilt on every save |
| `packages` | slug (unique), title, overview, nights, days, hero/mobile media, alt, focal, tags, stays, inclusions, exclusions, notes, pricing (`price_mode`, currency, `price_amount` DECIMAL(12,2), `price_original` DECIMAL(12,2), unit, valid from/until, note), `itinerary_pdf_media_id`, `is_featured`, `featured_order`, sort, status, `published_snapshot`, `published_at`, `has_unpublished_changes`, `import_key` |
| `package_days` | `package_id`, sort, title, description (paragraph text) — one copy per package |
| `package_media` | `package_id`, `media_id`, sort, caption, alt (route "moments" / gallery) |
| `destinations` | slug, name, official name, image, secondary image, texts, highlights, note, source URL, reel list, status, snapshot, `import_key` |
| `redirects` | `from_path` (unique), `to_path`, status code — written on slug changes |
| `announcements` | text, link, sort, enabled, `targeting` (all / selected), `page_keys`, `starts_at`, `ends_at` |
| `popups` | media, alt, heading, description, button label/url, enabled, targeting, page keys, delay, frequency, starts/ends, priority, `campaign_version` |
| `settings` | key/value for small global values (content version) |

Section data is stored as validated JSON (repeaters, headings, CTA lists);
packages, days, media and assignments are relational as required.

## 5. Migration / import approach

`php artisan cms:import` reads `database/content/` (JSON exported from the
Astro data files by `node laravel/scripts/export-astro-content.mjs`, plus
hand-transcribed page section content in `database/content/pages.php`).

- Media: copies the existing responsive WebP sets, videos and PDF into the
  media library, keeps their widths, records the legacy URL so old
  `/images/…`, `/videos/…` and `/itineraries/…` links 301 to the new files,
  and stores the Wikimedia credits on the media rows.
- Each record has a stable import key; the import only **creates missing**
  records and never overwrites rows that exist, so it is safe to re-run after
  administrators have edited content. `--dry-run` reports what it would do.
- Shared itinerary day blocks are expanded into per-package `package_days`
  copies, so editing one package never changes another.
- Every package is imported as **price on request**. The 6-night PDF's prices
  are not published (the client has not confirmed them).

## 6. Implementation stages

1. Scaffold Laravel, Filament, migrations, models.
2. Port layout + home hero end to end (schema → admin form → draft → preview → publish → public render), with tests.
3. Port all pages/sections, packages, destinations, sitemap, robots, redirects, 404.
4. Media library (upload validation, variants, usage tracking, replace/delete rules).
5. Announcements and popups (targeting, scheduling, dismissal, a11y).
6. Content import + legacy media redirects.
7. Tests (auth, drafts, independence, scheduling, packages, media, SEO), browser checks (axe, layouts, WhatsApp interception).
8. Hostinger deployment guide, packaging script, backup/restore/rollback, admin guide.
