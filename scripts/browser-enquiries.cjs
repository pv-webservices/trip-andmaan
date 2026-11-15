async (page) => {
  const assert = (value, message) => {
    if (!value) throw new Error(message)
  }
  await page.setViewportSize({ width: 390, height: 844 })
  await page.goto('http://localhost:4322/contact/?interest=Neil%20Island', {
    waitUntil: 'networkidle',
  })
  assert(
    (await page.getByLabel('Destination or travel interests').inputValue()) ===
      'Neil Island',
    'Interest prefill',
  )
  await page.getByLabel('Full name').fill('QA Visitor')
  await page.getByLabel('Mobile number').fill('abcdefghi')
  assert(
    await page.getByLabel('Mobile number').evaluate((el) => !el.validity.valid),
    'Invalid phone rejected',
  )
  await page.getByLabel('Mobile number').fill('+91 99999 99999')
  assert(
    await page.getByLabel('Mobile number').evaluate((el) => el.validity.valid),
    'Valid phone accepted',
  )
  await page.getByLabel('Email address').fill('qa@example.com')
  await page
    .getByLabel('What do you have in mind?')
    .fill('Local QA only: an island trip for two, with beach walks.')
  await page.getByRole('checkbox').check()
  await page.evaluate(() => {
    window.__opened = []
    window.open = (url) => {
      window.__opened.push(url)
      return null
    }
  })
  await page
    .getByRole('button', { name: 'Send Enquiry on WhatsApp' })
    .click()
  const fallback = page.getByRole('link', { name: 'Open WhatsApp' })
  await fallback.waitFor({ state: 'visible' })
  const href = await fallback.getAttribute('href')
  assert(href.startsWith('https://wa.me/919646969646?text='), 'WhatsApp recipient')
  assert(
    decodeURIComponent(href).includes('Neil Island'),
    'Enquiry includes selected interest',
  )
  assert(
    (await page.evaluate(() => window.__opened[0])) === href,
    'Submit opens WhatsApp',
  )
  await page.getByRole('button', { name: 'Open navigation' }).click()
  assert(
    await page.getByRole('dialog', { name: 'Navigation' }).isVisible(),
    'Mobile menu opens',
  )
  await page.keyboard.press('Shift+Tab')
  assert(
    await page.evaluate(() =>
      document.querySelector('#mobile-menu').contains(document.activeElement),
    ),
    'Menu traps focus',
  )
  await page.keyboard.press('Escape')
  assert(
    await page
      .getByRole('button', { name: 'Open navigation' })
      .evaluate((el) => document.activeElement === el),
    'Menu returns focus',
  )
  await page.getByRole('button', { name: 'Open navigation' }).click()
  await page
    .getByRole('navigation', { name: 'Mobile navigation' })
    .getByRole('link', { name: 'Gallery' })
    .click()
  await page.waitForURL('**/gallery/')
  return {
    validation: 'pass',
    interestPrefill: 'pass',
    whatsappEnquiry: 'pass',
    menuFocusAndEscape: 'pass',
    menuNavigation: 'pass',
    liveMessagesSent: 0,
  }
}