<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use App\Filament\Resources\Sections\SectionResource;
use App\Filament\Support\Admin;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Sections';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->with(['editor', 'publisher']))
            ->columns([
                TextColumn::make('label')->label('Section')->weight('bold')
                    ->description(fn (Section $s) => $s->definition()->description),
                TextColumn::make('status')->badge()
                    ->state(fn (Section $s) => $s->statusLabel())
                    ->color(fn ($state) => match ($state) {
                        'Published' => 'success',
                        'Draft changes' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('draft_updated_at')->label('Last edited')
                    ->state(fn (Section $s) => Admin::when($s->draft_updated_at, $s->editor?->name)),
                TextColumn::make('published_at')->label('Published')
                    ->state(fn (Section $s) => Admin::when($s->published_at, $s->publisher?->name)),
            ])
            ->recordUrl(fn (Section $s) => SectionResource::getUrl('edit', ['record' => $s]))
            ->recordActions([
                Action::make('edit')->label('Edit')->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Section $s) => SectionResource::getUrl('edit', ['record' => $s])),
                Action::make('publish')->label('Publish')->icon(Heroicon::OutlinedRocketLaunch)->color('success')
                    ->visible(fn (Section $s) => $s->hasUnpublishedChanges() && $s->draft_data)
                    ->requiresConfirmation()
                    ->action(fn (Section $s) => Admin::attempt(fn () => $s->publish(auth()->user()), 'Section published.')),
            ]);
    }
}
