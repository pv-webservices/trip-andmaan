# Asset provenance

The client's four JPEGs and eight MP4s were already in `public/`; originals remain untouched. The supplied PNG design reference lives outside the checkout at the path given in the chat.

- `/images/logo-hd.webp` / `logo-hd.png`: high-resolution restoration of the supplied logo made with gpt-image-2.5-sunburst (reference-image edit, transparent background), trimmed and compressed. The original 109 × 57 crop is kept at `output/media/logo-original-crop.png`. Replace with the client's master artwork when supplied.
- Original crop: supplied logo artwork cropped from the top left of that design reference, preserving colours/proportions; source resolution 109 × 57. Request the original transparent high-resolution asset before a high-resolution launch.
- `/images/client-kayak-*`: resized WebP derivatives of `image-2.jpeg`.
- `/images/client-neil-*`: resized WebP derivatives of `image-1.jpeg`, cropped to remove the overlaid caption text.
- `/images/client-arrival-*`: cropped WebP derivatives of `image-3.jpeg` (travel group on arrival).
- `/images/client-scuba-*` and `/scuba-film.mp4`: poster frame and silent 12-second clip from `video-5.mp4`, cropped to remove the camera timestamp.
- `/images/ai-*`: four illustrative AI-generated images (see README › AI image generation); prompts and routing records are in `output/generated/*.json`.
- `/images/underwater-*`: a representative frame from `video-2.mp4`.
- `/underwater-film.mp4`: silent, compressed first 20 seconds of `video-2.mp4`, with no automatic playback or preload.
- `/images/hero-*`, `havelock-*`, `neil-*`, `sunset-*`, `bridge-*`, `ross-*`, `baratang-*`, `north-bay-*`: real Andaman landscape photographs downloaded from Wikimedia Commons; full creator, original URL, licence and modification details are stored in `src/data/credits.json` and linked publicly at `/image-credits/`.
- Fonts: locally bundled Manrope and Caveat via Fontsource (SIL Open Font License); no runtime font network requests.
- Icons: small inline SVG line icons authored for this site; logo was not redrawn.

Several supplied videos advertise Diganta Travels. They are deliberately not used or relabelled. `image-4.jpeg` is sideways and its setting has not been identified, so it is omitted. `video-7.mp4` is drone footage of a resort property and is not used. No testimonial, endorsement, rating or company partnership is inferred from supplied people or media. Public-use rights and participant releases for supplied media remain a client review item.

Reference artwork is a layout guide. Its scenic illustrations and example testimonial copy are not republished as verified Andaman photography or reviews.

Reproduction: `scripts/prepare-media.py` uses the Commons search metadata saved in `output/media/commons.json`; downloads original photographs, saves responsive WebP versions and creates the logo crop. Additional media source changes are recorded in the credits JSON. Derivative scenic image files retain their respective original Creative Commons licences; the website code is separate.
