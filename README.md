# TRIP ANDAMAN

Static Astro + TypeScript business website. No database, accounts, payment engine or application backend.

## Local use

```powershell
npm install
npm run dev
npm run check
npm run build
npm run verify
npm run preview
```

## Content

- `src/data/site.ts`: verified business details, navigation and travel inspiration.
- `src/data/destinations.ts`: destination guides and official source links.
- `src/data/packages.ts`: unpublished editorial package drafts and optional confirmed fields. A draft produces no public offer or route. Publish only after the client confirms its copy and details. `/tour-packages/customized-trip/` is an enquiry page, not a fixed package.
- `src/data/credits.json`: photography provenance and licences, also shown on `/image-credits/`.
- `src/data/testimonials.ts`: empty, client-approved review collection; only approved entries render through the reusable testimonial component.
- `src/data/images.ts`: responsive widths available for each named image.
- `src/styles/`: the design system — `tokens.css` (colours, type scale, spacing, radii, motion), `base.css`, `components.css` (buttons, header, cards, forms, gallery, footer), `home.css`, `pages.css` and `responsive.css`. `global.css` only imports them.

The homepage reference determines the brand direction; the October 2026 redesign evolved it into a premium ocean-navy, lagoon and sun-yellow system around the original logo. Example reviews, prices, package durations and service promises from the reference are not treated as facts. The review section is replaced by three travel-planning steps.

## Enquiries

Without a form endpoint, the labelled form validates input and prepares an email draft. A second explicit action opens the user's email app. Nothing is submitted or described as sent. Phone and email links are always available.

To enable direct delivery, set `PUBLIC_ENQUIRY_ENDPOINT` to an approved HTTPS form service accepting FormData POST requests with JSON responses and CORS support. Configure the recipient, provider-side validation, spam protection and rate limits at that provider. The UI has a honeypot, required consent, loading, timeout/error and accepted states. A successful HTTP response means the service accepted the request; it is not proof of inbox delivery. Verify actual delivery and privacy wording before launch. Never put a private API key in a `PUBLIC_` variable.

WhatsApp links (`site.whatsapp` in `src/data/site.ts`) use the business number 9646 96 9646 with a prefilled greeting: a chat widget on desktop/tablet, a green button in the mobile bottom bar, and entries on the contact page and contact lists. Confirm the number is active on WhatsApp before launch. No social profiles were supplied.

## Static hosting / Hostinger

1. Copy `.env.example` to `.env`, set `PUBLIC_SITE_URL` to the confirmed HTTPS origin, and configure the enquiry endpoint if approved.
2. Run all local checks and rebuild. Without the public origin, pages use `noindex`, robots disallows crawling and the sitemap has no entries. This prevents accidental placeholder URLs. Set the real origin before indexing.
3. Upload the **contents** of `dist/` to the domain's document root (usually `public_html`). Preserve route directories, `_astro/`, `images/` and video files. No Node server is needed for the built site.
4. Configure the host to serve `404.html` for missing routes with a real 404 response. Test clean URLs, HTTPS and the preferred hostname on the deployed host.
5. Verify phone/email/map links, media, provider receipt, canonical/sitemap URLs, robots and mobile navigation on production.

Do not run a blanket rewrite to `index.html`: this is a static multipage site. Host-specific behavior and production indexing are unverified until deployment.

## Client content still required

- Confirmed tour products, itineraries, inclusions/exclusions, pricing and booking terms.
- Formal privacy, terms, cancellation/refund policies; none have been fabricated.
- Approved testimonials with permissions/source URLs, if desired.
- Original vector/high-resolution logo. The site uses an AI restoration (`logo-hd`) of the 109 px supplied crop; replace it with the client's master file when available.
- Confirmation of public-use rights and participant consent for supplied travel media.
- Confirmed domain and optional form-service configuration; WhatsApp/social URLs if wanted.

## AI image generation (build-time only)

`scripts/generate-images.mjs` creates the few illustrative images the site needs. It runs on a developer machine, reads `OPENAI_API_KEY` from `.env` and never ships the key: the built site is static, and `npm run verify` fails if a credential pattern appears in `dist/`.

```powershell
npm run images:plan             # show model/quality/size routing, no API calls
npm run images                  # generate missing assets
npm run images -- ai-beach-walk --force             # regenerate one asset
npm run images -- ai-island-hopping --premium=ai-island-hopping --force  # retry on Sunburst
```

- `scripts/image-gen/config.mjs`: model names, qualities, sizes, WebP widths — all overridable by env vars.
- `scripts/image-gen/router.mjs`: picks **gpt-image-2.5-flare** by default; **gpt-image-2.5-sunburst** for hero imagery, multiple people, complex scenes, reference-image edits or a rejected Flare result. Key/hero assets use `high`, routine ones `medium`. Orientation maps to 1024², 1536×1024 or 1024×1536.
- `scripts/image-gen/manifest.mjs`: each asset's purpose, placement, alt text and prompt.
- Raw PNGs and per-asset generation records go to `output/generated/`; optimised WebP files go to `public/images/`.

Current generated assets: `ai-island-hopping`, `ai-snorkel-reef`, `ai-trip-planning` (Flare, high) and `ai-beach-walk` (Sunburst, high — two people), plus `logo-hd` (Sunburst reference edit restoring the 109 px supplied logo at high resolution on a transparent background). Generated scenes are disclosed on `/image-credits/` and are never captioned as a specific Andaman place.

## Media

Original supplied files are preserved in `public/`. Only selected suitable files are used. The build removes the 12 original JPEG/MP4 source files from `dist/`, publishing only the selected optimized derivatives. Other-company promotional films, the unidentified sideways beach photograph and the remaining personal videos are not displayed or included in the deployment output. `underwater-film.mp4` is a silent 20-second derivative of supplied `video-2.mp4`; `scuba-film.mp4` is a silent 12-second derivative of supplied `video-5.mp4`, cropped to remove the camera timestamp. Both use `preload="none"`; muted ambient copies play only while on screen and never under reduced motion. Additional Andaman landscape photographs are licensed Wikimedia Commons assets, locally optimized and publicly credited. No generated landscapes or foreign destinations are labelled as Andaman.

See `ASSET-SOURCES.md` and `output/QA.md` for evidence and limits.
