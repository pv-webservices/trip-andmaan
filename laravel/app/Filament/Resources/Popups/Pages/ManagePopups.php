<?php

namespace App\Filament\Resources\Popups\Pages;

use App\Filament\Resources\Popups\PopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePopups extends ManageRecords
{
    protected static string $resource = PopupResource::class;

    public function getSubheading(): ?string
    {
        return 'Popups appear as soon as you save them as enabled. Editing a popup’s content or dates makes it a new campaign, so visitors who closed the old one will see it once.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New popup')->mutateDataUsing(fn (array $data) => [...$data, 'updated_by' => auth()->id()])];
    }
}
