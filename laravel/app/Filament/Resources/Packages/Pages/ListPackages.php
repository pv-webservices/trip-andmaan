<?php

namespace App\Filament\Resources\Packages\Pages;

use App\Filament\Resources\Packages\PackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    protected static string $resource = PackageResource::class;

    public function getSubheading(): ?string
    {
        return 'Drag rows to change the order of packages on the website. Archived packages are hidden; use the status filter to see them.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New package')];
    }
}
