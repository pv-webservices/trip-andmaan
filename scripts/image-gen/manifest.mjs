// Every generated asset has one purpose and an assigned placement.
// Generated scenes are never captioned as a specific Andaman location.
const photo =
  'Natural editorial travel photography, true-to-life colour, soft natural light, shallow depth of field where appropriate. No text, no logos, no watermarks, no borders.';

export const imageTasks = [
  {
    name: 'ai-island-hopping',
    placement: 'Experiences → Island hopping; homepage experience showcase',
    alt: 'View from the bow of a boat crossing turquoise water towards forested tropical islands',
    orientation: 'landscape',
    priority: 'key',
    complexity: 'simple',
    people: 0,
    prompt: `View from the front deck of a small passenger boat crossing calm turquoise tropical sea towards low, densely forested green islands under a bright sky with soft white clouds. Gentle wake, sunlight glinting on the water, a coiled rope and white railing in the near foreground. ${photo}`,
  },
  {
    name: 'ai-snorkel-reef',
    placement:
      'Experiences → Snorkeling & scuba diving; homepage experience showcase',
    alt: 'A snorkeller floating above a colourful coral reef in clear blue water',
    orientation: 'landscape',
    priority: 'key',
    complexity: 'simple',
    people: 1,
    prompt: `Underwater photograph of a single snorkeller in a mask and fins floating at the surface above a healthy shallow coral reef with hard corals, sea fans and small schools of colourful reef fish, clear turquoise water, sun rays streaming down. The snorkeller is seen from below and slightly behind, face not visible. ${photo}`,
  },
  {
    name: 'ai-trip-planning',
    placement: 'Customized trip page; homepage “How planning works” section',
    alt: 'Travel planning flat lay with a notebook, sun hat, sunglasses and seashells on a light wooden table',
    orientation: 'landscape',
    priority: 'key',
    complexity: 'simple',
    people: 0,
    prompt: `Overhead flat lay on a pale natural wooden table: an open blank travel notebook with a pen, a woven straw sun hat, sunglasses, a few seashells, a sprig of tropical green leaf, a camera and a cup of coconut water, arranged with generous empty space on the left side. Bright, airy morning light with soft shadows, ocean-blue and sand tones. Notebook pages are blank with no writing. ${photo}`,
  },
  {
    name: 'ai-beach-walk',
    placement: 'Trip styles → “A trip that feels like you”; About page',
    alt: 'Two travellers walking barefoot along a quiet tropical beach at golden hour',
    orientation: 'portrait',
    priority: 'key',
    complexity: 'simple',
    people: 2,
    prompt: `Two adult travellers seen from behind, walking hand in hand barefoot along the wet sand of a quiet tropical beach at golden hour, gentle waves washing in, a line of tall tropical forest and a few palms along the shore, warm low sunlight, long soft shadows, footprints in the sand. Faces not visible. Relaxed holiday clothing in white and soft blue. ${photo}`,
  },
  {
    name: 'logo-hd',
    placement: 'Header, mobile menu and footer brand mark',
    alt: 'Trip Andaman',
    orientation: 'landscape',
    priority: 'key',
    reference: 'output/generated/logo-reference.png',
    background: 'transparent',
    postprocess: 'trim',
    prompt:
      'Faithfully restore this exact existing logo as a crisp, high-resolution version on a fully transparent background. Preserve the exact wording "Trip Andaman", letterforms, italic script style, colours (deep blue, turquoise and sky blue lettering, yellow sun, green palm tree, island and blue wave), layout and proportions precisely. Do not redesign, add, remove or restyle anything. Clean sharp edges, no background, no shadow, no extra text.',
  },
];
