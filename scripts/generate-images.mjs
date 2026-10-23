// Build-time AI image pipeline (run on a developer machine, never in the browser).
// Usage: node --env-file=.env scripts/generate-images.mjs [name ...] [--force] [--dry-run] [--premium=name]
import { mkdir, writeFile, access, readFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';
import { imageConfig } from './image-gen/config.mjs';
import { route } from './image-gen/router.mjs';
import { generateImage, editImage } from './image-gen/client.mjs';
import { imageTasks } from './image-gen/manifest.mjs';

const args = process.argv.slice(2);
const force = args.includes('--force');
const dryRun = args.includes('--dry-run');
const premium = new Set(
  args.filter((a) => a.startsWith('--premium=')).map((a) => a.split('=')[1]),
);
const only = args.filter((a) => !a.startsWith('--'));
const tasks = imageTasks.filter((t) => !only.length || only.includes(t.name));
const exists = (file) =>
  access(file).then(
    () => true,
    () => false,
  );

async function optimise(task, raw) {
  if (task.postprocess === 'trim') {
    const file = path.join(imageConfig.publicDir, `${task.name}.png`);
    await sharp(raw)
      .trim()
      .resize({ width: 600, withoutEnlargement: true })
      .png({ compressionLevel: 9, palette: true })
      .toFile(file);
    await sharp(raw)
      .trim()
      .resize({ width: 600, withoutEnlargement: true })
      .webp({ quality: 90, alphaQuality: 90 })
      .toFile(file.replace('.png', '.webp'));
    return;
  }
  for (const width of imageConfig.widths) {
    await sharp(raw)
      .resize({ width, withoutEnlargement: true })
      .webp({ quality: imageConfig.webpQuality, effort: 6 })
      .toFile(path.join(imageConfig.publicDir, `${task.name}-${width}.webp`));
  }
}

await mkdir(imageConfig.rawDir, { recursive: true });
for (const task of tasks) {
  const raw = path.join(imageConfig.rawDir, `${task.name}.png`);
  const plan = route({ ...task, flareRejected: premium.has(task.name) });
  console.log(
    `${task.name}: ${plan.model} · ${plan.quality} · ${plan.size} (${plan.reason})`,
  );
  if (dryRun) continue;
  if (force || !(await exists(raw))) {
    const options = {
      model: plan.model,
      prompt: task.prompt,
      size: plan.size,
      quality: plan.quality,
      background: task.background,
    };
    const bytes = task.reference
      ? await editImage({ ...options, reference: task.reference })
      : await generateImage(options);
    await writeFile(raw, bytes);
  }
  await optimise(task, await readFile(raw));
  const record = {
    name: task.name,
    placement: task.placement,
    alt: task.alt,
    model: plan.model,
    quality: plan.quality,
    size: plan.size,
    reason: plan.reason,
    prompt: task.prompt,
  };
  await writeFile(
    path.join(imageConfig.rawDir, `${task.name}.json`),
    JSON.stringify(record, null, 2),
  );
}
