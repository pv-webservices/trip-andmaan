// Pure validation and message building for the WhatsApp enquiry composer.
// No DOM access here, so it can be unit tested with `node --test`.

export interface EnquiryValues {
  fullName: string;
  mobile: string;
  travelDate: string;
  travellers: string;
  interest: string;
  message: string;
  consent: boolean;
}

export type EnquiryField = keyof EnquiryValues;
export type EnquiryErrors = Partial<Record<EnquiryField, string>>;

export const LIMITS = {
  nameMin: 2,
  nameMax: 100,
  phoneDigitsMin: 10,
  phoneDigitsMax: 15,
  interestMax: 180,
  messageMin: 10,
  messageMax: 1000,
  travellersMin: 1,
  travellersMax: 100,
  /** Furthest travel date accepted, in days from today. */
  dateMaxDays: 730,
} as const;

// Control characters other than tab/newline; never useful in a chat message.
const CONTROL_CHARS = /[\u0000-\u0008\u000b\u000c\u000e-\u001f\u007f]/g;

/** Single-line field: drop control characters and collapse whitespace. */
export const cleanLine = (value: string): string =>
  value.replace(CONTROL_CHARS, '').replace(/\s+/g, ' ').trim();

/** Multi-line field: keep line breaks, drop control characters, cap blank lines. */
export const cleanText = (value: string): string =>
  value
    .replace(/\r\n?/g, '\n')
    .replace(CONTROL_CHARS, '')
    .replace(/[ \t]+/g, ' ')
    .replace(/\n{3,}/g, '\n\n')
    .trim();

export const phoneDigits = (value: string): string => value.replace(/\D/g, '');

const isoDate = (date: Date): string =>
  `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

export const todayIso = (now: Date = new Date()): string => isoDate(now);

export const maxTravelDateIso = (now: Date = new Date()): string => {
  const max = new Date(now);
  max.setDate(max.getDate() + LIMITS.dateMaxDays);
  return isoDate(max);
};

/**
 * Validates every field from its current value, so autofilled or
 * script-set values are checked exactly like typed ones.
 */
export function validateEnquiry(
  values: EnquiryValues,
  now: Date = new Date(),
): EnquiryErrors {
  const errors: EnquiryErrors = {};
  const name = cleanLine(values.fullName);
  if (name.length < LIMITS.nameMin)
    errors.fullName = 'Enter your name (at least 2 characters).';
  else if (name.length > LIMITS.nameMax)
    errors.fullName = `Keep your name under ${LIMITS.nameMax} characters.`;

  const mobile = values.mobile.trim();
  const digits = phoneDigits(mobile);
  if (!mobile) errors.mobile = 'Enter your mobile number.';
  else if (!/^\+?[\d\s().-]+$/.test(mobile))
    errors.mobile =
      'Use only numbers, spaces, brackets, dashes and a leading +.';
  else if (
    digits.length < LIMITS.phoneDigitsMin ||
    digits.length > LIMITS.phoneDigitsMax
  )
    errors.mobile = 'Enter a mobile number with 10 to 15 digits.';

  const date = values.travelDate.trim();
  if (date) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || Number.isNaN(Date.parse(date)))
      errors.travelDate = 'Enter a valid travel date.';
    else if (date < todayIso(now))
      errors.travelDate = 'Choose today or a future date.';
    else if (date > maxTravelDateIso(now))
      errors.travelDate = 'Choose a date within the next two years.';
  }

  const travellers = values.travellers.trim();
  if (travellers) {
    const count = Number(travellers);
    if (
      !Number.isInteger(count) ||
      count < LIMITS.travellersMin ||
      count > LIMITS.travellersMax
    )
      errors.travellers = 'Enter a whole number from 1 to 100.';
  }

  if (cleanLine(values.interest).length > LIMITS.interestMax)
    errors.interest = `Keep this under ${LIMITS.interestMax} characters.`;

  const message = cleanText(values.message);
  if (message.length < LIMITS.messageMin)
    errors.message =
      'Tell us a little about your trip (at least 10 characters).';
  else if (message.length > LIMITS.messageMax)
    errors.message = `Keep your message under ${LIMITS.messageMax} characters.`;

  if (!values.consent)
    errors.consent =
      'Tick the box to confirm you are happy to continue in WhatsApp.';
  return errors;
}

/** Builds the prefilled WhatsApp text from validated values. */
export function buildEnquiryMessage(
  values: EnquiryValues,
  source: string,
): string {
  const lines = [
    'Hello Trip Andaman, I would like to enquire about an Andaman trip.',
    '',
    `Name: ${cleanLine(values.fullName)}`,
    `Mobile: ${cleanLine(values.mobile)}`,
    `Travel date: ${values.travelDate.trim() || 'To discuss'}`,
    `Travellers: ${values.travellers.trim() || 'To discuss'}`,
    `Interested in: ${cleanLine(values.interest) || 'To discuss'}`,
    '',
    cleanText(values.message),
    '',
    `(Sent from the ${cleanLine(source)} on tripandaman.in)`,
  ];
  return lines.join('\n');
}

/** wa.me URL for a digits-only international number. */
export function whatsappUrl(number: string, text: string): string {
  const digits = phoneDigits(number);
  if (digits.length < LIMITS.phoneDigitsMin)
    throw new Error('WhatsApp number must be international digits only.');
  return `https://wa.me/${digits}?text=${encodeURIComponent(text)}`;
}
