<?php

namespace App\Cms\Sections;

use App\Cms\Fields as F;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

/** Homepage section types. */
class HomeSections
{
    /** @return list<SectionType> */
    public static function types(): array
    {
        return [
            new SectionType('home_hero', 'Homepage hero', 'The full-screen opening banner of the homepage.', fn () => [
                Grid::make(2)->schema([
                    F::text('eyebrow', 'Eyebrow', 80),
                    F::text('title', 'Main heading (H1)', 60, true),
                ]),
                Grid::make(3)->schema([
                    F::text('tagline_before', 'Tagline: start', 60),
                    F::text('tagline_script', 'Tagline: handwritten word', 40),
                    F::text('tagline_after', 'Tagline: end', 60),
                ]),
                F::area('copy', 'Supporting text', 400, true),
                F::group('Background', [
                    F::image('', 'Background image', true, true),
                    F::media('mobile_image_id', 'Mobile image (optional)', 'image', false, 'Shown on phones instead of the main image.'),
                    F::video(),
                ], 'If a background video is chosen it plays silently and loops; the image is shown until it loads and for visitors who prefer reduced motion.'),
                F::lines('script_lines', 'Decorative handwritten words (right edge)', 30),
                F::group('Buttons', [
                    F::link('primary', 'Primary button'),
                    F::link('secondary', 'Secondary button'),
                    Toggle::make('secondary_opens_film')->label('Secondary button plays the film')->default(true)
                        ->helperText('Opens the film window set in the "Film window" section instead of following the link.'),
                ]),
                F::group('Hero card', [
                    Toggle::make('card.visible')->label('Show the hero card')->default(true),
                    F::media('card.image_id', 'Card image'),
                    F::text('card.alt', 'Card image alternative text', 250),
                    F::text('card.title', 'Card title', 60),
                    F::text('card.text', 'Card text', 80),
                    Toggle::make('card.show_call')->label('Show call button')->default(true),
                ]),
                F::text('scroll_label', 'Scroll prompt label', 20),
            ], hideable: false),

            new SectionType('promises', 'Promise strip', 'Four short value statements under the hero.', fn () => [
                F::visible(),
                F::items('items', 'Statements', [
                    F::icon(),
                    F::text('title', 'Title', 60, true),
                    F::text('text', 'Text', 160, true)->columnSpanFull(),
                ], 'title', 6),
            ]),

            new SectionType('home_story', 'Why travel with us', 'Introduction with a photo collage and four pillars.', fn () => [
                F::visible(),
                F::heading(true),
                F::items('pillars', 'Pillars', [F::icon(), F::text('label', 'Label', 60, true)], 'label', 6),
                F::link('primary', 'Button'),
                Toggle::make('show_phone')->label('Show phone button')->default(true),
                F::group('Collage', [
                    F::image('tall_', 'Tall photo'),
                    F::image('wide1_', 'Upper photo'),
                    F::image('wide2_', 'Lower photo'),
                    Grid::make(2)->schema([
                        F::text('badge_title', 'Badge title', 60),
                        F::text('badge_text', 'Badge small text', 60),
                    ]),
                ]),
            ]),

            new SectionType('destinations_showcase', 'Featured destinations', 'Destination cards. Destinations themselves are edited under Destinations.', fn () => [
                F::visible(),
                F::heading(true),
                F::link('link', 'Link to all destinations'),
                TextInput::make('count')->label('Number of destinations shown')->numeric()->minValue(1)->maxValue(12)->default(5)->required(),
                F::text('hint', 'Swipe hint on phones', 40),
            ]),

            new SectionType('experience_tabs', 'Experience showcase', 'Tabbed list of experiences with a photo for each.', fn () => [
                F::visible(),
                F::heading(true),
                F::items('items', 'Experiences', [
                    F::text('title', 'Title', 60, true),
                    F::icon(),
                    F::area('text', 'Text', 300, true)->columnSpanFull(),
                    F::media('image_id', 'Photo', 'image', true),
                    F::text('alt', 'Photo alternative text', 250),
                    F::url('url', 'Read-more link', true)->placeholder('/experiences/#fish'),
                ], 'title', 10),
            ]),

            new SectionType('featured_packages', 'Featured packages', 'Featured package cards and the "start with what you love" cards. Choose which packages are featured, and their order, in Packages.', fn () => [
                F::visible(),
                F::heading(true),
                F::link('link', 'Link to all packages'),
                F::text('planning_eyebrow', 'Planning cards eyebrow', 80),
                F::text('planning_cta', 'Planning card link text', 40),
                F::items('planning', 'Planning cards', [
                    F::text('title', 'Title', 60, true),
                    F::text('tag', 'Tag', 60),
                    F::text('text', 'Text', 200)->columnSpanFull(),
                    F::media('image_id', 'Photo', 'image', true),
                    F::text('interest', 'Interest filled into the enquiry form', 120, true),
                ], 'title', 8),
            ]),

            new SectionType('film_band', 'Underwater film band', 'Full-width silent video band.', fn () => [
                F::visible(),
                F::heading(true),
                F::text('script', 'Handwritten words', 40),
                F::video('', 'Background video', true),
                Grid::make(3)->schema([
                    F::text('film_label', 'Film button text', 40),
                    F::text('whatsapp_label', 'WhatsApp button text', 40),
                    F::text('whatsapp_subject', 'WhatsApp message subject', 120),
                ]),
            ]),

            new SectionType('moments', 'Real moments', 'Four supplied photos or clips with captions.', fn () => [
                F::visible(),
                F::heading(true),
                F::link('link', 'Link to the gallery'),
                F::items('items', 'Moments', [
                    Radio::make('kind')->label('Type')->options(['image' => 'Photo', 'video' => 'Silent video'])->default('image')->inline()->live()->columnSpanFull(),
                    F::media('image_id', 'Photo')->visible(fn (Get $get) => $get('kind') !== 'video'),
                    F::media('video_id', 'Video', 'video')->visible(fn (Get $get) => $get('kind') === 'video'),
                    F::media('poster_id', 'Video poster')->visible(fn (Get $get) => $get('kind') === 'video'),
                    F::text('alt', 'Alternative text', 250),
                    F::text('caption', 'Caption', 80, true),
                    Toggle::make('tall')->label('Tall tile'),
                ], 'caption', 6),
            ]),

            new SectionType('steps', 'Planning steps', 'How planning works, with an illustration.', fn () => [
                F::visible(),
                F::heading(true),
                F::image('', 'Illustration'),
                F::text('script', 'Handwritten label on the image', 40),
                F::items('steps', 'Steps', [F::text('title', 'Title', 80, true), F::text('text', 'Text', 200, true)], 'title', 6),
                F::link('primary', 'Primary button'),
                F::link('secondary', 'Secondary button'),
            ]),

            new SectionType('testimonials', 'Traveller reviews', 'Only reviews marked approved are shown. Add genuine reviews only, with permission to show names and photos.', fn () => [
                F::visible('The section stays hidden automatically while there are no approved reviews.'),
                F::heading(),
                F::items('items', 'Reviews', [
                    F::text('name', 'Name', 80, true),
                    Toggle::make('approved')->label('Approved for publication')->default(false),
                    F::area('text', 'Review', 800, true)->columnSpanFull(),
                    TextInput::make('rating')->label('Rating (1–5, optional)')->numeric()->minValue(1)->maxValue(5),
                    F::media('photo_id', 'Photo (optional)'),
                    F::url('source_url', 'Link to the original review (optional)'),
                ], 'name'),
            ]),

            new SectionType('gallery_preview', 'Gallery preview', 'A short selection of gallery photos.', fn () => [
                F::visible(),
                F::heading(true),
                F::link('link', 'Link to the full gallery'),
                F::items('items', 'Photos', [F::media('image_id', 'Photo', 'image', true), F::text('title', 'Caption / alternative text', 160, true)], 'title', 9),
            ]),

            new SectionType('film_dialog', 'Film window', 'The video that opens from "Watch the Ocean" and "Watch the Film".', fn () => [
                F::text('title', 'Window title', 80, true),
                F::video('', 'Film', true),
                F::text('aria', 'Accessible description of the film', 200, true),
                F::text('caption', 'Caption', 200),
            ], hideable: false),
        ];
    }
}
