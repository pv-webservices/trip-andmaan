// CMS behaviours: scrolling announcement bar, popup banner and preview links.
// Nothing here sends data anywhere; popup dismissals are remembered only in
// this browser's storage, keyed by campaign so a new campaign is never hidden
// by an older dismissal.

type Frequency = 'session' | 'day' | 'week' | 'once';

const reduceMotion = (): boolean =>
  matchMedia('(prefers-reduced-motion: reduce)').matches;

export function initAnnouncementBar(bar: HTMLElement): void {
  const track = bar.querySelector<HTMLElement>('.announce-track');
  const viewport = bar.querySelector<HTMLElement>('.announce-viewport');
  const toggle = bar.querySelector<HTMLButtonElement>('.announce-toggle');
  const header = document.querySelector<HTMLElement>('.site-header');
  document.body.classList.add('has-announce');

  const updateOffset = (): void => {
    const height = bar.offsetHeight;
    header?.style.setProperty(
      '--announce-offset',
      `${Math.max(0, height - window.scrollY)}px`,
    );
  };
  updateOffset();
  window.addEventListener('scroll', updateOffset, { passive: true });
  window.addEventListener('resize', updateOffset);

  if (!track || !viewport) return;
  const originals = Array.from(track.children).filter(
    (item) => !item.hasAttribute('data-announce-copy'),
  );
  const contentWidth = originals.reduce(
    (sum, item) => sum + (item as HTMLElement).offsetWidth,
    0,
  );
  // Short content or reduced motion: show it still, wrapping if needed.
  if (reduceMotion() || contentWidth <= viewport.clientWidth) {
    bar.classList.add('is-static');
    updateOffset();
    return;
  }
  const speed = Math.min(120, Math.max(20, Number(bar.dataset.speed) || 50));
  track.style.setProperty('--announce-duration', `${contentWidth / speed}s`);

  toggle?.addEventListener('click', () => {
    const paused = bar.classList.toggle('is-paused');
    toggle.setAttribute('aria-pressed', String(paused));
    toggle.setAttribute(
      'aria-label',
      paused ? 'Play announcements' : 'Pause announcements',
    );
    toggle.innerHTML = paused ? playIcon : pauseIcon;
  });
}

const pauseIcon =
  '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>';
const playIcon =
  '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m8 5 11 7-11 7z"/></svg>';

const store = (frequency: Frequency): Storage | null => {
  try {
    return frequency === 'session' ? sessionStorage : localStorage;
  } catch {
    return null; // storage blocked: behave as a first visit
  }
};

const PERIOD: Record<Frequency, number> = {
  session: Infinity,
  once: Infinity,
  day: 24 * 60 * 60 * 1000,
  week: 7 * 24 * 60 * 60 * 1000,
};

export function wasRecentlyShown(
  key: string,
  frequency: Frequency,
  now: number = Date.now(),
): boolean {
  const value = store(frequency)?.getItem(key);
  if (!value) return false;
  return now - Number(value) < PERIOD[frequency];
}

function remember(key: string, frequency: Frequency): void {
  try {
    store(frequency)?.setItem(key, String(Date.now()));
  } catch {
    /* storage full or blocked: the popup may show again */
  }
}

export function initPopups(): void {
  const candidates = Array.from(
    document.querySelectorAll<HTMLDialogElement>('dialog[data-popup]'),
  );
  const popup = candidates.find(
    (dialog) =>
      !wasRecentlyShown(
        dialog.dataset.popupKey ?? '',
        (dialog.dataset.frequency as Frequency) ?? 'session',
      ),
  );
  if (!popup || typeof popup.showModal !== 'function') return;

  const key = popup.dataset.popupKey ?? '';
  const frequency = (popup.dataset.frequency as Frequency) ?? 'session';
  const delay = Math.min(120, Math.max(0, Number(popup.dataset.delay) || 0));
  let opener: HTMLElement | null = null;

  const open = (): void => {
    // Never interrupt another open dialog (menu, gallery, film).
    if (document.querySelector('dialog[open]')) {
      window.setTimeout(open, 4000);
      return;
    }
    opener = document.activeElement as HTMLElement | null;
    popup.showModal();
    popup.querySelector<HTMLElement>('.promo-close')?.focus();
    remember(key, frequency);
  };
  popup
    .querySelector('.promo-close')
    ?.addEventListener('click', () => popup.close());
  popup.addEventListener('click', (event) => {
    if (event.target === popup) popup.close(); // backdrop click
  });
  popup.addEventListener('close', () => opener?.focus?.());
  window.setTimeout(open, delay * 1000);
}

/** In preview, keep navigation inside /preview so drafts stay visible. */
export function initPreviewLinks(): void {
  document.addEventListener('click', (event) => {
    if (event.defaultPrevented) return;
    const link = (event.target as Element | null)?.closest?.('a');
    if (!link || link.target === '_blank') return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin) return;
    if (/^\/(preview|admin|storage|build|livewire)(\/|$)/.test(url.pathname))
      return;
    event.preventDefault();
    location.href = `/preview${url.pathname}${url.search}${url.hash}`;
  });
}
