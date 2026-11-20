// WhatsApp enquiry composer: validates, shows accessible errors and opens a
// prefilled wa.me chat. Nothing is posted, stored or logged.
import {
  buildEnquiryMessage,
  maxTravelDateIso,
  todayIso,
  validateEnquiry,
  whatsappUrl,
  type EnquiryErrors,
  type EnquiryField,
  type EnquiryValues,
} from './lib/enquiry';

// Form field name -> element id (ids carry the error message ids).
const FIELD_IDS: Record<EnquiryField, string> = {
  fullName: 'full-name',
  mobile: 'mobile',
  travelDate: 'travel-date',
  travellers: 'travellers',
  interest: 'interest',
  message: 'message',
  consent: 'consent',
};
const FIELD_ORDER = Object.keys(FIELD_IDS) as EnquiryField[];

const readValues = (form: HTMLFormElement): EnquiryValues => {
  const text = (name: string) =>
    form.querySelector<HTMLInputElement | HTMLTextAreaElement>(
      `[name="${name}"]`,
    )?.value ?? '';
  return {
    fullName: text('fullName'),
    mobile: text('mobile'),
    travelDate: text('travelDate'),
    travellers: text('travellers'),
    interest: text('interest'),
    message: text('message'),
    consent: Boolean(
      form.querySelector<HTMLInputElement>('[name="consent"]')?.checked,
    ),
  };
};

const showErrors = (form: HTMLFormElement, errors: EnquiryErrors): void => {
  for (const field of FIELD_ORDER) {
    const id = FIELD_IDS[field];
    const input = form.querySelector<HTMLElement>(`#${id}`);
    const message = form.querySelector<HTMLElement>(`#${id}-error`);
    if (!input || !message) continue;
    const error = errors[field];
    message.textContent = error ?? '';
    message.hidden = !error;
    if (error) {
      input.setAttribute('aria-invalid', 'true');
      input.setAttribute('aria-describedby', message.id);
    } else {
      input.removeAttribute('aria-invalid');
      input.removeAttribute('aria-describedby');
    }
  }
};

const showSummary = (form: HTMLFormElement, errors: EnquiryErrors): void => {
  const summary = form.querySelector<HTMLElement>('.form-summary');
  if (!summary) return;
  const count = Object.keys(errors).length;
  summary.hidden = count === 0;
  summary.textContent =
    count === 0
      ? ''
      : `Please check ${count === 1 ? '1 field' : `${count} fields`} below. Your details are still here.`;
};

const setResult = (
  form: HTMLFormElement,
  text: string,
  url: string | null,
): void => {
  const status = form.querySelector<HTMLElement>('.form-status');
  const actions = form.querySelector<HTMLElement>('.composer-actions');
  const link = form.querySelector<HTMLAnchorElement>('[data-composer-link]');
  if (status) status.textContent = text;
  if (actions) actions.hidden = url === null;
  if (link && url) link.href = url;
};

export function initEnquiryComposer(form: HTMLFormElement): void {
  const date = form.querySelector<HTMLInputElement>('[name="travelDate"]');
  if (date) {
    date.min = todayIso();
    date.max = maxTravelDateIso();
  }
  const interest = new URLSearchParams(location.search).get('interest');
  const interestField =
    form.querySelector<HTMLInputElement>('[name="interest"]');
  if (interest && interestField) interestField.value = interest.slice(0, 180);

  // Re-validate a field once it has been flagged, so errors clear as it is fixed.
  form.addEventListener('input', (event) => {
    const target = event.target as HTMLElement;
    if (target.getAttribute('aria-invalid') !== 'true') return;
    const errors = validateEnquiry(readValues(form));
    const field = FIELD_ORDER.find((f) => FIELD_IDS[f] === target.id);
    if (field && !errors[field])
      showErrors(form, { ...errors, [field]: undefined });
  });

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const values = readValues(form);
    const errors = validateEnquiry(values);
    showErrors(form, errors);
    showSummary(form, errors);
    const first = FIELD_ORDER.find((f) => errors[f]);
    if (first) {
      setResult(form, '', null);
      form.querySelector<HTMLElement>(`#${FIELD_IDS[first]}`)?.focus();
      return;
    }
    const url = whatsappUrl(
      form.dataset.whatsapp ?? '',
      buildEnquiryMessage(values, form.dataset.source ?? 'website'),
    );
    // Opened directly from the submit (a user action). Without "noopener" in
    // the features we can tell whether the browser blocked the new tab.
    const opened = window.open(url, '_blank');
    if (opened) {
      opened.opener = null;
      setResult(
        form,
        'WhatsApp should now be open with your message ready. It is not sent until you press Send in WhatsApp. If it did not open, use the button below or call us.',
        url,
      );
    } else {
      setResult(
        form,
        'Your browser did not open WhatsApp. Use “Open WhatsApp again” below, or call us. Your details are still in the form.',
        url,
      );
    }
  });
}
