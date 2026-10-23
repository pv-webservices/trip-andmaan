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
  const reports = []
  let scans = 0
  await page.emulateMedia({ reducedMotion: 'reduce' })
  for (const route of routes) {
    for (const width of [1440, 390]) {
      await page.setViewportSize({ width, height: 900 })
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
      await page.waitForFunction(
        () => Array.from(document.images).every((i) => i.complete),
        undefined,
        { timeout: 10000 },
      )
      await page.addScriptTag({ path: 'node_modules/axe-core/axe.min.js' })
      const report = await page.evaluate(async () => {
        const result = await axe.run(document, {
          runOnly: {
            type: 'tag',
            values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'],
          },
        })
        return result.violations.map((v) => ({
          id: v.id,
          impact: v.impact,
          description: v.description,
          nodes: v.nodes.map((n) => ({
            target: n.target,
            summary: n.failureSummary,
          })),
        }))
      })
      if (report.length) reports.push({ route, width, violations: report })
      scans++
    }
  }
  return { scans, reports }
}