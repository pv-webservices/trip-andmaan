import assert from 'node:assert/strict';
import { describe, test } from 'node:test';
import {
  buildEnquiryMessage,
  cleanLine,
  validateEnquiry,
  whatsappUrl,
  type EnquiryValues,
} from '../src/lib/enquiry.ts';

const NOW = new Date(2026, 10, 15); // 15 Nov 2026
const valid: EnquiryValues = {
  fullName: 'Asha Rao',
  mobile: '+91 98765 43210',
  travelDate: '2026-12-20',
  travellers: '4',
  interest: 'Havelock & Neil',
  message: 'A relaxed family trip with beach days.',
  consent: true,
};
const errorsFor = (patch: Partial<EnquiryValues>) =>
  validateEnquiry({ ...valid, ...patch }, NOW);

describe('validateEnquiry', () => {
  test('accepts a complete, valid enquiry', () => {
    assert.deepEqual(validateEnquiry(valid, NOW), {});
  });

  test('accepts optional fields left empty', () => {
    assert.deepEqual(
      errorsFor({ travelDate: '', travellers: '', interest: '' }),
      {},
    );
  });

  test('requires a name of at least two characters, ignoring spaces', () => {
    assert.ok(errorsFor({ fullName: '  A  ' }).fullName);
    assert.ok(errorsFor({ fullName: 'x'.repeat(101) }).fullName);
  });

  test('requires 10 to 15 phone digits after normalisation', () => {
    assert.ok(errorsFor({ mobile: '' }).mobile);
    assert.ok(errorsFor({ mobile: '98765 4321' }).mobile, '9 digits');
    assert.equal(
      errorsFor({ mobile: '9876543210' }).mobile,
      undefined,
      '10 digits',
    );
    assert.equal(errorsFor({ mobile: '+44 (20) 7946-0958' }).mobile, undefined);
    assert.ok(errorsFor({ mobile: '+1234567890123456' }).mobile, '16 digits');
    assert.ok(errorsFor({ mobile: '98765abc43210' }).mobile, 'letters');
  });

  test('rejects past, malformed and far-future travel dates', () => {
    assert.ok(errorsFor({ travelDate: '2026-11-14' }).travelDate);
    assert.equal(errorsFor({ travelDate: '2026-11-15' }).travelDate, undefined);
    assert.ok(errorsFor({ travelDate: '15/12/2026' }).travelDate);
    assert.ok(errorsFor({ travelDate: '2029-01-01' }).travelDate);
  });

  test('requires a whole number of travellers from 1 to 100', () => {
    assert.ok(errorsFor({ travellers: '0' }).travellers);
    assert.ok(errorsFor({ travellers: '2.5' }).travellers);
    assert.ok(errorsFor({ travellers: '101' }).travellers);
  });

  test('enforces message length and consent', () => {
    assert.ok(errorsFor({ message: 'short' }).message);
    assert.ok(errorsFor({ message: 'x'.repeat(1001) }).message);
    assert.ok(errorsFor({ consent: false }).consent);
  });

  test('validates script-set values the same as typed ones', () => {
    const autofilled = { ...valid, mobile: '12345', fullName: '' };
    assert.deepEqual(Object.keys(validateEnquiry(autofilled, NOW)).sort(), [
      'fullName',
      'mobile',
    ]);
  });
});

describe('buildEnquiryMessage', () => {
  test('includes every field and the source page', () => {
    const text = buildEnquiryMessage(valid, 'Contact page');
    assert.match(text, /^Hello Trip Andaman/);
    for (const part of [
      'Asha Rao',
      '+91 98765 43210',
      '2026-12-20',
      'Travellers: 4',
      'Havelock & Neil',
      'beach days',
      'Contact page',
    ])
      assert.ok(text.includes(part), part);
  });

  test('uses "To discuss" for empty optional fields', () => {
    const text = buildEnquiryMessage(
      { ...valid, travelDate: '', travellers: '', interest: '' },
      'x',
    );
    assert.equal(text.match(/To discuss/g)?.length, 3);
  });

  test('strips control characters and collapses whitespace', () => {
    assert.equal(cleanLine('  Asha\u0000\t  Rao \n'), 'Asha Rao');
  });
});

describe('whatsappUrl', () => {
  test('uses digits-only number and encodes the text', () => {
    const url = whatsappUrl('+91 96469-69646', 'Hi & bye?\nLine 2 #1');
    assert.equal(
      url,
      'https://wa.me/919646969646?text=Hi%20%26%20bye%3F%0ALine%202%20%231',
    );
    assert.equal(new URL(url).searchParams.get('text'), 'Hi & bye?\nLine 2 #1');
  });

  test('refuses an incomplete number', () => {
    assert.throws(() => whatsappUrl('12345', 'x'));
  });
});
