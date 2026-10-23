document.querySelectorAll<HTMLDialogElement>('dialog').forEach((dialog) => {
  dialog.addEventListener('keydown', (event) => {
    if (event.key !== 'Tab') return;
    const controls = Array.from(
      dialog.querySelectorAll<HTMLElement>(
        'a[href], button:not(:disabled), input, select, textarea, video[controls], [tabindex="0"]',
      ),
    ).filter((element) => element.getClientRects().length > 0);
    const first = controls[0];
    const last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first?.focus();
    }
  });
});
const menu = document.querySelector<HTMLDialogElement>('#mobile-menu');
const toggle = document.querySelector<HTMLButtonElement>('.menu-toggle');
if (menu && toggle) {
  toggle.addEventListener('click', () => {
    menu.showModal();
    toggle.setAttribute('aria-expanded', 'true');
  });
  menu
    .querySelector('.menu-close')
    ?.addEventListener('click', () => menu.close());
  menu.addEventListener('close', () => {
    toggle.setAttribute('aria-expanded', 'false');
    toggle.focus();
  });
  menu.addEventListener('click', (event) => {
    if (
      event.target === menu &&
      (event as MouseEvent).clientX < menu.getBoundingClientRect().left
    )
      menu.close();
  });
  const desktop = matchMedia('(min-width: 951px)');
  desktop.addEventListener('change', () => {
    if (desktop.matches && menu.open) menu.close();
  });
}
const header = document.querySelector('.site-header');
let scheduled = false;
const onScroll = () => {
  header?.classList.toggle('scrolled', scrollY > 12);
  scheduled = false;
};
onScroll();
window.addEventListener(
  'scroll',
  () => {
    if (!scheduled) requestAnimationFrame(onScroll);
    scheduled = true;
  },
  { passive: true },
);

const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealTargets = document.querySelectorAll<HTMLElement>('[data-reveal]');
if ('IntersectionObserver' in window && !reduceMotion) {
  const revealer = new IntersectionObserver(
    (entries) =>
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        revealer.unobserve(entry.target);
      }),
    { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
  );
  revealTargets.forEach((el) => revealer.observe(el));
} else {
  revealTargets.forEach((el) => el.classList.add('is-visible'));
}

// Ambient videos load and play only while visible, and never with reduced motion.
const ambient = document.querySelectorAll<HTMLVideoElement>(
  'video[data-autoplay]',
);
if ('IntersectionObserver' in window && !reduceMotion) {
  const player = new IntersectionObserver(
    (entries) =>
      entries.forEach((entry) => {
        const video = entry.target as HTMLVideoElement;
        if (entry.isIntersecting) video.play().catch(() => undefined);
        else video.pause();
      }),
    { threshold: 0.25 },
  );
  ambient.forEach((video) => player.observe(video));
}

// Experience showcase tabs (click, tap, hover on desktop, arrow keys).
const tabs = Array.from(
  document.querySelectorAll<HTMLButtonElement>('.xp-tab'),
);
const selectTab = (index: number, focus = false) => {
  tabs.forEach((tab, i) => {
    const active = i === index;
    tab.setAttribute('aria-selected', String(active));
    tab.tabIndex = active ? 0 : -1;
    const panel = document.getElementById(
      tab.getAttribute('aria-controls') || '',
    );
    panel?.classList.toggle('is-active', active);
    panel?.toggleAttribute('inert', !active);
    if (active) panel?.removeAttribute('aria-hidden');
    else panel?.setAttribute('aria-hidden', 'true');
  });
  if (focus) tabs[index].focus();
  const strip = tabs[index].parentElement;
  if (strip && strip.scrollWidth > strip.clientWidth)
    strip.scrollTo({
      left: tabs[index].offsetLeft - strip.offsetLeft - 16,
      behavior: reduceMotion ? 'auto' : 'smooth',
    });
};
const canHover = matchMedia('(hover: hover) and (min-width: 1025px)');
tabs.forEach((tab, i) => {
  tab.addEventListener('click', () => selectTab(i));
  tab.addEventListener('mouseenter', () => {
    if (canHover.matches) selectTab(i);
  });
  tab.addEventListener('keydown', (event) => {
    const keys: Record<string, number> = {
      ArrowDown: 1,
      ArrowRight: 1,
      ArrowUp: -1,
      ArrowLeft: -1,
    };
    if (event.key in keys) {
      event.preventDefault();
      selectTab((i + keys[event.key] + tabs.length) % tabs.length, true);
    }
  });
});

