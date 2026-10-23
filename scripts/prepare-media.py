"""Prepare licensed local scenery and source records; original client files stay intact."""
import json, re, requests
from pathlib import Path
from PIL import Image, ImageOps, ImageDraw
from io import BytesIO

root = Path(__file__).resolve().parents[1]
data = json.loads((root / 'output/media/commons.json').read_text(encoding='utf8'))
data.update(json.loads((root / 'output/media/scenic-search.json').read_text(encoding='utf8')))
choices = {
    'hero': ('Havelock beach', 'Sandy lagoon'),
    'havelock': ('Havelock beach', 'tropical bliss'),
    'sunset': ('Elephant Beach', 'Elephant Beach, Andamans.jpg'),
    'neil': ('Neil island', 'Tropical beach'),
    'bridge': ('Neil island', 'Coral bridge'),
    'ross': ('Ross island ruins', '(106)'),
    'baratang': ('Baratang cave', '(23)'),
    'north-bay': ('North Bay Andaman', 'island-1'),
}
session = requests.Session()
session.headers['User-Agent'] = 'TripAndamanWebsite/1.0 (licensed image preparation)'
credits = []
for name, (query, match) in choices.items():
    item = next(p for p in data[query]['query']['pages'].values() if match in p['title'])
    info = item['imageinfo'][0]
    meta = info['extmetadata']
    raw = root / 'output/media' / (name + '.jpg')
    if not raw.exists():
        response = session.get(info['url'], timeout=60)
        response.raise_for_status()
        raw.write_bytes(response.content)
    im = ImageOps.exif_transpose(Image.open(raw)).convert('RGB')
    for width in [640, 1200, 1920]:
        copy = im.copy()
        copy.thumbnail((width, width * 2))
        copy.save(root / f'public/images/{name}-{width}.webp', quality=84)
    plain = lambda value: re.sub('<[^>]+>', '', value)
    credits.append({'name': name, 'title': item['title'][5:], 'author': plain(meta.get('Artist', {}).get('value', 'See source')), 'license': meta['LicenseShortName']['value'], 'licenseUrl': meta.get('LicenseUrl', {}).get('value', ''), 'source': info['descriptionurl'], 'changes': 'Resized and converted to WebP; displayed with responsive crops.'})
    print(name, im.size)
(root / 'src/data/credits.json').write_text(json.dumps(credits, indent=2), encoding='utf8')
thumbs = [ImageOps.fit(Image.open(root / f'public/images/{n}-640.webp'), (400,240)) for n in choices]
sheet = Image.new('RGB', (1600, 540), 'white')
draw = ImageDraw.Draw(sheet)
for i, (name, thumb) in enumerate(zip(choices, thumbs)):
    x, y = i % 4 * 400, i // 4 * 270
    sheet.paste(thumb, (x,y))
    draw.text((x+8,y+245), name, fill='black')
sheet.save(root / 'output/media/scenery.jpg')
# Preserve the supplied brand artwork, extracted from the supplied reference.
reference = Path(r'F:/Z-Clients/Indian Clients/Business Website Clients/trip-andmaan/public/Trip Andaman Tropical Travel Homepage.png')
im = Image.open(reference)
print('reference', im.size)
im.crop((40, 4, 149, 61)).save(root / 'public/images/logo.png')
