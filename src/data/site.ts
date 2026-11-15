/** Canonical public origin. Non-www HTTPS; www redirects here (vercel.json). */
export const SITE_URL = 'https://tripandaman.in';

/**
 * Client WhatsApp/phone number in international digits only (country code 91,
 * no plus sign, spaces or dashes) — the format wa.me requires.
 */
export const WHATSAPP_NUMBER = '919646969646';

/** wa.me chat link to the business number with a pre-filled message. */
export const whatsappLink = (text: string): string =>
  `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(text)}`;

/** Standard enquiry greeting: "Hello Trip Andaman, I would like to enquire about …". */
export const enquiryText = (subject: string): string =>
  `Hello Trip Andaman, I would like to enquire about ${subject}.`;

export const site = {
  name: 'Trip Andaman',
  legalName: 'TRIP ANDAMAN',
  phone: '+91 9646 96 9646',
  tel: `+${WHATSAPP_NUMBER}`,
  address:
    'Haddo, Sri Vijaya Puram, Andaman and Nicobar Islands — 744102, India',
  map: 'https://www.google.com/maps/search/?api=1&query=Haddo%2C+Sri+Vijaya+Puram%2C+Andaman+744102',
  whatsapp: whatsappLink(enquiryText('an Andaman trip')),
};
export const navigation = [
  ['Home', '/'],
  ['About', '/about/'],
  ['Tour Packages', '/tour-packages/'],
  ['Destinations', '/destinations/'],
  ['Experiences', '/experiences/'],
  ['Gallery', '/gallery/'],
  ['Contact', '/contact/'],
];
export const experiences = [
  {
    title: 'Snorkeling & scuba diving',
    icon: 'fish',
    text: 'A different world beneath the blue. Ask about suitable sites, operators and conditions.',
    image: 'ai-snorkel-reef',
    alt: 'A snorkeller floating above a colourful coral reef',
  },
  {
    title: 'Island hopping',
    icon: 'boat',
    text: 'Let the journey between islands become part of the holiday.',
    image: 'client-jetty-group',
    alt: 'Travellers at the jetty in front of an inter-island ship — supplied photograph',
  },
  {
    title: 'Beach relaxation',
    icon: 'sun',
    text: 'Unhurried walks, a sea breeze and time to simply be.',
    image: 'havelock',
    alt: 'Forest meeting the white sand of Radhanagar Beach, Havelock Island',
  },
  {
    title: 'Water activities',
    icon: 'waves',
    text: 'Sea walks, jet skis, parasailing and speed boats at North Bay and Havelock, subject to local availability.',
    image: 'client-speedboat',
    alt: 'A traveller enjoying a speed boat ride between islands — supplied photograph',
  },
  {
    title: 'Sightseeing & heritage',
    icon: 'compass',
    text: 'Walk the corridors of the Cellular Jail, watch its Light & Sound Show and explore the ruins of Ross Island.',
    image: 'cellular-jail-wing',
    alt: 'A wing of the Cellular Jail and its courtyard — supplied photograph',
  },
  {
    title: 'Nature exploration',
    icon: 'leaf',
    text: 'From tropical forests to remarkable natural formations, take a closer look.',
    image: 'baratang',
    alt: 'Limestone cave formations at Baratang',
  },
];
export const planning = [
  {
    title: 'Beach days & slow stays',
    image: 'havelock',
    tag: 'Find your island pace',
    text: 'Make room for sandy shores and unhurried mornings.',
    interest: 'A beach-focused holiday',
  },
  {
    title: 'An island discovery',
    image: 'neil',
    tag: 'A little more to explore',
    text: 'Tell us which islands you would love to experience.',
    interest: 'Island discovery',
  },
  {
    title: 'Beneath the blue',
    image: 'client-scuba',
    tag: 'For your curious side',
    text: 'Ask about marine experiences for your travel dates.',
    interest: 'Water and marine experiences',
  },
  {
    title: 'A trip that feels like you',
    image: 'ai-beach-walk',
    tag: 'Your own Andaman story',
    text: 'Share your dates, interests and the way you like to travel.',
    interest: 'A customized Andaman trip',
  },
];

// Value propositions taken from the client's homepage design reference.
export const promises = [
  {
    icon: 'route',
    title: 'Customised trips',
    text: 'Planned around your dates, pace and interests.',
  },
  {
    icon: 'pin',
    title: 'Locally based',
    text: 'Our team is in Haddo, Sri Vijaya Puram.',
  },
  {
    icon: 'headset',
    title: 'Hassle-free support',
    text: 'From the first call to the final details.',
  },
  {
    icon: 'heart',
    title: 'Memorable experiences',
    text: 'Beaches, reefs, heritage and island nature.',
  },
];