const galleryLinks = Array.from(
  document.querySelectorAll<HTMLAnchorElement>('[data-gallery]'),
);
const lightbox = document.querySelector<HTMLDialogElement>('.lightbox');
let visibleLinks = galleryLinks;
let current = 0;
let galleryOpener: HTMLElement | null = null;
const showPhoto = (index: number) => {
  if (!lightbox || !visibleLinks.length) return;
  current = (index + visibleLinks.length) % visibleLinks.length;
  const link = visibleLinks[current];
  const image = lightbox.querySelector('img')!;
  image.src = link.href;
  image.alt = link.dataset.caption || '';
  lightbox.querySelector('.lightbox-caption')!.textContent = image.alt;
  lightbox.querySelector('.lightbox-counter')!.textContent =
    `${current + 1} / ${visibleLinks.length}`;
};
galleryLinks.forEach((link) =>
  link.addEventListener('click', (event) => {
    if (!lightbox) return;
    event.preventDefault();
    galleryOpener = link;
    visibleLinks = galleryLinks.filter(
      (item) => !item.closest<HTMLElement>('figure')?.hidden,
    );
    showPhoto(visibleLinks.indexOf(link));
    lightbox.showModal();
  }),
);
if (lightbox) {
  lightbox
    .querySelector('[data-gallery-close]')
    ?.addEventListener('click', () => lightbox.close());
  lightbox
    .querySelectorAll('[data-gallery-prev]')
    .forEach((b) => b.addEventListener('click', () => showPhoto(current - 1)));
  lightbox
    .querySelectorAll('[data-gallery-next]')
    .forEach((b) => b.addEventListener('click', () => showPhoto(current + 1)));
  lightbox.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      showPhoto(current - 1);
    }
    if (event.key === 'ArrowRight') {
      event.preventDefault();
      showPhoto(current + 1);
    }
  });
  lightbox.addEventListener('close', () => galleryOpener?.focus());
}
document
  .querySelectorAll<HTMLButtonElement>('[data-filter]')
  .forEach((button) =>
    button.addEventListener('click', () => {
      document.querySelectorAll('[data-filter]').forEach((item) => {
        item.classList.toggle('is-active', item === button);
        item.setAttribute('aria-pressed', String(item === button));
      });
      document
        .querySelectorAll<HTMLElement>('[data-category]')
        .forEach((item) => {
          item.hidden =
            button.dataset.filter !== 'All' &&
            item.dataset.category !== button.dataset.filter;
        });
    }),
  );

const film = document.querySelector<HTMLDialogElement>('.film-dialog');
if (film) {
  document.querySelectorAll('[data-open-film]').forEach((trigger) =>
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      film.showModal();
    }),
  );
  film
    .querySelector('[data-close-film]')
    ?.addEventListener('click', () => film.close());
  film.addEventListener('close', () => film.querySelector('video')?.pause());
}

const form = document.querySelector<HTMLFormElement>('.enquiry-form');
if (form) {
  form.addEventListener('input', () => {
    const draft = form.querySelector<HTMLAnchorElement>('[data-draft-link]');
    if (draft) draft.hidden = true;
    const status = form.querySelector<HTMLElement>('.form-status');
    if (status) status.textContent = '';
  });
  const interest = new URLSearchParams(location.search).get('interest');
  const interestField =
    form.querySelector<HTMLInputElement>('[name="interest"]');
  if (interest && interestField) interestField.value = interest.slice(0, 180);
  const date = form.querySelector<HTMLInputElement>('[name="travelDate"]');
  if (date) {
    const now = new Date();
    date.min = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
  }
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const fields = new FormData(form);
    if (fields.get('website')) return;
    const status = form.querySelector<HTMLElement>('.form-status')!;
    const button = form.querySelector<HTMLButtonElement>('[type="submit"]')!;
    const endpoint = form.dataset.endpoint;
    const values = Object.fromEntries(fields);
    if (!endpoint) {
      const message = `Andaman travel enquiry\n\nName: ${values.fullName}\nMobile: ${values.mobile}\nEmail: ${values.email}\nTravel date: ${values.travelDate || 'To discuss'}\nTravellers: ${values.travellers || 'To discuss'}\nInterested in: ${values.interest || 'To discuss'}\n\n${values.message}`;
      const link = form.querySelector<HTMLAnchorElement>('[data-draft-link]')!;
      link.href = `mailto:${form.dataset.email}?subject=${encodeURIComponent('Andaman travel enquiry')}&body=${encodeURIComponent(message)}`;
      link.hidden = false;
      status.textContent =
        'Your email draft is ready. Open it below, review it and send it from your email app. Nothing has been sent yet.';
      link.focus();
      return;
    }
    button.disabled = true;
    button.textContent = 'Sending enquiry…';
    status.textContent = '';
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        body: fields,
        headers: { Accept: 'application/json' },
        signal: AbortSignal.timeout(15000),
      });
      if (!response.ok) throw new Error('Submission failed');
      status.textContent =
        'The enquiry service accepted your message. You can also reach us by phone or email.';
      form.reset();
    } catch {
      status.textContent =
        'We could not confirm submission. Please try again, call us or use the email link. Your details are still here.';
    } finally {
      button.disabled = false;
      button.textContent = 'Send Enquiry';
    }
  });
}

// WhatsApp widget: the floating link opens a chat card instead of leaving the page.
const waFab = document.querySelector<HTMLAnchorElement>('.wa-fab');
const waPanel = document.querySelector<HTMLElement>('#wa-panel');
if (waFab && waPanel) {
  const setPanel = (open: boolean) => {
    waPanel.hidden = !open;
    waFab.setAttribute('aria-expanded', String(open));
    if (open) waPanel.querySelector<HTMLElement>('.wa-start')?.focus();
  };
  waFab.addEventListener('click', (event) => {
    event.preventDefault();
    setPanel(waPanel.hidden);
  });
  waPanel.querySelector('.wa-close')?.addEventListener('click', () => {
    setPanel(false);
    waFab.focus();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !waPanel.hidden) {
      setPanel(false);
      waFab.focus();
    }
  });
  document.addEventListener('click', (event) => {
    const target = event.target as Node;
    if (!waPanel.hidden && !waPanel.contains(target) && !waFab.contains(target))
      setPanel(false);
  });
}
