<?php

namespace App\Cms\Sections;

use App\Cms\Contrast;
use App\Cms\Fields as F;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

/** Settings shared by every page (Global settings). */
class GlobalSections
{
    /** @return list<SectionType> */
    public static function types(): array
    {
        return [
            new SectionType('global_contact', 'Contact details', 'Phone, WhatsApp number and address used in every header, footer, button and enquiry link.', fn () => [
                Grid::make(2)->schema([
                    F::text('business_name', 'Business name', 80, true),
                    F::text('legal_name', 'Legal name', 80, true),
                    F::text('phone_display', 'Phone number as displayed', 30, true, 'For example +91 9646 96 9646.'),
                    TextInput::make('whatsapp_number')->label('WhatsApp / phone number (digits only)')->required()
                        ->regex('/^[1-9][0-9]{9,14}$/')
                        ->helperText('Country code and number, no + or spaces, e.g. 919646969646. Phone links use the same number.'),
                    F::text('greeting', 'WhatsApp message greeting', 120, true, 'Messages read "<greeting> <subject>."'),
                    F::text('default_subject', 'Default enquiry subject', 120, true, 'Used when no page-specific subject applies, e.g. "an Andaman trip".'),
                ]),
                F::area('address', 'Address as displayed', 300, true),
                F::url('map_url', 'Map link (https://…)', true),
                F::group('Address for search engines', [
                    Grid::make(3)->schema([
                        F::text('street', 'Street / area', 120),
                        F::text('locality', 'City', 80),
                        F::text('region', 'State / territory', 80),
                        F::text('postal_code', 'PIN code', 12),
                        F::text('country', 'Country code', 2),
                        F::text('area_served', 'Area served', 120),
                    ]),
                ], null, true),
            ], hideable: false),

            new SectionType('global_navigation', 'Main navigation', 'Menu links in the header, mobile menu, footer and 404 page.', fn () => [
                F::items('items', 'Menu links', [
                    F::text('label', 'Label', 40, true),
                    F::url('url', 'Destination', true),
                ], 'label', 10),
            ], hideable: false),

            new SectionType('global_header', 'Header', 'Call button and "Plan My Trip" button in the header and mobile menu.', fn () => [
                F::text('call_label', 'Small label above the phone number', 40, true),
                F::link('cta', 'Header button', false, true),
                F::text('mobile_tagline', 'Mobile menu tagline', 120),
            ], hideable: false),

            new SectionType('global_footer', 'Footer', 'Footer text, column headings, legal links and social links.', fn () => [
                F::area('about', 'About text', 400, true),
                F::text('tagline', 'Handwritten tagline', 80),
                Grid::make(3)->schema([
                    F::text('explore_heading', 'Links column heading', 40, true),
                    F::text('islands_heading', 'Islands column heading', 40, true),
                    F::text('contact_heading', 'Contact column heading', 40, true),
                ]),
                F::items('legal_links', 'Bottom links', [F::text('label', 'Label', 40, true), F::url('url', 'Destination', true)], 'label', 6),
                F::items('social_links', 'Social media links (optional)', [F::text('label', 'Network name', 40, true), F::url('url', 'Profile address (https://…)', true)], 'label', 8),
                F::text('copyright', 'Copyright line', 120, true, '{year} is replaced with the current year.'),
            ], hideable: false),

            new SectionType('global_seo', 'Default search & sharing', 'Defaults for every page: site name, title suffix and link-preview image. Each page can override its title, description and image.', fn () => [
                Grid::make(2)->schema([
                    F::text('site_name', 'Site name', 60, true),
                    F::text('title_suffix', 'Title suffix', 40, true, 'Added to page titles, e.g. " | Trip Andaman".'),
                ]),
                F::image('og_', 'Default link preview image (1200×630 recommended)'),
            ], hideable: false),

            new SectionType('global_whatsapp', 'WhatsApp widget & quick actions', 'The floating chat card and the mobile bottom bar.', fn () => [
                Grid::make(2)->schema([
                    F::text('panel_title', 'Chat card title', 60, true),
                    F::text('panel_subtitle', 'Chat card subtitle', 80),
                    F::text('fab_label', 'Floating button label', 40, true),
                    F::text('start_label', 'Start chat button', 60, true),
                ]),
                F::area('bubble', 'Chat card message', 300, true),
                Grid::make(3)->schema([
                    F::text('quick_call_label', 'Mobile bar: call', 20, true),
                    F::text('quick_whatsapp_label', 'Mobile bar: WhatsApp', 20, true),
                    F::text('quick_plan_label', 'Mobile bar: plan button', 30, true),
                ]),
                F::url('quick_plan_url', 'Mobile bar plan button destination', true),
            ], hideable: false),

            new SectionType('global_enquiry_banner', 'Enquiry banner (shared text)', 'Text and default image of the enquiry band shown at the end of most pages. Each page can hide it or use a different image.', fn () => [
                F::heading(true),
                F::text('script', 'Handwritten words', 40),
                F::link('primary', 'Main button', false, true),
                Grid::make(2)->schema([
                    F::text('whatsapp_label', 'WhatsApp button text', 40, true),
                    F::text('whatsapp_subject', 'WhatsApp message subject', 120, true),
                ]),
                F::text('card_title', 'Contact card heading', 80, true),
                F::media('image_id', 'Default background image', 'image', true),
            ], hideable: false),

            new SectionType('global_enquiry_form', 'Enquiry form texts', 'The WhatsApp message composer on Contact and Plan My Trip. It never stores or sends data; it opens WhatsApp with a prepared message.', fn () => [
                F::text('heading', 'Form heading', 80, true),
                F::area('intro', 'Introduction', 300, true, '{phone} is replaced with the displayed phone number.'),
                F::text('submit_label', 'Submit button', 60, true),
                F::text('note_before', 'Note under the form: text before the WhatsApp and call links', 120, true),
                F::area('note', 'Note under the form: text after the links', 300, true),
            ], hideable: false),

            new SectionType('global_announcement_bar', 'Announcement bar appearance', 'Colours and speed of the scrolling bar. Messages are managed under Announcements.', fn () => [
                Grid::make(3)->schema([
                    ColorPicker::make('background')->label('Background colour')->required()->regex('/^#[0-9a-fA-F]{6}$/'),
                    ColorPicker::make('text_color')->label('Text colour')->required()->regex('/^#[0-9a-fA-F]{6}$/'),
                    TextInput::make('speed')->label('Scrolling speed (pixels per second)')->numeric()->minValue(20)->maxValue(120)->default(50)->required()
                        ->helperText('20 (slow) to 120 (fast). Visitors can always pause it, and it does not scroll for people who prefer reduced motion.'),
                ]),
            ], hideable: false, validator: fn (array $data) => Contrast::ratio($data['background'] ?? '#000000', $data['text_color'] ?? '#ffffff') < 4.5
                ? ['The text and background colours do not contrast enough to read (ratio '.number_format(Contrast::ratio($data['background'] ?? '#000000', $data['text_color'] ?? '#ffffff'), 1).':1; at least 4.5:1 is required).']
                : []),
        ];
    }
}
