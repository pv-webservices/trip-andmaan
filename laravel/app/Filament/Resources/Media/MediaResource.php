<?php

namespace App\Filament\Resources\Media;

use App\Cms\SafeUrl;
use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Library';

    protected static ?string $navigationLabel = 'Media library';

    protected static ?string $modelLabel = 'media file';

    protected static ?string $pluralModelLabel = 'media library';

    /** Metadata fields shared by upload and edit. */
    public static function metadataFields(): array
    {
        return [
            TextInput::make('title')->label('Name in the library')->maxLength(150),
            TextInput::make('alt')->label('Default alternative text')->maxLength(250)
                ->helperText('Used when this image is chosen in a new place. Each place can still have its own text.'),
            Textarea::make('caption')->maxLength(500)->rows(2),
            Section::make('Credit / attribution')->description('Shown on the Photo credits page when an author and source are given.')
                ->collapsible()->collapsed()->schema([
                    Grid::make(2)->schema([
                        TextInput::make('credit_title')->label('Original title')->maxLength(190),
                        TextInput::make('credit_author')->label('Author')->maxLength(190),
                        TextInput::make('credit_license')->label('Licence')->maxLength(100)->placeholder('CC BY 4.0'),
                        TextInput::make('credit_license_url')->label('Licence link')->maxLength(500)->rule(new SafeUrl),
                        TextInput::make('credit_source_url')->label('Source link')->maxLength(500)->rule(new SafeUrl),
                        TextInput::make('credit_changes')->label('Changes made')->maxLength(300),
                    ]),
                ]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('File')->schema([
                Text::make(fn (?Media $record) => $record ? self::preview($record) : ''),
            ]),
            ...self::metadataFields(),
            Section::make('Where it is used')->schema([
                Text::make(fn (?Media $record) => $record ? self::usages($record) : ''),
            ]),
        ]);
    }

    public static function preview(Media $m): HtmlString
    {
        $info = e(strtoupper($m->type)).' · '.e($m->mime).' · '.e($m->humanSize())
            .($m->width ? ' · '.$m->width.'×'.$m->height.' px' : '')
            .($m->variants ? ' · sizes: '.e(implode(', ', array_keys($m->variants))).' px' : '');
        $media = match ($m->type) {
            'image' => '<img src="'.e($m->urlNear(800)).'" alt="" style="max-height:320px;border-radius:8px">',
            'video' => '<video src="'.e($m->url()).'" controls muted style="max-height:320px;border-radius:8px"></video>',
            default => '<a href="'.e($m->url()).'" target="_blank" rel="noopener" style="text-decoration:underline">Open PDF</a>',
        };

        return new HtmlString($media.'<p style="margin-top:8px;font-size:13px;opacity:.75">'.$info.'<br>Address: '.e($m->url()).'</p>');
    }

    public static function usages(Media $m): HtmlString
    {
        $labels = $m->usages()->pluck('label')->unique()->values();
        if ($labels->isEmpty()) {
            return new HtmlString('<p>Not used anywhere. It can be deleted.</p>');
        }

        return new HtmlString('<ul style="list-style:disc;padding-left:20px">'.$labels->map(fn ($l) => '<li>'.e($l).'</li>')->implode('').'</ul>');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->withCount('usages'))
            ->columns([
                ImageColumn::make('thumb')->label('')->state(fn (Media $m) => $m->isImage() ? url($m->urlNear(200)) : null)->imageHeight(48),
                TextColumn::make('title')->label('Name')->searchable(['title', 'original_name', 'alt', 'caption', 'legacy_key'])->weight('bold')
                    ->description(fn (Media $m) => $m->alt),
                TextColumn::make('type')->badge(),
                TextColumn::make('dimensions')->state(fn (Media $m) => $m->width ? "{$m->width}×{$m->height}" : '—'),
                TextColumn::make('size')->state(fn (Media $m) => $m->humanSize()),
                TextColumn::make('usages_count')->label('Used in')->badge()
                    ->formatStateUsing(fn ($state) => $state ? "{$state} place".($state > 1 ? 's' : '') : 'Unused')
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('type')->options(['image' => 'Images', 'video' => 'Videos', 'document' => 'PDFs']),
            ])
            ->recordActions([EditAction::make()->label('Details')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/upload'),
            'edit' => EditMedia::route('/{record}'),
        ];
    }
}
