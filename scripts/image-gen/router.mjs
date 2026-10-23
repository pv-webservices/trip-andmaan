import { imageConfig } from './config.mjs';

// Tasks that genuinely need the premium model. Everything else uses Flare.
const premiumReasons = [
  ['reference', (t) => Boolean(t.reference), 'reference-image edit'],
  ['hero', (t) => t.priority === 'hero', 'hero imagery'],
  ['people', (t) => (t.people ?? 0) >= 2, 'multiple people'],
  ['complex', (t) => t.complexity === 'complex', 'complex composition'],
  ['retry', (t) => t.flareRejected === true, 'Flare result rejected'],
];

export function selectModel(task) {
  const reason = premiumReasons.find(([, test]) => test(task));
  return reason
    ? {
        model: imageConfig.models.sunburst,
        tier: 'sunburst',
        reason: reason[2],
      }
    : {
        model: imageConfig.models.flare,
        tier: 'flare',
        reason: 'standard imagery',
      };
}

export function selectQuality(task) {
  const important = ['hero', 'key'].includes(task.priority) || task.reference;
  return important ? imageConfig.quality.premium : imageConfig.quality.default;
}

export function selectSize(task) {
  const size = imageConfig.sizes[task.orientation];
  if (!size)
    throw new Error(
      `Unknown orientation "${task.orientation}" for ${task.name}`,
    );
  return size;
}

export function route(task) {
  return {
    ...selectModel(task),
    quality: selectQuality(task),
    size: selectSize(task),
  };
}
