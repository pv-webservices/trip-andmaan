async (page) => {
  await page.setViewportSize({ width: 1440, height: 1000 })
  await page.emulateMedia({ reducedMotion: 'no-preference' })
  await page.goto('http://localhost:4322/', { waitUntil: 'networkidle' })
  await page.evaluate(async () => {
    await document.fonts.ready
    document.querySelectorAll('img').forEach((i) => (i.loading = 'eager'))
  })
  await page.waitForFunction(
    () => Array.from(document.images).every((i) => i.complete),
    undefined,
    { timeout: 15000 },
  )
  for (let y = 0; y < 4800; y += 650) {
    await page.evaluate(
      (y) => window.scrollTo({ top: y, behavior: 'instant' }),
      y,
    )
    await page.waitForTimeout(120)
  }
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }))
  await page.waitForTimeout(700)
  await page.screenshot({
    path: 'output/playwright/home-desktop.png',
    fullPage: true,
  })
  await page.screenshot({ path: 'output/playwright/hero-desktop.png' })
  await page.setViewportSize({ width: 390, height: 844 })
  await page.reload({ waitUntil: 'networkidle' })
  await page.evaluate(() =>
    document.querySelectorAll('img').forEach((i) => (i.loading = 'eager')),
  )
  await page.waitForFunction(
    () => Array.from(document.images).every((i) => i.complete),
    undefined,
    { timeout: 15000 },
  )
  const height = await page.evaluate(
    () => document.documentElement.scrollHeight,
  )
  for (let y = 0; y < height; y += 650) {
    await page.evaluate(
      (y) => window.scrollTo({ top: y, behavior: 'instant' }),
      y,
    )
    await page.waitForTimeout(100)
  }
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }))
  await page.waitForTimeout(700)
  await page.screenshot({
    path: 'output/playwright/home-mobile.png',
    fullPage: true,
  })
  await page.screenshot({ path: 'output/playwright/hero-mobile.png' })
  return await page.evaluate(() => ({
    viewport: innerWidth,
    scrollWidth: document.documentElement.scrollWidth,
    broken: Array.from(document.images)
      .filter((i) => i.loading !== 'lazy' && !i.naturalWidth)
      .map((i) => i.src),
  }))
}