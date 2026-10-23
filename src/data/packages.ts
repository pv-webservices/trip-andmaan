export interface TourPackage {
  slug: string;
  title: string;
  published: boolean;
  image: string;
  overview: string;
  duration?: string;
  price?: string;
  destinations?: string[];
  itinerary?: { day: string; description: string }[];
  inclusions?: string[];
  exclusions?: string[];
}
// Editorial drafts stay out of public pages until the client confirms the details.
export const packages: TourPackage[] = [
  {
    slug: 'andaman-getaway',
    title: 'Andaman Getaway',
    published: false,
    image: 'havelock',
    overview: '',
  },
  {
    slug: 'island-explorer',
    title: 'Island Explorer',
    published: false,
    image: 'neil',
    overview: '',
  },
  {
    slug: 'andaman-bliss',
    title: 'Andaman Bliss',
    published: false,
    image: 'hero',
    overview: '',
  },
];
