<?php

namespace App\Cms\Sections;

use App\Cms\Fields as F;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

/** Section types of the inner pages and the package/destination templates. */
class PageSections
{
    /** @return list<SectionType> */
    public static function types(): array
    {
        return [
            // ---- About ----
            new SectionType('about_intro', 'Introduction', 'Photos, introduction text and check list.', fn () => [
                F::visible(),
                F::heading(),
                F::lines('paragraphs', 'Paragraphs', 800, true),
                F::items('checklist', 'Check list', [F::text('title', 'Bold start', 60, true), F::text('text', 'Text', 200)], 'title', 8),
                F::link('primary', 'Button'),
                Toggle::make('show_phone')->label('Show phone button')->default(true),
                F::image('main_', 'Main photo'),
                F::image('inset_', 'Small inset photo'),
            ]),
            new SectionType('photo_strip', 'Photo strip', 'A row of captioned photos.', fn () => [
                F::visible(),
                F::heading(true),
                F::items('items', 'Photos', [F::media('image_id', 'Photo', 'image', true), F::text('alt', 'Alternative text', 250), F::text('caption', 'Caption', 80, true)], 'caption', 8),
            ]),
            new SectionType('value_cards', 'Numbered cards', 'Numbered cards with a title and text.', fn () => [
                F::visible(),
                F::heading(),
                F::items('items', 'Cards', [F::text('title', 'Title', 80, true), F::area('text', 'Text', 300, true)], 'title', 6),
            ]),
            new SectionType('destinations_grid', 'Destination cards', 'A grid of destination cards. Destinations are edited under Destinations.', fn () => [
                F::visible(),
                F::heading(true, false),
                F::link('link', 'Link to all destinations'),
                TextInput::make('count')->label('Number of destinations shown (0 = all)')->numeric()->minValue(0)->maxValue(24)->default(3),
                F::area('note', 'Note under the cards', 300),
            ]),

            // ---- Tour packages ----
            new SectionType('package_groups', 'Package lists', 'Packages are grouped by length. Packages are edited under Packages; empty groups are hidden.', fn () => [
                F::items('groups', 'Groups', [
                    F::text('eyebrow', 'Eyebrow', 40, true),
                    F::text('title', 'Heading', 80, true),
                    TextInput::make('min_nights')->label('From nights')->numeric()->minValue(0)->required(),
                    TextInput::make('max_nights')->label('To nights')->numeric()->minValue(0)->required(),
                    TextInput::make('anchor')->label('Jump-link name')->regex('/^[a-z][a-z0-9-]*$/')->maxLength(30)->required()
                        ->helperText('Lowercase letters, numbers and hyphens, e.g. "short".'),
                ], 'title', 8),
                F::text('note', 'Note under each heading', 80, false, '{count} is replaced with the number of packages; {price} with "price on request" when none are priced.'),
                F::text('custom_jump_label', 'Jump link to the customised trip', 40),
            ], hideable: false),
            new SectionType('service_cards', 'Service cards', 'Photo cards describing what every package includes.', fn () => [
                F::visible(),
                F::heading(true),
                F::items('items', 'Cards', [
                    F::media('image_id', 'Photo', 'image', true), F::text('alt', 'Alternative text', 250),
                    F::text('title', 'Title', 60, true), F::area('text', 'Text', 300, true),
                ], 'title', 6),
            ]),
            new SectionType('customise_block', 'Customise call-out', 'Invitation to plan a customised trip.', fn () => [
                F::visible(),
                F::heading(true),
                F::image('', 'Photo'),
                F::link('primary', 'Button', false),
            ]),
            new SectionType('faq', 'Questions & answers', 'Shown as an accordion and published to search engines as FAQ structured data.', fn () => [
                F::visible(),
                F::text('title', 'Heading', 80, true),
                F::items('items', 'Questions', [
                    F::text('question', 'Question', 200, true)->columnSpanFull(),
                    F::area('answer', 'Answer', 1000, true)->columnSpanFull(),
                ], 'question'),
                Toggle::make('first_open')->label('Open the first answer by default')->default(true),
                F::link('link', 'Link under the questions'),
            ]),

            // ---- Package detail template ----
            new SectionType('package_labels', 'Package page texts', 'Headings and labels used on every package page. Package-specific content is edited in Packages.', fn () => [
                F::group('Itinerary', [Grid::make(3)->schema([
                    F::text('itinerary_eyebrow', 'Eyebrow', 40), F::text('itinerary_title', 'Heading', 40), F::text('itinerary_accent', 'Highlighted words', 40),
                    F::text('pdf_label', 'PDF download button', 60), F::text('notes_title', '"Good to know" heading', 60),
                    F::text('inclusions_title', 'Inclusions heading', 60), F::text('exclusions_title', 'Exclusions heading', 60),
                ])]),
                F::group('Photos', [Grid::make(3)->schema([
                    F::text('moments_title', 'Heading', 60), F::text('moments_accent', 'Highlighted words', 60), F::text('moments_note', 'Note', 120),
                ])]),
                F::group('Enquiry panel', [Grid::make(3)->schema([
                    F::text('aside_title', 'Heading', 60), F::text('route_label', 'Route label', 30), F::text('price_label', 'Price label', 30),
                    F::text('on_request_text', 'Text when the price is on request', 120), F::text('whatsapp_label', 'WhatsApp button', 60),
                    F::text('pdf_short_label', 'PDF button', 40), F::text('all_label', 'Link to all packages', 60),
                ])]),
                F::group('Similar packages', [Grid::make(3)->schema([
                    F::text('related_eyebrow', 'Eyebrow', 40), F::text('related_title', 'Heading', 40), F::text('related_accent', 'Highlighted words', 40),
                    F::text('related_link', 'Link text', 40), TextInput::make('related_count')->label('How many')->numeric()->minValue(0)->maxValue(6)->default(3),
                ])]),
                F::group('Search engines', [
                    F::text('meta_title', 'Default page title', 120, true, '{title} and {duration} are replaced for each package.'),
                    F::text('meta_description', 'Default description', 200, true, '{duration} and {overview} are replaced; long text is shortened.'),
                ]),
            ], hideable: false),
            new SectionType('default_notes', 'Notes on every package', '"Good to know" notes shown on every package before its own travel notes.', fn () => [
                F::lines('notes', 'Notes', 400, true),
            ], hideable: false),

            // ---- Customised trip ----
            new SectionType('customized_intro', 'Planning introduction', 'Text beside the enquiry form.', fn () => [
                F::heading(true),
                F::image('', 'Photo'),
                F::text('next_title', '"What happens next" heading', 80),
                F::lines('next_items', 'Next steps', 200),
                F::area('note', 'Note', 300, false, 'A call link with the phone number is added after this note.'),
                F::text('call_label', 'Call link text', 40, false, '{phone} is replaced with the phone number.'),
                F::text('form_source', 'Page name used in WhatsApp messages', 60, true),
            ], hideable: false),

            // ---- Destination detail template ----
            new SectionType('destination_labels', 'Destination page texts', 'Headings and labels used on every destination page. Island content is edited in Destinations.', fn () => [
                F::group('Introduction', [Grid::make(3)->schema([
                    F::text('about_eyebrow', 'Eyebrow', 40), F::text('about_title', 'Heading', 40), F::text('about_accent', 'Highlighted words', 40),
                    F::text('radar_title', 'Highlights heading', 40), F::text('radar_accent', 'Highlighted words', 40),
                    F::text('before_label', '"Before you go" label', 40),
                ]), F::area('before_suffix', 'Text after each island note', 400),
                    Grid::make(3)->schema([F::text('source_label', 'Source label', 60), F::text('source_link', 'Source link text', 60), F::text('source_suffix', 'Text after the link', 120)])]),
                F::group('Enquiry panel', [Grid::make(3)->schema([
                    F::text('aside_eyebrow', 'Eyebrow', 60), F::text('aside_title', 'Heading', 60), F::text('plan_label', 'Plan button', 40),
                    F::text('whatsapp_label', 'WhatsApp button', 40), F::text('others_label', 'Link to other destinations', 60),
                ]), F::area('aside_text', 'Text', 300, false, '{name} is replaced with the destination name.')]),
                F::group('Clips', [Grid::make(3)->schema([
                    F::text('reels_eyebrow', 'Eyebrow', 60), F::text('reels_title', 'Heading', 60), F::text('reels_accent', 'Highlighted words', 40),
                    F::text('reels_link', 'Link text', 60), F::url('reels_url', 'Link destination'),
                ])]),
                F::group('More destinations', [Grid::make(3)->schema([
                    F::text('more_eyebrow', 'Eyebrow', 40), F::text('more_title', 'Heading', 40), F::text('more_accent', 'Highlighted words', 40), F::text('more_link', 'Link text', 40),
                ])]),
                F::group('Search engines', [
                    F::text('meta_title', 'Default page title', 120, true, '{name} and {official} are replaced.'),
                    F::text('meta_description', 'Default description', 250, true, '{name}, {official} and {intro} are replaced.'),
                ]),
            ], hideable: false),

            // ---- Experiences ----
            new SectionType('notice', 'Notice', 'A highlighted note with an icon.', fn () => [
                F::visible(), F::icon(), F::area('text', 'Text', 500, true),
            ]),
            new SectionType('experience_list', 'Experiences', 'The list of experiences. The hero jump links are built from these items.', fn () => [
                F::text('eyebrow', 'Eyebrow above each title', 60),
                F::area('note', 'Note under each experience', 300),
                F::text('whatsapp_label', 'WhatsApp button text', 60),
                F::items('items', 'Experiences', [
                    F::text('title', 'Title', 80, true),
                    TextInput::make('anchor')->label('Jump-link name')->regex('/^[a-z][a-z0-9-]*$/')->maxLength(30)->required(),
                    F::icon(),
                    F::area('text', 'Text', 400, true)->columnSpanFull(),
                    F::media('image_id', 'Photo', 'image', true),
                    F::text('alt', 'Photo alternative text', 250),
                    F::text('whatsapp_subject', 'WhatsApp message subject', 120),
                ], 'title', 12),
            ], hideable: false),
            new SectionType('reel_section', 'Video clips', 'A grid of short silent clips.', fn () => [
                F::visible(),
                F::heading(true),
                F::link('link', 'Heading link'),
                F::reels(),
                Grid::make(2)->schema([F::text('whatsapp_label', 'WhatsApp button text', 60), F::text('whatsapp_subject', 'WhatsApp message subject', 120)]),
                F::link('secondary', 'Second button'),
                F::items('films', 'Films with controls (optional)', [
                    F::media('video_id', 'Video', 'video', true), F::media('poster_id', 'Poster', 'image'),
                    F::text('title', 'Title', 80, true), F::text('meta', 'Details, e.g. "20 seconds · silent"', 60),
                    F::text('aria', 'Accessible description', 200, true),
                ], 'title', 4),
            ]),

            // ---- Gallery ----
            new SectionType('gallery_grid', 'Photo gallery', 'Filterable photo gallery with a full-screen viewer.', fn () => [
                F::text('all_label', '"All" filter label', 20, true),
                F::items('items', 'Photos', [
                    F::media('image_id', 'Photo', 'image', true),
                    F::text('title', 'Caption / alternative text', 160, true),
                    F::text('category', 'Category (filter)', 40, true),
                ], 'title'),
                F::area('note', 'Note under the gallery', 300),
                F::text('credits_label', 'Photo credits link text', 60),
            ], hideable: false),

            // ---- Contact ----
            new SectionType('contact_panel', 'Contact panel', 'Contact cards beside the enquiry form.', fn () => [
                F::heading(true),
                Grid::make(3)->schema([F::text('call_label', 'Call card label', 30), F::text('whatsapp_label', 'WhatsApp card label', 30), F::text('map_label', 'Map card label', 30)]),
                F::text('whatsapp_subject', 'WhatsApp message subject', 120),
                F::area('note', 'Note', 300),
                F::text('form_source', 'Page name used in WhatsApp messages', 60, true),
            ], hideable: false),

            // ---- 404 ----
            new SectionType('not_found', 'Page not found', 'Shown when a page does not exist.', fn () => [
                F::text('eyebrow', 'Eyebrow', 80), F::text('number', 'Large number', 10), F::text('title', 'Heading', 120, true),
                F::area('text', 'Text', 300, true),
                Grid::make(3)->schema([F::text('home_label', 'Home button', 40), F::text('whatsapp_label', 'WhatsApp button', 40), F::text('call_label', 'Call button', 40)]),
                F::text('whatsapp_subject', 'WhatsApp message subject', 160),
                F::media('image_id', 'Background image'),
            ], hideable: false),
        ];
    }
}
