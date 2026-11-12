import { rm } from 'node:fs/promises';
import path from 'node:path';
// Original media stays in public; publish only the selected, optimized derivatives.
const output = path.resolve('dist');
const originals = [
  ...Array.from({ length: 4 }, (_, i) => `image-${i + 1}.jpeg`),
  ...Array.from({ length: 8 }, (_, i) => `video-${i + 1}.mp4`),
];
for (const name of originals)
  await rm(path.join(output, name), { force: true });
// Client source folder (raw photos, raw videos, itinerary Word/PDF files).
// Optimized copies live in public/images and public/videos.
const sourceFolders = ['new material'];
for (const folder of sourceFolders)
  await rm(path.join(output, folder), { recursive: true, force: true });
console.log(
  `Excluded ${originals.length} original media files and ${sourceFolders.length} source folder from dist; public originals remain intact.`,
);
