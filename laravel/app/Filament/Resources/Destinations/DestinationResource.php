<?php

namespace App\Filament\Resources\Destinations;

use App\Cms\Fields as F;
use App\Filament\Resources\Destinations\Pages\CreateDestination;
use App\Filament\Resources\Destinations\Pages\EditDestination;
use App\Filament\Resources\Destinations\Pages\ListDestinations;
use App\Models\Destination;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DestinationResource extends Resource
{
    protected static ?string $model = Destination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAsiaAustralia;

    protected static string|UnitEnum|null $navigationGroup = 'Website content';

    protected static ?string $navigationLabel = 'Destinations';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Basics')->schema([
                Grid::make(3)->schema([
                    TextInput::make('name')->required()->maxLength(80),
                    TextInput::make('official_name')->label('Official / other name')->required()->maxLength(120),
                    TextInput::make('slug')->label('URL slug')->required()->maxLength(80)->regex('/^[a-z0-9]+(-[a-z0-9]+)*$/')
                        ->unique(ignoreRecord: true)->prefix('/destinations/')->suffix('/'),
                ]),
                TextInput::make('short')->label('Tagline')->required()->maxLength(120),
                TextInput::make('intro')->label('Introduction line')->required()->maxLength(200),
                Textarea::make('body')->label('Description')->required()->rows(4)->maxLength(2000),
                F::lines('highlights', 'Highlights', 120),
                Textarea::make('note')->label('"Before you go" note')->rows(2)->maxLength(600),
                F::url('source_url', 'Official reference link (https://…)'),
            ]),
            Section::make('Images')->schema([
                Grid::make(3)->schema([
                    F::media('image_id', 'Main image', 'image', true),
                    TextInput::make('image_alt')->label('Hero image alternative text')->maxLength(250)->placeholder('Defaults to "<name> scenery"'),
                    Select::make('focal')->label('Focal point')->options(F::FOCAL)->default('50% 50%')->selectablePlaceholder(false),
                ]),
                F::media('mobile_image_id', 'Mobile hero image (optional)', 'image'),
                Grid::make(3)->schema([
                    F::media('secondary_image_id', 'Second photo (optional)', 'image'),
                    TextInput::make('secondary_alt')->label('Second photo alternative text')->maxLength(250),
                    Toggle::make('secondary_portrait')->label('Second photo is portrait'),
                ]),
            ]),
            Section::make('Video clips (optional)')->collapsible()->collapsed()->schema([F::reels()]),
            Section::make('Order & search')->collapsible()->collapsed()->schema([
                TextInput::make('sort')->label('Order in lists')->numeric()->integer()->minValue(0)->default(0),
                TextInput::make('meta_title')->label('Page title for search engines (optional)')->maxLength(120),
                Textarea::make('meta_description')->label('Description for search engines (optional)')->maxLength(300)->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold')->description(fn (Destination $d) => '/destinations/'.$d->slug.'/'),
                TextColumn::make('official_name')->label('Official name'),
                TextColumn::make('status')->badge()->state(fn (Destination $d) => $d->statusLabel())
                    ->color(fn ($state) => match ($state) {
                        'Published' => 'success', 'Published · draft changes' => 'warning', 'Archived' => 'gray', default => 'info',
                    }),
                TextColumn::make('updated_at')->label('Last edited')->since(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->where('status', '!=', 'archived'))
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDestinations::route('/'),
            'create' => CreateDestination::route('/create'),
            'edit' => EditDestination::route('/{record}/edit'),
        ];
    }
}
