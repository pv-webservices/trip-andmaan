<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    public function getSubheading(): ?string
    {
        return 'Images, videos and itinerary PDFs used on the website. Files that are in use cannot be deleted.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Upload')];
    }
}
