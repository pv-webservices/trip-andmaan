<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\Admin;
use App\Models\Page;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** One page and its sections. */
class ManagePage extends ViewRecord
{
    protected static string $resource = PageResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function getSubheading(): ?string
    {
        /** @var Page $page */
        $page = $this->getRecord();

        return match ($page->kind) {
            'global' => 'These settings appear on every page. Changes take effect on the whole website when published.',
            'template' => 'These texts are shared by every item that uses this template. Item content itself is edited under Packages or Destinations.',
            default => 'Public address: '.$page->path,
        };
    }

    protected function getHeaderActions(): array
    {
        /** @var Page $page */
        $page = $this->getRecord();

        return [
            Admin::previewAction(fn () => $page->previewUrl(), 'Preview page')->visible($page->kind !== 'global'),
            Admin::previewAction('/preview/', 'Preview site')->visible($page->kind === 'global'),
            Action::make('publishAll')
                ->label('Publish all changes on this page')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->requiresConfirmation()
                ->modalDescription('Every section with draft changes on this page goes live now.')
                ->visible(fn () => $page->sections()->get()->contains(fn (Section $s) => $s->hasUnpublishedChanges() && $s->draft_data))
                ->action(function () use ($page) {
                    Admin::attempt(function () use ($page) {
                        $page->sections()->get()
                            ->filter(fn (Section $s) => $s->hasUnpublishedChanges() && $s->draft_data)
                            ->each(fn (Section $s) => $s->publish(auth()->user()));
                    }, 'Published. The website shows the changes now.');
                }),
        ];
    }
}
