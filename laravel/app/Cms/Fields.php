<?php

namespace App\Cms;

use App\Media\MediaOptions;
use App\Media\MediaUsageRecorder;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

/**
 * Building blocks for the structured section editors. Naming conventions
 * matter: media references end in "_id", links in "url", rich text in "_html"
 * (see {@see Sanitizer} and {@see MediaUsageRecorder}).
 */
class Fields
{
    public const ICONS = [
        'arrow' => 'Arrow', 'route' => 'Route', 'pin' => 'Map pin', 'headset' => 'Headset', 'heart' => 'Heart',
        'compass' => 'Compass', 'sun' => 'Sun', 'boat' => 'Boat', 'leaf' => 'Leaf', 'waves' => 'Waves',
        'fish' => 'Fish', 'bag' => 'Bag', 'check' => 'Check', 'users' => 'People', 'calendar' => 'Calendar',
        'chat' => 'Chat', 'map' => 'Map', 'camera' => 'Camera', 'plane' => 'Plane', 'phone' => 'Phone',
    ];

    public const FOCAL = [
        '50% 50%' => 'Centre', '50% 0%' => 'Top', '50% 100%' => 'Bottom', '0% 50%' => 'Left', '100% 50%' => 'Right',
        '0% 0%' => 'Top left', '100% 0%' => 'Top right', '0% 100%' => 'Bottom left', '100% 100%' => 'Bottom right',
        '50% 30%' => 'Upper middle', '50% 70%' => 'Lower middle',
    ];

    public static function visible(string $help = 'Turn off to hide this section on the website without deleting its content.'): Toggle
    {
        return Toggle::make('visible')->label('Show this section')->default(true)->helperText($help);
    }

    public static function text(string $name, string $label, int $max = 200, bool $required = false, ?string $help = null): TextInput
    {
        return TextInput::make($name)->label($label)->maxLength($max)->required($required)->helperText($help);
    }

    public static function area(string $name, string $label, int $max = 1000, bool $required = false, ?string $help = null, int $rows = 3): Textarea
    {
        return Textarea::make($name)->label($label)->maxLength($max)->rows($rows)->required($required)->helperText($help);
    }

    public static function rich(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)->label($label)
            ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']])
            ->helperText('Links may use /page-path/, https://, tel: or mailto: addresses.');
    }

    /** Eyebrow + heading + highlighted heading words. */
    public static function heading(bool $withText = false, bool $required = true): Grid
    {
        $fields = [
            self::text('eyebrow', 'Eyebrow (small text above the heading)', 120),
            self::text('title', 'Heading', 160, $required),
            self::text('accent', 'Highlighted heading words', 120, false, 'Shown after the heading in the accent colour.'),
        ];
        if ($withText) {
            $fields[] = self::area('text', 'Introduction', 600)->columnSpanFull();
        }

        return Grid::make(3)->schema($fields);
    }

    public static function link(string $name, string $label, bool $withVisibility = true, bool $required = false): Fieldset
    {
        $fields = [
            self::text('label', 'Button text', 60, $required),
            TextInput::make('url')->label('Destination')->maxLength(500)->required($required)
                ->rule(new SafeUrl)
                ->placeholder('/contact/')
                ->helperText('A page path such as /tour-packages/, a full https:// address or tel:+91….'),
        ];
        if ($withVisibility) {
            $fields[] = Toggle::make('visible')->label('Show button')->default(true);
        }

        return Fieldset::make($label)->statePath($name)->schema($fields)->columns(3);
    }

    public static function url(string $name, string $label, bool $required = false): TextInput
    {
        return TextInput::make($name)->label($label)->maxLength(500)->required($required)->rule(new SafeUrl);
    }

    public static function media(string $name, string $label, string $type = 'image', bool $required = false, ?string $help = null): Select
    {
        return Select::make($name)
            ->label($label)
            ->required($required)
            ->searchable()
            ->allowHtml()
            ->options(fn (?string $state) => MediaOptions::recent($type, $state))
            ->getSearchResultsUsing(fn (string $search) => MediaOptions::search($type, $search))
            ->getOptionLabelUsing(fn ($value) => MediaOptions::label($value))
            ->createOptionForm(MediaOptions::uploadForm($type))
            ->createOptionUsing(fn (array $data) => MediaOptions::createFromUpload($data, $type))
            ->createOptionModalHeading('Upload to the media library')
            ->helperText($help ?? 'Choose from the media library or upload a new file. Choosing a file here changes only this place.');
    }

    /** Image picker with alt text and focal point, stored as "{prefix}image_id", "{prefix}alt", "{prefix}focal". */
    public static function image(string $prefix = '', string $label = 'Image', bool $required = false, bool $focal = false, bool $decorative = false): Grid
    {
        $fields = [
            self::media($prefix.'image_id', $label, 'image', $required)->columnSpanFull(),
            self::text($prefix.'alt', 'Alternative text', 250, false, $decorative
                ? 'This image is decorative; leave empty unless it adds information.'
                : 'Describe the image for people who cannot see it. Leave empty only if purely decorative.'),
        ];
        if ($focal) {
            $fields[] = Select::make($prefix.'focal')->label('Focal point')->options(self::FOCAL)->default('50% 50%')
                ->helperText('Which part of the image stays visible when it is cropped.');
        }

        return Grid::make(2)->schema($fields);
    }

    public static function icon(string $name = 'icon', string $label = 'Icon'): Select
    {
        return Select::make($name)->label($label)->options(self::ICONS)->default('arrow')->required();
    }

    /** @param array<int, Component|Field> $schema */
    public static function items(string $name, string $label, array $schema, string $titleField = 'title', ?int $max = null, int $columns = 2): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->schema($schema)
            ->columns($columns)
            ->reorderableWithButtons()
            ->collapsible()
            ->collapsed()
            ->cloneable()
            ->maxItems($max)
            ->itemLabel(fn (array $state): ?string => $state[$titleField] ?? null)
            ->defaultItems(0)
            ->addActionLabel('Add item');
    }

    /** A reorderable list of single lines (stored as a plain array of strings). */
    public static function lines(string $name, string $label, int $max = 400, bool $multiline = false): Repeater
    {
        $field = $multiline
            ? Textarea::make('text')->hiddenLabel()->maxLength($max)->rows(3)->required()
            : TextInput::make('text')->hiddenLabel()->maxLength($max)->required();

        return Repeater::make($name)->label($label)->simple($field)->reorderableWithButtons()->defaultItems(0)->addActionLabel('Add line');
    }

    public static function group(string $heading, array $schema, ?string $description = null, bool $collapsed = false): Section
    {
        return Section::make($heading)->description($description)->schema($schema)->collapsible()->collapsed($collapsed);
    }

    /** Video with poster image. */
    public static function video(string $prefix = '', string $label = 'Video', bool $required = false): Grid
    {
        return Grid::make(1)->schema([
            self::media($prefix.'video_id', $label, 'video', $required, 'Web-ready MP4 or WebM. Keep clips short; large files load slowly on mobile.'),
            self::media($prefix.'poster_id', 'Poster image (shown before the video plays)', 'image'),
        ]);
    }

    /** Fields of a silent reel clip. */
    public static function reels(string $name = 'reels', string $label = 'Video clips'): Repeater
    {
        return self::items($name, $label, [
            self::text('title', 'Title', 80, true),
            self::text('text', 'Short description', 200),
            self::media('video_id', 'Video', 'video', true),
            self::media('poster_id', 'Poster image', 'image'),
            self::text('aria', 'Accessible description', 200, false, 'For example "Silent clip: sea walk at North Bay". Defaults to the title.'),
        ]);
    }
}
