import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { imageConfig } from './config.mjs';

// Server-side only: the key is read from the environment and never written
// to source, build output or browser code.
function apiKey() {
  const key = process.env.OPENAI_API_KEY;
  if (!key)
    throw new Error(
      'OPENAI_API_KEY is not set. Add it to .env (never commit it).',
    );
  return key;
}

async function request(url, init) {
  const response = await fetch(url, {
    ...init,
    headers: { Authorization: `Bearer ${apiKey()}`, ...init.headers },
    signal: AbortSignal.timeout(imageConfig.timeoutMs),
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(
      `OpenAI image request failed (${response.status}): ${body.error?.message || 'unknown error'}`,
    );
  }
  const b64 = body.data?.[0]?.b64_json;
  if (!b64) throw new Error('OpenAI image response contained no image data.');
  return Buffer.from(b64, 'base64');
}

export function generateImage({ model, prompt, size, quality, background }) {
  return request(`${imageConfig.endpoint}/generations`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      model,
      prompt,
      size,
      quality,
      n: 1,
      ...(background ? { background } : {}),
    }),
  });
}

export async function editImage({
  model,
  prompt,
  size,
  quality,
  reference,
  background,
}) {
  const form = new FormData();
  const bytes = await readFile(reference);
  const type = reference.endsWith('.png') ? 'image/png' : 'image/jpeg';
  form.append('image', new Blob([bytes], { type }), path.basename(reference));
  for (const [key, value] of Object.entries({
    model,
    prompt,
    size,
    quality,
    background,
  })) {
    if (value) form.append(key, value);
  }
  return request(`${imageConfig.endpoint}/edits`, {
    method: 'POST',
    body: form,
  });
}
