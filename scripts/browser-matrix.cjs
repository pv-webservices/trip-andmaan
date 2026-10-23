async (page) => {
  const routes = [
    '/',
    '/about/',
    '/tour-packages/',
    '/tour-packages/customized-trip/',
    '/destinations/',
    '/destinations/havelock-island/',
    '/destinations/neil-island/',
    '/destinations/ross-island/',
    '/destinations/north-bay-island/',
    '/destinations/baratang-island/',
    '/experiences/',
    '/gallery/',
    '/contact/',
    '/privacy/',
    '/travel-information/',
    '/image-credits/',
    '/404.html',
  ]
  const widths = [1440, 1280, 1024, 768, 430, 390, 360]
  const failures = []
  const errors = []
  let checks = 0
  page.on('pageerror', (error) => errors.push(error.message))
  await page.emulateMedia({ reducedMotion: 'reduce' })
  for (const route of routes) {
    await page.goto('http://localhost:4322' + route, {
      waitUntil: 'networkidle',
    })
    await page.evaluate(async () => {
      await document.fonts.ready
      document.querySelectorAll('img').forEach((i) => (i.loading = 'eager'))
      await Promise.all(
        Array.from(document.images).map((i) => i.decode().catch(() => {})),
      )
    })
    for (const width of widths) {
      await page.setViewportSize({ width, height: 900 })
      await page.waitForTimeout(75)
      await page.waitForFunction(
        () => Array.from(document.images).every((i) => i.complete),
        undefined,
        { timeout: 10000 },
      )
      const state = await page.evaluate(() => ({
        overflow: document.documentElement.scrollWidth > innerWidth + 1,
        h1: document.querySelectorAll('h1').length,
        broken: Array.from(document.images)
          .filter((i) => !i.naturalWidth)
          .map((i) => i.src),
        current: document.querySelectorAll('.desktop-nav [aria-current="page"]')
          .length,
      }))
      const expectedCurrent = [
        '/privacy/',
        '/travel-information/',
        '/image-credits/',
        '/404.html',
      ].includes(route)
        ? 0
        : 1
      if (
        state.overflow ||
        state.h1 !== 1 ||
        state.broken.length ||
        state.current !== expectedCurrent
      )
        failures.push({ route, width, ...state })
      checks++
    }
  }
  if (errors.length) failures.push({ browserErrors: errors })
  return { checks, routes: routes.length, widths, failures }
}