<?php

namespace App\Cms;

/**
 * Stable page keys, their public paths and the section layout of each page.
 * Section keys never change, so content can be imported and targeted safely.
 */
class PageMap
{
    /**
     * @return array<string, array{title:string, path:?string, kind:string, view:?string, index:bool, sections:array<string, array{0:string,1:string}>}>
     */
    public static function pages(): array
    {
        $hero = ['hero' => ['page_hero', 'Hero banner']];
        $meta = ['meta' => ['meta', 'Search & sharing (SEO)']];
        $banner = ['enquiry_banner' => ['enquiry_banner', 'Enquiry banner']];

        return [
            'home' => ['title' => 'Home', 'path' => '/', 'kind' => 'page', 'view' => 'pages.home', 'index' => true, 'sections' => $meta + [
                'hero' => ['home_hero', 'Hero banner'],
                'promises' => ['promises', 'Promise strip'],
                'story' => ['home_story', 'Why travel with us'],
                'destinations' => ['destinations_showcase', 'Featured destinations'],
                'experiences' => ['experience_tabs', 'Experience showcase'],
                'packages' => ['featured_packages', 'Featured packages & planning cards'],
                'film_band' => ['film_band', 'Underwater film band'],
                'moments' => ['moments', 'Real moments'],
                'steps' => ['steps', 'Planning steps'],
                'testimonials' => ['testimonials', 'Traveller reviews'],
                'gallery' => ['gallery_preview', 'Gallery preview'],
            ] + $banner + ['film_dialog' => ['film_dialog', 'Film window']]],
            'about' => ['title' => 'About', 'path' => '/about/', 'kind' => 'page', 'view' => 'pages.about', 'index' => true, 'sections' => $meta + $hero + [
                'intro' => ['about_intro', 'Introduction'],
                'hosted' => ['photo_strip', 'Travellers we’ve looked after'],
                'values' => ['value_cards', 'Start with what matters'],
                'destinations' => ['destinations_grid', 'Find your island'],
            ] + $banner],
            'tour_packages' => ['title' => 'Tour Packages', 'path' => '/tour-packages/', 'kind' => 'page', 'view' => 'pages.tour-packages', 'index' => true, 'sections' => $meta + $hero + [
                'groups' => ['package_groups', 'Package lists'],
                'services' => ['service_cards', 'What every package takes care of'],
                'customise' => ['customise_block', 'Start from a package'],
                'faq' => ['faq', 'Good to know before you book'],
            ] + $banner],
            'package_detail' => ['title' => 'Package page (all packages)', 'path' => null, 'kind' => 'template', 'view' => 'pages.package', 'index' => false, 'sections' => [
                'labels' => ['package_labels', 'Package page texts'],
                'default_notes' => ['default_notes', 'Notes on every package'],
            ] + $banner],
            'customized_trip' => ['title' => 'Plan My Trip', 'path' => '/tour-packages/customized-trip/', 'kind' => 'page', 'view' => 'pages.customized-trip', 'index' => true, 'sections' => $meta + $hero + [
                'intro' => ['customized_intro', 'Planning introduction'],
            ]],
            'destinations' => ['title' => 'Destinations', 'path' => '/destinations/', 'kind' => 'page', 'view' => 'pages.destinations', 'index' => true, 'sections' => $meta + $hero + [
                'list' => ['destinations_grid', 'Destination cards'],
            ] + $banner],
            'destination_detail' => ['title' => 'Destination page (all destinations)', 'path' => null, 'kind' => 'template', 'view' => 'pages.destination', 'index' => false, 'sections' => [
                'labels' => ['destination_labels', 'Destination page texts'],
            ] + $banner],
            'experiences' => ['title' => 'Experiences', 'path' => '/experiences/', 'kind' => 'page', 'view' => 'pages.experiences', 'index' => true, 'sections' => $meta + $hero + [
                'notice' => ['notice', 'Notice'],
                'items' => ['experience_list', 'Experiences'],
                'water_sports' => ['reel_section', 'Water sports at North Bay'],
            ] + $banner],
            'gallery' => ['title' => 'Gallery', 'path' => '/gallery/', 'kind' => 'page', 'view' => 'pages.gallery', 'index' => true, 'sections' => $meta + $hero + [
                'photos' => ['gallery_grid', 'Photo gallery'],
                'videos' => ['reel_section', 'Beneath the blue (videos)'],
            ] + $banner],
            'contact' => ['title' => 'Contact', 'path' => '/contact/', 'kind' => 'page', 'view' => 'pages.contact', 'index' => true, 'sections' => $meta + $hero + [
                'panel' => ['contact_panel', 'Contact panel'],
            ]],
            'travel_information' => ['title' => 'Travel information', 'path' => '/travel-information/', 'kind' => 'page', 'view' => 'pages.prose', 'index' => true, 'sections' => $meta + $hero + [
                'body' => ['prose', 'Text content'],
            ]],
            'privacy' => ['title' => 'Privacy & enquiries', 'path' => '/privacy/', 'kind' => 'page', 'view' => 'pages.prose', 'index' => true, 'sections' => $meta + $hero + [
                'body' => ['prose', 'Text content'],
            ]],
            'image_credits' => ['title' => 'Photo credits', 'path' => '/image-credits/', 'kind' => 'page', 'view' => 'pages.image-credits', 'index' => false, 'sections' => $meta + $hero + [
                'body' => ['prose', 'Text after the credits list'],
            ]],
            'not_found' => ['title' => 'Page not found (404)', 'path' => null, 'kind' => 'template', 'view' => 'pages.not-found', 'index' => false, 'sections' => $meta + [
                'body' => ['not_found', 'Page not found'],
            ]],
            'global' => ['title' => 'Global settings', 'path' => null, 'kind' => 'global', 'view' => null, 'index' => false, 'sections' => [
                'contact' => ['global_contact', 'Contact details'],
                'navigation' => ['global_navigation', 'Main navigation'],
                'header' => ['global_header', 'Header'],
                'footer' => ['global_footer', 'Footer'],
                'seo' => ['global_seo', 'Default search & sharing'],
                'whatsapp' => ['global_whatsapp', 'WhatsApp widget & quick actions'],
                'enquiry_banner' => ['global_enquiry_banner', 'Enquiry banner (shared text)'],
                'enquiry_form' => ['global_enquiry_form', 'Enquiry form texts'],
                'announcement_bar' => ['global_announcement_bar', 'Announcement bar appearance'],
            ]],
        ];
    }

    /** Page keys that announcements and popups can target. */
    public static function targetOptions(): array
    {
        return collect(self::pages())
            ->reject(fn ($p) => $p['kind'] === 'global')
            ->map(fn ($p) => $p['title'])
            ->all();
    }
}
