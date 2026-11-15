# Google Search Console — tripandaman.in

Canonical site: **https://tripandaman.in/** (HTTPS, non-www). Host: Vercel. DNS: **GoDaddy** (nameservers `ns61.domaincontrol.com`, `ns62.domaincontrol.com`).

> Important (found 15 Nov 2026): before this update the live site sent `Disallow: /` in robots.txt, `noindex` on every page and an empty sitemap, because the build had no public URL configured. Deploy the updated code **before** submitting anything below, and re-check step 1.

## 1. Pre-launch checklist (on the live domain, after deploying)

Run each check in a browser or with `curl -I`:

- [ ] `https://tripandaman.in/` loads over HTTPS; `http://tripandaman.in/` → 308 → `https://tripandaman.in/`.
- [ ] `https://www.tripandaman.in/contact/` → 308 → `https://tripandaman.in/contact/`.
- [ ] `https://tripandaman.in/contact` → 308 → `/contact/`; `https://tripandaman.in/index.html` → `/`.
- [ ] `https://tripandaman.in/robots.txt` shows `Allow: /` and `Sitemap: https://tripandaman.in/sitemap.xml` (no `Disallow: /`).
- [ ] `https://tripandaman.in/sitemap.xml` lists 31 URLs.
- [ ] View source of the home page: `<meta name="robots" content="index, follow, max-image-preview:large">` and `<link rel="canonical" href="https://tripandaman.in/">`.
- [ ] `https://tripandaman.in/any-missing-page/` shows the branded 404 page **with HTTP status 404** (`curl -I` shows `404`).
- [ ] `https://tripandaman.in/favicon.ico`, `/favicon.svg`, `/favicon-96x96.png`, `/apple-touch-icon.png`, `/site.webmanifest` return 200.
- [ ] On a phone: the green WhatsApp button opens a chat with **+91 9646 96 9646** and a prefilled “Hello Trip Andaman, I would like to enquire about …” message (don't press Send unless you mean to). The Call button opens the dialler with the same number.

## 2. Add a Domain property (DNS TXT verification, GoDaddy)

A Domain property covers `https`/`http` and `www`/non-www together.

1. Open https://search.google.com/search-console → **Add property** → **Domain** → enter `tripandaman.in` → Continue.
2. Copy the TXT record value Google shows (`google-site-verification=…`).
3. In GoDaddy: **My Products** → `tripandaman.in` → **DNS** → **Manage DNS** → **Add New Record**:
   - Type: `TXT`
   - Name: `@`
   - Value: paste the `google-site-verification=…` string
   - TTL: default (1 hour)
   - Save. Leave the existing Vercel `A`/`CNAME` records untouched.
4. Back in Search Console press **Verify**. If it fails, wait 15–60 minutes for DNS to propagate and retry. Keep the TXT record permanently — removing it unverifies the property.

## 3. Submit the sitemap

1. Search Console → **Sitemaps** → enter `sitemap.xml` (full URL `https://tripandaman.in/sitemap.xml`) → **Submit**. This project generates `sitemap.xml` (there is no `sitemap-index.xml`).
2. Opening the sitemap in a browser may show “This XML file does not appear to have any style information”. That is normal for XML sitemaps.
3. Status **Success** with **31 discovered URLs** is the goal. **Pending** or **Couldn't fetch** often clears within a few hours to a couple of days, but don't assume it is harmless: open the sitemap URL yourself, confirm it returns 200 with XML, confirm robots.txt doesn't block it, and use **URL Inspection → Test live URL** on the sitemap's pages. Resubmit after fixing anything you find.

## 4. Indexable URLs — exactly 31

**Core pages (10)**
1. https://tripandaman.in/
2. https://tripandaman.in/tour-packages/
3. https://tripandaman.in/tour-packages/customized-trip/
4. https://tripandaman.in/destinations/
5. https://tripandaman.in/experiences/
6. https://tripandaman.in/contact/
7. https://tripandaman.in/about/
8. https://tripandaman.in/gallery/
9. https://tripandaman.in/travel-information/
10. https://tripandaman.in/privacy/

**Destination guides (6)**
11. https://tripandaman.in/destinations/havelock-island/
12. https://tripandaman.in/destinations/neil-island/
13. https://tripandaman.in/destinations/ross-island/
14. https://tripandaman.in/destinations/north-bay-island/
15. https://tripandaman.in/destinations/baratang-island/
16. https://tripandaman.in/destinations/sri-vijaya-puram/

**Tour packages (15)**
17. https://tripandaman.in/tour-packages/andaman-island-escape-4n5d/
18. https://tripandaman.in/tour-packages/havelock-neil-getaway-5n6d/
19. https://tripandaman.in/tour-packages/andaman-with-baratang-5n6d/
20. https://tripandaman.in/tour-packages/andaman-highlights-5n6d/
21. https://tripandaman.in/tour-packages/andaman-classic-6n7d/
22. https://tripandaman.in/tour-packages/andaman-explorer-baratang-6n7d/
23. https://tripandaman.in/tour-packages/havelock-holiday-baratang-6n7d/
24. https://tripandaman.in/tour-packages/coral-and-heritage-6n7d/
25. https://tripandaman.in/tour-packages/island-hopper-6n7d/
26. https://tripandaman.in/tour-packages/havelock-long-stay-7n8d/
27. https://tripandaman.in/tour-packages/three-islands-unhurried-7n8d/
28. https://tripandaman.in/tour-packages/andaman-complete-7n8d/
29. https://tripandaman.in/tour-packages/andaman-discovery-baratang-8n9d/
30. https://tripandaman.in/tour-packages/grand-andaman-baratang-9n10d/
31. https://tripandaman.in/tour-packages/north-to-south-andaman-10n11d/

### Manual “Request indexing” — priority batches

The sitemap is enough for Google to find every page; manual requests just nudge the most important ones. Search Console has a daily quota (roughly 10–12 requests), so spread them out. Use **URL Inspection** → paste URL → **Request indexing**.

- **Day 1 (7):** `/`, `/tour-packages/`, `/contact/`, `/tour-packages/customized-trip/`, `/destinations/`, `/destinations/havelock-island/`, `/destinations/neil-island/`
- **Day 2 (8):** `/experiences/`, `/about/`, `/tour-packages/andaman-island-escape-4n5d/`, `/tour-packages/havelock-neil-getaway-5n6d/`, `/tour-packages/andaman-classic-6n7d/`, `/tour-packages/andaman-explorer-baratang-6n7d/`, `/destinations/ross-island/`, `/destinations/baratang-island/`
- **Day 3 (8):** the remaining destination pages (`/destinations/north-bay-island/`, `/destinations/sri-vijaya-puram/`), `/gallery/`, `/travel-information/`, and four 5–6-night packages
- **Later:** leave the remaining packages and `/privacy/` to the sitemap.

## 5. Do not submit these (noindex)

| URL | Why |
| --- | --- |
| `/404.html` and any missing URL | Error page: `noindex, follow`, real 404 status |
| `/image-credits/` | Utility page (photo licences): `noindex, follow`. It stays crawlable so Google can see the noindex. |
| `http://…`, `https://www.…`, `…/index.html`, URLs without the trailing slash | Redirect to the canonical URL; submit only the canonical form |
| `/itineraries/*.pdf` | Downloads, not pages; Google may still index the PDF on its own, which is fine |

There is no thank-you page (enquiries go to WhatsApp, so none is needed).

## 6. Favicon in search results

The favicon is the gold-arc “A” from the Trip Andaman logo on a white square (`/favicon.ico` 48×48, plus 48/96/192/512px PNG, SVG and the 180px Apple touch icon), referenced in the `<head>` of every page, and not blocked by robots.txt. Google picks up favicon changes only after it recrawls the home page and reprocesses the icon, which can take days or weeks. Requesting indexing of the home page doesn't guarantee or directly speed up the favicon update.

## 7. Follow-up, weeks 1–4

**Week 1**
- Sitemaps: status Success, 31 discovered.
- **Pages** report (Indexing → Pages): watch “Indexed” grow. Expected exclusions: `/image-credits/` (“Excluded by noindex tag”) and redirects (“Page with redirect”). Investigate anything listed under “Crawled – currently not indexed” or “Duplicate without user-selected canonical”.
- URL Inspection on `/` and one package page: “URL is on Google”, user-declared canonical = Google-selected canonical.

**Week 2**
- **Enhancements:** Breadcrumbs (inner pages) should show valid items; FAQ (on `/tour-packages/`) may be detected, but Google currently shows FAQ rich results only for a few authoritative sites, so don't expect them. Fix any errors reported.
- **PageSpeed:** run https://pagespeed.web.dev/ on `/` and `/tour-packages/andaman-explorer-baratang-6n7d/` (mobile). Field data (real-user LCP/INP/CLS) appears only once the site has enough Chrome traffic. Until then, rely on lab data.

**Week 3**
- **Google Business Profile:** create or claim the profile for Trip Andaman (Haddo, Sri Vijaya Puram), with phone +91 9646 96 9646, website `https://tripandaman.in/`, category “Travel agency”. Use the same name, address and phone as the website.
- **Bing Webmaster Tools** (https://www.bing.com/webmasters): **Import from Google Search Console** (fastest) or add the site with DNS verification at GoDaddy, then submit `https://tripandaman.in/sitemap.xml`.

**Week 4**
- **Performance** report: first queries and clicks. Note which packages and destinations get impressions, and refine their titles and descriptions if the click-through rate is low.
- **Core Web Vitals** report (if there is enough data) and the HTTPS report: everything should be “Good” / “HTTPS”.
- Re-run the step 1 checklist after any new deployment.
