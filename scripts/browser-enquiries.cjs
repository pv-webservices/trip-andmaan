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
  await page.getByRole('button', { name: 'Prepare Email Enquiry' }).click()
  const draft = page.getByRole('link', { name: 'Open Email Draft' })
  await draft.waitFor({ state: 'visible' })
  const href = await draft.getAttribute('href')
  assert(
    href.startsWith('mailto:tripandamanin2026@gmail.com?'),
    'Correct mail recipient',
  )
  assert(
    decodeURIComponent(href).includes('Neil Island'),
    'Enquiry includes selected interest',
  )
  assert(
    (await page.getByRole('status').textContent()).includes(
      'Nothing has been sent yet',
    ),
    'No false sent claim',
  )
  await page.evaluate(
    () => (document.querySelector('form').dataset.endpoint = '/__qa_form'),
  )
  await page.route('**/__qa_form', (route) =>
    route.fulfill({ status: 500, contentType: 'application/json', body: '{}' }),
  )
  await page.getByRole('button', { name: 'Prepare Email Enquiry' }).click()
  await page.waitForFunction(() =>
    document
      .querySelector('.form-status')
      .textContent.includes('could not confirm'),
  )
  assert(
    (await page.getByLabel('Full name').inputValue()) === 'QA Visitor',
    'Failed request preserves fields',
  )
  await page.unroute('**/__qa_form')
  await page.route('**/__qa_form', (route) =>
    route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: '{"ok":true}',
    }),
  )
  await page.getByRole('button', { name: 'Send Enquiry' }).click()
  await page.waitForFunction(() =>
    document
      .querySelector('.form-status')
      .textContent.includes('service accepted'),
  )
  assert(
    (await page.getByLabel('Full name').inputValue()) === '',
    'Accepted response clears fields',
  )
  await page.unroute('**/__qa_form')
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
    emailDraft: 'pass',
    mockedProviderAccepted: 'pass',
    mockedProviderFailure: 'pass',
    menuFocusAndEscape: 'pass',
    menuNavigation: 'pass',
    liveMessagesSent: 0,
  }
}