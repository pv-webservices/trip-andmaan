<?php

namespace App\Filament\Resources\Packages;

use App\Cms\Fields as F;
use App\Cms\Views\Price;
use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;
use App\Models\Package;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Website content';

    protected static ?string $navigationLabel = 'Tour packages';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('Check before publishing')
                ->warning()
                ->description(fn (?Package $record) => $record ? implode(' ', $record->warnings()) : null)
                ->visible(fn (?Package $record) => $record && $record->warnings()),
            Tabs::make('package')->persistTabInQueryString()->tabs([
                Tab::make('Overview')->icon(Heroicon::OutlinedInformationCircle)->schema([
                    Grid::make(2)->schema([
                        TextInput::make('title')->required()->maxLength(120)->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')->label('URL slug')->required()->maxLength(120)
                            ->regex('/^[a-z0-9]+(-[a-z0-9]+)*$/')
                            ->rules(['not_in:'.implode(',', Package::RESERVED_SLUGS)])
                            ->unique(ignoreRecord: true)
                            ->prefix('/tour-packages/')->suffix('/')
                            ->helperText('Lowercase letters, numbers and hyphens. Changing it on a published package redirects the old address automatically when you publish.'),
                    ]),
                    Textarea::make('overview')->required()->maxLength(600)->rows(3),
                    Grid::make(2)->schema([
                        TextInput::make('nights')->numeric()->integer()->minValue(1)->maxValue(30)->required()->live(onBlur: true),
                        TextInput::make('days')->numeric()->integer()->minValue(1)->maxValue(31)->required()
                            ->helperText('Usually one more than the nights.'),
                    ]),
                    Repeater::make('stays')->label('Where travellers stay')->schema([
                        TextInput::make('place')->required()->maxLength(60),
                        TextInput::make('nights')->numeric()->integer()->minValue(1)->maxValue(30)->required(),
                    ])->columns(2)->reorderableWithButtons()->defaultItems(0)->addActionLabel('Add stay')
                        ->helperText('Shown as route chips and the route line, e.g. "2N Port Blair · 1N Havelock".'),
                    TagsInput::make('tags')->label('Highlight tags')->helperText('Short tags on the package card, e.g. Havelock, Neil.')->nestedRecursiveRules(['max:40']),
                ]),
                Tab::make('Itinerary')->icon(Heroicon::OutlinedListBullet)->schema([
                    Repeater::make('itineraryDays')
                        ->relationship()
                        ->label('Day by day')
                        ->orderColumn('sort')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->cloneable()
                        ->itemLabel(fn (array $state, ?int $index): string => 'Day '.(($index ?? 0) + 1).': '.($state['title'] ?? ''))
                        ->schema([
                            TextInput::make('title')->required()->maxLength(200),
                            Textarea::make('description')->required()->rows(6)->maxLength(5000)
                                ->helperText('Leave a blank line between paragraphs.'),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Add day')
                        ->helperText('Day numbers follow this order. Move days with the arrows. These days belong to this package only.'),
                ]),
                Tab::make('Images & files')->icon(Heroicon::OutlinedPhoto)->schema([
                    Grid::make(3)->schema([
                        F::media('image_id', 'Main image', 'image', true),
                        TextInput::make('image_alt')->label('Main image alternative text')->maxLength(250),
                        Select::make('focal')->label('Focal point')->options(F::FOCAL)->default('50% 50%')->selectablePlaceholder(false),
                    ]),
                    F::media('mobile_image_id', 'Mobile hero image (optional)', 'image'),
                    Repeater::make('gallery')
                        ->relationship()
                        ->label('Photos ("Moments from this route")')
                        ->orderColumn('sort')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->schema([
                            F::media('media_id', 'Photo', 'image', true),
                            TextInput::make('caption')->maxLength(120),
                            TextInput::make('alt')->label('Alternative text')->maxLength(250),
                        ])->columns(3)->defaultItems(0)->addActionLabel('Add photo'),
                    F::media('itinerary_pdf_id', 'Downloadable itinerary (PDF)', 'document'),
                ]),
                Tab::make('Pricing')->icon(Heroicon::OutlinedCurrencyRupee)->schema([
                    Radio::make('price_mode')->label('How is the price shown?')->options(Package::PRICE_MODES)->default('on_request')->required()->live(),
                    Grid::make(3)->visible(fn (Get $get) => $get('price_mode') !== 'on_request')->schema([
                        Select::make('currency')->options(Package::CURRENCIES)->default('INR')->required()->selectablePlaceholder(false),
                        TextInput::make('price_amount')->label('Price')->required(fn (Get $get) => $get('price_mode') !== 'on_request')
                            ->regex('/^\d{1,10}(\.\d{1,2})?$/')->helperText('Numbers only, e.g. 25000 or 24999.50'),
                        TextInput::make('price_original')->label('Original price (optional, shown struck through)')
                            ->regex('/^\d{1,10}(\.\d{1,2})?$/'),
                        Select::make('price_unit')->label('Per')->options(Package::PRICE_UNITS)->default('per_person')->required()->selectablePlaceholder(false),
                        DatePicker::make('price_valid_from')->label('Valid from (optional)')->native(false)->displayFormat('j M Y'),
                        DatePicker::make('price_valid_until')->label('Valid until (optional)')->native(false)->displayFormat('j M Y')
                            ->afterOrEqual('price_valid_from')
                            ->helperText('Outside these dates the website shows "Price on request".'),
                    ]),
                    TextInput::make('price_note')->label('Pricing note (optional)')->maxLength(300)
                        ->helperText('For example: "Based on two adults sharing, standard hotels."'),
                ]),
                Tab::make('Inclusions & notes')->icon(Heroicon::OutlinedClipboardDocumentCheck)->schema([
                    F::lines('inclusions', 'Included (optional)', 200),
                    F::lines('exclusions', 'Not included (optional)', 200),
                    F::lines('notes', 'Travel notes for this package', 400, true)
                        ->helperText('Shown under "Good to know" after the notes shared by every package (Pages › Package page).'),
                ]),
                Tab::make('Listing & search')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    Grid::make(3)->schema([
                        Toggle::make('is_featured')->label('Feature on the homepage'),
                        TextInput::make('featured_order')->label('Featured order')->numeric()->integer()->minValue(0)->default(0),
                        TextInput::make('sort')->label('Order in lists')->numeric()->integer()->minValue(0)->default(0),
                    ]),
                    TextInput::make('meta_title')->label('Page title for search engines (optional)')->maxLength(120)
                        ->helperText('Leave empty to use "{title} — {duration} Andaman Tour".'),
                    Textarea::make('meta_description')->label('Description for search engines (optional)')->maxLength(300)->rows(2),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('title')->searchable()->weight('bold')
                    ->description(fn (Package $p) => '/tour-packages/'.$p->slug.'/'),
                TextColumn::make('duration')->state(fn (Package $p) => "{$p->nights}N / {$p->days}D"),
                TextColumn::make('status')->badge()->state(fn (Package $p) => $p->statusLabel())
                    ->color(fn ($state) => match ($state) {
                        'Published' => 'success',
                        'Published · draft changes' => 'warning',
                        'Archived' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('price')->state(fn (Package $p) => (new Price($p->priceData()))->label()),
                ToggleColumn::make('is_featured')->label('Featured')
                    ->afterStateUpdated(fn (Package $p) => $p->forceFill(['has_unpublished_changes' => true])->saveQuietly()),
                TextColumn::make('updated_at')->label('Last edited')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                    ->default(null)
                    ->query(fn ($query, array $data) => $data['value'] ? $query->where('status', $data['value']) : $query->where('status', '!=', 'archived')),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackages::route('/'),
            'create' => CreatePackage::route('/create'),
            'edit' => EditPackage::route('/{record}/edit'),
        ];
    }
}
