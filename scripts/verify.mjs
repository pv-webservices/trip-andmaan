import { readFile, readdir, access } from 'node:fs/promises';
import path from 'node:path';
import assert from 'node:assert/strict';
const root = path.resolve('dist');
async function files(dir) {
  return (
    await Promise.all(
      (await readdir(dir, { withFileTypes: true })).map((entry) =>
        entry.isDirectory()
          ? files(path.join(dir, entry.name))
          : [path.join(dir, entry.name)],
      ),
    )
  ).flat();
}
const pages = (await files(root)).filter((f) => f.endsWith('.html'));
assert(
  pages.length >= 17,
  `Expected complete multipage site; found ${pages.length}`,
);
const failures = [];
for (const file of pages) {
  const html = await readFile(file, 'utf8');
  assert.equal(
    (html.match(/<h1[\s>]/g) || []).length,
    1,
    `${file}: exactly one h1`,
  );
  assert(/<title>[^<]+<\/title>/.test(html), `${file}: missing title`);
  for (const match of html.matchAll(/(?:href|src)="([^"#?]+)[^\"]*"/g)) {
    const url = match[1];
    if (!url.startsWith('/') || url.startsWith('//')) continue;
    const target = path.join(
      root,
      decodeURIComponent(url),
      url.endsWith('/') ? 'index.html' : '',
    );
    try {
      await access(target);
    } catch {
      failures.push(`${path.relative(root, file)} -> ${url}`);
    }
  }
  assert(!/href="(?:#|)"/.test(html), `${file}: empty action`);
  assert(
    !/andaman-getaway|island-explorer|andaman-bliss/.test(html),
    `${file}: draft package leaked`,
  );
}
assert.equal(
  failures.length,
  0,
  `Broken links/assets:\n${failures.join('\n')}`,
);
for (const file of ['video-1.mp4', 'video-6.mp4', 'image-4.jpeg']) {
  await assert.rejects(
    access(path.join(root, file)),
    `${file}: unused original should not be published`,
  );
}
// The OpenAI key is used only by the build-time image script; it must never ship.
for (const file of await files(root)) {
  if (!/\.(html|js|css|json|txt|xml)$/.test(file)) continue;
  const text = await readFile(file, 'utf8');
  assert(
    !/sk-[A-Za-z0-9_-]{20,}|OPENAI_API_KEY/.test(text),
    `${file}: possible API credential in build output`,
  );
}
console.log(
  `Verified ${pages.length} static pages: local links/assets, one h1, titles, no empty actions, no draft package leakage and no API credentials.`,
);
