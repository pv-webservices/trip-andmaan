// Central image-generation settings. Override any value through environment
// variables so models or quality can change without touching the pipeline.
const env = process.env;

export const imageConfig = {
  endpoint: env.OPENAI_IMAGE_ENDPOINT || 'https://api.openai.com/v1/images',
  models: {
    // Default: fast, cost-efficient model for routine website imagery.
    flare: env.OPENAI_IMAGE_MODEL_FLARE || 'gpt-image-2.5-flare',
    // Premium: hero assets, people, reference edits and detail-sensitive scenes.
    sunburst: env.OPENAI_IMAGE_MODEL_SUNBURST || 'gpt-image-2.5-sunburst',
  },
  quality: {
    default: env.OPENAI_IMAGE_QUALITY_DEFAULT || 'medium',
    premium: env.OPENAI_IMAGE_QUALITY_PREMIUM || 'high',
  },
  sizes: {
    square: '1024x1024',
    landscape: '1536x1024',
    portrait: '1024x1536',
  },
  // Preview WebP widths; run `npm run images:optimize` afterwards to produce the
  // deployed responsive set and update src/data/image-manifest.json.
  widths: [640, 1200, 1536],
  webpQuality: Number(env.IMAGE_WEBP_QUALITY || 80),
  timeoutMs: Number(env.OPENAI_IMAGE_TIMEOUT_MS || 240000),
  rawDir: 'source-files/illustrations',
  publicDir: 'public/images/illustrations',
};
