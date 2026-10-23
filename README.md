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
- `src/styles/global.css`: brand tokens, responsive layouts and reduced-motion styles.

The homepage reference determines the layout and brand direction. Example reviews, prices, package durations and service promises from the reference are not treated as facts. The review section is replaced by three travel-planning steps.

## Enquiries

Without a form endpoint, the labelled form validates input and prepares an email draft. A second explicit action opens the user's email app. Nothing is submitted or described as sent. Phone and email links are always available.

To enable direct delivery, set `PUBLIC_ENQUIRY_ENDPOINT` to an approved HTTPS form service accepting FormData POST requests with JSON responses and CORS support. Configure the recipient, provider-side validation, spam protection and rate limits at that provider. The UI has a honeypot, required consent, loading, timeout/error and accepted states. A successful HTTP response means the service accepted the request; it is not proof of inbox delivery. Verify actual delivery and privacy wording before launch. Never put a private API key in a `PUBLIC_` variable.

WhatsApp is not linked because availability on the supplied number has not been confirmed. No social profiles were supplied.

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
- Higher-resolution original logo. The current unmodified artwork is a small crop from the supplied design reference.
- Confirmation of public-use rights and participant consent for supplied travel media.
- Confirmed domain and optional form-service configuration; WhatsApp/social URLs if wanted.

## Media

Original supplied files are preserved in `public/`. Only selected suitable files are used. The build removes the 12 original JPEG/MP4 source files from `dist/`, publishing only the selected optimized derivatives. Other-company promotional films, the unidentified sideways beach photograph and the remaining personal videos are not displayed or included in the deployment output. `underwater-film.mp4` is a silent 20-second derivative of supplied `video-2.mp4`, loaded only on demand. Additional Andaman landscape photographs are licensed Wikimedia Commons assets, locally optimized and publicly credited. No generated landscapes or foreign destinations are labelled as Andaman.

See `ASSET-SOURCES.md` and `output/QA.md` for evidence and limits.
