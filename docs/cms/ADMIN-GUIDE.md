# Trip Andaman website — administrator guide

Sign in at **`https://tripandaman.in/admin`** with your email and password.

## The one rule: save, preview, publish

Most content has two versions: what **visitors see** (published) and your **draft**.

1. **Save draft** keeps your changes private.
2. **Preview** opens the page with your drafts in a new tab (orange "Preview" bar at
   the bottom). Only signed-in administrators can see it; search engines cannot.
3. **Save & publish** makes the changes live straight away.
4. **Discard draft** throws away unpublished changes.

Each screen shows its status ("Published", "Draft changes", "Never published") and
who last edited and published it. The **Dashboard** lists everything waiting to be
published. If you leave a form with unsaved changes, the browser warns you.

These take effect **as soon as you save** (no publish step): announcements, popups,
media library details (alt text, credits) and **Replace file**.

## Pages and sections

*Website content › Pages & global settings › choose a page › choose a section.*

Each page is split into the sections you see on the site (Hero banner, Promise
strip, Featured destinations…). Editing a section changes **that section on that
page only** — the About hero never changes the homepage hero.

- **Show this section** hides a section without deleting its content.
- **Headings** have three parts: small *eyebrow*, *heading*, and *highlighted words*
  shown in the accent colour.
- **Buttons** have text, a destination and a *Show button* switch. Destinations can
  be a page path such as `/contact/`, a full `https://` address or `tel:+91…`.
  Other kinds of links are refused.
- **Lists** (cards, steps, FAQs, photos): *Add item*, drag order with the arrows,
  duplicate or delete with the item's icons.
- **Search & sharing (SEO)**: the title and description shown by Google and in link
  previews; each page has its own.

**Package page / Destination page** are templates: texts shared by every package or
destination page (e.g. "Get a Quote on WhatsApp", the notes on every package).

**Global settings** apply to every page: contact details and WhatsApp number, main
menu, header button, footer (including social links), default search image, the
WhatsApp chat widget, the shared enquiry banner text, the enquiry form texts and the
announcement bar colours. Pages use these automatically; where a page can override
something (e.g. a different enquiry-banner image) its section says so.

## Hero banners

Every page's *Hero banner* section has: eyebrow, heading, supporting text, image
(+ alternative text and focal point), an optional **mobile image** for phones, an
optional silent **background video** with poster image, and optional buttons. The
homepage hero also has the handwritten words, the tagline and the small hero card.
*Focal point* chooses which part of the photo stays in view when it is cropped.
Visitors who prefer reduced motion see the image instead of the video.

## Announcement bar

*Promotions › Announcement bar.* Each message has text, an optional link, **Enabled**,
**Show on** (every page or selected pages) and optional **start/end** date-times in
India time. Drag rows to reorder. *Colours & speed* sets the bar's colours (they must
be readable; low-contrast pairs are refused) and scrolling speed. Visitors can pause
it, it does not scroll for people who prefer less motion, and when no message
applies the bar disappears completely.

## Popup banners

*Promotions › Popup banners.* Image (+ description of it), heading, text, button,
**Enabled**, pages, start/end, **delay** before it appears, **how often** each visitor
sees it (once per browser session / day / week / once) and **priority**.

Only one popup is shown at a time: the highest priority, then the most recent start
date. Visitors close it with the × button, the Escape key or by clicking outside.
Changing a popup's content, button, dates or frequency makes it a new campaign, so
people who closed the old version see the new one once.

## Tour packages

*Website content › Tour packages.* Drag rows to set the order on the website.

- **New package** → fill **Overview** (title, URL slug, overview, nights/days,
  where travellers stay, tags) → **Save draft**.
- **Itinerary**: *Add day*, enter the day title and description (blank line between
  paragraphs). Day numbers follow the order; move days with the arrows. Days belong
  to this package only — editing them never changes another package.
- **Images & files**: main image (+ alt text, focal point), optional mobile hero image,
  the "Moments from this route" photos with captions, and the downloadable
  itinerary PDF.
- **Pricing**: *Price on request*, *Starting price* ("From ₹25,000 per person") or
  *Fixed price*; currency, amount, optional original (struck-through) price, per
  person/couple/group/package, optional validity dates and a note. Outside the
  validity dates the site shows "Price on request". Imported packages are all
  "on request" until you enter a price.
- **Inclusions & notes**: optional included / not-included lists and travel notes
  (shown after the notes shared by every package).
- **Listing & search**: feature on the homepage and the featured order; optional
  search title and description.
- A yellow **Check before publishing** box warns about mismatches (e.g. 7 itinerary
  days for a 6-night package). Publishing is blocked only for missing essentials
  (title, image, at least one day, a price amount when pricing is not "on request").
- **Save & publish** puts the package live at `/tour-packages/<slug>/`, in the package
  lists and in the sitemap. Later edits stay private until you publish again.
- **More ›** *Unpublish* (page stops working, removed from lists and sitemap),
  *Archive* (unpublish and hide from the admin list; use the status filter to find it),
  *Duplicate* (an unpublished copy with its own days and photos).
- Changing the **URL slug** of a published package: when you publish, the old
  address redirects to the new one automatically (*Settings › Redirects*).

## Destinations

*Website content › Destinations* works like packages: name, official name, slug,
tagline, introduction, description, highlights, "Before you go" note, reference link,
images and optional video clips; Save draft → Preview → Save & publish.

## Media library

*Library › Media library.* Upload JPEG/PNG/WebP images (up to 15 MB; converted to WebP
in several sizes), MP4/WebM videos (up to 60 MB; not converted — export them small,
720p, a few MB) and PDFs (up to 15 MB). Other file types are refused.

Each file shows its type, size, dimensions and **where it is used**. Edit its name,
default alternative text, caption and credit (author, licence, source) — credits
with an author and source appear on the Photo credits page.

- **To change a picture in one place**, open that section/package and choose a
  different file there. Nothing else changes.
- **Replace file** swaps the file everywhere it is used, immediately. When it is used
  in more than one place, the places are listed and you must tick a confirmation.
- **Delete** is only possible when the file is not used anywhere.

You can also upload directly from any image field with the **+** button next to it.

## Redirects and administrators

*Settings › Redirects*: old addresses that forward to new ones (added automatically
for slug changes; you can add your own). *Settings › Administrators*: add people,
turn **Can sign in** off to block someone. Change your own password under your
initials (top right) › Profile.

**Forgotten password**: there is no e-mail reset. Someone with server (SSH) access
runs `php artisan cms:reset-password you@example.com` in `~/domains/<domain>/current`
and types a new password. Passwords need at least 12 characters with letters and
numbers. After five wrong attempts sign-in is paused briefly.

## Enquiries

The website never stores enquiries. The enquiry form, the WhatsApp buttons and the
chat widget open WhatsApp with a prepared message to the number in *Global settings ›
Contact details*; the visitor presses Send in WhatsApp.
