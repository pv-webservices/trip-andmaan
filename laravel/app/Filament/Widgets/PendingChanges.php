<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Filament\Resources\Packages\PackageResource;
use App\Filament\Resources\Sections\SectionResource;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Section;
use Filament\Widgets\Widget;

/** Dashboard list of drafts that are not live yet. */
class PendingChanges extends Widget
{
    protected string $view = 'filament.widgets.pending-changes';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $sections = Section::query()->with('page')->get()
            ->filter(fn (Section $s) => $s->hasUnpublishedChanges() && $s->draft_data)
            ->map(fn (Section $s) => ['label' => $s->page->title.' › '.$s->label, 'url' => SectionResource::getUrl('edit', ['record' => $s])]);
        $packages = Package::query()->where('status', '!=', 'archived')->where('has_unpublished_changes', true)->get()
            ->map(fn (Package $p) => ['label' => 'Package › '.$p->title.($p->isLive() ? '' : ' (not published)'), 'url' => PackageResource::getUrl('edit', ['record' => $p])]);
        $destinations = Destination::query()->where('status', '!=', 'archived')->where('has_unpublished_changes', true)->get()
            ->map(fn (Destination $d) => ['label' => 'Destination › '.$d->name.($d->isLive() ? '' : ' (not published)'), 'url' => DestinationResource::getUrl('edit', ['record' => $d])]);

        return ['items' => $sections->concat($packages)->concat($destinations)->values()];
    }
}
