async (page) => {
  const assert=(condition,message)=>{if(!condition)throw new Error(message)}
  await page.goto('http://localhost:4322/gallery/',{waitUntil:'networkidle'})
  await page.getByRole('button',{name:'Beaches',exact:true}).click()
  assert(await page.locator('figure:visible').count()===2,'Beaches filter')
  await page.getByRole('link',{name:/Sandy lagoon/}).click()
  const viewer=page.getByRole('dialog',{name:'Photo gallery viewer'})
  await viewer.waitFor({state:'visible'})
  assert(await viewer.locator('.lightbox-counter').textContent()==='1 / 2','Filtered gallery count')
  await page.getByRole('button',{name:'Next photograph'}).click()
  assert((await viewer.locator('img').getAttribute('alt')).includes('Elephant Beach'),'Next photograph')
  await page.keyboard.press('ArrowLeft')
  assert((await viewer.locator('img').getAttribute('alt')).includes('Sandy lagoon'),'Keyboard previous')
  await page.keyboard.press('ArrowLeft')
  assert((await viewer.locator('img').getAttribute('alt')).includes('Elephant Beach'),'Wrap navigation')
  await page.keyboard.press('Escape')
  assert(await page.getByRole('link',{name:/Sandy lagoon/}).evaluate(el=>el===document.activeElement),'Gallery returns focus')
  await page.getByRole('button',{name:'All',exact:true}).click()
  assert(await page.locator('figure:visible').count()===10,'All images restored')
  return {filtering:'pass',lightbox:'pass',nextPreviousAndWrap:'pass',arrowKeys:'pass',escape:'pass',focusReturn:'pass'}
}
