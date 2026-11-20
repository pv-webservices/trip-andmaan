<?php

/*
 * Initial section content, transcribed from the Astro templates in src/pages
 * and src/components (Nov 2026). Imported by `php artisan cms:import` only for
 * sections that do not exist yet, so administrator edits are never overwritten.
 *
 * Media references in keys ending in "_id" use the original names:
 *   "img:<manifest name>", "vid:<file name without .mp4>", "doc:<pdf file name>".
 */

$site = json_decode(file_get_contents(__DIR__.'/site.json'), true);
$reels = json_decode(file_get_contents(__DIR__.'/reels.json'), true);

$link = fn (string $label, string $url, bool $visible = true) => ['label' => $label, 'url' => $url, 'visible' => $visible];
$none = ['label' => '', 'url' => '', 'visible' => false];
$hero = fn (string $eyebrow, string $title, string $description, string $image, string $alt) => [
    'eyebrow' => $eyebrow, 'title' => $title, 'description' => $description,
    'image_id' => "img:{$image}", 'mobile_image_id' => null, 'alt' => $alt, 'focal' => null,
    'video_id' => null, 'poster_id' => null, 'primary' => $none, 'secondary' => $none, 'show_jump_links' => true,
];
$meta = fn (string $title, string $description) => ['title' => $title, 'description' => $description, 'og_image_id' => null, 'og_alt' => null];
$banner = fn (?string $image = null) => ['visible' => true, 'image_id' => $image ? "img:{$image}" : null];
$reel = fn (array $r, string $where = 'at North Bay') => [
    'title' => $r['title'], 'text' => $r['text'], 'video_id' => 'vid:'.$r['slug'], 'poster_id' => 'img:reel-'.$r['slug'],
    'aria' => "Silent clip: {$r['title']} {$where}",
];
$reelBySlug = collect($reels)->keyBy('slug');
$experienceItems = array_map(fn ($e) => [
    'title' => $e['title'], 'anchor' => $e['icon'], 'icon' => $e['icon'], 'text' => $e['text'],
    'image_id' => 'img:'.$e['image'], 'alt' => $e['alt'], 'whatsapp_subject' => mb_strtolower($e['title']).' in the Andamans',
    'url' => '/experiences/#'.$e['icon'],
], $site['experiences']);
$p = fn (string ...$paragraphs) => implode('', array_map(fn ($x) => "<p>{$x}</p>", $paragraphs));
$phone = $site['site']['phone'];
$tel = $site['site']['tel'];
$whatsapp = $site['site']['whatsapp'];

