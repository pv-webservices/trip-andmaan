<?php

namespace App\Filament\Resources\Popups;

use App\Cms\Fields as F;
use App\Cms\PageMap;
use App\Cms\SafeUrl;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Popups\Pages\ManagePopups;
use App\Models\Popup;
use BackedEnum;
use Filament\Actions\DeleteAction;
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
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWindow;

    protected static string|UnitEnum|null $navigationGroup = 'Promotions';

    protected static ?string $navigationLabel = 'Popup banners';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('name')->label('Campaign name (admin only)')->required()->maxLength(120),
            Section::make('Content')->schema([
                Grid::make(2)->schema([
                    F::media('image_id', 'Banner image (optional)'),
                    TextInput::make('image_alt')->label('Image alternative text')->maxLength(250)
                        ->helperText('Describe the image, including any text that appears in it.'),
                ]),
                TextInput::make('heading')->maxLength(120),
                Textarea::make('description')->maxLength(600)->rows(3),
                Grid::make(2)->schema([
                    TextInput::make('button_label')->label('Button text')->maxLength(80),
                    TextInput::make('button_url')->label('Button destination')->maxLength(500)->rule(new SafeUrl)
                        ->requiredWith('button_label')->placeholder('/tour-packages/'),
                ]),
            ]),
            Section::make('When and where')->schema([
                Toggle::make('is_enabled')->label('Enabled')->default(false),
                ...AnnouncementResource::targeting(),
                Grid::make(3)->schema([
                    TextInput::make('delay_seconds')->label('Delay before showing (seconds)')->numeric()->integer()->minValue(0)->maxValue(120)->default(5)->required(),
                    Select::make('frequency')->label('Show to each visitor')->options(Popup::FREQUENCIES)->default('session')->required()->selectablePlaceholder(false),
                    TextInput::make('priority')->numeric()->integer()->minValue(0)->maxValue(1000)->default(10)->required()
                        ->helperText('Only one popup shows at a time: the highest priority first, then the latest start date.'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold')->description(fn (Popup $p) => $p->heading),
                ToggleColumn::make('is_enabled')->label('Enabled'),
                TextColumn::make('targeting')->label('Pages')
                    ->formatStateUsing(fn ($state, Popup $p) => $state === 'all' ? 'Every page' : collect($p->page_keys)->map(fn ($k) => PageMap::targetOptions()[$k] ?? $k)->implode(', ')),
                TextColumn::make('schedule')->state(fn (Popup $p) => $p->scheduleLabel()),
                TextColumn::make('frequency')->formatStateUsing(fn ($state) => Popup::FREQUENCIES[$state] ?? $state),
                TextColumn::make('priority')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePopups::route('/')];
    }
}
