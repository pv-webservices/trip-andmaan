export const site = {
  name: 'TRIP ANDAMAN',
  phone: '9646 96 9646',
  tel: '+919646969646',
  email: 'tripandamanin2026@gmail.com',
  address:
    'Haddo, Sri Vijaya Puram, Andaman and Nicobar Islands — 744102, India',
  map: 'https://www.google.com/maps/search/?api=1&query=Haddo%2C+Sri+Vijaya+Puram%2C+Andaman+744102',
  // WhatsApp uses the business phone number; confirm it is active on WhatsApp.
  whatsapp: `https://wa.me/919646969646?text=${encodeURIComponent(
    'Hi Trip Andaman, I would like to plan an Andaman trip.',
  )}`,
};
export const navigation = [
  ['Home', '/'],
  ['About', '/about/'],
  ['Tours & Packages', '/tour-packages/'],
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
