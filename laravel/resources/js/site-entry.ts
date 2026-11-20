// Public site entry: the original Astro browser script plus CMS behaviours.
import './site';
import { initAnnouncementBar, initPopups, initPreviewLinks } from './cms';

const bar = document.querySelector<HTMLElement>('.announce');
if (bar) initAnnouncementBar(bar);
initPopups();
if (document.documentElement.dataset.preview === 'true') initPreviewLinks();
