// Responsive widths available in public/images for each named image.
const generated = [640, 1200, 1536];
const standard = [640, 1200, 1920];
// Supplied client photographs (Nov 2026), prepared at two widths.
const supplied = [640, 1200];
const suppliedImages = [
  'client-group-beach',
  'client-airport-welcome',
  'client-hotel-welcome',
  'client-jetty-group',
  'client-natural-bridge',
  'client-resort-stay',
  'client-beach-huts',
  'client-reef-shore',
  'client-rocky-cove',
  'client-lagoon',
  'client-beach-shade',
  'client-boat-ride',
  'client-speedboat',
  'cellular-jail-wing',
  'cellular-jail-corridor',
  'cellular-jail-cell',
  'cellular-jail-gate',
  'cellular-jail-view',
  'resort-palms',
  'neil-reef-flats',
  'coastal-rocks',
  'handicraft-elephant',
  'handicraft-butterfly',
];
const widthsByImage: Record<string, number[]> = {
  ...Object.fromEntries(suppliedImages.map((name) => [name, supplied])),
  'ai-island-hopping': generated,
  'ai-snorkel-reef': generated,
  'ai-trip-planning': generated,
  'ai-beach-walk': generated,
  underwater: [640],
  'client-scuba': [640, 848],
};

export const imageWidths = (name: string): number[] =>
  widthsByImage[name] ?? standard;
export const imageSrc = (name: string, width: number): string =>
  `/images/${name}-${width}.webp`;
export const largestImage = (name: string): string => {
  const widths = imageWidths(name);
  return imageSrc(name, widths[widths.length - 1]);
};
