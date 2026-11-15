# Asset provenance

The client's original JPEGs, MP4s and documents are kept, untouched, in `source-files/client-media/` and `source-files/client-material/` (never deployed). Deployed derivatives are rebuilt from the masters in `source-files/` by `npm run images:optimize`.

- `/images/brand/logo-light-*.webp` (navy + gold, for light backgrounds) and `/images/brand/logo-dark-*.webp` (ivory + gold, for dark backgrounds): from `source-files/brand/logo-light.png` / `logo-dark.png`, which were cut from the supplied logo (`source-files/brand/trip-andaman-logo-original.jpeg`), white background removed, 800 × 221. Favicons use the gold-arc “A” cropped from the same artwork (`source-files/brand/logo-mark.png`) on a white square. The site palette in `src/styles/tokens.css` is sampled from this logo (navy `#0e2f4b`, gold `#b9873e` / `#deaa61`).
- `/images/trips/client-kayak-*`: resized WebP derivatives of `image-2.jpeg`.
- `/images/trips/client-neil-*`: resized WebP derivatives of `image-1.jpeg`, cropped to remove the overlaid caption text.
- `/images/trips/client-arrival-*`: cropped WebP derivatives of `image-3.jpeg` (travel group on arrival).
- `/images/trips/client-scuba-*` and `/videos/scuba-film.mp4`: poster frame and silent 12-second clip from `video-5.mp4`, cropped to remove the camera timestamp.
- `/images/illustrations/ai-*`: four illustrative AI-generated images; masters, prompts and routing records are in `source-files/illustrations/`.
- `/images/video-posters/underwater-*`: a representative frame from `video-2.mp4`.
- `/videos/underwater-film.mp4`: silent, compressed first 20 seconds of `video-2.mp4`, with no automatic playback or preload.
- `/images/editorial/hero-*`, `havelock-*`, `neil-*`, `sunset-*`, `bridge-*`, `ross-*`, `baratang-*`, `north-bay-*`: real Andaman landscape photographs downloaded from Wikimedia Commons; full creator, original URL, licence and modification details are stored in `src/data/credits.json` and linked publicly at `/image-credits/`.
- Fonts: locally bundled Manrope and Caveat via Fontsource (SIL Open Font License); no runtime font network requests.
- Icons: small inline SVG line icons authored for this site; logo was not redrawn.

Several supplied videos advertise Diganta Travels. They are deliberately not used or relabelled. `image-4.jpeg` is sideways and its setting has not been identified, so it is omitted. `video-7.mp4` is drone footage of a resort property and is not used. No testimonial, endorsement, rating or company partnership is inferred from supplied people or media. Public-use rights and participant releases for supplied media remain a client review item.

Reference artwork is a layout guide. Its scenic illustrations and example testimonial copy are not republished as verified Andaman photography or reviews.

Reproduction: the original Commons downloads are in `source-files/editorial/` with the search metadata (`commons.json`, `scenic-search.json`). The one-off download script was retired; its history is in git. Additional media source changes are recorded in the credits JSON. Derivative scenic image files retain their respective original Creative Commons licences; the website code is separate.
