<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Pages\ManagePage;
use App\Filament\Resources\Pages\RelationManagers\SectionsRelationManager;
use App\Models\Page;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Pages → select page → select section → edit → preview → publish. */
class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Website content';

    protected static ?string $navigationLabel = 'Pages & global settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->with('sections'))
            ->columns([
                TextColumn::make('title')->label('Page')->weight('bold')
                    ->description(fn (Page $p) => $p->path ?? match ($p->kind) {
                        'global' => 'Shared by every page: contact details, menu, footer, defaults',
                        default => 'Template used by every item of this kind',
                    }),
                TextColumn::make('kind')->badge()->label('Type')
                    ->formatStateUsing(fn ($state) => ['page' => 'Page', 'template' => 'Template', 'global' => 'Global'][$state] ?? $state)
                    ->color(fn ($state) => ['page' => 'gray', 'template' => 'info', 'global' => 'warning'][$state] ?? 'gray'),
                TextColumn::make('pending')->label('Unpublished changes')->badge()
                    ->state(fn (Page $p) => $p->sections->filter->hasUnpublishedChanges()->count())
                    ->formatStateUsing(fn ($state) => $state ? "{$state} section".($state > 1 ? 's' : '') : 'None')
                    ->color(fn ($state) => $state ? 'warning' : 'success'),
                TextColumn::make('last_published')->label('Last published')
                    ->state(fn (Page $p) => $p->sections->max('published_at')?->timezone('Asia/Kolkata')->format('j M Y, g:i a') ?? '—'),
            ])
            ->recordUrl(fn (Page $p) => static::getUrl('view', ['record' => $p]));
    }

    public static function getRelations(): array
    {
        return [SectionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'view' => ManagePage::route('/{record}'),
        ];
    }
}
