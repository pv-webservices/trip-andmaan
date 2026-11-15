// Builds every deployed image from the masters in source-files/.
//   npm run images:optimize
// Outputs (all committed, all deployed):
//   public/images/<folder>/<name>-<width>.webp   responsive WebP
//   public/images/brand/*                         logos and Open Graph image
//   public/favicon*, apple-touch-icon.png, web-app-manifest-*.png
//   src/data/image-manifest.json                  widths + intrinsic size
import { mkdir, readdir, rm, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';

const root = path.resolve();
const masters = path.join(root, 'source-files');
const out = path.join(root, 'public/images');
const FOLDERS = ['editorial', 'trips', 'illustrations', 'video-posters'];
const WIDTHS = [480, 800, 1200, 1600];
const WEBP = { quality: 72, effort: 5 };
const MASTER_EXT = /\.(jpe?g|png|webp)$/i;
// Byte budget for the 1600px derivative; smaller widths scale down from it.
const MAX_BYTES = 340 * 1024;

async function responsive() {
  const manifest = {};
  for (const folder of FOLDERS) {
    const dir = path.join(out, folder);
    await rm(dir, { recursive: true, force: true });
    await mkdir(dir, { recursive: true });
    const files = (await readdir(path.join(masters, folder))).filter((f) =>
      MASTER_EXT.test(f),
    );
    for (const file of files) {
      const name = file.replace(MASTER_EXT, '');
      const source = sharp(path.join(masters, folder, file)).rotate();
      const { width, height } = await source.metadata();
      const widths = WIDTHS.filter((w) => w <= width);
      if (widths.length === 0 || width - widths.at(-1) > 120)
        widths.push(Math.min(width, WIDTHS.at(-1)));
      const unique = [...new Set(widths)].sort((a, b) => a - b);
      const written = [];
      for (const w of unique) {
        // Step quality down for very detailed photos until under budget;
        // drop the largest width if it still cannot fit.
        const budget = MAX_BYTES * (w / 1600) ** 2 + 30_000;
        let quality = WEBP.quality;
        let data;
        do {
          data = await source
            .clone()
            .resize({ width: w, withoutEnlargement: true })
            .webp({ ...WEBP, quality })
            .toBuffer();
          quality -= 8;
        } while (data.length > budget && quality >= 52);
        // Highly textured photos (foliage, rock): soften fine grain slightly.
        if (data.length > budget)
          data = await source
            .clone()
            .resize({ width: w, withoutEnlargement: true })
            .median(3)
            .webp({ ...WEBP, quality: 60 })
            .toBuffer();
        if (data.length > budget && w > 1200 && written.length) continue;
        await writeFile(path.join(dir, `${name}-${w}.webp`), data);
        written.push(w);
      }
      unique.splice(0, unique.length, ...written);
      const largest = unique.at(-1);
      manifest[name] = {
        folder,
        widths: unique,
        width: largest,
        height: Math.round((height * largest) / width),
      };
    }
  }
  await writeFile(
    path.join(root, 'src/data/image-manifest.json'),
    `${JSON.stringify(manifest, null, 2)}\n`,
  );
  return Object.keys(manifest).length;
}

async function brand() {
  const dir = path.join(out, 'brand');
  await mkdir(dir, { recursive: true });
  for (const tone of ['light', 'dark']) {
    const logo = sharp(path.join(masters, `brand/logo-${tone}.png`));
    for (const width of [240, 400])
      await logo
        .clone()
        .resize({ width })
        .webp({ quality: 82, effort: 6 })
        .toFile(path.join(dir, `logo-${tone}-${width}.webp`));
  }
  // Organization logo for structured data (on white, readable anywhere).
  await sharp(path.join(masters, 'brand/logo-light.png'))
    .flatten({ background: '#ffffff' })
    .png({ compressionLevel: 9 })
    .toFile(path.join(dir, 'logo-800x221.png'));
  // Open Graph / Twitter card: 1200x630 JPEG.
  await sharp(path.join(masters, 'editorial/hero.jpg'))
    .rotate()
    .resize(1200, 630, { fit: 'cover', position: 'attention' })
    .jpeg({ quality: 80, mozjpeg: true })
    .toFile(path.join(dir, 'og-image-1200x630.jpg'));
}

/** Single-image ICO with an embedded PNG (supported by all current browsers). */
function ico(png, size) {
  const header = Buffer.alloc(22);
  header.writeUInt16LE(0, 0);
  header.writeUInt16LE(1, 2);
  header.writeUInt16LE(1, 4);
  header.writeUInt8(size >= 256 ? 0 : size, 6);
  header.writeUInt8(size >= 256 ? 0 : size, 7);
  header.writeUInt8(0, 8);
  header.writeUInt8(0, 9);
  header.writeUInt16LE(1, 10);
  header.writeUInt16LE(32, 12);
  header.writeUInt32LE(png.length, 14);
  header.writeUInt32LE(22, 18);
  return Buffer.concat([header, png]);
}

async function icons() {
  // The gold-arc "A" from the approved logo, cropped from the original artwork.
  const mark = await sharp(
    path.join(masters, 'brand/trip-andaman-logo-original.jpeg'),
  )
    .extract({ left: 40, top: 245, width: 228, height: 205 })
    .png()
    .toBuffer()
    .then((crop) =>
      sharp(crop)
        .trim({ background: '#ffffff', threshold: 40 })
        .png()
        .toBuffer(),
    );
  await writeFile(path.join(masters, 'brand/logo-mark.png'), mark);
  const square = async (size, padding = 0.12) => {
    const inner = Math.round(size * (1 - padding * 2));
    const glyph = await sharp(mark)
      .resize(inner, inner, { fit: 'contain', background: '#ffffff' })
      .toBuffer();
    return sharp({
      create: { width: size, height: size, channels: 3, background: '#ffffff' },
    })
      .composite([{ input: glyph, gravity: 'centre' }])
      .png({ palette: true, quality: 95, compressionLevel: 9 })
      .toBuffer();
  };
  const pub = path.join(root, 'public');
  const files = {
    'favicon-48x48.png': [48, 0.06],
    'favicon-96x96.png': [96, 0.08],
    'apple-touch-icon.png': [180, 0.12],
    'web-app-manifest-192x192.png': [192, 0.12],
    'web-app-manifest-512x512.png': [512, 0.12],
  };
  for (const [file, [size, padding]] of Object.entries(files))
    await writeFile(path.join(pub, file), await square(size, padding));
  await writeFile(
    path.join(pub, 'favicon.ico'),
    ico(await square(48, 0.06), 48),
  );
  const svgPng = (await square(96, 0.08)).toString('base64');
  await writeFile(
    path.join(pub, 'favicon.svg'),
    `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 96 96"><image width="96" height="96" xlink:href="data:image/png;base64,${svgPng}"/></svg>\n`,
  );
}

const count = await responsive();
await brand();
await icons();
console.log(`Optimised ${count} images, brand assets and favicons.`);
