<?php

namespace App\Filament\Resources\Redirects\Pages;

use App\Filament\Resources\Redirects\RedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRedirects extends ManageRecords
{
    protected static string $resource = RedirectResource::class;

    public function getSubheading(): ?string
    {
        return 'When a package or destination address changes, the old address is added here automatically. You can also add your own.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New redirect')];
    }
}
