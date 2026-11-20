<?php

namespace App\Filament\Resources\Announcements;

use App\Cms\PageMap;
use App\Cms\SafeUrl;
use App\Filament\Resources\Announcements\Pages\ManageAnnouncements;
use App\Models\Announcement;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Promotions';

    protected static ?string $navigationLabel = 'Announcement bar';

    protected static ?int $navigationSort = 1;

    public static function targeting(): array
    {
        return [
            Radio::make('targeting')->label('Show on')->options(['all' => 'Every page', 'selected' => 'Selected pages only'])->default('all')->inline()->live(),
            CheckboxList::make('page_keys')->label('Pages')->options(PageMap::targetOptions())->columns(3)
                ->visible(fn (Get $get) => $get('targeting') === 'selected')
                ->required(fn (Get $get) => $get('targeting') === 'selected'),
            Grid::make(2)->schema([
                DateTimePicker::make('starts_at')->label('Start (India time, optional)')->seconds(false)->timezone('Asia/Kolkata'),
                DateTimePicker::make('ends_at')->label('End (India time, optional)')->seconds(false)->timezone('Asia/Kolkata')->after('starts_at'),
            ]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('text')->label('Message')->required()->maxLength(300),
            Grid::make(2)->schema([
                TextInput::make('link_url')->label('Link (optional)')->maxLength(500)->rule(new SafeUrl)->placeholder('/tour-packages/'),
                TextInput::make('link_label')->label('Link text')->maxLength(80)->placeholder('Learn more'),
            ]),
            Toggle::make('is_enabled')->label('Enabled')->default(true),
            ...self::targeting(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('text')->label('Message')->wrap()->searchable(),
                ToggleColumn::make('is_enabled')->label('Enabled'),
                TextColumn::make('targeting')->label('Pages')
                    ->formatStateUsing(fn ($state, Announcement $a) => $state === 'all' ? 'Every page' : collect($a->page_keys)->map(fn ($k) => PageMap::targetOptions()[$k] ?? $k)->implode(', ')),
                TextColumn::make('schedule')->state(fn (Announcement $a) => $a->scheduleLabel()),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAnnouncements::route('/')];
    }
}
