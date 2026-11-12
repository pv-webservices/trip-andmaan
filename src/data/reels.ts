// Short silent clips cut from the client's supplied North Bay water-sport
// videos (Nov 2026). Third-party headers, captions and phone numbers were
// cropped out; audio was removed.
export interface Reel {
  slug: string;
  title: string;
  text: string;
  seconds: number;
}

export const reels: Reel[] = [
  {
    slug: 'sea-walk',
    title: 'Sea walk',
    text: 'Walk on the seabed among the fish, with a helmet supplying air.',
    seconds: 15,
  },
  {
    slug: 'scuba-dive',
    title: 'Scuba diving',
    text: 'A guided boat dive for beginners and certified divers.',
    seconds: 15,
  },
  {
    slug: 'snorkeling',
    title: 'Snorkelling',
    text: 'Float over shallow coral gardens with a mask and life jacket.',
    seconds: 14,
  },
  {
    slug: 'semi-submarine',
    title: 'Semi-submarine',
    text: 'Watch the coral through glass windows and stay completely dry.',
    seconds: 15,
  },
  {
    slug: 'dolphin-ride',
    title: 'Andaman Dolphin ride',
    text: 'An underwater-viewing boat ride over the North Bay reef.',
    seconds: 15,
  },
  {
    slug: 'parasailing',
    title: 'Parasailing',
    text: 'Lift off behind a speed boat for a bird’s-eye view of the bay.',
    seconds: 15,
  },
  {
    slug: 'jet-ski-banana',
    title: 'Jet ski & banana ride',
    text: 'High-speed fun on the water for families and groups.',
    seconds: 16,
  },
  {
    slug: 'kayaking',
    title: 'Kayaking',
    text: 'Paddle calm water at your own pace.',
    seconds: 15,
  },
];

export const reelVideo = (slug: string): string => `/videos/${slug}.mp4`;
export const reelPoster = (slug: string): string =>
  `/images/reel-${slug}-640.webp`;
