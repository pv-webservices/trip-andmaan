// Tour packages supplied by the client in "ALL ANDAMAN TOUR PACKAGES.docx"
// and "10 nights.pdf" (November 2026). Prices were not supplied, so every
// package is quoted on request. Day descriptions are lightly edited for
// spelling and consistency; routes and sightseeing follow the source files.
import { day, type ItineraryDay } from './itinerary-days';

export interface Stay {
  place: string;
  nights: number;
}
export interface TourPackage {
  slug: string;
  title: string;
  published: boolean;
  nights: number;
  days: number;
  image: string;
  alt: string;
  overview: string;
  stays: Stay[];
  tags: string[];
  itinerary: ItineraryDay[];
  /** Supplied travel photographs that suit this route. */
  moments: string[];
}

const PB = 'Port Blair';
const HL = 'Havelock';
const NL = 'Neil';

export const packages: TourPackage[] = [
  {
    slug: 'andaman-island-escape-4n5d',
    title: 'Andaman Island Escape',
    published: true,
    nights: 4,
    days: 5,
    image: 'havelock',
    alt: 'Forest meeting the white sand of Radhanagar Beach, Havelock Island',
    overview:
      'A compact first taste of the Andamans: the Cellular Jail story, Elephant Beach and Radhanagar on Havelock, the beaches and rock bridge of Neil, and a Chidiyatapu sunset.',
    stays: [
      { place: PB, nights: 2 },
      { place: HL, nights: 1 },
      { place: NL, nights: 1 },
    ],
    tags: ['Havelock', 'Neil', 'Chidiyatapu'],
    itinerary: [
      day.arrivalFull,
      day.havelockElephantRadhanagar,
      day.neilAll,
      day.portBlairChidiyatapu,
      day.departure,
    ],
    moments: [
      'cellular-jail-gate',
      'client-group-beach',
      'client-natural-bridge',
    ],
  },
  {
    slug: 'havelock-neil-getaway-5n6d',
    title: 'Havelock & Neil Getaway',
    published: true,
    nights: 5,
    days: 6,
    image: 'hero',
    alt: 'Driftwood on the white sand of a turquoise lagoon, Havelock Island',
    overview:
      'Two relaxed nights on Havelock with a full morning at Elephant Beach, then Neil Island’s beaches and natural bridge before museums and shopping in Port Blair.',
    stays: [
      { place: PB, nights: 2 },
      { place: HL, nights: 2 },
      { place: NL, nights: 1 },
    ],
    tags: ['Havelock', 'Neil', 'Museums'],
    itinerary: [
      day.arrivalFull,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilAll,
      day.portBlairMuseums,
      day.departure,
    ],
    moments: ['client-lagoon', 'client-reef-shore', 'cellular-jail-corridor'],
  },
  {
    slug: 'andaman-with-baratang-5n6d',
    title: 'Andaman with Baratang',
    published: true,
    nights: 5,
    days: 6,
    image: 'baratang',
    alt: 'Limestone cave formations at Baratang',
    overview:
      'Island beaches plus a day in the wild north: limestone caves reached through mangrove creeks and the mud volcano at Baratang.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 1 },
      { place: NL, nights: 1 },
    ],
    tags: ['Baratang', 'Havelock', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.havelockElephantRadhanagarBySpeedboat,
      day.neilAll,
      day.portBlairMuseums,
      day.baratang,
      day.departure,
    ],
    moments: ['client-boat-ride', 'client-group-beach', 'coastal-rocks'],
  },
  {
    slug: 'andaman-highlights-5n6d',
    title: 'Andaman Highlights',
    published: true,
    nights: 5,
    days: 6,
    image: 'north-bay',
    alt: 'North Bay lighthouse and coastline',
    overview:
      'The greatest hits in six days: Ross Island’s heritage, water activities at North Bay, Radhanagar Beach and a full sightseeing day on Neil.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 1 },
      { place: NL, nights: 1 },
    ],
    tags: ['Ross & North Bay', 'Havelock', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.neilAll,
      day.portBlairMuseums,
      day.departure,
    ],
    moments: [
      'client-speedboat',
      'client-beach-shade',
      'client-natural-bridge',
    ],
  },
  {
    slug: 'andaman-classic-6n7d',
    title: 'Andaman Classic',
    published: true,
    nights: 6,
    days: 7,
    image: 'cellular-jail-wing',
    alt: 'A wing of the Cellular Jail and its courtyard, Sri Vijaya Puram',
    overview:
      'A balanced week with Ross and North Bay, Radhanagar and Kalapathar on Havelock, Neil’s beaches and a full Port Blair day of museums, Chatham Saw Mill and Corbyn’s Cove.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 1 },
      { place: NL, nights: 1 },
    ],
    tags: ['Ross & North Bay', 'Kalapathar', 'City tour'],
    itinerary: [
      day.arrivalLightShow,
      day.rossNorthBaySpeedboat,
      day.havelockRadhanagar,
      day.kalapatharToNeil,
      day.bharatpurToPortBlair,
      day.portBlairMuseumsChatham,
      day.departure,
    ],
    moments: [
      'cellular-jail-corridor',
      'client-rocky-cove',
      'client-reef-shore',
    ],
  },
  {
    slug: 'andaman-explorer-baratang-6n7d',
    title: 'Andaman Explorer with Baratang',
    published: true,
    nights: 6,
    days: 7,
    image: 'ross',
    alt: 'Tree roots growing over historic ruins on Ross Island',
    overview:
      'Heritage, coral and caves: Ross and North Bay, Havelock and Neil, then an early-convoy journey to the limestone caves and mud volcano of Baratang.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 1 },
      { place: NL, nights: 1 },
    ],
    tags: ['Baratang', 'Ross & North Bay', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.neilAll,
      day.portBlairMuseums,
      day.baratang,
      day.departure,
    ],
    moments: ['cellular-jail-gate', 'client-speedboat', 'coastal-rocks'],
  },
  {
    slug: 'havelock-holiday-baratang-6n7d',
    title: 'Havelock Holiday with Baratang',
    published: true,
    nights: 6,
    days: 7,
    image: 'sunset',
    alt: 'Evening light over the sea at Elephant Beach',
    overview:
      'Two nights on Havelock for Radhanagar sunsets and Elephant Beach, Neil Island, an afternoon on Ross Island and a day trip to Baratang.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 2 },
      { place: NL, nights: 1 },
    ],
    tags: ['Baratang', 'Havelock', 'Ross Island'],
    itinerary: [
      day.arrivalFull,
      day.museumsThenHavelock,
      day.elephantBeach,
      day.neilAll,
      day.portBlairRoss,
      day.baratang,
      day.departure,
    ],
    moments: ['client-lagoon', 'client-boat-ride', 'client-natural-bridge'],
  },
  {
    slug: 'coral-and-heritage-6n7d',
    title: 'Coral & Heritage Trail',
    published: true,
    nights: 6,
    days: 7,
    image: 'neil',
    alt: 'Sandy shore and forest on Shaheed Dweep (Neil Island)',
    overview:
      'Freedom-struggle history at the Cellular Jail and Ross Island, then reefs and beaches at North Bay, Elephant Beach, Radhanagar and Neil.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 2 },
      { place: NL, nights: 1 },
    ],
    tags: ['Ross & North Bay', 'Havelock', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilAll,
      day.portBlairMuseums,
      day.departure,
    ],
    moments: ['cellular-jail-cell', 'client-speedboat', 'client-reef-shore'],
  },
  {
    slug: 'island-hopper-6n7d',
    title: 'Island Hopper',
    published: true,
    nights: 6,
    days: 7,
    image: 'bridge',
    alt: 'Rocky coastline and natural arch on Shaheed Dweep (Neil Island)',
    overview:
      'Two nights on each island: time to settle into Havelock’s beaches, then two slow days on Neil with a whole morning for Bharatpur’s coral.',
    stays: [
      { place: PB, nights: 2 },
      { place: HL, nights: 2 },
      { place: NL, nights: 2 },
    ],
    tags: ['Havelock', 'Neil', 'Slow travel'],
    itinerary: [
      day.arrivalFull,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilLaxmanpurBridge,
      day.bharatpur,
      day.portBlairFisheries,
      day.departure,
    ],
    moments: ['client-jetty-group', 'neil-reef-flats', 'client-rocky-cove'],
  },
  {
    slug: 'havelock-long-stay-7n8d',
    title: 'Havelock Long Stay',
    published: true,
    nights: 7,
    days: 8,
    image: 'hero',
    alt: 'Driftwood on the white sand of a turquoise lagoon, Havelock Island',
    overview:
      'Three nights on Havelock for Radhanagar, Elephant Beach and a Kalapathar sunset, with Ross, North Bay and Neil Island along the way.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 3 },
      { place: NL, nights: 1 },
    ],
    tags: ['Havelock', 'Kalapathar', 'Ross & North Bay'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.kalapathar,
      day.neilAll,
      day.portBlairFisheries,
      day.departure,
    ],
    moments: ['client-beach-shade', 'resort-palms', 'client-natural-bridge'],
  },
  {
    slug: 'three-islands-unhurried-7n8d',
    title: 'Three Islands, Unhurried',
    published: true,
    nights: 7,
    days: 8,
    image: 'havelock',
    alt: 'Forest meeting the white sand of Radhanagar Beach, Havelock Island',
    overview:
      'Two nights each on Havelock and Neil, with Ross and North Bay at the start, for a week that never feels rushed.',
    stays: [
      { place: PB, nights: 3 },
      { place: HL, nights: 2 },
      { place: NL, nights: 2 },
    ],
    tags: ['Ross & North Bay', 'Havelock', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilLaxmanpurBridge,
      day.bharatpur,
      day.portBlairMuseums,
      day.departure,
    ],
    moments: ['client-lagoon', 'neil-reef-flats', 'client-hotel-welcome'],
  },
  {
    slug: 'andaman-complete-7n8d',
    title: 'Andaman Complete',
    published: true,
    nights: 7,
    days: 8,
    image: 'cellular-jail-corridor',
    alt: 'A sunlit corridor of cells inside the Cellular Jail',
    overview:
      'Everything in and around Port Blair, including a full city tour, plus Ross, North Bay, two nights on Havelock and a night on Neil.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 2 },
      { place: NL, nights: 1 },
    ],
    tags: ['City tour', 'Ross & North Bay', 'Havelock'],
    itinerary: [
      day.arrivalJailOnly,
      day.rossNorthBay,
      day.portBlairCityTour,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilAll,
      day.portBlairShopping,
      day.departure,
    ],
    moments: ['cellular-jail-view', 'handicraft-elephant', 'client-reef-shore'],
  },
  {
    slug: 'andaman-discovery-baratang-8n9d',
    title: 'Andaman Discovery with Baratang',
    published: true,
    nights: 8,
    days: 9,
    image: 'neil',
    alt: 'Sandy shore and forest on Shaheed Dweep (Neil Island)',
    overview:
      'Nine days for the full circuit: Ross and North Bay, two nights each on Havelock and Neil, the museums, and Baratang’s caves and mud volcano.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 2 },
      { place: NL, nights: 2 },
    ],
    tags: ['Baratang', 'Ross & North Bay', 'Neil'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.neilLaxmanpurBridge,
      day.bharatpur,
      day.portBlairMuseums,
      day.baratang,
      day.departure,
    ],
    moments: ['client-hotel-welcome', 'client-boat-ride', 'neil-reef-flats'],
  },
  {
    slug: 'grand-andaman-baratang-9n10d',
    title: 'Grand Andaman with Baratang',
    published: true,
    nights: 9,
    days: 10,
    image: 'sunset',
    alt: 'Evening light over the sea at Elephant Beach',
    overview:
      'Our most relaxed island circuit: three nights on Havelock, two on Neil, Ross and North Bay, the museums and a Baratang day trip.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 3 },
      { place: NL, nights: 2 },
    ],
    tags: ['Baratang', 'Kalapathar', 'Slow travel'],
    itinerary: [
      day.arrivalFull,
      day.rossNorthBay,
      day.havelockRadhanagar,
      day.elephantBeach,
      day.kalapathar,
      day.neilLaxmanpurBridge,
      day.bharatpur,
      day.portBlairMuseums,
      day.baratang,
      day.departure,
    ],
    moments: ['client-group-beach', 'client-rocky-cove', 'resort-palms'],
  },
  {
    slug: 'north-to-south-andaman-10n11d',
    title: 'North to South Andaman',
    published: true,
    nights: 10,
    days: 11,
    image: 'baratang',
    alt: 'Limestone cave formations at Baratang',
    overview:
      'The complete Andaman road and sea journey: Havelock and Neil, then north through Baratang to Mayabunder, Diglipur’s Ross & Smith twin islands and the beaches of Rangat.',
    stays: [
      { place: PB, nights: 4 },
      { place: HL, nights: 2 },
      { place: NL, nights: 1 },
      { place: 'Mayabunder', nights: 1 },
      { place: 'Diglipur', nights: 1 },
      { place: 'Rangat', nights: 1 },
    ],
    tags: ['Diglipur', 'Baratang', 'Ross & Smith'],
    itinerary: [
      day.grandArrival,
      day.grandHavelock,
      day.grandElephantKalapathar,
      day.grandNeil,
      day.grandNeilToPortBlair,
      day.grandBaratangMayabunder,
      day.grandDiglipur,
      day.grandRangat,
      day.grandRangatToPortBlair,
      day.rossNorthBayFerry,
      day.departure,
    ],
    moments: ['client-jetty-group', 'coastal-rocks', 'client-speedboat'],
  },
];

export const publishedPackages = packages.filter((p) => p.published);

export const durationLabel = (p: TourPackage) =>
  `${p.nights} Nights / ${p.days} Days`;

export const routeLabel = (p: TourPackage) =>
  p.stays.map((s) => `${s.nights}N ${s.place}`).join(' · ');

export const packageGroups = [
  {
    id: 'short',
    eyebrow: '4–5 nights',
    title: 'Short island escapes',
    match: (p: TourPackage) => p.nights <= 5,
  },
  {
    id: 'week',
    eyebrow: '6 nights',
    title: 'A week in the islands',
    match: (p: TourPackage) => p.nights === 6,
  },
  {
    id: 'long',
    eyebrow: '7–10 nights',
    title: 'Longer journeys',
    match: (p: TourPackage) => p.nights >= 7,
  },
];
