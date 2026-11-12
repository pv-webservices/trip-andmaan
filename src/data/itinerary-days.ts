// Day-by-day building blocks shared by the supplied tour packages.
// Text follows the client's itinerary documents, lightly edited for clarity.
export interface ItineraryDay {
  title: string;
  description: string[];
}

const RADHANAGAR =
  'Spend a few hours at Radhanagar Beach, known as one of the best beaches in Asia. Its turquoise water, white sand and calm surroundings make it perfect for relaxing, and the sunset views here become some of the best memories of an Andaman trip.';
const ELEPHANT =
  'Elephant Beach is one of the most beautiful places on Havelock Island, famous for clear water, live corals and colourful fish. The reef begins only a metre or two from the shore. The beach is open in the first half of the day, so a morning visit gives you the most time there.';
const ELEPHANT_ACTIVITIES =
  'Water activities such as glass-bottom boat rides, snorkelling, banana rides and sofa rides are available (at your own cost).';
const LAXMANPUR =
  'Laxmanpur Beach is a wide, white-sand beach full of shells, ideal for an evening walk.';
const NATURAL_BRIDGE =
  'The Natural Bridge (also called Howrah Bridge) is a rock formation reached by walking along the rocky shore, with the open sea on one side and green forest on the other.';
const BHARATPUR =
  'Bharatpur Beach is perfect for swimming and lazing around, with a living coral reef close to shore. Snorkelling and scuba diving are available here, and a glass-bottom boat ride shows the marine life to those who prefer to stay dry.';
const ROSS =
  'Take a boat to Ross Island (Netaji Subhas Chandra Bose Island), where the ruins of a water distillation plant, swimming pool, Japanese bunkers, church and bakery show how the British settlement once lived.';
const NORTH_BAY =
  'Continue by ferry to North Bay Coral Island, famous for water activities such as jet ski rides, scuba diving, the semi-submarine coral safari, sea walking, the Andaman Dolphin ride and parasailing (at your own cost).';
const BARATANG =
  'Baratang Island is around 100 km from Port Blair through tribal reserve forest. Vehicles pass the Jarawa reserve in a convoy, and the first convoy leaves the Jirkatang check post at 6 am, so we start early to allow enough time for the caves. A boat ride through mangrove creeks leads to the limestone caves, and a short jeep ride takes you to the mud volcano.';
const KALAPATHAR =
  'Kalapathar Beach sits on one corner of Havelock Island and takes its name from the village and the black rocks (kala pathar) along its shore. It is a peaceful place with lush forest behind the sand and a beautiful sunset.';
const MUSEUMS =
  'Visit the Anthropological Museum and the Samudrika Naval Marine Museum. The evening is free for shopping.';
const CELLULAR_JAIL =
  'After lunch, visit the Cellular Jail, which brings back the years of India’s freedom struggle, and Corbyn’s Cove, a coconut palm-fringed beach where you can relax or try water sports (at your own cost). In the evening, watch the Light & Sound Show at the Cellular Jail.';
const ARRIVE =
  'Arrive at Port Blair (Sri Vijaya Puram), the capital of the Andaman and Nicobar Islands. Our representative will meet you outside the airport and transfer you to your hotel.';

