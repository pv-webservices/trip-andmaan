<?php

namespace App\Filament\Resources\Announcements\Pages;

use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Sections\SectionResource;
use App\Models\Announcement;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageAnnouncements extends ManageRecords
{
    protected static string $resource = AnnouncementResource::class;

    public function getSubheading(): ?string
    {
        return 'Enabled messages scroll in a bar above the header. Changes appear on the website as soon as you save. Drag rows to reorder. When no message applies, the bar is not shown at all.';
    }

    protected function getHeaderActions(): array
    {
        $section = Page::query()->where('key', 'global')->first()?->sections()->where('key', 'announcement_bar')->first();

        return [
            Action::make('appearance')->label('Colours & speed')->icon(Heroicon::OutlinedSwatch)->color('gray')
                ->url($section ? SectionResource::getUrl('edit', ['record' => $section]) : null)
                ->visible((bool) $section),
            CreateAction::make()->label('New announcement')
                ->mutateDataUsing(fn (array $data) => [...$data, 'updated_by' => auth()->id(), 'sort' => (int) Announcement::max('sort') + 1]),
        ];
    }
}
