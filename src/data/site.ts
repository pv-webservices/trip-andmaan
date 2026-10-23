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
    image: 'ai-island-hopping',
    alt: 'View from the bow of a boat crossing turquoise water towards forested islands',
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
    text: 'Explore options to spend time on the water, subject to local availability.',
    image: 'client-kayak',
    alt: 'Two travellers kayaking on calm water — supplied travel photograph',
  },
  {
    title: 'Sightseeing & heritage',
    icon: 'compass',
    text: 'Discover the islands’ stories, historic places and changing landscapes.',
    image: 'ross',
    alt: 'Tree roots growing over historic ruins on Ross Island',
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