export const day = {
  arrivalFull: {
    title:
      'Arrive in Port Blair · Cellular Jail, Corbyn’s Cove & Light and Sound Show',
    description: [ARRIVE, CELLULAR_JAIL, 'Overnight stay in Port Blair.'],
  },
  arrivalLightShow: {
    title: 'Arrive in Port Blair · Cellular Jail & Light and Sound Show',
    description: [
      ARRIVE,
      'In the evening, visit the Cellular Jail and watch the Light & Sound Show, which tells the story of India’s freedom struggle.',
      'Overnight stay in Port Blair.',
    ],
  },
  arrivalJailOnly: {
    title: 'Arrive in Port Blair · Cellular Jail & Light and Sound Show',
    description: [
      ARRIVE,
      'After lunch, visit the Cellular Jail. In the evening, watch the Light & Sound Show there.',
      'Overnight stay in Port Blair.',
    ],
  },
  havelockElephantRadhanagar: {
    title:
      'Cruise to Havelock (Swaraj Dweep) · Elephant Beach & Radhanagar Beach',
    description: [
      'Collect a packed breakfast, check out and take the cruise to Havelock Island. Our representative will receive you and take you to Elephant Beach.',
      ELEPHANT,
      ELEPHANT_ACTIVITIES,
      `After lunch, visit Radhanagar. ${RADHANAGAR}`,
      'Overnight stay in Havelock.',
    ],
  },
  havelockElephantRadhanagarBySpeedboat: {
    title:
      'Cruise to Havelock (Swaraj Dweep) · Elephant Beach & Radhanagar Beach',
    description: [
      'Take the cruise to Havelock Island, then a speed boat straight to Elephant Beach. Your luggage is handled by our tour coordinator.',
      ELEPHANT,
      ELEPHANT_ACTIVITIES,
      `Return to the jetty by speed boat and check in. ${RADHANAGAR}`,
      'Overnight stay in Havelock.',
    ],
  },
  havelockRadhanagar: {
    title: 'Cruise to Havelock (Swaraj Dweep) · Radhanagar Beach',
    description: [
      'After breakfast, take the cruise to Havelock Island. Our representative will receive you and transfer you to your hotel.',
      RADHANAGAR,
      'Overnight stay in Havelock.',
    ],
  },
  museumsThenHavelock: {
    title: 'Museums, then cruise to Havelock · Radhanagar Beach',
    description: [
      'Check out and visit the Anthropological Museum and Samudrika Naval Marine Museum, then take the cruise to Havelock Island.',
      `After lunch, visit Radhanagar. ${RADHANAGAR}`,
      'Overnight stay in Havelock.',
    ],
  },
  elephantBeach: {
    title: 'Elephant Beach by speed boat',
    description: [
      ELEPHANT,
      ELEPHANT_ACTIVITIES,
      'Return to the hotel. Overnight stay in Havelock.',
    ],
  },
  kalapathar: {
    title: 'Kalapathar Beach, Havelock',
    description: [
      `After lunch, head to Kalapathar Beach for the sunset. ${KALAPATHAR}`,
      'Overnight stay in Havelock.',
    ],
  },
  kalapatharToNeil: {
    title: 'Kalapathar Beach, then cruise to Neil (Shaheed Dweep)',
    description: [
      `After breakfast and check-out, visit Kalapathar Beach. ${KALAPATHAR}`,
      `After lunch, take the cruise to Neil Island. ${LAXMANPUR} ${NATURAL_BRIDGE}`,
      'Overnight stay on Neil Island.',
    ],
  },
  neilAll: {
    title:
      'Cruise to Neil (Shaheed Dweep) · Laxmanpur, Natural Bridge & Bharatpur',
    description: [
      'After breakfast, take the cruise to Neil Island. Our representative will take you to your hotel, then out to the island’s best-known spots.',
      LAXMANPUR,
      NATURAL_BRIDGE,
      BHARATPUR,
      'Overnight stay on Neil Island.',
    ],
  },
  neilLaxmanpurBridge: {
    title: 'Cruise to Neil (Shaheed Dweep) · Laxmanpur Beach & Natural Bridge',
    description: [
      'After breakfast, take the cruise to Neil Island and check in.',
      LAXMANPUR,
      NATURAL_BRIDGE,
      'Overnight stay on Neil Island.',
    ],
  },
  bharatpur: {
    title: 'Bharatpur Beach, Neil Island',
    description: [
      'Bharatpur Beach is known for one of the most beautiful coral reefs in the Andamans: a pristine beach shaded by trees, with clear turquoise water.',
      BHARATPUR,
      'Overnight stay on Neil Island.',
    ],
  },
  bharatpurToPortBlair: {
    title: 'Bharatpur Beach, then return to Port Blair',
    description: [
      `After breakfast and check-out, visit Bharatpur Beach. ${BHARATPUR}`,
      'After lunch, take the cruise to Port Blair. Dinner and overnight stay in Port Blair.',
    ],
  },
  portBlairChidiyatapu: {
    title: 'Return to Port Blair · Chidiyatapu sunset',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in.',
      'After lunch, drive 28 km to Chidiyatapu, the southern tip of South Andaman. Known as Andaman’s bird island, it has mangrove forest, many bird species and wide sunset views over the ocean.',
      'Overnight stay in Port Blair.',
    ],
  },
  portBlairMuseums: {
    title: 'Return to Port Blair · Museums & shopping',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in.',
      MUSEUMS,
      'Overnight stay in Port Blair.',
    ],
  },
  portBlairFisheries: {
    title: 'Return to Port Blair · Museums & shopping',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in.',
      'Visit the Anthropological Museum and the Fisheries Museum, which shows the islands’ marine life. The evening is free for shopping.',
      'Overnight stay in Port Blair.',
    ],
  },
  portBlairMuseumsChatham: {
    title: 'Port Blair city day · Museums, Chatham Saw Mill & Corbyn’s Cove',
    description: [
      'Visit the Anthropological Museum, the Fisheries Museum, the Samudrika Naval Marine Museum and Chatham Saw Mill.',
      'After lunch, relax at Corbyn’s Cove Beach, then spend the evening shopping.',
      'Overnight stay in Port Blair.',
    ],
  },
  portBlairCityTour: {
    title: 'Port Blair city tour & Corbyn’s Cove',
    description: [
      'Visit Chatham Saw Mill, the Samudrika Naval Marine Museum, the Anthropological Museum, the Fisheries Museum, the Rajiv Gandhi Water Sports Complex, Marina Park, Sagarika Emporium, Gandhi Park and the Science Centre.',
      'After lunch, explore Corbyn’s Cove, a coconut palm-fringed beach where you can relax or try water sports (at your own cost).',
      'Overnight stay in Port Blair.',
    ],
  },
  portBlairRoss: {
    title: 'Return to Port Blair · Ross Island & shopping',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in.',
      `After lunch, ${ROSS.charAt(0).toLowerCase()}${ROSS.slice(1)}`,
      'Return to Port Blair for shopping. Overnight stay in Port Blair.',
    ],
  },
  portBlairShopping: {
    title: 'Return to Port Blair · Evening shopping',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in. The evening is free for shopping.',
      'Overnight stay in Port Blair.',
    ],
  },
  rossNorthBay: {
    title: 'Ross Island & North Bay Coral Island',
    description: [ROSS, NORTH_BAY, 'Overnight stay in Port Blair.'],
  },
  rossNorthBaySpeedboat: {
    title: 'Ross Island & North Bay Coral Island',
    description: [
      ROSS.replace('Take a boat', 'Take a speed boat'),
      NORTH_BAY,
      'Overnight stay in Port Blair.',
    ],
  },
  rossNorthBayFerry: {
    title: 'Ross Island & North Bay Coral Island',
    description: [
      `After breakfast, our driver drops you at the jetty. ${ROSS}`,
      NORTH_BAY,
      'Overnight stay in Port Blair.',
    ],
  },
  baratang: {
    title: 'Day trip to Baratang · Limestone caves & mud volcano',
    description: [BARATANG, 'Return to Port Blair for the night.'],
  },
  departure: {
    title: 'Departure from Port Blair',
    description: [
      'After breakfast, check out and transfer to Port Blair airport for your onward flight. Your trip ends with sweet memories.',
    ],
  },
  // 10 Nights / 11 Days (North to South Andaman)
  grandArrival: {
    title:
      'Arrive in Port Blair · Cellular Jail, Corbyn’s Cove & Light and Sound Show',
    description: [
      'Our driver will meet you at the airport and transfer you to your hotel.',
      'After lunch, visit the Cellular Jail and Corbyn’s Cove Beach. In the evening, watch the Light & Sound Show at the Cellular Jail.',
      'Overnight stay in Port Blair.',
    ],
  },
  grandHavelock: {
    title: 'Cruise to Havelock · Radhanagar Beach',
    description: [
      'After check-out, our driver drops you at the cruise terminal. At Havelock jetty our tour coordinator meets you and takes you to the resort.',
      `Around 2 pm, head to Radhanagar Beach. ${RADHANAGAR}`,
      'Dinner and overnight stay in Havelock.',
    ],
  },
  grandElephantKalapathar: {
    title: 'Elephant Beach by speed boat · Kalapathar Beach',
    description: [
      'Around 9 am, take a speed boat from the jetty to Elephant Beach.',
      ELEPHANT,
      `After lunch, visit Kalapathar Beach. ${KALAPATHAR}`,
      'Overnight stay in Havelock.',
    ],
  },
  grandNeil: {
    title: 'Cruise to Neil · Laxmanpur, Natural Bridge & Bharatpur',
    description: [
      'After breakfast and check-out, take the cruise at around 8:30 am and reach Neil Island by about 10:30 am.',
      'After freshening up, visit Laxmanpur Beach, the Natural Bridge and Bharatpur Beach.',
      'Overnight stay on Neil Island.',
    ],
  },
  grandNeilToPortBlair: {
    title: 'Return to Port Blair · Samudrika & Anthropological Museums',
    description: [
      'After breakfast, take the cruise back to Port Blair and check in.',
      'Around 2 pm, visit the Samudrika Naval Marine Museum and the Anthropological Museum.',
      'Overnight stay in Port Blair.',
    ],
  },
  grandBaratangMayabunder: {
    title: 'North via Baratang to Mayabunder',
    description: [
      'Leave around 4 am with a packed breakfast to join the first convoy from Jirkatang at 6 am.',
      BARATANG,
      'Continue north to Mayabunder and check in around 5:30 pm. Overnight stay in Mayabunder.',
    ],
  },
  grandDiglipur: {
    title: 'Mayabunder to Diglipur · Ross & Smith twin islands',
    description: [
      'Check out early and drive about 80 km north to Diglipur, a rare destination for eco-friendly travellers.',
      'Visit Ross and Smith Islands, the twin islands joined by a narrow bar of dazzling white sand.',
      'Check in around 4 pm. Overnight stay in Diglipur.',
    ],
  },
  grandRangat: {
    title: 'Diglipur to Rangat · Amkunj, Cuthbert Bay & Morrice Dera',
    description: [
      'Check out around 6 am for the drive of about 100–110 km (four hours or more) to Rangat.',
      'Between 1 pm and 4 pm, visit Amkunj Beach, Cuthbert Bay Beach and Morrice Dera Beach.',
      'Check in around 5:30 pm. Overnight stay in Rangat.',
    ],
  },
  grandRangatToPortBlair: {
    title: 'Rangat to Port Blair through the heartland',
    description: [
      'Leave early for the 170 km drive back to Port Blair. More than a transfer, it is a journey through the heart of the Andamans, with panoramic views and hidden gems along the way.',
      'Check in around 4:30 pm. Overnight stay in Port Blair.',
    ],
  },
} satisfies Record<string, ItineraryDay>;
