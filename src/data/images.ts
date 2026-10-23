// Responsive widths available in public/images for each named image.
const generated = [640, 1200, 1536];
const standard = [640, 1200, 1920];
const widthsByImage: Record<string, number[]> = {
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