return [
    'global' => [
        'contact' => [
            'business_name' => $site['site']['name'],
            'legal_name' => $site['site']['legalName'],
            'phone_display' => $phone,
            'whatsapp_number' => $site['whatsappNumber'],
            'greeting' => 'Hello Trip Andaman, I would like to enquire about',
            'default_subject' => 'an Andaman trip',
            'address' => $site['site']['address'],
            'map_url' => $site['site']['map'],
            'street' => 'Haddo', 'locality' => 'Sri Vijaya Puram', 'region' => 'Andaman and Nicobar Islands',
            'postal_code' => '744102', 'country' => 'IN', 'area_served' => 'Andaman and Nicobar Islands',
        ],
        'navigation' => ['items' => array_map(fn ($n) => ['label' => $n[0], 'url' => $n[1]], $site['navigation'])],
        'header' => [
            'call_label' => 'Call us',
            'cta' => ['label' => 'Plan My Trip', 'url' => '/tour-packages/customized-trip/'],
            'mobile_tagline' => 'Your next island story starts here.',
        ],
        'footer' => [
            'about' => 'A travel and tourism business based in Haddo, Sri Vijaya Puram — helping you discover the beaches, reefs, heritage and island nature of Andaman.',
            'tagline' => 'Discover · Explore · Experience',
            'explore_heading' => 'Explore', 'islands_heading' => 'The islands', 'contact_heading' => 'Get in touch',
            'legal_links' => [
                ['label' => 'Privacy & enquiries', 'url' => '/privacy/'],
                ['label' => 'Travel information', 'url' => '/travel-information/'],
                ['label' => 'Photo credits', 'url' => '/image-credits/'],
            ],
            'social_links' => [],
            'copyright' => '© {year} Trip Andaman. All rights reserved.',
        ],
        'seo' => ['site_name' => 'Trip Andaman', 'title_suffix' => ' | Trip Andaman', 'og_image_id' => null, 'og_alt' => null],
        'whatsapp' => [
            'panel_title' => 'Trip Andaman', 'panel_subtitle' => 'Sri Vijaya Puram, Andaman',
            'bubble' => 'Hello! Tell us your travel dates, group size and the islands you’d love to visit — we’ll help you plan your Andaman trip.',
            'start_label' => 'Start chat on WhatsApp', 'fab_label' => 'Chat with us',
            'quick_call_label' => 'Call', 'quick_whatsapp_label' => 'WhatsApp', 'quick_plan_label' => 'Plan My Trip',
            'quick_plan_url' => '/tour-packages/customized-trip/',
        ],
        'enquiry_banner' => [
            'eyebrow' => 'Ready for your Andaman adventure?', 'title' => 'Let’s plan your', 'accent' => null, 'script' => 'dream trip',
            'text' => 'Tell us your dates, who’s travelling and what you’d love to see. We’ll talk through the islands, experiences and arrangements that suit you.',
            'primary' => ['label' => 'Plan My Trip', 'url' => '/tour-packages/customized-trip/'],
            'whatsapp_label' => 'WhatsApp Us', 'whatsapp_subject' => 'planning an Andaman trip',
            'card_title' => 'Talk to our Sri Vijaya Puram team', 'image_id' => 'img:sunset',
        ],
        'enquiry_form' => [
            'heading' => 'Tell us about your dream trip',
            'intro' => 'Fill in a few details and we will turn them into a WhatsApp message for you to review and send to {phone}.',
            'submit_label' => 'Open WhatsApp with my enquiry',
            'note_before' => 'Prefer to skip the form?',
            'note' => 'Please do not share passport, payment or other sensitive details.',
        ],
        'announcement_bar' => ['background' => '#04203a', 'text_color' => '#ffffff', 'speed' => 50],
    ],

    'home' => [
        'meta' => $meta('Andaman Holidays & Customised Island Trips', 'Discover the Andaman Islands with Trip Andaman, based in Haddo, Sri Vijaya Puram. Explore Havelock, Neil, Ross, North Bay and Baratang, then plan a trip around you.'),
        'hero' => [
            'eyebrow' => 'Explore the unseen', 'title' => 'Andaman',
            'tagline_before' => 'Your next', 'tagline_script' => 'Paradise', 'tagline_after' => 'awaits',
            'copy' => 'Crystal-clear waters, white sandy beaches, breathtaking islands and unforgettable experiences — discover the real Andaman with Trip Andaman.',
            'image_id' => 'img:hero', 'mobile_image_id' => null, 'alt' => 'Driftwood on the white sand of a turquoise lagoon, Havelock Island', 'focal' => null,
            'video_id' => null, 'poster_id' => null,
            'script_lines' => ['Discover', 'Explore', 'Experience'],
            'primary' => $link('Plan My Trip', '/tour-packages/customized-trip/'),
            'secondary' => $link('Watch the Ocean', '/gallery/'),
            'secondary_opens_film' => true,
            'card' => ['visible' => true, 'image_id' => 'img:client-kayak', 'alt' => 'Two travellers kayaking on calm Andaman water — supplied photograph', 'title' => 'Based in Sri Vijaya Puram', 'text' => 'Talk to a local team today', 'show_call' => true],
            'scroll_label' => 'Scroll',
        ],
        'promises' => ['visible' => true, 'items' => $site['promises']],
        'story' => [
            'visible' => true, 'eyebrow' => 'Why travel with Trip Andaman', 'title' => 'Your trusted travel partner', 'accent' => 'in Andaman',
            'text' => 'We are a locally based travel and tourism business in Haddo, Sri Vijaya Puram. From turquoise beaches to island heritage and the reefs beneath the blue, we help you shape an Andaman holiday around your dates, your group and the way you like to travel.',
            'pillars' => [['icon' => 'pin', 'label' => 'Local expertise'], ['icon' => 'route', 'label' => 'Personalised itineraries'], ['icon' => 'headset', 'label' => 'Reliable support'], ['icon' => 'compass', 'label' => 'Island inspiration']],
            'primary' => $link('About Trip Andaman', '/about/'), 'show_phone' => true,
            'tall_image_id' => 'img:client-neil', 'tall_alt' => 'Travellers at the natural rock bridge on Neil Island — supplied photograph',
            'wide1_image_id' => 'img:havelock', 'wide1_alt' => 'Forest meeting the white sand of Radhanagar Beach, Havelock Island',
            'wide2_image_id' => 'img:sunset', 'wide2_alt' => 'Evening light over the sea at Elephant Beach',
            'badge_title' => 'Haddo, Sri Vijaya Puram', 'badge_text' => 'Andaman & Nicobar Islands',
        ],
        'destinations' => [
            'visible' => true, 'eyebrow' => 'Most loved places', 'title' => 'Top destinations in', 'accent' => 'Andaman',
            'text' => 'From iconic beaches to limestone caves and island heritage — find the places that speak to you.',
            'link' => $link('All Destinations', '/destinations/'), 'count' => 5, 'hint' => 'Swipe to explore',
        ],
        'experiences' => [
            'visible' => true, 'eyebrow' => 'Unforgettable experiences', 'title' => 'More than just a', 'accent' => 'vacation',
            'text' => 'Relax on white sand, explore vibrant marine life or hop between islands. Choose what you love — we’ll help you plan around it.',
            'items' => array_map(fn ($e) => array_intersect_key($e, array_flip(['title', 'icon', 'text', 'image_id', 'alt', 'url'])), $experienceItems),
        ],
        'packages' => [
            'visible' => true, 'eyebrow' => 'Popular tour packages', 'title' => 'Your island holiday,', 'accent' => 'your way',
            'text' => 'Choose from {count} day-by-day itineraries, from a 4-night escape to an 11-day journey north to Diglipur. Every one can be tailored.',
            'link' => $link('All Packages', '/tour-packages/'),
            'planning_eyebrow' => 'Or start with what you love', 'planning_cta' => 'Make it your trip',
            'planning' => array_map(fn ($x) => ['title' => $x['title'], 'tag' => $x['tag'], 'text' => $x['text'], 'image_id' => 'img:'.$x['image'], 'interest' => $x['interest']], $site['planning']),
        ],
        'film_band' => [
            'visible' => true, 'eyebrow' => 'Beneath the blue', 'title' => 'A whole other world', 'accent' => null, 'script' => 'awaits',
            'text' => 'Snorkelling, diving and coral viewing are some of the most memorable parts of an Andaman holiday. Ask about suitable options for your dates.',
            'video_id' => 'vid:underwater-film', 'poster_id' => 'img:underwater',
            'film_label' => 'Watch the Film', 'whatsapp_label' => 'Ask About Diving', 'whatsapp_subject' => 'snorkelling and scuba diving in the Andamans',
        ],
        'moments' => [
            'visible' => true, 'eyebrow' => 'Real moments', 'title' => 'Moments from', 'accent' => 'the islands',
            'text' => 'Photographs and footage from trips we have arranged — a garland welcome at the airport, a beach day with friends, a walk through history and a sea walk at North Bay.',
            'link' => $link('View Gallery', '/gallery/'),
            'items' => [
                ['kind' => 'image', 'image_id' => 'img:client-group-beach', 'video_id' => null, 'poster_id' => null, 'alt' => 'A group of travellers on a white-sand beach with turquoise water behind', 'caption' => 'A beach day with friends', 'tall' => true],
                ['kind' => 'video', 'image_id' => null, 'video_id' => 'vid:sea-walk', 'poster_id' => 'img:reel-sea-walk', 'alt' => 'Silent clip of travellers on a sea walk among fish and coral', 'caption' => 'Sea walk, North Bay', 'tall' => true],
                ['kind' => 'image', 'image_id' => 'img:cellular-jail-corridor', 'video_id' => null, 'poster_id' => null, 'alt' => 'A sunlit corridor of cells inside the Cellular Jail', 'caption' => 'Cellular Jail', 'tall' => false],
                ['kind' => 'image', 'image_id' => 'img:client-airport-welcome', 'video_id' => null, 'poster_id' => null, 'alt' => 'Two travellers welcomed with marigold garlands at Port Blair airport', 'caption' => 'Airport welcome', 'tall' => false],
            ],
        ],
        'steps' => [
            'visible' => true, 'eyebrow' => 'From daydream to island days', 'title' => 'Planning made', 'accent' => 'simple',
            'text' => 'A few details are all it takes to start the conversation.',
            'image_id' => 'img:ai-trip-planning', 'alt' => 'Travel planning flat lay with a notebook, sun hat, sunglasses and seashells', 'script' => 'Let’s plan!',
            'steps' => [
                ['title' => 'Tell us your island wish list', 'text' => 'Share your dates, travel group and the places you’d love to see.'],
                ['title' => 'Explore the possibilities', 'text' => 'Talk through destinations, experiences and the pace that suits you.'],
                ['title' => 'Bring your plans together', 'text' => 'Request a written proposal and confirm every detail before booking.'],
            ],
            'primary' => $link('Start Planning', '/tour-packages/customized-trip/'),
            'secondary' => $link('Travel Information', '/travel-information/'),
        ],
        'testimonials' => ['visible' => true, 'eyebrow' => 'TRAVELLER STORIES', 'title' => 'Our Happy', 'accent' => 'Travellers', 'items' => []],
        'gallery' => [
            'visible' => true, 'eyebrow' => 'Glimpses of Andaman', 'title' => 'Photo', 'accent' => 'gallery',
            'text' => 'Coastal colours, island textures and moments by the sea.', 'link' => $link('Full Gallery', '/gallery/'),
            'items' => [
                ['image_id' => 'img:hero', 'title' => 'Sandy lagoon, Havelock Island'],
                ['image_id' => 'img:cellular-jail-view', 'title' => 'Sea view from the Cellular Jail tower — supplied photograph'],
                ['image_id' => 'img:bridge', 'title' => 'Rocky coastline, Shaheed Dweep'],
                ['image_id' => 'img:client-group-beach', 'title' => 'A beach day with friends — supplied photograph'],
                ['image_id' => 'img:ross', 'title' => 'Historic remains on Ross Island'],
                ['image_id' => 'img:neil-reef-flats', 'title' => 'Reef flats at low tide — supplied photograph'],
            ],
        ],
        'enquiry_banner' => $banner(),
        'film_dialog' => [
            'title' => 'Beneath the Andaman blue', 'video_id' => 'vid:underwater-film', 'poster_id' => 'img:underwater',
            'aria' => 'Silent underwater film showing fish swimming in turquoise water',
            'caption' => 'Silent underwater footage from the supplied travel collection.',
        ],
    ],

    'about' => [
        'meta' => $meta('About Us — Your Local Andaman Travel Partner', 'Meet Trip Andaman, a travel and tourism business in Haddo, Sri Vijaya Puram. Customised Andaman trips, local knowledge and support from first call to final details.'),
        'hero' => $hero('A hello from the islands', 'A little closer to Andaman', 'We’re a locally based travel and tourism business helping visitors discover the beaches, reefs, heritage and island nature of Andaman.', 'neil', 'Sandy shore and forest on Shaheed Dweep (Neil Island)'),
        'intro' => [
            'visible' => true, 'eyebrow' => 'Meet Trip Andaman', 'title' => 'The islands are calling.', 'accent' => 'Let’s talk travel.',
            'paragraphs' => [
                'TRIP ANDAMAN is a travel and tourism business based in Haddo, Sri Vijaya Puram, in the Andaman and Nicobar Islands.',
                'Maybe you’re picturing days beside the sea. Perhaps you’re curious about the islands’ heritage, coastal landscapes or marine experiences. Share your travel dates, group size and interests — we’ll discuss destinations, travel options and the details you need before making your plans.',
            ],
            'checklist' => array_map(fn ($x) => ['title' => $x['title'], 'text' => $x['text']], $site['promises']),
            'primary' => $link('Say Hello', '/contact/'), 'show_phone' => true,
            'main_image_id' => 'img:ai-beach-walk', 'main_alt' => 'Two travellers walking along a quiet tropical beach at golden hour',
            'inset_image_id' => 'img:client-kayak', 'inset_alt' => 'Two travellers kayaking on calm water — supplied photograph',
        ],
        'hosted' => [
            'visible' => true, 'eyebrow' => 'Travellers we’ve looked after', 'title' => 'Families, friends and', 'accent' => 'groups of every size',
            'text' => 'From a garland welcome on arrival to the last sunset on the beach, these are real moments from Andaman trips we have arranged.',
            'items' => [
                ['image_id' => 'img:client-hotel-welcome', 'alt' => 'Travellers welcomed with garlands in a hotel lobby', 'caption' => 'Welcome on arrival'],
                ['image_id' => 'img:client-beach-huts', 'alt' => 'A group of travellers relaxing under thatched beach huts', 'caption' => 'Beach-hut afternoons'],
                ['image_id' => 'img:client-lagoon', 'alt' => 'A traveller wading in a calm, shallow lagoon', 'caption' => 'Into the lagoon'],
                ['image_id' => 'img:client-rocky-cove', 'alt' => 'A couple resting on the rocks beside the sea', 'caption' => 'A quiet moment by the sea'],
            ],
        ],
        'values' => [
            'visible' => true, 'eyebrow' => 'Your holiday, thoughtfully considered', 'title' => 'Start with what', 'accent' => 'matters to you',
            'items' => [
                ['title' => 'The way you like to travel', 'text' => 'A family holiday, time for two or a journey with friends. Tell us what would make the trip feel right.'],
                ['title' => 'The places on your wish list', 'text' => 'Explore our island guides, then let us know which destinations have captured your imagination.'],
                ['title' => 'The details before you go', 'text' => 'Ask for a written proposal with confirmed arrangements, charges and applicable booking terms.'],
            ],
        ],
        'destinations' => ['visible' => true, 'eyebrow' => 'Find your island', 'title' => 'So many ways to', 'accent' => 'explore', 'text' => null, 'link' => $link('All Destinations', '/destinations/'), 'count' => 3, 'note' => null],
        'enquiry_banner' => $banner(),
    ],

    'tour_packages' => [
        'meta' => $meta('Andaman Tour Packages — 4 to 10 Night Itineraries', 'Andaman tour packages from 4 to 10 nights covering Port Blair, Havelock, Neil, Ross, North Bay, Baratang and Diglipur, with day-by-day itineraries.'),
        'hero' => $hero('Tours, packages & possibilities', 'Andaman tour packages', 'Fifteen tried-and-tested itineraries, from a 4-night island escape to an 11-day journey from Port Blair to Diglipur. Every one can be tailored.', 'ai-island-hopping', 'View from the bow of a boat crossing turquoise water towards forested islands'),
        'groups' => [
            'groups' => [
                ['anchor' => 'short', 'eyebrow' => '4–5 nights', 'title' => 'Short island escapes', 'min_nights' => 0, 'max_nights' => 5],
                ['anchor' => 'week', 'eyebrow' => '6 nights', 'title' => 'A week in the islands', 'min_nights' => 6, 'max_nights' => 6],
                ['anchor' => 'long', 'eyebrow' => '7–10 nights', 'title' => 'Longer journeys', 'min_nights' => 7, 'max_nights' => 99],
            ],
            'note' => '{count} itineraries · {price}',
            'custom_jump_label' => 'Customised trip',
        ],
        'services' => [
            'visible' => true, 'eyebrow' => 'Looked after from arrival to departure', 'title' => 'What every package', 'accent' => 'takes care of',
            'text' => 'Real moments from trips we have arranged: the welcome at the airport, the cruise between islands and the stay at the end of the day.',
            'items' => [
                ['image_id' => 'img:client-airport-welcome', 'alt' => 'Travellers welcomed with marigold garlands outside Port Blair airport', 'title' => 'Airport welcome', 'text' => 'Our representative meets you outside Port Blair airport and stays in touch for the whole trip.'],
                ['image_id' => 'img:client-jetty-group', 'alt' => 'A group of travellers at the jetty in front of an inter-island ship', 'title' => 'Cruises & transfers', 'text' => 'Cruise tickets, jetty pick-ups and island transfers are arranged and coordinated for you.'],
                ['image_id' => 'img:client-resort-stay', 'alt' => 'A group of travellers by the pool at their hotel', 'title' => 'Hand-picked stays', 'text' => 'Hotels and resorts in Port Blair, Havelock and Neil, chosen to suit your budget.'],
            ],
        ],
        'customise' => [
            'visible' => true, 'eyebrow' => 'Nothing quite right?', 'title' => 'Start from a package.', 'accent' => 'Make it yours.',
            'text' => 'Tell us your dates, number of travellers and the islands you would love to see. We will adapt any itinerary or build one from scratch.',
            'image_id' => 'img:client-group-beach', 'alt' => 'A group of travellers on a white-sand Andaman beach',
            'primary' => ['label' => 'Plan a Customised Trip', 'url' => '/tour-packages/customized-trip/'],
        ],
        'faq' => [
            'visible' => true, 'title' => 'Good to know before you book', 'first_open' => true,
            'items' => [
                ['question' => 'What is included in the price?', 'answer' => 'Every package is quoted on request for your dates, hotel category and group size. Your written quotation lists exactly what is included, such as hotels, cruises, transfers and sightseeing.'],
                ['question' => 'Are water activities included?', 'answer' => 'Activities such as scuba diving, sea walking, jet skis and parasailing at North Bay, Elephant Beach and Neil are paid on the spot, at your own cost, unless your quotation says otherwise.'],
                ['question' => 'Can I change a package?', 'answer' => 'Yes. Every itinerary can be lengthened, shortened or combined. Add Baratang, an extra night on Havelock or a trip to Diglipur, and we will rework the plan.'],
                ['question' => 'What should I check before booking?', 'answer' => 'Ask for the complete itinerary, the hotels proposed, the total price, and the payment, amendment and cancellation terms in writing before you confirm.'],
            ],
            'link' => $link('Read our travel information', '/travel-information/'),
        ],
        'enquiry_banner' => $banner(),
    ],

    'package_detail' => [
        'labels' => [
            'itinerary_eyebrow' => 'Day by day', 'itinerary_title' => 'Your', 'itinerary_accent' => 'itinerary',
            'pdf_label' => 'Download Itinerary (PDF)', 'notes_title' => 'Good to know',
            'inclusions_title' => 'Included', 'exclusions_title' => 'Not included',
            'moments_title' => 'Moments from', 'moments_accent' => 'this route', 'moments_note' => 'Photographs from trips we have arranged.',
            'aside_title' => 'Enquire about this tour', 'route_label' => 'Route', 'price_label' => 'Price',
            'on_request_text' => 'On request, for your dates and hotel choice', 'whatsapp_label' => 'Get a Quote on WhatsApp',
            'pdf_short_label' => 'Itinerary PDF', 'all_label' => 'See all packages',
            'related_eyebrow' => 'Compare options', 'related_title' => 'Similar', 'related_accent' => 'packages', 'related_link' => 'All Packages', 'related_count' => 3,
            'meta_title' => '{title} — {duration} Andaman Tour', 'meta_description' => '{duration} Andaman tour. {overview}',
        ],
        'default_notes' => ['notes' => [
            'Prices depend on your dates, hotel category and group size. Ask for a written quotation.',
            'Water activities at North Bay, Elephant Beach and Neil are at your own cost unless your quotation includes them.',
            'Cruise timings, sea conditions and local permits can change the order of days. We will keep you informed.',
        ]],
        'enquiry_banner' => $banner(),
    ],

    'customized_trip' => [
        'meta' => $meta('Plan a Customised Andaman Trip', 'Tell Trip Andaman about your dates, group and interests. Start an enquiry for an Andaman holiday shaped around your preferences.'),
        'hero' => $hero('Your own island story', 'A trip that feels like you', 'Start with a wish list. Let’s talk about bringing your Andaman plans together.', 'ai-trip-planning', 'Travel planning flat lay with a notebook, sun hat, sunglasses and seashells'),
        'intro' => [
            'eyebrow' => 'Let’s start planning', 'title' => 'Your dates. Your pace.', 'accent' => 'Your Andaman.',
            'text' => 'Whether you’re dreaming of a beach-focused break, island sightseeing or exploring the marine world, tell us what interests you.',
            'image_id' => 'img:ai-beach-walk', 'alt' => 'Two travellers walking along a quiet tropical beach at golden hour',
            'next_title' => 'What happens next?',
            'next_items' => [
                'We talk through suitable destinations, experiences and pace.',
                'Ask for a written itinerary and quotation.',
                'Confirm the services and applicable terms before booking.',
            ],
            'note' => 'This is a trip-planning enquiry, with no fixed price, duration or guaranteed availability. Prefer to talk?',
            'call_label' => 'Call {phone}', 'form_source' => 'Plan My Trip page',
        ],
    ],

    'destinations' => [
        'meta' => $meta('Andaman Destinations — Havelock, Neil, Ross & More', 'Andaman destination guides to Havelock, Neil, Ross, North Bay, Baratang and Sri Vijaya Puram (Port Blair), with island highlights and trip ideas.'),
        'hero' => $hero('Six places. So many possibilities.', 'Find your island', 'From white-sand shores to stories held in the forest — discover a different side of Andaman.', 'havelock', 'Forest meeting the white sand of Radhanagar Beach, Havelock Island'),
        'list' => [
            'visible' => true, 'eyebrow' => 'The island edit', 'title' => 'Where will your journey', 'accent' => 'take you?',
            'text' => 'Explore the places below, then ask about current access and travel arrangements for your dates.',
            'link' => $none, 'count' => 0,
            'note' => 'Destination guides are inspiration for your enquiry. Travel routes, activities and access must be confirmed before travel.',
        ],
        'enquiry_banner' => $banner(),
    ],

    'destination_detail' => [
        'labels' => [
            'about_eyebrow' => 'Get to know the island', 'about_title' => 'A place to', 'about_accent' => 'discover',
            'radar_title' => 'On your', 'radar_accent' => 'radar', 'before_label' => 'Before you go.',
            'before_suffix' => 'Share your dates and any specific interests when you enquire, and confirm the route, visiting arrangements, charges and terms before booking.',
            'source_label' => 'Destination reference:', 'source_link' => 'Official tourism information ↗', 'source_suffix' => '. Check current local notices before travel.',
            'aside_eyebrow' => 'Make room for the islands', 'aside_title' => 'Picture yourself here?',
            'aside_text' => 'Tell us when you’re travelling, who’s coming along and what you’d love to experience on {name}.',
            'plan_label' => 'Plan a Trip Here', 'whatsapp_label' => 'Ask on WhatsApp', 'others_label' => 'Explore other destinations',
            'reels_eyebrow' => 'Water sports at North Bay', 'reels_title' => 'A taste of', 'reels_accent' => 'the bay',
            'reels_link' => 'See all water sports', 'reels_url' => '/experiences/#water-sports',
            'more_eyebrow' => 'Keep exploring', 'more_title' => 'More island', 'more_accent' => 'inspiration', 'more_link' => 'All Destinations',
            'meta_title' => '{name} — {official} Travel Guide',
            'meta_description' => 'Discover {name} ({official}). {intro} Explore destination highlights and contact Trip Andaman to discuss your visit.',
        ],
        'enquiry_banner' => $banner(),
    ],

    'experiences' => [
        'meta' => $meta('Andaman Experiences — Snorkelling, Island Hopping & Beach Days', 'Discover snorkelling, island hopping, beach relaxation, water activities, heritage sightseeing and nature in the Andamans. Enquire about options for your travel dates.'),
        'hero' => $hero('Experiences in the Andamans', 'A little more wonder', 'Some memories begin on the shore. Others begin beneath the surface.', 'ai-snorkel-reef', 'A snorkeller floating above a colourful coral reef'),
        'notice' => ['visible' => true, 'icon' => 'compass', 'text' => 'These are destination experiences to explore, rather than a list of activities operated by TRIP ANDAMAN. Ask about availability, providers and conditions for your dates.'],
        'items' => [
            'eyebrow' => 'Find your kind of day',
            'note' => 'Confirm local availability, access, charges and any suitability requirements before making arrangements.',
            'whatsapp_label' => 'Ask About This on WhatsApp',
            'items' => array_map(fn ($e) => array_diff_key($e, ['url' => 1]), $experienceItems),
        ],
        'water_sports' => [
            'visible' => true, 'eyebrow' => 'Water sports at North Bay', 'title' => 'See it before', 'accent' => 'you dive in',
            'text' => 'Short silent clips from North Bay Coral Island, where most of our packages spend a day. Activities are paid locally and depend on sea conditions.',
            'link' => $none,
            'reels' => array_map(fn ($r) => $reel($r), $reels),
            'whatsapp_label' => 'Ask About Water Sports', 'whatsapp_subject' => 'water sports at North Bay',
            'secondary' => $link('Packages with North Bay', '/tour-packages/'),
            'films' => [],
        ],
        'enquiry_banner' => $banner('havelock'),
    ],

    'gallery' => [
        'meta' => $meta('Andaman Photo Gallery — Island Scenery & Travel Moments', 'Explore an Andaman photo gallery of beaches, island landscapes, heritage and supplied travel moments. Find inspiration for your own island journey.'),
        'hero' => $hero('The Andaman gallery', 'Postcards from the islands', 'A collection of coastal colours, quiet corners and moments of discovery.', 'bridge', 'Rocky coastline and natural arch on Shaheed Dweep (Neil Island)'),
        'photos' => [
            'all_label' => 'All',
            'items' => array_map(fn ($g) => ['image_id' => 'img:'.$g[0], 'title' => $g[1], 'category' => $g[2]], [
                ['hero', 'Sandy lagoon, Havelock Island', 'Beaches'],
                ['sunset', 'Evening at Elephant Beach', 'Beaches'],
                ['underwater', 'Beneath the surface — supplied travel film', 'Underwater'],
                ['neil', 'Coastal scenery, Shaheed Dweep', 'Islands'],
                ['north-bay', 'North Bay lighthouse', 'Islands'],
                ['ross', 'Historic remains on Ross Island', 'Heritage'],
                ['baratang', 'Limestone formations, Baratang', 'Nature'],
                ['bridge', 'Rocky coastline, Shaheed Dweep', 'Nature'],
                ['client-kayak', 'A moment on the water — supplied photograph', 'Travel moments'],
                ['client-neil', 'Natural bridge visit — supplied photograph', 'Travel moments'],
                ['client-arrival', 'A travel group on arrival — supplied photograph', 'Travel moments'],
                ['cellular-jail-wing', 'A wing of the Cellular Jail — supplied photograph', 'Heritage'],
                ['cellular-jail-corridor', 'Sunlit corridor of cells, Cellular Jail — supplied photograph', 'Heritage'],
                ['cellular-jail-view', 'Sea view from the Cellular Jail tower — supplied photograph', 'Heritage'],
                ['resort-palms', 'Palm-fringed shore beside a resort — supplied photograph', 'Beaches'],
                ['neil-reef-flats', 'Reef flats at low tide — supplied photograph', 'Nature'],
                ['coastal-rocks', 'Forest-backed rocky coast — supplied photograph', 'Nature'],
                ['client-group-beach', 'A beach day with friends — supplied photograph', 'Travel moments'],
                ['client-airport-welcome', 'Garland welcome at Port Blair airport — supplied photograph', 'Travel moments'],
                ['client-jetty-group', 'Ready to board the island cruise — supplied photograph', 'Travel moments'],
                ['client-reef-shore', 'Exploring the shore at low tide — supplied photograph', 'Travel moments'],
                ['client-rocky-cove', 'A rest on the rocks by the sea — supplied photograph', 'Travel moments'],
                ['client-lagoon', 'Wading in a shallow lagoon — supplied photograph', 'Travel moments'],
                ['client-boat-ride', 'Life jackets on for the boat ride — supplied photograph', 'Travel moments'],
                ['client-beach-huts', 'Under the beach huts — supplied photograph', 'Travel moments'],
                ['handicraft-elephant', 'Carved wooden elephant, local handicraft — supplied photograph', 'Shopping'],
                ['handicraft-butterfly', 'Wooden butterfly wall art, local handicraft — supplied photograph', 'Shopping'],
            ]),
            'note' => 'Scenic photographs are credited to their creators. Supplied travel moments are labelled separately.',
            'credits_label' => 'View photo credits',
        ],
        'videos' => [
            'visible' => true, 'eyebrow' => 'Take a closer look', 'title' => 'Beneath the', 'accent' => 'blue',
            'text' => 'Silent clips of water sports at North Bay Coral Island, plus underwater footage from our travel collection.',
            'link' => $link('Water Sports', '/experiences/#water-sports'),
            'reels' => array_map(fn ($r) => $reel($r), $reels),
            'whatsapp_label' => null, 'whatsapp_subject' => null, 'secondary' => $none,
            'films' => [
                ['video_id' => 'vid:underwater-film', 'poster_id' => 'img:underwater', 'title' => 'Fish in turquoise water', 'meta' => '20 seconds · silent', 'aria' => 'Silent film of fish swimming underwater'],
                ['video_id' => 'vid:scuba-film', 'poster_id' => 'img:client-scuba', 'title' => 'A scuba dive above the coral', 'meta' => '12 seconds · silent', 'aria' => 'Silent clip of scuba divers above a coral reef'],
            ],
        ],
        'enquiry_banner' => $banner(),
    ],

    'contact' => [
        'meta' => $meta('Contact Trip Andaman — Plan Your Island Holiday', 'Call or WhatsApp +91 9646 96 9646 to discuss your Andaman holiday. Trip Andaman is based in Haddo, Sri Vijaya Puram.'),
        'hero' => $hero('Your next adventure starts here', 'Let’s talk about your trip', 'A question, a wish list or the beginnings of a plan — we’d love to hear from you.', 'sunset', 'Evening light over the sea at Elephant Beach'),
        'panel' => [
            'eyebrow' => 'Say hello to Trip Andaman', 'title' => 'A real conversation.', 'accent' => 'An island of possibilities.',
            'text' => 'Planning a visit to the Andaman Islands? Call, WhatsApp or send your details and we’ll talk through your travel requirements.',
            'call_label' => 'Call us', 'whatsapp_label' => 'WhatsApp', 'map_label' => 'Find us',
            'whatsapp_subject' => 'an Andaman trip (from your Contact page)',
            'note' => 'The map searches the Haddo locality. Contact us for directions to the office.',
            'form_source' => 'Contact page',
        ],
    ],

    'travel_information' => [
        'meta' => $meta('Travel & Booking Information', 'Useful questions to discuss before confirming an Andaman holiday, including itineraries, quotations, availability and booking terms.'),
        'hero' => $hero('A FEW USEFUL DETAILS', 'Before Your Island Escape', 'Make space for the dreaming. Take a moment for the practical details, too.', 'ai-island-hopping', 'View from a boat crossing turquoise water towards forested islands'),
        'body' => [
            'blocks' => [
                ['title' => 'Inspiration to', 'accent' => 'Start a Conversation', 'body_html' => $p('The destination guides, photographs and travel themes on this website provide inspiration for an enquiry. They do not confirm a package, departure date, activity or booking.')],
                ['title' => 'Confirm the', 'accent' => 'Details', 'body_html' => $p('Before booking, request a written itinerary and quotation that identifies your travel dates, destinations, accommodation and transport arrangements, inclusions, exclusions and total charges.')],
                ['title' => 'Access &', 'accent' => 'Activities', 'body_html' => $p('Discuss current island access, transport schedules, local restrictions, weather and sea conditions. For water activities, confirm the provider, suitability requirements and applicable arrangements directly before committing.')],
                ['title' => 'Payment &', 'accent' => 'Booking Terms', 'body_html' => $p('Request the terms that apply to your specific proposed booking, including payment arrangements, changes, cancellations and refunds. This website does not publish a standard cancellation or refund policy and does not take online payments.')],
                ['title' => 'Plan With', 'accent' => 'Current Information', 'body_html' => $p('Check <a href="https://tourism.andamannicobar.gov.in/" target="_blank" rel="noopener noreferrer">official Andaman tourism information</a> and confirm practical details close to your travel date.')],
            ],
            'button' => $link('Discuss Your Travel Plans →', '/contact/'),
        ],
    ],

    'privacy' => [
        'meta' => $meta('Privacy & Travel Enquiries', 'How the Trip Andaman website handles enquiry details, local interactions and links to external services.'),
        'hero' => $hero('CLEAR INFORMATION', 'Privacy & Enquiries', 'A practical explanation of how this website’s enquiry tools work.', 'north-bay', 'North Bay lighthouse among tropical trees'),
        'body' => [
            'blocks' => [
                ['title' => 'How Enquiries', 'accent' => 'Work', 'body_html' => $p(
                    "You can contact TRIP ANDAMAN by phone ({$phone}) or WhatsApp. The enquiry form on this website asks for your name, mobile number, optional travel date, number of travellers and interests, and a message. Please share only what is needed to discuss your holiday — no passport, payment or other sensitive details.",
                    'The form does not send your details to this website, a database or any other service. When you press “Open WhatsApp with my enquiry”, your browser turns the details into a WhatsApp message addressed to TRIP ANDAMAN and opens WhatsApp. At that point you are transferred to WhatsApp, a service operated by Meta Platforms under its own terms and privacy policy. Nothing is sent until you choose to press Send inside WhatsApp, and you can edit or discard the message first.',
                    'If you do send it, TRIP ANDAMAN receives your message and WhatsApp number in its WhatsApp account and uses them to reply to your travel enquiry.',
                )],
                ['title' => 'Website', 'accent' => 'Storage', 'body_html' => $p(
                    'This website does not use analytics, advertising or tracking cookies, and it does not save form entries in your browser. Form fields and gallery controls work locally in your browser.',
                    'The website is hosted on Vercel, which processes technical request information (such as IP address and browser type) to deliver and protect the site, under its own terms.',
                )],
                ['title' => 'External', 'accent' => 'Links', 'body_html' => $p('Phone links open your phone app. WhatsApp links open WhatsApp. Map links open Google Maps. Destination references and photo-credit links lead to third-party websites with their own privacy practices.')],
                ['title' => 'Questions About', 'accent' => 'Your Information?', 'body_html' => $p(
                    "Call <a href=\"tel:{$tel}\">{$phone}</a> or <a href=\"".htmlspecialchars($whatsapp).'" target="_blank" rel="noopener noreferrer">message us on WhatsApp</a> to ask about information you have shared with us.',
                    'This page describes how the website currently works. For business data-handling terms, please contact TRIP ANDAMAN.',
                )],
            ],
            'button' => $none,
        ],
    ],

    'image_credits' => [
        'meta' => $meta('Photography Credits & Sources', 'Sources, creators and licences for scenic photographs used on the Trip Andaman website.'),
        'hero' => $hero('CREATORS & SOURCES', 'Behind the Photographs', 'A thank you to the photographers who help bring these islands into view.', 'baratang', 'Limestone cave formations at Baratang'),
        'body' => [
            'blocks' => [
                ['title' => 'Supplied', 'accent' => 'Travel Collection', 'body_html' => $p('The kayaking photograph, natural bridge visit photograph, arrival-day group photograph, underwater footage and scuba diving clip were supplied for this project. They are presented as supplied travel media, without attributing reviews or endorsements to the people shown.')],
                ['title' => 'Illustrative', 'accent' => 'Imagery', 'body_html' => $p(
                    'A small number of illustrative images were created with AI image generation: the boat-deck island view, the snorkeller above a coral reef, the travel-planning flat lay and the couple walking on a beach. They illustrate types of experiences and are not photographs of a specific Andaman location, traveller or service.',
                    'The TRIP ANDAMAN logo is supplied brand artwork, shown here with its white background removed (and in a light variant on dark sections). It remains the property of its owner.',
                )],
            ],
            'button' => $none,
        ],
    ],

    'not_found' => [
        'meta' => $meta('Page Not Found', 'This page could not be found. Return to Trip Andaman\'s home page, tour packages or destinations, or contact us on WhatsApp or phone.'),
        'body' => [
            'eyebrow' => 'A little off the island trail', 'number' => '404', 'title' => 'Let’s get you back to paradise.',
            'text' => 'We couldn’t find this page. It may have moved, or the link may be mistyped. Try one of these instead:',
            'home_label' => 'Back to Home', 'whatsapp_label' => 'WhatsApp us', 'call_label' => 'Call {phone}',
            'whatsapp_subject' => 'an Andaman trip (I reached a missing page on your website)', 'image_id' => 'img:neil',
        ],
    ],

    // Extras used by the destination and package import.
    '_destination_extras' => [
        'havelock-island' => ['secondary' => ['hero', 'Driftwood on a sandy lagoon, Havelock Island', false]],
        'neil-island' => ['secondary' => ['client-neil', 'Travellers at the natural rock bridge on Neil Island — supplied photograph', true]],
        'north-bay-island' => [
            'secondary' => ['client-speedboat', 'A traveller on a speed boat in the bay — supplied photograph', true],
            'reels' => array_map(fn ($slug) => $reel($reelBySlug[$slug]), ['sea-walk', 'semi-submarine', 'parasailing', 'snorkeling']),
        ],
        'sri-vijaya-puram' => ['secondary' => ['cellular-jail-corridor', 'A sunlit corridor of cells inside the Cellular Jail — supplied photograph', true]],
    ],
    '_moment_captions' => [
        'client-group-beach' => 'A group day on the white sand',
        'client-jetty-group' => 'Ready to board the island cruise',
        'client-natural-bridge' => 'At the Natural Bridge, Neil Island',
        'client-reef-shore' => 'Low tide on Neil’s reef flats',
        'client-rocky-cove' => 'Rest stop on the rocky shore',
        'client-lagoon' => 'Wading in a shallow lagoon',
        'client-beach-shade' => 'Beach shade, Havelock',
        'client-boat-ride' => 'Life jackets on for the boat ride',
        'client-speedboat' => 'Speed boat to the islands',
        'client-hotel-welcome' => 'A garland welcome at the hotel',
        'cellular-jail-gate' => 'Cellular Jail entrance',
        'cellular-jail-corridor' => 'Inside the Cellular Jail',
        'cellular-jail-cell' => 'Veer Savarkar’s cell',
        'cellular-jail-view' => 'View from the Cellular Jail tower',
        'resort-palms' => 'Palms by the sea at the resort',
        'neil-reef-flats' => 'Reef flats, Neil Island',
        'coastal-rocks' => 'Forest meets the rocky coast',
        'handicraft-elephant' => 'Island woodcraft to take home',
    ],
    '_featured' => ['andaman-island-escape-4n5d', 'andaman-classic-6n7d', 'north-to-south-andaman-10n11d'],
];
