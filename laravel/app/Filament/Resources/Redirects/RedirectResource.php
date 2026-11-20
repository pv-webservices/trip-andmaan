<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Permanent redirects. Slug changes add rows here automatically. */
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Redirects';

    public static function form(Schema $schema): Schema
    {
        $path = ['required', 'max:250', 'regex:#^/[a-z0-9/_.-]*$#'];

        return $schema->columns(2)->components([
            TextInput::make('from_path')->label('Old address')->rules($path)->unique(ignoreRecord: true)->placeholder('/old-page/'),
            TextInput::make('to_path')->label('New address')->rules($path)->different('from_path')->placeholder('/new-page/'),
            TextInput::make('reason')->maxLength(190)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('from_path')->label('Old address')->searchable(),
            TextColumn::make('to_path')->label('Redirects to')->searchable(),
            TextColumn::make('reason'),
            TextColumn::make('created_at')->label('Added')->since(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRedirects::route('/')];
    }
}
