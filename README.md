# Trip Andaman — tripandaman.in

Static Astro + TypeScript website for Trip Andaman, a travel business in Haddo, Sri Vijaya Puram. There is no database, user accounts, payment engine or backend, and **no email**: every enquiry goes to the client's WhatsApp or a phone call.

- Live: https://tripandaman.in (canonical, non-www; `www` redirects here)
- Hosting: Vercel, deployed from the GitHub repository (`main` → production)
- DNS: GoDaddy (nameservers `ns61/ns62.domaincontrol.com`)

## Commands

| Command | What it does |
| --- | --- |
| `npm install` | Install dependencies |
| `npm run dev` | Local dev server on http://localhost:4321 |
| `npm run build` | Production build into `dist/` |
| `npm run preview` | Serve the built `dist/` locally |
| `npm run check` | TypeScript / Astro type check |
| `npm test` | Unit tests (enquiry validation and WhatsApp message building) |
| `npm run audit` | SEO, link and contact audit of `dist/` (run after `build`) |
| `npm run test:browser` | Browser tests on the build: axe WCAG 2.1 AA on every page, 320–1440px layouts, WhatsApp/tel CTAs, the enquiry composer (WhatsApp is intercepted; nothing is sent) |
| `npm run images:optimize` | Rebuild all deployed images, favicons and `src/data/image-manifest.json` from `source-files/` |
| `npm run images:plan` / `images:generate` | Plan / generate the AI illustrations (needs `OPENAI_API_KEY` in `.env`) |

Before pushing: `npm run check && npm test && npm run build && npm run audit && npm run test:browser`.

## Deployment (Vercel)

