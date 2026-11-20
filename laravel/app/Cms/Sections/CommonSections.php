<?php

namespace App\Cms\Sections;

use App\Cms\Fields as F;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

/** Section types used on several pages. */
class CommonSections
{
    /** @return list<SectionType> */
    public static function types(): array
    {
        return [
            new SectionType('meta', 'Search & sharing (SEO)', 'Title and description shown in search results and link previews.', fn () => [
                F::text('title', 'Page title', 120, true, 'About 50–60 characters. "| Trip Andaman" is added automatically when it fits.'),
                F::area('description', 'Meta description', 170, true, 'About 120–160 characters summarising the page.'),
                F::image('og_', 'Link preview image (optional)', false, false)
                    ->columnSpanFull(),
            ], hideable: false),

            new SectionType('page_hero', 'Hero banner', 'The banner at the top of the page with the page heading.', fn () => [
                Grid::make(2)->schema([
                    F::text('eyebrow', 'Eyebrow (small text above the heading)', 120),
                    F::text('title', 'Page heading (H1)', 120, true),
                ]),
                F::area('description', 'Supporting text', 400, true),
                F::group('Background image', [
                    F::image('', 'Image', true, true),
                    F::media('mobile_image_id', 'Mobile image (optional)', 'image', false, 'Shown on phones instead of the main image. Uses the same alternative text.'),
                ]),
                F::group('Background video (optional)', [
                    F::video(),
                ], 'If a video is chosen it plays silently behind the heading; the image is used as the fallback.', true),
                F::group('Buttons (optional)', [
                    F::link('primary', 'Primary button'),
                    F::link('secondary', 'Secondary button'),
                ], null, true),
                Toggle::make('show_jump_links')->label('Show jump links under the text')->default(true)
                    ->helperText('Only on pages that have them (Experiences, Tour Packages).'),
            ], hideable: false),

            new SectionType('enquiry_banner', 'Enquiry banner', 'The "Let’s plan your dream trip" band near the end of the page. Its text is shared and edited in Global settings › Enquiry banner.', fn () => [
                F::visible(),
                F::media('image_id', 'Background image for this page (optional)', 'image', false, 'Leave empty to use the shared image from Global settings.'),
            ]),

            new SectionType('prose', 'Text content', 'Headed paragraphs of text with an optional button.', fn () => [
                F::items('blocks', 'Text blocks', [
                    F::text('title', 'Heading', 120, true),
                    F::text('accent', 'Highlighted heading words', 80),
                    F::rich('body_html', 'Text')->columnSpanFull(),
                ]),
                F::link('button', 'Button at the end'),
            ], hideable: false),
        ];
    }
}
