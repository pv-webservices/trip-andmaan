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
console.log(
  `Excluded ${originals.length} original source media files from dist; public originals remain intact.`,
);