1. Push to `main`. Vercel builds with `npm run build` and publishes `dist/` (see `vercel.json`).
2. `vercel.json` sets: `www` → `https://tripandaman.in` (308), `/index.html` and `/…/index.html` → directory URL, trailing slashes on page URLs, security headers (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`) and long-lived caching for hashed `/_astro/*` assets. `dist/404.html` is served with a real 404 status.
3. In Vercel → Project → Domains, keep `tripandaman.in` as the primary domain; `www.tripandaman.in` may also be set to redirect there (the `vercel.json` rule does the same).
4. After each deploy, spot-check: https://tripandaman.in/robots.txt, `/sitemap.xml`, a missing URL (404), `/favicon.ico`, and a WhatsApp button.

No environment variables are needed to build the site. The canonical origin is `SITE_URL` in `src/data/site.ts` (and `site` in `astro.config.mjs`).

## Contact CTAs (WhatsApp + phone only)

All contact details live in `src/data/site.ts`:

- `WHATSAPP_NUMBER` — international digits only, e.g. `919646969646` (country code 91, no `+`, spaces or dashes). The phone link (`tel:+919646969646`) is built from the same number.
- `site.phone` — the number as displayed: `+91 9646 96 9646`.
- `whatsappLink(text)` builds `https://wa.me/<number>?text=<encoded text>`; `enquiryText(subject)` gives the standard message "Hello Trip Andaman, I would like to enquire about …".

Where CTAs come from:

- Floating WhatsApp button (desktop/tablet) and the mobile bottom bar — message mentions the current page (`enquirySubject` prop on `BaseLayout`).
- `src/components/WhatsAppButton.astro` — any page-specific "Ask on WhatsApp" button.
- Package pages — "Get a Quote on WhatsApp" naming the package; destination pages — "Ask on WhatsApp" naming the island.
- `src/components/EnquiryForm.astro` + `src/scripts/enquiry.ts` + `src/lib/enquiry.ts` — the optional enquiry form on `/contact/` and `/tour-packages/customized-trip/`. It is a **WhatsApp message composer only**: it validates name, mobile (10–15 digits), optional date/travellers/interest, message and consent, shows accessible errors, then opens WhatsApp with the prepared text. It never posts data anywhere (`method="dialog"`, no `action`), stores nothing and logs nothing. The visitor decides whether to press Send in WhatsApp. Without JavaScript the form is hidden and direct WhatsApp/phone links are shown.

To change the number, edit `WHATSAPP_NUMBER` and `site.phone`, then run `npm run build && npm run audit` (the audit fails if any WhatsApp or tel link points elsewhere).

## Folder structure

```
src/
  pages/                 routes (one .astro file per page; [slug] pages for packages and destinations)
  layouts/BaseLayout.astro   <head> (title, canonical, robots, OG/Twitter, JSON-LD, favicons), header, footer
  components/            Picture, PageHero (+ BreadcrumbList), EnquiryForm, WhatsAppButton, …
  data/                  site.ts (contact + nav), packages.ts, destinations.ts, seo.ts, sitemap.ts,
                         images.ts + image-manifest.json (generated), credits.json
  lib/enquiry.ts         pure validation + message building (unit tested)
  scripts/               browser JS (site.ts, enquiry.ts)
  styles/                tokens.css, base.css, components.css, home.css, pages.css, packages.css, …
public/                  deployed as-is
  images/brand/          logos (240/400px WebP), Organization logo PNG, Open Graph image 1200×630
  images/editorial/      licensed Andaman landscape photos (Wikimedia Commons) — responsive WebP
  images/trips/          client-supplied trip photos — responsive WebP
  images/illustrations/  AI-generated illustrative images — responsive WebP
  images/video-posters/  poster frames for videos
  videos/                optimised MP4 clips used on the site
  itineraries/           downloadable itinerary PDFs
  favicon.ico, favicon.svg, favicon-48x48.png, favicon-96x96.png, apple-touch-icon.png,
  web-app-manifest-192x192.png, web-app-manifest-512x512.png, site.webmanifest
source-files/            full-resolution masters — never deployed
  brand/                 original logo artwork, logo PNGs, favicon mark
  editorial/ trips/ illustrations/ video-posters/   image masters used by images:optimize
  client-media/ client-material/                    original client photos, videos and documents
docs/                    ASSET-SOURCES.md, GOOGLE-SEARCH-CONSOLE.md, qa/
scripts/                 optimize-images, seo-audit, browser-tests, AI image generation
tests/                   unit tests (node --test)
```

### Adding or changing an image

1. Put the master in the right `source-files/<folder>/` with a lowercase-hyphenated name (`neil-sunset.jpg`).
2. `npm run images:optimize` — writes 480/800/1200/1600px WebP (within a size budget) and updates the manifest.
3. Use it with `<Picture name="neil-sunset" alt="…" />` (`eager` only for the above-the-fold hero). Use `alt=""` only for decorative images.

### Adding an itinerary PDF

Put the PDF in `public/itineraries/` (lowercase-hyphenated name) and set `itineraryPdf` on the package in `src/data/packages.ts`.

## SEO

- Every page: unique title (≤ 65 chars; the "| Trip Andaman" suffix is dropped if it would exceed that) and description (70–170 chars), one `h1`, self-referencing canonical, `index, follow, max-image-preview:large`, Open Graph/Twitter tags, `TravelAgency` + `WebSite` JSON-LD; inner pages add `BreadcrumbList`; `/tour-packages/` adds `FAQPage`.
- `noindex, follow` (no canonical, not in the sitemap): `/404.html` and `/image-credits/`.
- `robots.txt` allows everything and lists `https://tripandaman.in/sitemap.xml`. The sitemap (`src/data/sitemap.ts`) lists the 31 indexable pages with `lastmod` from the last git commit touching each page's sources.
- `npm run audit` enforces all of the above. See `docs/GOOGLE-SEARCH-CONSOLE.md` for indexing steps.

## Content still to confirm with the client

- WhatsApp number +91 9646 96 9646 is active on WhatsApp (Business) and is the right enquiry destination.
- Package prices are shown as "on request"; the 6-night itinerary PDF lists prices (₹25,000–30,000 pp) — confirm whether to publish them.
- The 6-night PDF's day order differs slightly from the "Andaman Explorer with Baratang" page text.
- Formal privacy/terms/cancellation policies (the privacy page describes how the website works; it is not legal advice).
- Public-use rights and participant consent for supplied travel photos and videos.
- A vector logo, if available (favicons and logos are cut from the supplied JPEG).
